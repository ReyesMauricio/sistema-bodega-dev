<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modelsSearch\TrabajoMesaModelSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="trabajo-mesa-model-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id_trabajo_mesa') ?>

    <?= $form->field($model, 'NumeroMesa') ?>

    <?= $form->field($model, 'Libras') ?>

    <?= $form->field($model, 'Costo') ?>

    <?= $form->field($model, 'ProducidoPor') ?>

    <?php // echo $form->field($model, 'Documento_inv') ?>

    <?php // echo $form->field($model, 'Bodega') ?>

    <?php // echo $form->field($model, 'Fecha') ?>

    <?php // echo $form->field($model, 'CreateDate') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
