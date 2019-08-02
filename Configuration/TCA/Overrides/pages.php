<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', [
    'site' => [
        'label' => 'Choose site',
        'displayCond' => 'FIELD:is_siteroot:=:1',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'size' => 1,
            'itemsProcFunc' => \B13\Bolt\Configuration\PackageHelper::class . '->findAllSitePackages'
        ]
    ]
]);

# Ensure that site is not visible for page translations
if (version_compare(TYPO3_version, '9.0', '>=')) {
    $GLOBALS['TCA']['pages']['columns']['site']['l10n_mode'] = 'exclude';
}


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', 'site', '', 'after:title');
