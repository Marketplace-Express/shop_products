<?php
/**
 * User: Wajdi Jurry
 * Date: 17/03/19
 * Time: 11:20 م
 */

namespace Shop_products\Modules\Api\Controllers;


use Shop_products\Controllers\BaseController;
use Shop_products\RequestHandler\Product\AutocompleteRequestHandler;
use Shop_products\RequestHandler\Product\SearchRequestHandler;
use Shop_products\Services\SearchService;

/**
 * Class SearchController
 * @package Shop_products\Modules\Api\Controllers
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
            $request = $this->getJsonMapper()->map($this->queryStringToObject($this->request->getQuery()), new AutocompleteRequestHandler());

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
            $request = $this->getJsonMapper()->map($this->queryStringToObject($this->request->getQuery()), new SearchRequestHandler());

            if (!$request->isValid()) {
                $request->invalidRequest();
            }

            $request->successRequest($this->getService()->search($request->toArray()));
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode() ?: 500);
        }
    }

}