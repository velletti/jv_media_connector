<?php
namespace JVE\JvMediaConnector\Tests\Unit\Controller;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use JVE\JvMediaConnector\Controller\MediaController;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use JVE\JvMediaConnector\Domain\Repository\MediaRepository;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use JVE\JvMediaConnector\Domain\Model\Media;
/**
 * Test case.
 *
 * @author Jörg Velletti <typo3@velletti.de>
 */
class MediaControllerTest extends UnitTestCase
{
    /**
     * @var MediaController
     */
    protected $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = $this->getMockBuilder(MediaController::class)
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

        $allMedias = $this->getMockBuilder(ObjectStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mediaRepository = $this->getMockBuilder(MediaRepository::class)
            ->setMethods(['findAll'])
            ->disableOriginalConstructor()
            ->getMock();
        $mediaRepository->expects(self::once())->method('findAll')->will(self::returnValue($allMedias));
        $this->inject($this->subject, 'mediaRepository', $mediaRepository);

        $view = $this->getMockBuilder(ViewInterface::class)->getMock();
        $view->expects(self::once())->method('assign')->with('medias', $allMedias);
        $this->inject($this->subject, 'view', $view);

        $this->subject->listAction();
    }

    /**
     * @test
     */
    public function createActionAddsTheGivenMediaToMediaRepository()
    {
        $media = new Media();

        $mediaRepository = $this->getMockBuilder(MediaRepository::class)
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
        $media = new Media();

        $mediaRepository = $this->getMockBuilder(MediaRepository::class)
            ->setMethods(['remove'])
            ->disableOriginalConstructor()
            ->getMock();

        $mediaRepository->expects(self::once())->method('remove')->with($media);
        $this->inject($this->subject, 'mediaRepository', $mediaRepository);

        $this->subject->deleteAction($media);
    }
}
