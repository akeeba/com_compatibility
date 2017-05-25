<?php
namespace Akeeba\Compatibility\Site\Controller;


use FOF30\Container\Container;
use FOF30\Controller\Controller;
use FOF30\Controller\Mixin\PredefinedTaskList;

class Compatibility extends Controller
{
	use PredefinedTaskList;

	public function __construct(Container $container, array $config = array())
	{
		parent::__construct($container, $config);

		$this->predefinedTaskList = ['browse'];
	}

	public function browse($cache = true)
	{
		$this->display($cache);
	}
}