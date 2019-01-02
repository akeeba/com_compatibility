<?php
/**
 * @package		com_compatibility
 * @copyright	Copyright (c)2017-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */
?>
<div class="akeeba-panel--primary">
    <header class="akeeba-block-header">
        <h3>
            <span class="akion-information-circled"></span>
            @lang('COM_COMPATIBILITY_LBL_USEFUL_INFO')
        </h3>
    </header>
    @include('site:com_compatibility/Compatibility/info')
</div>

@each('site:com_compatibility/Compatibility/software', $this->data, 'software', 'raw|No software found')
