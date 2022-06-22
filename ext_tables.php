<?php

defined('TYPO3_MODE') or die();

// disable sys_templates - could be done as an option of the extension dynamically
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
    mod {
        web_list.deniedNewTables := addToList(sys_template)
        web_ts.menu.function {
            TYPO3\CMS\Tstemplate\Controller\TypoScriptTemplateConstantEditorModuleFunctionController = 0
            TYPO3\CMS\Tstemplate\Controller\TypoScriptTemplateInformationModuleFunctionController = 0
        }
    }
    TCEFORM.pages.TSconfig.disabled=1
');
