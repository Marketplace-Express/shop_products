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

    /**
     * @param bool $new
     * @return Base|Product|PhysicalProduct|DownloadableProduct
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
}