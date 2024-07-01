<?php
Yii::$app->language = 'es_ES';

use app\models\UsuarioBodega;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use kartik\export\ExportMenu;

$this->title = 'Listado de usuarios';
$this->params['breadcrumbs'][] = $this->title;
?>
