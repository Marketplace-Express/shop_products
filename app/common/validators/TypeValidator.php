<?php
/**
 * User: Wajdi Jurry
 * Date: 19/01/19
 * Time: 04:10 م
 */

namespace Shop_products\Validators;


use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;
use Phalcon\Validation\ValidatorInterface;

class TypeValidator extends Validator implements ValidatorInterface
{
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_DOUBLE = 'double';
    const TYPE_FLOAT = 'float';
    const TYPE_ARRAY = 'array';
    const TYPE_OBJECT = 'object';
    const TYPE_URL = 'url';
    const TYPE_EMAIL = 'email';
    const TYPE_IP = 'ip';

    /**
     * Executes the validation
     *
     * $validation->add('field', new TypeValidator([
     *      'type' => TypeValidator::TYPE_OBJECT,
     *      'className' => Object::class,
     *      'allowEmpty' => false
     * ]);
     *
     * @param \Phalcon\Validation $validation
     * @param string $attribute
     * @return bool
     * @throws \Exception
     */
    public function validate(\Phalcon\Validation $validation, $attribute)
    {
        $type = $this->getOption('type');
        $values = $validation->getValue($attribute);
        $allowEmpty = $this->getOption('allowEmpty', true);

        if ($type == self::TYPE_ARRAY) {
            if (!is_array($values)) {
                $message = $this->getOption('message', 'Invalid array parameter');
                $validation->appendMessage(new Message($message, $attribute));
                return false;
            } else {
                return true;
            }
        }

        if (!is_array($values)) {
            $values = [$values];
        }

        foreach ($values as $value) {
            switch ($type) {
                case self::TYPE_BOOLEAN:
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case self::TYPE_STRING:
                    $value = filter_var($value, FILTER_SANITIZE_STRING);
                    break;
                case self::TYPE_INTEGER:
                    $value = filter_var($value, FILTER_VALIDATE_INT);
                    break;
                case self::TYPE_FLOAT:
                case self::TYPE_DOUBLE:
                    $value = filter_var($value, FILTER_VALIDATE_FLOAT);
                    break;
                case self::TYPE_OBJECT:
                    $className = $this->getOption('className');
                    if (empty($className)) {
                        throw new \Exception('You have to provide a class name');
                    }
                    $class = new $className;
                    $value = $className instanceof $class;
                    break;
                case self::TYPE_URL:
                    $value = filter_var($value, FILTER_VALIDATE_URL);
                    break;
                case self::TYPE_EMAIL:
                    $value = filter_var($value, FILTER_VALIDATE_EMAIL);
                    break;
                case self::TYPE_IP:
                    $value = filter_var($value, FILTER_VALIDATE_IP);
                    break;
                default:
                    throw new \Exception('Unknown Type', 400);
            }
            if (!$allowEmpty && !$value) {
                $message = $this->getOption('message', 'Invalid Type');
                $validation->appendMessage(new Message($message, $attribute));
                return false;
            }
        }
        return true;
    }
}