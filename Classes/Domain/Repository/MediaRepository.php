<?php
namespace JVE\JvMediaConnector\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
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
 * The repository for Medias
 */
class MediaRepository extends Repository
{
    /**
     * @var array
     */
    protected $defaultOrderings = array(
        'crdate' => QueryInterface::ORDER_DESCENDING
    );

    public function findByUserAllpages($userUid = 0 ,  $ignoreEnableFields = FALSE , $uid = 0 )
    {
        $query = $this->createQuery();
        $querySettings = $query->getQuerySettings() ;
        $querySettings->setRespectStoragePage(false);
        $querySettings->setRespectSysLanguage(FALSE);
        $querySettings->setIgnoreEnableFields($ignoreEnableFields) ;
        $query->setQuerySettings($querySettings) ;
        if ( $uid > 0 ) {
            $query->matching(
                $query->logicalAnd(
                    $query->equals('uid', $uid ),
                    $query->equals('feuser.uid', $userUid )

                )

            ) ;
        } else {
            $query->matching(
                $query->logicalAnd(
                    $query->equals('feuser.uid', $userUid ) )
                );
        }

        $res = $query->execute() ;
        return $res ;
    }
}
