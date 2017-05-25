<?php
namespace Akeeba\Compatibility\Admin\Toolbar;


class Toolbar extends \FOF30\Toolbar\Toolbar
{
	public function onControlPanelsShow()
	{
		$this->onCpanelsBrowse();
	}
}