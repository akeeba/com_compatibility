<?php
/**
 * @package		com_compatibility
 * @copyright	Copyright (c)2017-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */
?>
<div id="article-intro-accordion" class="panel-group">
    <div class="panel panel-default">
        <div id="article-accordion-heading" class="panel-heading">
            <h4 class="panel-title">
                <a href="#article-intro" data-toggle="collapse" data-parent="#article-intro-accordion">
                    <span class="glyphicon glyphicon-info-sign"> </span> Useful information (click to show / hide)
                </a>
            </h4>
        </div>
        <div id="article-intro" class="panel-collapse collapse in">
            <div class="panel-body">
                @include('site:com_compatibility/Compatibility/info')
            </div>
        </div>
    </div>

@each('site:com_compatibility/Compatibility/software', $this->data, 'software', 'raw|No software found')

</div>
