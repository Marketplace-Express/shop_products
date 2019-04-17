<?php

namespace Shop_products\Models;

use Exception;
use Phalcon\Mvc\Model\ResultInterface;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\ResultSetInterface;

/**
 * ProductImages
 * 
 * @package Shop_products\Models
 * @autogenerated by Phalcon Developer Tools
 * @date 2019-01-11, 16:39:33
 */
class ProductImages extends BaseModel
{
    /**
     *
     * @var string
     */
    public $imageId;

    /**
     *
     * @var string
     */
    public $productId;

    /**
     *
     * @var string
     */
    public $imageLink;

    /**
     * @var int
     */
    public $imageSize;

    /**
     * @var string
     */
    public $imageType;

    /**
     * @var int
     */
    public $imageWidth;

    /**
     * @var int
     */
    public $imageHeight;

    /**
     * @var string
     */
    public $imageDeleteHash;

    /**
     * @var string
     */
    public $imageName;

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
     * @throws Exception
     */
    public function initialize()
    {
        $this->defaultBehavior();
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
     * @return ProductImages[]|ProductImages|ResultSetInterface
     */
    public static function find($parameters = null)
    {
        $operator = '';
        if (!array_key_exists('conditions', $parameters)) {
            $parameters['conditions'] = '';
        }
        if (!empty($parameters['conditions'])) {
            $operator = ' AND ';
        }
        $parameters['conditions'] .= $operator.'isDeleted = false';
        $parameters['hydration'] = Resultset::HYDRATE_RECORDS;
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ProductImages|ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        $query = self::find($parameters);
        return $query->getFirst();
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
            'image_id' => 'imageId',
            'product_id' => 'productId',
            'image_link' => 'imageLink',
            'image_size' => 'imageSize',
            'image_type' => 'imageType',
            'image_width' => 'imageWidth',
            'image_height' => 'imageHeight',
            'image_delete_hash' => 'imageDeleteHash',
            'image_name' => 'imageName',
            'created_at' => 'createdAt',
            'deleted_at' => 'deletedAt',
            'is_deleted' => 'isDeleted'
        ];
    }

    /**
     * @return array
     */
    public function toApiArray()
    {
        return [
            'imageId' => $this->imageId,
            'imageLink' => $this->imageLink,
            'productId' => $this->productId,
            'imageWidth' => $this->imageWidth,
            'imageHeight' => $this->imageHeight,
            'imageType' => $this->imageType,
            'imageDeleteHash' => $this->imageDeleteHash
        ];
    }

}
