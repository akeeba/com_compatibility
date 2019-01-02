<?php
/**
 * @package		com_compatibility
 * @copyright	Copyright (c)2017-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */

namespace Akeeba\Compatibility\Site\Dispatcher;

use FOF30\Container\Container;
use FOF30\Dispatcher\Dispatcher as FOFDispatcher;

class Dispatcher extends FOFDispatcher
{
	public function __construct(Container $container, array $config = array())
	{
		$this->defaultView = 'Compatibility';

		parent::__construct($container, $config);
	}

}
