<?php
return [
    'ctrl' => [
        'title' => 'LLL:EXT:jv_media_connector/Resources/Private/Language/locallang_db.xlf:tx_jvmediaconnector_domain_model_media',
        'label' => 'sysfile',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'usecount,feuser,sysfile',
        'iconfile' => 'EXT:jv_media_connector/Resources/Public/Icons/tx_jvmediaconnector_domain_model_media.gif'
    ],
    'types' => [
        '1' => ['showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, usecount, feuser, sysfile, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, starttime, endtime'],
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => ['type' => 'language'],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 0,
                'items' => [
                    ['label' => '', 'value' => 0],
                ],
                'foreign_table' => 'tx_jvmediaconnector_domain_model_media',
                'foreign_table_where' => 'AND tx_jvmediaconnector_domain_model_media.pid=###CURRENT_PID### AND tx_jvmediaconnector_domain_model_media.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        't3ver_label' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ],
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'items' => [
                    '1' => [
                        '0' => 'LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.enabled'
                    ]
                ],
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'behaviour' => [
                'allowLanguageSynchronization' => true
            ],
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'datetime',
                'size' => 13,
                'default' => 0,
            ],
        ],
        'endtime' => [
            'exclude' => true,
            'behaviour' => [
                'allowLanguageSynchronization' => true
            ],
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'datetime',
                'size' => 13,
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038)
                ],
            ],
        ],

        'usecount' => [
            'exclude' => true,
            'label' => 'LLL:EXT:jv_media_connector/Resources/Private/Language/locallang_db.xlf:tx_jvmediaconnector_domain_model_media.usecount',
            'config' => [
                'type' => 'number',
                'size' => 4
            ]
        ],
        'feuser' => [
            'exclude' => true,
            'label' => 'LLL:EXT:jv_media_connector/Resources/Private/Language/locallang_db.xlf:tx_jvmediaconnector_domain_model_media.feuser',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'fe_users',
                'minitems' => 0,
                'maxitems' => 1,
                'appearance' => [
                    'collapseAll' => 0,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1
                ],
            ],
        ],
        'sysfile' => [
            'exclude' => true,
            'label' => 'LLL:EXT:jv_media_connector/Resources/Private/Language/locallang_db.xlf:tx_jvmediaconnector_domain_model_media.sysfile',
            'config' => [
                // TODO: Important! Verify that the fieldname value in foreign table either matches the column name
                // or is set properly in the following TCA, see https://docs.typo3.org/permalink/t3tca:confval-inline-foreign-match-fields
                'type' => 'file',
                'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
                'appearance' => array(
                    'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference'
                ),
                'minitems' => 0,
                'maxitems' => 1,
            ],

        ],
    
    ],
];
