<?php
/**
 * @package        com_compatibility
 * @copyright      Copyright (c)2017-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU General Public License version 3 or later
 */

namespace Akeeba\Component\Compatibility\Site\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterBase;

class Router extends RouterBase
{

	public function build(&$query)
	{
		if (isset($query['view']))
		{
			unset($query['view']);
		}

		return [];
	}

	public function parse(&$segments)
	{
		return ['view' => 'compatiblity'];
	}
}