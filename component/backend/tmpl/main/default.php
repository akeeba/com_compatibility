<?php
/**
 * @package		com_compatibility
 * @copyright	Copyright (c)2017-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */

defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<div class="alert alert-info">
	<?= Text::_('COM_COMPATIBILITY_CONTROLPANEL_LBL_HOWTOUSE') ?>
</div>

<div class="card">
	<div class="card-header">
		<h3>
			<?= Text::_('COM_COMPATIBILITY_CONTROLPANEL_LBL_IMPORTEXPORT') ?>
		</h3>
	</div>
	<div class="card-body">
		<p>
			<?= Text::_('COM_COMPATIBILITY_CONTROLPANEL_LBL_WHYIMPORTEXPORT') ?>
		</p>

		<form action="<?= Route::_('index.php?option=com_compatibility&task=import') ?>" enctype="multipart/form-data"
		      method="post"
		      id="adminForm" name="adminForm">

			<div class="row mb-3">
				<label for="importfile" class="col-sm-3 col-form-label">
					<?= Text::_('COM_COMPATIBILITY_CONTROLPANEL_LBL_IMPORTFILE') ?>
				</label>
				<div class="col-sm-9">
					<input type="file" class="form-control" name="importfile" id="importfile" value="" />
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-sm-4 offset-sm-3">
					<button type="submit" class="btn btn-warning w-100">
						<span class="fa fa-upload"></span>
						<?= Text::_('COM_COMPATIBILITY_CONTROLPANEL_LBL_IMPORT') ?>
					</button>
				</div>
				<div class="col-sm-4 offset-sm-1">
					<a class="btn btn-success w-100"
					   href="<?= Route::_('index.php?option=com_compatibility&task=export&format=json') ?>">
						<span class="fa fa-file-code"></span>
						<?= Text::_('COM_COMPATIBILITY_CONTROLPANEL_LBL_EXPORT') ?>
					</a>
				</div>
			</div>

			<?= HTMLHelper::_('form.token') ?>
		</form>
	</div>
</div>
