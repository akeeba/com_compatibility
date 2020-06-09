<?php
/**
 * @package        com_compatibility
 * @copyright      Copyright (c)2017-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU General Public License version 3 or later
 */

namespace Akeeba\Compatibility\Site\Model;

use Akeeba\ReleaseSystem\Site\Model\Categories;
use Akeeba\ReleaseSystem\Site\Model\Items;
use FOF30\Container\Container;
use FOF30\Model\DataModel\Exception\RecordNotLoaded;
use FOF30\Model\Model;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

// Protect from unauthorized access
defined('_JEXEC') or die();

class Compatibility extends Model
{
	/**
	 * CMS and PHP compatibility rules
	 *
	 * @var array|null
	 */
	private $cmsRules = null;

	/**
	 * Returns the version information for the front-end
	 *
	 * @return  array
	 */
	public function getDisplayData(): array
	{
		return array_filter(
			array_map([$this, 'getCategoryVersionInformation'], $this->getConfiguredSoftware()),
			function (?array $item): bool {
				return is_array($item) && !empty($item);
			});
	}

	/**
	 * Returns the configured software for displaying compatibility information
	 *
	 * @return  array  Array of plain objects with the keys catid, title and icon
	 */
	protected function getConfiguredSoftware(): array
	{
		$nullObject = new \stdClass();

		$extensions = $this->container->params->get('extensions', $nullObject);

		return array_map(function (array $software) {
			$software = (object) $software;

			return (object) [
				'catid' => $software->category ?? null,
				'title' => $software->title ?? null,
				'icon'  => $software->icon ?? 'aklogo-company-logo',
			];
		}, ArrayHelper::fromObject($extensions));
	}

	/**
	 * Returns the version compatibility information for a configured software
	 *
	 * @return  array|null  Version compatibility information, null on invalid category
	 */
	protected function getCategoryVersionInformation(object $item): ?array
	{
		// Get the category ID
		$category_id = $item->catid ?? 0;

		if (empty($category_id))
		{
			return null;
		}

		// Try to load the category
		/** @var Categories $category */
		$arsContainer = Container::getInstance('com_ars');
		$category     = $arsContainer->factory->model('Categories')->tmpInstance();

		try
		{
			$category = $category->findOrFail($item->catid);
		}
		catch (RecordNotLoaded $e)
		{
			return null;
		}

		// Get all item entries ordered by ordering ascending (newest release first)
		$db      = $this->container->db;
		$query   = $db->getQuery(true)
			->select([
				$db->qn('r.id'),
				$db->qn('r.version'),
				$db->qn('i.environments'),
			])
			->from($db->qn('#__ars_releases') . ' AS ' . $db->qn('r'))
			->innerJoin($db->qn('#__ars_items') . ' AS ' . $db->qn('i') . ' ON (' .
				$db->qn('i.release_id') . ' = ' . $db->qn('r.id') .
				')'
			)
			->where($db->qn('r.category_id') . ' = ' . $db->q($category_id))
			->where($db->qn('r.published') . ' = ' . $db->q(1))
			->where($db->qn('r.maturity') . ' = ' . $db->q('stable'))
			->where($db->qn('i.published') . ' = ' . $db->q(1))
			->where($db->qn('environments') . ' IS NOT NULL')
			->order($db->qn('r.ordering') . ' ASC');
		$entries = $db->setQuery($query)->loadAssocList();

		// Index releases by environment and version
		$byEnvironment  = [];
		$versionNumbers = [];

		foreach ($entries as $entry)
		{
			// Get the entry's information
			$id           = $entry['id'];
			$version      = $entry['version'];
			$environments = json_decode($entry['environments']);

			// If there are no environments skip this entry.
			if (empty($environments))
			{
				continue;
			}

			// Cache the ID to version number mapping
			$versionNumbers[$id] = $version;

			// Cache the environment to version ID mapping
			foreach ($environments as $e)
			{
				if (!isset($byEnvironment[$e]))
				{
					$byEnvironment[$e] = [];
				}

				if (!in_array($id, $byEnvironment[$e]))
				{
					$byEnvironment[$e][] = $id;
				}
			}
		}

		$phpVersions       = $this->getPHPVersions();
		$joomlaVersions    = $this->getJoomlaVersions();
		$wordPressVersions = $this->getWordPressVersions();

		// Which CMS does this extension belong to?
		$cmsVersions = [];
		$cmsType     = 'Standalone';
		$allEID      = array_keys($byEnvironment);

		if (!empty(array_intersect($allEID, $joomlaVersions)))
		{
			$cmsType     = 'Joomla';
			$cmsVersions = $joomlaVersions;
		}
		elseif (!empty(array_intersect($allEID, $wordPressVersions)))
		{
			$cmsType     = 'WordPress';
			$cmsVersions = $wordPressVersions;
		}

		// Create a fake CMS version entry for PHP-only software (e.g. Akeeba Solo)
		if (empty($cmsVersions))
		{
			$cmsVersions = ['' => ''];
		}

		// Order all versions ascending
		$cmsVersions = $this->orderVersions($cmsVersions);
		$phpVersions = $this->orderVersions($phpVersions);

		// Prepare the matrix
		$matrix = [];

		foreach ($cmsVersions as $cmsVersion => $cmsEnvId)
		{
			if (empty($cmsEnvId))
			{
				$releasesForCms = array_keys($versionNumbers);
			}
			elseif (!isset($byEnvironment[$cmsEnvId]))
			{
				continue;
			}
			else
			{
				$releasesForCms = $byEnvironment[$cmsEnvId];
			}

			$matrix[$cmsVersion] = [];


			foreach ($phpVersions as $phpVersion => $phpEnvId)
			{
				$matrix[$cmsVersion][$phpVersion] = [];

				if (!$this->allowedCMSAndPHPCombination($cmsType, $cmsVersion, $phpVersion))
				{
					continue;
				}

				if (!isset($byEnvironment[$phpEnvId]))
				{
					continue;
				}

				$releasesForPHP = $byEnvironment[$phpEnvId];
				$commonReleases = array_intersect($releasesForCms, $releasesForPHP);

				if (empty($commonReleases))
				{
					continue;
				}

				$latestReleaseId = array_shift($commonReleases);

				$matrix[$cmsVersion][$phpVersion] = [
					'id'      => $latestReleaseId,
					'version' => $versionNumbers[$latestReleaseId],
					'link'    => Route::_('index.php?option=com_ars&view=Items&release_id=' . $latestReleaseId),
				];
			}
		}

		$versionIDs    = array_keys($versionNumbers);
		$latestVersion = $versionNumbers[array_shift($versionIDs)];

		$matrix              = $this->postProcessMatrix($matrix);
		$reportedPHPVersions = [];

		if (!empty($matrix))
		{
			$keys                = array_keys($matrix);
			$firstKey            = array_shift($keys);
			$reportedPHPVersions = array_keys($matrix[$firstKey]);
		}

		return [
			'type'   => $cmsType,
			'latest' => $latestVersion,
			'php'    => $reportedPHPVersions,
			'matrix' => $matrix,
			'logo'   => sprintf("<span class=\"%s\"></span>", $item->icon ?: 'aklogo-company-logo'),
			'title'  => $item->title ?: $category->title,
			'link'   => Route::_('index.php?option=com_ars&view=Releases&category_id=' . $item->catid),
			'slug'   => $category->alias,
		];
	}

