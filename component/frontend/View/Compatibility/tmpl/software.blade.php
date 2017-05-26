<?php
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

<div class="panel panel-default">
    <div id="article-accordion-heading" class="panel-heading">
        <h4 class="panel-title">
            <a href="#article-software-{{ $software['slug'] }}" data-toggle="collapse" data-parent="#article-intro-accordion">
                {{ $title }}
            </a>
        </h4>
    </div>
    <div id="article-software-{{ $software['slug'] }}" class="panel-collapse collapse">
        <div class="panel-body">
            <table class="table table-striped" style="width: 100%;">
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
                            <span class="label label-{{ ($version == $latestCms) ? 'success' : 'default' }}">
                            {{ $type  }} {{ $version }}
                            </span>
                        </td>
                        @foreach ($releases as $phpVersion => $release)
                            <td>
                                @unless(empty($release))
                                    <a href="{{ $release['link'] }}">
                                        @if ($release['version'] == $latestRelease)
                                        <span class="label label-{{ ($version == $latestCms) ? 'success' : 'warning' }}">
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
                <strong>Looking for other versions?</strong>
                You can find all the versions in reverse chronological order in the product's <a href="{{ $software['link'] }}">download page</a>.
            </div>
        </div>
    </div>
</div>
