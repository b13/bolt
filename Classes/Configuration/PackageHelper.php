<?php

declare(strict_types=1);

namespace B13\Bolt\Configuration;

/*
 * This file is part of TYPO3 CMS-based extension "bolt" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Package\Exception\UnknownPackageException;
use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;

/**
 * Helper to render a dynamic selection of available site extensions
 * and to fetch a package for certain page
 */
class PackageHelper
{
    /**
     * @var PackageManager
     */
    protected $packageManager;

    /**
     * @var SiteFinder
     */
    protected $siteFinder;

    public function __construct(PackageManager $packageManager, SiteFinder $siteFinder)
    {
        $this->packageManager = $packageManager;
        $this->siteFinder = $siteFinder;
    }

    public function getSitePackage(int $rootPageId): ?PackageInterface
    {
        try {
            return $this->getSitePackageFromSite(
                $this->siteFinder->getSiteByRootPageId($rootPageId)
            );
        } catch (SiteNotFoundException $e) {
            return null;
        }
    }

    public function getSitePackageFromSite(Site $site): ?PackageInterface
    {
        $configuration = $site->getConfiguration();
        if (!isset($configuration['sitePackage'])) {
            return null;
        }
        $packageKey = (string)$configuration['sitePackage'];
        try {
            return $this->packageManager->getPackage($packageKey);
        } catch (UnknownPackageException $_) {
            return null;
        }
    }

    /**
     * "itemsProcFunc" method adding a list of available "site_*" extension
     * keys as select drop down items. Used in Site backend module.
     */
    public function getSiteListForSiteModule(array &$fieldDefinition): void
    {
        $fieldDefinition['items'][] = [
            '-- None --',
            '',
        ];
        $currentValue = $fieldDefinition['row']['sitePackage'] ?? '';
        $gotCurrentValue = false;
        foreach ($this->packageManager->getActivePackages() as $package) {
            if ($package->getPackageMetaData()->getPackageType() === 'typo3-cms-site') {
                $packageKey = $package->getPackageKey();
                $fieldDefinition['items'][] = [
                    0 => $packageKey,
                    1 => $packageKey,
                ];
                if ($currentValue === $packageKey) {
                    $gotCurrentValue = true;
                }
            }
        }
        if (!$gotCurrentValue && $currentValue !== '') {
            $fieldDefinition['items'][] = [
                0 => $currentValue,
                1 => $currentValue,
            ];
        }
    }
}
