<?php
/**
 * User: Wajdi Jurry
 * Date: ٢٤‏/٨‏/٢٠١٩
 * Time: ١٢:٤٠ م
 */

namespace app\common\requestHandler;


use app\common\exceptions\NotFound;
use app\common\exceptions\ValidationFailed;
use app\common\validators\rules\RulesAbstract;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\ControllerInterface;

abstract class RequestAbstract  implements IRequestHandler, ControllerInterface
{
    /** @var array */
    public $errorMessages = [];

    /** @var RulesAbstract */
    protected $validationRules;

    /** @var Controller */
    protected $controller;

    /**
     * RequestAbstract constructor.
     * @param Controller $controller
     * @param RulesAbstract|null $rulesAbstract
     */
    public function __construct(Controller $controller, ?RulesAbstract $rulesAbstract = null)
    {
        $this->controller = $controller;
        $this->validationRules = $rulesAbstract;
    }

    /**
     * @param null $message
     * @throws ValidationFailed
     */
    final public function invalidRequest($message = null)
    {
        if (is_null($message)) {
            $message = $this->errorMessages;
        }
        throw new ValidationFailed($message, 400);
    }

    /**
     * @param string $message
     * @throws NotFound
     */
    final public function notFound($message = 'Not Found')
    {
        throw new NotFound($message);
    }

    /**
     * @param null $message
     * @param int $code
     * @return mixed|\Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function successRequest($message = null, int $code = 200)
    {
        http_response_code($code);
        if ($code != 204) {
            $this->controller->response
                ->setJsonContent([
                    'status' => 200,
                    'message' => $message
                ]);
        }
        return $this->controller->response;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $this->errorMessages = $this->validate();
        return !count($this->errorMessages);
    }
}
