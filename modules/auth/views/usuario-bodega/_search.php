<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\auth\models\UsuarioBodegaSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="usuario-bodega-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'USUARIO') ?>

    <?= $form->field($model, 'BODEGA') ?>

    <?= $form->field($model, 'CAJA') ?>

    <?= $form->field($model, 'PAQUETE') ?>

    <?= $form->field($model, 'CORREOSUP') ?>

    <?php // echo $form->field($model, 'CORREOTIENDA') ?>

    <?php // echo $form->field($model, 'BASE') ?>

    <?php // echo $form->field($model, 'HAMACHI') ?>

    <?php // echo $form->field($model, 'CENTRO_COSTO') ?>

    <?php // echo $form->field($model, 'TIPO') ?>

    <?php // echo $form->field($model, 'ESQUEMA') ?>

    <?php // echo $form->field($model, 'PREFIJODOC') ?>

    <?php // echo $form->field($model, 'RESOL_ACTUAL') ?>

    <?php // echo $form->field($model, 'SERIEMAQUINA') ?>

    <?php // echo $form->field($model, 'NUM_AUTORIZACION') ?>

    <?php // echo $form->field($model, 'FECHA_AUTORIZACION') ?>

    <?php // echo $form->field($model, 'CANT_LIMITE_TICKET') ?>

    <?php // echo $form->field($model, 'RESOLUCION') ?>

    <?php // echo $form->field($model, 'FECHA_SOLICITUD') ?>

    <?php // echo $form->field($model, 'SERIE') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
