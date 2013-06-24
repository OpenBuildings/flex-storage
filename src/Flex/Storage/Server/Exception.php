<?php

namespace Flex\Storage;

/**
 * Local 
 *
 * @author     Ivan Kerin
 * @copyright  (c) 2011-2013 Despark Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class Server_Exception extends \Exception
{
	public function __construct($message, array $variables = array(), Exception $previous = NULL)
	{
		if ($variables)
		{
			$message = strtr($message, $variables);
		}

		parent::__construct($message, 0, $previous);
	}
}
