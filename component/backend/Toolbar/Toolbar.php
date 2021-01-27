<?php
/**
 * @package		com_compatibility
 * @copyright	Copyright (c)2017-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */

namespace Akeeba\Compatibility\Admin\Toolbar;

// Protect from unauthorized access
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper as JToolbarHelper;

defined('_JEXEC') or die();

class Toolbar extends \FOF40\Toolbar\Toolbar
{
	public function onControlPanelsShow()
	{
		$option = $this->container->componentName;

		JToolbarHelper::title(Text::_(strtoupper($option)), str_replace('com_', '', $option));

		if (!$this->isDataView())
		{
			return;
		}

		JToolbarHelper::preferences($option);

	}
}
