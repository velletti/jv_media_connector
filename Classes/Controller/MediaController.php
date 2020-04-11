<?php
namespace JVE\JvMediaConnector\Controller;

/***
 *
 * This file is part of the "Media Connector" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2019 Jörg Velletti <typo3@velletti.de>, Jörg Velletti EDV Systems
 *
 ***/

/**
 * MediaController
 */

use TYPO3\CMS\Core\Resource\Exception;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Connection;

class MediaController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * @var integer
     */
    protected $userUid ;

    /**
     * @var string
     */
    protected $userPath ;

    /**
     * persistencemanager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    protected $persistenceManager = NULL ;

    /**
     * @var \Fab\MediaUpload\Service\UploadFileService
     */
    protected $uploadFileService;


    /**
     * mediaRepository
     *
     * @var \JVE\JvMediaConnector\Domain\Repository\MediaRepository
     */
    protected $mediaRepository = null;


    /**
     *  need session handling to remember Id of Event / location / organizer etc you want to link to
     * @var \TYPO3\CMS\Core\Session\Backend\DatabaseSessionBackend

     */
    protected $sessionRepository  ;
    /**
     * action initialize
     *
     * @return void
     */
    public function initializeAction()
    {
        $this->settings['pageId']						=  $GLOBALS['TSFE']->id ;

        if (class_exists(\TYPO3\CMS\Core\Context\Context::class)) {
            $languageAspect = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class)->getAspect('language') ;
            // (previously known as TSFE->sys_language_uid)
            $this->settings['sys_language_uid']	 = $languageAspect->getId() ;
        } else {
            $this->settings['sys_language_uid']	 = $GLOBALS['TSFE']->sys_language_uid ;
        }


        $this->settings['EmConfiguration']	 			= \JVE\JvMediaConnector\Utility\EmConfigurationUtility::getEmConf();

        $this->persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
        $this->userUid = intval( $GLOBALS['TSFE']->fe_user->user['uid'] )  ;
        $this->userPath = substr( "00000000" . $this->userUid  , -8 , 8 ) ;



        $this->mediaRepository = GeneralUtility::makeInstance( "JVE\\JvMediaConnector\\Domain\\Repository\\MediaRepository" ) ;
        $this->sessionRepository = GeneralUtility::makeInstance( "TYPO3\\CMS\\Core\\Session\\Backend\\DatabaseSessionBackend" ) ;

        $this->uploadFileService = GeneralUtility::makeInstance( "Fab\\MediaUpload\\Service\\UploadFileService" ) ;

        $this->sessionRepository = GeneralUtility::makeInstance( "TYPO3\\CMS\\Core\\Session\\Backend\\DatabaseSessionBackend" ) ;
        $config = $GLOBALS['TYPO3_CONF_VARS']['SYS']['session']['FE']['options'] ;
        $this->sessionRepository->initialize( "FE" , $config  ) ;

    }



    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {



        $medias = $this->mediaRepository->findByUserAllpages( $this->userUid );
        $this->view->assign('medias', $medias);
        $this->view->assign('sessionData', $this->getSessionData() );
    }

    /**
     * action new
     *
     * @return void
     */
    public function newAction()
    {
        if ( $this->userUid < 1 ) {
            $this->redirect('list' , null , null, null ,$this->settings['pids']['list']);
        }
    }



    /**
     * action delete
     *
     * @param \JVE\JvMediaConnector\Domain\Model\Media $media
     * @return void
     */
    public function deleteAction(\JVE\JvMediaConnector\Domain\Model\Media $media)
    {
        if ( $this->userUid == $media->getFeuser()->getUid() ) {

            $sessionData = $this->getSessionData() ;


            if( is_array( $sessionData ) ) {

                /** @var \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool */
                $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

                /** @var \TYPO3\CMS\Core\Database\Connection $dbConnectionForSysRef */
                $dbConnectionForSysRef = $connectionPool->getConnectionForTable('sys_file_reference');

                /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
                $queryBuilder = $dbConnectionForSysRef->createQueryBuilder();

                // ****************** 1. get the sys_file UID   ********************
                $affectedRows = $queryBuilder
                    ->select("uid", "uid_local" , "uid_foreign")
                    ->from('sys_file_reference')
                    ->where(
                        $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($media->getSysfile()->getUid() ))
                    )
                    ->execute();
                $mediaRow = $affectedRows->fetch();

                // ****************** 2. get the sys_file record   ********************
                if( is_array($mediaRow) && array_key_exists("uid_local" , $mediaRow )) {
                    $fileUid = $mediaRow['uid_local'] ;
                    /** @var \TYPO3\CMS\Core\Database\Connection $dbConnectionForSysRef */
                    $dbConnectionForSysFile = $connectionPool->getConnectionForTable('sys_file');

                    /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
                    $queryBuilderFile = $dbConnectionForSysFile->createQueryBuilder();

                    $fileQuery = $queryBuilderFile
                        ->select("*" )
                        ->from('sys_file')
                        ->where(
                            $queryBuilderFile->expr()->eq('uid', $queryBuilderFile->createNamedParameter( $fileUid  ))
                        )
                        ->execute();
                    $fileRow = $fileQuery->fetch();

                    // ****************** 3. Delete File from  sys_file record   ********************
                    // works aktually only with /fileadmin/ folder => storage == 1

                    if( is_array($fileRow) && array_key_exists("identifier" , $fileRow )) {
                        // ToDo : implement use File Storage  instead of /fileadmin/
                        $file2delete =   \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName("fileadmin" . $fileRow['identifier']);
                        if (strpos(  $file2delete  , $fileRow['identifier'] ) > 0  && file_exists($file2delete)) {
                            unlink( $file2delete) ;
                        }
                        // ****************** 4. Delete row from  sys_file record   ********************
                        $queryBuilderFile
                            ->delete('sys_file')
                            ->where(
                                $queryBuilderFile->expr()->eq('uid', $queryBuilderFile->createNamedParameter( $fileUid )),
                                $queryBuilderFile->expr()->eq('storage', $queryBuilderFile->createNamedParameter( 1 ))
                            )
                            ->execute();
                    }
                }
                if ( $fileUid > 0 ) {
                    // remove existing Relations to this media  UID Local may change. Igore that relation may not exists
                    $affectedRows = $queryBuilder
                        ->delete('sys_file_reference')
                        ->where(
                            $queryBuilder->expr()->eq('table_local', $queryBuilder->createNamedParameter('sys_file')),
                            $queryBuilder->expr()->eq('uid_local', $queryBuilder->createNamedParameter( $fileUid ))
                        )
                        ->execute();
                }

            }

            $this->mediaRepository->remove($media);
        } else {

            $this->addFlashMessage('The object was NOT deleted. User "' .  $this->userUid . '"" has NO Access to this image!' . $media->getUid(), '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        }
        $this->redirect('list' , null , null, array( "random" => time() ) ,$this->settings['pids']['list']);
    }





    /**
     * action resize
     *
     * @return void
     */
    public function resizeAction()
    {

    }
    /**
     * @param string $image absolutePathToFileName check if  exist is done before
     * @param array $cropData
     *
     * @return array
     */
    protected function doCrop($image, $cropData) {
        $imageInfo = getimagesize($image);
        if ( $imageInfo[0] == 0 ) { return array( "success" => false , "debug" => "ERR-01: No Image dimensions or no image")  ;}
        if ( $cropData['dw'] == 0 ) { return array( "success" => false , "debug" => "ERR-02: No display dimensions")  ;}

        $debug['imageInfo'] = $imageInfo ;
        $debug['cropData'] = $cropData ;


        if( is_array($this->settings ) && is_array($this->settings['crop'] )) {
            $debug['settings'] = $this->settings['crop']  ;
            if( array_key_exists('maxHeight' , $this->settings['crop'])) {
                $maxHeight = $this->settings['crop']['maxHeight'];
            } else {
                $maxHeight = 768 ;
            }

            if( array_key_exists('maxWidth' , $this->settings['crop'])) {
                $maxWidth = $this->settings['crop']['maxWidth'];
            } else {
                $maxWidth = 1024 ;
            }
            if( array_key_exists('quality' , $this->settings['crop'])) {
                $quality = $this->settings['crop']['quality'];
            } else {
                $quality = 80 ;
            }
        }

        $debug['maxWidth'] = $maxWidth ;
        $debug['maxHeight'] = $maxHeight ;

        $ratio = $imageInfo[0] / $cropData['dw'] ;

        $debug["ratio"] = $ratio ;
        $src_w = ( $cropData['x2'] - $cropData['x'])  * $ratio ;
        $src_h = ( $cropData['y2'] - $cropData['y']) *  $ratio ;
        $src_x =  $cropData['x'] *  $ratio ;
        $src_y =  $cropData['y'] *  $ratio ;

        $dest_x = 0  ;
        $dest_y = 0  ;
        if(  $imageInfo[0] > $maxWidth ) {
            $dest_w = $maxWidth ;
            $dest_h = $imageInfo[1] * ( $maxWidth / $imageInfo[0]) ;
            $debug["reducedbyMaxW"] = true ;
        } else {
            $dest_w = $imageInfo[0] ;
            $dest_h = $imageInfo[0] /  ( $src_w  )  * $src_h ;
        }
        if ( $dest_h > $maxHeight ) {
            $dest_h =  $maxHeight  ;
            $dest_w = $dest_w * ( $maxHeight / $dest_h ) ;
            $debug["reducedbyMaxH"] = true ;
        }





        $sourceImage = $this->readImage($image , $imageInfo );

        $tempImage = imagecreatetruecolor($dest_w, $dest_h);
        $debug["command"] = array (
            'dest_x' => $dest_x ,
            'dest_y' => $dest_y ,
            'src_x' => $src_x  ,
            'src_y' => $src_y ,
            'dest_w' => $dest_w ,
            'dest_h' => $dest_h ,
            'src_w' => $src_w ,
            'src_h' => $src_h ,
        ) ;

        imagecopyresampled(     $tempImage, $sourceImage, $dest_x, $dest_y, $src_x , $src_y , $dest_w, $dest_h, $src_w , $src_h);

        $result =  $this->writeImage($tempImage, $image, $quality , $imageInfo);
 // var_dump($debug) ;
// die;
        return array( "success" => $result , 'debug' => $debug ) ;

    }

    /**
     * @param string $image absolutePathToFileName
     * @param array $imageInfo  result from getimagesize() in DoCrop()
     *
     * @return resource
     */
    protected function readImage($image , $imageInfo ) {

        $sourceImage = null;

        switch ($imageInfo['2']) {
            case '1':
                $sourceImage = imagecreatefromgif($image);
                break;
            case '2':
                $sourceImage = imagecreatefromjpeg($image);
                break;
            case '3':
                $sourceImage = imagecreatefrompng($image);
                break;
            default:
                break;
        }

        return $sourceImage;
    }

    /**
     * @param resource $tempImage
     * @param string $image absolutePathToFileName
     * @param integer $quality percentage value for quality ( e.g. 70 )
     * @param array $imageInfo result from getimagesize() in DoCrop() ;
     *
     * @return bool
     */
    protected function writeImage($tempImage, $image, $quality , $imageInfo) {
        $success = FALSE;

        switch ($imageInfo['2']) {
            case '1':
                $success = imagegif($tempImage, $image);
                break;
            case '2':
                $success = imagejpeg($tempImage, $image, $quality);
                break;
            case '3':
                $success = imagepng($tempImage, $image, floor($quality / 10));
                break;
            default:
                break;
        }

        return $success;
    }

    /**
     * cropImage Action, called via ajax
     */
    public function cropImageAction() {
        $cropData = GeneralUtility::_POST();
        if( $this->request->hasArgument("cropData")) {
            $cropData = $this->request->getArgument("cropData") ;
        }

        $relativeImagePath = substr( $cropData['img'] , 1 , 999) ;
        // toDo : get sanitized Filename without " " or special Chars ..

        $fileName =  preg_replace( '/[^a-z0-9.]+/', '-', strtolower( basename($cropData['img']) ) );
        $md5        = md5($fileName) ;
        $fileName = substr( $md5 ,0, 4 ) . "_" . $fileName ;

        $imageUrl = GeneralUtility::getFileAbsFileName($relativeImagePath);
        unset($cropData['img']) ;
        $response = array(
            'meta' => array(
                'success' => FALSE,
                'message' => 'uploaderror_filecrop'
            ),

        );
        if( file_exists( $imageUrl)) {
            $info = getimagesize($imageUrl) ;
            $crop = $this->doCrop($imageUrl, $cropData) ;
            if ($crop['success']) {
                $response['meta']['debug'] = $crop['debug'] ;
                $response['meta']['message'] = "upload_cretefile_error" ;
                if(  $this->createMedia($relativeImagePath , $fileName )) {
                    $response['meta']['success'] = TRUE ;
                    $response['data']['image'] = $relativeImagePath ;
                    $response['data']['width'] = $info[0] ;
                    $response['data']['height'] = $info[1] ;
                }
            }
        }
        $jsonOutput = json_encode($response);
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Length: ' . strlen($jsonOutput));
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Transfer-Encoding: 8bit');
        echo $jsonOutput;
        exit();

    }

    /**
     * action create
     *
     * @return boolean
     */
    protected function createMedia($relativeImagePath , $fileName )
    {

        /** @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository $userRepository */
        $userRepository  = $this->objectManager->get(\TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository::class);

        /** @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user */
        $user = $userRepository->findByUid($this->userUid) ;

        /** @var \JVE\JvMediaConnector\Domain\Model\Media $newMedia */
        $newMedia = $this->objectManager->get(\JVE\JvMediaConnector\Domain\Model\Media::class);

        $storage = ResourceFactory::getInstance()->getStorageObject(1);
        $orgFolder = $storage->getFolder( "user_upload/org/" , true );
        if( !$storage->hasFolder("user_upload/org/" . $this->userPath)) {
            $storage->createFolder( $this->userPath , $orgFolder ) ;
        }
        $targetFolder = $storage->getFolder( "user_upload/org/" . $this->userPath , true );

        /** @var \TYPO3\CMS\Core\Resource\File $file */
        $file = $storage->addFile(
            $relativeImagePath ,
            $targetFolder,
            $fileName,
            \TYPO3\CMS\Core\Resource\DuplicationBehavior::RENAME
        );

        # Note: Use method `addUploadedFile` instead of `addFile` if file is uploaded
        # via a regular "input" control instead of the upload widget (fine uploader plugin)
        # $file = $storage->addUploadedFile()
        $this->persistenceManager->persistAll() ;
        /** @var \JVE\JvMediaConnector\Domain\Model\FileReference $fileReference */
        $fileReference = $this->objectManager->get(\JVE\JvMediaConnector\Domain\Model\FileReference::class);
        if( is_array($this->settings ) && is_array($this->settings['pids'] ) &&  array_key_exists('storagePid' , $this->settings['pids'])) {
            $fileReference->setPid($this->settings['pids']['storagePid']);
        }
        $fileReference->setFile($file);
        $fileReference->setTablenames("tx_jvmediaconnector_domain_model_media");
        $fileReference->setTableLocal("sys_file");

        $this->persistenceManager->persistAll() ;

        $newMedia->setSysfile($fileReference );
        $newMedia->setFeuser( $user ) ;
        $newMedia->setPid( $fileReference->getPid() ) ;

        $this->mediaRepository->add($newMedia);
        $this->persistenceManager->persistAll() ;
        if ( $newMedia->getUid() > 0 ) {
            return true ;
        };
        return false ;

    }



    /**
     * action createMediaRef Has no views ... redirects back
     *
     * @return void
     */
    public function createMediaRefAction() {

        $sessionData = $this->getSessionData() ;

        if( is_array( $sessionData )  && $this->request->hasArgument('media')  ) {

            /** @var \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool */
            $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

            /** @var \TYPO3\CMS\Core\Database\Connection $dbConnectionForSysRef */
            $dbConnectionForSysRef = $connectionPool->getConnectionForTable('sys_file_reference');

            /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
            $queryBuilder = $dbConnectionForSysRef->createQueryBuilder();


            $media = intval( $this->request->getArgument('media')) ;

            $affectedRows = $queryBuilder
                ->select( "uid","uid_local" )
                ->from('sys_file_reference')
                ->where(
                        $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter( $media ))
                )
                ->execute();

            $mediaRow = $affectedRows->fetch() ;

            if ( $sessionData['maxRelations'] < 2 ) {
                // remove existing Relations to this media  UID Local may change. Igore that relation may not exists
                $affectedRows = $queryBuilder
                    ->delete('sys_file_reference')
                    ->where(
                        $queryBuilder->expr()->eq('table_local', $queryBuilder->createNamedParameter('sys_file')),
                        $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($sessionData['table'] )),
                        $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($sessionData['id'] )),
                        $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter($sessionData['fieldname'] ))
                    )
                    ->execute();
            }


            $affectedRows = $queryBuilder
                ->insert('sys_file_reference')
                ->values([
                    'uid_local' => intval( $mediaRow['uid_local']) ,
                    'table_local' => 'sys_file'  ,
                    'uid_foreign' => intval( $sessionData['id'] ) ,
                    'tablenames' => $sessionData['table']  ,
                    'fieldname' => $sessionData['fieldname']  ,

                ])
                ->execute();

            $insertId = $dbConnectionForSysRef->lastInsertId() ;
            // create Reference from Array
        }
