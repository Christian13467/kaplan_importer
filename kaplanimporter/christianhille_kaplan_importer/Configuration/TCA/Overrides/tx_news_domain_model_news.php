<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {

    $additionalColumns = [
        'chh_kaplanimporter_anlass' => [
            'displayCond' => 'FIELD:chh_kaplanimporter_anlass:REQ:true',
            'exclude' => 1,
            'label' => 'Anlass',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'chh_kaplanimporter_zusatz' => [
            'displayCond' => 'FIELD:chh_kaplanimporter_zusatz:REQ:true',
            'exclude' => 1,
            'label' => 'Zusatz',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'chh_kaplanimporter_ort' => [
            'displayCond' => 'FIELD:chh_kaplanimporter_ort:REQ:true',
            'exclude' => 1,
            'label' => 'Ort/Raum',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'chh_kaplanimporter_ort2' => [
            'displayCond' => 'FIELD:chh_kaplanimporter_ort2:REQ:true',
            'exclude' => 1,
            'label' => 'Raum',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'chh_kaplanimporter_kategorie' => [
            'displayCond' => 'FIELD:chh_kaplanimporter_kategorie:REQ:true',
            'exclude' => 1,
            'label' => 'Kategorie',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ]
    ];

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tx_news_domain_model_news', $additionalColumns);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'tx_news_domain_model_news',
        '--div--;Kaplan Importer,' .
        'chh_kaplanimporter_anlass,chh_kaplanimporter_zusatz,chh_kaplanimporter_ort,chh_kaplanimporter_ort2,chh_kaplanimporter_kategorie'
    );
});
