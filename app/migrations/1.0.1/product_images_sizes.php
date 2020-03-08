<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class ProductImagesSizesMigration_101
 */
class ProductImagesSizesMigration_101 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('product_images_sizes', [
                'columns' => [
                    new Column(
                        'row_id',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'size' => 11,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'image_id',
                        [
                            'type' => Column::TYPE_CHAR,
                            'notNull' => true,
                            'size' => 10,
                            'after' => 'row_id'
                        ]
                    ),
                    new Column(
                        'small',
                        [
                            'type' => Column::TYPE_TEXT,
                            'after' => 'image_id'
                        ]
                    ),
                    new Column(
                        'big',
                        [
                            'type' => Column::TYPE_TEXT,
                            'after' => 'small'
                        ]
                    ),
                    new Column(
                        'thumb',
                        [
                            'type' => Column::TYPE_TEXT,
                            'after' => 'big'
                        ]
                    ),
                    new Column(
                        'medium',
                        [
                            'type' => Column::TYPE_TEXT,
                            'after' => 'thumb'
                        ]
                    ),
                    new Column(
                        'large',
                        [
                            'type' => Column::TYPE_TEXT,
                            'after' => 'medium'
                        ]
                    ),
                    new Column(
                        'huge',
                        [
                            'type' => Column::TYPE_TEXT,
                            'after' => 'large'
                        ]
                    ),
                    new Column(
                        'deleted_at',
                        [
                            'type' => Column::TYPE_DATETIME,
                            'after' => 'huge'
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
                    )
                ],
                'indexes' => [
                    new Index('PRIMARY', ['row_id'], 'PRIMARY'),
                    new Index('product_images_sizes_product_images_image_id_fk', ['image_id'])
                ],
                'references' => [
                    new Reference(
                        'product_images_sizes_product_images_image_id_fk',
                        [
                            'referencedTable' => 'product_images',
                            'referencedSchema' => 'shop_products',
                            'columns' => ['image_id'],
                            'referencedColumns' => ['image_id'],
                            'onUpdate' => 'NO ACTION',
                            'onDelete' => 'CASCADE'
                        ]
                    )
                ],
                'options' => [
                    'TABLE_TYPE' => 'BASE TABLE',
                    'AUTO_INCREMENT' => '1',
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
