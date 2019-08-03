<?php
namespace B13\Bolt\TypoScript;

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

use B13\Bolt\Configuration\PackageHelper;
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
        // let's copy the rootline value, as $templateService->processTemplate() might reset it
        $rootLine = $hookParameters['rootLine'];
        if (!is_array($rootLine) || empty($rootLine)) {
            return;
        }
        $packageHelper = GeneralUtility::makeInstance(PackageHelper::class);
        foreach ($rootLine as $level => $pageRecord) {
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
                $hasRootTemplate = (bool)$this->getRootId($templateService);
                $fakeRow = [
                    'config' => $setup,
                    'constants' => $constants,
                    'nextLevel' => 0,
                    'static_file_mode' => 1,
                    'tstamp' => $setup ? filemtime($setupFile) : time(),
                    'uid' => 'sys_bolt_' . $package->getPackageKey(),
                    'title' => $package->getPackageKey(),
                    // make this the root template
                    'root' => !$hasRootTemplate
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

    /**
     * $templateService->rootId is protected in TYPO3 v9, so it has to be evaluated differently.
     *
     * @param TemplateService $templateService
     * @return int
     */
    protected function getRootId(TemplateService $templateService)
    {
        if (method_exists($templateService, 'getRootId')) {
            return $templateService->getRootId();
        }
        // v8
        // @extensionScannerIgnoreLine
        return $templateService->rootId;
    }
}
