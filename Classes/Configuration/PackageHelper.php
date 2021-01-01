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

use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Package\Exception\UnknownPackageException;
use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Package\PackageManager;
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

    public function getSitePackage(int $pageId): ?PackageInterface
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
