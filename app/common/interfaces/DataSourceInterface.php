<?php
/**
 * User: Wajdi Jurry
 * Date: 13/01/19
 * Time: 11:11 ص
 */

namespace app\common\interfaces;


use app\common\exceptions\NotFound;
use app\common\models\sorting\SortProduct;

interface DataSourceInterface
{
    public function getByIdentifier(string $categoryId, string $vendorId, int $page, int $limit, SortProduct $sort);

    /**
     * @param string $productId
     * @param string $vendorId
     * @return mixed
     * @throws NotFound
     */
    public function getById(string $productId, string $vendorId);
}
