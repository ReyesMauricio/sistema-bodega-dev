<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modelsSearch\UsuarioModelSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="usuario-model-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'IdUsuario') ?>

    <?= $form->field($model, 'Usuario') ?>

    <?= $form->field($model, 'Nombre') ?>

    <?= $form->field($model, 'Digita') ?>

    <?= $form->field($model, 'Produce') ?>

    <?php // echo $form->field($model, 'Empaca') ?>

    <?php // echo $form->field($model, 'Activo') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
