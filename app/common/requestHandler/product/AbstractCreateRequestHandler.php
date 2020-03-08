<?php
/**
 * User: Wajdi Jurry
 * Date: 08/03/19
 * Time: 11:19 ص
 */

namespace app\common\requestHandler\product;


use app\common\requestHandler\RequestAbstract;
use app\common\services\user\UserService;
use app\common\validators\rules\AbstractProductRules;
use app\common\validators\SpecialCharactersValidator;
use app\common\validators\TypeValidator;
use Phalcon\Utils\Slug;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;
use app\common\validators\SegmentsValidator;
use app\common\validators\UuidValidator;

abstract class AbstractCreateRequestHandler extends RequestAbstract
{
    public $title;
    public $categoryId;
    public $price;
    public $quantity;
    public $salePrice;
    public $endSaleTime;
    public $customPageId;
    public $keywords;
    public $segments;
    public $isPublished = false;

    /** @var AbstractProductRules */
    protected $validationRules;

    /**
     * @var \JsonMapper
     */
    private $jsonMapper;

    /**
     * @return UserService
     */
    protected function getUserService(): UserService
    {
        return $this->controller->getDI()->getUserService();
    }

    /**
     * @return \JsonMapper
     */
    protected function getJsonMapper(): \JsonMapper
    {
        $jsonMapper = $this->jsonMapper ?? new \JsonMapper();
        $jsonMapper->bEnforceMapType = false;
        return $jsonMapper;
    }

    /**
     * @return array
     */
    protected function fields()
    {
        return [
            'title' => $this->title,
            'categoryId' => $this->categoryId,
            'price' => $this->price,
            'salePrice' => $this->salePrice,
            'endSaleTime' => $this->endSaleTime,
            'customPageId' => $this->customPageId,
            'keywords' => $this->keywords,
            'segments' => $this->segments,
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
            'categoryId',
            new UuidValidator()
        );

        $validator->add(
            'customPageId',
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
                    if (!is_array($data['keywords'])) {
                        return false;
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
            'segments',
            new SegmentsValidator()
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
     */
    public function toArray(): array
    {
        return [
            'productId' => $this->controller->getDI()->getSecurity()->getRandom()->uuid(),
            'productCategoryId' => $this->categoryId,
            'productUserId' => $this->getUserService()->userId,
            'productVendorId' => $this->getUserService()->vendorId,
            'productCustomPageId' => $this->customPageId,
            'productTitle' => $this->title,
            'productPrice' => $this->price,
            'productQuantity' => $this->quantity,
            'productSalePrice' => $this->salePrice,
            'productSaleEndTime' => $this->endSaleTime,
            'productKeywords' => $this->keywords,
            'productSegments' => $this->segments,
            'productLinkSlug' => (new Slug())->generate($this->title),
            'isPublished' => $this->isPublished
        ];
    }
}
