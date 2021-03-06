<?php

namespace Flex\Storage;

/**
 * Local Store
 *
 * @author     Ivan Kerin
 * @copyright  (c) 2011-2013 Despark Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class Server_Local implements Server
{
	public function __construct($file_root, $web_root = NULL)
	{
		$this->file_root($file_root);
		$this->web_root($web_root);
	}

	public static function recursive_rmdir($dir)
	{
		$contents = array_diff(scandir($dir), array('.','..'));

		foreach ($contents as $item)
		{
			$item = $dir.DIRECTORY_SEPARATOR.$item;
			if (is_dir($item))
			{
				self::recursive_rmdir($item);
			}
			else
			{
				unlink($item);
			}
		}

		return rmdir($dir);
	}

	protected $_file_root;
	protected $_web_root;
	protected $_url_type = Server::URL_HTTP;

	public function file_root($file_root = NULL)
	{
		if ($file_root !== NULL)
		{
			if ( ! is_dir($file_root))
				throw new Exception_IO('File root :file_root is not a directory', array(':file_root' => $file_root));

			$this->_file_root = rtrim($file_root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
			return $this;
		}
		return $this->_file_root;
	}

	public function web_root($web_root = NULL)
	{
		if ($web_root !== NULL)
		{
			if (filter_var($web_root, FILTER_VALIDATE_URL) === FALSE AND ! preg_match('/^(\/[a-zA-Z0-9]+)+$/', $web_root))
				throw new Exception('Web root :web_root is not a valid absolute or relative url', array(':web_root' => $web_root));

			$this->_web_root = rtrim((string) $web_root, '/').'/';
			return $this;
		}
		return $this->_web_root;
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
	 * Check if the file actually exists
	 *
	 * @param string $file
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function file_exists($file)
	{
		return file_exists($this->realpath($file));
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
		return is_file($this->realpath($file));
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
		return is_dir($this->realpath($file));
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
		$file = $this->realpath($file);

		if (is_file($file))
		{
			return unlink($file);
		}
		elseif (is_dir($file))
		{
			return self::recursive_rmdir($file);
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
		return mkdir($this->realpath($file), 0777, TRUE);
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
		return rename($this->realpath($file), $this->realpath($new_file));
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
		return copy($this->realpath($file), $this->realpath($new_file));
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
		if ( ! is_file($local_file))
			throw new Exception_IO(":file must be local file", array(":file" => $local_file));

		$dir = dirname($file);
		$this->ensure_writable_directory($dir);
		$file = $this->realpath($file);

		return copy($local_file, $file);
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
		$dir = dirname($file);
		$this->ensure_writable_directory($dir);
		$file = $this->realpath($file);

		if (is_uploaded_file($local_file))
		{
			return move_uploaded_file($local_file, $file); // @codeCoverageIgnore
		}
		elseif (is_file($local_file))
		{
			return rename($local_file, $file);
		}

		throw new Exception_IO(":file must be local file", array(":file" => $local_file));
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
			throw new Exception_IO(":dir must be local directory", array(":dir" => dirname($local_file)));

		return copy($this->realpath($file), $local_file);
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
			throw new Exception_IO(':dir must be local directory', array(':dir' => dirname($local_file)));

		return rename($this->realpath($file), $local_file);
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
		return file_get_contents($this->realpath($file));
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
		$dir = dirname($file);
		$this->ensure_writable_directory($dir);

		return file_put_contents($this->realpath($file), $content) !== FALSE;
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
		return is_writable($this->realpath($file));
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
		return $this->file_root().$file;
	}

	/**
	 * Return a publicly accessable location of a file
	 *
	 * @param string $file
	 * @param string $url_type, one of Server::URL_HTTP, Server::URL_SSL, Server::URL_STREAMING
	 * @return string
	 * @author Ivan Kerin
	 **/
	public function url($file, $url_type = NULL)
	{
		$root = $this->web_root();

		$url_type = $url_type ?: $this->url_type();

		if ($url_type == Server::URL_SSL)
		{
			$root = str_replace('http://', 'https://', $this->web_root());
		}

		$file = implode('/', array_map('rawurlencode', explode('/', $file)));

		return $root.str_replace(DIRECTORY_SEPARATOR, '/', $file);
	}

	private function ensure_writable_directory($dir)
	{
		if ( ! $this->is_dir($dir))
		{
			if ( ! $this->mkdir($dir))
				throw new Exception_IO('Cannot create dir :dir (:realdir)', array(':dir' => $dir, ':realdir' => $this->realpath($dir)));
		}

		if ( ! $this->is_writable($dir))
			throw new Exception_IO('Directory :dir must be writable',	array(':dir' => $dir)); // @codeCoverageIgnore

		return $dir;
	}

}
