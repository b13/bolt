<?php
namespace CMSExperts\Bolt\TypoScript;

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
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hooks into the process of building TypoScript templates
 * only works with TYPO3 v8.6.0 natively, otherwise the XCLASS kicks in
 */
class Loader
{
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
     * @return void
     */
    public function addSiteConfiguration(&$hookParameters, $templateService)
    {
        $packageHelper = GeneralUtility::makeInstance(PackageHelper::class);
        if (is_array($hookParameters['rootLine'])) {
            foreach ($hookParameters['rootLine'] as $level => $pageRecord) {
                $package = $packageHelper->getPackageFromPageRecord($pageRecord);
                if ($package) {

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

                    // pre-process the lines of the constants and setup and check for "@" syntax
                    // @import
                    // @sitetitle
                    // @clear
                    // are the currently allowed syntax (must be on the head of each line)

                    $fakeRow = [
                        'config' => $setup,
                        'constants' => $constants,
                        'nextLevel' => 0,
                        'static_file_mode' => 1,
                        'tstamp' => filemtime($setupFile),
                        'uid' => 'sys_bolt_' . $package->getPackageKey(),
                        'title' => $package->getPackageKey()
                    ];
                    $templateService->processTemplate($fakeRow, 'sys_bolt_' . $package->getPackageKey(), $pageRecord['uid'], 'sys_bolt_' . $package->getPackageKey());
                    if (!$templateService->rootId) {
                        $templateService->rootId = $pageRecord['uid'];
                    }
                }
            }
        }
    }
}
