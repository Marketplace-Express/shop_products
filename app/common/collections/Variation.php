<?php
/**
 * User: Wajdi Jurry
 * Date: 19/01/19
 * Time: 11:17 م
 */

namespace app\common\collections;


use Phalcon\Validation;
use app\common\utils\UuidUtil;
use app\common\validators\TypeValidator;

class Variation extends BaseCollection
{
    /** @var string */
    public $variation_id;

    /** @var string */
    public $product_id;

    /** @var int */
    public $quantity;

    /** @var string */
    public $image;

    /** @var float */
    public $price;

    /** @var float */
    public $sale_price;

    /** @var string */
    public $end_sale_time;

    /** @var string */
    public $sku;

    /** @var array */
    public $attributes;

    /** @var array */
    public $custom_attributes;

    /** @var array */
    public $conditions;

    /** @var string */
    public $created_at;

    /** @var string */
    public $updated_at;

    /** @var string */
    public $deleted_at;

    /** @var bool */
    public $is_deleted;

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
     * Collection name
     * @return string
     */
    public function getSource()
    {
        return 'product_variations';
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
            new Validation\Validator\Callback([
                'callback' => function($data) {
                    if (!(new UuidUtil())->isValid($data['product_id'])) {
                        return false;
                    }
                    return true;
                },
                'message' => 'Invalid product id'
            ])
        );

        $validation->add(
            'quantity',
            new Validation\Validator\NumericValidator([
                'allowFloat' => false,
                'min' => 0,
                'allowEmpty' => true,
                'message' => 'Invalid quantity'
            ])
        );

        $validation->add(
            'image',
            new Validation\Validator\Callback([
                'callback' => function($data) {
                    if (!empty($data['image']) && !(new UuidUtil())->isValid($data['image'])) {
                        return false;
                    }
                    return true;
                },
                'message' => 'Invalid image id'
            ])
        );

        $validation->add(
            ['price', 'sale_price'],
            new Validation\Validator\NumericValidator([
                'allowFloat' => true,
                'min' => 0,
                'allowEmpty' => true,
                'message' => 'Invalid price value'
            ])
        );

        $validation->add(
            'end_sale_time',
            new Validation\Validator\Callback([
                'callback' => function($data) {
                    if (!empty($data['end_sale_time'] && strtotime($data['end_sale_time']) > time())) {
                        return new Validation\Validator\Date([
                            'format' => 'Y-m-d H:i:s'
                        ]);
                    }
                    return true;
                },
                'message' => 'Invalid date format or Date should be greater than now'
            ])
        );

        $validation->add(
            'sku',
            new Validation\Validator\AlphaNumericValidator([
                'allowEmpty' => false
            ])
        );

        $validation->add(
            ['attributes', 'custom_attributes', 'conditions'],
            new Validation\Validator\Callback([
                'callback' => function($data) {
                    if (!empty($data['attributes'] && !is_array($data['attributes'])
                        || !empty($data['custom_attributes'] && !is_array($data['custom_attributes']))
                        || !empty($data['conditions'] && !is_array($data['conditions'])))
                    ) {
                        return false;
                    }
                    return true;
                },
                'message' => 'Invalid :field'
            ])
        );

        // Check conditions duplicated entries in same include or exclude
        $validation->add(
            'conditions',
            new Validation\Validator\Callback([
                'callback' => function($data) {
                    $includeCountries = $data['conditions']['include']['countries'];
                    $excludeCountries = $data['conditions']['exclude']['countries'];
                    if (array_unique($includeCountries) !== $includeCountries
                        || array_unique($excludeCountries) !== $excludeCountries
                    ) {
                        return false;
                    }
                    return true;
                },
                'message' => 'There are duplicated entries'
            ])
        );

        // Check conditions duplicate in include and exclude
        $validation->add(
            'conditions',
            new Validation\Validator\Callback([
                'callback' => function ($data) {
                    $includeCountries = $data['conditions']['include']['countries'];
                    $excludeCountries = $data['conditions']['exclude']['countries'];
                    if ((!empty($includeCountries) || !empty($excludeCountries))
                        && count(array_intersect($includeCountries, $excludeCountries))) {
                        return false;
                    }
                    return true;
                },
                'message' => 'Included values should not exist in excluded values'
            ])
        );

        $message = $validation->validate([
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'image' => $this->image,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'end_sale_time' => $this->end_sale_time,
            'sku' => $this->sku,
            'attributes' => $this->attributes,
            'custom_attributes' => $this->custom_attributes,
            'conditions' => $this->conditions
        ]);

        if (count($message)) {
            return false;
        }
        return true;
    }

}