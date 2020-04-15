<?php
declare(strict_types=1);
/**
 * User: Wajdi Jurry
 * Date: 13/01/19
 * Time: 07:04 م
 */

namespace app\common\requestHandler\product;

use app\common\requestHandler\RequestAbstract;
use app\common\validators\PackageValidator;
use app\common\validators\rules\DownloadableProductRules;
use app\common\validators\rules\PhysicalProductRules;
use app\common\validators\SegmentsValidator;
use app\common\validators\WeightValidator;
use Phalcon\Utils\Slug;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;
use app\common\utils\DigitalUnitsConverterUtil;
use app\common\validators\TypeValidator;
use app\common\validators\UuidValidator;

class UpdateRequestHandler extends RequestAbstract
{
    /** @var string */
    public $title;

    /** @var string */
    public $categoryId;

    /** @var string */
    public $customPageId;

    /** @var float */
    public $price;

    /** @var float */
    public $salePrice;

    /** @var string */
    public $endSaleTime;

    /** @var array */
    public $keywords;

    /** @var bool */
    public $isPublished;

    /** @var string */
    public $brandId;

    /** @var \app\common\models\embedded\physical\Weight */
    public $weight;

    /** @var \app\common\models\embedded\physical\Package */
    public $package;

    /** @var \app\common\models\embedded\Segment */
    public $segment;

    /** @var int */
    public $digitalSize;

    /** @var PhysicalProductRules */
    private $physicalProductRules;

    /** @var DownloadableProductRules */
    private $downloadableProductRules;

    /**
     * @return PhysicalProductRules
     */
    private function getPhysicalProductRules(): PhysicalProductRules
    {
        return $this->physicalProductRules ??
            $this->physicalProductRules = new PhysicalProductRules();
    }

    /**
     * @return DownloadableProductRules
     */
    private function getDownloadableRules(): DownloadableProductRules
    {
        return $this->downloadableProductRules ??
            $this->downloadableProductRules = new DownloadableProductRules();
    }

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();

        // Validate English input
        $validator->add(
            'name',
            new Validation\Validator\Callback([
                'callback' => function ($data) {
                    $name = preg_replace('/[\d\s_]/i', '', $data['title']); // clean string
                    if (!empty($name) && preg_match('/[a-z]/i', $name) == false) {
                        return false;
                    }
                    return true;
                },
                'message' => 'English language only supported'
            ])
        );

        $validator->add(
            'title',
            new Validation\Validator\AlphaNumericValidator([
                'whiteSpace' => $this->getPhysicalProductRules()->productTitle->whiteSpace,
                'underscore' => $this->getPhysicalProductRules()->productTitle->underscore,
                'min' => $this->getPhysicalProductRules()->productTitle->min,
                'max' => $this->getPhysicalProductRules()->productTitle->max,
                'allowEmpty' => true
            ])
        );

        $validator->add(
            ['categoryId', 'customPageId', 'brandId'],
            new UuidValidator([
                'allowEmpty' => true
            ])
        );

        $validator->add(
            'price',
            new TypeValidator([
                'type' => TypeValidator::TYPE_FLOAT
            ])
        );

        $validator->add(
            'salePrice',
            new TypeValidator([
                'type' => TypeValidator::TYPE_FLOAT
            ])
        );

        $validator->add(
            'price',
            new Validation\Validator\NumericValidator([
                'allowFloat' => true,
                'min' => 0,
                'allowEmpty' => true
            ])
        );

        $validator->add(
            'salePrice',
            new Validation\Validator\NumericValidator([
                'allowFloat' => true,
                'min' => 0,
                'allowEmpty' => true
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
                'callback' => function ($data) {
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
            'weight',
            new WeightValidator([
                'allowEmpty' => true
            ])
        );

        $validator->add(
            'package',
            new PackageValidator([
                'allowEmpty' => true
            ])
        );

        $validator->add(
            'segment',
            new SegmentsValidator([
                'allowEmpty' => true
            ])
        );

        $validator->add(
            'digitalSize',
            new Validation\Validator\NumericValidator([
                'min' => 1,
                'max' => $this->getDownloadableRules()->maxDigitalSize,
                'messageMaximum' => 'Digital size exceeds the max limit ' .
                    DigitalUnitsConverterUtil::bytesToMb(
                        $this->getDownloadableRules()->maxDigitalSize
                    ) . ' Mb',
                'messageMinimum' => 'Invalid digital size',
                'allowEmpty' => true
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
            'title' => $this->title,
            'categoryId' => $this->categoryId,
            'customPageId' => $this->customPageId,
            'price' => $this->price,
            'salePrice' => $this->salePrice,
            'endSaleTime' => $this->endSaleTime,
            'keywords' => $this->keywords,
            'brandId' => $this->brandId,
            'weight' => $this->weight,
            'package' => $this->package,
            'segment' => $this->segment,
            'digitalSize' => $this->digitalSize,
            'isPublished' => $this->isPublished
        ]);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        $result = [];

        if (!empty($this->title)) {
            $result['productTitle'] = $this->title;
            $result['productLinkSlug'] = (new Slug())->generate($this->title);
        }

        if (!empty($this->categoryId)) {
            $result['productCategoryId'] = $this->categoryId;
        }

        if (!empty($this->customPageId)) {
            $result['productCustomPageId'] = $this->customPageId;
        }

        if (!empty($this->price)) {
            $result['productPrice'] = $this->price;
        }

        if (!empty($this->salePrice)) {
            $result['productSalePrice'] = $this->salePrice;
        }

        if (!empty($this->endSaleTime)) {
            $result['productEndSaleTime'] = $this->endSaleTime;
        }

        if (!empty($this->keywords)) {
            $result['keywords'] = $this->keywords;
        }

        if (!empty($this->brandId)) {
            $result['productBrandId'] = $this->brandId;
        }

        if (in_array($this->isPublished, [true, false], true)) {
            $result['isPublished'] = $this->isPublished;
        }

        $result['weight'] = $this->weight;
        $result['package'] = $this->package;
        $result['segment'] = $this->segment;
        $result['digitalSize'] = $this->digitalSize;


        if (empty($result)) {
            throw new \Exception('Nothing to be updated', 400);
        }

        return $result;
    }
}
