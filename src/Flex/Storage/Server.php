<?php

namespace Flex\Storage;

/**
 * Class for manupulating a server
 *
 * @author     Ivan Kerin
 * @copyright  (c) 2011-2013 Despark Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
interface Server
{
	const URL_HTTP = 'HTTP';
	const URL_SSL = 'SSL';
	const URL_STREAMING = 'STREAMING';

	/**
	 * Check if the file actually exists
	 *
	 * @param string $file
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function file_exists($file);

	/**
	 * Check if the file actually exists and is a file
	 *
	 * @param string $file
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function is_file($file);

	/**
	 * Check if the file actually exists and is a directory
	 *
	 * @param string $file
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function is_dir($file);

	/**
	 * Delete the specified file
	 *
	 * @param string $file
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function unlink($file);

	/**
	 * Create a directory
	 *
	 * @param string $file
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function mkdir($dir_name);

	/**
	 * Move a file to the destination
	 *
	 * @param string $file
	 * @param string $new_file
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function rename($file, $new_file);

	/**
	 * Copy a file to the destination
	 *
	 * @param string $file
	 * @param string $new_file
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function copy($file, $new_file);

	/**
	 * Copy a local file to the server
	 *
	 * @param string $file
	 * @param string $local_file
	 * @param bool $remove_file	defaults to true
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function upload($file, $local_file);

	/**
	 * Move the file from the local path to the server (move it)
	 *
	 * @param string $file
	 * @param string $local_file
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function upload_move($file, $local_file);

	/**
	 * Copy a file from the server to a local file
	 *
	 * @param string $file
	 * @param string $local_file
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function download($file, $local_file);

	/**
	 * Move the file from the server to a local path (move it)
	 *
	 * @param string $file
	 * @param string $local_file
	 * @return bool
	 * @author Ivan Kerin
	 **/
	public function download_move($file, $local_file);

	/**
	 * Return file contents
	 *
	 * @param string $file
	 * @return string
	 * @author Ivan Kerin
	 **/
	public function file_get_contents($file);

	/**
	 * Write contents to a file
	 *
	 * @param string $file
	 * @param string $content
	 * @return string
	 * @author Ivan Kerin
	 **/
	public function file_put_contents($file, $content);

	/**
	 * Check if the file is writable
	 *
	 * @param string $file
	 * @param string $content
	 * @return string
	 * @author Ivan Kerin
	 **/	
	public function is_writable($file);

	/**
	 * Return the local file path, used by local filesystems
	 *
	 * @param string $file
	 * @return string
	 * @author Ivan Kerin
	 **/	
	public function realpath($file);

	/**
	 * Return a publicly accessable location of a file
	 *
	 * @param string $file
	 * @param string $type, one of Server::URL_HTTP, Server::URL_SSL, Server::URL_STREAMING
	 * @return string
	 * @author Ivan Kerin
	 **/	
	public function url($file, $type = NULL);
}
