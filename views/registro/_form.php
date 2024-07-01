<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\RegistroModel $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="registro-model-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'CodigoBarra')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'Articulo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'Descripcion')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'Clasificacion')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'Libras')->textInput() ?>

    <?= $form->field($model, 'Unidades')->textInput() ?>

    <?= $form->field($model, 'IdTipoEmpaque')->textInput() ?>

    <?= $form->field($model, 'IdUbicacion')->textInput() ?>

    <?= $form->field($model, 'EmpacadoPor')->textInput() ?>

    <?= $form->field($model, 'ProducidoPor')->textInput() ?>

    <?= $form->field($model, 'BodegaCreacion')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'BodegaActual')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'Observaciones')->textInput() ?>

    <?= $form->field($model, 'UsuarioCreacion')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'DOCUMENTO_INV')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'Estado')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'Activo')->textInput() ?>

    <?= $form->field($model, 'Costo')->textInput() ?>

    <?= $form->field($model, 'FechaCreacion')->textInput() ?>

    <?= $form->field($model, 'FechaModificacion')->textInput() ?>

    <?= $form->field($model, 'Sesion')->textInput() ?>

    <?= $form->field($model, 'IdTipoRegistro')->textInput() ?>

    <?= $form->field($model, 'CreateDate')->textInput() ?>

    <?= $form->field($model, 'ContadorImpresiones')->textInput() ?>

    <?= $form->field($model, 'EmpresaDestino')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
