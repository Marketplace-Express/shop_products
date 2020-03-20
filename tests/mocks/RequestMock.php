<?php
/**
 * User: Wajdi Jurry
 * Date: 19/10/18
 * Time: 03:02 م
 */

namespace tests\mocks;

use Phalcon\Http\Request;

class RequestMock extends Request
{
    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function setQuery($attribute, $data)
    {
        $this->$attribute = $data;
    }

    public function getQuery($name = null, $filters = null, $defaultValue = null, $notAllowEmpty = false, $noRecursive = false)
    {
        if (!property_exists($this, $name)) {
            $this->__set($name, null);
        }
        return $this->$name;
    }

    public function getJsonRawBody($associative = false)
    {
        return $this->_rawBody;
    }

    public function setJsonRawBody($data)
    {
        $this->_rawBody = $data;
    }
}