<?php

/*
 * This file is part of TYPO3 CMS-based extension "bolt" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

$GLOBALS['SiteConfiguration']['site']['columns']['sitePackage'] = [
    'label' => 'Site package of this site',
    'description' => '[EXT:bolt] Attached site extension with TypoScript and TsConfig entry points',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'itemsProcFunc' => \B13\Bolt\Configuration\PackageHelper::class . '->getSiteListForSiteModule',
    ],
];
$GLOBALS['SiteConfiguration']['site']['palettes']['default']['showitem'] .= ',
    sitePackage,
';
