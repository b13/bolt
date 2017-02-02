<?php
namespace CMSExperts\Bolt\Configuration;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Helper to render a dynamic selection of available site extensions
 * and to fetch a package for certain page
 */
class PackageHelper
{

    /**
     * Fetches a package object from a pages.site variable, if given and installed
     *
     * @param $pageRecord
     * @return PackageInterface
     */
    public function getPackageFromPageRecord($pageRecord)
    {
        $package = null;
        if ($pageRecord['site']) {
            $packageName = $pageRecord['site'];

            $packageManager = GeneralUtility::makeInstance(PackageManager::class);
            $activePackages = $packageManager->getActivePackages();
            foreach ($activePackages as $activePackage) {
                if ($activePackage->getPackageKey() === $packageName ||
                    $activePackage->getValueFromComposerManifest('name') === $packageName) {
                    $package = $activePackage;
                    break;
                }
            }
        }
        return $package;
    }

    /**
     * Items proc func for selecting a record
     *
     * @param $configuration
     */
    public function findAllSitePackages(&$configuration)
    {
        $configuration['items'][] = [' -- none --', '0'];
        /** @var PackageManager $packageManager */
        $packageManager = GeneralUtility::makeInstance(PackageManager::class);
        foreach ($packageManager->getActivePackages() as $package) {
            if ($package->getValueFromComposerManifest('type') === 'typo3-cms-site'
                || (strpos($package->getPackageKey(), 'site_') === 0)
                || strpos($package->getPackageKey(), 'theme_') === 0
                ) {
                $configuration['items'][] = [$package->getPackageMetaData()->getDescription() . ' (' . $package->getValueFromComposerManifest('name') . ')', $package->getValueFromComposerManifest('name')];
            }
        }
    }
}