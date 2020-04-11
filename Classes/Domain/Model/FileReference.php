<?php
namespace JVE\JvMediaConnector\Domain\Model;

use TYPO3\CMS\Core\Resource\File;

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
     * Uid of the referenced sys_file. Needed for extbase to serialize the
     * reference correctly.
     *
     * @var int
     */
    protected $uidLocal;

    /**
     * @param File $file
     */
    public function setFile(File $file) {
        $this->originalFileIdentifier = (int)$file->getUid();
    }

    /**
     * @param string $tablename
     */
    public function setTablenames( $tablename) {
        $this->tablenames = trim($tablename) ;
    }

    /**
     * @param string $table
     */
    public function setTableLocal( $table) {
        $this->tableLocal = trim($table) ;
    }

    /**
     * @param string $field
     */
    public function setFieldname( $field) {
        $this->fieldname = trim($field) ;
    }


    /**
     * @param string $link
     */
    public function setLink( $link) {
        $this->link = trim($link) ;
    }

    /**
     * @param int $uidLocal
     */
    public function setUidLocal($uidLocal)
    {
        $this->uidLocal = $uidLocal ;
    }

    /**
     * @param int $uidForeign
     */
    public function setUidForeign($uidForeign)
    {
        $this->uidForeign = $uidForeign ;
    }

    /**
     * @return int
     */
    public function getUidLocal()
    {
        return $this->uidLocal;
    }


}
