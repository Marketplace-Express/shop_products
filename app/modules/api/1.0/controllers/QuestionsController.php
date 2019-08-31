<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 07:32 م
 */

namespace app\modules\api\controllers;


use app\common\controllers\BaseController;
use app\common\requestHandler\question\CreateRequestHandler;
use app\common\requestHandler\question\DeleteRequestHandler;
use app\common\requestHandler\question\GetByIdRequestHandler;
use app\common\requestHandler\question\GetForProductRequestHandler;
use app\common\requestHandler\question\UpdateRequestHandler;
use app\common\services\QuestionsService;

/**
 * Class QuestionsController
 * @package app\modules\api\controllers
 * @RoutePrefix('/api/1.0/questions')
 */
class QuestionsController extends BaseController
{
    /**
     * @return QuestionsService
     */
    public function getService(): QuestionsService
    {
        return new QuestionsService();
    }

    /**
     * @Post('/')
     * @AuthMiddleware("\app\common\events\middleware\RequestMiddlewareEvent")
     */
    public function createAction()
    {
        try {
            /** @var CreateRequestHandler $request */
            $request = $this->getJsonMapper()->map($this->request->getJsonRawBody(), new CreateRequestHandler($this));
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $question = $this->getService()->create($request->productId, $request->text);
            $request->successRequest($question);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param $id
     * @Put("/{id}")
     * @AuthMiddleware("\app\common\events\middleware\RequestMiddlewareEvent")
     */
    public function updateAction($id)
    {
        try {
            /** @var UpdateRequestHandler $request */
            $request = $this->getJsonMapper()->map($this->request->getJsonRawBody(), new UpdateRequestHandler($this, $id));
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $question = $this->getService()->update($request->id, $request->text);
            $request->successRequest($question);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param $id
     * @Delete('/{id}')
     * @AuthMiddleware("\app\common\events\middleware\RequestMiddlewareEvent")
     */
    public function deleteAction($id)
    {
        try {
            /** @var DeleteRequestHandler $request */
            $request = $this->getJsonMapper()->map(new \stdClass(), new DeleteRequestHandler($this, $id));
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $this->getService()->delete($id);
            $request->successRequest(['deleted' => true]);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param $productId
     * @Get('/product/{productId}')
     */
    public function getAllAction($productId)
    {
        try {
            /** @var GetForProductRequestHandler $request */
            $request = $this->getJsonMapper()->map(new \stdClass(), new GetForProductRequestHandler($this, $productId));
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $questions = $this->getService()->getAll($productId);
            $request->successRequest($questions);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param $id
     * @Get('/{id}')
     */
    public function getAction($id)
    {
        try {
            /** @var GetByIdRequestHandler $request */
            $request = $this->getJsonMapper()->map(new \stdClass(), new GetByIdRequestHandler($this, $id));
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $question = $this->getService()->getById($id);
            $request->successRequest($question);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }
}
