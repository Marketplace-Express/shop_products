<?php
/**
 * User: Wajdi Jurry
 * Date: 13/01/19
 * Time: 02:13 م
 */

namespace Shop_products\RequestHandler\Product;


use Phalcon\Validation;
use Phalcon\Validation\Message\Group;
use Shop_products\Enums\ProductTypesEnums;
use Shop_products\Exceptions\ArrayOfStringsException;
use Shop_products\RequestHandler\RequestHandlerInterface;
use Shop_products\Validators\TypeValidator;
use Shop_products\Validators\UuidValidator;

class CreatePhysicalProductRequestHandler extends AbstractCreateRequestHandler implements RequestHandlerInterface
{
    private $brandId;
    private $weight;
    private $packageDimensions;

    /**
     * @param mixed $brandId
     */
    public function setBrandId($brandId): void
    {
        $this->brandId = $brandId;
    }

    /**
     * @param mixed $weight
     */
    public function setWeight($weight): void
    {
        $this->weight = $weight;
    }

    /**
     * @param mixed $packageDimensions
     */
    public function setPackageDimensions($packageDimensions): void
    {
        $this->packageDimensions = $packageDimensions;
    }

    private function objectToArray($object)
    {
        return json_decode(json_encode($object), true);
    }

    /**
     * @return array
     */
    protected function fields()
    {
        return array_merge(parent::fields(), [
            'brandId'  => $this->brandId,
            'weight' => $this->weight,
            'packageDimensions' => $this->objectToArray($this->packageDimensions)
        ]);
    }

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();

        $validator->add(
            'brandId',
            new UuidValidator([
                'allowEmpty' => true
            ])
        );

        $validator->add(
            'weight',
            new Validation\Validator\NumericValidator([
                'allowFloat' => true,
                'allowEmpty' => false
            ])
        );

        $validator->add(
            'packageDimensions',
            new Validation\Validator\PresenceOf()
        );

        $validator->add(
            'packageDimensions',
            new TypeValidator([
                'type' => TypeValidator::TYPE_FLOAT,
                'allowEmpty' => false,
                'message' => 'Invalid dimensions'
            ])
        );

        return $validator->validate($this->fields());
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        // TODO: TO BE ENHANCED LATER
        $messages = $this->validate();
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
        return parent::isValid();
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
        return array_merge(parent::toArray(), [
            'productBrandId' => $this->brandId,
            'productType' => ProductTypesEnums::TYPE_PHYSICAL,
            'productWeight' => $this->weight,
            'productPackageDimensions' => $this->objectToArray($this->packageDimensions)
        ]);
    }
}