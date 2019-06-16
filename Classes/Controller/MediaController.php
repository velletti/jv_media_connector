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
     * action create
     *
     * @return void
     */
    public function createAction()
    {
        $userUid = substr( "00000000" . $feuserUid = intval( $GLOBALS['TSFE']->fe_user->user['uid'] ) , -8 , 8 ) ;


        /** @var \JVE\JvMediaConnector\Domain\Model\Media $newMedia */
        $newMedia = $this->objectManager->get(\JVE\JvMediaConnector\Domain\Model\Media::class);
        /** @var array $uploadedFiles */
        $uploadedFiles = $this->uploadFileService->getUploadedFiles('sysfile') ;

        /** @var \Fab\MediaUpload\UploadedFile $uploadedFile */
        foreach($uploadedFiles as $uploadedFile) {


            $uploadedFile->getTemporaryFileNameAndPath();

            $storage = ResourceFactory::getInstance()->getStorageObject(1);
            $orgFolder = $storage->getFolder( "user_upload/org/" , true );
            if( !$storage->hasFolder("user_upload/org/" . $userUid)) {
                $storage->createFolder( $userUid , $orgFolder ) ;
            }
            $targetFolder = $storage->getFolder( "user_upload/org/" . $userUid , true );

            /** @var \TYPO3\CMS\Core\Resource\File $file */
            $file = $storage->addFile(
                $uploadedFile->getTemporaryFileNameAndPath(),
                $targetFolder,
                $uploadedFile->getFileName(),
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
            $newMedia->setFeuser( intval( $GLOBALS['TSFE']->fe_user->user['uid'])) ;
            $newMedia->sethidden( 1 ) ;
            $newMedia->sethidden( 1 ) ;

            $this->mediaRepository->add($newMedia);
            $this->persistenceManager->persistAll() ;
            echo $file->getUid() ;
            echo "<hr>";
            echo $newMedia->getUid() ;
            die;

        }

        $this->redirect('list');
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
     * action confirm
     *
     * @return void
     */
    public function confirmAction()
    {

    }

    /**
     * action resize
     *
     * @return void
     */
    public function resizeAction()
    {

    }
}
