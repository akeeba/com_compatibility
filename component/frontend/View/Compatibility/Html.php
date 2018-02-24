<?php
/**
 * @package		com_compatibility
 * @copyright	Copyright (c)2017-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */

namespace Akeeba\Compatibility\Site\View\Compatibility;

use Akeeba\Compatibility\Site\Model\Compatibility;
use Akeeba\ReleaseSystem\Site\Model\Categories;
use FOF30\Container\Container;
use FOF30\View\DataView\Html as FOFHtml;

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
				$matrix['logo'] = \JHtml::image('images/' . $logo, '', ['width' => 32, 'height' => '32', 'style' => 'vertical-align: middle;']);
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
				return 'logos/akeebabackup_128.png';

				break;

			case 39:
				return 'logos/solo-64.png';

				break;

			case 38:
				return 'logos/solo-64.png';

				break;

			case 12:
				return 'logos/admintools_128.png';

				break;

			case 26:
				return 'logos/ats_128.png';

				break;

			default:
				return '';
		}
	}
}
