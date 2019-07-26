<?php
/**
 * User: Wajdi Jurry
 * Date: 08/03/19
 * Time: 12:24 م
 */

namespace Shop_products\RequestHandler;


use Shop_products\Enums\ProductTypesEnum;
use Shop_products\RequestHandler\Product\CreateDownloadableProductRequestHandler;
use Shop_products\RequestHandler\Product\CreatePhysicalProductRequestHandler;

class ProductRequestResolver
{

    const PHYSICAL_PRODUCT = CreatePhysicalProductRequestHandler::class;
    const DOWNLOADABLE_PRODUCT = CreateDownloadableProductRequestHandler::class;

    public $type;

    /**
     * @return mixed
     * @throws \Exception
     */
    public function resolve()
    {
        $validTypes = [
            ProductTypesEnum::TYPE_PHYSICAL => self::PHYSICAL_PRODUCT,
            ProductTypesEnum::TYPE_DOWNLOADABLE => self::DOWNLOADABLE_PRODUCT
        ];

        if (!array_key_exists($this->type, $validTypes)) {
            throw new \Exception('Invalid product type', 400);
        }

        return new $validTypes[$this->type];
    }
}