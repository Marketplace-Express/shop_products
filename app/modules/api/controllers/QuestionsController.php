<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 07:32 م
 */

namespace app\modules\api\controllers;


use app\common\services\QuestionsService;
use app\common\requestHandler\question\{
    CreateRequestHandler,
    DeleteRequestHandler,
    GetByIdRequestHandler,
    GetForProductRequestHandler,
    UpdateRequestHandler
};

/**
 * Class QuestionsController
 * @package app\modules\api\controllers
 * @RoutePrefix('/api/questions')
 */
class QuestionsController extends BaseController
{
    /** @var QuestionsService */
    private $service;

    /** @var \JsonMapper */
    private $mapper;

    /**
     * @param QuestionsService $service
     */
    public function setService(QuestionsService $service)
    {
        $this->service = $service;
    }

    /**
     * @param \JsonMapper $mapper
     */
    public function setMapper(\JsonMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function initialize()
    {
        $this->setService($this->di->getAppServices('questionService'));
        $this->setMapper($this->di->get('jsonMapper'));
    }

    /**
     * @Post('/')
     * @AuthMiddleware("\app\common\events\middleware\RequestMiddlewareEvent")
     * @param CreateRequestHandler $request
     */
    public function createAction(CreateRequestHandler $request)
    {
        try {
            /** @var CreateRequestHandler $request */
            $request = $this->mapper->map($this->request->getJsonRawBody(), $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $question = $this->service->create($request->productId, $request->text);
            $request->successRequest($question);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param $id
     * @param UpdateRequestHandler $request
     * @Put("/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}")
     * @AuthMiddleware("\app\common\events\middleware\RequestMiddlewareEvent")
     */
    public function updateAction($id, UpdateRequestHandler $request)
    {
        try {
            /** @var UpdateRequestHandler $request */
            $request = $this->mapper->map($this->request->getJsonRawBody(), $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $question = $this->service->update($id, $request->text);
            $request->successRequest($question);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param $id
     * @param DeleteRequestHandler $request
     * @Delete('/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}')
     * @AuthMiddleware("\app\common\events\middleware\RequestMiddlewareEvent")
     */
    public function deleteAction($id, DeleteRequestHandler $request)
    {
        try {
            /** @var DeleteRequestHandler $request */
            $request = $this->mapper->map(['id' => $id], $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $this->service->delete($id);
            $request->successRequest('Deleted');
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param $productId
     * @Get('/product/{productId:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}')
     */
    public function getAllAction($productId)
    {
        try {
            $questions = $this->service->getAll($productId);
            $this->response->setJsonContent([
                'status' => 200,
                'message' => $this->service->getAll($productId)
            ]);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param $id
     * @param GetByIdRequestHandler $request
     * @Get('/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}')
     */
    public function getAction($id, GetByIdRequestHandler $request)
    {
        try {
            /** @var GetByIdRequestHandler $request */
            $request = $this->mapper->map(['id' => $id], $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $question = $this->service->getById($id);
            $request->successRequest($question);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }
}
