<?php
/**
 * @package		com_compatibility
 * @copyright	Copyright (c)2017-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */

namespace Akeeba\Compatibility\Admin\Model;

use FOF40\Model\Model;
use Joomla\CMS\Language\Text;
use RuntimeException;

// Protect from unauthorized access
defined('_JEXEC') or die();

class ControlPanel extends Model
{
	public function importFromFileDescriptor(?array $file): void
	{
		// Sanity checks
		if (!$file)
		{
			throw new RuntimeException(Text::_('COM_COMPATIBILITY_CONTROLPANEL_ERR_NOFILE'));
		}

		$data = file_get_contents($file['tmp_name']);

		if ($data === false)
		{
			throw new RuntimeException(Text::_('COM_COMPATIBILITY_CONTROLPANEL_ERR_EMPTYFILE'));
		}

		$data = json_decode($data, false);

		if (is_null($data))
		{
			throw new RuntimeException(Text::_('COM_COMPATIBILITY_CONTROLPANEL_ERR_NOTJSON'));
		}

		$validProperties = [
			'extensions',
			'cms',
			'exclude_php',
			'show_intro',
			'intro_text',
			'load_fef',
			'fef_reset',
			'dark_mode_backend',
			'dark_mode_frontend',
		];
		$importedCount   = 0;

		$params = $this->container->params;

		foreach ($validProperties as $prop)
		{
			if (!isset($data->{$prop}))
			{
				continue;
			}

			$importedCount++;

			$params->set($prop, $data->{$prop});
		}

		if (!$importedCount)
		{
			throw new RuntimeException('COM_COMPATIBILITY_CONTROLPANEL_ERR_NODATA');
		}

		$params->save();
	}
}
