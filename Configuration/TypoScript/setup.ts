
plugin.tx_jvmediaconnector_connector {
    view {
        templateRootPaths.0 = EXT:{extension.extensionKey}/Resources/Private/Templates/
        templateRootPaths.1 = {$plugin.tx_jvmediaconnector_connector.view.templateRootPath}
        partialRootPaths.0 = EXT:jv_media_connector/Resources/Private/Partials/
        partialRootPaths.1 = {$plugin.tx_jvmediaconnector_connector.view.partialRootPath}
        layoutRootPaths.0 = EXT:jv_media_connector/Resources/Private/Layouts/
        layoutRootPaths.1 = {$plugin.tx_jvmediaconnector_connector.view.layoutRootPath}
    }
    persistence {
        storagePid = {$plugin.tx_jvmediaconnector_connector.persistence.storagePid}
        #recursive = 1
    }
    features {
        #skipDefaultArguments = 1
        # if set to 1, the enable fields are ignored in BE context
        ignoreAllEnableFieldsInBe = 0
        # Should be on by default, but can be disabled if all action in the plugin are uncached
        requireCHashArgumentForActionArguments = 0
    }
    mvc {
        #callDefaultActionIfActionCantBeResolved = 1
    }
    setttings {
        pids {
            # Page Id where the cached LIST plugin is placed
            list = 34

            # Page Id where the uncached  plugin is placed, that manages the To upload, resize, create and delete media
            manage = 35


        }
    }
}

config.tx_extbase {
    view {
        widget {
            Fab\MediaUpload\ViewHelpers\Widget\UploadViewHelper {
                templateRootPath = EXT:jv_media_connector/Resources/Private/Templates/
            }
            Fab\MediaUpload\ViewHelpers\Widget\ShowUploadedViewHelper {
                templateRootPath = EXT:jv_media_connector/Resources/Private/Templates/
            }
        }
    }
    persistence {
        # Enable this if you need the reference index to be updated
        updateReferenceIndex = 1
        classes {
            JVE\JvMediaConnector\Domain\Model\FileReference {
                mapping {
                    tableName = sys_file_reference
                    columns {
                        uid_local.mapOnProperty = originalFileIdentifier
                    }
                }
            }
        }
    }
    objects {
        TYPO3\CMS\Extbase\Domain\Model\FileReference.className = JVE\JvMediaConnector\Domain\Model\FileReference
    }
}


cropImage = PAGE
cropImage {

    typeNum = 44900073
    config{
        disableAllHeaderCode = 1
        additionalHeaders = Content-Type:text/xml;charset=utf-8
        metaCharset = utf-8
        xhtml_cleaning = 0
        debug = 0
        admPanel = 0

        sendCacheHeaders = 1
        cache_period = 180
        #no_cache = 1
        #Header for Squid
        additionalHeaders = Cache-Control: must-revalidate, max-age=180, s-maxage=180 | Vary: Accept-Encoding

    }
    20 = USER_INT
    20 {

        userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
        pluginName = Connector
        vendorName = JVE
        extensionName = JvMediaConnector
        controller = Media
        action = showVisualLoginCached

        switchableControllerActions {
            Media {
                1 = cropImage
            }
        }

        settings < plugin.tx_jvmediaconnector_connector.settings
        persistence < plugin.tx_jvmediaconnector_connector.persistence
        view < plugin.tx_jvmediaconnector_connector.view

    }
}


