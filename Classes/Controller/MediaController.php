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

class MediaController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{


    /**
     * persistencemanager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager = NULL ;

    /**
     * @var \Fab\MediaUpload\Service\UploadFileService
     * @inject
     */
    protected $uploadFileService;


    /**
     * mediaRepository
     *
     * @var \JVE\JvMediaConnector\Domain\Repository\MediaRepository
     * @inject
     */
    protected $mediaRepository = null;


    /**
     * action initialize
     *
     * @return void
     */
    public function initializeAction()
    {
        $this->settings['pageId']						=  $GLOBALS['TSFE']->id ;
        $this->settings['sys_language_uid']				=  $GLOBALS['TSFE']->sys_language_uid ;

        $this->settings['EmConfiguration']	 			= \JVE\JvEvents\Utility\EmConfigurationUtility::getEmConf();

        $this->persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
    }



    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        $medias = $this->mediaRepository->findAll();
        $this->view->assign('medias', $medias);
    }

    /**
     * action new
     *
     * @return void
     */
    public function newAction()
    {

    }



    /**
     * action delete
     *
     * @param \JVE\JvMediaConnector\Domain\Model\Media $media
     * @return void
     */
    public function deleteAction(\JVE\JvMediaConnector\Domain\Model\Media $media)
    {
        $this->addFlashMessage('The object was deleted. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        $this->mediaRepository->remove($media);
        $this->redirect('list');
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
        $fileName = "test.jpg" ;

        $imageUrl = GeneralUtility::getFileAbsFileName($relativeImagePath);
        unset($cropData['img']) ;
        $response = array(
            'meta' => array(
                'success' => FALSE,
                'message' => 'uploaderror_filecrop'
            ),

        );
        if( file_exists( $imageUrl)) {
            $crop = $this->doCrop($imageUrl, $cropData) ;
            if ($crop['success']) {
                $response['meta']['debug'] = $crop['debug'] ;
                $response['meta']['message'] = "upload_cretefile_error" ;
                if(  $this->createMedia($relativeImagePath , $fileName )) {
                    $response['meta']['success'] = TRUE ;
                    $response['data']['image'] = $relativeImagePath ;
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
        $userUid = substr( "00000000" . $feuserUid = intval( $GLOBALS['TSFE']->fe_user->user['uid'] ) , -8 , 8 ) ;
        /** @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository $userRepository */
        $userRepository  = $this->objectManager->get(\TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository::class);

        /** @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user */
        $user = $userRepository->findByUid($userUid) ;

        /** @var \JVE\JvMediaConnector\Domain\Model\Media $newMedia */
        $newMedia = $this->objectManager->get(\JVE\JvMediaConnector\Domain\Model\Media::class);

        $storage = ResourceFactory::getInstance()->getStorageObject(1);
        $orgFolder = $storage->getFolder( "user_upload/org/" , true );
        if( !$storage->hasFolder("user_upload/org/" . $userUid)) {
            $storage->createFolder( $userUid , $orgFolder ) ;
        }
        $targetFolder = $storage->getFolder( "user_upload/org/" . $userUid , true );

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
     * action confirm
     *
     * @return void
     */
    public function confirmAction()
    {

    }


}
