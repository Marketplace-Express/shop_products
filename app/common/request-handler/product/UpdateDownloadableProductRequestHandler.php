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
use Shop_products\Utils\DigitalUnitsConverterUtil;
use Shop_products\Validators\TypeValidator;
use Shop_products\Validators\UuidValidator;

class UpdateDownloadableProductRequestHandler extends AbstractUpdateRequestHandler implements RequestHandlerInterface
{
    private $digitalSize;

    /**
     * @param mixed $digitalSize
     */
    public function setDigitalSize($digitalSize): void
    {
        $this->digitalSize = $digitalSize;
    }

    private function getValidationConfig()
    {
        return \Phalcon\Di::getDefault()->getConfig()->application->validation->productTitle;
    }

    private function getMaxDigitalSizeValidationConfig()
    {
        return $this->getDI()->getConfig()->application->validation->downloadable->maxDigitalSize;
    }

    protected function fields()
    {
        return array_merge(parent::fields(), [
            'digitalSize' => $this->digitalSize
        ]);
    }

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();

        $validator->add(
            'digitalSize',
            new Validation\Validator\NumericValidator([
                'min' => 1,
                'max' => $this->getMaxDigitalSizeValidationConfig(),
                'messageMaximum' => 'Digital size exceeds the max limit ' . DigitalUnitsConverterUtil::bytesToMb($this->getMaxDigitalSizeValidationConfig()),
                'messageMinimum' => 'Invalid digital size'
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

        if (!empty($this->digitalSize)) {
            $result['productDigitalSize'] = $this->digitalSize;
        }

        if (empty($result)) {
            throw new \Exception('Nothing to be updated', 400);
        }

        return $result;
    }
}