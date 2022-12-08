<?php
defined('TYPO3') || die('Access denied.');

call_user_func(
    function()
    {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'JvMediaConnector',
            'Connector',
            [
                \JVE\JvMediaConnector\Controller\MediaController::class => 'list, new, confirm, resize, create, delete, createMediaRef'
            ],
            // non-cacheable actions
            [
                \JVE\JvMediaConnector\Controller\MediaController::class => 'list, new, confirm, resize, create, delete , createMediaRef'
            ]
        );

    // wizards
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        'mod {
            wizards.newContentElement.wizardItems.plugins {
                elements {
                    connector {
                        iconIdentifier = jv_media_connector-plugin-connector
                        title = LLL:EXT:jv_media_connector/Resources/Private/Language/locallang_db.xlf:tx_jv_media_connector_connector.name
                        description = LLL:EXT:jv_media_connector/Resources/Private/Language/locallang_db.xlf:tx_jv_media_connector_connector.description
                        tt_content_defValues {
                            CType = list
                            list_type = jvmediaconnector_connector
                        }
                    }
                }
                show = *
            }
       }'
    );
		$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
		
			$iconRegistry->registerIcon(
				'jv_media_connector-plugin-connector',
				\TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
				['source' => 'EXT:jv_media_connector/Resources/Public/Icons/user_plugin_connector.svg']
			);
		
    }
);
