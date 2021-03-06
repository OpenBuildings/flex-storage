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
	/**
	 * The identity endpoint
	 */
	const IDENTITY = 'https://identity.api.rackspacecloud.com/v2.0/';

	/**
	 * Connect to rackspace and get the object store's container
	 * @param  string $container_name
	 * @param  string $region         LON, DFW ...
	 * @param  array  $options        options passed to constructor
	 * @return OpenCloud\ObjectStore\Container
	 */
	public static function connect($container_name, $region, array $options)
	{
		$conn = new \OpenCloud\Rackspace(Server_Rackspace::IDENTITY, $options);

		$object_store = $conn->ObjectStore('cloudFiles', $region, 'publicURL');

		return $object_store->Container($container_name);
	}

	protected $_container_name;
	protected $_region;
	protected $_options;
	protected $_container;
	protected $_url_type = Server::URL_HTTP;

	protected $_cdn_uri;
	protected $_cdn_ssl;
	protected $_cdn_streaming;

	public function __construct($container_name = NULL, $region = NULL, array $options = array())
	{
		$this->_container_name = $container_name;
		$this->_region = $region;
		$this->_options = $options;
	}

	public function container($container = NULL)
	{
		if ( ! $this->_container)
		{
			$this->_container = Server_Rackspace::connect($this->_container_name, $this->_region, $this->_options);
		}

		return $this->_container;
	}

	public function url_type($url_type = NULL)
	{
		if ($url_type !== NULL)
		{
			$this->_url_type = (string) $url_type;
			return $this;
		}
		return $this->_url_type;
	}

	/**
	 * Getter / Setter of CDN URI.
	 * You can set it beforehand so that calling url method
	 * does not require authenticating to the Rackspace server
	 *
	 * @param  string $cdn_uri
	 * @return string|$this
	 */
	public function cdn_uri($cdn_uri = NULL)
	{
		if ($cdn_uri !== NULL)
		{
			$this->_cdn_uri = (string) $cdn_uri;
			return $this;
		}

		if ( ! $this->_cdn_uri)
		{
			$this->_cdn_uri = $this->container()->CDNURI();
		}

		return $this->_cdn_uri;
	}

	/**
	 * Getter / Setter of CDN URI for ssl.
	 * You can set it beforehand so that calling url method
	 * does not require authenticating to the Rackspace server
	 *
	 * @param  string $cdn_ssl
	 * @return string|$this
	 */
	public function cdn_ssl($cdn_ssl = NULL)
	{
		if ($cdn_ssl !== NULL)
		{
			$this->_cdn_ssl = (string) $cdn_ssl;
			return $this;
		}

		if ( ! $this->_cdn_ssl)
		{
			$this->_cdn_ssl = $this->container()->SSLURI();
		}

		return $this->_cdn_ssl;
	}

	/**
	 * Getter / Setter of CDN URI for streaming.
	 * You can set it beforehand so that calling url method
	 * does not require authenticating to the Rackspace server
	 *
	 * @param  string $cdn_streaming
	 * @return string|$this
	 */
	public function cdn_streaming($cdn_streaming = NULL)
	{
		if ($cdn_streaming !== NULL)
		{
			$this->_cdn_streaming = (string) $cdn_streaming;
			return $this;
		}

		if ( ! $this->_cdn_streaming)
		{
			$this->_cdn_streaming = $this->container()->StreamingURI();
		}

		return $this->_cdn_streaming;
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
		throw new Exception_Notsupported('Rackspace server does support directories'); // @codeCoverageIgnore
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
			$new_object = $this->container()->DataObject();

			$new_object->name = $new_file;
			$new_object->content_type = $object->content_type;
			$new_object->extra_headers = $object->extra_headers;

			$object->Copy($new_object);

			return TRUE;
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

		$this->container()->DataObject($file)->SaveToFilename($local_file);
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
		$object->SaveToFilename($local_file);
		$object->Delete();
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
		$object->Create();
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
	public function url($file, $url_type = NULL)
	{
		$url_type = $url_type ?: $this->url_type();

		$file = implode('/', array_map('rawurlencode', explode('/', $file)));

		switch ($url_type)
		{
			case Server::URL_SSL:
				$full = $this->cdn_ssl().'/'.$file;
			break;

			case Server::URL_STREAMING:
				$full = $this->cdn_streaming().'/'.$file;
			break;

			case Server::URL_HTTP:
				$full = $this->cdn_uri().'/'.$file;
			break;
		}

		return $full;
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
