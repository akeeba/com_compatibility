<?php
/**
 * @package		com_compatibility
 * @copyright	Copyright (c)2017-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */

namespace Akeeba\Compatibility\Site\Controller;

use FOF40\Container\Container;
use FOF40\Controller\Controller;
use FOF40\Controller\Mixin\PredefinedTaskList;

// Protect from unauthorized access
defined('_JEXEC') or die();

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
