<?php
/**
 * User: Wajdi Jurry
 * Date: 27/07/19
 * Time: 05:50 م
 */

namespace app\common\repositories;


use app\common\exceptions\NotFound;
use app\common\exceptions\OperationFailed;
use app\common\models\ProductRates;

class RateRepository extends BaseRepository
{
    /**
     * @param string $userId
     * @param string $productId
     * @param int $stars
     * @param string|null $text
     * @return ProductRates
     * @throws OperationFailed
     */
    public function create(string $userId, string $productId, int $stars, ?string $text): ProductRates
    {
        $rate = ProductRates::model(true);

        $rate->userId = $userId;
        $rate->rateText = $text;
        $rate->rateStars = $stars;
        $rate->productId = $productId;

        if (!$rate->save()) {
            throw new OperationFailed($rate->getMessages(), 400);
        }

        return $rate;
    }

    /**
     * @param string $rateId
     * @param int $stars
     * @param string|null $text
     * @return ProductRates
     * @throws OperationFailed
     * @throws NotFound
     */
    public function update(string $rateId, int $stars, ?string $text): ProductRates
    {
        $rate = ProductRates::findFirst([
            'conditions' => 'rateId = :rateId:',
            'bind' => ['rateId' => $rateId]
        ]);

        if (!$rate) {
            throw new NotFound();
        }

        $rate->rateStars = $stars;
        $rate->rateText = $text;

        if (!$rate->update()) {
            throw new OperationFailed($rate->getMessages(), 400);
        }

        return $rate;
    }

    /**
     * @param string $rateId
     * @return bool
     * @throws NotFound
     * @throws OperationFailed
     */
    public function delete(string $rateId): bool
    {
        $rate = ProductRates::model()::findFirst([
            'conditions' => 'rateId = :rateId:',
            'bind' => ['rateId' => $rateId]
        ]);

        if (!$rate) {
            throw new NotFound('Rate not found or maybe deleted');
        }

        if (!$rate->delete()) {
            throw new OperationFailed($rate->getMessages(), 400);
        }

        return true;
    }

    /**
     * @param string $productId
     * @return bool
     */
    public function deleteProductRates(string $productId)
    {
        $allDeleted = false;
        $allProductRates = ProductRates::model()::find([
            'conditions' => 'productId = :productId:',
            'bind' => [
                'productId' => $productId
            ]
        ]);

        if (!count($allProductRates)) {
            return false;
        }

        if ($allProductRates) {
            foreach ($allProductRates as $productRate) {
                $allDeleted = $productRate->delete();
            }
        }

        return $allDeleted;
    }
}
