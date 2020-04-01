<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class ProductImagesMigration_100
 */
class ProductImagesMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('product_images', [
                'columns' => [
                    new Column(
                        'image_id',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 10,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'image_album_id',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 7,
                            'after' => 'image_id'
                        ]
                    ),
                    new Column(
                        'product_id',
                        [
                            'type' => Column::TYPE_CHAR,
                            'notNull' => true,
                            'size' => 36,
                            'after' => 'image_album_id'
                        ]
                    ),
                    new Column(
                        'image_link',
                        [
                            'type' => Column::TYPE_TEXT,
                            'notNull' => true,
                            'after' => 'product_id'
                        ]
                    ),
                    new Column(
                        'image_size',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'default' => "0",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'image_link'
                        ]
                    ),
                    new Column(
                        'image_type',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 15,
                            'after' => 'image_size'
                        ]
                    ),
                    new Column(
                        'image_width',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 1,
                            'after' => 'image_type'
                        ]
                    ),
                    new Column(
                        'image_height',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 1,
                            'after' => 'image_width'
                        ]
                    ),
                    new Column(
                        'image_delete_hash',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 20,
                            'after' => 'image_height'
                        ]
                    ),
                    new Column(
                        'image_name',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 100,
                            'after' => 'image_delete_hash'
                        ]
                    ),
                    new Column(
                        'image_order',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'default' => "0",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'image_name'
                        ]
                    ),
                    new Column(
                        'created_at',
                        [
                            'type' => Column::TYPE_DATETIME,
                            'default' => "CURRENT_TIMESTAMP",
                            'notNull' => true,
                            'after' => 'image_order'
                        ]
                    ),
                    new Column(
                        'deleted_at',
                        [
                            'type' => Column::TYPE_DATETIME,
                            'after' => 'created_at'
                        ]
                    ),
                    new Column(
                        'is_main',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'default' => "0",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'deleted_at'
                        ]
                    ),
                    new Column(
                        'is_variation_image',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'default' => "0",
                            'size' => 1,
                            'after' => 'is_main'
                        ]
                    ),
                    new Column(
                        'is_used',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'default' => "0",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'is_variation_image'
                        ]
                    ),
                    new Column(
                        'is_deleted',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'default' => "0",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'is_used'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('PRIMARY', ['image_id'], 'PRIMARY'),
                    new Index('images_listing_index', ['product_id', 'is_deleted'], null)
                ],
                'references' => [
                    new Reference(
                        'product_images_product_product_id_fk',
                        [
                            'referencedTable' => 'product',
                            'referencedSchema' => 'shop_products',
                            'columns' => ['product_id'],
                            'referencedColumns' => ['product_id'],
                            'onUpdate' => 'NO ACTION',
                            'onDelete' => 'CASCADE'
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
