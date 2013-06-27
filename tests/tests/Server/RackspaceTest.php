<?php

/**
 * @package flex-storage
 * @group   flex-storage.server
 * @group   flex-storage.server.rackspace
 */
class Server_RackspaceTest extends PHPUnit_Framework_TestCase {

	public static $server;
	public $dir;

	public static function setUpBeforeClass()
	{
		if ( ! isset($_SERVER['PHP_RCLOUD_USER']) OR ! isset($_SERVER['PHP_RCLOUD_API_KEY']))
			throw new Exception('PHP_RCLOUD_API_KEY and PHP_RCLOUD_USER must be set as environment variables');
			
		self::$server = new Flex\Storage\Server_Rackspace('flex_storage_test', 'LON', array(
			'username' => $_SERVER['PHP_RCLOUD_USER'],
			'apiKey' => $_SERVER['PHP_RCLOUD_API_KEY'],
		));
	}

	public function setUp()
	{
		$this->dir = __DIR__.'/../../data/';
		parent::setUp();
	}

	public function test_file_exists()
	{
		$this->assertTrue(self::$server->file_exists('test.txt'));
		$this->assertFalse(self::$server->file_exists('test_notexists.txt'));
	}

	public function test_is_file()
	{
		$this->assertTrue(self::$server->is_file('test.txt'));
		$this->assertFalse(self::$server->is_file('test_notexists.txt'));
	}

	public function is_dir()
	{
		$this->setExpectedException('Flex\Storage\Exception_Notsupported');
		self::$server->is_dir('testdir3/test3');
	}

	public function test_unlink()
	{
		self::file_put_contents('test2.txt', 'test');
		$this->assertRackspaceFileExists('test2.txt');

		$this->assertTrue(self::$server->unlink('test2.txt'));
		$this->assertRackspaceFileNotExists('test2.txt');
	}

	public function test_mkdir()
	{
		$this->setExpectedException('Flex\Storage\Exception_Notsupported');
		self::$server->mkdir('testdir3/test3');
	}

	public function test_rename()
	{
		self::file_put_contents('test3.txt', 'test_rename');

		$this->assertRackspaceFileExists('test3.txt');
		$this->assertRackspaceFileNotExists('test3_renamed.txt');

		self::$server->rename('test3.txt', 'test3_renamed.txt');

		$this->assertRackspaceFileNotExists('test3.txt');
		$this->assertRackspaceFileExists('test3_renamed.txt');
		$this->assertEquals('test_rename', self::file_get_contents('test3_renamed.txt'));

		self::unlink('test3_renamed.txt');
	}

	public function test_copy()
	{
		self::$server->copy('test.txt', 'test4_copy.txt');
		$this->assertRackspaceFileEquals('test.txt', 'test4_copy.txt');

		self::unlink('test4_copy.txt');
	}

	public function test_upload()
	{
		file_put_contents($this->dir.'test5.txt', 'local');
		self::$server->upload('test6_uploaded.txt', $this->dir.'test5.txt');
		$this->assertRackspaceFileExists('test6_uploaded.txt');
		$this->assertFileExists($this->dir.'test5.txt');
		$this->assertEquals(file_get_contents($this->dir.'test5.txt'), self::file_get_contents('test6_uploaded.txt'));

		unlink($this->dir.'test5.txt');
		self::unlink('test6_uploaded.txt');
	}

	public function test_upload_move()
	{
		file_put_contents($this->dir.'test7.txt', 'local');
		self::$server->upload_move('test8_uploaded.txt', $this->dir.'test7.txt');
		$this->assertRackspaceFileExists('test8_uploaded.txt');
		$this->assertFileNotExists($this->dir.'test7.txt');

		$this->assertEquals('local', self::file_get_contents('test8_uploaded.txt'));
		self::unlink('test8_uploaded.txt');
	}

	public function test_download()
	{
		self::$server->download('test.txt', $this->dir.'test9_downloaded.txt');
		$this->assertRackspaceFileExists('test.txt');
		$this->assertFileExists($this->dir.'test9_downloaded.txt');
		$this->assertEquals(self::file_get_contents('test.txt'), file_get_contents($this->dir.'test9_downloaded.txt'));

		unlink($this->dir.'test9_downloaded.txt');
	}

	public function test_download_move()
	{
		self::file_put_contents('test10.txt', 'server');
		self::$server->download_move('test10.txt', $this->dir.'test11_downloaded.txt');
		$this->assertFileExists($this->dir.'test11_downloaded.txt');
		$this->assertRackspaceFileNotExists('test10.txt');

		$this->assertEquals('server', file_get_contents($this->dir.'test11_downloaded.txt'));
		unlink($this->dir.'test11_downloaded.txt');
	}

	public function test_file_get_contents()
	{
		$this->assertEquals(self::file_get_contents('test.txt'), self::$server->file_get_contents('test.txt'));
	}

	public function test_file_put_contents()
	{
		self::$server->file_put_contents('test12.txt', 'test12');
		$this->assertRackspaceFileExists('test12.txt');
		$this->assertEquals('test12', self::file_get_contents('test12.txt'));

		self::unlink('test12.txt');
	}

	public function test_is_writable()
	{
		$this->assertTrue(self::$server->is_writable('testdir'));
	}

	public function test_realpath()
	{
		$this->setExpectedException('Flex\Storage\Exception_Notsupported');
		self::$server->realpath('test.txt');
	}

	public function test_url()
	{
		$this->assertEquals('http://7b286e6f63f2a7f84847-0cd42b8dee15b5017160a1d30c7ce549.r33.cf3.rackcdn.com/test.txt', self::$server->url('test.txt'));

		$this->assertEquals('https://3533bfdb7f646acec3be-0cd42b8dee15b5017160a1d30c7ce549.ssl.cf3.rackcdn.com/test.txt', self::$server->url('test.txt', Flex\Storage\Server::URL_SSL));			

		$this->assertEquals('http://90bef8afdf3b9a686f39-0cd42b8dee15b5017160a1d30c7ce549.r33.stream.cf3.rackcdn.com/test.txt', self::$server->url('test.txt', Flex\Storage\Server::URL_STREAMING));	
	}
	
	private static function unlink($name)
	{
		self::$server->container()->DataObject($name)->Delete();
	}
		
	private static function file_put_contents($name, $content)
	{
		$file = self::$server->container()->DataObject();
		$file->SetData($content);
		$file->name = $name;
		$file->content_type = 'text/plain';
		$file->Create();
	}

	private static function file_get_contents($name)
	{
		return self::$server->container()->DataObject($name)->SaveToString();
	}
	
	public static function assertRackspaceFileEquals($file1, $file2)
	{
		$content1 = self::$server->container()->DataObject($file1)->SaveToString();
		$content2 = self::$server->container()->DataObject($file2)->SaveToString();
		self::assertEquals($content1, $content2, 'Failed asserting that file '.$file1.' equals '.$file2);
	}
	
	public static function assertRackspaceFileExists($file)
	{
		try 
		{
			self::$server->container()->DataObject($file);
		} 
		catch (OpenCloud\Common\Exceptions\ObjFetchError $exception) 
		{
			self::fail('Filed asserting that file '.$file.' exists on rackspace');
		}
	}

	public static function assertRackspaceFileNotExists($file)
	{
		try 
		{
			self::$server->container()->DataObject($file);
			self::fail('Filed asserting that file '.$file.' does not exist on rackspace');
		} 
		catch (OpenCloud\Common\Exceptions\ObjFetchError $exception) 
		{
			
		}
	}

}