<?php
/**
 * User: Wajdi Jurry
 * Date: 13/01/19
 * Time: 07:40 م
 */

namespace Shop_products\RequestHandler\Product;


use Phalcon\Validation\Message\Group;
use Shop_products\Controllers\BaseController;
use Shop_products\RequestHandler\RequestHandlerInterface;

class DeleteRequestHandler extends BaseController implements RequestHandlerInterface
{

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        // TODO: Implement validate() method.
    }

    public function isValid(): bool
    {
        // TODO: Implement isValid() method.
    }

    public function notFound($message = 'Not Found')
    {
        // TODO: Implement notFound() method.
    }

    public function invalidRequest($message = null)
    {
        // TODO: Implement invalidRequest() method.
    }

    public function successRequest($message = null)
    {
        http_response_code(200);
        return $this->response
            ->setJsonContent([
                'status' => 200,
                'message' => $message
            ]);
    }

    public function toArray(): array
    {
        // TODO: Implement toArray() method.
    }
}