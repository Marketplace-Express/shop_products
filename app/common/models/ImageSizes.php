<?php
/**
 * User: Wajdi Jurry
 * Date: 18/05/19
 * Time: 04:25 م
 */

namespace app\common\models;

use app\common\interfaces\ApiArrayData;
use Phalcon\Mvc\Model\Resultset;

class ImageSizes extends BaseModel implements ApiArrayData
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
     * @var bool
     * @Column(column='is_deleted', type='boolean')
     */
    public $isDeleted = false;

    /**
     * @var string
     * @Column(column='deleted_at', type='datetime', nullable=true)
     */
    public $deletedAt;

    /**
     * @return string
     */
    public function getSource()
    {
        return 'images_sizes';
    }

    /**
     * @throws \Exception
     */
    public function initialize()
    {
        $this->defaultBehavior();

        $this->belongsTo(
            'imageId',
            Image::class,
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

    /**
     * @param null $parameters
     * @return \Phalcon\Mvc\Model\ResultsetInterface|ImageSizes[]
     */
    static public function find($parameters = null)
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
     * @return ImageSizes
     */
    public static function findFirst($parameters = null)
    {
        $query = self::find($parameters);
        return $query->getFirst();
    }

    /**
     * @return array
     */
    public function toApiArray(): array
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
