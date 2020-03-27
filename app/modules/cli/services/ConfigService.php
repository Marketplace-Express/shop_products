<?php
/**
 * User: Wajdi Jurry
 * Date: 3/26/20
 * Time: 4:28 PM
 */

namespace app\modules\cli\services;


class ConfigService
{
    public function setGlobalConfig(Object $data)
    {
        if (!file_put_contents(APP_PATH . '/config/remote_config.json')) {
            throw new \Exception('Could not write global config');
        }
    }
}