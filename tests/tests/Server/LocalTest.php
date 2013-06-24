<?php

/**
 * @package flex-storage
 * @group   flex-storage.server
 * @group   flex-storage.server.local
 */
class Server_LocalTest extends PHPUnit_Framework_TestCase {

	public $server;

	public function setUp()
	{
		$this->server = new Flex\Storage\Server_Local(__DIR__.'/../../data', 'http://example.com');
	}

	public function test_file_root()
	{
		$this->assertEquals(__DIR__.'/../../data/', $this->server->file_root());

		$this->server->file_root(__DIR__.'/../../data/testdir');
		$this->assertEquals(__DIR__.'/../../data/testdir/', $this->server->file_root());

		$this->setExpectedException('Flex\Storage\Server_Exception');
		$this->server->file_root('not valid directory');
	}

	public function test_web_root()
	{
		$this->assertEquals('http://example.com', $this->server->web_root());

		$this->server->web_root('http://test.example.com');
		$this->assertEquals('http://test.example.com', $this->server->web_root());

		$this->setExpectedException('Flex\Storage\Server_Exception');
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
		file_put_contents(__DIR__.'/../../data/test2.txt', 'test');
		$this->assertFileExists(__DIR__.'/../../data/test2.txt');

		$this->assertTrue($this->server->unlink('test2.txt'));
		$this->assertFileNotExists(__DIR__.'/../../data/test2.txt');

		mkdir(__DIR__.'/../../data/testdir2');
		$this->assertFileExists(__DIR__.'/../../data/testdir2');

		$this->assertTrue($this->server->unlink('testdir2'));
		$this->assertFileNotExists(__DIR__.'/../../data/testdir2');
	}

	public function test_mkdir()
	{
		$this->assertFileNotExists(__DIR__.'/../../data/testdir3/test3');	
		$this->server->mkdir('testdir3/test3');
		$this->assertFileExists(__DIR__.'/../../data/testdir3/test3');	

		rmdir(__DIR__.'/../../data/testdir3/test3');
		rmdir(__DIR__.'/../../data/testdir3');
	}

	public function test_rename()
	{
		file_put_contents(__DIR__.'/../../data/test3.txt', 'test_rename');
		if (is_file(__DIR__.'/../../data/test3_renamed.txt'))
		{
			unlink(__DIR__.'/../../data/test3_renamed.txt');
		}

		$this->assertFileExists(__DIR__.'/../../data/test3.txt');
		$this->assertFileNotExists(__DIR__.'/../../data/test3_renamed.txt');

		$this->server->rename('test3.txt', 'test3_renamed.txt');

		$this->assertFileNotExists(__DIR__.'/../../data/test3.txt');
		$this->assertFileExists(__DIR__.'/../../data/test3_renamed.txt');
		$this->assertEquals('test_rename', file_get_contents(__DIR__.'/../../data/test3_renamed.txt'));

		unlink(__DIR__.'/../../data/test3_renamed.txt');
	}

	public function test_copy()
	{
		if (is_file(__DIR__.'/../../data/test4_copy.txt'))
		{
			unlink(__DIR__.'/../../data/test4_copy.txt');
		}

		$this->server->copy('test.txt', 'test4_copy.txt');
		$this->assertFileEquals(__DIR__.'/../../data/test.txt', __DIR__.'/../../data/test4_copy.txt');

		unlink(__DIR__.'/../../data/test4_copy.txt');
	}


}