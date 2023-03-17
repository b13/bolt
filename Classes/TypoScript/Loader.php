<?php

declare(strict_types=1);

namespace B13\Bolt\TypoScript;

/*
 * This file is part of TYPO3 CMS-based extension "bolt" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Bolt\Configuration\PackageHelper;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hooks into the process of building TypoScript templates.
 * Works with TYPO3 >= 8.6.0 and <= 11, with TYPO3 v12 event kicks in.
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

    /**
     *  $hookParameters = [
     *      'extensionStaticsProcessed' => &$this->extensionStaticsProcessed,
     *      'isDefaultTypoScriptAdded'  => &$this->isDefaultTypoScriptAdded,
     *      'absoluteRootLine' => &$this->absoluteRootLine,
     *      'rootLine'         => &$this->rootLine,
     *      'startTemplateUid' => $start_template_uid,
     *  ];
     * @param array $hookParameters
     * @param TemplateService $templateService
     */
    public function addSiteConfiguration(&$hookParameters, TemplateService $templateService): void
    {
        // let's copy the rootline value, as $templateService->processTemplate() might reset it
        $rootLine = $hookParameters['rootLine'] ?? null;
        if (!is_array($rootLine) || empty($rootLine)) {
            return;
        }

        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() >= 12) {
            // TYPO3 12.0 still has hook 'runThroughTemplatesPostProcessing', but we use the
            // event already. This if can be removed when TYPO3 12.1 has been released and
            // the hook is removed in the core.
            return;
        }

        foreach ($rootLine as $level => $pageRecord) {
            $package = $this->packageHelper->getSitePackage((int)$pageRecord['uid']);
            if ($package !== null) {
                $constantsFile = $package->getPackagePath() . 'Configuration/TypoScript/constants.typoscript';
                $setupFile = $package->getPackagePath() . 'Configuration/TypoScript/setup.typoscript';
                if (!file_exists($constantsFile)) {
                    $constantsFile = $package->getPackagePath() . 'Configuration/TypoScript/constants.txt';
                }
                if (!file_exists($setupFile)) {
                    $setupFile = $package->getPackagePath() . 'Configuration/TypoScript/setup.txt';
                }

                if (file_exists($constantsFile)) {
                    $constants = (string)@file_get_contents($constantsFile);
                } else {
                    $constants = '';
                }
                if (file_exists($setupFile)) {
                    $setup = (string)@file_get_contents($setupFile);
                } else {
                    $setup = '';
                }

                $hasRootTemplate = (bool)$templateService->getRootId();
                $fakeRow = [
                    'config' => $setup,
                    'constants' => $constants,
                    'nextLevel' => 0,
                    'static_file_mode' => 1,
                    'tstamp' => $setup ? filemtime($setupFile) : time(),
                    'uid' => 'sys_bolt_' . (int)$pageRecord['uid'] . $package->getPackageKey(),
                    'title' => $package->getPackageKey(),
                    // make this the root template
                    'root' => !$hasRootTemplate,
                ];
                $templateService->processTemplate($fakeRow, 'sys_bolt_' . $package->getPackageKey(), (int)$pageRecord['uid'], 'sys_bolt_' . $package->getPackageKey());

                if (!$hasRootTemplate) {
                    // $templateService->processTemplate() adds the constants and setup info
                    // to the very end however, we like to add ours add root template
                    array_pop($templateService->constants);
                    array_unshift($templateService->constants, $constants);
                    array_pop($templateService->config);
                    array_unshift($templateService->config, $setup);
                    // when having the 'root' flag, set $processTemplate resets the rootline -> we don't want that.
                    $hookParameters['rootLine'] = $rootLine;
                }
            }
        }
    }
}
