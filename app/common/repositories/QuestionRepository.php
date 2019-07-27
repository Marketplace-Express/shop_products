<?php
/**
 * User: Wajdi Jurry
 * Date: 27/07/19
 * Time: 05:45 م
 */

namespace app\common\repositories;


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