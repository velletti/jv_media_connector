<?php
namespace JVE\JvMediaConnector\Tests\Unit\Controller;

/**
 * Test case.
 *
 * @author Jörg Velletti <typo3@velletti.de>
 */
class MediaControllerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \JVE\JvMediaConnector\Controller\MediaController
     */
    protected $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = $this->getMockBuilder(\JVE\JvMediaConnector\Controller\MediaController::class)
            ->setMethods(['redirect', 'forward', 'addFlashMessage'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function listActionFetchesAllMediasFromRepositoryAndAssignsThemToView()
    {

        $allMedias = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mediaRepository = $this->getMockBuilder(\JVE\JvMediaConnector\Domain\Repository\MediaRepository::class)
            ->setMethods(['findAll'])
            ->disableOriginalConstructor()
            ->getMock();
        $mediaRepository->expects(self::once())->method('findAll')->will(self::returnValue($allMedias));
        $this->inject($this->subject, 'mediaRepository', $mediaRepository);

        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
        $view->expects(self::once())->method('assign')->with('medias', $allMedias);
        $this->inject($this->subject, 'view', $view);

        $this->subject->listAction();
    }

    /**
     * @test
     */
    public function createActionAddsTheGivenMediaToMediaRepository()
    {
        $media = new \JVE\JvMediaConnector\Domain\Model\Media();

        $mediaRepository = $this->getMockBuilder(\JVE\JvMediaConnector\Domain\Repository\MediaRepository::class)
            ->setMethods(['add'])
            ->disableOriginalConstructor()
            ->getMock();

        $mediaRepository->expects(self::once())->method('add')->with($media);
        $this->inject($this->subject, 'mediaRepository', $mediaRepository);

        $this->subject->createAction($media);
    }

    /**
     * @test
     */
    public function deleteActionRemovesTheGivenMediaFromMediaRepository()
    {
        $media = new \JVE\JvMediaConnector\Domain\Model\Media();

        $mediaRepository = $this->getMockBuilder(\JVE\JvMediaConnector\Domain\Repository\MediaRepository::class)
            ->setMethods(['remove'])
            ->disableOriginalConstructor()
            ->getMock();

        $mediaRepository->expects(self::once())->method('remove')->with($media);
        $this->inject($this->subject, 'mediaRepository', $mediaRepository);

        $this->subject->deleteAction($media);
    }
}
