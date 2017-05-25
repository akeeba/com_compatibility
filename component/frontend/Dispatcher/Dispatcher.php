<?php
/**
 * @package     Akeeba\Compatibility\Site\Dispatcher
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
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