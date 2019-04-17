<?php
/**
 * User: Wajdi Jurry
 * Date: 13/01/19
 * Time: 11:11 ص
 */

namespace Shop_products\Interfaces;


interface DataSourceInterface
{
    public function getByCategoryId(string $categoryId, string $vendorId): ?array;

    public function getByVendorId(string $vendorId): ?array;

    public function getById(string $productId, string $vendorId);
}