<?php
/**
 * User: Wajdi Jurry
 * Date: ٢‏/٤‏/٢٠٢٠
 * Time: ١١:٠٧ ص
 */

namespace app\common\repositories;


use app\common\exceptions\OperationFailed;
use app\common\exceptions\OperationNotPermitted;
use app\common\models\RateImage;
use app\common\validators\rules\RateRules;
use Phalcon\Db\RawValue;

class RateImagesRepository extends BaseRepository
{
    /**
     * @param string $rateId
     * @param array $imagesIds
     * @return RateImage[]
     * @throws OperationFailed
     * @throws OperationNotPermitted
     */
    public function saveImages(string $rateId, array $imagesIds = [])
    {
        // Validate number of images
        $imagesCount = RateImage::count([
            'conditions' => 'rateId = :rateId:',
            'bind' => ['rateId' => $rateId]
        ]);

        if ($imagesCount > (new RateRules())->maxNumOfImages) {
            throw new OperationNotPermitted('Number of images exceeded');
        }

        $result = [];
        foreach ($imagesIds as $imageId) {
            $rateImage = new RateImage([
                'imageId' => $imageId,
                'rateId' => $rateId
            ]);

            if (!$rateImage->save()) {
                throw new OperationFailed($rateImage->getMessages());
            }

            $result[] = $rateImage;
        }

        return $result;
    }

    public function deleteImages(string $rateId)
    {
        $images = RateImage::find([
            'conditions' => 'rateId = :rateId:',
            'bind' => ['rateId' => $rateId]
        ]);

        foreach ($images as $image) {
            if (!$image->delete()) {
                throw new OperationFailed('Could not delete rate image');
            }
        }
    }
}