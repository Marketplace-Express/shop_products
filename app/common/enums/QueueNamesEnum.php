<?php
/**
 * User: Wajdi Jurry
 * Date: 16/02/19
 * Time: 06:13 م
 */

namespace app\common\enums;


class QueueNamesEnum
{
    const CATEGORY_SYNC_QUEUE = 'categories_sync';
    const CATEGORY_ASYNC_QUEUE = 'categories_async';
    const PRODUCT_SYNC_QUEUE = 'products_sync';
    const PRODUCT_ASYNC_QUEUE = 'products_async';
}