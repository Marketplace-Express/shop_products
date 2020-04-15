<?php
/**
 * User: Wajdi Jurry
 * Date: 13/01/19
 * Time: 02:13 م
 */

namespace app\common\requestHandler\product;


use app\common\validators\PackageValidator;
use app\common\validators\rules\PhysicalProductRules;
use app\common\validators\WeightValidator;
use Phalcon\Validation\Message\Group;
use app\common\enums\ProductTypesEnum;

class CreatePhysicalProductRequestHandler extends AbstractCreateRequestHandler
{

    /**
     * @var \app\common\models\embedded\physical\Weight
     */
    public $weight;

    /**
     * @var \app\common\models\embedded\physical\Package
     */
    public $package;

    /**
     * @var PhysicalProductRules
     */
    protected $validationRules;

    /**
     * CreatePhysicalProductRequestHandler constructor.
     */
    public function __construct()
    {
        parent::__construct(new PhysicalProductRules());
    }

    /**
     * @return array
     */
    protected function fields()
    {
        return array_merge(parent::fields(), [
            'brandId'  => $this->brandId,
            'weight' => $this->weight,
            'package' => $this->package
        ]);
    }

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = $this->mainValidator();

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

        return $validator->validate($this->fields());
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'productBrandId' => $this->brandId,
            'productType' => ProductTypesEnum::TYPE_PHYSICAL,
            'weight' => $this->weight,
            'package' => $this->package
        ]);
    }
}
