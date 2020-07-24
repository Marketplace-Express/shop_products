<?php
/**
 * User: Wajdi Jurry
 * Date: 08/03/19
 * Time: 11:19 ص
 */

namespace app\common\requestHandler\product;


use app\common\requestHandler\RequestAbstract;
use app\common\validators\rules\AbstractProductRules;
use app\common\validators\SpecialCharactersValidator;
use app\common\validators\TypeValidator;
use Phalcon\Utils\Slug;
use Phalcon\Validation;
use app\common\validators\SegmentsValidator;
use app\common\validators\UuidValidator;

abstract class AbstractCreateRequestHandler extends RequestAbstract
{
    /** @var string */
    public $vendorId;

    /** @var string */
    public $userId;

    /** @var string */
    public $title;

    /** @var string */
    public $categoryId;

    /** @var float */
    public $price;

    /** @var float */
    public $salePrice;

    /** @var int */
    public $quantity;

    /** @var string */
    public $endSaleTime;

    /** @var string */
    public $customPageId;

    /** @var string */
    public $brandId;

    /** @var array */
    public $keywords;

    /** @var \app\common\models\embedded\Segment */
    public $segment;

    /** @var bool */
    public $isPublished = false;

    /** @var AbstractProductRules */
    protected $validationRules;

    /**
     * @return array
     */
    protected function fields()
    {
        return [
            'storeId' => $this->storeId,
            'userId' => $this->userId,
            'title' => $this->title,
            'categoryId' => $this->categoryId,
            'price' => $this->price,
            'salePrice' => $this->salePrice,
            'endSaleTime' => $this->endSaleTime,
            'customPageId' => $this->customPageId,
            'brandId' => $this->brandId,
            'keywords' => $this->keywords,
            'segment' => $this->segment,
            'isPublished' => $this->isPublished,
            'quantity' => $this->quantity
        ];
    }

    public function mainValidator(): Validation
    {
        $validator = new Validation();

        // Validate English input
        $validator->add(
            'title',
            new Validation\Validator\Callback([
                'callback' => function ($data) {
                    $name = preg_replace('/[\d\s_]/i', '', $data['title']); // clean string
                    if (preg_match('/[a-z]/i', $name) == false) {
                        return false;
                    }
                    return true;
                },
                'message' => 'English language only supported'
            ])
        );

        $validator->add(
            'title',
            new SpecialCharactersValidator([
                'allowEmpty' => false
            ])
        );

        $validator->add(
            ['categoryId', 'storeId', 'userId'],
            new UuidValidator()
        );

        $validator->add(
            'customPageId',
            new UuidValidator([
                'allowEmpty' => true
            ])
        );

        $validator->add(
            'brandId',
            new UuidValidator([
                'allowEmpty' => true
            ])
        );

        $validator->add(
            'price',
            new Validation\Validator\NumericValidator([
                'allowFloat' => true,
                'min' => 0
            ])
        );

        $validator->add(
            'salePrice',
            new Validation\Validator\NumericValidator([
                'allowFloat' => true,
                'min' => 0,
                'allowEmpty' => true
            ])
        );

        $validator->add(
            'endSaleTime',
            new Validation\Validator\Date([
                'format' => 'Y-m-d H:i:s',
                'allowEmpty' => true
            ])
        );

        $validator->add(
            'quantity',
            new Validation\Validator\NumericValidator([
                'min' => 1
            ])
        );

        $validator->add(
            'endSaleTime',
            new Validation\Validator\Callback([
                'callback' => function ($data) {
                    if (!empty($data['endSaleTime']) && time() >= strtotime($data['endSaleTime'])) {
                        return false;
                    }
                    return true;
                },
                'message' => 'End sale date should be greater than this time'
            ])
        );

        $validator->add(
            'keywords',
            new Validation\Validator\Callback([
                'callback' => function($data) {
                    if (empty($data['keywords'])) {
                        return true;
                    }
                    foreach ($data['keywords'] as $keyword) {
                        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $keyword)) {
                            return false;
                        }
                    }
                    return true;
                },
                'message' => 'Invalid keywords'
            ])
        );

        $validator->add(
            'segment',
            new SegmentsValidator([
                'allowEmpty' => true
            ])
        );

        $validator->add(
            'isPublished',
            new TypeValidator([
                'type' => TypeValidator::TYPE_BOOLEAN
            ])
        );

        return $validator;
    }

    /**
     * @return array
     * @throws \Phalcon\Exception
     */
    public function toArray(): array
    {
        return [
            'productCategoryId' => $this->categoryId,
            'productUserId' => $this->userId,
            'productStoreId' => $this->storeId,
            'productCustomPageId' => $this->customPageId,
            'productTitle' => $this->title,
            'productPrice' => $this->price,
            'productQuantity' => $this->quantity,
            'productSalePrice' => $this->salePrice,
            'productSaleEndTime' => $this->endSaleTime,
            'productLinkSlug' => (new Slug())->generate($this->title),
            'isPublished' => $this->isPublished,
            'keywords' => $this->keywords,
            'segment' => $this->segment,
        ];
    }
}
