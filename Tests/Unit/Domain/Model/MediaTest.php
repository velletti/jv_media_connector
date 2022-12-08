<?php
namespace JVE\JvMediaConnector\Tests\Unit\Domain\Model;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use JVE\JvMediaConnector\Domain\Model\Media;
/**
 * Test case.
 *
 * @author Jörg Velletti <typo3@velletti.de>
 */
class MediaTest extends UnitTestCase
{
    /**
     * @var Media
     */
    protected $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = new Media();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getUserpathReturnsInitialValueForString()
    {
        self::assertSame(
            '',
            $this->subject->getUserpath()
        );
    }

    /**
     * @test
     */
    public function setUserpathForStringSetsUserpath()
    {
        $this->subject->setUserpath('Conceived at T3CON10');

        self::assertAttributeEquals(
            'Conceived at T3CON10',
            'userpath',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function getUsecountReturnsInitialValueForInt()
    {
        self::assertSame(
            0,
            $this->subject->getUsecount()
        );
    }

    /**
     * @test
     */
    public function setUsecountForIntSetsUsecount()
    {
        $this->subject->setUsecount(12);

        self::assertAttributeEquals(
            12,
            'usecount',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function getFeuserReturnsInitialValueForFrontendUser()
    {
    }

    /**
     * @test
     */
    public function setFeuserForFrontendUserSetsFeuser()
    {
    }

    /**
     * @test
     */
    public function getSysfileReturnsInitialValueFor()
    {
    }

    /**
     * @test
     */
    public function setSysfileForSetsSysfile()
    {
    }
}
