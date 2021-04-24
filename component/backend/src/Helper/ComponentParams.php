<?php
/**
 * @package		com_compatibility
 * @copyright	Copyright (c)2017-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */

namespace Akeeba\Component\Compatibility\Administrator\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory as JoomlaFactory;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();


class ComponentParams
{
	public static function save(Registry $params, string $option): void
	{
		/** @var DatabaseDriver $db */
		$db   = JoomlaFactory::getContainer()->get('DatabaseDriver');
		$data = $params->toString('JSON');

		$sql = $db->getQuery(true)
			->update($db->qn('#__extensions'))
			->set($db->qn('params') . ' = ' . $db->q($data))
			->where($db->qn('element') . ' = :option')
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->bind(':option', $option);

		$db->setQuery($sql);

		try
		{
			$db->execute();

			// The component parameters are cached. We just changed them. Therefore we MUST reset the system cache which holds them.
			CacheCleaner::clearCacheGroups(['_system'], [0, 1]);
		}
		catch (\Exception $e)
		{
			// Don't sweat if it fails
		}

		// Reset ComponentHelper's cache
		$refClass = new \ReflectionClass(ComponentHelper::class);
		$refProp = $refClass->getProperty('components');
		$refProp->setAccessible(true);
		$components = $refProp->getValue();
		$components[$option]->params = $params;
		$refProp->setValue($components);
	}
}