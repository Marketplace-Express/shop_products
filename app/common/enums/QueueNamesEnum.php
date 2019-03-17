<?php
/**
 * User: Wajdi Jurry
 * Date: 16/02/19
 * Time: 06:13 م
 */

namespace Shop_products\Enums;


class QueueNamesEnum
{
    const CATEGORY_SYNC_QUEUE = 'categories-sync';
    const CATEGORY_ASYNC_QUEUE = 'categories-async';
    const PRODUCT_SYNC_QUEUE = 'products-sync';
    const PRODUCT_ASYNC_QUEUE = 'products-async';
}