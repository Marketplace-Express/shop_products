<?php
/**
 * User: Wajdi Jurry
 * Date: 13/01/19
 * Time: 11:25 ص
 */

namespace Shop_products\Models;

use Phalcon\Mvc\Model;
use Shop_products\Traits\ModelCollectionBehaviorTrait;

abstract class Base extends Model
{
    use ModelCollectionBehaviorTrait;

    protected static $instance;

    public static function model(bool $new = false)
    {
        return !empty(self::$instance) && !$new ? self::$instance : new static;
    }

    public function getMessages($filter = null)
    {
        $messages = [];
        foreach (parent::getMessages($filter) as $message) {
            $messages[$message->getField()] = $message->getMessage();
        }
        return $messages;
    }

    public static function getType()
    {
        return Model::class;
    }
}