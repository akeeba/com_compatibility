<?php
/**
 * @package		com_compatibility
 * @copyright	Copyright (c)2017-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */

/** @var array $software */

$type          = $software['type'];
$latestRelease = $software['latest'];
$latestCms     = '';

foreach ($software['matrix'] as $cms => $releases)
{
    $latestCms = $cms;
}

$title = $software['logo'] . ' ' . $software['title'];

?>

<div class="akeeba-panel">
    <header class="akeeba-block-header">
        <h3 id="{{ $software['slug'] }}-compatibility">
            {{ $title }}
        </h3>
    </header>
    <div id="article-software-{{ $software['slug'] }}" class="panel-collapse collapse">
        <table class="akeeba-table--striped--comfortable--hover--leftbold" style="width: 100%;">
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
                            <span class="akeeba-label--{{ ($version == $latestCms) ? 'green' : 'grey' }}">
                            {{ $type  }} {{ $version }}
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

        <div class="alert alert-info">
            <strong>Looking for all other versions?</strong>
            You can find all the versions in reverse chronological order in the product's <a href="{{ $software['link'] }}">download page</a>.
        </div>

    </div>
</div>
