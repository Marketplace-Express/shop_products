<?php
/**
 * User: Wajdi Jurry
 * Date: 27/07/19
 * Time: 02:00 م
 */

namespace app\common\requestHandler\image;


use app\common\controllers\BaseController;
use app\common\exceptions\ArrayOfStringsException;
use app\common\requestHandler\RequestHandlerInterface;
use app\common\services\user\UserService;
use app\common\validators\UuidValidator;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;

class DeleteRequestHandler extends BaseController implements RequestHandlerInterface
{
    /**
     * @var string
     */
    private $albumId;

    /**
     * @var string
     */
    private $productId;

    /**
     * @var array
     */
    private $errorMessages = [];

    public function setAlbumId($albumId)
    {
        $this->albumId = $albumId;
    }

    public function getAlbumId()
    {
        return $this->albumId;
    }

    public function setProductId($productId)
    {
        $this->productId = $productId;
    }

    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @return UserService
     */
    private function getUserService(): UserService
    {
        return $this->getDI()->getUserService();
    }

    /**
     * @return int
     */
    public function getAccessLevel(): int
    {
        return $this->getUserService()->accessLevel;
    }

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();

        $validator->add(
            'albumId',
            new Validation\Validator\Regex([
                'pattern' => '/([a-z0-9]){7}/i',
                'allowEmpty' => false,
                'message' => 'Invalid album Id'
            ])
        );

        $validator->add(
            'productId',
            new UuidValidator()
        );

        return $validator->validate([
            'albumId' => $this->getAlbumId(),
            'productId' => $this->getProductId()
        ]);
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $messages = $this->validate();
        if (count($messages)) {
            foreach ($messages as $message) {
                $this->errorMessages[$message->getField()] = $message->getMessage();
            }
            return false;
        }
        return true;
    }

    public function notFound($message = 'Not Found')
    {
        // TODO: Implement notFound() method.
    }

    /**
     * @param null $message
     * @throws ArrayOfStringsException
     */
    public function invalidRequest($message = null)
    {
        throw new ArrayOfStringsException($this->errorMessages, 400);
    }

    public function successRequest($message = null)
    {
        http_response_code(204);
    }

    public function toArray(): array
    {
        // TODO: Implement toArray() method.
    }
}