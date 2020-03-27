<?php
/**
 * User: Wajdi Jurry
 * Date: 3/27/20
 * Time: 7:51 PM
 */

namespace app\common\models\factory;



use app\common\models\embedded\DownloadableProperties;
use app\common\models\embedded\PhysicalProperties;

class PropertiesFactory
{
    const TYPES = [
        'physical' => PhysicalProperties::class,
        'downloadable' => DownloadableProperties::class
    ];

    /**
     * @param string $type
     * @param array $data
     * @return DownloadableProperties|PhysicalProperties
     */
    static public function create(string $type, array $data)
    {
        /** @var PhysicalProperties|DownloadableProperties $properties */
        $properties = self::TYPES[$type];
        $properties = new $properties;
        $properties->setAttributes($data);
        return $properties;
    }
}