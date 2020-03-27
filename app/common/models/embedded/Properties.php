<?php
/**
 * User: Wajdi Jurry
 * Date: 21/01/19
 * Time: 09:33 م
 */

namespace app\common\models\embedded;


use app\common\models\BaseCollection;
use Phalcon\Validation;
use app\common\validators\{PackageDimensionsValidator, SegmentsValidator, TypeValidator, UuidValidator};

/**
 * Class Properties
 * @package app\common\models\embedded
 */
class Properties extends BaseCollection
{
    /** @var string */
    public $product_id;

    /** @var array */
    public $keywords;

    /** @var Segment */
    public $segments;

    /** @var bool */
    public $is_deleted = false;

    private $_oldOperationMade;

    /**
     * @return string
     */
    public function getSource(): string
    {
        return 'properties';
    }

    public function initialize()
    {
        $this->defaultBehavior();
        $this->segments = new Segment();
    }

    /**
     * @param array $data
     */
    public function setAttributes(array $data)
    {
        $this->product_id = $data['productId'];
        $this->keywords = $data['productKeywords'] ?? [];
        if (!empty($data['productSegments'])) {
            $this->segments->setAttributes($data['productSegments']);
        }
    }

    /**
     * @param array|null $parameters
     * @return Properties[]
     */
    public static function find(array $parameters = null)
    {
        $parameters['conditions']['is_deleted'] = false;
        return parent::find($parameters);
    }

    /**
     * @param array|null $parameters
     * @return Properties|null
     */
    public static function findFirst(array $parameters = null)
    {
        $parameters[0]['is_deleted'] = false;
        return parent::findFirst($parameters);
    }

    /**
     * @param mixed $id
     * @return Properties|bool
     */
    public static function findById($id)
    {
        return parent::findById($id);
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
    public function toApiArray(): array
    {
        return [
            'productKeywords' => $this->keywords,
            'productSegments' => $this->segments->toApiArray()
        ];
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

        $validation->add(
            'packageDimensions',
            new PackageDimensionsValidator([
                'allowEmpty' => true
            ])
        );

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
            'keywords' => $this->keywords,
            'segments' => $this->segments
        ]);

        return !$this->_errorMessages->count();
    }
}
