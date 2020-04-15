<?php
/**
 * User: Wajdi Jurry
 * Date: 21/01/19
 * Time: 09:33 م
 */

namespace app\common\models\embedded;


use app\common\models\BaseCollection;
use Phalcon\Validation;
use app\common\validators\{
    SegmentsValidator,
    UuidValidator
};

/**
 * Class Properties
 * @package app\common\models\embedded
 */
class Properties extends BaseCollection
{
    /** @var string */
    public $productId;

    /** @var array */
    public $keywords = [];

    /** @var \app\common\models\embedded\Segment */
    public $segment;

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
    }

    /**
     * @throws \Exception
     */
    public function update()
    {
        throw new \Exception('Update not supported. Use save() instead', 503);
    }

    /**
     * @return Segment
     */
    protected function getSegment(): Segment
    {
        if (!$this->segment || !$this->segment instanceof Segment) {
            $this->segment = new Segment();
        }
        return $this->segment;
    }

    /**
     * @return array
     */
    public function attributes(): array
    {
        return [
            'keywords',
            'segment'
        ];
    }

    /**
     * @param array $data
     */
    public function setAttributes(array $data)
    {
        if (!$this->productId) {
            $this->productId = $data['productId'];
        }
        $this->keywords = $data['keywords'] ?? [];
        $this->getSegment()->setAttributes($data['segment']);
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
            'keywords' => $this->keywords,
            'segment' => $this->segment
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
            'productId',
            new UuidValidator()
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
            'productId' => $this->productId,
            'keywords' => $this->keywords,
            'segments' => $this->segment
        ]);

        return !$this->_errorMessages->count();
    }
}
