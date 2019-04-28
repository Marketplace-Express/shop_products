<?php
/**
 * User: Wajdi Jurry
 * Date: 13/01/19
 * Time: 07:40 م
 */

namespace Shop_products\RequestHandler\Product;


use Phalcon\Validation;
use Phalcon\Validation\Message\Group;
use Shop_products\Controllers\BaseController;
use Shop_products\Exceptions\ArrayOfStringsException;
use Shop_products\RequestHandler\RequestHandlerInterface;
use Shop_products\Validators\UuidValidator;

class DeleteRequestHandler extends BaseController implements RequestHandlerInterface
{

    private $vendorId;

    private $errorMessages = [];

    /**
     * @param string $vendorId
     */
    public function setVendorId($vendorId)
    {
        $this->vendorId = $vendorId;
    }

    /**
     * @return mixed
     */
    public function getVendorId()
    {
        return $this->vendorId;
    }

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();

        $validator->add(
            'vendorId',
            new UuidValidator()
        );

        return $validator->validate([
            'vendorId' => $this->vendorId
        ]);
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $messages = $this->validate();
        if (count($messages)) {
            foreach ($messages as $message) {
                $this->errorMessages[$message->getField()] = $message->getMessage();
            }
            return false;
        }
        return true;
    }

    public function notFound($message = 'Not Found')
    {
        // TODO: Implement notFound() method.
    }

    /**
     * @param null $message
     * @throws ArrayOfStringsException
     */
    public function invalidRequest($message = null)
    {
        throw new ArrayOfStringsException($this->errorMessages, 400);
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