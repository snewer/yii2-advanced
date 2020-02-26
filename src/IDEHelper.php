<?php

use common\components\Vk;

class Yii extends \yii\BaseYii
{
    /**
     * @var BaseApplication|WebApplication|ConsoleApplication
     */
    public static $app;
}

/**
 * @property Vk $vk
 */
abstract class BaseApplication extends yii\base\Application
{
}

class WebApplication extends yii\web\Application
{
}

class ConsoleApplication extends yii\console\Application
{
}