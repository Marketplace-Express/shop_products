<?php
/**
 * User: Wajdi Jurry
 * Date: 3/27/20
 * Time: 8:43 PM
 */

namespace app\common\models\embedded;


class Segment
{
    /** @var array */
    public $countries = [];

    /** @var array */
    public $age = [];

    /** @var array */
    public $gender = [];

    /**
     * @param mixed $data
     */
    public function setAttributes($data): void
    {
        if (!empty($data)) {
            $this->countries = $data->countries;
            $this->age = $data->age;
            $this->gender = $data->gender;
        }
    }

    /**
     * @return array
     */
    public function toApiArray(): array
    {
        return [
            'countries' => $this->countries,
            'age' => $this->age,
            'gender' => $this->gender
        ];
    }
}