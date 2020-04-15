<?php
/**
 * User: Wajdi Jurry
 * Date: 27/02/19
 * Time: 09:24 م
 */

namespace app\common\requestHandler\product;


use app\common\requestHandler\RequestAbstract;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;

class SearchRequestHandler extends RequestAbstract
{
    /** @var string */
    public $keyword;

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();

        $validator->add(
            'keyword',
            new Validation\Validator\PresenceOf()
        );

        return $validator->validate([
            'keyword' => $this->keyword
        ]);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'keyword' => $this->keyword . '*'
        ];
    }
}
