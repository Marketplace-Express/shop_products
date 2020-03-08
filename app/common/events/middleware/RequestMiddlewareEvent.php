<?php
/**
 * User: Wajdi Jurry
 * Date: 07/04/19
 * Time: 07:59 م
 */

namespace app\common\events\middleware;

use app\common\exceptions\OperationFailed;
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

    /** @var \JsonMapper */
    private $jsonMapper;

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
        $this->userService = $this->getDI()->getUserService();

        $accessToken = $this->request->getHeader('Authorization');

//        $this->generate();
        $this->validate($accessToken);

        /** @var \stdClass $accessToken */
        $this->token = $this->getJsonMapper()->map(
            $accessToken,
            new Token()
        );
    }

    private function generate()
    {
        exit(JWT::encode([
            'user_id' => 'fded67e4-9fcd-4a2d-ae2e-de15d70a8bb5',
            'vendor_id' => $this->request->get('vendorId') ?? '9cde9748-b010-4767-9e89-566bf98f1833',
            'access_level' => 2,
            'exp' => time() + 3600 * 10,
            'entropy' => mt_rand(10000, 20000)
        ], $this->saltKey, $this->allowedAlg));
    }

    /**
     * @return \JsonMapper
     */
    private function getJsonMapper()
    {
        return $this->jsonMapper ?? $this->jsonMapper = new \JsonMapper();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function authenticate(): bool
    {
        $this->userService->userId = $this->token->user_id;
        $this->userService->vendorId = $this->token->vendor_id;
        $this->userService->accessLevel = $this->token->access_level;
        return true;
    }

    /**
     * @param $accessToken
     * @throws \Exception
     */
    private function validate(&$accessToken)
    {
        try {

            if (empty($accessToken)) {
                throw new \Exception('Unauthorized action', 400);
            }
            $accessToken = explode(' ', $accessToken)[1];

            try {
                $accessToken = JWT::decode($accessToken, $this->saltKey, [$this->allowedAlg]);
            } catch (\UnexpectedValueException $exception) {
                throw new \Exception('Invalid token', 400, $exception);
            }

            // user is logged in, then check token structure
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

            $errors = $validator->validate([
                'user_id' => $accessToken->user_id,
                'vendor_id' => $accessToken->vendor_id,
                'access_level' => $accessToken->access_level
            ]);

            if (count($errors)) {
                throw new OperationFailed($errors);
            }
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode() ?: 400);
        }
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
            'namespace' => 'app\common\controllers',
            'controller' => 'exceptionhandler',
            'action' => 'raiseError',
            'params' => [$errors, $code]
        ]);
    }
}
