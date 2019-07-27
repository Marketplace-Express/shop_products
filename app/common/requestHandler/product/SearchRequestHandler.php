<?php
/**
 * User: Wajdi Jurry
 * Date: 27/02/19
 * Time: 09:24 م
 */

namespace app\common\requestHandler\product;


use Phalcon\Validation;
use Phalcon\Validation\Message\Group;
use app\common\controllers\BaseController;
use app\common\exceptions\ArrayOfStringsException;
use app\common\requestHandler\RequestHandlerInterface;

class SearchRequestHandler extends BaseController implements RequestHandlerInterface
{

    /** @var string */
    protected $keyword;

    protected $errorMessages;

    /**
     * @param string $keyword
     */
    public function setKeyword(string $keyword): void
    {
        $this->keyword = $keyword;
    }

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();

        $validator->add(
            'keyword',
            new Validation\Validator\PresenceOf()
        );

        return $validator->validate([
            'keyword' => $this->keyword
        ]);
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $messages = self::validate();
        if (count($messages)) {
            foreach ($messages as $message) {
                $this->errorMessages[$message->getField()] = $message->getMessage();
            }
            return false;
        }
        return true;
    }

    /**
     * @param string $message
     *
     * @throws \Exception
     */
    public function notFound($message = 'Not Found')
    {
        throw new \Exception($message, 404);
    }

    /**
     * @param null $message
     * @return mixed|void
     * @throws ArrayOfStringsException
     */
    public function invalidRequest($message = null)
    {
        if (is_null($message)) {
            $message = $this->errorMessages;
        }
        throw new ArrayOfStringsException($message, 400);
    }

    /**
     * @param mixed $message
     * @return mixed|\Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function successRequest($message = null)
    {
        http_response_code(200);
        return $this->response
            ->setJsonContent([
                'status' => 200,
                'message' => $message
            ]);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'keyword' => $this->keyword . '*'
        ];
    }
}