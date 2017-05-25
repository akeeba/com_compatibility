<?php
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
}