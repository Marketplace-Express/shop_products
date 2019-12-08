<?php
/**
 * User: Wajdi Jurry
 * Date: 05/04/19
 * Time: 02:48 م
 */

namespace app\common\requestHandler\image;


use app\common\requestHandler\RequestAbstract;
use app\common\validators\rules\ImagesRules;
use app\common\validators\TypeValidator;
use Phalcon\Http\Request\File;
use Phalcon\Mvc\Controller;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;
use app\common\utils\DigitalUnitsConverterUtil;
use app\common\validators\UuidValidator;

class UploadRequestHandler extends RequestAbstract
{
    /** @var File */
    public $image;

    /** @var string */
    public $albumId;

    /** @var string */
    public $productId;

    /** @var bool */
    public $isVariationImage = false;

    /**
     * @var ImagesRules
     */
    protected $validationRules;

    /**
     * Set uploaded image
     * @param Controller $controller
     */
    public function __construct(Controller $controller)
    {
        $images = $controller->request->getUploadedFiles();
        $this->image = array_shift($images); // take only 1 image
        parent::__construct($controller, new ImagesRules());
    }

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
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
                'maxSize' => $this->validationRules->maxSize,
                'allowedTypes' => $this->validationRules->allowedTypes,
                'minResolution' => $this->validationRules->minResolution,
                'messageSize' => ':field exceeds ' . DigitalUnitsConverterUtil::bytesToMb(
                    $this->validationRules->maxSize) . ' Mb',
                'allowEmpty' => false
            ])
        );

        $validator->add(
            'isVariationImage',
            new TypeValidator([
                'type' => TypeValidator::TYPE_BOOLEAN
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
            'productId' => $this->productId,
            'isVariationImage' => $this->isVariationImage
        ]);
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        if (!$this->controller->request->hasFiles()) {
            $this->errorMessages[] = new Validation\Message('required', 'image');
            return false;
        }
        return parent::isValid();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'image' => $this->image,
            'albumId' => $this->albumId,
            'productId' => $this->productId,
            'isVariationImage' => $this->isVariationImage
        ];
    }
}
