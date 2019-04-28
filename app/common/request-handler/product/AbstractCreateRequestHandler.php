<?php
/**
 * User: Wajdi Jurry
 * Date: 08/03/19
 * Time: 11:19 ص
 */

namespace Shop_products\RequestHandler\Product;


use Exception;
use Phalcon\Utils\Slug;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;
use Shop_products\Controllers\BaseController;
use Shop_products\RequestHandler\RequestHandlerInterface;
use Shop_products\Validators\SegmentsValidator;
use Shop_products\Validators\UuidValidator;

abstract class AbstractCreateRequestHandler extends BaseController implements RequestHandlerInterface
{
    protected $title;
    protected $categoryId;
    protected $userId;
    protected $vendorId;
    protected $price;
    protected $salePrice;
    protected $endSaleTime;
    protected $customPageId;
    protected $keywords;
    protected $segments;

    protected $errorMessages;

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @param mixed $categoryId
     */
    public function setCategoryId($categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @param mixed $vendorId
     */
    public function setVendorId($vendorId): void
    {
        $this->vendorId = $vendorId;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price): void
    {
        $this->price = (float) $price;
    }

    /**
     * @param mixed $salePrice
     */
    public function setSalePrice($salePrice): void
    {
        $this->salePrice = (float) $salePrice;
    }

    /**
     * @param mixed $endSaleTime
     */
    public function setEndSaleTime($endSaleTime): void
    {
        $this->endSaleTime = $endSaleTime;
    }

    /**
     * @param mixed $customPageId
     */
    public function setCustomPageId($customPageId): void
    {
        $this->customPageId = $customPageId;
    }

    /**
     * @param mixed $keywords
     */
    public function setKeywords($keywords): void
    {
        $this->keywords = $keywords;
    }

    /**
     * @param mixed $segments
     */
    public function setSegments($segments): void
    {
        $this->segments = $segments;
    }

    private function getTitleValidationConfig()
    {
        return $this->getDI()->getConfig()->application->validation->productTitle;
    }

    /**
     * @return array
     */
    protected function fields()
    {
        return [
            'title' => $this->title,
            'categoryId' => $this->categoryId,
            'vendorId' => $this->vendorId,
            'userId' => $this->userId,
            'price' => $this->price,
            'salePrice' => $this->salePrice,
            'endSaleTime' => $this->endSaleTime,
            'customPageId' => $this->customPageId,
            'keywords' => $this->keywords,
            'segments' => $this->segments
        ];
    }

    public function validate(): Group
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
            new Validation\Validator\AlphaNumericValidator([
                'whiteSpace' => $this->getTitleValidationConfig()->whiteSpace,
                'underscore' => $this->getTitleValidationConfig()->underscore,
                'min' => $this->getTitleValidationConfig()->min,
                'max' => $this->getTitleValidationConfig()->max,
                'message' => 'Product title should contain only letters'
            ])
        );

        $validator->add(
            ['categoryId', 'userId', 'vendorId'],
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

        return $validator->validate($this->fields());
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        // TODO: TO BE ENHANCED LATER
        $messages = self::validate();
        $multiErrorFields = [];
        foreach ($messages as $message) {
            $multiErrorFields[] = $message->getField();
        }
        $multiErrorFields = array_diff_assoc($multiErrorFields, array_unique($multiErrorFields));

        foreach ($messages as $message) {
            if (in_array($message->getField(), $multiErrorFields)) {
                $this->errorMessages[$message->getField()][] = $message->getMessage();
            } else {
                $this->errorMessages[$message->getField()] = $message->getMessage();
            }
        }
        return empty($this->errorMessages);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function toArray(): array
    {
        return [
            'productCategoryId' => $this->categoryId,
            'productUserId' => $this->userId,
            'productVendorId' => $this->vendorId,
            'productCustomPageId' => $this->customPageId,
            'productTitle' => $this->title,
            'productPrice' => $this->price,
            'productSalePrice' => $this->salePrice,
            'productSaleEndTime' => $this->endSaleTime,
            'productKeywords' => $this->keywords,
            'productSegments' => $this->segments,
            'productLinkSlug' => (new Slug())->generate($this->title)
        ];
    }
}