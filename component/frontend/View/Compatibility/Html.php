<?php

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

			// TODO Get the logo, render it as an img tag at 32px square
			$matrix['logo'] = '';
			$matrix['title'] = $category->title;
			$matrix['link'] = \JRoute::_('index.php?option=com_ars&view=Releases&category_id=' . $id);
			$matrix['slug'] = $category->alias;

			$this->data[] = $matrix;

			unset($matrix);
		}
	}

}