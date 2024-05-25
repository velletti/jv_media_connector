<?php
defined('TYPO3') || die('Access denied.');

call_user_func(
    function()
    {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'JvMediaConnector',
            'Connector',
            'Media Connector'
        );

        $pluginSignature = str_replace('_', '', 'jv_media_connector') . '_connector';
        $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:jv_media_connector/Configuration/FlexForms/flexform_connector.xml');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jvmediaconnector_domain_model_media', 'EXT:jv_media_connector/Resources/Private/Language/locallang_csh_tx_jvmediaconnector_domain_model_media.xlf');
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jvmediaconnector_domain_model_media');

    }
);
