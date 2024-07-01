<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modelsSearch\DetalleRegistroModelSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="detalle-registro-model-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'IdDetalleRegistro') ?>

    <?= $form->field($model, 'IdRegistro') ?>

    <?= $form->field($model, 'ArticuloDetalle') ?>

    <?= $form->field($model, 'Cantidad') ?>

    <?= $form->field($model, 'PrecioUnitario') ?>

    <?php // echo $form->field($model, 'ContadorImpresiones') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
