<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class ProductVariationsMigration_102
 */
class ProductVariationsMigration_102 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('product_variations', [
                'columns' => [
                    new Column(
                        'variation_id',
                        [
                            'type' => Column::TYPE_CHAR,
                            'notNull' => true,
                            'size' => 36,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'product_id',
                        [
                            'type' => Column::TYPE_CHAR,
                            'notNull' => true,
                            'size' => 36,
                            'after' => 'variation_id'
                        ]
                    ),
                    new Column(
                        'quantity',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'default' => "0",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'product_id'
                        ]
                    ),
                    new Column(
                        'user_id',
                        [
                            'type' => Column::TYPE_CHAR,
                            'notNull' => true,
                            'size' => 36,
                            'after' => 'quantity'
                        ]
                    ),
                    new Column(
                        'image_id',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 36,
                            'after' => 'user_id'
                        ]
                    ),
                    new Column(
                        'price',
                        [
                            'type' => Column::TYPE_FLOAT,
                            'default' => "0",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'image_id'
                        ]
                    ),
                    new Column(
                        'sale_price',
                        [
                            'type' => Column::TYPE_FLOAT,
                            'default' => "0",
                            'size' => 1,
                            'after' => 'price'
                        ]
                    ),
                    new Column(
                        'sku',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 100,
                            'after' => 'sale_price'
                        ]
                    ),
                    new Column(
                        'created_at',
                        [
                            'type' => Column::TYPE_DATETIME,
                            'notNull' => true,
                            'after' => 'sku'
                        ]
                    ),
                    new Column(
                        'updated_at',
                        [
                            'type' => Column::TYPE_DATETIME,
                            'after' => 'created_at'
                        ]
                    ),
                    new Column(
                        'deleted_at',
                        [
                            'type' => Column::TYPE_DATETIME,
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
                        'deletion_token',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'default' => "N/A",
                            'size' => 36,
                            'after' => 'is_deleted'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('PRIMARY', ['variation_id'], 'PRIMARY'),
                    new Index('product_variations_uindex', ['product_id', 'sku', 'is_deleted', 'deletion_token'], 'UNIQUE'),
                    new Index('product_variations_product_product_id_fk', ['product_id'], null),
                    new Index('product_variations_product_images_image_id_fk', ['image_id'], null)
                ],
                'references' => [
                    new Reference(
                        'product_variations_product_images_image_id_fk',
                        [
                            'referencedTable' => 'product_images',
                            'referencedSchema' => 'shop_products',
                            'columns' => ['image_id'],
                            'referencedColumns' => ['image_id'],
                            'onUpdate' => 'NO ACTION',
                            'onDelete' => 'NO ACTION'
                        ]
                    ),
                    new Reference(
                        'product_variations_product_product_id_fk',
                        [
                            'referencedTable' => 'product',
                            'referencedSchema' => 'shop_products',
                            'columns' => ['product_id'],
                            'referencedColumns' => ['product_id'],
                            'onUpdate' => 'NO ACTION',
                            'onDelete' => 'NO ACTION'
                        ]
                    )
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
