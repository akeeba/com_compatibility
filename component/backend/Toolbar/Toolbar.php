<?php
/**
 * @package		com_compatibility
 * @copyright	Copyright (c)2017-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */

namespace Akeeba\Compatibility\Admin\Toolbar;

// Protect from unauthorized access
defined('_JEXEC') or die();

class Toolbar extends \FOF30\Toolbar\Toolbar
{
	public function onControlPanelsShow()
	{
		$this->onCpanelsBrowse();
	}
}
