<?php
namespace Akeeba\Compatibility\Admin\Controller;


use FOF30\Container\Container;
use FOF30\Controller\Controller;
use FOF30\Controller\Mixin\PredefinedTaskList;

class ControlPanel extends Controller
{
	use PredefinedTaskList;

	public function __construct(Container $container, array $config = array())
	{
		parent::__construct($container, $config);

		$this->setPredefinedTaskList(['controlpanel']);
	}

	public function controlpanel($cachable = false)
	{
		$this->display(true);
	}
}