<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class ProductRatesMigration_100
 */
class ProductRatesMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('product_rates', [
                'columns' => [
                    new Column(
                        'rate_id',
                        [
                            'type' => Column::TYPE_CHAR,
                            'notNull' => true,
                            'size' => 36,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'user_id',
                        [
                            'type' => Column::TYPE_CHAR,
                            'notNull' => true,
                            'size' => 36,
                            'after' => 'rate_id'
                        ]
                    ),
                    new Column(
                        'product_id',
                        [
                            'type' => Column::TYPE_CHAR,
                            'notNull' => true,
                            'size' => 36,
                            'after' => 'user_id'
                        ]
                    ),
                    new Column(
                        'rate_text',
                        [
                            'type' => Column::TYPE_TEXT,
                            'after' => 'product_id'
                        ]
                    ),
                    new Column(
                        'rate_stars',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'default' => "1",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'rate_text'
                        ]
                    ),
                    new Column(
                        'created_at',
                        [
                            'type' => Column::TYPE_DATETIME,
                            'default' => "CURRENT_TIMESTAMP",
                            'notNull' => true,
                            'after' => 'rate_stars'
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
                            'notNull' => true,
                            'size' => 36,
                            'after' => 'is_deleted'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('PRIMARY', ['rate_id'], 'PRIMARY'),
                    new Index('product_rated_rate_id_uindex', ['rate_id'], 'UNIQUE'),
                    new Index('product_uindex', ['user_id', 'product_id', 'is_deleted', 'deletion_token'], 'UNIQUE'),
                    new Index('product_rated_product_product_id_fk', ['product_id'], null),
                    new Index('product_rates_index', ['is_deleted', 'product_id'], null)
                ],
                'references' => [
                    new Reference(
                        'product_rated_products_product_id_fk',
                        [
                            'referencedTable' => 'products',
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
