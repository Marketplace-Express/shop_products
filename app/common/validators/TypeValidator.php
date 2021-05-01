<?php
/**
 * User: Wajdi Jurry
 * Date: 19/01/19
 * Time: 04:10 م
 */

namespace app\common\validators;


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

        if (empty($value) && $allowEmpty) {
            return true;
        }

        if ($type == self::TYPE_ARRAY) {
            if (!is_array($values)) {
                $message = $this->getOption('message', 'Invalid array parameter');
                $validation->appendMessage(new Message($message, $attribute));
                return false;
            } else {
                return true;
            }
        }

        if (!is_array($values) && !is_object($values)) {
            $values = [$values];
        }

        foreach ($values as $value) {
            switch ($type) {
                case self::TYPE_BOOLEAN:
                    $filteredValue = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case self::TYPE_STRING:
                    $filteredValue = filter_var($value, FILTER_SANITIZE_STRING);
                    break;
                case self::TYPE_INTEGER:
                    $filteredValue = filter_var($value, FILTER_VALIDATE_INT);
                    break;
                case self::TYPE_FLOAT:
                case self::TYPE_DOUBLE:
                    $filteredValue = filter_var($value, FILTER_VALIDATE_FLOAT);
                    break;
                case self::TYPE_OBJECT:
                    $className = $this->getOption('className');
                    if (empty($className) || !is_string($className)) {
                        throw new \Exception('Class name must be string');
                    }
                    $class = new $className;
                    $filteredValue = $className instanceof $class;
                    break;
                case self::TYPE_URL:
                    $filteredValue = filter_var($value, FILTER_VALIDATE_URL);
                    break;
                case self::TYPE_EMAIL:
                    $filteredValue = filter_var($value, FILTER_VALIDATE_EMAIL);
                    break;
                case self::TYPE_IP:
                    $filteredValue = filter_var($value, FILTER_VALIDATE_IP);
                    break;
                default:
                    throw new \Exception('Unknown Type', 400);
            }
            if ($filteredValue !== $value) {
                $message = $this->getOption('message', 'Invalid value type');
                $validation->appendMessage(new Message($message, $attribute));
                return false;
            }
        }
        return true;
    }
}
