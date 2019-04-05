<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class ProductMigration_100
 */
class ProductMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('product', [
                'columns' => [
                    new Column(
                        'product_id',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 36,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'product_category_id',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 36,
                            'after' => 'product_id'
                        ]
                    ),
                    new Column(
                        'product_vendor_id',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 36,
                            'after' => 'product_category_id'
                        ]
                    ),
                    new Column(
                        'product_user_id',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 36,
                            'after' => 'product_vendor_id'
                        ]
                    ),
                    new Column(
                        'product_title',
                        [
                            'type' => Column::TYPE_TEXT,
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'product_user_id'
                        ]
                    ),
                    new Column(
                        'product_link_slug',
                        [
                            'type' => Column::TYPE_TEXT,
                            'size' => 1,
                            'after' => 'product_title'
                        ]
                    ),
                    new Column(
                        'product_type',
                        [
                            'type' => Column::TYPE_CHAR,
                            'default' => "physical",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'product_link_slug'
                        ]
                    ),
                    new Column(
                        'product_custom_page_id',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 36,
                            'after' => 'product_type'
                        ]
                    ),
                    new Column(
                        'product_brand_id',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 36,
                            'after' => 'product_custom_page_id'
                        ]
                    ),
                    new Column(
                        'product_price',
                        [
                            'type' => Column::TYPE_FLOAT,
                            'default' => "0",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'product_brand_id'
                        ]
                    ),
                    new Column(
                        'product_sale_price',
                        [
                            'type' => Column::TYPE_FLOAT,
                            'default' => "0",
                            'size' => 1,
                            'after' => 'product_price'
                        ]
                    ),
                    new Column(
                        'product_sale_end_time',
                        [
                            'type' => Column::TYPE_DATETIME,
                            'size' => 1,
                            'after' => 'product_sale_price'
                        ]
                    ),
                    new Column(
                        'product_weight',
                        [
                            'type' => Column::TYPE_FLOAT,
                            'size' => 1,
                            'after' => 'product_sale_end_time'
                        ]
                    ),
                    new Column(
                        'product_digital_size',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'default' => "0",
                            'size' => 11,
                            'after' => 'product_weight'
                        ]
                    ),
                    new Column(
                        'created_at',
                        [
                            'type' => Column::TYPE_DATETIME,
                            'default' => "CURRENT_TIMESTAMP",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'product_digital_size'
                        ]
                    ),
                    new Column(
                        'updated_at',
                        [
                            'type' => Column::TYPE_DATETIME,
                            'size' => 1,
                            'after' => 'created_at'
                        ]
                    ),
                    new Column(
                        'deleted_at',
                        [
                            'type' => Column::TYPE_DATETIME,
                            'size' => 1,
                            'after' => 'updated_at'
                        ]
                    ),
                    new Column(
                        'is_deleted',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'default' => "0",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'deleted_at'
                        ]
                    ),
                    new Column(
                        'is_published',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'default' => "0",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'is_deleted'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('product_product_id_uindex', ['product_id'], 'UNIQUE')
                ],
                'options' => [
                    'TABLE_TYPE' => 'BASE TABLE',
                    'AUTO_INCREMENT' => '',
                    'ENGINE' => 'InnoDB',
                    'TABLE_COLLATION' => 'utf8mb4_0900_ai_ci'
                ],
            ]
        );
    }

    /**
     * Run the migrations
     *
     * @return void
     */
    public function up()
    {

    }

    /**
     * Reverse the migrations
     *
     * @return void
     */
    public function down()
    {

    }

}
