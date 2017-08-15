<?php

namespace tad\WPBrowser\Connector;

use Codeception\Exception\ModuleException;
use Codeception\Lib\Connector\Universal;
use Codeception\Module\WPLoader;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\History;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Process\Process;
use tad\WPBrowser\Module\Support\UriToIndexMapper;
use function Patchwork\redefine;
use function Patchwork\relay;

class WordPress extends Universal {
	/**
	 * @var bool
	 */
	protected $insulated = true;

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var string
	 */
	protected $domain;

	/**
	 * @var array
	 */
	protected $headers;

	/**
	 * @var string
	 */
	protected $rootFolder;
	/**
	 * @var UriToIndexMapper
	 */
	protected $uriToIndexMapper;

	/**
	 * @var WPLoader
	 */
	protected $wpLoader;

	/**
	 * @var bool
	 */
	protected $echoOutput = true;

	protected $loadTemplateHandle;

	public function __construct(
		array $server = array(),
		History $history = null,
		CookieJar $cookieJar = null,
		UriToIndexMapper $uriToIndexMapper = null
	) {
		parent::__construct($server, $history, $cookieJar);
		$this->uriToIndexMapper = $uriToIndexMapper ? $uriToIndexMapper : new UriToIndexMapper($this->rootFolder);
	}

	/**
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function doRequestInProcess($request) {
		if ($this->mockedResponse) {
			$response = $this->mockedResponse;
			$this->mockedResponse = null;
			return $response;
		}

		$requestCookie = $request->getCookies();
		$requestServer = $request->getServer();
		$requestFiles = $this->remapFiles($request->getFiles());

		$parseResult = parse_url($request->getUri());
		$uri = $parseResult["path"];
		if (array_key_exists("query", $parseResult)) {
			$uri .= "?" . $parseResult["query"];
		}

		$requestRequestArray = $this->remapRequestParameters($request->getParameters());

		$requestServer['REQUEST_METHOD'] = strtoupper( $request->getMethod() );
		$requestServer['REQUEST_URI'] = $uri;
		$requestServer['HTTP_HOST'] = $this->domain;
		$requestServer['SERVER_PROTOCOL'] = 'HTTP/1.1';
		$requestServer['SERVER_NAME'] = $this->domain;
		$requestServer['HTTP_CLIENT_IP'] = '127.0.0.1';

		$this->index = $this->uriToIndexMapper->getIndexForUri($uri);

		$phpSelf = str_replace($this->rootFolder, '', $this->index);
		$requestServer['PHP_SELF'] = $phpSelf;

		$env = [
			'headers' => $this->headers,
			'cookie' => $requestCookie,
			'server' => $requestServer,
			'files' => $requestFiles,
			'request' => $requestRequestArray,
		];

		if (strtoupper($request->getMethod()) == 'GET') {
			$env['get'] = $env['request'];
		} else {
			$env['post'] = $env['request'];
		}

		$requestScript = dirname(dirname(__DIR__)) . '/scripts/request.php';

		$command = PHP_BINARY .
			' ' . escapeshellarg($requestScript) .
			' ' . escapeshellarg($this->index) .
			' ' . escapeshellarg(base64_encode(serialize($env)));

		$process = new Process($command);
		$process->run();
		$rawProcessOutput = $process->getOutput();

		$unserializedResponse = @unserialize(base64_decode($rawProcessOutput));

		if (false === $unserializedResponse) {
			$message = 'Server responded with: ' . $rawProcessOutput;
			throw new ModuleException(\Codeception\Module\WordPress::class, $message);
		}

		$_SERVER = empty($unserializedResponse['server']) ? [] : $unserializedResponse['server'];
		$_FILES = empty($unserializedResponse['files']) ? [] : $unserializedResponse['files'];
		$_REQUEST = empty($unserializedResponse['request']) ? [] : $unserializedResponse['request'];
		$_GET = empty($unserializedResponse['get']) ? [] : $unserializedResponse['get'];
		$_POST = empty($unserializedResponse['post']) ? [] : $unserializedResponse['post'];

		$content = $unserializedResponse['content'];
		$headers = $this->replaceSiteUrlDeep($unserializedResponse['headers'], $this->url);

		$response = new Response($content, $unserializedResponse['status'], $headers);

		return $response;
	}

	public function doRequest($request) {
		if ($this->mockedResponse) {
			$response = $this->mockedResponse;
			$this->mockedResponse = null;
			return $response;
		}

		$_COOKIE = $request->getCookies();
		$_SERVER = $request->getServer();
		$_FILES = $this->remapFiles($request->getFiles());

		$uri = str_replace('http://localhost', '', $request->getUri());

		$_REQUEST = $this->remapRequestParameters($request->getParameters());
		if (strtoupper($request->getMethod()) == 'GET') {
			$_GET = $_REQUEST;
		} else {
			$_POST = $_REQUEST;
		}

		$_SERVER['REQUEST_METHOD']  = strtoupper($request->getMethod());
		$_SERVER['REQUEST_URI']     = $uri;
		$_SERVER['PHP_SELF']        = str_replace($this->rootFolder, '', $this->uriToIndexMapper->getIndexForUri($uri));
		$_SERVER['SERVER_PROTOCOL'] = !empty($_SERVER['SERVER_PROTOCOL']) ?: 'HTTP/1.0';
		$_SERVER['SERVER_NAME'] = !empty($_SERVER['SERVER_NAME']) ?: $this->domain;

		if ($this->echoOutput) {
			$this->addPrintOutputAction();
		} else {
			$this->removePrintOutputAction();
		}

		ob_start();
		$this->wpLoader->go_to($uri);
		$content = ob_get_contents();
		ob_end_clean();

		$headers = [];
		$php_headers = headers_list();
		foreach ($php_headers as $value) {
			// Get the header name
			$parts = explode(':', $value);
			if (count($parts) > 1) {
				$name = trim(array_shift($parts));
				// Build the header hash map
				$headers[$name] = trim(implode(':', $parts));
			}
		}
		$headers['Content-type'] = isset($headers['Content-type'])
			? $headers['Content-type']
			: "text/html; charset=UTF-8";

		$response = new Response($content, 200, $headers);
		return $response;
	}

	private function replaceSiteUrlDeep($array, $url) {
		if (empty($array)) {
			return [];
		}
		$replaced = [];
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$replaced[$key] = $this->replaceSiteUrlDeep($value, $url);
			} else {
				$replaced[$key] = str_replace(urlencode($url), urldecode(''), str_replace($url, '', $value));
			}
		}

		return $replaced;
	}

	public function setUrl($url) {
		$this->url = $url;
	}

	public function setIndexFor($uri) {
		$this->index = $this->rootFolder . $this->uriToIndexMapper->getIndexForUri($uri);
	}

	public function getIndex() {
		return $this->index;
	}

	public function getRootFolder() {
		return $this->rootFolder;
	}

	/**
	 * @param string $rootFolder
	 */
	public function setRootFolder($rootFolder) {
		if (!is_dir($rootFolder)) {
			throw new \InvalidArgumentException('Root folder [' . $rootFolder . '] is not an existing folder!');
		}
		$this->rootFolder = $rootFolder;
		$this->uriToIndexMapper->setRoot($rootFolder);
	}

