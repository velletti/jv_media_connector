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
class MediaController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * mediaRepository
     *
     * @var \JVE\JvMediaConnector\Domain\Repository\MediaRepository
     * @inject
     */
    protected $mediaRepository = null;

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
     * @param \JVE\JvMediaConnector\Domain\Model\Media $newMedia
     * @return void
     */
    public function createAction(\JVE\JvMediaConnector\Domain\Model\Media $newMedia)
    {
        $this->addFlashMessage('The object was created. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        $this->mediaRepository->add($newMedia);
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
