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
     * @param float|null $salePrice
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
        if (!$this->isValidQuantity($productId, $quantity)) {
            throw new \Exception('variation quantity is bigger that product quantity');
        }

        if ($imageId) {
            // Throw NotFound exception if image does not exist
            ImageRepository::getInstance()->getUnused($imageId);
        }

        $variation = Variation::model(true);

        // Start a transaction to guarantee data lost
        $txManager = new TxManager($variation->getDI());

        try {

            $variation->setTransaction($txManager->getOrCreateTransaction());

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

            if ($imageId) {
                $image = ImageRepository::getInstance()->getModel()::findFirst([
                    'conditions' => 'imageId = :imageId:',
                    'bind' => ['imageId' => $imageId]
                ]);

                if ($image) {
                    $image->update(['isUsed' => true]);
                }
            }

            /** @var VariationProperties $properties */
            $properties = VariationProperties::model(true);
            $properties->productId = $productId;
            $properties->variationId = $variation->variationId;
            $properties->attributes = $attributes;
            if (!$properties->create()) {
                $txManager->rollback();
                throw new OperationFailed($properties->getMessages(), 400);
            }

            // Commit the transaction if data saved
            $txManager->commit();
            $variation->properties = $properties;

        } catch (TxFailed $exception) {
            $txManager->rollback();
            throw new OperationFailed($exception->getMessage());
        }

        return $variation;
    }

    /**
     * @param string $variationId
     * @param string|null $imageId
     * @param int $quantity
     * @param string $sku
     * @param float $price
     * @param float|null $salePrice
     * @param array $attributes
     * @return Variation|\Phalcon\Mvc\Model
     * @throws NotFound
     * @throws OperationFailed
     */
    public function update(
        string $variationId,
        ?string $imageId,
        int $quantity,
        string $sku,
        float $price,
        ?float $salePrice,
        array $attributes = []
    ) {
        $variation = Variation::model()::findFirst([
            'conditions' => 'variationId = :variationId:',
            'bind' => ['variationId' => $variationId],
            'for_update' => true
        ]);

        if (!$variation) {
            throw new NotFound('variation not found or maybe deleted');
        }

        if (!$this->isValidQuantity($variation->productId, abs($variation->quantity - $quantity))) {
            throw new \Exception('variation quantity is bigger that product quantity');
        }

        if (!empty($imageId) && $imageId !== $variation->imageId) {
            // Throw NotFound exception if image does not exist
            ImageRepository::getInstance()->getUnused($imageId);
        }

        $txManager = new TxManager($variation->getDI());

        try {
            $variation->setTransaction($txManager->getOrCreateTransaction());
            $isVariationUpdated = $variation->update([
                'quantity' => $quantity,
                'sku' => $sku,
                'price' => $price,
                'salePrice' => $salePrice,
                'imageId' => $imageId
            ]);

            if (!$isVariationUpdated) {
                $txManager->rollback();
                throw new OperationFailed($variation->getMessages());
            }

            $variation->properties->attributes = $attributes;
            $isPropertiesUpdated = $variation->properties->save();

            if (!$isPropertiesUpdated) {
                $txManager->rollback();
            }

            $txManager->commit();

        } catch (TxFailed $exception) {
            $txManager->rollback();
            throw new OperationFailed($exception->getRecordMessages());
        }

        return $variation;
    }

    /**
     * @param string $productId
     * @param int $quantity
     * @return bool
     */
    public function isValidQuantity(string $productId, int $quantity = 0): bool
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

        return ($quantity + $variationsQuantities) <= $productQuantity;
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
