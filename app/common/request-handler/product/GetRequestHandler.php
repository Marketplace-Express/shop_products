<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 08:23 م
 */

namespace Shop_products\RequestHandler\Product;

use Phalcon\Validation;
use Phalcon\Validation\Message\Group;
use Shop_products\Controllers\BaseController;
use Shop_products\Exceptions\ArrayOfStringsException;
use Shop_products\RequestHandler\RequestHandlerInterface;
use Shop_products\Services\User\UserService;
use Shop_products\Validators\UuidValidator;

class GetRequestHandler extends BaseController implements RequestHandlerInterface
{
    /** @var string $categoryId */
    private $categoryId;

    /** @var string $vendorId */
    private $vendorId;

    /** @var bool */
    public $requireCategoryId = false;

    private $errorMessages;

    public function getCategoryId()
    {
        return $this->categoryId;
    }

    public function getVendorId()
    {
        return $this->vendorId;
    }

    /**
     * @return int
     */
    public function getAccessLevel(): int
    {
        return $this->getUserService()->accessLevel;
    }

    public function setCategoryId(string $categoryId)
    {
        $this->categoryId = $categoryId;
    }

    public function setVendorId(string $vendorId)
    {
        $this->vendorId = $vendorId;
    }

    /**
     * @return UserService
     */
    private function getUserService(): UserService
    {
        return $this->getDI()->getUserService();
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

        $validator->add(
            'categoryId',
            new UuidValidator([
                'allowEmpty' => !$this->requireCategoryId
            ])
        );

        return $validator->validate([
            'categoryId' => $this->getCategoryId(),
            'vendorId' => $this->getVendorId()
        ]);
    }

    public function isValid(): bool
    {
        $messages = $this->validate();
        if (count($messages)) {
            foreach ($messages as $message) {
                if (is_array($field = $message->getField())) {
                    $field = $message->getField()[0];
                }
                $this->errorMessages[$field] = $message->getMessage();
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
        return [
            'categoryId' => $this->getCategoryId(),
            'vendorId' => $this->getVendorId()
        ];
    }
}