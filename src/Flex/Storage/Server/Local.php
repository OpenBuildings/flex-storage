<?php

namespace Flex\Storage;

/**
 * Local 
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
	
	protected $_file_root;
	
	public function file_root($file_root = NULL)
	{
		if ($file_root !== NULL)
		{
			if ( ! is_dir($file_root))
				throw new Server_Exception('File root :file_root is not a directory', array(':file_root' => $file_root));

			$this->_file_root = rtrim($file_root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
			return $this;
		}
		return $this->_file_root;
	}

	protected $_web_root;
	
	public function web_root($web_root = NULL)
	{
		if ($web_root !== NULL)
		{
			if (filter_var($web_root, FILTER_VALIDATE_URL) === FALSE AND $web_root !== '/')
				throw new Server_Exception('Web root :web_root is not a valid url or "/"', array(':web_root' => $web_root));

			$this->_web_root = (string) $web_root;
			return $this;
		}
		return $this->_web_root;
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
			return rmdir($file);
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
			return move_uploaded_file($local_file, $file);
		}
		elseif (is_file($local_file))
		{
			return rename($local_file, $file);
		}
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
			throw new Server_Exception(":dir must be local directory", array(":dir" => dirname($local_file)));
			
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
	 * @param string|boolean $protocol
	 * @return string
	 * @author Ivan Kerin
	 **/	
	public function url($file)
	{
		$this->web_root().str_replace(DIRECTORY_SEPARATOR, '/', $file);
	}

	private function ensure_writable_directory($dir)
	{
		if ( ! $this->is_dir($dir))
		{
			if ( ! $this->mkdir($dir))
				throw new Server_Exception("Cannot create dir :dir (:realdir)", array(":dir" => $dir, ':realdir' => $this->realpath($dir)));
		}

		if ( ! $this->is_writable($dir))
			throw new Server_Exception('Directory :dir must be writable',
				array(':dir' => $dir));

		return $dir;
	}

}
