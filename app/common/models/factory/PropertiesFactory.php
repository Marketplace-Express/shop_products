<?php
/**
 * User: Wajdi Jurry
 * Date: 3/27/20
 * Time: 7:51 PM
 */

namespace app\common\models\factory;



use app\common\models\embedded\DownloadableProperties;
use app\common\models\embedded\PhysicalProperties;
use app\common\models\Product;

class PropertiesFactory
{
    const TYPES = [
        'physical' => PhysicalProperties::class,
        'downloadable' => DownloadableProperties::class
    ];

    /**
     * @param Product $product
     * @param array $data
     * @return DownloadableProperties|PhysicalProperties
     */
    static public function create(Product $product, array $data = [])
    {
        /** @var PhysicalProperties|DownloadableProperties $properties */
        $properties = self::TYPES[$product->productType];
        $properties = new $properties;
        if ($data) {
            $properties->setAttributes($data);
        }
        return $properties;
    }
}