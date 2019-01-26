<?php
/**
 * User: Wajdi Jurry
 * Date: 29/07/18
 * Time: 10:41 م
 */

namespace Shop_products\Controllers;

use Phalcon\Mvc\Controller;
use Shop_products\Logger\ApplicationLogger;

class ExceptionhandlerController extends Controller
{
    /**
     * Defined as protected for unit test
     * Also, it is encapsulated for this class and its children
     *
     * @return ApplicationLogger
     */
    protected function getLogger(): ApplicationLogger
    {
        return new ApplicationLogger();
    }

    /**
     * @param $errors
     * @param int $code
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function raiseErrorAction($errors, $code)
    {
        if (!is_array($errors) && !is_object($errors) && ($jsonError = json_decode($errors, true)) != null) {
            $errors = $jsonError;
        }

        /**
         * Log Error
         * @ignore
         */
        $this->getLogger()->logError($errors);

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
