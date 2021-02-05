<?php
/**
 * @package        com_compatibility
 * @copyright      Copyright (c)2017-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU General Public License version 3 or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

use Joomla\CMS\Filter\InputFilter;

$params    = $this->container->params;
$showIntro = $params->get('show_intro', 1);
$introText = $params->get('intro_text', '');

if ($showIntro)
{
	$filter   = new InputFilter([], [], 1, 1, 1, -1);
	$hasIntro = strlen($filter->clean($introText, 'html')) > 0;
}
?>
@if ($this->container->params->get('show_intro', 1) == 1)
    <div class="akeeba-panel--primary">
        <header class="akeeba-block-header">
            <h3>
                <span class="akion-information-circled"></span>
                @lang('COM_COMPATIBILITY_LBL_USEFUL_INFO')
            </h3>
        </header>
        @if ($hasIntro)
            {{ $introText }}
        @else
            @include('site:com_compatibility/Compatibility/info')
        @endif
    </div>
@endif

@each('site:com_compatibility/Compatibility/software', $this->data, 'software', 'raw|No software found')
