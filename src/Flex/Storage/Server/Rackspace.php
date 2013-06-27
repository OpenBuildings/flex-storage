<?php

namespace Flex\Storage;

/**
 * Rackspace server
 *
 * @author     Ivan Kerin
 * @copyright  (c) 2011-2013 Despark Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class Server_Rackspace implements Server
{
	const IDENTITY = 'https://identity.api.rackspacecloud.com/v2.0/';

	public function __construct($container = NULL, $region = NULL, array $options = array())
	{
		if ($container AND $region)
		{
			$this->connect($container, $region, $options);
		}
	}

	protected $_container;

	public function connect($container, $region, array $options)
	{
		$conn = new \OpenCloud\Rackspace(Server_Rackspace::IDENTITY, $options);

		$object_store = $conn->ObjectStore('cloudFiles', $region, 'publicURL');

		$this->_container = $object_store->Container($container);

		return $this;
	}

	public function container($container = NULL)
	{
		if ( ! $this->_container)
			throw new Server_Exception('Not yet connected');
			
		return $this->_container;
	}

	/**
	 * Check if the file actually exists
	 *
	 * @param string $file
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function file_exists($file)
	{
		return (bool) $this->object($file);
	}


	/**
	 * Check if the file actually exists and is a file
	 *
	 * @param string $file
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function is_file($file)
	{
		return $this->file_exists($file);
	}

	/**
	 * Check if the file actually exists and is a directory
	 *
	 * @param string $file
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function is_dir($file)
	{
		throw new Exception_Notsupported('Rackspace server does support directories');
	}


	/**
	 * Delete the specified file
	 *
	 * @param string $file
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function unlink($file)
	{
		if ($object = $this->object($file))
		{
			return (bool) $object->Delete();
		}
	}

	/**
	 * Create a directory
	 *
	 * @param string $file
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function mkdir($file)
	{
		throw new Exception_Notsupported('Rackspace server does not support directories');
	}

	/**
	 * Move a file to the destination
	 *
	 * @param string $file
	 * @param string $new_file
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function rename($file, $new_file)
	{
		if ($object = $this->object($file))
		{
			$new_object = $this->container()->DataObject();
			
			$new_object->name = $new_file;
			$new_object->content_type = $object->content_type;
			$new_object->extra_headers = $object->extra_headers;

			$object->Copy($new_object);
			$object->Delete();
			return TRUE;
		}
	}

	/**
	 * Copy a file to the destination
	 *
	 * @param string $file
	 * @param string $new_file
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function copy($file, $new_file)
	{
		if ($object = $this->object($file))
		{
			if ($object = $this->object($file))
			{
				$new_object = $this->container()->DataObject();
				
				$new_object->name = $new_file;
				$new_object->content_type = $object->content_type;
				$new_object->extra_headers = $object->extra_headers;

				$object->Copy($new_object);
				
				return TRUE;
			}
		}
	}

	/**
	 * Copy a local file to the server
	 *
	 * @param string $file
	 * @param string $local_file
	 * @param bool $remove_file	defaults to true
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function upload($file, $local_file)
	{
		$this->container()->DataObject()->Create(array('name' => $file), $local_file);
	}

	/**
	 * Move the file from the local path to the server (move it)
	 *
	 * @param string $file
	 * @param string $local_file
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function upload_move($file, $local_file)
	{
		$this->container()->DataObject()->Create(array('name' => $file), $local_file);

		return unlink($local_file);
	}

	/**
	 * Copy a file from the server to a local file
	 *
	 * @param string $file
	 * @param string $local_file
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function download($file, $local_file)
	{
		if ( ! is_dir(dirname($local_file)))
			throw new Server_Exception(":dir must be local directory", array(":dir" => dirname($local_file)));
			
		return (bool) $this->container()->DataObject($file)->SaveToFilename($local_file);
	}

	/**
	 * Move the file from the server to a local path (move it)
	 *
	 * @param string $file
	 * @param string $local_file
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function download_move($file, $local_file)
	{
		if ( ! is_dir(dirname($local_file)))
			throw new Server_Exception(":dir must be local directory", array(":dir" => dirname($local_file)));

		$object = $this->container()->DataObject($file);
		if ($object->SaveToFilename($local_file))
		{
			return (bool) $object->Delete();
		}
	}

	/**
	 * Return file contents
	 *
	 * @param string $file
	 * @return string
	 * @author Ivan Kerin
	 **/
	public function file_get_contents($file)
	{
		return $this->container()->DataObject($file)->saveToString();
	}

	/**
	 * Write contents to a file
	 *
	 * @param string $file
	 * @param string $content
	 * @return string
	 * @author Ivan Kerin
	 **/
	public function file_put_contents($file, $content)
	{
		$object = $this->container()->DataObject();
		$object->SetData($content);
		$object->name = $file;
		$object->content_type = 'text/plain';
		return (bool) $object->Create();
	}

	/**
	 * Check if the file is writable
	 *
	 * @param string $file
	 * @param string $content
	 * @return string
	 * @author Ivan Kerin
	 **/	
	public function is_writable($file)
	{
		return TRUE;
	}


	/**
	 * Return the local file path, used by local filesystems
	 *
	 * @param string $file
	 * @return string
	 * @author Ivan Kerin
	 **/	
	public function realpath($file)
	{
		throw new Exception_Notsupported('Rackspace server does not support local filenames');
	}

	/**
	 * Return a publicly accessable location of a file
	 *
	 * @param string $file
	 * @param string $type, one of Server::URL_HTTP, Server::URL_SSL, Server::URL_STREAMING
	 * @return string
	 * @author Ivan Kerin
	 **/	
	public function url($file, $type = NULL)
	{
		return $this->container()->DataObject($file)->PublicURL($type);
	}

	private function object($name)
	{
		try 
		{
			return $this->container()->DataObject($name);
		} 
		catch (\OpenCloud\Common\Exceptions\ObjFetchError $exception) 
		{
			return NULL;
		}
	}
}
