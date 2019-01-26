<?php
/**
 * User: Wajdi Jurry
 * Date: 13/01/19
 * Time: 07:04 م
 */

namespace Shop_products\RequestHandler\Product;


use Phalcon\Validation;
use Phalcon\Validation\Message\Group;
use Shop_products\Controllers\BaseController;
use Shop_products\Exceptions\ArrayOfStringsException;
use Shop_products\RequestHandler\RequestHandlerInterface;
use Shop_products\Validators\TypeValidator;
use Shop_products\Validators\UuidValidator;

class UpdateRequestHandler extends BaseController implements RequestHandlerInterface
{
    private $title;
    private $categoryId;
    private $linkSlug;
    private $customPageId;
    private $brandId;
    private $price;
    private $salePrice;
    private $endSaleTime;
    private $keywords;
    private $isPublished;

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
    public function getLinkSlug()
    {
        return $this->linkSlug;
    }

    /**
     * @param mixed $linkSlug
     */
    public function setLinkSlug($linkSlug): void
    {
        $this->linkSlug = $linkSlug;
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
    public function getisPublished()
    {
        return $this->isPublished;
    }

    /**
     * @param mixed $isPublished
     */
    public function setIsPublished($isPublished): void
    {
        $this->isPublished = $isPublished;
    }

    private function getValidationConfig()
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
                'whiteSpace' => $this->getValidationConfig()->whiteSpace,
                'underscore' => $this->getValidationConfig()->underscore,
                'min' => $this->getValidationConfig()->min,
                'max' => $this->getValidationConfig()->max,
                'allowEmpty' => true
            ])
        );

        $validator->add(
            ['categoryId', 'customPageId', 'brandId'],
            new UuidValidator()
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

        $validator->add(
            'keywords',
            new Validation\Validator\Callback([
                'callback' => function($data) {
                    if (!empty($data['keywords'])) {
                        if (!is_array($data['keywords'])) {
                            return false;
                        }
                        foreach ($data['keywords'] as $keyword) {
                            if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $keyword)) {
                                return false;
                            }
                        }
                    }
                    return true;
                },
                'message' => 'Invalid keywords'
            ])
        );

        $validator->add(
            'isPublished',
            new TypeValidator([
                'type' => TypeValidator::TYPE_BOOLEAN,
                'allowEmpty' => true
            ])
        );

        return $validator->validate([
            'title' => $this->getTitle(),
            'categoryId' => $this->getCategoryId(),
            'customPageId' => $this->getCustomPageId(),
            'brandId' => $this->getBrandId(),
            'price' => $this->getPrice(),
            'salePrice' => $this->getSalePrice(),
            'endSaleTime' => $this->getEndSaleTime(),
            'keywords' => $this->getKeywords(),
            'isPublished' => $this->getisPublished()
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
        $result = [];

        if (!empty($this->getTitle())) {
            $result['productTitle'] = $this->getTitle();
        }

        if (!empty($this->getCategoryId())) {
            $result['productCategoryId'] = $this->getCategoryId();
        }

        if (!empty($this->getBrandId())) {
            $result['productBrandId'] = $this->getBrandId();
        }

        if (!empty($this->getCustomPageId())) {
            $result['productCustomPageId'] = $this->getCustomPageId();
        }

        if (!empty($this->getPrice())) {
            $result['productPrice'] = $this->getPrice();
        }

        if (!empty($this->getSalePrice())) {
            $result['productSalePrice'] = $this->getSalePrice();
        }

        if (!empty($this->getEndSaleTime())) {
            $result['productEndSaleTime'] = $this->getEndSaleTime();
        }

        if (!empty($this->getKeywords())) {
            $result['productKeywords'] = implode(',', $this->getKeywords());
        }

        if (!empty($this->getisPublished())) {
            $result['isPublished'] = $this->getisPublished();
        }

        if (empty($result)) {
            throw new \Exception('Nothing to be updated', 400);
        }

        return $result;
    }
}