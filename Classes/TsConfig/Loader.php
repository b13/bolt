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
use TYPO3\CMS\Core\Configuration\Event\ModifyLoadedPageTsConfigEvent;

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

    public function __invoke(ModifyLoadedPageTsConfigEvent $event): void
    {
        $rootLine = $event->getRootLine();
        $tsConfig = $event->getTsConfig();
        foreach ($rootLine as $level => $pageRecord) {
            $package = $this->packageHelper->getSitePackage($pageRecord['uid']);
            if ($package !== null) {
                $tsConfigFile = $package->getPackagePath() . 'Configuration/PageTs/main.tsconfig';
                if (!file_exists($tsConfigFile)) {
                    $tsConfigFile = $package->getPackagePath() . 'Configuration/PageTsConfig/main.tsconfig';
                }
                if (file_exists($tsConfigFile)) {
                    $fileContents = @file_get_contents($tsConfigFile);
                    $tsConfig['uid_' . $pageRecord['uid']] .= LF . $fileContents;
                }
            }
        }
        $event->setTsConfig($tsConfig);
    }
}
