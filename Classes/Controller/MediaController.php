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

use Doctrine\DBAL\Driver\Exception;
use JVE\JvMediaConnector\Domain\Repository\MediaRepository;
use JVE\JvMediaConnector\Utility\EmConfigurationUtility;
use JVE\JvMediaConnector\Domain\Model\Media;
use JVE\JvMediaConnector\Domain\Model\FileReference;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use JVelletti\JvEvents\Domain\Repository\FrontendUserRepository;
use JVelletti\JvEvents\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Session\Backend\Exception\SessionNotCreatedException;
use TYPO3\CMS\Core\Session\Backend\Exception\SessionNotFoundException;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Session\Backend\DatabaseSessionBackend;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Extbase\Service\CacheService;

class MediaController extends ActionController
{

    const UPLOAD_FOLDER = 'typo3temp/MediaUpload/';

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
     * @var PersistenceManager
     */
    protected $persistenceManager = NULL ;

    /**
     * CacheService
     */

    public $cacheService ;

    /**
     * @param CacheService $cacheService
     * @return void
     */
    public function injectCacheService(CacheService $cacheService) {
        $this->cacheService = $cacheService ;
    }

    /**
     * @param PersistenceManager $persistenceManager
     */
    public function injectPersistenceManager (PersistenceManager $persistenceManager) {
        $this->persistenceManager = $persistenceManager ;
    }



    /**
     * mediaRepository
     *
     * @var MediaRepository
     */
    protected $mediaRepository ;

    /**
     * @param MediaRepository $mediaRepository
     */
    public function injectMediaRepository (MediaRepository $mediaRepository) {
        $this->mediaRepository = $mediaRepository ;
    }



    /**
     *  need session handling to remember Id of Event / location / organizer etc you want to link to
     * @var DatabaseSessionBackend

     */
    protected $sessionRepository  ;

    /**
     * @param DatabaseSessionBackend $sessionRepository
     */
    public function injectSessionRepository (DatabaseSessionBackend $sessionRepository) {
        $this->sessionRepository = $sessionRepository ;
    }


    /**
     * action initialize
     *
     * @return void
     */
    public function initializeAction()
    {
        $this->settings['pageId']						=  $GLOBALS['TSFE']->id ;

        /** @var LanguageAspect $languageAspect */
        $languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language') ;

        // (previously known as TSFE->sys_language_uid)
        $this->settings['sys_language_uid'] = $languageAspect->getId() ;

        $this->settings['EmConfiguration']	 			= EmConfigurationUtility::getEmConf();

        $this->persistenceManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
        /** @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendUser */
        $frontendUser = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user');
        $feUser = ($frontendUser->user ?? null ) ;
        if( is_array($feUser) && array_key_exists('uid' , $feUser) ) {
            $this->userUid = intval( $feUser['uid'] )  ;
        } else {
            $this->userUid = 0 ;
        }
        $this->userPath = substr( "00000000" . $this->userUid  , -8 , 8 ) ;



         $this->mediaRepository = GeneralUtility::makeInstance('JVE\\JvMediaConnector\\Domain\\Repository\\MediaRepository');
         $this->sessionRepository = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Session\\Backend\\DatabaseSessionBackend');

        $config = $GLOBALS['TYPO3_CONF_VARS']['SYS']['session']['FE']['options'] ;
        $this->sessionRepository->initialize( "FE" , $config  ) ;

    }



    /**
     * action list
     *
     * @return void
     */
    public function listAction(): ResponseInterface
    {
        try {
            $medias = $this->mediaRepository->findByUserAllpages( $this->userUid );
        } catch ( \Exception $e) {
            $this->addFlashMessage('Error: ' . $e->getMessage(), '', AbstractMessage::ERROR);
        }

        $this->view->assign('medias', $medias);
        $this->view->assign('sessionData', $this->getSessionData() );
        return $this->htmlResponse();
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
        if (! is_dir(Environment::getPublicPath() . '/' . self::UPLOAD_FOLDER)) {
            mkdir ( Environment::getPublicPath() . '/' . self::UPLOAD_FOLDER ) ;
        }
        // Todo Add Security check, file types and size
        // $json['output'] =  $this->createMedia($_FILES['file']['tmp_name'] , $_FILES['file']['name'] , true ) ;

        if(file_exists( Environment::getPublicPath() . '/' . $this->getFileName() )) {
            unlink( Environment::getPublicPath() . '/' .  $this->getFileName() ) ;
        }
        $ext = $this->getFileExt($_FILES['file']['tmp_name']) ;
        $rnd = time() ;
        if ( $ext ) {
            if (  move_uploaded_file( $_FILES['file']['tmp_name'] ,  $this->getFileName() . "_" . $rnd . "." . $ext )) {
                $this->returnJson( ['success' => true , "ext" => $ext , "rnd" => $rnd  ] ) ;
            }
        }

        $this->returnJson( ['success' => false ] ) ;

    }

