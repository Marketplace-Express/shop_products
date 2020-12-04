<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 08:23 م
 */

namespace app\common\requestHandler\product;

use app\common\models\sorting\SortProduct;
use app\common\requestHandler\IArrayData;
use app\common\requestHandler\RequestAbstract;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;
use app\common\services\user\UserService;
use app\common\validators\UuidValidator;

/**
 * Class GetAllRequestHandler
 * @package app\common\requestHandler\product
 */
class GetAllRequestHandler extends RequestAbstract implements IArrayData
{
    /** @var string */
    public $categoryId;

    /** @var string */
    public $storeId;

    /** @var int */
    public $limit;

    /** @var int */
    public $page;

    /** @var bool */
    public $editMode = false;

    /** @var SortProduct */
    protected $sort;

    /**
     * GetRequestHandler constructor.
     */
    public function __construct()
    {
        // Initialize sort param
        $sort = new SortProduct();
        $sort->createdAt = -1;
        $this->sort = $sort;

        if ($this->request->get('sort')) {
            $this->sort = $this->di->getJsonMapper()->map(
                json_decode($this->request->get('sort')),
                new SortProduct()
            );
        }
    }

    /**
     * @return UserService
     */
    protected function getUserService(): UserService
    {
        return $this->di->getUserService();
    }

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();

        $validator->add(
            'storeId',
            new UuidValidator()
        );

        $validator->add(
            'categoryId',
            new UuidValidator([
                'allowEmpty' => true
            ])
        );

        $validator->add(
            ['limit', 'page'],
            new Validation\Validator\NumericValidator([
                'allowFloat' => false,
                'allowSign' => false,
                'min' => 1
            ])
        );

        return $validator->validate([
            'categoryId' => $this->categoryId,
            'storeId' => $this->storeId,
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
            'storeId' => $this->storeId,
            'limit' => $this->limit,
            'page' => $this->page,
            'sort' => $this->sort,
            'editMode' => $this->editMode
        ];
    }
}
