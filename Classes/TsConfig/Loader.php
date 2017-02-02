<?php
namespace CMSExperts\Bolt\TsConfig;

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
use CMSExperts\Bolt\Configuration\PackageHelper;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Dynamically loads PageTSconfig from an extension. Is added AFTER a site's
 * The File needs to be put into
 * EXT:site_mysite/Configuration/PageTsConfig/main.tsconfig
 */
class Loader
{
    /**
     * Adds TSconfig
     *
     * @param array $TSdataArray
     * @param int $id
     * @param array $rootLine
     * @param array $returnPartArray
     * @return array
     */
    public function addSiteConfiguration($TSdataArray, $id, $rootLine, $returnPartArray)
    {
        $packageHelper = GeneralUtility::makeInstance(PackageHelper::class);
        foreach ($rootLine as $level => $pageRecord) {
            $pageSiteRecord = BackendUtility::getRecord('pages', $pageRecord['uid'], 'site');
            $package = $packageHelper->getPackageFromPageRecord($pageSiteRecord);
            if ($package) {
                $tsConfigFile = $package->getPackagePath() . 'Configuration/PageTs/main.tsconfig';
                if (!file_exists($tsConfigFile)) {
                    $tsConfigFile = $package->getPackagePath() . 'Configuration/PageTsConfig/main.tsconfig';
                }
                if (file_exists($tsConfigFile)) {
                    $fileContents = @file_get_contents($tsConfigFile);
                    $TSdataArray['uid_' . $pageRecord['uid']] .= LF . $fileContents;
                }
            }
        }
        return [$TSdataArray, $id, $rootLine, $returnPartArray];
    }
}
