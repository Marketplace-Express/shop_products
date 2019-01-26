<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 08:21 م
 */

namespace Shop_products\RequestHandler;

use Phalcon\Validation\Message\Group;

interface RequestHandlerInterface
{
    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate() : Group;

    public function isValid() : bool;

    public function notFound($message = 'Not Found');

    public function invalidRequest($message = null);

    public function successRequest($message = null);

    public function toArray(): array;
}