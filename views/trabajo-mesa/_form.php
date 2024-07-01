<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\TrabajoMesaModel $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="trabajo-mesa-model-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'NumeroMesa')->textInput() ?>

    <?= $form->field($model, 'Libras')->textInput() ?>

    <?= $form->field($model, 'Costo')->textInput() ?>

    <?= $form->field($model, 'ProducidoPor')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'Documento_inv')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'Bodega')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'Fecha')->textInput() ?>

    <?= $form->field($model, 'CreateDate')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
