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
            'itemsProcFunc' => \CMSExperts\Bolt\Configuration\PackageHelper::class . '->findAllSitePackages'
        ]
    ]
]);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', 'site', '', 'after:title');