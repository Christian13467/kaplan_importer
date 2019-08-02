<?php
defined('TYPO3_MODE') or die();

return call_user_func(function () {
    $languageFile = 'LLL:EXT:amt_feed_importer/Resources/Private/Language/locallang.xlf:';

    return [
        'ctrl' => [
            'title' => 'Kaplan Import Definition',
            'label' => 'name',
            'tstamp' => 'tstamp',
            'crdate' => 'crdate',
            'cruser_id' => 'cruser_id',
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
            'searchFields' => 'name,url,categories,target_folder,rooms_and_places_as_categories,category_as_category,create_missing_categories,days,code,arbeitsgruppe',
            'iconfile' => 'EXT:christianhille_kaplan_importer/Resources/Public/Icons/import.png'
        ],
        'interface' => [
            'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, url, days, categories, target_folder, rooms_and_places_as_categories, category_as_category, create_missing_categories,code,arbeitsgruppe',
        ],
        'types' => [
            '1' => ['showitem' => 'name, url, code,arbeitsgruppe, days, target_folder,
                    --div--;Kategorien,  categories, rooms_and_places_as_categories, category_as_category, create_missing_categories, sys_language_uid, l10n_parent, l10n_diffsource, 
                    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, hidden, starttime, endtime'],
        ],
        'columns' => [
            'sys_language_uid' => [
                'exclude' => true,
                'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'special' => 'languages',
                    'items' => [
                        [
                            'LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages',
                            -1,
                            'flags-multiple'
                        ]
                    ],
                    'default' => 0,
                ],
            ],
            'l10n_parent' => [
                'displayCond' => 'FIELD:sys_language_uid:>:0',
                'exclude' => true,
                'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'default' => 0,
                    'items' => [
                        ['', 0],
                    ],
                    'foreign_table' => 'tx_kaplanimporter_domain_model_importdefinition',
                    'foreign_table_where' => 'AND tx_kaplanimporter_domain_model_importdefinition.pid=###CURRENT_PID### AND tx_kaplanimporter_domain_model_importdefinition.sys_language_uid IN (-1,0)',
                ],
            ],
            'l10n_diffsource' => [
                'config' => [
                    'type' => 'passthrough',
                ],
            ],
            't3ver_label' => [
                'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
                'config' => [
                    'type' => 'input',
                    'size' => 30,
                    'max' => 255,
                ],
            ],
            'hidden' => [
                'exclude' => true,
                'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
                'config' => [
                    'type' => 'check',
                    'items' => [
                        '1' => [
                            '0' => 'LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:labels.enabled'
                        ]
                    ],
                ],
            ],
            'starttime' => [
                'exclude' => true,
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ],
                'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
                'config' => [
                    'type' => 'input',
                    'renderType' => 'inputDateTime',
                    'size' => 13,
                    'eval' => 'datetime',
                    'default' => 0,
                ],
            ],
            'endtime' => [
                'exclude' => true,
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ],
                'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
                'config' => [
                    'type' => 'input',
                    'renderType' => 'inputDateTime',
                    'size' => 13,
                    'eval' => 'datetime',
                    'default' => 0,
                    'range' => [
                        'upper' => mktime(0, 0, 0, 1, 1, 2038)
                    ],
                ],
            ],
            
            'name' => [
                'exclude' => false,
                'label' => 'Name',
                'config' => [
                    'type' => 'input',
                    'size' => 30,
                    'eval' => 'trim,required',
                ],
            ],
            'arbeitsgruppe' => [
                'exclude' => false,
                'label' => 'Arbeitsgruppe',
                'config' => [
                    'type' => 'input',
                    'size' => 100,
                    'eval' => 'trim,required',
                ],
            ],
            'code' => [
                'exclude' => false,
                'label' => 'Code',
                'config' => [
                    'type' => 'input',
                    'size' => 30,
                    'eval' => 'trim,required',
                ],
            ],
            'days' => [
                'exclude' => false,
                'label' => 'Tage',
                'config' => [
                    'type' => 'input',
                    'eval' => 'int',
                    'range' => array(
                        'lower' => 1,
                        'upper' => 110
                    ),
                ],
            ],
            'url' => [
                'exclude' => false,
                'label' => 'Url',
                'config' => [
                    'type' => 'input',
                    'eval' => 'trim,required',
                    'size' => 512,
                    'default' => 'http://web.kaplanhosting.net/get.asp?mode=V1&groups=all'
                ],
            ],
            'target_folder' => [
                'exclude' => false,
                'label' => 'Zielordner für Termine aus Kaplan',
                'config' => [
                    'type' => 'group',
                    'internal_type' => 'db',
                    'allowed' => 'pages',
                    'foreign_table' => 'pages',
                    'size' => 1,
                ],
            ],
            'rooms_and_places_as_categories' => [
                'exclude' => false,
                'label' => "Lege Raeume als Kategorien an",
                'config' => [
                    'type' => 'check',
                    'default' => 1,
                ],
            ],
            
            'category_as_category' => [
                'exclude' => false,
                'label' => 'Kategorie als Kategorie',
                'config' => [
                    'type' => 'check',
                    'default' => 1,
                ],
            ],
            'create_missing_categories' => [
                'exclude' => 1,
                'label' => 'Erzeuge fehlende Kategorien',
                'config' => [
                    'type' => 'check',
                    'default' => 1,
                ],
            ],
        ],
    ];
});
