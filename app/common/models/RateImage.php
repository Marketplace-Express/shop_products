<?php

namespace app\common\models;

use app\common\interfaces\ApiArrayData;
use app\common\models\resultset\RateImageResultSet;
use app\common\validators\ExistenceValidator;
use app\common\validators\UuidValidator;
use Phalcon\Mvc\Model\Behavior\Timestampable;
use Phalcon\Validation;

/**
 * Class RateImages
 * @package app\common\models
 * @property Image $image
 * @property ProductRates $rate
 */
class RateImage extends BaseModel implements ApiArrayData
{
    /**
     * @var int
     * @Primary
     * @Identity
     * @Column(column='row_id', type='bigint', nullable=false)
     */
    public $rowId;

    /**
     *
     * @var string
     * @Column(column='image_id', type='varchar', length=36, nullable=false)
     */
    public $imageId;

    /**
     *
     * @var string
     * @Column(column='rate_id', type='varchar', length=36, nullable=false)
     */
    public $rateId;

    /**
     *
     * @var string
     * @Column(column='created_at', type='datetime')
     */
    public $createdAt;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("shop_products");
        $this->setSource("rate_images");

        $this->addBehavior(new Timestampable([
            'beforeValidationOnCreate' => [
                'field' => 'createdAt',
                'format' => self::$dateFormat
            ]
        ]));

        $this->belongsTo(
            'imageId',
            Image::class,
            'imageId',
            [
                'alias' => 'image',
                'conditions' => 'isDeleted = false',
                'foreignKey' => true
            ]
        );

        $this->belongsTo(
            'rateId',
            ProductRates::class,
            'rateId',
            [
                'alias' => 'rate',
                'conditions' => 'isDeleted = false',
                'foreignKey' => true
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
     * @return string
     */
    public function getResultsetClass()
    {
        return RateImageResultSet::class;
    }

    public function beforeDelete()
    {
        $this->operationMode = $this->_operationMade;
    }

    public function afterSave()
    {
        if ($this->operationMode == self::OP_DELETE) {
            $this->afterDelete();
            return;
        }
        $this->image->save(['isUsed' => true]);
    }

    public function afterDelete()
    {
        $this->image->delete();
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
            'row_id' => 'rowId',
            'image_id' => 'imageId',
            'rate_id' => 'rateId',
            'created_at' => 'createdAt',
        ];
    }

    /**
     * @return bool
     */
    public function validation(): bool
    {
        $validation = new Validation();

        $validation->add(
            'rateId',
            new UuidValidator()
        );

        $validation->add(
            ['rateId', 'imageId'],
            new Validation\Validator\Uniqueness([
                'message' => 'The image is already used',
            ])
        );

        $validation->add(
            'imageId',
            new ExistenceValidator([
                'model' => Image::class,
                'column' => 'imageId',
                'conditions' => [
                    'where' => 'isRateImage = true AND isDeleted = false',
                    'bind' => []
                ],
                'message' => 'Image does not exist'
            ])
        );

        $this->_errorMessages = $validation->validate([
            'imageId' => $this->imageId,
            'rateId' => $this->rateId
        ]);

        return !count($this->_errorMessages);
    }

    /**
     * @return array
     */
    public function toApiArray(): array
    {
        return $this->image->toApiArray();
    }
}
