<?php
/**
 * User: Wajdi Jurry
 * Date: ٧‏/١٢‏/٢٠١٩
 * Time: ٦:١٥ م
 */

namespace app\common\models\embedded;


use app\common\collections\BaseCollection;
use app\common\traits\ModelCollectionBehaviorTrait;
use app\common\validators\UuidValidator;
use MongoDB\BSON\UTCDateTime;
use Phalcon\Validation;

class VariationProperties extends BaseCollection
{
    use ModelCollectionBehaviorTrait;

    /**
     * @var string
     */
    public $variationId;

    /**
     * @var string
     */
    public $productId;

    /**
     * @var array
     */
    public $attributes = [];

    /**
     * @var bool
     */
    public $is_deleted = false;

    /**
     * @var UTCDateTime
     */
    public $created_at;

    /**
     * @var UTCDateTime
     */
    public $deleted_at;

    /**
     * @return string
     */
    public function getSource(): string
    {
        return 'variation_properties';
    }

    public function initialize()
    {
        $this->defaultBehavior();
    }

    /**
     * @param array|null $parameters
     * @return array|null
     */
    static public function find(array $parameters = null)
    {
        if ($parameters) {
            if (!empty($parameters['conditions'])) {
                $parameters['conditions']['is_deleted'] = false;
            } else {
                $parameters[0]['is_deleted'] = false;
            }
        }
        return parent::find($parameters);
    }

    /**
     * @param array|null $parameters
     * @return array|null
     */
    static public function findFirst(array $parameters = null)
    {
        $properties = self::find($parameters);
        return array_shift($properties);
    }

    /**
     * @return array
     */
    public function toApiArray(): array
    {
        return [
            'attributes' => $this->attributes
        ];
    }

    /**
     * @return bool
     */
    public function validation(): bool
    {
        $validation = new Validation();

        $validation->add(
            ['variationId', 'productId'],
            new UuidValidator([
                'allowEmpty' => false
            ])
        );

        // TODO: validate attributes from categories service

        $this->_errorMessages = $validation->validate([
            'variationId' => $this->variationId,
            'productId' => $this->productId
        ]);

        return !count($this->_errorMessages);
    }
}
