<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 08:23 م
 */

namespace app\common\requestHandler\product;

use app\common\requestHandler\RequestAbstract;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;
use app\common\services\user\UserService;
use app\common\validators\UuidValidator;

class GetRequestHandler extends RequestAbstract
{
    /** @var string $categoryId */
    public $categoryId;

    /** @var string $vendorId */
    public $vendorId;

    /** @var int $limit */
    public $limit;

    /** @var int $page */
    public $page;

    /** @var bool */
    public $requireCategoryId = false;

    /**
     * @return int
     */
    public function getAccessLevel(): int
    {
        return $this->getUserService()->accessLevel;
    }

    /**
     * @return UserService
     */
    private function getUserService(): UserService
    {
        return $this->controller->getDI()->getUserService();
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
            'categoryId' => $this->categoryId,
            'vendorId' => $this->vendorId,
            'limit' => $this->limit,
            'page' => $this->page
        ]);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'categoryId' => $this->categoryId,
            'vendorId' => $this->vendorId,
            'limit' => $this->limit,
            'page' => $this->page
        ];
    }
}
