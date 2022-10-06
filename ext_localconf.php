<?php

defined('TYPO3') or die();

// Register autoloading for TypoScript
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Core/TypoScript/TemplateService']['runThroughTemplatesPostProcessing']['bolt'] = \B13\Bolt\TypoScript\Loader::class . '->addSiteConfiguration';
