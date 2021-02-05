<?php
/**
 * @package		com_compatibility
 * @copyright	Copyright (c)2017-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */

namespace Akeeba\Compatibility\Admin\Controller;

use FOF40\Container\Container;
use FOF40\Controller\Controller;
use FOF40\Controller\Mixin\PredefinedTaskList;
use FOF40\Utils\ViewManifestMigration;
use FOF40\View\Exception\AccessForbidden;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Document\JsonDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use RuntimeException;

// Protect from unauthorized access
defined('_JEXEC') or die();

class ControlPanel extends Controller
{
	use PredefinedTaskList;

	public function __construct(Container $container, array $config = [])
	{
		parent::__construct($container, $config);

		$this->setPredefinedTaskList(['show', 'export', 'import']);
	}

	public function show($cachable = false)
	{
		ViewManifestMigration::migrateJoomla4MenuXMLFiles($this->container);
		ViewManifestMigration::removeJoomla3LegacyViews($this->container);

		$this->display(true);
	}

	public function export($cacheable = false)
	{
		// Only allow when format=json
		if ($this->input->get('format', 'html') != 'json')
		{
			throw new AccessForbidden();
		}

		// Get the JSON content
		$config = [
			'extensions'         => $this->container->params->get('extensions', new \stdClass()),
			'cms'                => $this->container->params->get('cms', new \stdClass()),
			'exclude_php'        => $this->container->params->get('exclude_php', []),
			'show_intro'         => $this->container->params->get('show_intro', 1),
			'intro_text'         => $this->container->params->get('intro_text', ''),
			'load_fef'           => $this->container->params->get('load_fef', 3),
			'fef_reset'          => $this->container->params->get('fef_reset', 3),
			'dark_mode_backend'  => $this->container->params->get('dark_mode_backend', -1),
			'dark_mode_frontend' => $this->container->params->get('dark_mode_frontend', -1),
		];

		/** @var AdministratorApplication $app */
		$app = Factory::getApplication();
		/** @var JsonDocument $doc */
		$doc = $app->getDocument();

		// Disable caching
		$app->setHeader('Pragma', 'public', true);
		$app->setHeader('Expires', '0', true);
		$app->setHeader("Cache-Control", "must-revalidate, post-check=0, pre-check=0", true);
		$app->setHeader("Cache-Control", "public", true);

		// Send MIME headers
		$doc->setMimeEncoding('application/json');
		$doc->setName($this->container->componentName . '_settings');

		// Set the content and display
		echo json_encode($config, JSON_PRETTY_PRINT);

		$this->display(false);
	}

	public function import($cacheable = false)
	{
		$this->csrfProtection();

		$file     = $this->input->files->get('importfile', null, 'file');
		$redirect = Route::_('index.php?option=com_compatibility&view=ControlPanel');

		try
		{
			/** @var \Akeeba\Compatibility\Admin\Model\ControlPanel $model */
			$model = $this->getModel();
			$model->importFromFileDescriptor($file);

			$this->setRedirect($redirect, Text::_('COM_COMPATIBILITY_CONTROLPANEL_LBL_DONEIMPORTING'), 'success');
		}
		catch (RuntimeException $e)
		{
			$this->setRedirect($redirect, $e->getMessage(), 'error');
		}
	}
}
