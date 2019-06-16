
plugin.tx_jvmediaconnector_connector {
    view {
        # cat=plugin.tx_jvmediaconnector_connector/file; type=string; label=Path to template root (FE)
        templateRootPath = EXT:jv_media_connector/Resources/Private/Templates/
        # cat=plugin.tx_jvmediaconnector_connector/file; type=string; label=Path to template partials (FE)
        partialRootPath = EXT:jv_media_connector/Resources/Private/Partials/
        # cat=plugin.tx_jvmediaconnector_connector/file; type=string; label=Path to template layouts (FE)
        layoutRootPath = EXT:jv_media_connector/Resources/Private/Layouts/
    }
    persistence {
        # cat=plugin.tx_jvmediaconnector_connector//a; type=string; label=Default storage PID
        storagePid =
    }
}