	/**
	 * Get the PHP versions mapping (version to ARS environment ID)
	 *
	 * @return  array
	 */
	protected function getPHPVersions()
	{
		static $versions = null;

		if (is_null($versions))
		{
			$prefix   = 'php/';
			$versions = $this->loadVersionsFromEnvironment($prefix);

			$excluded = $this->container->params->get('exclude_php');

			$temp = [];

			foreach ($versions as $v => $id)
			{
				if (in_array($id, $excluded))
				{
					continue;
				}

				$temp[$v] = $id;
			}

			$versions = $temp;
		}

		return $versions;
	}

	/**
	 * Get the Joomla versions mapping (version to ARS environment ID)
	 *
	 * @return  array
	 */
	protected function getJoomlaVersions()
	{
		static $versions = null;

		if (is_null($versions))
		{
			$prefix   = 'joomla/';
			$versions = $this->loadVersionsFromEnvironment($prefix);
		}

		return $versions;
	}

	/**
	 * Get the WordPress versions mapping (version to ARS environment ID)
	 *
	 * @return  array
	 */
	protected function getWordPressVersions()
	{
		static $versions = null;

		if (is_null($versions))
		{
			$versions = [];

			$prefix = 'classicpress/';
			$temp   = $this->loadVersionsFromEnvironment($prefix);

			foreach ($temp as $k => $v)
			{
				$versions['CP' . $k] = $v;
			}

			$prefix = 'wordpress/';
			$temp   = $this->loadVersionsFromEnvironment($prefix);

			foreach ($temp as $k => $v)
			{
				$versions['WP' . $k] = $v;
			}
		}

		return $versions;
	}

	/**
	 * Order a version to environment ID map by version ascending
	 *
	 * @param   array  $versions  The version to environment ID map to sort
	 *
	 * @return  array  The sorted map
	 */
	protected function orderVersions($versions)
	{
		$keys = array_keys($versions);
		usort($keys, 'version_compare');

		$temp = [];

		foreach ($keys as $k)
		{
			$temp[$k] = $versions[$k];
		}

		return $temp;
	}

	/**
	 * Loads a set of versions from the ARS environments stored in the database.
	 *
	 * @param   string  $prefix  The common prefix preceding the version name in the environment's xmltitle field
	 *
	 * @return  array  An associative array version => environment ID
	 */
	protected function loadVersionsFromEnvironment($prefix): array
	{
		$allVersions = [];

		$db    = $this->container->db;
		$query = $db->getQuery(true)
			->select([
				$db->qn('id'),
				$db->qn('xmltitle'),
			])
			->from($db->qn('#__ars_environments'))
			->where($db->qn('xmltitle') . ' LIKE ' . $db->q('' . $prefix . '%'));

		$entries = $db->setQuery($query)->loadAssocList('id', 'xmltitle');

		foreach ($entries as $eid => $version)
		{
			$version               = str_replace($prefix, '', $version);
			$allVersions[$version] = $eid;
		}

		return $allVersions;
	}

