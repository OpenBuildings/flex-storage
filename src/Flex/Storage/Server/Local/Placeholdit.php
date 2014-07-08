<?php

namespace Flex\Storage;

/**
 * A special Server that will return a placeholder url, if the image is not present locally.
 * By default uses http://placehold.it
 *
 * @author     Ivan Kerin
 * @copyright  (c) 2011-2013 Despark Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class Server_Local_Placeholdit extends Server_Local
{
	/**
	 * The placeholder url.
	 * @var string
	 */
	protected $_placeholder = 'http://placehold.it/250x150';

	/**
	 * Getter / Setter for placeholder image.
	 * @param  string       $placeholder
	 * @return string|$this
	 */
	public function placeholder($placeholder = NULL)
	{
		if ($placeholder !== NULL)
		{
			$this->_placeholder = $placeholder;
			return $this;
		}
		return $this->_placeholder;
	}

	/**
	 * Return a publicly accessable location of a file.
	 * If not present, will return the placeholder
	 *
	 * @param string $file
	 * @param string $type, one of Server::URL_HTTP, Server::URL_SSL, Server::URL_STREAMING
	 * @return string
	 **/
	public function url($file, $type = NULL)
	{
		if ( ! $this->is_file($file))
		{
			return $this->placeholder();
		}

		return parent::url($file, $type);
	}
}
