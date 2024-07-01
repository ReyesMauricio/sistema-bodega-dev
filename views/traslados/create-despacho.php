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
<h1>Crear despacho</h1>


<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-truck"></i> &nbsp;Crear despacho</h3>
    </div>
    <?php $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL, 'id' => 'form-create-despacho']); ?>
    <div class="card-body">
        <form role="form">
            <div class="row">
                <div class="col-md-4">
                    <?= Html::activeLabel($traslado, 'bodega_origen', ['class' => 'control-label']) ?>
                    <?= $form->field($traslado, 'bodega_origen',  ['showLabels' => false])->widget(Select2::class, [
                        'data' => ArrayHelper::map(
                            $bodegasDespachos,
                            'NOMBRE',
                            'NOMBRE'
                        ),
                        'language' => 'es',
                        'options' => [
                            'placeholder' => '-- Seleccionar bodega de origen --',
                            'focus' => true,
                            'id' => 'origen',
                        ],
                        'pluginOptions' => ['allowClear' => true],
                    ]); ?>
                </div>
                <div class="col-md-4">
                    <?= Html::activeLabel($traslado, 'bodega_destino', ['class' => 'control-label']) ?>
                    <?= $form->field($traslado, 'bodega_destino',  ['showLabels' => false])->widget(Select2::class, [
                        'data' => ArrayHelper::map(
                            $bodegasDestino,
                            'NOMBRE',
                            'NOMBRE'
                        ),
                        'language' => 'es',
                        'options' => [
                            'placeholder' => '-- Seleccionar bodega destino --',
                            'focus' => true,
                            'id' => 'destino',
                        ],
                        'pluginOptions' => ['allowClear' => true],
                    ]); ?>
                </div>
                <div class="col-md-4">
                    <?= Html::activeLabel($traslado, 'fecha_traslado', ['class' => 'control-label']) ?>
                    <?= $form->field($traslado, 'fecha_traslado', ['showLabels' => false])->widget(DatePicker::class, [
                        'options' => [
                            'placeholder' => 'Seleccionar fecha',
                            'value' => isset($traslado->fecha_traslado) ? $traslado->fecha_traslado : date("Y-m-d"),
                            'id' => 'fecha-despacho',
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
                            'id' => 'codigos',
                            'maxlength' => true,
                            'rows' => 10
                        ]
                    ) ?>
                </div>
                <div class="col-md-4 mt-2">
                    <br>
                    <?= Html::submitButton('Finalizar despacho', ['class' => 'btn btn-success', 'id' => 'create-despacho']) ?>
                </div>
            </div>
        </form>
        <?php
        ActiveForm::end();
        ?>
    </div>
</div>
<script>
    let botonFinalizarDespacho = document.getElementById('create-despacho')
    let formDespacho = document.getElementById('form-create-despacho')

    botonFinalizarDespacho.addEventListener('click', (e) => {
        e.preventDefault()
        if ($('#codigos').val() == '' || $('#fecha-despacho').val() == '' || $('#origen').val() == '' || $('#destino').val() == '') {
            Swal.fire({
                title: 'COMPLETA EL FORMULARIO',
                showCancelButton: false,
                icon: 'error',
                confirmButtonText: 'Continuar',
                cancelButtonText: `Cancelar`,
            })
            return
        }
        Swal.fire({
            title: 'Estas a punto de finalizar este despacho, deseas continuar?',
            showCancelButton: true,
            icon: 'error',
            confirmButtonText: 'Continuar',
            cancelButtonText: `Cancelar`,
        }).then((result) => {
            if (result.isConfirmed) {
                formDespacho.submit()
            }
        })


    })
</script>