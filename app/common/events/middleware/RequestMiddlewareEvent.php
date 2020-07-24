<?php
/**
 * User: Wajdi Jurry
 * Date: 07/04/19
 * Time: 07:59 م
 */

namespace app\common\events\middleware;


use app\common\services\user\Token;
use app\common\services\user\UserService;
use app\common\validators\UuidValidator;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Validation;
use Sid\Phalcon\AuthMiddleware\MiddlewareInterface;
use Firebase\JWT\JWT;

class RequestMiddlewareEvent extends Plugin implements MiddlewareInterface
{
    /**
     * @var Token $token
     */
    private $token;

    private $saltKey;
    private $allowedAlg;

    /** @var UserService $userService */
    private $userService;

    /**
     * @return mixed
     */
    private function getTokenConfig()
    {
        return $this->getDI()->getConfig()->application->token;
    }

    /**
     * RequestMiddlewareEvent constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->saltKey = $this->getTokenConfig()->saltKey;
        $this->allowedAlg = $this->getTokenConfig()->allowedAlg;
        $this->userService = $this->di->getUserService();

        // Generate a token
//        $this->generate();

        $this->token = @$this->di->getJsonMapper()->map(
            $this->decode($this->request->getHeader('Authorization')),
            new Token()
        );
    }

    private function generate()
    {
        exit(JWT::encode([
            'user_id' => 'fded67e4-9fcd-4a2d-ae2e-de15d70a8bb5',
            'vendor_id' => '74a20f34-7f76-4a26-8cf6-e69dc2166576',
            'access_level' => 2,
            'exp' => time() + 3600 * 10,
            'entropy' => mt_rand(10000, 20000)
        ], $this->saltKey, $this->allowedAlg));
    }

    /**
     * @param string $token
     * @return object
     */
    private function decode(string $token)
    {
        try {
            $token = explode(' ', $token)[1];
            return JWT::decode($token, $this->saltKey, [$this->allowedAlg]);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), 400);
        }
    }

    /**
     * @return bool
     */
    private function isValid(): bool
    {
        $errorMessages = $this->validate();
        if (count($errorMessages)) {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function authenticate(): bool
    {
        // validate token
        if (!$this->isValid()) {
            $this->handleError('Invalid token', 400);
        }

        $this->userService->userId = $this->token->user_id;
        $this->userService->storeId = $this->token->vendor_id;
        $this->userService->accessLevel = $this->token->access_level;
        return true;
    }

    /**
     * @return Validation\Message\Group
     */
    private function validate(): Validation\Message\Group
    {
        $validator = new Validation();
        $validator->add(
            ['user_id', 'vendor_id', 'access_level'],
            new Validation\Validator\PresenceOf()
        );
        $validator->add(
            ['user_id', 'vendor_id'],
            new UuidValidator()
        );
        $validator->add(
            'access_level',
            new Validation\Validator\NumericValidator([
                'allowFloat' => false,
                'allowSign' => false
            ])
        );

        return $validator->validate([
            'user_id' => $this->token->user_id,
            'vendor_id' => $this->token->vendor_id,
            'access_level' => $this->token->access_level
        ]);
    }

    /**
     * Forward error response to ExceptionhandlerController
     * @param $errors
     * @param int $code
     * @codeCoverageIgnore
     */
    private function handleError($errors, $code = 500)
    {
        $this->dispatcher->forward([
            'namespace' => 'app\modules\api\controllers',
            'controller' => 'exceptionHandler',
            'action' => 'raiseError',
            'params' => [$errors, $code]
        ]);
    }
}
