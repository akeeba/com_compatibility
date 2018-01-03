<?php
/**
 * @package		com_compatibility
 * @copyright	Copyright (c)2017-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */

namespace Akeeba\Compatibility\Site\Model;

use Akeeba\ReleaseSystem\Site\Model\Categories;
use Akeeba\ReleaseSystem\Site\Model\Items;
use FOF30\Container\Container;
use FOF30\Model\Model;
use JRoute;

class Compatibility extends Model
{
	public function getMatrix($category_id)
	{
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
			$cmsType     = 'Joomla!';
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
					'link'    => JRoute::_('index.php?option=com_ars&view=Items&release_id=' . $latestReleaseId),
				];
			}
		}

		$versionIDs = array_keys($versionNumbers);

		return [
			'type'   => $cmsType,
			'latest' => $versionNumbers[array_shift($versionIDs)],
			'php'    => array_keys($phpVersions),
			'matrix' => $matrix,
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
			$prefix      = 'php/';
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
			$prefix      = 'joomla/';
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
			$prefix      = 'wordpress/';
			$versions = $this->loadVersionsFromEnvironment($prefix);
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
	protected function allowedCMSAndPHPCombination($cmsType, $cmsVersion, $phpVersion)
	{
		$cmsType = strtolower($cmsType);

		// Standalone software is exempt from this check
		if (strpos($cmsType, 'alone'))
		{
			return true;
		}

		// WordPress has no minimum / maximum PHP versions posted anywhere as far as I know
		if (strpos($cmsType, 'wordpress'))
		{
			return true;
		}

		// Joomla minimum requirements, see https://downloads.joomla.org/technical-requirements
		$minPHP = '5.3.10';
		$maxPHP = '7.9.999';

		if (version_compare($cmsVersion, '1.5.999', 'lt'))
		{
			// Joomla! 1.5 - PHP 4.3.10 to 5.5
			$minPHP = '4.3.10';
			$maxPHP = '5.5.999';
		}
		elseif (version_compare($cmsVersion, '1.7.999', 'lt'))
		{
			// Joomla! 1.6, 1.7 - PHP 5.2 to 5.5
			$minPHP = '5.2.4';
			$maxPHP = '5.5.999';
		}
		elseif (version_compare($cmsVersion, '2.5.999', 'lt'))
		{
			// Joomla! 2.5 - PHP 5.2 to 5.6
			$minPHP = '5.2.4';
			$maxPHP = '5.6.999';
		}
		elseif (version_compare($cmsVersion, '3.2.999', 'lt'))
		{
			// Joomla! 3.0 to 3.2 - PHP 5.3.0 to 5.6
			$minPHP = '5.3.1';
			$maxPHP = '5.6.999';
		}
		elseif (version_compare($cmsVersion, '3.2.999', 'lt'))
		{
			// Joomla! 3.3, 3.4 - PHP 5.3.10 to 5.6
			$minPHP = '5.3.10';
			$maxPHP = '5.6.999';
		}
		elseif (version_compare($cmsVersion, '3.6.999', 'lt'))
		{
			// Joomla! 3.5, 3.6 - PHP 5.3.10 to 7.0
			$minPHP = '5.3.10';
			$maxPHP = '7.0.999';
		}
		elseif (version_compare($cmsVersion, '3.999.999', 'lt'))
		{
			// Joomla! 3.7 and later 3.x - PHP 5.3.10 to 7.1
			$minPHP = '5.3.10';
			$maxPHP = '7.1.999';
		}

		$parts = explode('.', $phpVersion);
		$phpVersion = $parts[0] . '.' . $parts[1] . '.99';

		return version_compare($phpVersion, $minPHP, 'ge') && version_compare($phpVersion, $maxPHP, 'le');
	}
}
