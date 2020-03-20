<?php
/**
 * User: Wajdi Jurry
 * Date: 17/03/19
 * Time: 11:20 م
 */

namespace app\modules\api\controllers;


use app\common\requestHandler\product\{
    AutocompleteRequestHandler,
    SearchRequestHandler
};

/**
 * Class SearchController
 * @package app\modules\api\controllers
 * @RoutePrefix('/api/search')
 */
class SearchController extends BaseController
{
    /**
     * @Get('/autocomplete')
     * @param AutocompleteRequestHandler $request
     */
    public function autocompleteAction(AutocompleteRequestHandler $request)
    {
        try {
            /** @var AutocompleteRequestHandler $request */
            $request = $this->di->get('jsonMapper')->map($this->request->getQuery(), $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->di->getAppServices('searchService')->autocomplete($request->toArray()));
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @Get('/')
     * @param SearchRequestHandler $request
     */
    public function searchAction(SearchRequestHandler $request)
    {
        try {
            /** @var SearchRequestHandler $request */
            $request = $this->di->get('jsonMapper')->map($this->request->getQuery(), $request);

            if (!$request->isValid()) {
                $request->invalidRequest();
            }

            $request->successRequest($this->di->getAppServices('searchService')->search($request->toArray()));
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

}
