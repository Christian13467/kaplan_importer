<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "christianhille_kaplan_importer"
 *
 * Auto generated by Extension Builder 2019-07-29
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'Kaplan Importer',
    'description' => 'Import von Kaplan Termindaten nach ext:news',
    'category' => 'be',
    'author' => '',
    'author_email' => '',
    'state' => 'alpha',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.5',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-8.7.999',
            'news' => '6.0.0-0.0.0'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
