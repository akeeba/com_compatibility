<?php
/**
 * @package        com_compatibility
 * @copyright      Copyright (c)2017-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU General Public License version 3 or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/** @var \Akeeba\Component\Compatibility\Site\View\Compatibility\HtmlView $this */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;

$params    = ComponentHelper::getParams('com_compatibility');
$showIntro = $params->get('show_intro', 1) == 1;
$introText = $params->get('intro_text', '');

if ($showIntro)
{
	$filter   = new InputFilter([], [], 1, 1, 1, -1);
	$hasIntro = strlen($filter->clean($introText, 'html')) > 0;
}
?>
<?php if ($showIntro): ?>
    <div class="card">
        <div class="card-header">
            <h3>
                <span class="fa fa-info-circle"></span>
                <?= Text::_('COM_COMPATIBILITY_LBL_USEFUL_INFO') ?>
            </h3>
        </div>
		<div class="card-body">
			<?php if ($hasIntro): ?>
				<?= $introText ?>
			<?php else: ?>
				<?= $this->loadTemplate('info') ?>
			<?php endif ?>
		</div>
    </div>
<?php endif ?>

<?php foreach ($this->data as $software) {
	$this->_software = $software;
	echo $this->loadTemplate('software');
} ?>
