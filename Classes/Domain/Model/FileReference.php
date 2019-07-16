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
 * see https://github.com/fabarea/media_upload
 */
class FileReference extends \TYPO3\CMS\Extbase\Domain\Model\FileReference {

    /**
     * @params \TYPO3\CMS\Core\Resource\File $file
     */
    public function setFile(\TYPO3\CMS\Core\Resource\File $file) {
        $this->originalFileIdentifier = (int)$file->getUid();
    }

    /**
     * @params string $tablename
     */
    public function setTablenames( $tablename) {
        $this->tablenames = trim($tablename) ;
    }

    /**
     * @params string $table
     */
    public function setTableLocal( $table) {
        $this->tableLocal = trim($table) ;
    }

    /**
     * @return int
     */
    public function getUidLocal()
    {
        return $this->uidLocal;
    }


}
