<?php
/**
 * @package		com_compatibility
 * @copyright	Copyright (c)2017-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */

namespace Akeeba\Component\Compatibility\Administrator\Controller;

defined('_JEXEC') or die();

use Akeeba\Component\Compatibility\Administrator\Model\CompatibiltyModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\JsonDocument;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use RuntimeException;

class DisplayController extends BaseController
{
	protected $default_view = 'main';

	public function display($cachable = false, $urlparams = [])
	{
		$view   = $this->input->get('view', $this->default_view);

		if ($view != $this->default_view)
		{
			$this->input->set('view', $this->default_view);
		}

		return parent::display($cachable, $urlparams);
	}

	public function export($cacheable = false)
	{
		// Only allow when format=json
		if ($this->input->get('format', 'html') != 'json')
		{
			throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$cParams = ComponentHelper::getParams('com_compatibility');

		// Get the JSON content
		$config = [
			'extensions'         => $cParams->get('extensions', new \stdClass()),
			'cms'                => $cParams->get('cms', new \stdClass()),
			'exclude_php'        => $cParams->get('exclude_php', []),
			'show_intro'         => $cParams->get('show_intro', 1),
			'intro_text'         => $cParams->get('intro_text', ''),
		];

		/** @var JsonDocument $doc */
		$doc = $this->app->getDocument();

		// Disable caching
		$this->app->setHeader('Pragma', 'public', true);
		$this->app->setHeader('Expires', '0', true);
		$this->app->setHeader("Cache-Control", "must-revalidate, post-check=0, pre-check=0", true);
		$this->app->setHeader("Cache-Control", "public", true);

		// Send MIME headers
		$doc->setMimeEncoding('application/json');
		$doc->setName('com_compatibility_settings');

		// Set the content and display
		echo json_encode($config, JSON_PRETTY_PRINT);

		$this->display(false);
	}

	public function import($cacheable = false)
	{
		$this->checkToken();

		$file     = $this->input->files->get('importfile', null, 'file');
		$redirect = Route::_('index.php?option=com_compatibility', false);

		try
		{
			/** @var CompatibiltyModel $model */
			$model = $this->getModel('Compatibilty', 'Administrator');
			$model->importFromFileDescriptor($file);

			$this->setRedirect($redirect, Text::_('COM_COMPATIBILITY_CONTROLPANEL_LBL_DONEIMPORTING'), 'success');
		}
		catch (RuntimeException $e)
		{
			$this->setRedirect($redirect, $e->getMessage(), 'error');
		}
	}
}