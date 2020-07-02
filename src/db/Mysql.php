<?php

namespace amoracr\backup\db;

use Yii;
use amoracr\backup\db\Database;

/**
 * Description of Mysql
 *
 * @author alonso
 */
class Mysql extends Database
{

    public function dumpDatabase($dbHandle, $path)
    {
        $this->validateDumpCommand();
        $dumpCommand = $this->prepareCommand($dbHandle, $this->dumpCommand);
        $file = Yii::getAlias($path) . DIRECTORY_SEPARATOR . $dbHandle . '.sql';
        $command = sprintf("%s > %s  2> /dev/null", $dumpCommand, $file);
        system($command);

        if (!file_exists($file)) {
            return false;
        }

        return $file;
    }

    public function importDatabase($dbHandle, $file)
    {
        $this->validateLoadCommand();
        $importCommand = $this->prepareDbCommand($dbHandle, $this->loadCommand);
        $command = sprintf("%s < %s  2> /dev/null", $importCommand, $file);
        system($command);

        return true;
    }

    protected function prepareCommand($dbHandle, $templateCommand)
    {
        $command = $templateCommand;
        $database = Yii::$app->$dbHandle->createCommand("SELECT DATABASE()")->queryScalar();
        $params = [
            'username' => Yii::$app->$dbHandle->username,
            'host' => 'localhost',
            'password' => Yii::$app->$dbHandle->password,
            'db' => $database,
        ];

        if ((string) $params['password'] === '') {
            $command = str_replace('-p\'{password}\'', '', $command);
            unset($params['password']);
        }

        return $this->replaceParams($command, $params);
    }

}
