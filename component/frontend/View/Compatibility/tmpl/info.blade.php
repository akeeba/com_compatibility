<?php
/**
 * @package		com_compatibility
 * @copyright	Copyright (c)2017-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

?>
<p>
    @lang('COM_COMPATIBILITY_LBL_INTRO_TOP')
</p>

<div class="akeeba-block--info">
    @sprintf('COM_COMPATIBILITY_LBL_VERSIONNUMBER_NOTICE', Joomla\CMS\Uri\Uri::base() . 'how-do-version-numbers-work.html')
</div>

<h4>@lang('COM_COMPATIBILITY_HEAD_SUPPORT_POLICY')</h4>

<p>
	@lang('COM_COMPATIBILITY_LBL_SUPPORT_POLICY')
</p>

<h4>
    @lang('COM_COMPATIBILITY_HEAD_PHP_VERSION_INFO')
</h4>

<p>
    @sprintf('COM_COMPATIBILITY_HEAD_LBL_PHP_VERSION_SUPPORTED', 'http://php.net/supported-versions.php')
</p>
<p>
	@lang('COM_COMPATIBILITY_HEAD_LBL_PHP_VERSION_UPGRADENAG')
</p>