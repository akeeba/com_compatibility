<?php
/**
 * @package		com_compatibility
 * @copyright	Copyright (c)2017-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * @var \Akeeba\Compatibility\Site\View\Compatibility\Html $this
 * @var array $software
 */

$type           = $software['type'];
$latestRelease  = $software['latest'];
$title          = $software['logo'] . ' ' . $software['title'];
$latestVersions = $this->getCMSLabels(array_keys($software['matrix']), $software['type']);
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
                <?php
                    $cmsReleaseType = $latestVersions[rtrim($version, '+')];
                    $cmsLabelColor = in_array($cmsReleaseType, ['beta', 'lts']) ? 'orange' : ($cmsReleaseType === 'latest' ? 'green' : 'grey');
                    $cmsName = ($type === 'Joomla') ? 'Joomla' : (strtolower(substr($version, 0, 2)) == 'wp' ? 'WordPress' : 'ClassicPress');
                    $displayVersion = ($type === 'Joomla') ? $version : substr($version, 2);
                    $latestVersionLabelColor = $cmsLabelColor;
                    // Standalone products get special treatment
                    $cmsLabelColor = ($type === 'Standalone') ? 'teal' : $cmsLabelColor;
                    $latestVersionLabelColor = ($type === 'Standalone') ? 'green' : $cmsLabelColor;
                    $cmsName = ($type === 'Standalone') ? 'PHP' : $cmsName;
                ?>
                <tr>
                    <td>
                        <span class="akeeba-label--{{ $cmsLabelColor }}">
                            {{ $cmsName }} {{ $displayVersion }}
                        </span>
                    </td>
                    @foreach ($releases as $phpVersion => $release)
                        <td>
                            @unless(empty($release))
                                <a href="{{ $release['link'] }}">
                                    @if ($release['version'] == $latestRelease)
                                        <span class="akeeba-label--{{ $latestVersionLabelColor }}">
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
