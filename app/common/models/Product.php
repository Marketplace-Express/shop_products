<?php

namespace Shop_products\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\ResultSetInterface;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Validation;
use Shop_products\Enums\ProductTypesEnums;
use Shop_products\Validators\TypeValidator;
use Shop_products\Validators\UuidValidator;

/**
 * Product
 * 
 * @package Shop_products\Models
 * @autogenerated by Phalcon Developer Tools
 * @date 2019-01-11, 16:38:52
 * @property-read Resultset\Complex $productImages
 * @property-read Resultset\Complex $productQuestions
 * @property-read Resultset\Complex $productRates
 * @property-write PhysicalProduct $physicalProperties
 * @property-write DownloadableProduct $downloadableProperties
 */
class Product extends BaseModel
{
    const WHITE_LIST = [
        'productId',
        'productCategoryId',
        'productUserId',
        'productVendorId',
        'productTitle',
        'productLinkSlug',
        'productType',
        'productCustomPageId',
        'productAlbumId',
        'productAlbumDeleteHash',
        'productPrice',
        'productSalePrice',
        'productSaleEndTime',
        'isPublished'
    ];

    /**
     * @var string
     * @Primary
     * @Column(column='product_id', type='string', length=37)
     */
    public $productId;

    /**
     * @var string
     * @Column(column='product_category_id', type='string', length=36)
     */
    public $productCategoryId;

    /**
     * @var string
     * @Column(column='product_user_id', type='string', length=36)
     */
    public $productUserId;

    /**
     * @var string
     * @Column(column='product_vendor_id', type='string', length=36)
     */
    public $productVendorId;

    /**
     * @var string
     * @Column(column='product_title', type='text')
     */
    public $productTitle;

    /**
     * @var string
     * @Column(column='product_type', type='string')
     */
    public $productType;

    /**
     * @var string
     * @Column(column='product_link_slug', type='text')
     */
    public $productLinkSlug;

    /**
     * @var string
     * @Column(column='product_custom_page_id', type='string', length=36, nullable=true)
     */
    public $productCustomPageId;

    /**
     * @var string
     * @Column(column='product_album_id', type='string', length=10)
     */
    public $productAlbumId;

    /**
     * @var string
     * @Column(column='product_album_delete_hash', type='string', length=20)
     */
    public $productAlbumDeleteHash;

    /**
     * @var float
     * @Column(column='product_price', type='float')
     */
    public $productPrice;

    /**
     * @var float
     * @Column(column='product_sale_price', type='float', nullable=true)
     */
    public $productSalePrice;

    /**
     * @var string
     * @Column(column='product_sale_end_time', type='datetime', nullable=true)
     */
    public $productSaleEndTime;

    /**
     * @var string
     * @Column(column='created_at', type='datetime')
     */
    public $createdAt;

    /**
     * @var string
     * @Column(column='updated_at', type='datetime', nullable=true)
     */
    public $updatedAt;

    /**
     * @var string
     * @Column(column='deleted_at', type='string', nullable=true)
     */
    public $deletedAt;

    /**
     * @var bool
     * @Column(column='is_published', type='boolean', default=0)
     */
    public $isPublished = false;

    /**
     * @var array
     * This value appended from Mongo Collection
     */
    private $productKeywords;

    /**
     * @var array
     * This value appended from Mongo Collection
     */
    private $productSegments;

    public $exposedFields = [];

    private static $attachRelations = true;
    private static $editMode = false;

    /**
     *
     * @var integer
     * @Column(column='is_deleted', type='boolean', nullable=false, default=0)
     */
    public $isDeleted;

    public static function model(bool $new = false, bool $attachRelations = true, bool $editMode = false)
    {
        self::$attachRelations = $attachRelations;
        self::$editMode = $editMode;
        return parent::model($new);
    }

