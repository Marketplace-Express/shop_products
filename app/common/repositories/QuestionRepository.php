<?php
/**
 * User: Wajdi Jurry
 * Date: 27/07/19
 * Time: 05:45 م
 */

namespace app\common\repositories;


use app\common\exceptions\NotFound;
use app\common\exceptions\OperationFailed;
use app\common\models\ProductQuestions;

class QuestionRepository
{
    /**
     * @return ProductQuestions
     */
    public function getModel(): ProductQuestions
    {
        return new ProductQuestions();
    }

    /**
     * @return QuestionRepository
     */
    static public function getInstance(): QuestionRepository
    {
        return new self;
    }

    /**
     * @param string $userId
     * @param string $productId
     * @param string $text
     * @return array
     * @throws OperationFailed
     */
    public function create(string $userId, string $productId, string $text): array
    {
        $data = [
            'userId' => $userId,
            'productId' => $productId,
            'text' => $text
        ];
        $question = $this->getModel();
        if (!$question->create($data, ProductQuestions::WHITE_LIST)) {
            throw new OperationFailed($question->getMessages());
        }

        return $question->toApiArray();
    }

    /**
     * @param string $id
     * @param string $text
     * @return array
     * @throws NotFound
     * @throws OperationFailed
     */
    public function update(string $id, string $text): array
    {
        $question = $this->getModel()::findFirst([
            'conditions' => 'id = :id:',
            'bind' => ['id' => $id]
        ]);
        if (!$question) {
            throw new NotFound();
        }
        $updated = $question->update(['text' => $text], ProductQuestions::WHITE_LIST);
        if (!$updated) {
            throw new OperationFailed($question->getMessages());
        }
        return $question->toApiArray();
    }

    /**
     * @param string $id
     * @return array
     * @throws NotFound
     * @throws OperationFailed
     */
    public function delete(string $id)
    {
        $question = $this->getModel()::findFirst([
            'conditions' => 'id = :id:',
            'bind' => ['id' => $id]
        ]);
        if (!$question) {
            throw new NotFound();
        }
        if (!$question->delete()) {
            throw new OperationFailed('question could not be deleted');
        }
        return $question->toApiArray();
    }

    /**
     * @param string $id
     * @return array
     * @throws NotFound
     */
    public function getById(string $id)
    {
        $question = $this->getModel()::findFirst([
            'conditions' => 'id = :id:',
            'bind' => ['id' => $id]
        ]);
        if (!$question) {
            throw new NotFound();
        }
        return $question->toApiArray();
    }

    /**
     * @param string $productId
     * @return array
     */
    public function getAll(string $productId)
    {
        $result = [];
        $questions = $this->getModel()::find([
            'conditions' => 'productId = :productId:',
            'bind' => ['productId' => $productId]
        ]);
        foreach ($questions as $question) {
            $result[] = $question->toApiArray();
        }
        return $result;
    }

    /**
     * @param string $productId
     * @return bool
     */
    public function deleteProductQuestions(string $productId)
    {
        $allProductQuestions = $this->getModel()::find([
            'conditions' => 'productId = :productId:',
            'bind' => [
                'productId' => $productId
            ]
        ]);

        if ($allProductQuestions) {
            foreach ($allProductQuestions as $productQuestion) {
                $productQuestion->delete();
            }
            return true;
        }
        return false;
    }
}