    public function getFileName(  )  {
        return self::UPLOAD_FOLDER . $this->userPath  ;
    }

    public function getFileExt( $file )  {
        $imageInfo = getimagesize($file )  ;

        if ( is_array( $imageInfo )) {
            switch ($imageInfo['2']) {

                case '1':
                    return "gif" ;
                case '2':
                    return "jpg" ;
                case '3':
                    return "png" ;
            }
        }
        return false ;
    }




    /**
     * action delete
     *
     * @param Media $media
     * @return void
     */
    public function deleteAction(Media $media)
    {
        if ( $this->userUid == $media->getFeuser()->getUid() ) {

            $sessionData = $this->getSessionData() ;


            if( is_array( $sessionData ) ) {

                /** @var ConnectionPool $connectionPool */
                $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

                /** @var Connection $dbConnectionForSysRef */
                $dbConnectionForSysRef = $connectionPool->getConnectionForTable('sys_file_reference');

                /** @var QueryBuilder $queryBuilder */
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
                    /** @var Connection $dbConnectionForSysRef */
                    $dbConnectionForSysFile = $connectionPool->getConnectionForTable('sys_file');

                    /** @var QueryBuilder $queryBuilder */
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
                        $file2delete =   GeneralUtility::getFileAbsFileName("fileadmin" . $fileRow['identifier']);
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
                            $queryBuilder->expr()->eq('uid_local', $queryBuilder->createNamedParameter( $fileUid ))
                        )
                        ->execute();
                }

            }

