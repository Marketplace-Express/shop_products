<?php
/**
 * User: Wajdi Jurry
 * Date: ٣٠‏/٨‏/٢٠١٩
 * Time: ٣:٢٨ م
 */

namespace app\common\validators\rules;


class ImagesRules extends RulesAbstract
{
    /** @var int */
    public $maxSize = 1048576; // 1 MB
    
    /** @var array */
    public $allowedTypes = [
        'image/jpg',
        'image/jpeg',
        'image/png',
        'image/webp'
    ];
    
    /** @var string */
    public $minResolution = '800x600';

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [];
    }
}
