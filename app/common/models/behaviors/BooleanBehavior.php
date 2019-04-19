<?php
/**
 * User: Wajdi Jurry
 * Date: 19/04/19
 * Time: 11:41 ص
 */

namespace Shop_products\Models\Behaviors;


use Phalcon\Db\Column;
use Phalcon\Mvc\Model\Behavior;
use Phalcon\Mvc\Model\BehaviorInterface;

/**
 * Class BooleanBehavior
 * @package Shop_products\Models\Behaviors
 * This behavior casts booleans to tiny integers, since MySQL does not support booleans
 */
class BooleanBehavior extends Behavior implements BehaviorInterface
{
    public function notify($type, \Phalcon\Mvc\ModelInterface $model)
    {
        if ($type == 'beforeValidation') {
            $model->skipOperation(true);
            $metaData = $model->getModelsMetaData();
            $boolColumns = array_keys($metaData->getDataTypes($model), Column::TYPE_BOOLEAN, true);
            $mappedColumns = $metaData->getColumnMap($model);
            foreach ($boolColumns as $boolColumn) {
                if (array_key_exists($boolColumn, $mappedColumns)) {
                    $column = $mappedColumns[$boolColumn];
                    $model->{$column} = (int)$model->{$column};
                }
            }
        }
    }
}