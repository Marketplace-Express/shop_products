<?php
/**
 * User: Wajdi Jurry
 * Date: 29/07/18
 * Time: 10:41 م
 */

namespace app\modules\api\controllers;

use Phalcon\Mvc\Controller;

class ExceptionhandlerController extends Controller
{
    /**
     * @param $errors
     * @param int $code
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function raiseErrorAction($errors, int $code)
    {
        if (!is_array($errors) && !is_object($errors) && ($jsonError = json_decode($errors, true)) != null) {
            $errors = $jsonError;
        }

        /**
         * Log Error
         * @ignore
         */
        $this->di->getLogger()->logError($errors);

        // response->setStatusCode slows down the performance
        // replacing it with http_response_code
        http_response_code($code);
        return $this->response
            ->setJsonContent([
                'status' => $code,
                'message' => $errors
            ]);
    }
}
