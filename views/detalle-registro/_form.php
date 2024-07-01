<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\DetalleRegistroModel $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="detalle-registro-model-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'IdRegistro')->textInput() ?>

    <?= $form->field($model, 'ArticuloDetalle')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'Cantidad')->textInput() ?>

    <?= $form->field($model, 'PrecioUnitario')->textInput() ?>

    <?= $form->field($model, 'ContadorImpresiones')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
