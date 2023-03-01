<?php

defined('TYPO3') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
    # Disable adding new sys_template records in list module
    mod.web_list.deniedNewTables := addToList(sys_template)

    # Hide tstemplate "Info/Modify" and "Constant Editor" in core v10 & v11
    mod.web_ts.menu.function.TYPO3\CMS\Tstemplate\Controller\TypoScriptTemplateConstantEditorModuleFunctionController = 0
    mod.web_ts.menu.function.TYPO3\CMS\Tstemplate\Controller\TypoScriptTemplateInformationModuleFunctionController = 0

    # Hide TSconfig and tsconfig_includes fields when editing pages
    TCEFORM.pages.TSconfig.disabled=1
    TCEFORM.pages.tsconfig_includes.disabled=1
');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('
    # Hide tstemplate "Edit TypoScript record" and "Constant Editor" in core v12
    options.hideModules := addToList(web_typoscript_infomodify, web_typoscript_constanteditor)
');
