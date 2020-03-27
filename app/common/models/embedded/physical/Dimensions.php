<?php
/**
 * User: Wajdi Jurry
 * Date: ٣١‏/٨‏/٢٠١٩
 * Time: ١٠:٢٠ م
 */

namespace app\common\models\embedded\physical;


class Dimensions
{
    /** @var float[] */
    public $dimensions;

    /** @var string */
    public $unit;

    /**
     * @param array $data
     */
    public function setAttributes(array $data): void
    {
        $this->dimensions = $data->dimensions ?? [];
        $this->unit = $data->unit ?? null;
    }

    /**
     * @return array
     */
    public function toApiArray(): array
    {
        return [
            'dimensions' => $this->dimensions,
            'unit' => $this->unit
        ];
    }
}
