<?php

namespace Flex\Storage;

/**
 * Local 
 *
 * @author     Ivan Kerin
 * @copyright  (c) 2011-2013 Despark Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class Server_Local_Fallback extends Server_Local
{
	protected $_fallback;
	
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
	 * Return a publicly accessable location of a file
	 *
	 * @param string $file
	 * @param string $type, one of Server::URL_HTTP, Server::URL_SSL, Server::URL_STREAMING
	 * @return string
	 * @author Ivan Kerin
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
