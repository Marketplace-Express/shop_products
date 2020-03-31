<?php
/**
 * User: Wajdi Jurry
 * Date: ٣١‏/٨‏/٢٠١٩
 * Time: ١٠:٢٠ م
 */

namespace app\common\models\embedded\physical;


class Package
{
    /** @var float[] */
    public $dimensions;

    /** @var string */
    public $unit;

    /**
     * @param mixed $data
     */
    public function setAttributes($data): void
    {
        if (!empty($data)) {
            $this->dimensions = $data->dimensions ?? [];
            $this->unit = $data->unit ?? null;
        }
    }

    /**
     * @return array
     */
    public function toApiArray(): array
    {
        return [
            'dimensions' => $this->dimensions ? array_map('floatval', $this->dimensions) : [],
            'unit' => $this->unit
        ];
    }
}
