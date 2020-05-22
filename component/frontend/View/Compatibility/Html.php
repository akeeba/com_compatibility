<?php
/**
 * @package        com_compatibility
 * @copyright      Copyright (c)2017-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU General Public License version 3 or later
 */

namespace Akeeba\Compatibility\Site\View\Compatibility;

use Akeeba\Compatibility\Site\Model\Compatibility;
use FOF30\View\DataView\Html as FOFHtml;

// Protect from unauthorized access
defined('_JEXEC') or die();

class Html extends FOFHtml
{
	/**
	 * The data to push to the frontend of the site
	 *
	 * @var   array
	 * @since 1.0.0
	 */
	public $data = [];

	/** @inheritDoc */
	protected function onBeforeBrowse()
	{
		/** @var Compatibility $model */
		$model = $this->getModel();

		// Get the version information per configured software
		$this->data = $model->getDisplayData();
	}
}
