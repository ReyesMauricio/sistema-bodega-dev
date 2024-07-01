<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modelsSearch\RegistroModelSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="registro-model-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'IdRegistro') ?>

    <?= $form->field($model, 'CodigoBarra') ?>

    <?= $form->field($model, 'Articulo') ?>

    <?= $form->field($model, 'Descripcion') ?>

    <?= $form->field($model, 'Clasificacion') ?>

    <?php // echo $form->field($model, 'Libras') ?>

    <?php // echo $form->field($model, 'Unidades') ?>

    <?php // echo $form->field($model, 'IdTipoEmpaque') ?>

    <?php // echo $form->field($model, 'IdUbicacion') ?>

    <?php // echo $form->field($model, 'EmpacadoPor') ?>

    <?php // echo $form->field($model, 'ProducidoPor') ?>

    <?php // echo $form->field($model, 'BodegaCreacion') ?>

    <?php // echo $form->field($model, 'BodegaActual') ?>

    <?php // echo $form->field($model, 'Observaciones') ?>

    <?php // echo $form->field($model, 'UsuarioCreacion') ?>

    <?php // echo $form->field($model, 'DOCUMENTO_INV') ?>

    <?php // echo $form->field($model, 'Estado') ?>

    <?php // echo $form->field($model, 'Activo') ?>

    <?php // echo $form->field($model, 'Costo') ?>

    <?php // echo $form->field($model, 'FechaCreacion') ?>

    <?php // echo $form->field($model, 'FechaModificacion') ?>

    <?php // echo $form->field($model, 'Sesion') ?>

    <?php // echo $form->field($model, 'IdTipoRegistro') ?>

    <?php // echo $form->field($model, 'CreateDate') ?>

    <?php // echo $form->field($model, 'ContadorImpresiones') ?>

    <?php // echo $form->field($model, 'EmpresaDestino') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
