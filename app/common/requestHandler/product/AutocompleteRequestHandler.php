<?php
/**
 * User: Wajdi Jurry
 * Date: 27/02/19
 * Time: 09:24 م
 */

namespace app\common\requestHandler\product;


use Phalcon\Validation;
use Phalcon\Validation\Message\Group;

class AutocompleteRequestHandler extends SearchRequestHandler
{

    /** @var null|string */
    public $scope;

    /**
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();

        $validator->add(
            'scope',
            new Validation\Validator\PresenceOf()
        );

        return $validator->validate([
            'scope' => $this->scope
        ]);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'keyword' => $this->keyword,
            'scope' => $this->scope
        ];
    }
}
