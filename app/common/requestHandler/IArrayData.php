<?php


namespace app\common\requestHandler;


interface IArrayData extends IRequestHandler
{
    /**
     * @return array
     */
    public function toArray(): array;
}