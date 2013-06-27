<?php

/**
 * @package flex-storage
 * @group   flex-storage.server
 * @group   flex-storage.server.fallback
 */
class Server_Local_FallbackTest extends PHPUnit_Framework_TestCase {

	public static $rackspace_server;
	public $dir;

	public static function setUpBeforeClass()
	{
		if ( ! isset($_SERVER['PHP_RCLOUD_USER']) OR ! isset($_SERVER['PHP_RCLOUD_API_KEY']))
			throw new Exception('PHP_RCLOUD_API_KEY and PHP_RCLOUD_USER must be set as environment variables');
			
		self::$rackspace_server = new Flex\Storage\Server_Rackspace('flex_storage_test', 'LON', array(
			'username' => $_SERVER['PHP_RCLOUD_USER'],
			'apiKey' => $_SERVER['PHP_RCLOUD_API_KEY'],
		));
	}

	public function setUp()
	{
		$this->dir = __DIR__.'/../../../data/';
		$this->server = new Flex\Storage\Server_Local_Fallback($this->dir, 'http://example.com');
		$this->server->fallback(self::$rackspace_server);

		parent::setUp();
	}

	public function test_url()
	{
		$this->assertEquals('http://example.com/test.txt', $this->server->url('test.txt'));	
		$this->assertEquals('https://example.com/test.txt', $this->server->url('test.txt', Flex\Storage\Server::URL_SSL));	

		$file = self::$rackspace_server->container()->DataObject();
		$file->SetData('test');
		$file->Create(array('name' => 'test_not_exists.txt', 'content_type' => 'text/plain'));

		$this->assertEquals('http://7b286e6f63f2a7f84847-0cd42b8dee15b5017160a1d30c7ce549.r33.cf3.rackcdn.com/test_not_exists.txt', $this->server->url('test_not_exists.txt'));

		$this->assertEquals('https://3533bfdb7f646acec3be-0cd42b8dee15b5017160a1d30c7ce549.ssl.cf3.rackcdn.com/test_not_exists.txt', $this->server->url('test_not_exists.txt', Flex\Storage\Server::URL_SSL));

		$this->assertEquals('http://90bef8afdf3b9a686f39-0cd42b8dee15b5017160a1d30c7ce549.r33.stream.cf3.rackcdn.com/test_not_exists.txt', $this->server->url('test_not_exists.txt', Flex\Storage\Server::URL_STREAMING));

		self::$rackspace_server->container()->DataObject('test_not_exists.txt')->Delete();
	}
}