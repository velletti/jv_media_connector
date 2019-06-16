<?php
namespace JVE\JvMediaConnector\Domain\Model;

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
 * Connection between Uploaded File and Frontend User
 */
class Media extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * usecount
     *
     * @var int
     */
    protected $usecount = 0;

    /**
     * user who had uploaded this image
     *
     * @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
     * @lazy
     */
    protected $feuser = null;

    /**
     * $sysfile
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $sysfile ;





    /**
     * Returns the usecount
     *
     * @return int $usecount
     */
    public function getUsecount()
    {
        return $this->usecount;
    }

    /**
     * Sets the usecount
     *
     * @param int $usecount
     * @return void
     */
    public function setUsecount($usecount)
    {
        $this->usecount = $usecount;
    }

    /**
     * Returns the feuser
     *
     * @return \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $feuser
     */
    public function getFeuser()
    {
        return $this->feuser;
    }

    /**
     * Sets the feuser
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $feuser
     * @return void
     */
    public function setFeuser(\TYPO3\CMS\Extbase\Domain\Model\FrontendUser $feuser)
    {
        $this->feuser = $feuser;
    }

    /**
     * Returns the sysfile
     *
     * @return
     */
    public function setSysfile(\TYPO3\CMS\Extbase\Domain\Model\FileReference $sysfile)
    {
        $this->sysfile = $sysfile ;
    }

    /**
     * Returns the sysfile
     *
     * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference $sysfile
     */
    public function getSysfile()
    {
        return $this->sysfile;
    }

    /**
     * Sets the sysfile
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $sysfile
     * @return void
     */
    public function addSysfile(\TYPO3\CMS\Extbase\Domain\Model\FileReference $sysfile)
    {
        $this->sysfile->attach($sysfile);
    }
}
