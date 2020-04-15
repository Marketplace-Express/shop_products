<?php
/**
 * User: Wajdi Jurry
 * Date: ٢٣‏/٨‏/٢٠١٩
 * Time: ٢:٠٥ ص
 */

namespace app\common\exceptions;


use Phalcon\Mvc\Model\Message as ModelMessage;
use Phalcon\Validation\Message;
use Throwable;

/**
 * Class BaseException
 * @package app\exceptions
 */
abstract class BaseException extends \Exception
{
    public function __construct($messages = "", $code = 0, Throwable $previous = null)
    {
        if (is_array($messages) || is_object($messages)) {
            $errors = [];
            foreach ($messages as $key => $message) {
                if ($message instanceof Message || $message instanceof ModelMessage) {
                    if (is_array($message->getField())) {
                        $errors["multiple"][] = $message->getMessage();
                    } else {
                        $errors[$message->getField()] = $message->getMessage();
                    }
                } elseif ($message instanceof \Throwable) {
                    $errors[$key] = $message->getMessage();
                } else {
                    $errors[$key] = $message;
                }
            }
            $errors = json_encode($errors);
        } else {
            $errors = $messages;
        }
        $this->message = $errors;
        $this->code = $code;
        parent::__construct($this->message, $this->code, $previous);
    }
}
