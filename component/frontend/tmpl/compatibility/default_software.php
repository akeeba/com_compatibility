<?php
/**
 * @package        com_compatibility
 * @copyright      Copyright (c)2017-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU General Public License version 3 or later
 */

// Protect from unauthorized access
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

/**
 * @var \Akeeba\Component\Compatibility\Site\View\Compatibility\HtmlView $this
 * @var array                                                            $software
 */

$software       = $this->_software;
$type           = $software['type'];
$latestRelease  = $software['latest'];
$title          = $software['logo'] . ' ' . $software['title'];
$latestVersions = $this->getCMSLabels(array_keys($software['matrix']), $software['type']);
?>

<div class="card mt-3" id="<?= $software['slug'] ?>-compatibility">
    <div class="card-header">
        <h3>
            <?= $software['logo'] ?>
            <span>
                <?= $this->escape($software['title']) ?>
            </span>
        </h3>
    </div>
    <div id="article-software-<?= $software['slug'] ?>" class="card-body">
        <table class="table table-striped table-hover" style="width: 100%;">
			<caption class="visually-hidden">
				<?= Text::sprintf('COM_COMPATIBILITY_LBL_TABLE_CAPTION', $software['title']) ?>
			</caption>
            <thead>
            <tr>
                <td></td>
				<?php foreach($software['php'] as $phpVersion): ?>
				<th scope="col">PHP <?= $phpVersion ?></th>
				<?php endforeach ?>
            </tr>
            </thead>
            <tbody>
			<?php foreach ($software['matrix'] as $version => $releases): ?>
                <?php
                    $cmsReleaseType = $latestVersions[rtrim($version, '+')];
                    $cmsLabelColor = in_array($cmsReleaseType, ['beta', 'lts']) ? 'warning' : ($cmsReleaseType === 'latest' ? 'success' : 'dark');
                    $cmsName = ($type === 'Joomla') ? 'Joomla' : (strtolower(substr($version, 0, 2)) == 'wp' ? 'WordPress' : 'ClassicPress');
                    $displayVersion = ($type === 'Joomla') ? $version : substr($version, 2);
                    $latestVersionLabelColor = $cmsLabelColor;
                    // Standalone products get special treatment
                    $cmsLabelColor = ($type === 'Standalone') ? 'primary' : $cmsLabelColor;
                    $latestVersionLabelColor = ($type === 'Standalone') ? 'success' : $cmsLabelColor;
                    $cmsName = ($type === 'Standalone') ? 'PHP' : $cmsName;
                ?>
                <tr>
                    <th scope="row">
                        <span class="badge bg-<?= $cmsLabelColor ?>">
                            <?= $cmsName ?> <?= $displayVersion ?>
                        </span>
                    </th>
					<?php foreach ($releases as $phpVersion => $release): ?>
                        <td>
							<?php if (!empty($release)): ?>
                                <a href="<?= $release['link'] ?>">
									<?php if ($release['version'] == $latestRelease): ?>
									<span class="badge bg-<?= $latestVersionLabelColor ?>">
										<?= $release['version'] ?>
									</span>
									<?php else: ?>
									<span class="text-muted">
										<?= $release['version'] ?>
									</span>
									<?php endif ?>
                                </a>
								<?php else: ?>
                                &mdash;
							<?php endif ?>
                        </td>
					<?php endforeach ?>
                </tr>
			<?php endforeach ?>
            </tbody>
        </table>

        <div class="p-2">
            <p>
                <a href="<?= $software['link'] ?>" class="btn btn-outline-info btn-sm">
                    <span class="fa fa-download"></span>
                    All other <em><?= $software['title'] ?></em> versions
                </a>
            </p>
        </div>

    </div>
</div>
