<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\UsuarioBodega $model */

$this->title = 'Update Usuario Bodega: ' . $model->USUARIO;
$this->params['breadcrumbs'][] = ['label' => 'Usuario Bodegas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->USUARIO, 'url' => ['view', 'USUARIO' => $model->USUARIO]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="usuario-bodega-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
