<?php
/**
 * User: Wajdi Jurry
 * Date: 17/03/19
 * Time: 11:20 م
 */

namespace app\modules\api\controllers;


use app\common\controllers\BaseController;
use app\common\requestHandler\product\AutocompleteRequestHandler;
use app\common\requestHandler\product\SearchRequestHandler;
use app\common\services\SearchService;

/**
 * Class SearchController
 * @package app\modules\api\controllers
 * @RoutePrefix('/api/1.0/search')
 */
class SearchController extends BaseController
{
    /** @var SearchService */
    private $service;

    /**
     * @return SearchService
     */
    public function getService(): SearchService
    {
        return $this->service ?? $this->service = new SearchService();
    }

    /**
     * @Get('/autocomplete')
     */
    public function autocompleteAction()
    {
        try {
            /** @var AutocompleteRequestHandler $request */
            $request = $this->getJsonMapper()->map(
                $this->request->getQuery(),
                new AutocompleteRequestHandler()
            );

            if (!$request->isValid()) {
                $request->invalidRequest();
            }

            $request->successRequest($this->getService()->autocomplete($request->toArray()));

        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode() ?: 500);
        }
    }

    /**
     * @Get('/')
     */
    public function searchAction()
    {
        try {
            /** @var SearchRequestHandler $request */
            $request = $this->getJsonMapper()->map(
                $this->request->getQuery(),
                new SearchRequestHandler()
            );

            if (!$request->isValid()) {
                $request->invalidRequest();
            }

            $request->successRequest($this->getService()->search($request->toArray()));
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode() ?: 500);
        }
    }

}
