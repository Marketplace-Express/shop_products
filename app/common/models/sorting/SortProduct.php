<?php
/**
 * User: Wajdi Jurry
 * Date: ٧‏/٩‏/٢٠١٩
 * Time: ١٢:٣٦ م
 */

namespace app\common\models\sorting;

/**
 * Class Product
 * @package app\common\models\sorting
 */
class SortProduct
{
    const ORDERING =  [
        1 => 'ASC',
        -1 => 'DESC'
    ];

    const FIELD_MAPPING = [
        'id' => 'productId',
        'name' => 'productTitle',
        'price' => 'productPrice',
        'createdAt' => 'createdAt'
    ];

    /** @var string */
    public $id;

    /** @var int */
    public $name;

    /** @var int */
    public $price;

    /** @var int */
    public $createdAt;

    /** @var array */
    private $sorting = [];

    /**
     * @return array
     */
    protected function prepareSorting(): array
    {
        foreach (self::FIELD_MAPPING as $field => $modelAttribute) {
            if ($this->$field) {
                $this->sorting[$modelAttribute] = $this->$field;
            }
        }

        $this->sorting = array_filter($this->sorting, function ($attribute) {
            return !empty($attribute) && !is_null($attribute);
        });

        return $this->sorting;
    }

    /**
     * @return array
     */
    protected function prepareDirection(): array
    {
        $this->sorting = array_map(function ($field, $order) {
            if (!array_key_exists($order, self::ORDERING)) {
                $order = 1;
            }
            return $field . " " . self::ORDERING[$order];
        }, array_keys($this->sorting), $this->sorting);

        return $this->sorting;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getSqlSort(): string
    {
        $this->prepareSorting();
        $this->prepareDirection();
        if (empty($this->sorting)) {
            throw new \Exception('Invalid sorting arguments', 400);
        }
        return join(', ', $this->sorting);
    }
}
