<?php
/**
 * User: Wajdi Jurry
 * Date: 28/03/2020
 * Time: 10:34 م
 */

namespace app\common\models\embedded;


use app\common\validators\PackageValidator;
use app\common\validators\WeightValidator;
use app\common\models\embedded\physical\{
    Package,
    Weight
};
use Phalcon\Validation;

/**
 * Class PhysicalProperties
 * @package app\common\models
 * @Entity
 */
class PhysicalProperties extends Properties
{
    /** @var Package */
    public $package;

    /** @var Weight */
    public $weight;

    /**
     * @return Package
     */
    protected function getPackage(): Package
    {
        if (!$this->package || !$this->package instanceof Package) {
            $this->package = new Package();
        }
        return $this->package;
    }

    /**
     * @return Weight
     */
    protected function getWeight(): Weight
    {
        if (!$this->weight || !$this->weight instanceof Weight) {
            $this->weight = new Weight();
        }
        return $this->weight;
    }

    /**
     * @return array
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'package',
            'weight'
        ]);
    }

    /**
     * @param array $data
     */
    public function setAttributes(array $data)
    {
        parent::setAttributes($data);
        $this->getPackage()->setAttributes($data['package']);
        $this->getWeight()->setAttributes($data['weight']);
    }

    /**
     * @return array
     */
    public function toApiArray(): array
    {
        return array_merge(parent::toApiArray(), [
            'package' => $this->package,
            'weight' => $this->weight
        ]);
    }

    /**
     * @return bool
     */
    public function validation()
    {
        $validation = new Validation();

        $validation->add(
            'weight',
            new WeightValidator([
                'allowEmpty' => true
            ])
        );

        $validation->add(
            'package',
            new PackageValidator([
                'allowEmpty' => true
            ])
        );

        $this->_errorMessages = $validation->validate([
            'weight' => $this->weight,
            'package' => $this->package
        ]);

        return !$this->_errorMessages->count();
    }
}