	/**
	 * Do we have an allowed CMS and PHP combination?
	 *
	 * Different Joomla! versions have different minimum and maximum PHP requirements. While our software may be
	 * compatible with a wide range of CMS and PHP versions we can't list PHP/Joomla combinations which would cause
	 * Joomla! to die unceremoniously. For example, Akeeba Backup 5.3 supports PHP 7.0 and Joomla! 3.3 to 3.7 but
	 * Joomla! itself didn't support PHP 7.0 until its 3.5 release.
	 *
	 * @param   string  $cmsType
	 * @param   string  $cmsVersion
	 * @param   string  $phpVersion
	 *
	 * @return  bool
	 */
	protected function allowedCMSAndPHPCombination(string $cmsType, string $cmsVersion, string $phpVersion): bool
	{
		$this->loadCMSRules();

		$cmsType = strtolower($cmsType);

		// Standalone software is exempt from this check
		if (strpos($cmsType, 'alone'))
		{
			return true;
		}

		// WordPress and ClassicPress require special pre-processing

		// WordPress has no minimum / maximum PHP versions posted anywhere as far as I know
		if (strpos($cmsType, 'wordpress') !== false)
		{
			switch (strtoupper(substr($cmsVersion, 0, 2)))
			{
				case 'CP':
					$cmsType    = 'cp';
					$cmsVersion = substr($cmsVersion, 2);
					$cmsVersion = trim($cmsVersion, '+ /');

					break;

				case 'WP':
					$cmsType    = 'wp';
					$cmsVersion = substr($cmsVersion, 2);
					$cmsVersion = trim($cmsVersion, '+ /');

					break;

				default:
					$cmsType = 'wp';
			}
		}

		if (!isset($this->cmsRules[$cmsType]))
		{
			return false;
		}

		foreach ($this->cmsRules[$cmsType] as $maxCMSVersion => $phpVersions)
		{
			// Only process rules matching a CMS version that's lower or equal to the CMS version being listed here.
			if (version_compare($cmsVersion, $maxCMSVersion, 'gt'))
			{
				continue;
			}

			if (!empty($phpVersions[0]) && version_compare($phpVersion, $phpVersions[0], 'lt'))
			{
				continue;
			}

			if (!empty($phpVersions[1]) && version_compare($phpVersion, $phpVersions[1], 'gt'))
			{
				continue;
			}

			return true;
		}

		return false;
	}

	/**
	 * Load the CMS and PHP version compatibility rules
	 */
	private function loadCMSRules(): void
	{
		if (!is_null($this->cmsRules))
		{
			return;
		}

		$this->cmsRules = [
			'joomla' => [],
			'cp'     => [],
			'wp'     => [],
		];

		$nullObject = new \stdClass();
		$cmsRules   = $this->container->params->get('cms', $nullObject);
		$cmsRules   = array_map(function (array $rule) {
			$rule = (object) $rule;

			return (object) [
				'type'    => $rule->type ?? '',
				'version' => $rule->version ?? '0.0.999',
				'min'     => $rule->min ?? '999.999.999',
				'max'     => $rule->max ?? '999.999.999',
			];
		}, ArrayHelper::fromObject($cmsRules));

		foreach ($cmsRules as $rule)
		{
			$this->cmsRules[$rule->type][$rule->version] = [$rule->min, $rule->max];
		}
	}

	private function postProcessMatrix(array $matrix)
	{
		// Remove empty rows
		$matrix = array_filter($matrix, function (array $row): bool {
			foreach ($row as $column)
			{
				if (!empty($column))
				{
					return true;
				}
			}

			return false;
		});

		if (empty($matrix))
		{
			return $matrix;
		}

		// Let's see which columns should be included
		$keys        = array_keys($matrix);
		$firstKey    = array_shift($keys);
		$phpVersions = array_keys($matrix[$firstKey]);
		$columnMap   = array_combine($phpVersions, array_fill(0, count($phpVersions), false));

		array_map(function ($row) use (&$columnMap) {
			$thisMap = array_map(function ($innerValue) {
				return !empty($innerValue);
			}, $row);

			foreach ($thisMap as $k => $v)
			{
				$columnMap[$k] = $v || $columnMap[$k];
			}
		}, $matrix);

		// Create a dumb array with the accepted column tags (PHP versions)
		$acceptedColumns = array_keys(array_filter($columnMap, function ($v) {
			return $v;
		}));

		// Let's reconstruct the matrix keeping only the accepted columns
		return array_map(function ($row) use ($acceptedColumns) {
			$temp = [];

			foreach ($acceptedColumns as $phpVer)
			{
				$temp[$phpVer] = $row[$phpVer];
			}

			return $temp;
		}, $matrix);
	}
}
