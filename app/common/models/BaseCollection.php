<?php
/**
 * User: Wajdi Jurry
 * Date: 19/01/19
 * Time: 11:14 م
 */

namespace app\common\models;

use Phalcon\Mvc\MongoCollection;
use app\common\traits\ModelCollectionBehaviorTrait;

abstract class BaseCollection extends MongoCollection
{
    use ModelCollectionBehaviorTrait;

    /** @var MongoCollection $instance */
    protected static $instance;

    public function onConstruct()
    {
        self::$instance = $this;
    }

    /**
     * @param bool $new
     * @return mixed
     */
    public static function model(bool $new = false)
    {
        return (self::$instance && !$new) ? self::$instance : new static;
    }

    /**
     * Returns model's error messages
     * @return array
     */
    public function getMessages(): array
    {
        $messages = [];
        foreach (parent::getMessages() as $message) {
            if (is_array($field = $message->getField())) {
                $field = $message->getField()[0];
            }
            $messages[$field] = $message->getMessage();
        }
        return $messages;
    }

    /**
     * Returns model type
     * @return string
     */
    public function getType()
    {
        return MongoCollection::class;
    }
}
