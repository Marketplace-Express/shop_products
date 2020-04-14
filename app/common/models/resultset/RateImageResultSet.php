<?php
/**
 * User: Wajdi Jurry
 * Date: ١٢‏/٤‏/٢٠٢٠
 * Time: ١:١١ م
 */

namespace app\common\models\resultset;


use Phalcon\Mvc\Model\Resultset\Simple;

/**
 * Class RateImageResultSet
 * @package app\common\models\resultsets
 */
class RateImageResultSet extends Simple
{
    /**
     * @return array
     */
    public function toApiArray(): array
    {
        $result = [];
        foreach ($this as $rateImage) {
            $result[] = $rateImage->toApiArray();
        }
        return $result;
    }
}