<?php
/**
 * User: Wajdi Jurry
 * Date: 13/01/19
 * Time: 11:25 ص
 */

namespace app\common\models;

use Phalcon\Mvc\Model;
use app\common\traits\ModelCollectionBehaviorTrait;

abstract class BaseModel extends Model implements \ArrayAccess
{
    use ModelCollectionBehaviorTrait;

    protected static $instance;

    /**
     * @var int
     * Override default _operationMode
     */
    protected $operationMode;

    /**
     * @param bool $new
     * @return mixed
     */
    public static function model(bool $new = false)
    {
        return !empty(self::$instance) && !$new ? self::$instance : new static;
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

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return property_exists($this, $offset);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->$offset;
        }

        return null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->$offset);
        }
    }

    abstract public function toApiArray(): array;
}
