<?php
/**
 * @package		com_compatibility
 * @copyright	Copyright (c)2017-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */

namespace Akeeba\Compatibility\Site\View\Compatibility;

use Akeeba\Compatibility\Site\Model\Compatibility;
use FOF40\View\DataView\Html as FOFHtml;

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

	protected function getCMSLabels(array $cmsVersions, string $softwareType = 'Joomla'): array
	{
		// Quick exit if I have no versions.
		if (empty($cmsVersions))
		{
			return [];
		}

		$cmsVersions = array_map(function ($version) {
			return rtrim($version, '+');
		}, $cmsVersions);

		/**
		 * Special case: WordPress and ClassicPress. I need to separate them into WP and CP versions and
		 * run this method twice, then return the combined result.
		 */
		if ($softwareType == 'WordPress')
		{
			$cpVersions = [];
			$wpVersions = [];

			foreach ($cmsVersions as $cms)
			{
				$type = strtoupper(substr($cms, 0, 2));

				if ($type == 'CP')
				{
					$cpVersions[] = $cms;

					continue;
				}

				$wpVersions[] = $cms;
			}

			return array_merge($this->getCMSLabels($wpVersions, 'wp'), $this->getCMSLabels($cpVersions, 'cp'));
		}

		$ret                 = [];
		$longSupportVersions = $this->getLongSupportVersions($softwareType);
		$betaVersions        = $this->getBetaVersions($softwareType);
		$previousStable      = '';
		$latestVersion       = '';

		foreach ($cmsVersions as $version)
		{
			if (in_array($version, $betaVersions))
			{
				continue;
			}

			$previousStable = $latestVersion;
			$latestVersion  = $version;
		}

		if (count(array_intersect($betaVersions, $cmsVersions)))
		{
			$previousStable = '';
		}

		foreach ($cmsVersions as $version)
		{
			if ($version === $latestVersion)
			{
				$ret[$version] = 'latest';

				continue;
			}

			if (in_array($version, $longSupportVersions, true))
			{
				$ret[$version] = 'lts';

				continue;
			}

			if (in_array($version, $betaVersions, true))
			{
				$ret[$version] = 'beta';

				continue;
			}

			if ($version === $previousStable)
			{
				$ret[$version] = 'previous';

				continue;
			}

			$ret[$version] = 'obsolete';
		}

		return $ret;
	}

	private function getLongSupportVersions(string $softwareType): array
	{
		// TODO Make this configurable
		$ret = [];

		switch (strtolower($softwareType))
		{
			case 'joomla':
				$ret = ['3.10'];

				break;

			case 'wp':
				$ret = ['4.9'];

				break;
		}

		if (in_array(strtolower($softwareType), ['wp', 'cp']))
		{
			$ret = array_map(function ($v) use ($softwareType) {
				return strtoupper($softwareType) . $v;
			}, $ret);
		}

		return $ret;
	}

	private function getBetaVersions(string $softwareType)
	{
		// TODO Make this configurable
		switch (strtolower($softwareType))
		{
			case 'joomla':
				return ['4.0'];
				break;
		}

		return [];
	}
}