    /**
     * Initialize method for model.
     * @throws \Exception
     */
    public function initialize()
    {
        $this->setSchema("shop_products");
        $this->setSource("product");
        $this->defaultBehavior();

        $this->useDynamicUpdate(true);

        $this->hasOne(
            'productId',
            PhysicalProduct::class,
            'productId',
            [
                'alias' => 'physicalProperties'
            ]
        );

        $this->hasOne(
            'productId',
            DownloadableProduct::class,
            'productId',
            [
                'alias' => 'downloadableProperties'
            ]
        );

        if (self::$attachRelations) {
            $this->hasMany('productId', ProductImages::class, 'productId', ['alias' => 'productImages']);
            $this->hasMany('productId', ProductQuestions::class, 'productId', ['alias' => 'productQuestions']);
            $this->hasMany('productId', ProductRates::class, 'productId', ['alias' => 'productRates']);
        }
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'product';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Product[]|ResultSetInterface|Resultset\Simple
     */
    public static function find($parameters = null)
    {
        $operator = '';
        if (!empty($parameters['conditions'])) {
            $operator = ' AND ';
        }
        $parameters['conditions'] .= $operator.'isDeleted = false';
        $parameters['hydration'] = Resultset::HYDRATE_RECORDS;
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ModelInterface|Product
     */
    public static function findFirst($parameters = null)
    {
        $query = self::find($parameters);
        return $query->getFirst();
    }

    /**
     * @return array
     */
    public static function getWhiteList()
    {
        return self::WHITE_LIST;
    }

    /**
     * @param array $data
     * @param null $dataColumnMap
     * @param null|array $whiteList
     * @return $this|Model
     */
    public function assign(array $data, $dataColumnMap = null, $whiteList = null)
    {
        foreach ($data as $attribute => $value) {
            if (!empty($whiteList) && !in_array($attribute, $whiteList)) {
                continue;
            }
            $this->writeAttribute($attribute, $value);
            $this->exposedFields[$attribute] = $value;
        }
        return $this;
    }

    /**
     * Fetch related data
     */
    public function afterFetch()
    {
        if (!self::$attachRelations) {
            return;
        }

        $images = $questions = $rates = [];

        $this->productImages->filter(function ($image) use (&$images) {
            /** @var ProductImages $image */
            $images[] = $image->toApiArray();
        });

        $this->productQuestions->filter(function ($question) use (&$questions) {
            /** @var ProductQuestions $question */
            $questions[] = $question->toApiArray();
        });

        $this->productRates->filter(function ($rate) use (&$rates) {
            /** @var ProductRates $rate */
            $rates[] = $rate->toApiArray();
        });

        $this->productImages = $images;
        $this->productQuestions = $questions;
        $this->productRates = $rates;

        if (self::$editMode) {
            $this->exposedFields['isPublished'] = (bool) $this->isPublished;
        }
    }

    public function afterCreate()
    {
        $this->exposedFields['isPublished'] = $this->isPublished;
    }

    /**
     * Independent Column Mapping.
     * Keys are the real names in the table and the values their names in the application
     *
     * @return array
     */
    public function columnMap()
    {
        return [
            'product_id' => 'productId',
            'product_category_id' => 'productCategoryId',
            'product_user_id' => 'productUserId',
            'product_vendor_id' => 'productVendorId',
            'product_title' => 'productTitle',
            'product_link_slug' => 'productLinkSlug',
            'product_type' => 'productType',
            'product_custom_page_id' => 'productCustomPageId',
            'product_album_id' => 'productAlbumId',
            'product_album_delete_hash' => 'productAlbumDeleteHash',
            'product_price' => 'productPrice',
            'product_sale_price' => 'productSalePrice',
            'product_sale_end_time' => 'productSaleEndTime',
            'created_at' => 'createdAt',
            'updated_at' => 'updatedAt',
            'deleted_at' => 'deletedAt',
            'is_published' => 'isPublished',
            'is_deleted' => 'isDeleted'
        ];
    }

    public function toApiArray()
    {
        return array_merge(
            [
                'productId' => $this->productId,
                'productCategoryId' => $this->productCategoryId,
                'productVendorId' => $this->productVendorId,
                'productTitle' => $this->productTitle,
                'productType' => $this->productType,
                'productLinkSlug' => $this->productLinkSlug,
                'productCustomPageId' => $this->productCustomPageId,
                'productPrice' => (float) $this->productPrice,
                'productSalePrice' => (float) $this->productSalePrice,
                'productSaleEndTime' => $this->productSaleEndTime,
                'productAlbumId' => $this->productAlbumId,
                'productKeywords' => $this->productKeywords ?? null,
                'productSegments' => $this->productSegments ?? null
            ],
            (self::$attachRelations) ? [
                'productImages' => $this->productImages,
                'productQuestions' => $this->productQuestions,
                'productRates' => $this->productRates
            ] : [],
            $this->exposedFields
        );
    }

    private function getTitleValidationConfig()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->getDI()->getConfig()->application->validation->productTitle;
    }

    /**
     * @return bool
     */
    public function validation()
    {
        $validation = new Validation();

        $validation->add(
            ['productCategoryId', 'productVendorId', 'productUserId'],
            new UuidValidator()
        );

        $validation->add(
            'productCustomPageId',
            new UuidValidator([
                'allowEmpty' => true
            ])
        );

        // Validate English input
        $validation->add(
            'productTitle',
            new Validation\Validator\Callback([
                'callback' => function ($data) {
                    $name = preg_replace('/[\d\s_]/i', '', $data['productTitle']); // clean string
                    if (preg_match('/[a-z]/i', $name) == false) {
                        return false;
                    }
                    return true;
                },
                'message' => 'English language only supported'
            ])
        );

        /** @noinspection PhpUndefinedFieldInspection */
        $validation->add(
            'productTitle',
            new Validation\Validator\AlphaNumericValidator([
                'whiteSpace' => $this->getTitleValidationConfig()->whiteSpace,
                'underscore' => $this->getTitleValidationConfig()->underscore,
                'min' => $this->getTitleValidationConfig()->min,
                'max' => $this->getTitleValidationConfig()->max,
                'message' => 'Product title should contain only letters'
            ])
        );

        if ($this->_operationMade == self::OP_CREATE) {
            $validation->add(
                'productType',
                new Validation\Validator\InclusionIn([
                    'domain' => ProductTypesEnums::getValues(),
                    'allowEmpty' => true,
                    'message' => 'Product type should be physical or downloadable'
                ])
            );
        }

        $validation->add(
            'productPrice',
            new TypeValidator([
                'type' => TypeValidator::TYPE_FLOAT,
                'allowEmpty' => $this->_operationMade == self::OP_CREATE ? false : true
            ])
        );

        $validation->add(
            'productSalePrice',
            new TypeValidator([
                'type' => TypeValidator::TYPE_FLOAT
            ])
        );

        $validation->add(
            'productPrice',
            new Validation\Validator\NumericValidator([
                'allowFloat' => true,
                'min' => 0
            ])
        );

        $validation->add(
            'productSalePrice',
            new Validation\Validator\NumericValidator([
                'allowFloat' => true,
                'min' => 0,
                'allowEmpty' => true
            ])
        );

        $validation->add(
            'productSaleEndTime',
            new Validation\Validator\Date([
                'format' => 'Y-m-d H:i:s',
                'allowEmpty' => true
            ])
        );

        $message = $validation->validate([
            'productCategoryId' => $this->productCategoryId,
            'productVendorId' => $this->productVendorId,
            'productUserId' => $this->productUserId,
            'productCustomPageId' => $this->productCustomPageId,
            'productTitle' => $this->productTitle,
            'productPrice' => $this->productPrice,
            'productSalePrice' => $this->productSalePrice,
            'productSaleEndTime' => $this->productSaleEndTime
        ]);

        $this->_errorMessages = $message;

        return !$message->count();
    }
}
