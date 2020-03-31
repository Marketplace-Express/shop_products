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
     * @param string $productId
     * @param string $userId
     * @param string|null $imageId
     * @param int $quantity
     * @param string $sku
     * @param float $price
     * @param float $salePrice
     * @param array $attributes
     * @return Variation
     * @throws NotFound
     * @throws OperationFailed
     * @throws \Exception
     */
    public function create(
        string $productId,
        string $userId,
        ?string $imageId,
        int $quantity,
        string $sku,
        float $price,
        ?float $salePrice,
        array $attributes = []
    ): Variation
    {
        $productQuantity = (int) ProductRepository::getInstance()->getColumnsForProduct($productId, ['productQuantity'])['productQuantity'];
        $productVariations = Variation::find([
            'conditions' => 'productId = :productId:',
            'columns' => 'quantity',
            'bind' => ['productId' => $productId]
        ]);

        $variationsQuantities = 0;
        if (count($productVariations)) {
            foreach ($productVariations as $variation) {
                $variationsQuantities += (int) $variation->quantity;
            }
        }

        if ($quantity + $variationsQuantities > $productQuantity) {
            throw new OperationFailed('variation quantity is bigger that product quantity', 400);
        }

        /** @var Variation $variation */
        $variation = Variation::model(true);

        // Start a transaction to guarantee data lost
        $transaction = new TxManager($variation->getDI());

        try {

            $variation->setTransaction($transaction->getOrCreateTransaction());

            $isCreated = $variation->create([
                'productId' => $productId,
                'userId' => $userId,
                'imageId' => $imageId ?: null,
                'quantity' => $quantity,
                'price' => $price,
                'salePrice' => $salePrice,
                'sku' => $sku
            ]);

            if (!$isCreated) {
                throw new OperationFailed($variation->getMessages(), 400);
            }

            $image = ImageRepository::getInstance()->getModel()::findFirst([
                'conditions' => 'imageId = :imageId:',
                'bind' => ['imageId' => $imageId]
            ]);
            if ($image) {
                $image->update(['isVariationImage' => true, 'isUsed' => true]);
            }

            /** @var VariationProperties $properties */
            $properties = VariationProperties::model(true);
            $properties->productId = $productId;
            $properties->variationId = $variation->variationId;
            $properties->attributes = $attributes;
            if (!$properties->create()) {
                $transaction->rollback();
                throw new OperationFailed($properties->getMessages(), 400);
            }

            // Commit the transaction if data saved
            $transaction->commit();
            $variation->properties = $properties;

        } catch (TxFailed $exception) {
            $transaction->rollback();
            throw new OperationFailed($exception->getMessage());
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
        $variation = Variation::findFirst([
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
