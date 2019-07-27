<?php
/**
 * User: Wajdi Jurry
 * Date: 18/05/19
 * Time: 04:25 م
 */

namespace app\common\models;


class ProductImagesSizes extends BaseModel
{

    /**
     * @var int
     * @Primary
     * @Identity
     * @Column(column='row_id', type="int", legnth=11)
     */
    public $rowId;

    /**
     * @var string
     * @Column(column='image_id', type="string", length=10)
     */
    public $imageId;

    /**
     * @var string
     * @Column(column='small', type='string')
     */
    public $small;

    /**
     * @var string
     * @Column(column='big', type='string')
     */
    public $big;

    /**
     * @var string
     * @Column(column='thumb', type='string')
     */
    public $thumb;

    /**
     * @var string
     * @Column(column='medium', type='string')
     */
    public $medium;

    /**
     * @var string
     * @Column(column='large', type='string')
     */
    public $large;

    /**
     * @var string
     * @Column(column='huge', type='string')
     */
    public $huge;

    /**
     * @return string
     */
    public function getSource()
    {
        return 'product_images_sizes';
    }

    public function initialize()
    {
        $this->belongsTo(
            'imageId',
            ProductImages::class,
            'imageId',
            [
                'reusable' => true
            ]
        );
    }

    /**
     * @param bool $new
     * @return self
     */
    public static function model(bool $new = false)
    {
        return parent::model($new);
    }

    public function toApiArray()
    {
        return [
            'small' => $this->small,
            'big' => $this->big,
            'thumb' => $this->thumb,
            'medium' => $this->medium,
            'large' => $this->large,
            'huge' => $this->huge
        ];
    }
}