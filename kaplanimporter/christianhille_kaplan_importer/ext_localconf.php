<?php
defined('TYPO3_MODE') or die();

call_user_func(function ($extensionKey) {

    // $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\AMT\AmtFeedImporter\Task\RSS2ImportTask::class] = [
    // 'extension' => $extensionKey,
    // 'title' => $localLang . 'amt_feed_importer.rss2_task.name',
    // 'description' => $localLang . 'amt_feed_importer.rss2_task.description',
    // 'additionalFields' => \AMT\AmtFeedImporter\Task\RSS2FeedAdditionalFieldProvider::class,
    // ];

//     $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\AMT\AmtFeedImporter\Task\AtomImportTask::class] = [
//         'extension' => $extensionKey,
//         'title' => $localLang . 'amt_feed_importer.atom_task.name',
//         'description' => $localLang . 'amt_feed_importer.atom_task.description',
//         'additionalFields' => \AMT\AmtFeedImporter\Task\AtomFeedAdditionalFieldProvider::class
//     ];

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
        'christianhille_kaplan_importer',
        'tx_kaplanimporter_domain_model_importdefinition',
        'categories',
        [
            'label' => 'Kategorien',
            'exclude' => 1,
            'fieldConfiguration' => [
                'foreign_table_where' => ' AND sys_category.sys_language_uid IN (-1, 0) ' .
                'ORDER BY sys_category.title ASC',
            ],
        ]
    );
    
//     \GeorgRinger\News\Utility\ImportJob::register(
// 		\CHRISTIANHILLE\KaplanImporter\Job\KaplanNewsDataImportJob::class, 
// 		"Kaplan Import Job", 
// 		"Importieren von Kaplan Terminen"
// 	);
	
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\CHRISTIANHILLE\KaplanImporter\Task\KaplanImportTask::class] = [
        'extension' => $extensionKey,
        'title' => 'Kaplan Import Task',
        'description' => "Importieren von Kaplan Terminen",
	    'additionalFields' => \CHRISTIANHILLE\KaplanImporter\Task\AdditionalFieldProvider::class
    ];

	
	
}, $_EXTKEY);
