<?php

namespace Shop_products\Models;

class RateImages extends BaseModel
{

    const MODEL_ALIAS = 'ri';

    /**
     * @var string
     * @Primary
     * @Column(column="image_id", type="string", length=36)
     */
    public $imageId;

    /**
     * @var string
     * @Column(column="rate_id", type="string", length=36)
     */
    public $rateId;

    /**
     * @var string
     * @Column(column="image_link", type="string")
     */
    public $imageLink;

    /**
     * @var string
     * @Column(column="created_at", type="string")
     */
    public $createdAt;

    /**
     * @var string
     * @Column(column="deleted_at", type="string", nullable=true)
     */
    public $deletedAt;

    /**
     * @var integer
     * @Column(column="is_deleted", type="integer", length=1, default=0)
     */
    public $isDeleted;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema('shop_products');
        $this->setSource('rate_images');
        $this->belongsTo(
            'rate_id',
            ProductRates::class,
            'rate_id',
            [
                'reusable' => true
            ]
        );
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'rate_images';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return RateImages[]|RateImages|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return RateImages|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /**
     * @return array
     */
    public function columnMap()
    {
        return [
            'image_id' => 'imageId',
            'rate_id' => 'rateId',
            'image_link' => 'imageLink',
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
            'rateId' => $this->rateId,
            'imageLink' => $this->imageLink,
        ];
    }

}
