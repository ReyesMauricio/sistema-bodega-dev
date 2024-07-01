<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\DetalleRegistroModel $model */

$this->title = 'Update Detalle Registro Model: ' . $model->IdDetalleRegistro;
$this->params['breadcrumbs'][] = ['label' => 'Detalle Registro Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->IdDetalleRegistro, 'url' => ['view', 'IdDetalleRegistro' => $model->IdDetalleRegistro]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="detalle-registro-model-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
