<?php
/**
 * User: Wajdi Jurry
 * Date: 05/04/19
 * Time: 11:36 م
 */

namespace Shop_products\Utils;

use Mechpave\ImgurClient\Entity\Token;
use Mechpave\ImgurClient\ImgurClient;
use Mechpave\ImgurClient\Model\ImageModel;
use Phalcon\Di;

class ImgurUtil
{
    /** @var ImgurClient */
    private $imagurInstance;

    public function __construct()
    {
        $config = Di::getDefault()->getConfig()->application->imgur;
        $token = new Token();
        $token->setAccessToken($config->accessToken);
        $this->imagurInstance = new ImgurClient($config->apiKey, $config->apiSecret);
        $this->imagurInstance->setToken($token);
        return $this->imagurInstance;
    }

    /**
     * @param string $title
     * @return mixed
     */
    public function createAlbum(string $title)
    {
        return $this->imagurInstance->album()
            ->create([], $title, null, 'secret');
    }

    /**
     * @param string $albumId
     * @return mixed
     */
    public function deleteAlbum(string $albumId)
    {
        return $this->imagurInstance->album()->delete($albumId);
    }

    /**
     * @param $image
     * @param string $name
     * @param string $albumId
     * @return mixed
     */
    public function uploadImage($image, string $name, string $albumId)
    {
        return $this->imagurInstance->image()->upload(
            $image, ImageModel::TYPE_FILE, null, null, $albumId, $name
        );
    }
}