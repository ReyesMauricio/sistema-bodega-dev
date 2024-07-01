<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;

/** @var yii\web\View $this */
/** @var app\models\UsuarioModel $model */
/** @var yii\widgets\ActiveForm $form */
?>


<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-truck"></i> &nbsp;<?= $this->title ?></h3>
    </div>
    <?php $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL]); ?>
    <div class="card-body">
        <form role="form">


            <?= $form->field($model, 'Usuario')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'Nombre')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'Digita')->widget(SwitchInput::class, [
                'value' => $model->Digita, //checked status can change by db value
                'options' => ['uncheck' => 0, 'value' => 1], //value if not set ,default is 1
                'pluginOptions' => [
                    'onColor' => 'success',
                    'offColor' => 'danger',
                    'onText' => 'Digita',
                    'offText' => 'No digita'
                ]
            ]); ?>
            
            <?= $form->field($model, 'Produce')->widget(SwitchInput::class, [
                'value' => $model->Produce, //checked status can change by db value
                'options' => ['uncheck' => 0, 'value' => 1], //value if not set ,default is 1
                'pluginOptions' => [
                    'onColor' => 'success',
                    'offColor' => 'danger',
                    'onText' => 'Produce',
                    'offText' => 'No produce'
                ]
            ]); ?>

            <?= $form->field($model, 'Empaca')->widget(SwitchInput::class, [
                'value' => $model->Empaca, //checked status can change by db value
                'options' => ['uncheck' => 0, 'value' => 1], //value if not set ,default is 1
                'pluginOptions' => [
                    'onColor' => 'success',
                    'offColor' => 'danger',
                    'onText' => 'Empaca',
                    'offText' => 'No empaca'
                ]
            ]); ?>

            <?= $form->field($model, 'Activo')->widget(SwitchInput::class, [
                'value' => $model->Activo, //checked status can change by db value
                'options' => ['uncheck' => 0, 'value' => 1], //value if not set ,default is 1
                'pluginOptions' => [
                    'onColor' => 'success',
                    'offColor' => 'danger',
                    'onText' => 'Activo',
                    'offText' => 'Inactivo'
                ]
            ]); ?>

            <div class="form-group">
                <?= Html::submitButton($model->isNewRecord ? 'Guardar' : 'Actualizar', ['class' => 'btn btn-success']) ?>
            </div>
        </form>
        <?php ActiveForm::end(); ?>
    </div>
</div>