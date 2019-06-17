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
     * @param string $image
     * @param array $cropData
     *
     * @return boolean
     */
    protected function doCrop($image, $cropData) {
        // @todo: change this to $this->settings['avatarHeight'] ... when all sizes are adjusted, hardcoded for now
        $targetHeight = 768;
        $targetWidth = 1024;
        $quality = 85;

        $sourceImage = $this->readImage($image);

        $tempImage = imagecreatetruecolor($targetWidth, $targetHeight);

        imagecopyresampled($tempImage, $sourceImage, 0, 0, $cropData['x'], $cropData['y'], $targetWidth, $targetHeight, $cropData['x2'] - $cropData['x'], $cropData['y2'] - $cropData['y']);

        return $this->writeImage($tempImage, $image, $quality);

    }

    /**
     * @param string $image
     *
     * @return resource
     */
    protected function readImage($image) {

        $imageInfo = getimagesize($image);
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
     * @param string $image
     * @param integer $quality percentage value for quality ( e.g. 70 )
     *
     * @return bool
     */
    protected function writeImage($tempImage, $image, $quality) {
        $success = FALSE;
        $imageInfo = getimagesize($image);

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
            if ($this->doCrop($imageUrl, $cropData)) {
                if(  $this->createMedia($relativeImagePath , $fileName )) {
                    $response = array(
                        'meta' => array(
                            'success' => TRUE
                        ),
                        'data' => array(
                            'image' => $relativeImagePath
                        )
                    );
                }
            }
        }

        echo json_encode($response);
        exit();

    }

    /**
     * action create
     *
     * @return void
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
        $fileReference->setFile($file);
        $this->persistenceManager->persistAll() ;

        $newMedia->setSysfile($fileReference );
        $newMedia->setFeuser( $user ) ;

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
