<?php
/**
 * User: Wajdi Jurry
 * Date: 2020/09/25
 * Time: 01:12
 */

namespace app\modules\cli\request;


interface RequestHandlerInterface
{
    public function process();
}