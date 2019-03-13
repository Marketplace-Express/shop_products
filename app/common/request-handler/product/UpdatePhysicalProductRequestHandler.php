<?php
/**
 * User: Wajdi Jurry
 * Date: 13/01/19
 * Time: 07:04 م
 */

namespace Shop_products\RequestHandler\Product;


use Phalcon\Validation;
use Phalcon\Validation\Message\Group;
use Shop_products\Exceptions\ArrayOfStringsException;
use Shop_products\RequestHandler\RequestHandlerInterface;
use Shop_products\Validators\TypeValidator;
use Shop_products\Validators\UuidValidator;

class UpdatePhysicalProductRequestHandler extends AbstractUpdateRequestHandler implements RequestHandlerInterface
{
    private $brandId;
    private $weight;
    private $dimensions;

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
     * @param mixed $dimensions
     */
    public function setDimensions($dimensions): void
    {
        $this->dimensions = $dimensions;
    }

    /**
     * @return array
     */
    protected function fields()
    {
        return array_merge(parent::fields(), [
            'brandId' => $this->brandId,
            'weight' => $this->weight,
            'dimensions' => $this->dimensions
        ]);
    }

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();

        $validator->add(
            ['brandId'],
            new UuidValidator()
        );

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
                'allowEmpty' => false,
                'message' => 'Invalid dimensions'
            ])
        );

        return $validator->validate($this->fields());
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

        if (!empty($this->brandId)) {
            $result['productBrandId'] = $this->brandId;
        }

        if (!empty($this->weight)) {
            $result['productWeight'] = $this->weight;
        }

        if (!empty($this->dimensions)) {
            $result['productDimensions'] = $this->dimensions;
        }

        if (empty($result)) {
            throw new \Exception('Nothing to be updated', 400);
        }

        return array_merge(parent::toArray(), $result);
    }
}