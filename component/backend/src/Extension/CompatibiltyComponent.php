<?php
/**
 * @package        com_compatibility
 * @copyright      Copyright (c)2017-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU General Public License version 3 or later
 */

namespace Akeeba\Component\Compatibility\Administrator\Extension;

defined('_JEXEC') || die;

use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Psr\Container\ContainerInterface;

class CompatibiltyComponent extends MVCComponent implements BootableExtensionInterface, RouterServiceInterface
{
	use HTMLRegistryAwareTrait;
	use RouterServiceTrait;

	public function boot(ContainerInterface $container)
	{
//		$dbo = $container->has('database.driver') ? $container->get('database.driver') : Factory::getDbo();
//		$this->getRegistry()->register('compatibilty', new CompatibiltyHtml($dbo));
	}
}