<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 08:23 م
 */

namespace app\common\requestHandler\product;

use Phalcon\Validation;
use Phalcon\Validation\Message\Group;
use app\common\controllers\BaseController;
use app\common\exceptions\OperationFailed;
use app\common\requestHandler\RequestHandlerInterface;
use app\common\services\user\UserService;
use app\common\validators\UuidValidator;

class GetRequestHandler extends BaseController implements RequestHandlerInterface
{
    /** @var string $categoryId */
    private $categoryId;

    /** @var string $vendorId */
    private $vendorId;

    /** @var int $limit */
    private $limit;

    /** @var int $page */
    private $page;

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

    public function getLimit()
    {
        return $this->limit ?? 10;
    }

    public function getPage()
    {
        return $this->page ?? 1;
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

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function setPage($page)
    {
        $this->page = $page;
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

        $validator->add(
            ['limit', 'page'],
            new Validation\Validator\NumericValidator([
                'allowFloat' => false,
                'allowSign' => false,
                'min' => 0,
                'allowEmpty' => true
            ])
        );

        return $validator->validate([
            'categoryId' => $this->getCategoryId(),
            'vendorId' => $this->getVendorId(),
            'limit' => $this->getLimit(),
            'page' => $this->getPage()
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
     * @throws OperationFailed
     */
    public function invalidRequest($message = null)
    {
        throw new OperationFailed($this->errorMessages, 400);
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
            'vendorId' => $this->getVendorId(),
            'limit' => $this->getLimit(),
            'page' => $this->getPage()
        ];
    }
}
