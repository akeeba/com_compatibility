<?php
/**
 * @package		com_compatibility
 * @copyright	Copyright (c)2017-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/** @var array $software */

$type          = $software['type'];
$latestRelease = $software['latest'];
$latestCms     = '';
$latestCP      = '';
$latestWP      = '';

if ($software['type'] == 'WordPress')
{
	foreach ($software['matrix'] as $cms => $releases)
	{
		$type = strtoupper(substr($cms, 0, 2));

		if ($type == 'CP')
        {
            $latestCP = $cms;
        }
		else
        {
	        $latestWP = $cms;
        }
	}
}

foreach ($software['matrix'] as $cms => $releases)
{
   $latestCms = $cms;
}

$title = $software['logo'] . ' ' . $software['title'];

?>

<div class="akeeba-panel--information" id="{{ $software['slug'] }}-compatibility">
    <header class="akeeba-block-header">
        <h3>
            {{ $software['logo'] }}
            <span>
                {{{ $software['title'] }}}
            </span>
        </h3>
    </header>
    <div id="article-software-{{ $software['slug'] }}">
        <table class="akeeba-table--striped--comfortable--hover" style="width: 100%;">
            <thead>
            <tr>
                <th></th>
                @foreach($software['php'] as $phpVersion)
                    <th>PHP {{ $phpVersion }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach ($software['matrix'] as $version => $releases)
                <tr>
                    <td>
                            @if ($type == 'WP')
                            <?php $latestCms = (substr($version, 0, 2) == 'CP') ? $latestCP : $latestWP ?>
                            <span class="akeeba-label--{{ ($version == $latestCms) ? 'green' : 'grey' }}">
                                {{ (substr($version, 0, 2) == 'CP') ? 'ClassicPress' : 'WordPress' }}
                                {{ substr($version, 2) }}
                            @else
                            <span class="akeeba-label--{{ ($version == $latestCms) ? 'green' : 'grey' }}">
                                {{ $type  }} {{ $version }}
                            @endif
                            </span>
                    </td>
                    @foreach ($releases as $phpVersion => $release)
                        <td>
                            @unless(empty($release))
                                <a href="{{ $release['link'] }}">
                                    @if ($release['version'] == $latestRelease)
                                        <span class="akeeba-label--{{ ($version == $latestCms) ? 'green' : 'grey' }}">
                                        @endif
                                            {{ $release['version'] }}
                                            @if ($release['version'] == $latestRelease)
                                        </span>
                                    @endif
                                </a>
                                @else
                                &mdash;
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>

        <p>&nbsp;</p>

        <div class="akeeba-alert--info">
            <p>
                <a href="{{ $software['link'] }}" class="akeeba-btn--big">
                    <span class="akion-android-download"></span>
                    All other <em>{{ $software['title'] }}</em> versions
                </a>
            </p>
        </div>

    </div>
</div>
