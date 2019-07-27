<?php
/**
 * User: Wajdi Jurry
 * Date: 13/01/19
 * Time: 11:11 ص
 */

namespace app\common\interfaces;


use app\common\exceptions\NotFoundException;

interface DataSourceInterface
{
    public function getByIdentifier(string $categoryId, string $vendorId): ?array;

    /**
     * @param string $productId
     * @param string $vendorId
     * @return mixed
     * @throws NotFoundException
     */
    public function getById(string $productId, string $vendorId);
}