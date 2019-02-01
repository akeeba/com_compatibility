<?php
/**
 * @package		com_compatibility
 * @copyright	Copyright (c)2017-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */

namespace Akeeba\Compatibility\Site\View\Compatibility;

use Akeeba\Compatibility\Site\Model\Compatibility;
use Akeeba\ReleaseSystem\Site\Model\Categories;
use FOF30\Container\Container;
use FOF30\View\DataView\Html as FOFHtml;

// Protect from unauthorized access
defined('_JEXEC') or die();

class Html extends FOFHtml
{
	public $data = [];

	protected function onBeforeBrowse()
	{
		/** @var Categories $category */
		$arsContainer = Container::getInstance('com_ars');
		$category     = $arsContainer->factory->model('Categories');

		$catIDs = $this->container->params->get('include');

		/** @var Compatibility $model */
		$model = $this->getModel();

		foreach ($catIDs as $id)
		{
			$category = $category->findOrFail($id);

			$matrix = $model->getMatrix($id);

			if (!$matrix)
			{
				continue;
			}

			// Get the logo, render it as an img tag at 32px square
			$matrix['logo'] = '';
			$logo = $this->getLogo($id);

			if ($logo)
			{
				$matrix['logo'] = "<span class=\"$logo\"></span>";
			}

			$matrix['title'] = $category->title;
			$matrix['link'] = \JRoute::_('index.php?option=com_ars&view=Releases&category_id=' . $id);
			$matrix['slug'] = $category->alias;

			$this->data[] = $matrix;

			unset($matrix);
		}
	}

	protected function getLogo($id)
	{
		switch ($id)
		{
			case 1:
				return 'aklogo-backup-j';

				break;

			case 39:
				return 'aklogo-backup-wp';

				break;

			case 38:
				return 'aklogo-solo';

				break;

			case 12:
				return 'aklogo-admintools-j';

				break;

			case 26:
				return 'aklogo-tickets';

				break;

			case 48:
				return 'aklogo-admintools-wp';

				break;

			default:
				return 'aklogo-company-logo';
		}
	}
}