            $this->mediaRepository->remove($media);
        } else {

            $this->addFlashMessage('The object was NOT deleted. User "' .  $this->userUid . '"" has NO Access to this image!' . $media->getUid(), '', AbstractMessage::WARNING);
        }
        return $this->redirect('list' , null , null, array( "random" => time() ) ,$this->settings['pids']['list']);
    }





    /**
     * action resize
     *
     * @return void
     */
    public function resizeAction(): ResponseInterface
    {
        if( $this->userUid > 0 ) {

            if( $this->request->hasArgument("ext")  ) {
                $ext = substr( $this->request->getArgument("ext") , 0 , 3 ) ;
                $rnd = intval( $this->request->getArgument("rnd") ) ;
                $ext =  str_replace( "." , "" , $ext) ;
                if ( strlen( $ext ) == 3  ) {
                    // $image = $this->mediaRepository->findByUserAllpages($this->userUid , true ,$uid )->getFirst() ;
                    $this->view->assign("property" , "sysfile") ;
                    $this->view->assign("tempFile" ,  $this->getFileName() . "_" . $rnd . "." . $ext  ) ;
                }

            }
        }


        return $this->htmlResponse();
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
                $response['meta']['message'] = "upload_createfile_error" ;
                $response['meta']['relativeImagePath'] = $relativeImagePath ;
                $response['meta']['fileName'] = $fileName ;
                if(  $this->createMedia($relativeImagePath , $fileName )) {
                    $response['meta']['success'] = TRUE ;
                    $response['data']['image'] = $relativeImagePath ;
                    $response['data']['width'] = $info[0] ;
                    $response['data']['height'] = $info[1] ;
                    $response['meta']['message'] = "upload_done" ;
                }
            }
        } else {
            $response['meta']['message'] = "upload_noFile_at_url " . $imageUrl;
        }
        $this->returnJson($response) ;
    }

    private function returnJson( $response) {
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
     * @return boolean|int
     */
    protected function createMedia($relativeImagePath , $fileName , $returnMedia=false )
    {

        /** @var FrontendUserRepository $userRepository */
        $userRepository  = GeneralUtility::makeInstance(FrontendUserRepository::class);

        /** @var \JVelletti\JvEvents\Domain\Model\FrontendUser $user */
        $user = $userRepository->findByUid($this->userUid) ;

        /** @var Media $newMedia */
        $newMedia = GeneralUtility::makeInstance(Media::class);

        /** @var ResourceFactory $factory */
        $factory = GeneralUtility::makeInstance(ResourceFactory::class) ;

        // ******* Maybe we need to make Uid of storage (fileadmin/userupload/ configurable !
        $storageObjectId = 1 ;

        $storage = $factory->getStorageObject( $storageObjectId );

        if( !$storage->hasFolder("user_upload/org" )) {
            $orgFolder = $storage->getFolder( "user_upload/" , true );
            $storage->createFolder( 'org' , $orgFolder ) ;
        }
        $orgFolder = $storage->getFolder( "user_upload/org/" , true );
        if( !$storage->hasFolder("user_upload/org/" . $this->userPath)) {
            $storage->createFolder( $this->userPath , $orgFolder ) ;
        }
        $targetFolder = $storage->getFolder( "user_upload/org/" . $this->userPath , true );

        /** @var File $file */
        $file = $storage->addFile(
            $relativeImagePath ,
            $targetFolder,
            $fileName,
            DuplicationBehavior::REPLACE
        );
        // Persits to have a valid UID
        $this->persistenceManager->persistAll() ;

        if( (int)$file->getUid() == 0 ) {
            return false ;
        }
        # Note: Use method `addUploadedFile` instead of `addFile` if file is uploaded
        # via a regular "input" control instead of the upload widget (fine uploader plugin)
        # $file = $storage->addUploadedFile()

        /** @var FileReference $fileReference */
        $fileReference = GeneralUtility::makeInstance(FileReference::class);

        if( is_array($this->settings ) && is_array($this->settings['pids'] ) &&  array_key_exists('storagePid' , $this->settings['pids'])) {
            $fileReference->setPid($this->settings['pids']['storagePid']);
        } else {
            $fileReference->setPid( 35 ) ;
        }
        $fileReference->setFile($file);
        $fileReference->setTablenames("tx_jvmediaconnector_domain_model_media");

        $newMedia->setSysfile( $fileReference );
        $newMedia->setFeuser( $user ) ;
        $newMedia->setHidden( 0 ) ;
        $newMedia->setPid( $fileReference->getPid() ) ;

        $this->mediaRepository->add($newMedia);
        $this->persistenceManager->persistAll() ;







        if ( $newMedia->getUid() > 0 && $file->getUid() > 0 ) {
            /** @var ConnectionPool $connectionPool */
            $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

            /** @var \TYPO3\CMS\Core\Database\Connection $dbConnectionForSysRef */
            $dbConnectionForSysRef = $connectionPool->getConnectionForTable('sys_file_reference');

            /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
            $queryBuilder = $dbConnectionForSysRef->createQueryBuilder();

            $affectedRow = $queryBuilder
                ->update('sys_file_reference')
                ->set(    'uid_local' , (int)$file->getUid() )
                ->set(    'hidden' , 0 )
                ->set(    'tablenames' , 'tx_jvmediaconnector_domain_model_media' )
                ->where( $queryBuilder->expr()->eq('uid_foreign' , $newMedia->getUid() ))
                ->execute();
            if( $returnMedia ) {
                return  $newMedia->getUid() ;
            }
            return true ;
        };
        return false ;

    }



    /**
     * action createMediaRef Has no views ... redirects back
     *
     */
    public function createMediaRefAction(): ResponseInterface
    {

        $sessionData = $this->getSessionData() ;

        if( is_array( $sessionData )  && $this->request->hasArgument('media')  ) {

            /** @var ConnectionPool $connectionPool */
            $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

            /** @var Connection $dbConnectionForSysRef */
            $dbConnectionForSysRef = $connectionPool->getConnectionForTable('sys_file_reference');

            /** @var QueryBuilder $queryBuilder */
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
        $clearCachePids = GeneralUtility::trimExplode("," , $this->settings['EmConfiguration']['clearCachePids']) ;
        if( is_array($clearCachePids) && count( $clearCachePids) > 0 && intval($clearCachePids[0]) > 0 ) {
            $this->cacheService->clearPageCache( $clearCachePids );
        }
        $this->addFlashMessage('Media was linked successful. ' , '', AbstractMessage::OK);

        $pid = $this->settings['pids']['list'] ;
        $returnArray = array() ;
        if( is_array( $sessionData )  && array_key_exists("returnPid" , $sessionData) ) {
            $pid = $sessionData['returnPid'] ;
            $returnArray = $sessionData['returnArray'] ;
        }
        if ( $pid < 1 ) {
            $pid =  $GLOBALS['TSFE']->id ;
        }
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class) ;
        $uriBuilder->reset();
        $uriBuilder->setTargetPageUid( $pid );
        if( is_array($returnArray)) {
            $uriBuilder->setArguments($returnArray );
        }
        // $uriBuilder->setSection('post_' . $post->getUid()); // anchor
        $uriBuilder->setCreateAbsoluteUri(true); // complete uir with domain
        $uri = $uriBuilder->build();

        return $this->redirectToUri($uri );

    }

    /**
     * action confirm
     *
     * @return void
     */
    public function confirmAction(): ResponseInterface
    {
        return $this->htmlResponse();
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
                } catch( SessionNotCreatedException $e) {
                    // ignore this
                }
            }

        }
        if(!$sessionData) {
            try{
                /** @var array $session */
                $session = $this->sessionRepository->get($sessionId ) ;
                $sessionData = unserialize( $session['ses_data'] ) ;
            } catch( SessionNotFoundException $e) {
                // ignore this
            }
        }
        return $sessionData ;
    }

}
