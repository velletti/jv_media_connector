<?php
defined('TYPO3') || die('Access denied.');

call_user_func(
    function()
    {

        $pluginSignature = str_replace('_', '', 'jv_media_connector') . '_connector';
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('*', 'FILE:EXT:jv_media_connector/Configuration/FlexForms/flexform_connector.xml', $pluginSignature);

    }
);
