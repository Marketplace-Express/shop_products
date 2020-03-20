<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 07:32 م
 */

namespace app\modules\api\controllers;


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
    /**
     * @Post('/')
     * @AuthMiddleware("\app\common\events\middleware\RequestMiddlewareEvent")
     * @param CreateRequestHandler $request
     */
    public function createAction(CreateRequestHandler $request)
    {
        try {
            /** @var CreateRequestHandler $request */
            $request = $this->di->get('jsonMapper')->map($this->request->getJsonRawBody(), $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $question = $this->di->getAppServices('questionService')->create($request->productId, $request->text);
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
            $request = $this->di->get('jsonMapper')->map($this->request->getJsonRawBody(), $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $question = $this->di->getAppServices('questionService')->update($id, $request->text);
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
            $request = $this->di->get('jsonMapper')->map(['id' => $id], $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $this->di->getAppServices('questionService')->delete($id);
            $request->successRequest('Deleted');
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param $id
     * @param GetForProductRequestHandler $request
     * @Get('/product/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}')
     */
    public function getAllAction($id, GetForProductRequestHandler $request)
    {
        try {
            /** @var GetForProductRequestHandler $request */
            $request = $this->di->get('jsonMapper')->map(['id' => $id], $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $questions = $this->di->getAppServices('questionService')->getAll($id);
            $request->successRequest($questions);
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
            $request = $this->di->get('jsonMapper')->map(['id' => $id], $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $question = $this->di->getAppServices('questionService')->getById($id);
            $request->successRequest($question);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }
}