	public function getHeaders() {
		return $this->headers;
	}

	public function setHeaders(array $headers = []) {
		$this->headers = $headers;
	}

	public function getDomain() {
		return $this->domain;
	}

	public function setDomain($domain) {
		$this->domain = $domain;
	}

	public function resetCookies() {
		$this->cookieJar = new CookieJar();
	}

	public function setWpLoader(WPLoader $wpLoader) {
		$this->wpLoader = $wpLoader;
	}

	public function echoOutput($echoOutput) {
		$this->echoOutput = $echoOutput;
	}

	protected function addPrintOutputAction() {
		add_action('wp', [$this, 'includeTemplateLoader'], 99);
		add_filter('redirect_canonical', '__return_false');
		$this->loadTemplateHandle = redefine('load_template', function($_template_file){
			relay([$_template_file, false]);
		});
	}

	protected function removePrintOutputAction() {
		remove_filter('redirect_canonical', '__return_false');
		remove_action('wp', [$this, 'includeTemplateLoader'], 99);
		restore($this->loadTemplateHandle);
	}

	public function includeTemplateLoader() {
		remove_action('wp', [$this, 'includeTemplateLoader'], 99);

		$templateLoaderPath = $this->wpLoader->_getConfig('wpRootFolder') . '/wp-includes/template-loader.php';
		if (!defined('WP_USE_THEMES')) {
			define('WP_USE_THEMES', true);
		}

		include $templateLoaderPath;
	}
}
