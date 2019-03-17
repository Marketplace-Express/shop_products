<?php

namespace Shop_products\Models;

/**
 * ProductImages
 * 
 * @package Shop_products\Models
 * @autogenerated by Phalcon Developer Tools
 * @date 2019-01-11, 16:39:33
 */
class ProductImages extends Base
{

    /**
     *
     * @var string
     */
    public $productImageId;

    /**
     *
     * @var string
     */
    public $productId;

    /**
     *
     * @var string
     */
    public $imageFile;

    /**
     *
     * @var string
     */
    public $createdAt;

    /**
     *
     * @var string
     */
    public $deletedAt;

    /**
     *
     * @var integer
     */
    public $isDeleted;

    public function onConstruct()
    {
        self::$instance = $this;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("shop_products");
        $this->setSource("product_images");
        $this->belongsTo('productId', 'Shop_products\Models\Product', 'productId', ['alias' => 'Product']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'product_images';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ProductImages[]|ProductImages|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ProductImages|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /**
     * Independent Column Mapping.
     * Keys are the real names in the table and the values their names in the application
     *
     * @return array
     */
    public function columnMap()
    {
        return [
            'product_image_id' => 'productImageId',
            'product_id' => 'productId',
            'image_file' => 'imageFile',
            'created_at' => 'createdAt',
            'deleted_at' => 'deletedAt',
            'is_deleted' => 'isDeleted'
        ];
    }

}
