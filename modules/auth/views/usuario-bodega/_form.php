<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\UsuarioBodega $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="usuario-bodega-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'USUARIO')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'BODEGA')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'CAJA')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'PAQUETE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'CORREOSUP')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'CORREOTIENDA')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'BASE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HAMACHI')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'CENTRO_COSTO')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'TIPO')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ESQUEMA')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'PREFIJODOC')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'RESOL_ACTUAL')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'SERIEMAQUINA')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'NUM_AUTORIZACION')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'FECHA_AUTORIZACION')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'CANT_LIMITE_TICKET')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'RESOLUCION')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'FECHA_SOLICITUD')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'SERIE')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
