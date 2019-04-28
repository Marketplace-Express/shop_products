<?php
/**
 * User: Wajdi Jurry
 * Date: 28/12/18
 * Time: 04:21 م
 */

namespace Shop_products\Enums;


class MongoQueryOperatorsEnum
{
    const OP_IN = '$in';
    const OP_EQUALS = '$eq';
    const OP_NOT_EQUALS = '$ne';
    const OP_GREATER_THAN = '$gt';
    const OP_GREATER_THAN_EQUALS = '$gte';
    const OP_LESS_THAN = '$lt';
    const OP_LESS_THAN_EQUALS = '$lte';
    const OP_NOT_IN = '$nin';
}