<?php
/**
 * User: Wajdi Jurry
 * Date: ١٢‏/٤‏/٢٠٢٠
 * Time: ٤:١٤ م
 */

namespace app\common\requestHandler\rate;


use app\common\requestHandler\IArrayData;
use app\common\requestHandler\RequestAbstract;
use app\common\validators\rules\RateRules;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;

class UpdateRequestHandler extends RequestAbstract implements IArrayData
{
    /** @var string */
    public $rateId;

    /** @var int */
    public $stars;

    /** @var string */
    public $text;

    /** @var array */
    public $imagesIds = [];

    /** @var array */
    public $deletedImagesIds = [];

    /** @var RateRules */
    protected $validationRules;

    /**
     * UpdateRequestHandler constructor.
     */
    public function __construct()
    {
        parent::__construct(new RateRules());
    }

    /**
     * @inheritDoc
     */
    public function validate(): Group
    {
        $validator = new Validation();

        $validator->add(
            'stars',
            new Validation\Validator\NumericValidator([
                'min' => $this->validationRules->minStars,
                'max' => $this->validationRules->maxStars
            ])
        );

        $validator->add(
            'text',
            new Validation\Validator\StringLength([
                'max' => $this->validationRules->maxTextLength,
                'allowEmpty' => $this->validationRules->allowEmptyText
            ])
        );

        $validator->add(
            'imagesIds',
            new Validation\Validator\Callback([
                'callback' => function ($data) {
                    if (!empty($data['imagesIds']) && count($data['imagesIds']) > $this->validationRules->maxNumOfImages) {
                        return false;
                    }
                    return true;
                }
            ])
        );

        return $validator->validate([
            'stars' => $this->stars,
            'text' => $this->text,
            'imagesIds' => $this->imagesIds
        ]);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'rateId' => $this->rateId,
            'stars' => $this->stars,
            'text' => $this->text,
            'imagesIds' => $this->imagesIds,
            'deletedImagesIds' => $this->deletedImagesIds
        ];
    }
}