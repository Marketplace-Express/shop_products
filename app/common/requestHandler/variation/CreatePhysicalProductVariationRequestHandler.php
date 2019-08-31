<?php
/**
 * User: Wajdi Jurry
 * Date: 19/01/19
 * Time: 05:17 م
 */

namespace app\common\requestHandler\variation;

use Phalcon\Validation;
use Phalcon\Validation\Message\Group;
use app\common\controllers\BaseController;
use app\common\exceptions\OperationFailed;
use app\common\requestHandler\RequestHandlerInterface;

class CreatePhysicalProductVariationRequestHandler extends BaseController implements RequestHandlerInterface
{
    private $quantity;
    private $price;
    private $salePrice;
    private $endSaleTime;
    private $sku;
    private $image;
    private $attributes;
    private $customAttributes;
    private $conditions;

    private $errorMessages;

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity): void
    {
        $this->quantity = $quantity;
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
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param mixed $sku
     */
    public function setSku($sku): void
    {
        $this->sku = $sku;
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
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image): void
    {
        $this->image = $image;
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param mixed $attributes
     */
    public function setAttributes($attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * @return mixed
     */
    public function getCustomAttributes()
    {
        return $this->customAttributes;
    }

    /**
     * @param mixed $customAttributes
     */
    public function setCustomAttributes($customAttributes): void
    {
        $this->customAttributes = $customAttributes;
    }

    /**
     * @return mixed
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param mixed $conditions
     */
    public function setConditions($conditions): void
    {
        $this->conditions = $conditions;
    }

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();

        $validator->add(
            'quantity',
            new Validation\Validator\NumericValidator([
                'allowFloat' => false,
                'allowEmpty' => true,
                'min' => 0
            ])
        );

        $validator->add(
            ['price', 'salePrice'],
            new Validation\Validator\NumericValidator([
                'allowFloat' => true,
                'allowEmpty' => true,
                'min' => 0
            ])
        );

        $validator->add(
            'endSaleTime',
            new Validation\Validator\Callback([
                'callback' => function($data) {
                    if (!empty($data['endSaleTime'] && strtotime($data['endSaleTime']) > time())) {
                        return new Validation\Validator\Date([
                            'format' => 'Y-m-d H:i:s'
                        ]);
                    }
                    return true;
                },
                'message' => 'Invalid date format or Date should be greater than now'
            ])
        );

        $validator->add(
            'sku',
            new Validation\Validator\AlphaNumericValidator([
                'allowEmpty' => false,
                'message' => 'Invalid SKU'
            ])
        );

        $validator->add(
            'image',
            new Validation\Validator\Callback([
                'callback' => function($data) {
                    if (!empty($data['image']) && !$this->getUuidUtil()->isValid($data['image'])) {
                        return false;
                    }
                    return true;
                },
                'message' => 'Invalid image id'
            ])
        );

        $validator->add(
            ['attributes', 'customAttributes', 'conditions'],
            new Validation\Validator\Callback([
                'callback' => function($data) {
                    if (!empty($data['attributes']) && !is_array($data['attributes'])
                        || !empty($data['customAttributes'] && !is_array($data['customAttributes']))
                        || !empty($data['conditions'] && !is_array($data['conditions']))
                    ) {
                        return false;
                    }
                    return true;
                },
                'message' => 'Invalid :field'
            ])
        );

        // Check conditions duplicated entries in same include or exclude
        $validator->add(
            'conditions',
            new Validation\Validator\Callback([
                'callback' => function($data) {
                    $includeCountries = $data['conditions']['include']['countries'];
                    $excludeCountries = $data['conditions']['exclude']['countries'];
                    if (array_unique($includeCountries) !== $includeCountries
                        || array_unique($excludeCountries) !== $excludeCountries
                    ) {
                        return false;
                    }
                    return true;
                },
                'message' => 'There are duplicated entries'
            ])
        );

        // Check conditions duplicate in include and exclude
        $validator->add(
            'conditions',
            new Validation\Validator\Callback([
                'callback' => function ($data) {
                    $includeCountries = $data['conditions']['include']['countries'];
                    $excludeCountries = $data['conditions']['exclude']['countries'];
                    if ((!empty($includeCountries) || !empty($excludeCountries))
                        && count(array_intersect($includeCountries, $excludeCountries))) {
                        return false;
                    }
                    return true;
                },
                'message' => 'Included values should not exist in excluded values'
            ])
        );

        return $validator->validate([
            'quantity' => $this->getQuantity(),
            'price' => $this->getPrice(),
            'salePrice' => $this->getSalePrice(),
            'endSaleTime' => $this->getEndSaleTime(),
            'image' => $this->getImage(),
            'sku' => $this->getSku(),
            'attributes' => $this->getAttributes(),
            'customAttributes' => $this->getCustomAttributes(),
            'conditions' => $this->getConditions()
        ]);
    }

    public function isValid(): bool
    {
        $messages = $this->validate();
        if (!empty($messages)) {
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
     * @throws OperationFailed
     */
    public function invalidRequest($message = null)
    {
        $message = $message ?? $this->errorMessages;
        throw new OperationFailed($message, 400);
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

        if (!empty($this->getQuantity())) {
            $result['quantity'] = $this->getQuantity();
        }

        if (!empty($this->getPrice())) {
            $result['price'] = $this->getPrice();
        }

        if (!empty($this->getSalePrice())) {
            $result['sale_price'] = $this->getSalePrice();
        }

        if (!empty($this->getEndSaleTime())) {
            $result['end_sale_time'] = $this->getEndSaleTime();
        }

        if (!empty($this->getImage())) {
            $result['image'] = $this->getImage();
        }

        if (!empty($this->getSku())) {
            $result['sku'] = $this->getSku();
        }

        if (!empty($this->getAttributes())) {
            $result['attributes'] = $this->getAttributes();
        }

        if (!empty($this->getCustomAttributes())) {
            $result['custom_attributes'] = $this->getCustomAttributes();
        }

        if (!empty($this->getConditions())) {
            $result['conditions'] = $this->getConditions();
        }

        if (empty($result)) {
            throw new \Exception('Nothing to be updated', 400);
        }

        $result['variation_id'] = $this->getUuidUtil()->uuid();

        return $result;
    }
}
