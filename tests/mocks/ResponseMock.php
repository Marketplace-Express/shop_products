<?php
/**
 * User: Wajdi Jurry
 * Date: 26/10/18
 * Time: 10:38 م
 */

namespace tests\mocks;


use Phalcon\Http\Response;

class ResponseMock extends Response
{
    public $jsonContent;

    public function setJsonContent($content, $jsonOptions = 0, $depth = 512)
    {
        $this->jsonContent = $content;
        return $this;
    }

    public function send()
    {
        return $this;
    }
}