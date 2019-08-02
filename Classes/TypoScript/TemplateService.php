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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * XCLASS to template service
 *
 * as long as there is no hook the template service is extended to allow our logic
 */
class TemplateService extends \TYPO3\CMS\Core\TypoScript\TemplateService
{
    /**
     * Traverses the rootLine from the root and out. For each page it checks if there is a template record. If there is a template record, $this->processTemplate() is called.
     * Resets and affects internal variables like $this->constants, $this->config and $this->rowSum
     * Also creates $this->rootLine which is a root line stopping at the root template (contrary to $this->getTypoScriptFrontendController()->rootLine which goes all the way to the root of the tree
     *
     * @param array $theRootLine The rootline of the current page (going ALL the way to tree root)
     * @param int $start_template_uid Set specific template record UID to select; this is only for debugging/development/analysis use in backend modules like "Web > Template". For parsing TypoScript templates in the frontend it should be 0 (zero)
     * @return void
     * @see start()
     */
    public function runThroughTemplates($theRootLine, $start_template_uid = 0)
    {
        $this->constants = [];
        $this->config = [];
        $this->rowSum = [];
        $this->hierarchyInfoToRoot = [];
        $this->absoluteRootLine = $theRootLine;
        $this->isDefaultTypoScriptAdded = false;

        reset($this->absoluteRootLine);
        $c = count($this->absoluteRootLine);
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_template');
        for ($a = 0; $a < $c; $a++) {
            // If some template loaded before has set a template-id for the next level, then load this template first!
            if ($this->nextLevel) {
                $queryBuilder->setRestrictions($this->queryBuilderRestrictions);
                $queryResult = $queryBuilder
                    ->select('*')
                    ->from('sys_template')
                    ->where(
                        $queryBuilder->expr()->eq(
                            'uid',
                            $queryBuilder->createNamedParameter($this->nextLevel, \PDO::PARAM_INT)
                        )
                    )
                    ->execute();
                $this->nextLevel = 0;
                if ($row = $queryResult->fetch()) {
                    $this->versionOL($row);
                    if (is_array($row)) {
                        $this->processTemplate($row, 'sys_' . $row['uid'], $this->absoluteRootLine[$a]['uid'], 'sys_' . $row['uid']);
                        $this->outermostRootlineIndexWithTemplate = $a;
                    }
                }
            }

            $where = [
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter($this->absoluteRootLine[$a]['uid'], \PDO::PARAM_INT)
                )
            ];
            // If first loop AND there is set an alternative template uid, use that
            if ($a === $c - 1 && $start_template_uid) {
                $where[] = $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($start_template_uid, \PDO::PARAM_INT)
                );
            }
            $queryBuilder->setRestrictions($this->queryBuilderRestrictions);
            $queryResult = $queryBuilder
                ->select('*')
                ->from('sys_template')
                ->where(...$where)
                ->orderBy('root', 'DESC')
                ->addOrderBy('sorting')
                ->setMaxResults(1)
                ->execute();
            if ($row = $queryResult->fetch()) {
                $this->versionOL($row);
                if (is_array($row)) {
                    $this->processTemplate($row, 'sys_' . $row['uid'], $this->absoluteRootLine[$a]['uid'], 'sys_' . $row['uid']);
                    $this->outermostRootlineIndexWithTemplate = $a;
                }
            }
            $this->rootLine[] = $this->absoluteRootLine[$a];
        }

        // Hook into the default TypoScript to add custom typoscript logic
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Core/TypoScript/TemplateService']['runThroughTemplatesPostProcessing'])) {
            $hookParameters = [
                'extensionStaticsProcessed' => &$this->extensionStaticsProcessed,
                'isDefaultTypoScriptAdded'  => &$this->isDefaultTypoScriptAdded,
                'absoluteRootLine' => &$this->absoluteRootLine,
                'rootLine'         => &$this->rootLine,
                'startTemplateUid' => $start_template_uid,
            ];
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Core/TypoScript/TemplateService']['runThroughTemplatesPostProcessing'] as $listener) {
                GeneralUtility::callUserFunction($listener, $hookParameters, $this);
            }
        }

        // Process extension static files if not done yet, but explicitly requested
        if (!$this->extensionStaticsProcessed && $this->processExtensionStatics) {
            $this->addExtensionStatics('sys_0', 'sys_0', 0, []);
        }

        // Add the global default TypoScript from the TYPO3_CONF_VARS
        $this->addDefaultTypoScript();

        $this->processIncludes();
    }
}
