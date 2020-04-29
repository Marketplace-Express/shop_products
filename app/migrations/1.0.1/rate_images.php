<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class RateImagesMigration_101
 */
class RateImagesMigration_101 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('rate_images', [
                'columns' => [
                    new Column(
                        'row_id',
                        [
                            'type' => Column::TYPE_BIGINTEGER,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'size' => 1,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'image_id',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 36,
                            'after' => 'row_id'
                        ]
                    ),
                    new Column(
                        'rate_id',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 36,
                            'after' => 'image_id'
                        ]
                    ),
                    new Column(
                        'created_at',
                        [
                            'type' => Column::TYPE_DATETIME,
                            'default' => "CURRENT_TIMESTAMP",
                            'notNull' => true,
                            'after' => 'rate_id'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('PRIMARY', ['row_id'], 'PRIMARY'),
                    new Index('rate_images_image_id_uindex', ['image_id'], 'UNIQUE'),
                    new Index('rate_images_product_rates_rate_id_fk', ['rate_id'], null)
                ],
                'references' => [
                    new Reference(
                        'rate_images_images_image_id_fk',
                        [
                            'referencedTable' => 'images',
                            'referencedSchema' => 'shop_products',
                            'columns' => ['image_id'],
                            'referencedColumns' => ['image_id'],
                            'onUpdate' => 'NO ACTION',
                            'onDelete' => 'CASCADE'
                        ]
                    ),
                    new Reference(
                        'rate_images_product_rates_rate_id_fk',
                        [
                            'referencedTable' => 'product_rates',
                            'referencedSchema' => 'shop_products',
                            'columns' => ['rate_id'],
                            'referencedColumns' => ['rate_id'],
                            'onUpdate' => 'NO ACTION',
                            'onDelete' => 'CASCADE'
                        ]
                    )
                ],
                'options' => [
                    'TABLE_TYPE' => 'BASE TABLE',
                    'AUTO_INCREMENT' => '23',
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
