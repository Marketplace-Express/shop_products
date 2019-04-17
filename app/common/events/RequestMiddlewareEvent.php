<?php
/**
 * User: Wajdi Jurry
 * Date: 07/04/19
 * Time: 07:59 م
 */

namespace Shop_products\Events;

use Firebase\JWT\JWT;
use Shop_products\Controllers\BaseController;
use Shop_products\Services\User\Token;
use Shop_products\Services\User\UserService;
use Sid\Phalcon\AuthMiddleware\MiddlewareInterface;
use Exception;
use stdClass;
use Throwable;

class RequestMiddlewareEvent extends BaseController implements MiddlewareInterface
{
    /** @var Token $token */
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
     * @throws Exception
     */
    public function onConstruct()
    {
        parent::onConstruct();
        $this->saltKey = $this->getTokenConfig()->saltKey;
        $this->allowedAlg = $this->getTokenConfig()->allowedAlg;
        $this->userService = $this->getDI()->getUserService();

        $accessToken = $this->request->getHeader('Authorization');

//        $this->generate();
        $this->validate($accessToken);
        
        /** @var stdClass $accessToken */
        $this->token = $this->getJsonMapper()->map(
            $accessToken,
            new Token()
        );
    }

    private function generate()
    {
        exit(JWT::encode([
            'user_id' => '12345',
            'vendor_id' => '54321',
            'access_level' => 2,
            'exp' => time() + 1800,
            'entropy' => mt_rand(10000, 20000)
        ], $this->saltKey, 'HS512'));
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function authenticate(): bool
    {
        $this->userService->userId = $this->token->user_id;
        $this->userService->accessLevel = $this->token->access_level;
        return true;
    }

    /**
     * @param $accessToken
     * @throws Exception
     */
    private function validate(&$accessToken)
    {
        try {

            if (empty($accessToken)) {
                throw new Exception('Unauthorized action', 400);
            }
            $accessToken = explode(' ', $accessToken)[1];

            $accessToken = JWT::decode($accessToken, $this->saltKey, [$this->allowedAlg]);

            // user is logged in, then check token structure

            if (!property_exists($accessToken, 'user_id')) {
                throw new Exception('Invalid user id', 400);
            }

            if (!property_exists($accessToken, 'access_level')) {
                throw new Exception('Invalid token arguments', 400);
            }

            if (!property_exists($accessToken, 'vendor_id')) {
                throw new Exception('Invalid vendor Id', 400);
            }
        } catch (Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode() ?: 400);
        }
    }
}