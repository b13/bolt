<?php
defined('TYPO3_MODE') or die();

$GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] .= ',site';

// Register autoloading for TypoScript
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Core/TypoScript/TemplateService']['runThroughTemplatesPostProcessing']['bolt'] = \CMSExperts\Bolt\TypoScript\Loader::class . '->addSiteConfiguration';
// For everything lower than TYPO3 v8, use an XCLASS
if (version_compare(TYPO3_version, '8.5.0') < 0) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\TypoScript\TemplateService::class]['className'] = \CMSExperts\Bolt\TypoScript\TemplateService::class;
}

// Register autoloading of pageTSconfig
\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)->connect(
    \TYPO3\CMS\Backend\Utility\BackendUtility::class,
    'getPagesTSconfigPreInclude',
    CMSExperts\Bolt\TsConfig\Loader::class,
    'addSiteConfiguration'
);