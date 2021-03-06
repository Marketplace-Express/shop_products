<?php
/**
 * User: Wajdi Jurry
 * Date: 05/04/19
 * Time: 02:48 م
 */

namespace app\common\requestHandler\image;


use app\common\enums\ImagesTypesEnum;
use app\common\requestHandler\IArrayData;
use app\common\requestHandler\RequestAbstract;
use app\common\validators\rules\ImagesRules;
use Phalcon\Http\Request\File;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;
use app\common\utils\DigitalUnitsConverterUtil;
use app\common\validators\UuidValidator;

class UploadRequestHandler extends RequestAbstract implements IArrayData
{
    /** @var File */
    public $image;

    /** @var string */
    public $albumId;

    /** @var string */
    public $userId;

    /** @var string */
    public $productId;

    /** @var string */
    public $entity = ImagesTypesEnum::TYPE_PRODUCT;

    /** @var ImagesRules */
    protected $validationRules;

    /**
     * Set uploaded image
     */
    public function __construct()
    {
        $images = $this->request->getUploadedFiles();
        $this->image = array_shift($images); // take only 1 image
        parent::__construct(new ImagesRules());
    }

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();

        $validator->add(
            ['userId', 'productId'],
            new Validation\Validator\PresenceOf()
        );

        $validator->add(
            ['userId', 'productId'],
            new UuidValidator()
        );

        $validator->add(
            'productId',
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
            'entity',
            new Validation\Validator\InclusionIn([
                'domain' => ImagesTypesEnum::getValues()
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
            'userId' => $this->userId,
            'productId' => $this->productId,
            'entity' => $this->entity
        ]);
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        if (!$this->request->hasFiles()) {
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
            'userId' => $this->userId,
            'productId' => $this->productId,
            'entity' => $this->entity,
            'albumId' => $this->albumId
        ];
    }
}
