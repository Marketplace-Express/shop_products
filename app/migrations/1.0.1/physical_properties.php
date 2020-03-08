<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class PhysicalPropertiesMigration_101
 */
class PhysicalPropertiesMigration_101 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('physical_properties', [
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
                        'product_id',
                        [
                            'type' => Column::TYPE_CHAR,
                            'notNull' => true,
                            'size' => 36,
                            'after' => 'row_id'
                        ]
                    ),
                    new Column(
                        'product_weight',
                        [
                            'type' => Column::TYPE_FLOAT,
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'product_id'
                        ]
                    ),
                    new Column(
                        'product_weight_unit',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 6,
                            'after' => 'product_weight'
                        ]
                    ),
                    new Column(
                        'product_brand_id',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 36,
                            'after' => 'product_weight_unit'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('PRIMARY', ['row_id'], 'PRIMARY'),
                    new Index('physical_properties_product_product_id_fk', ['product_id'])
                ],
                'references' => [
                    new Reference(
                        'physical_properties_product_product_id_fk',
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
