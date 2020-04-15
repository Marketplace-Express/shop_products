<?php
/**
 * User: Wajdi Jurry
 * Date: 19/12/18
 * Time: 09:29 م
 */

namespace app\common\logger;

use Phalcon\Di\Injectable;
use Phalcon\Logger\Adapter\File;

class ApplicationLogger extends Injectable
{
    /**
     * Logging file name
     */
    const LOG_FILE = 'app.log';

    /** @var File */
    private $file;

    /**
     * @return File
     */
    public function getFile()
    {
        $config = $this->di->getConfig();
        if (!file_exists($config->application->logsDir . self::LOG_FILE)) {
            touch($config->application->logsDir . self::LOG_FILE);
        }
        $this->file = new File($config->application->logsDir . self::LOG_FILE);
        return $this->file;
    }

    public function logError($errors)
    {
        $errors = (is_array($errors)) ? json_encode($errors) : $errors;
        $this->getFile()->log(\Phalcon\Logger::ERROR, $errors);
    }
}