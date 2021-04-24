<?php
/**
 * @package        com_compatibility
 * @copyright      Copyright (c)2017-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU General Public License version 3 or later
 */

namespace Akeeba\Component\Compatibility\Administrator\Model;

defined('_JEXEC') or die();

use Akeeba\Component\Compatibility\Administrator\Helper\ComponentParams;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use RuntimeException;

class CompatibiltyModel extends BaseDatabaseModel
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
		];
		$importedCount   = 0;

		$params = ComponentHelper::getParams($this->option);

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

		ComponentParams::save($params, $this->option);
	}
}