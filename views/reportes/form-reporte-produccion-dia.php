<?php

use app\models\UsuarioModel;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Obtener produccion del dia';
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
                    <?= Html::activeLabel($model, 'fecha', ['class' => 'control-label']) ?>
                    <?= $form->field($model, 'fecha',  ['showLabels' => false])->widget(DatePicker::class, [
                        'name' => 'fecha',
                        'options' => [
                            'placeholder' => 'Seleccionar fecha',
                            'value' =>  date("Y-m-d"),
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
                <div class="col-md-12 mt-2">
                    <br>
                    <?= Html::a(
                        '<i class="fa fa-download"></i> Descargar reporte',
                        [
                            Url::to('reportes/crear-reporte-produccion-dia-excel')
                        ],
                        [
                            'id' => 'boton-generar-reporte',
                            'class' => 'btn btn-warning'
                        ]
                    ) ?>
                    <?= Html::a('<i class="fas fa-redo">&nbsp;&nbsp;Reiniciar</i>', ['reporte-produccion-dia'], [
                        'class' => 'btn btn-outline-success',
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
<script>

    let fecha = document.getElementById('dynamicmodel-fecha')
    let botonGenerarReporte = document.getElementById('boton-generar-reporte')
    let hrefBase = botonGenerarReporte.href
    botonGenerarReporte.href += `&fecha=${fecha.value}`
    $('#dynamicmodel-fecha').on('change', function(e) {
        let data = $("#dynamicmodel-fecha option:selected").val();
        botonGenerarReporte.href = ''
        botonGenerarReporte.href = hrefBase + `&fecha=${fecha.value}`;
    })
</script>