<?php

/**
 * @package flex-storage
 * @group   flex-storage.server
 * @group   flex-storage.server.local
 */
class Server_LocalTest extends PHPUnit_Framework_TestCase {

	public $server;
	public $dir;

	public function setUp()
	{
		$this->dir = __DIR__.'/../../data/';
		$this->server = new Flex\Storage\Server_Local($this->dir, 'http://example.com');
		parent::setUp();
	}

	public function test_file_root()
	{
		$this->assertEquals($this->dir, $this->server->file_root());

		$this->server->file_root($this->dir.'testdir');
		$this->assertEquals($this->dir.'testdir/', $this->server->file_root());

		$this->setExpectedException('Flex\Storage\Exception');
		$this->server->file_root('not valid directory');
	}

	public function test_web_root()
	{
		$this->assertEquals('http://example.com/', $this->server->web_root());

		$this->server->web_root('http://test.example.com');
		$this->assertEquals('http://test.example.com/', $this->server->web_root());

		$this->setExpectedException('Flex\Storage\Exception');
		$this->server->web_root('notvalidurl/test.example.com');
	}

	public function test_file_exists()
	{
		$this->assertTrue($this->server->file_exists('test.txt'));
		$this->assertFalse($this->server->file_exists('test_notexists.txt'));
	}

	public function test_is_file()
	{
		$this->assertTrue($this->server->is_file('test.txt'));
		$this->assertFalse($this->server->is_file('test_notexists.txt'));
		$this->assertFalse($this->server->is_file('testdir'));
	}

	public function test_is_dir()
	{
		$this->assertFalse($this->server->is_dir('test.txt'));
		$this->assertFalse($this->server->is_dir('test_notexists.txt'));
		$this->assertTrue($this->server->is_dir('testdir'));
	}

	public function test_unlink()
	{
		file_put_contents($this->dir.'test2.txt', 'test');
		$this->assertFileExists($this->dir.'test2.txt');

		$this->assertTrue($this->server->unlink('test2.txt'));
		$this->assertFileNotExists($this->dir.'test2.txt');

		mkdir($this->dir.'testdir2');
		$this->assertFileExists($this->dir.'testdir2');

		$this->assertTrue($this->server->unlink('testdir2'));
		$this->assertFileNotExists($this->dir.'testdir2');
	}

	public function test_mkdir()
	{
		$this->assertFileNotExists($this->dir.'testdir3/test3');	
		$this->server->mkdir('testdir3/test3');
		$this->assertFileExists($this->dir.'testdir3/test3');	

		rmdir($this->dir.'testdir3/test3');
		rmdir($this->dir.'testdir3');
	}

	public function test_rename()
	{
		file_put_contents($this->dir.'test3.txt', 'test_rename');

		$this->assertFileExists($this->dir.'test3.txt');
		$this->assertFileNotExists($this->dir.'test3_renamed.txt');

		$this->server->rename('test3.txt', 'test3_renamed.txt');

		$this->assertFileNotExists($this->dir.'test3.txt');
		$this->assertFileExists($this->dir.'test3_renamed.txt');
		$this->assertEquals('test_rename', file_get_contents($this->dir.'test3_renamed.txt'));

		unlink($this->dir.'test3_renamed.txt');
	}

	public function test_copy()
	{
		$this->server->copy('test.txt', 'test4_copy.txt');
		$this->assertFileEquals($this->dir.'test.txt', $this->dir.'test4_copy.txt');

		unlink($this->dir.'test4_copy.txt');
	}

	public function test_upload()
	{
		file_put_contents($this->dir.'test5.txt', 'local');
		$this->server->upload('test6_uploaded.txt', $this->dir.'test5.txt');
		$this->assertFileExists($this->dir.'test6_uploaded.txt');
		$this->assertFileExists($this->dir.'test5.txt');
		$this->assertFileEquals($this->dir.'test5.txt', $this->dir.'test6_uploaded.txt');

		unlink($this->dir.'test5.txt');
		unlink($this->dir.'test6_uploaded.txt');
	}

	public function test_upload_move()
	{
		file_put_contents($this->dir.'test7.txt', 'local');
		$this->server->upload_move('test8_uploaded.txt', $this->dir.'test7.txt');
		$this->assertFileExists($this->dir.'test8_uploaded.txt');
		$this->assertFileNotExists($this->dir.'test7.txt');

		$this->assertEquals('local', file_get_contents($this->dir.'test8_uploaded.txt'));
		unlink($this->dir.'test8_uploaded.txt');
	}

	public function test_download()
	{
		$this->server->download('test.txt', $this->dir.'test9_downloaded.txt');
		$this->assertFileExists($this->dir.'test.txt');
		$this->assertFileExists($this->dir.'test9_downloaded.txt');
		$this->assertFileEquals($this->dir.'test.txt', $this->dir.'test9_downloaded.txt');

		unlink($this->dir.'test9_downloaded.txt');
	}

	public function test_download_move()
	{
		file_put_contents($this->dir.'test10.txt', 'server');
		$this->server->download_move('test10.txt', $this->dir.'test11_downloaded.txt');
		$this->assertFileExists($this->dir.'test11_downloaded.txt');
		$this->assertFileNotExists($this->dir.'test10.txt');

		$this->assertEquals('server', file_get_contents($this->dir.'test11_downloaded.txt'));
		unlink($this->dir.'test11_downloaded.txt');
	}

	public function test_file_get_contents()
	{
		$this->assertEquals(file_get_contents($this->dir.'test.txt'), $this->server->file_get_contents('test.txt'));
	}

	public function test_file_put_contents()
	{
		$this->server->file_put_contents('test12.txt', 'test12');
		$this->assertFileExists($this->dir.'test12.txt');
		$this->assertEquals('test12', file_get_contents($this->dir.'test12.txt'));

		unlink($this->dir.'test12.txt');
	}

	public function test_is_writable()
	{
		$this->assertTrue($this->server->is_writable('testdir'));
		$this->assertFalse($this->server->is_writable('testdir_notexists'));
	}

	public function test_realpath()
	{
		$this->assertEquals($this->dir.'test.txt', $this->server->realpath('test.txt'));
	}

	public function test_url()
	{
		$this->assertEquals('http://example.com/test.txt', $this->server->url('test.txt'));	
		$this->assertEquals('https://example.com/test.txt', $this->server->url('test.txt', Flex\Storage\Server::URL_SSL));	

		$this->server->url_type(Flex\Storage\Server::URL_SSL);

		$this->assertEquals('https://example.com/test.txt', $this->server->url('test.txt'));	
		$this->assertEquals('http://example.com/test.txt', $this->server->url('test.txt', Flex\Storage\Server::URL_HTTP));
	}
}