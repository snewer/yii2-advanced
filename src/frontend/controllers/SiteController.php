<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionRobotsTxt()
    {
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'text/plain; charset=UTF-8');
        if (YII_ENV_PROD) {
            $file = Yii::getAlias('@frontend/web/robots.prod.txt');
        } else {
            $file = Yii::getAlias('@frontend/web/robots.dev.txt');
        }
        return file_get_contents($file);
    }
}