<?php
/**
 * User: Wajdi Jurry
 * Date: 21/01/19
 * Time: 09:33 م
 */

namespace Shop_products\Collections;


use Phalcon\Validation;
use Shop_products\Utils\UuidUtil;
use Shop_products\Validators\SegmentsValidator;
use Shop_products\Validators\TypeValidator;
use Shop_products\Validators\UuidValidator;

class Product extends BaseCollection
{
    /** @var string */
    public $product_id;

    /** @var array */
    public $dimensions;

    /** @var array */
    public $keywords;

    /** @var \stdClass */
    public $segments;

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
        $parameters[0]['is_deleted'] = false;
        return parent::find($parameters);
    }

    /**
     * @param array|null $parameters
     * @return array|Variation|bool
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
        throw new \Exception('Update not supported. Use save() instead', 500);
    }

    public function validation()
    {
        $validation = new Validation();

        $validation->add(
            'product_id',
            new UuidValidator()
        );

        if ($this->dimensions) {
            $validation->add(
                'dimensions',
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

        $messages = $validation->validate([
            'product_id' => $this->product_id,
            'dimensions' => $this->dimensions,
            'keywords' => $this->keywords,
            'segments' => $this->segments
        ]);

        if (count($messages)) {
            $this->_errorMessages = $messages;
            return false;
        }
        return true;
    }
}