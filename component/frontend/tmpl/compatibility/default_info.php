<?php
/**
 * @package        com_compatibility
 * @copyright      Copyright (c)2017-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU General Public License version 3 or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

?>
<p>
	<?= Text::_('COM_COMPATIBILITY_LBL_INTRO_TOP') ?>
</p>

<div class="alert alert-info">
	<?= Text::sprintf('COM_COMPATIBILITY_LBL_VERSIONNUMBER_NOTICE', Uri::base() . 'how-do-version-numbers-work.html') ?>
</div>

<h4><?= Text::_('COM_COMPATIBILITY_HEAD_SUPPORT_POLICY') ?></h4>

<p>
	<?= Text::_('COM_COMPATIBILITY_LBL_SUPPORT_POLICY') ?>
</p>

<h4>
	<?= Text::_('COM_COMPATIBILITY_HEAD_PHP_VERSION_INFO') ?>
</h4>

<p>
	<?= Text::sprintf('COM_COMPATIBILITY_HEAD_LBL_PHP_VERSION_SUPPORTED', 'http://php.net/supported-versions.php') ?>
</p>
<p>
	<?= Text::_('COM_COMPATIBILITY_HEAD_LBL_PHP_VERSION_UPGRADENAG') ?>
</p>