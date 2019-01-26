<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 08:41 م
 */

namespace Shop_products\Controllers;


class NotfoundController extends BaseController
{
    public function indexAction()
    {
        http_response_code(404);
        return $this->response
            ->setJsonContent([
                'status' => 404,
                'message' => 'API not found'
            ]);
    }
}