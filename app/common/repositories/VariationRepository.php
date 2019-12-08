<?php
/**
 * User: Wajdi Jurry
 * Date: ٧‏/١٢‏/٢٠١٩
 * Time: ٧:٠٣ م
 */

namespace app\common\repositories;


use app\common\exceptions\NotFound;
use app\common\exceptions\OperationFailed;
use app\common\models\embedded\Variation;
use app\common\models\embedded\VariationProperties;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;

class VariationRepository extends BaseRepository
{
    /**
     * @return Variation
     */
    public function getModel(): Variation
    {
        return Variation::model(true);
    }

    /**
     * @return VariationProperties
     */
    public function getPropertiesModel(): VariationProperties
    {
        return VariationProperties::model(true);
    }

    /**
     * @param string $productId
     * @param string $userId
     * @param string|null $imageId
     * @param int $quantity
     * @param float $price
     * @param float $salePrice
     * @param array $attributes
     * @return Variation
     * @throws OperationFailed
     * @throws \app\common\exceptions\NotFound
     */
    public function create(
        string $productId,
        string $userId,
        ?string $imageId,
        int $quantity,
        float $price,
        ?float $salePrice,
        array $attributes = []
    ): Variation
    {
        $productQuantity = (int) ProductRepository::getInstance()->getColumnsForProduct($productId, ['productQuantity'])['productQuantity'];
        if ($quantity > $productQuantity) {
            throw new OperationFailed('variation quantity is bigger that product quantity', 400);
        }

        $variation = $this->getModel();
        if ($attributes) {
            // Start a transaction to guarantee data lost
            $transaction = new TxManager();
            try {
                $transaction->setDI($variation->getDI());
                $variation->setTransaction($transaction->getOrCreateTransaction());
                $isCreated = $variation->create([
                    'productId' => $productId,
                    'userId' => $userId,
                    'imageId' => $imageId,
                    'quantity' => $quantity,
                    'price' => $price,
                    'salePrice' => $salePrice
                ]);

                if (!$isCreated) {
                    $transaction->rollback();
                    throw new OperationFailed($variation->getMessages());
                }

                $properties = $this->getPropertiesModel();
                $properties->productId = $productId;
                $properties->variationId = $variation->variationId;
                $properties->attributes = $attributes;
                if (!$properties->create()) {
                    $transaction->rollback();
                    throw new OperationFailed($properties->getMessages());
                }

                // Commit the transaction if data saved
                $transaction->commit();
                $variation->properties = $properties;

            } catch (TxFailed $exception) {
                $transaction->rollback();
                throw new OperationFailed($exception->getMessage());
            }
        } else {
            $isCreated = $variation->create([
                'productId' => $productId,
                'userId' => $userId,
                'imageId' => $imageId,
                'quantity' => $quantity,
                'price' => $price,
                'salePrice' => $salePrice
            ]);

            if (!$isCreated) {
                throw new OperationFailed($variation->getMessages());
            }
        }

        return $variation;
    }

    /**
     * @param string $id
     * @return bool
     * @throws OperationFailed
     * @throws NotFound
     */
    public function deleteVariation(string $id): bool
    {
        /** @var Variation $variation */
        $variation = $this->getModel()::findFirst([
            'conditions' => 'variationId = :variationId:',
            'bind' => ['variationId' => $id]
        ]);

        if (!$variation) {
            throw new NotFound('variation not found');
        }

        if (!$variation->delete()) {
            throw new OperationFailed('variation cannot be deleted');
        }

        return true;
    }
}
