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
use TYPO3\CMS\Backend\Utility\BackendUtility;
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
        $event->setTsConfig(
            $this->findAndAddConfiguration(
                $event->getRootLine(),
                $event->getTsConfig()
            )
        );
    }

    public function addSiteConfiguration(ModifyLoadedPageTsConfigEvent $event): void
    {
        $event->setTsConfig(
            $this->findAndAddConfiguration(
                $event->getRootLine(),
                $event->getTsConfig()
            )
        );
    }

    protected function findAndAddConfiguration(array $rootLine, array $tsConfig): array
    {
        foreach ($rootLine as $pageRecord) {
            $package = $this->packageHelper->getSitePackage((int)$pageRecord['uid']);
            if ($package === null && ($pageRecord['is_siteroot'] ?? false)) {
                // Translations of site roots will yield no $package when looking by root page or pageId
                $fullPageRecord = BackendUtility::getRecord('pages', (int)$pageRecord['uid']);
                $transOrigPointerField = $GLOBALS['TCA']['pages']['ctrl']['transOrigPointerField'] ?? 'l10n_parent';
                if ($fullPageRecord[$transOrigPointerField] ?? false) {
                    $package = $this->packageHelper->getSitePackage((int)$fullPageRecord[$transOrigPointerField]);
                }
            }
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
        return $tsConfig;
    }
}
