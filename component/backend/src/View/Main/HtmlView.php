<?php
/**
 * @package        com_compatibility
 * @copyright      Copyright (c)2017-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU General Public License version 3 or later
 */

namespace Akeeba\Component\Compatibility\Administrator\View\Main;

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
	public function display($tpl = null)
	{
		ToolbarHelper::title(Text::_('COM_COMPATIBILITY'), '');
		ToolbarHelper::preferences('com_compatibility');

		parent::display($tpl);
	}

}