<?php

use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Crear nuevos articulos';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-briefcase"></i> &nbsp;<?= $this->title ?></h3>

    </div>
    <?php $form = ActiveForm::begin(['id' => 'detalle-form', 'type' => ActiveForm::TYPE_HORIZONTAL]); ?>
    <div class="card-body">
        <form role="form">
            <div class="row">
                <div class="col-md-4">
                    <?= Html::activeLabel($articulo, 'descripcion', ['class' => 'control-label']) ?>
                    <?= $form->field($articulo, 'descripcion', ['showLabels' => false])->textInput(
                        [
                            'placeholder' => 'Descripcion del articulo'
                        ]
                    ) ?>
                </div>
                <div class="col-md-4">
                    <?= Html::activeLabel($articulo, 'clasificacion', ['class' => 'control-label']) ?>
                    <?= $form->field($articulo, 'clasificacion',  ['showLabels' => false])->widget(Select2::class, [
                        'name' => 'clasificacion',
                        'id' => 'clasificacion',
                        'data' => ArrayHelper::map($clasificaciones, "CLASIFICACION_2", "CLASIFICACION_2"),
                        'language' => 'es',
                        'options' => ['placeholder' => '- Seleccionar clasificacion -'],
                        'pluginOptions' => ['allowClear' => true,],
                    ]);
                    ?>
                </div>
                <div class="col-md-4">
                    <?= Html::activeLabel($articulo, 'tipo', ['class' => 'control-label']) ?>
                    <?= $form->field($articulo, 'tipo',  ['showLabels' => false])->widget(Select2::class, [
                        'name' => 'clasificacion',
                        'id' => 'clasificacion',
                        'data' => ArrayHelper::map([
                            ['tipo' => 'FARD0'],
                            ['tipo' => 'P'],
                            ['tipo' => 'T'],
                        ], "tipo", "tipo"),
                        'language' => 'es',
                        'options' => ['placeholder' => '- Seleccionar tipo -'],
                        'pluginOptions' => ['allowClear' => true,],
                    ]);
                    ?>
                </div>
                <div class="col-md-12 mt-2">
                    <br>
                    <?= Html::submitButton('<i class="fa fa-plus"></i> Crear articulo', [
                        'class' => 'btn btn-success',
                    ]) ?>
                    <?= Html::a('<i class="fas fa-redo">&nbsp;&nbsp;Reiniciar</i>', ['crear-articulo'], [
                        'class' => 'btn btn-outline-warning',
                        'data-pjax' => 0,
                    ]) ?>
                </div>
            </div>
        </form>
        <?php
        ActiveForm::end();
        ?>
    </div>
</div>