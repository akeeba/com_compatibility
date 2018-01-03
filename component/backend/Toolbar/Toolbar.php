<?php
/**
 * @package		com_compatibility
 * @copyright	Copyright (c)2017-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */

namespace Akeeba\Compatibility\Admin\Toolbar;

class Toolbar extends \FOF30\Toolbar\Toolbar
{
	public function onControlPanelsShow()
	{
		$this->onCpanelsBrowse();
	}
}
