<?php
namespace JVE\JvMediaConnector\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Annotation as Extbase ;


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
class Media extends AbstractEntity
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
     * @var FrontendUser
     * @Extbase\ORM\Lazy
     */
    protected $feuser = null;

    /**
     * $sysfile
     * @var FileReference
     */
    protected $sysfile ;



    /**
     * Initializes all ObjectStorage properties
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->sysfile = new FileReference ;
    }

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
     * @return FrontendUser $feuser
     */
    public function getFeuser()
    {
        if($this->feuser instanceof LazyLoadingProxy ) {
            return $this->feuser->_loadRealInstance() ;
        }
        return $this->feuser;
    }

    /**
     * Sets the feuser
     *
     * @param FrontendUser $feuser
     * @return void
     */
    public function setFeuser(FrontendUser $feuser)
    {
        $this->feuser = $feuser;
    }

    /**
     * sets  the sysfile
     *
     */
    public function setSysfile(FileReference $sysfile)
    {
        $this->sysfile = $sysfile ;
    }

    /**
     * Returns the sysfile
     *
     * @return FileReference $sysfile
     */
    public function getSysfile()
    {
        return $this->sysfile;
    }


}
