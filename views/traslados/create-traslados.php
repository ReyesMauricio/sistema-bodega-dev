<?php

use kartik\number\NumberControl;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use kartik\widgets\DepDrop;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Crear registro';
$this->params['breadcrumbs'][] = ['label' => 'Listado', 'url' => ['index-traslados']];
$this->params['breadcrumbs'][] = $this->title;
?>
<h1>Crear traslado</h1>


<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-truck"></i> &nbsp;Crear traslado</h3>
    </div>
    <?php $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL, 'id' => 'form-create-traslado']); ?>
    <div class="card-body">
        <form role="form">
            <div class="row">
                <div class="col-md-4">
                    <?= Html::activeLabel($traslado, 'bodega_origen', ['class' => 'control-label']) ?>
                    <?= $form->field($traslado, 'bodega_origen',  ['showLabels' => false])->widget(Select2::class, [
                        'data' => ArrayHelper::map(
                            $bodegas,
                            'NOMBRE',
                            'NOMBRE'
                        ),
                        'language' => 'es',
                        'options' => [
                            'placeholder' => '-- Seleccionar bodega de origen --',
                            'focus' => true,
                        ],
                        'pluginOptions' => ['allowClear' => true],
                    ]); ?>
                </div>
                <div class="col-md-4">
                    <?= Html::activeLabel($traslado, 'bodega_destino', ['class' => 'control-label']) ?>
                    <?= $form->field($traslado, 'bodega_destino', ['showLabels' => false])->widget(DepDrop::class, [
                        'language' => 'es',
                        'type' => DepDrop::TYPE_SELECT2,
                        'options' => [
                            'placeholder' => '- Seleccionar bodega de destino -',
                        ],
                        'pluginOptions' => [
                            'depends' => ['dynamicmodel-bodega_origen'],
                            'initialize' => /* In the code snippet provided, the `false` value is being
                            used as an argument for the `showLabels` option in the
                            `ActiveForm::field()` method. */
                            /* In the code snippet provided, the `false` value is being
                            used as an argument for the `showLabels` option in the
                            `ActiveForm::field()` method. */
                            false,
                            'url' => Url::to(['/traslados/bodegas-destino']),
                            'placeholder' => '- Seleccionar bodega de destino -',
                            'loadingText' => 'Cargando datos...',
                        ]
                    ]); ?>
                </div>
                <div class="col-md-4">
                    <?= Html::activeLabel($traslado, 'fecha_traslado', ['class' => 'control-label']) ?>
                    <?= $form->field($traslado, 'fecha_traslado', ['showLabels' => false])->widget(DatePicker::class, [
                        'options' => [
                            'placeholder' => 'Seleccionar fecha',
                            'value' => date("Y-m-d"),
                        ],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' =>
                            'yyyy-m-dd',
                            'todayHighlight' => true,
                            'endDate' => "+1d",
                        ],
                    ]); ?>
                </div>
                <div class="col-md-12">
                    <?= Html::activeLabel($traslado, 'codigo_barra', ['class' => 'control-label']) ?>
                    <?= $form->field($traslado, 'codigo_barra', ['showLabels' => false])->textarea(
                        [
                            'maxlength' => true,
                            'rows' => 10
                        ]
                    ) ?>
                </div>
                <div class="col-md-4 mt-2">
                    <br>
                    <?= Html::submitButton('Verificar', ['class' => 'btn btn-success', 'id' => 'create-traslado']) ?>
                </div>
            </div>
        </form>
        <?php
        ActiveForm::end();
        ?>
    </div>
</div>