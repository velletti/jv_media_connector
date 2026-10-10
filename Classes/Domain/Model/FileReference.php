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
     * @var ?int
     */
    protected ?int $uidLocal= null;

    /**
     * @param File $file
     */
    public function setFile(File $file): void {
        $this->originalFileIdentifier = (int)$file->getUid();
    }

    /**
     * @param string $tablename
     */
    public function setTablenames( $tablename): void {
        $this->tablenames = trim($tablename) ;
    }

    /**
     * @param string $table
     */
    public function setTableLocal( $table): void {
        $this->tableLocal = trim($table) ;
    }

    /**
     * @param string $field
     */
    public function setFieldname( $field): void {
        $this->fieldname = trim($field) ;
    }


    /**
     * @param string $link
     */
    public function setLink( $link): void {
        $this->link = trim($link) ;
    }

    /**
     * @param int $uidLocal
     */
    public function setUidLocal(?int $uidLocal): void
    {
        $this->uidLocal = $uidLocal ;
    }

    /**
     * @param int $uidForeign
     */
    public function setUidForeign($uidForeign): void
    {
        $this->uidForeign = $uidForeign ;
    }

    /**
     * @return int
     */
    public function getUidLocal():?int
    {
        return $this->uidLocal;
    }


}
