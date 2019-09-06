<?php
/**
 * User: Wajdi Jurry
 * Date: 21/01/19
 * Time: 09:33 م
 */

namespace app\common\collections;


use app\common\models\embedded\physical\PackageDimensions;
use Phalcon\Validation;
use app\common\validators\SegmentsValidator;
use app\common\validators\TypeValidator;
use app\common\validators\UuidValidator;

/**
 * Class Product
 * @package app\common\collections
 */
class Product extends BaseCollection
{
    /** @var string */
    public $product_id;

    /** @var PackageDimensions */
    public $packageDimensions;

    /** @var array */
    public $keywords;

    /** @var \stdClass */
    public $segments;

    private $_oldOperationMade;

    /** @var bool */
    public $is_deleted = false;

    public function getSource()
    {
        return 'product';
    }

    public function setAttributes(array $attributes)
    {
        foreach ($attributes as $attribute => $value) {
            $this->$attribute = $value;
        }
    }

    /**
     * @param array|null $parameters
     * @return Variation[]
     */
    public static function find(array $parameters = null)
    {
        $parameters['conditions']['is_deleted'] = false;
        return parent::find($parameters);
    }

    /**
     * @param array|null $parameters
     * @return Product|bool|array
     */
    public static function findFirst(array $parameters = null)
    {
        $parameters[0]['is_deleted'] = false;
        return parent::findFirst($parameters);
    }

    /**
     * @param mixed $id
     * @return array|Variation|bool
     */
    public static function findById($id)
    {
        return parent::findById($id);
    }

    /**
     * @throws \Exception
     */
    public function initialize()
    {
        $this->defaultBehavior();
    }

    /**
     * @return bool|void
     * @throws \Exception
     */
    public function update()
    {
        throw new \Exception('Update not supported. Use save() instead', 503);
    }

    /**
     * Over-ride operation made when deleting a document
     * To prevent execute validation
     *
     * Default -> self::OP_UPDATE
     * Update -> self::OP_DELETE
     */
    public function beforeValidationOnUpdate()
    {
        if ($this->is_deleted) {
            $this->_oldOperationMade = $this->_operationMade;
            $this->_operationMade = self::OP_DELETE;
        }
    }

    /**
     * @return array
     */
    public function toApiArray()
    {
        $data = [
            'packageDimensions' => $this->packageDimensions,
            'productKeywords' => $this->keywords,
            'productSegments' => $this->segments
        ];

        if ($this->packageDimensions == null) {
            // for downloadable products
            unset($data['packageDimensions']);
        }

        return $data;
    }

    /**
     * @return bool
     */
    public function validation()
    {
        if ($this->_operationMade == self::OP_DELETE) {
            $this->_operationMade = $this->_oldOperationMade;
            return true;
        }

        $validation = new Validation();

        $validation->add(
            'product_id',
            new UuidValidator()
        );

        if ($this->packageDimensions) {
            $validation->add(
                'packageDimensions',
                new TypeValidator([
                    'type' => TypeValidator::TYPE_FLOAT,
                    'allowEmpty' => false,
                    'message' => 'Invalid Dimensions'
                ])
            );
        }

        $validation->add(
            'keywords',
            new Validation\Validator\Callback([
                'callback' => function($data) {
                    if (!empty($data['keywords']) && !is_array($data['keywords'])) {
                        return false;
                    }
                    return true;
                },
                'message' => 'Invalid keywords'
            ])
        );

        $validation->add(
            'segments',
            new SegmentsValidator()
        );

        $this->_errorMessages = $validation->validate([
            'product_id' => $this->product_id,
            'packageDimensions' => $this->packageDimensions->dimensions,
            'packageDimensionsUnit' => $this->packageDimensions->unit,
            'keywords' => $this->keywords,
            'segments' => $this->segments
        ]);

        return !$this->_errorMessages->count();
    }
}
