<?php
/**
 * User: Wajdi Jurry
 * Date: 13/01/19
 * Time: 11:25 ص
 */

namespace app\common\models;

use Phalcon\Mvc\Model;
use app\common\traits\ModelCollectionBehaviorTrait;

abstract class BaseModel extends Model
{
    use ModelCollectionBehaviorTrait;

    protected static $instance;

    public $exposedFields = [];

    /**
     * @param bool $new
     * @return mixed
     */
    public static function model(bool $new = false)
    {
        return !empty(self::$instance) && !$new ? self::$instance : new static;
    }

    /**
     * @param null $filter
     * @return array|Model\MessageInterface[]
     */
    public function getMessages($filter = null)
    {
        // TODO: TO BE ENHANCED LATER
        $messages = [];
        $multiErrorFields = [];
        foreach (parent::getMessages() as $message) {
            if (is_string($message)) {
                return [$message];
            }
            $multiErrorFields[] = $message->getField();
        }
        $multiErrorFields = array_diff_assoc($multiErrorFields, array_unique($multiErrorFields));

        foreach (parent::getMessages() as $message) {
            if (in_array($message->getField(), $multiErrorFields)) {
                $messages[$message->getField()][] = $message->getMessage();
            } else {
                $messages[$message->getField()] = $message->getMessage();
            }
        }
        return $messages;
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return Model::class;
    }

    /**
     * Assign attributes to the model
     * @param array $data
     * @param null $dataColumnMap
     * @param null|array $whiteList
     * @return $this|Model
     *
     * @throws \Exception
     */
    public function assign($data, $dataColumnMap = null, $whiteList = null)
    {
        if (in_array($this->_operationMade, [self::OP_CREATE, self::OP_UPDATE]) && empty($whiteList)) {
            throw new \Exception('You should provide a whitelist', 400);
        }
        foreach ($data as $attribute => $value) {
            if (!empty($whiteList) && !in_array($attribute, $whiteList)) {
                continue;
            }
            $this->writeAttribute($attribute, $value);
        }
        return $this;
    }

    abstract public function toApiArray(): array;
}
