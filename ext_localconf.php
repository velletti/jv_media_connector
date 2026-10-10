<?php
defined('TYPO3') || die('Access denied.');

call_user_func(
    function()
    {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'JvMediaConnector',
            'Connector',
            [
                \JVE\JvMediaConnector\Controller\MediaController::class => 'list, new, confirm, resize, create, delete, createMediaRef, cropImage'
            ],
            // non-cacheable actions
            [
                \JVE\JvMediaConnector\Controller\MediaController::class => 'list, new, confirm, resize, create, delete , createMediaRef, cropImage'
            ],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

    }
);
