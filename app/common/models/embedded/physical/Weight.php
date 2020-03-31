<?php
/**
 * User: Wajdi Jurry
 * Date: 31/08/2018
 * Time: 10:21 PM
 */

namespace app\common\models\embedded\physical;


class Weight
{
    /** @var float|null */
    public $amount;

    /** @var string|null */
    public $unit;

    /**
     * @return array
     */
    public function attributes(): array
    {
        return [
            'amount',
            'unit'
        ];
    }

    /**
     * @param mixed $data
     */
    public function setAttributes($data): void
    {
        if (!empty($data)) {
            $this->amount = $data->amount ?? null;
            $this->unit = $data->unit ?? null;
        }
    }

    /**
     * @return array
     */
    public function toApiArray(): array
    {
        return [
            'amount' => $this->amount,
            'unit' => $this->unit
        ];
    }
}
