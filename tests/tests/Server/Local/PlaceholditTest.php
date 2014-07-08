<?php

/**
 * @package flex-storage
 * @group   flex-storage.server
 * @group   flex-storage.server.placeholdit
 */
class Server_Local_PlaceholditTest extends PHPUnit_Framework_TestCase {

	public static $rackspace_server;
	public $dir;


	public function setUp()
	{
		$this->dir = __DIR__.'/../../../data/';
		$this->server = new Flex\Storage\Server_Local_Placeholdit($this->dir, 'http://example.com');
		$this->server->placeholder('http://example.com/placeholder.jpg');

		parent::setUp();
	}

	public function test_url()
	{
		$this->assertEquals('http://example.com/test.txt', $this->server->url('test.txt'));
		$this->assertEquals('https://example.com/test.txt', $this->server->url('test.txt', Flex\Storage\Server::URL_SSL));

		$this->assertEquals('http://example.com/placeholder.jpg', $this->server->url('test_not_exists.txt'));
		$this->assertEquals('http://example.com/placeholder.jpg', $this->server->url('test_not_exists.txt', Flex\Storage\Server::URL_SSL));
		$this->assertEquals('http://example.com/placeholder.jpg', $this->server->url('test_not_exists.txt', Flex\Storage\Server::URL_STREAMING));
	}
}
