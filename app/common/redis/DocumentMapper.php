<?php
/**
 * User: Wajdi Jurry
 * Date: 23/02/19
 * Time: 06:35 م
 */

namespace Shop_products\Redis;


use Ehann\RediSearch\Document\Document;
use Ehann\RediSearch\Fields\TextField;
use Shop_products\Utils\UuidUtil;

class DocumentMapper extends Document
{
    /** @var null|string */
    protected $id;

    /** @var TextField */
    public $title;

    /** @var TextField */
    public $linkSlug;

    /**
     * DocumentMapper constructor.
     * @param string $id
     * @throws \Exception
     */
    public function __construct(string $id)
    {
        if (empty($id) || !(new UuidUtil())->isValid($id)) {
            throw new \Exception('Invalid document id');
        }
        $this->id = $id;
        parent::__construct($id);
    }

    /**
     * @param string $title
     * @param string|null $linkSlug
     * @return $this
     */
    public function makeDocument(string $title, ?string $linkSlug)
    {
        $this->title = new TextField('title', $title);
        $this->linkSlug = new TextField('link_slug', $linkSlug);
        return $this;
    }
}