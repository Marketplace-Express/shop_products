<?php
/**
 * User: Wajdi Jurry
 * Date: ١١‏/٤‏/٢٠٢٠
 * Time: ٧:٠٥ م
 */

namespace app\common\services;


use app\common\repositories\ImageRepository;
use app\common\repositories\RateImagesRepository;
use app\common\repositories\RateRepository;

class RateService
{
    /**
     * @param string $userId
     * @param string $productId
     * @param int $stars
     * @param string|null $text
     * @param array $imagesIds
     * @return array
     * @throws \app\common\exceptions\OperationFailed
     * @throws \app\common\exceptions\OperationNotPermitted
     */
    public function create(string $userId, string $productId, int $stars, ?string $text, array $imagesIds = [])
    {
        // Create rate
        $rate = RateRepository::getInstance()->create($userId, $productId, $stars, $text);

        try {
            // Create rate images
            RateImagesRepository::getInstance()->saveImages($rate->rateId, $imagesIds);
        } catch (\Throwable $exception) {
            // failed to create image
            $x = $exception;
        }

        return $rate->toApiArray();
    }

    /**
     * @param string $rateId
     * @param int $stars
     * @param string|null $text
     * @param array $imagesIds
     * @param array $deletedImagesIds
     * @return array
     * @throws \app\common\exceptions\NotFound
     * @throws \app\common\exceptions\OperationFailed
     * @throws \app\common\exceptions\OperationNotPermitted
     */
    public function update(string $rateId, int $stars, ?string $text, array $imagesIds = [], array $deletedImagesIds = [])
    {
        $rate = RateRepository::getInstance()->update($rateId, $stars, $text);

        if (!empty($deletedImagesIds)) {
            RateImagesRepository::getInstance()->deleteImages($rateId);
            ImageRepository::getInstance()->deleteImagesByIds($deletedImagesIds);
        }

        if (!empty($imagesIds)) {
            RateImagesRepository::getInstance()->saveImages($rateId, $imagesIds);
        }

        return $rate->toApiArray();
    }

    /**
     * @param string $rateId
     * @throws \app\common\exceptions\NotFound
     * @throws \app\common\exceptions\OperationFailed
     */
    public function delete(string $rateId)
    {
        RateRepository::getInstance()->delete($rateId);
    }
}