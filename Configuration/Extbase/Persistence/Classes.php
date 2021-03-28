<?php
/**
 * Classes.php
 *
 * @author Klaus Fiedler <klaus@tollwerk.de> / @jkphl
 * @copyright Copyright © 2019 Klaus Fiedler <klaus@tollwerk.de>
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *   persistence {
*       classes {
*          JVE\JvMediaConnector\Domain\Model\FileReference {
*                mapping {
*                   tableName = sys_file_reference
*                   columns {
*                      uid_local.mapOnProperty = originalFileIdentifier
*                   }
*                }
*          }
*       }
 ***********************************************************************************/


declare(strict_types=1);
return [
    \JVE\JvMediaConnector\Domain\Model\FileReference::class => [
        'tableName' => 'sys_file_reference',
        'properties' => [
            'originalFileIdentifier' => [
                'fieldName' => 'uid_local',
            ],
        ],
    ],
];
