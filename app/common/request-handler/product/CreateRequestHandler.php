<?php
/**
 * User: Wajdi Jurry
 * Date: 13/01/19
 * Time: 02:13 م
 */

namespace Shop_products\RequestHandler\Product;


use Phalcon\Validation;
use Phalcon\Validation\Message\Group;
use Shop_products\Controllers\BaseController;
use Shop_products\Enums\ProductTypesEnums;
use Shop_products\Exceptions\ArrayOfStringsException;
use Shop_products\RequestHandler\RequestHandlerInterface;
use Shop_products\Validators\SegmentsValidator;
use Shop_products\Validators\TypeValidator;
use Shop_products\Validators\UuidValidator;

class CreateRequestHandler extends BaseController implements RequestHandlerInterface
{
    private $title;
    private $categoryId;
    private $userId;
    private $vendorId;
    private $type;
    private $price;
    private $salePrice;
    private $endSaleTime;
    private $customPageId;
    private $brandId;
    private $weight;
    private $dimensions;
    private $keywords;
    private $segments;

    private $errorMessages;

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param mixed $categoryId
     */
    public function setCategoryId($categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getVendorId()
    {
        return $this->vendorId;
    }

    /**
     * @param mixed $vendorId
     */
    public function setVendorId($vendorId): void
    {
        $this->vendorId = $vendorId;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price): void
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getSalePrice()
    {
        return $this->salePrice;
    }

    /**
     * @param mixed $salePrice
     */
    public function setSalePrice($salePrice): void
    {
        $this->salePrice = $salePrice;
    }

    /**
     * @return mixed
     */
    public function getEndSaleTime()
    {
        return $this->endSaleTime;
    }

    /**
     * @param mixed $endSaleTime
     */
    public function setEndSaleTime($endSaleTime): void
    {
        $this->endSaleTime = $endSaleTime;
    }

    /**
     * @return mixed
     */
    public function getCustomPageId()
    {
        return $this->customPageId;
    }

    /**
     * @param mixed $customPageId
     */
    public function setCustomPageId($customPageId): void
    {
        $this->customPageId = $customPageId;
    }

    /**
     * @return mixed
     */
    public function getBrandId()
    {
        return $this->brandId;
    }

    /**
     * @param mixed $brandId
     */
    public function setBrandId($brandId): void
    {
        $this->brandId = $brandId;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param mixed $weight
     */
    public function setWeight($weight): void
    {
        $this->weight = $weight;
    }

    /**
     * @return mixed
     */
    public function getDimensions()
    {
        return $this->objectToArray($this->dimensions);
    }

    /**
     * @param mixed $dimensions
     */
    public function setDimensions($dimensions): void
    {
        $this->dimensions = $dimensions;
    }

    /**
     * @return mixed
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param mixed $keywords
     */
    public function setKeywords($keywords): void
    {
        $this->keywords = $keywords;
    }

    /**
     * @return mixed
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * @param mixed $segments
     */
    public function setSegments($segments): void
    {
        $this->segments = $segments;
    }

    private function objectToArray($object)
    {
        $array = [];
        foreach ($object as $property => $item) {
            $array[$property] = $item;
        }
        return $array;
    }

    private function getTitleValidationConfig()
    {
        return \Phalcon\Di::getDefault()->getConfig()->application->validation->productTitle;
    }

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();

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
            ['customPageId', 'brandId'],
            new UuidValidator([
                'allowEmpty' => true
            ])
        );

        $validator->add(
            'type',
            new Validation\Validator\InclusionIn([
                'domain' => ProductTypesEnums::getValues(),
                'allowEmpty' => false,
                'message' => 'Product type should be physical or downloadable'
            ])
        );

        $validator->add(
            ['price', 'salePrice'],
            new Validation\Validator\NumericValidator([
                'allowFloat' => true,
                'min' => 0,
                'allowEmpty' => false
            ])
        );

        $validator->add(
            'endSaleTime',
            new Validation\Validator\Date([
                'format' => 'Y-m-d H:i:s',
                'allowEmpty' => true
            ])
        );

        if ($this->getType() == ProductTypesEnums::TYPE_PHYSICAL) {
            $validator->add(
                'weight',
                new Validation\Validator\NumericValidator([
                    'allowFloat' => true,
                    'allowEmpty' => false
                ])
            );

            $validator->add(
                'dimensions',
                new TypeValidator([
                    'type' => TypeValidator::TYPE_FLOAT,
                    'allowEmpty' => false
                ])
            );
        }

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

        return $validator->validate([
            'title' => $this->getTitle(),
            'categoryId' => $this->getCategoryId(),
            'vendorId' => $this->getVendorId(),
            'userId' => $this->getUserId(),
            'type' => $this->getType(),
            'price' => $this->getPrice(),
            'salePrice' => $this->getSalePrice(),
            'endSaleTime' => $this->getEndSaleTime(),
            'customPageId' => $this->getCustomPageId(),
            'brandId' => $this->getBrandId(),
            'keywords' => $this->getKeywords(),
            'segments' => $this->getSegments()
        ]);

    }

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
        if (!$message) {
            $message = $this->errorMessages;
        }
        throw new ArrayOfStringsException($message, 400);
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

    /**
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        return [
            'productId' => $this->getUuidUtil()->uuid(),
            'productCategoryId' => $this->getCategoryId(),
            'productUserId' => $this->getUserId(),
            'productVendorId' => $this->getVendorId(),
            'productBrandId' => $this->getBrandId(),
            'productCustomPageId' => $this->getCustomPageId(),
            'productTitle' => $this->getTitle(),
            'productType' => $this->getType(),
            'productPrice' => $this->getPrice(),
            'productSalePrice' => $this->getSalePrice(),
            'productSaleEndTime' => $this->getEndSaleTime(),
            'productKeywords' => $this->getKeywords(),
            'productSegments' => $this->getSegments(),
            'productWeight' => $this->getWeight(),
            'productDimensions' => $this->getDimensions()
        ];
    }
}