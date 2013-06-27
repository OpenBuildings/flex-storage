<?php

namespace Flex\Storage;

/**
 * A special Server that allows returning urls from a different server if they do not exist locally.
 * This is very useful for shoing production assets on test/dev/staging 
 *
 * @author     Ivan Kerin
 * @copyright  (c) 2011-2013 Despark Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class Server_Local_Fallback extends Server_Local
{
	/**
	 * The fallback server. If not set this service works like Server_Local
	 * @var Server
	 */
	protected $_fallback;
	
	/**
	 * Getter / Setter for fallback server.
	 * @param  Server $fallback 
	 * @return Server|$this           
	 */
	public function fallback(Server $fallback = NULL)
	{
		if ($fallback !== NULL)
		{
			$this->_fallback = $fallback;
			return $this;
		}
		return $this->_fallback;
	}

	/**
	 * Return a publicly accessable location of a file.
	 * If not present, try shoing from fallback server
	 *
	 * @param string $file
	 * @param string $type, one of Server::URL_HTTP, Server::URL_SSL, Server::URL_STREAMING
	 * @return string
	 **/	
	public function url($file, $type = NULL)
	{
		if ( ! $this->is_file($file) AND $this->fallback())
		{
			return $this->fallback()->url($file, $type);
		}
		return parent::url($file, $type);
	}
}
