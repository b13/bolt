<?php

declare(strict_types=1);

namespace B13\Bolt\TsConfig;

/*
 * This file is part of TYPO3 CMS-based extension "bolt" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Bolt\Configuration\PackageHelper;
use TYPO3\CMS\Core\Configuration\Event\ModifyLoadedPageTsConfigEvent as LegacyModifyLoadedPageTsConfigEvent;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Event\ModifyLoadedPageTsConfigEvent;

/**
 * Dynamically loads PageTSconfig from an extension. Is added AFTER a site's
 * The File needs to be put into
 * EXT:site_mysite/Configuration/PageTsConfig/main.tsconfig
 */
class Loader
{
    /**
     * @var PackageHelper
     */
    protected $packageHelper;

    public function __construct(PackageHelper $packageHelper)
    {
        $this->packageHelper = $packageHelper;
    }

    public function addSiteConfigurationCore11(LegacyModifyLoadedPageTsConfigEvent $event): void
    {
        if (class_exists(ModifyLoadedPageTsConfigEvent::class)) {
            // TYPO3 v12 calls both old and new event. Check for class existence of new event to
            // skip handling of old event in v12, but continue to work with < v12.
            // Simplify this construct when v11 compat is dropped, clean up Services.yaml.
            return;
        }
        $this->findAndAddConfiguration($event);
    }

    public function addSiteConfiguration(ModifyLoadedPageTsConfigEvent $event): void
    {
        $this->findAndAddConfiguration($event);
    }

    protected function findAndAddConfiguration($event): void
    {
        $rootLine = $event->getRootLine();
        $tsConfig = $event->getTsConfig();
        foreach ($rootLine as $pageRecord) {
            $package = $this->packageHelper->getSitePackage($pageRecord['uid']);
            if ($package !== null) {
                $tsConfigFile = $package->getPackagePath() . 'Configuration/PageTs/main.tsconfig';
                if (!file_exists($tsConfigFile)) {
                    $tsConfigFile = $package->getPackagePath() . 'Configuration/PageTsConfig/main.tsconfig';
                }
                if (file_exists($tsConfigFile)) {
                    $fileContents = @file_get_contents($tsConfigFile);
                    if (!isset($tsConfig['uid_' . $pageRecord['uid']])) {
                        $tsConfig['uid_' . $pageRecord['uid']] = '';
                    }
                    $tsConfig['uid_' . $pageRecord['uid']] .= LF . $fileContents;
                }
            }
        }
        $event->setTsConfig($tsConfig);
    }
}
