<?php
/**
 * User: Wajdi Jurry
 * Date: ٢٤‏/٨‏/٢٠١٩
 * Time: ١٢:٤٠ م
 */

namespace app\common\requestHandler;


use app\common\exceptions\NotFound;
use app\common\exceptions\ValidationFailed;
use app\common\interfaces\ApiArrayData;
use app\common\validators\rules\RulesAbstract;
use Phalcon\Di\Injectable;
use Phalcon\Http\Response\StatusCode;

abstract class RequestAbstract extends Injectable implements IRequestHandler
{
    /** @var array */
    public $errorMessages = [];

    /** @var RulesAbstract */
    protected $validationRules;

    /**
     * RequestAbstract constructor.
     * @param RulesAbstract|null $rulesAbstract
     */
    public function __construct(?RulesAbstract $rulesAbstract = null)
    {
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
    public function successRequest($message = null, int $code = StatusCode::OK)
    {
        http_response_code($code);

        if ($message instanceof ApiArrayData) {
            $message = $message->toApiArray();
        }

        if (is_array($message)) {
            array_walk($message, function (&$data) {
                $data = ($data instanceof ApiArrayData) ? $data->toApiArray() : $data;
            });
        }

        if ($code != StatusCode::NO_CONTENT) {
            $this->response
                ->setJsonContent([
                    'status' => 200,
                    'message' => $message
                ]);
        }
        return $this->response;
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
