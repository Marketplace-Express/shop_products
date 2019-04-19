<?php
/**
 * User: Wajdi Jurry
 * Date: 05/04/19
 * Time: 02:48 م
 */

namespace Shop_products\RequestHandler\Image;


use Exception;
use Phalcon\Di;
use Phalcon\Http\Request\File;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;
use Shop_products\Controllers\BaseController;
use Shop_products\Exceptions\ArrayOfStringsException;
use Shop_products\RequestHandler\RequestHandlerInterface;
use Shop_products\Utils\DigitalUnitsConverterUtil;
use Shop_products\Validators\UuidValidator;

class UploadRequestHandler extends BaseController implements RequestHandlerInterface
{

    /** @var File $image */
    private $image;

    /** @var string $albumId */
    private $albumId;

    /** @var string $productId */
    private $productId;

    private $errorMessages;

    /**
     * Set uploaded image
     */
    public function onConstruct()
    {
        if (!empty($uploadedFile = $this->request->getUploadedFiles('image'))) {
            $this->image = $uploadedFile[0];
        }
    }

    /**
     * @param string $albumId
     */
    public function setAlbumId($albumId)
    {
        $this->albumId = $albumId;
    }

    /**
     * @param string $productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    }

    private function getValidationConfig()
    {
        return Di::getDefault()->getConfig()->application->validation->image;
    }

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     * @throws Exception
     */
    public function validate(): Group
    {
        if (empty($this->image)) {
            throw new Exception('Empty image', 400);

        }
        $validator = new Validation();

        $validator->add(
            'productId',
            new UuidValidator()
        );

        $validator->add(
            ['albumId', 'productId'],
            new Validation\Validator\PresenceOf([
                'allowEmpty' => false
            ])
        );

        $validator->add(
            'image',
            new Validation\Validator\File([
                'maxSize' => $this->getValidationConfig()->maxSize,
                'allowedTypes' => (array) $this->getValidationConfig()->allowedTypes,
                'minResolution' => $this->getValidationConfig()->minResolution,
                'messageSize' => ':field exceeds ' . DigitalUnitsConverterUtil::bytesToMb(
                    $this->getValidationConfig()->maxSize) . ' Mb',
                'allowEmpty' => false
            ])
        );

        return $validator->validate([
            'image' => [
                'name' => $this->image->getName(),
                'tmp_name' => $this->image->getTempName(),
                'error' => $this->image->getError(),
                'type' => $this->image->getType(),
                'size' => $this->image->getSize()
            ],
            'albumId' => $this->albumId,
            'productId' => $this->productId
        ]);
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isValid(): bool
    {
        $messages = $this->validate();
        foreach ($messages as $message) {
            $this->errorMessages[$message->getField()] = $message->getMessage();
        }
        return !count($messages);
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
        throw new ArrayOfStringsException($this->errorMessages, 400);
    }

    public function successRequest($message = null)
    {
        return $this->response
            ->setJsonContent([
                'status' => 200,
                'message' => $message
            ]);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'image' => $this->image,
            'albumId' => $this->albumId,
            'productId' => $this->productId
        ];
    }
}