// got from EM Settings
        $clearCachePids = array( $GLOBALS['TSFE']->id ) ;
        $this->cacheService->clearPageCache( $clearCachePids );
        // got from EM Settings
        $clearCachePids = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode("," , $this->settings['EmConfiguration']['clearCachePids']) ;
        if( is_array($clearCachePids) && count( $clearCachePids) > 0 ) {
            $this->cacheService->clearPageCache( $clearCachePids );
        }
        $this->addFlashMessage('Media was linked successful. ' , '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);

        $pid = $this->settings['pids']['list'] ;
        $returnArray = array() ;
        if( is_array( $sessionData )  && array_key_exists("returnPid" , $sessionData) ) {
            $pid = $sessionData['returnPid'] ;
            $returnArray = $sessionData['returnArray'] ;
        }
        if ( $pid < 1 ) {
            $pid =  $GLOBALS['TSFE']->id ;
        }
        $uriBuilder = $this->controllerContext->getUriBuilder();
        $uriBuilder->reset();
        $uriBuilder->setTargetPageUid( $pid );
        if( is_array($returnArray)) {
            $uriBuilder->setArguments($returnArray );
        }
        // $uriBuilder->setSection('post_' . $post->getUid()); // anchor
        $uriBuilder->setCreateAbsoluteUri(true); // complete uir with domain
        $uri = $uriBuilder->build();

        $this->redirectToUri($uri );

    }

    /**
     * action confirm
     *
     * @return void
     */
    public function confirmAction()
    {

    }

    private function getSessionData() {
        $sessionData = null ;
        $sessionId = "media_uid_" . $this->userPath ;
        if($this->request->hasArgument('reference')) {
            // https://tango.ddev.local/index.php?id=34&no_cache=1&tx_jvmediaconnector_connector[reference][id]=1&tx_jvmediaconnector_connector[reference][table]=tx_jv_events_domain_model_event
            $sessionData = $this->request->getArgument('reference') ;
            if( array_key_exists( 'table', $sessionData)  && array_key_exists( 'id', $sessionData)  ) {
                try{
                    $this->sessionRepository->remove($sessionId ) ;
                    $this->sessionRepository->set($sessionId , array( "ses_data" => serialize($sessionData )) ) ;
                } catch( \TYPO3\CMS\Core\Session\Backend\Exception\SessionNotCreatedException $e) {
                    // ignore this
                }
            }

        }
        if(!$sessionData) {
            try{
                /** @var array $session */
                $session = $this->sessionRepository->get($sessionId ) ;
                $sessionData = unserialize( $session['ses_data'] ) ;
            } catch( \TYPO3\CMS\Core\Session\Backend\Exception\SessionNotFoundException $e) {
                // ignore this
            }
        }
        return $sessionData ;
    }

}
