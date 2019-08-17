<?php
namespace B13\Bolt\Configuration;

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

use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Package\Exception\UnknownPackageException;
use TYPO3\CMS\Core\Package\Package;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * Helper to render a dynamic selection of available site extensions
 * and to fetch a package for certain page
 */
class PackageHelper
{

    /**
     * @var PackageManager
     */
    protected $packageManager = null;

    /**
     * @var SiteFinder
     */
    protected $siteFinder = null;

    /**
     * PackageHelper constructor.
     * @param null|PackageManager $packageManager
     * @param null|SiteFinder $siteFinder
     */
    public function __construct(PackageManager $packageManager = null, SiteFinder $siteFinder = null)
    {
        $this->packageManager = $packageManager ?? GeneralUtility::makeInstance(PackageManager::class);
        $this->siteFinder = $siteFinder ?? GeneralUtility::makeInstance(SiteFinder::class);
    }


    /**
     * @param int $pageId
     * @return null|Package
     */
    public function getSitePackage(int $pageId): ?Package
    {
        try {
            $site = $this->siteFinder->getSiteByRootPageId($pageId);
            $configuration = $site->getConfiguration();
            $packageKey = (string)$configuration['sitePackage'];
            return $this->packageManager->getPackage($packageKey);
        } catch (SiteNotFoundException $e) {
            return null;
        } catch (UnknownPackageException $e) {
            return null;
        }
    }
}
