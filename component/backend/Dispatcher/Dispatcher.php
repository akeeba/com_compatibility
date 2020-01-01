<?php
/**
 * @package		com_compatibility
 * @copyright	Copyright (c)2017-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */

namespace Akeeba\Compatibility\Admin\Dispatcher;

use FOF30\Container\Container;
use FOF30\Dispatcher\Dispatcher as FOFDispatcher;

// Protect from unauthorized access
defined('_JEXEC') or die();

class Dispatcher extends FOFDispatcher
{
	public function __construct(Container $container, array $config = array())
	{
		$this->defaultView = 'ControlPanel';

		parent::__construct($container, $config);
	}

	public function onBeforeDispatch(): bool
	{
		// Renderer options (0=none, 1=frontend, 2=backend, 3=both)
		$useFEF   = in_array($this->container->params->get('load_fef', 3), [2, 3]);
		$fefReset = $useFEF && in_array($this->container->params->get('fef_reset', 3), [2, 3]);

		if (!$useFEF)
		{
			$this->container->rendererClass = '\\FOF30\\Render\\Joomla3';
		}

		$darkMode = $this->container->params->get('dark_mode_backend', -1);

		$this->container->renderer->setOptions([
			'load_fef'      => $useFEF,
			'fef_reset'     => $fefReset,
			'fef_dark'      => $useFEF ? $darkMode : 0,
			// Render submenus as drop-down navigation bars powered by Bootstrap
			'linkbar_style' => 'classic',
		]);

		return true;
	}

}
