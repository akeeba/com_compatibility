<?php
/**
 * @package        com_compatibility
 * @copyright      Copyright (c)2017-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU General Public License version 3 or later
 */

/** @var \FOF30\View\DataView\Html $this */

// Protect from unauthorized access
defined('_JEXEC') or die();
?>

<div class="akeeba-block--info">
    @lang('COM_COMPATIBILITY_CONTROLPANEL_LBL_HOWTOUSE')
</div>

<div class="akeeba-panel--default">
    <header class="akeeba-block-header">
        <h3>@lang('COM_COMPATIBILITY_CONTROLPANEL_LBL_IMPORTEXPORT')</h3>
    </header>

    <p>@lang('COM_COMPATIBILITY_CONTROLPANEL_LBL_WHYIMPORTEXPORT')</p>

    <form name="adminForm" id="adminForm" class="akeeba-form"
          action="index.php" method="post" enctype="multipart/form-data">

        <div class="akeeba-form-group">
            <label>@lang('COM_COMPATIBILITY_CONTROLPANEL_LBL_IMPORTFILE')</label>

            <input type="file" name="importfile" value="" />
        </div>

        <div class="akeeba-form-group--actions">
            <div class="akeeba-container--50-50">
                <button type="submit" class="akeeba-btn--orange--block">
                    <span class="akion-android-upload"></span>
                    @lang('COM_COMPATIBILITY_CONTROLPANEL_LBL_IMPORT')
                </button>
                <a class="akeeba-btn--success--block" href="@route('index.php?option=com_compatibility&view=ControlPanel&task=export&format=json')">
                    <span class="akion-code-download"></span>
                    @lang('COM_COMPATIBILITY_CONTROLPANEL_LBL_EXPORT')
                </a>
            </div>
        </div>

        <input type="hidden" name="option" value="{{ $this->getContainer()->componentName }}" />
        <input type="hidden" name="view" value="{{ $this->input->get('view') }}" />
        <input type="hidden" name="task" value="import" />
        <input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1" />
    </form>
</div>