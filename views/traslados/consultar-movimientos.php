<?php

use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Consultar movimientos';
$this->params['breadcrumbs'][] = $this->title;

?>
<?php
Modal::begin([
    'options' => [
        'tabindex' => false,
    ],
    'headerOptions' => ['class' => 'bg-primary'],
    'title' => 'Informacion de reporte',
    'id' => 'create-modal',
    'size' => 'modal-xl',
    'class' => 'bg-primary',
    'scrollable' => true,
]);
echo "<div id='createModalContent'></div>";
Modal::end();
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
                    <?= Html::activeLabel($model, 'reporte', ['class' => 'control-label']) ?>
                    <?php echo Select2::widget([
                        'name' => 'reporte',
                        'id' => 'reporte',
                        'data' =>
                        ArrayHelper::map([
                            ['clave' => 'D', 'empresa' => 'Despachos'],
                            ['clave' => 'T', 'empresa' => 'Traslados'],
                        ], "clave", "empresa"),
                        'value' => 'D',
                        'language' => 'es',
                        'options' => ['placeholder' => '- Seleccionar tipo -'],
                        'pluginOptions' => ['allowClear' => true, 'initialize' => true,],
                    ]);
                    ?>
                </div>
                <div class="col-md-8">
                    <label for="">Seleccione un rango de fecha:</label>
                    <?php
                    $layout3 = <<< HTML
                        <span class="input-group-text">Desde</span>
                        {input1}
                        {separator}
                        <span class="input-group-text">Hasta</span>
                        {input2}
                        
                            <span class="input-group-text kv-date-remove">
                                <i class="fas fa-times kv-dp-icon"></i>
                            </span>
                        
                        HTML;
                    echo  DatePicker::widget([
                        'name' => 'fechaInicio',
                        'id' => 'fechaInicio',
                        'type' => DatePicker::TYPE_RANGE,
                        'value' => date("Y-m-d"),
                        'name2' => 'fechaFin',
                        'value2' => date("Y-m-d"),
                        'separator' => '<i class="fas fa-arrows-alt-h"></i>',
                        'layout' => $layout3,
                        'options' => [
                            'placeholder' => 'Seleccionar fecha de inicio',
                        ],
                        'options2' => [
                            'placeholder' => 'Seleccionar fecha de fin',
                        ],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-m-dd',
                            'todayHighlight' => true
                        ],
                    ]); ?>
                </div>
                <div class="col-md-12 mt-2">
                    <br>
                    <?= Html::button(
                        '<i class="fa fa-search"></i> Consultar',
                        [
                            'value' => Url::to('index.php?r=traslados/mostrar-consulta-movimientos'),
                            'id' => 'modalButton',
                            'class' => 'btn btn-warning'
                        ]
                    ) ?>
                    <?= Html::a('<i class="fas fa-redo">&nbsp;&nbsp;Reiniciar</i>', ['consultar-movimientos'], [
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
    let reporte = document.getElementById('reporte')
    let fechaInicio = document.getElementById('fechaInicio')
    let fechaFin = document.getElementById('fechaInicio-2')
    let botonGenerarReporte = document.getElementById('modalButton')
    let hrefBase = botonGenerarReporte.value
    botonGenerarReporte.value += `&fechaInicio=${fechaInicio.value}&fechaFin=${fechaFin.value}&tipo=${reporte.value}`


    $('#fechaInicio').on('change', function(e) {
        botonGenerarReporte.value = ''
        botonGenerarReporte.value += `${hrefBase}&fechaInicio=${fechaInicio.value}&fechaFin=${fechaFin.value}&tipo=${reporte.value}`;
    })
    $('#fechaInicio-2').on('change', function(e) {
        botonGenerarReporte.value = ''
        botonGenerarReporte.value += `${hrefBase}&fechaInicio=${fechaInicio.value}&fechaFin=${fechaFin.value}&tipo=${reporte.value}`;
    })

    $('#reporte').on('change', function(e) {
        let data = $("#dynamicmodel-fecha option:selected").val();
        botonGenerarReporte.value = ''
        botonGenerarReporte.value = `${hrefBase}&fechaInicio=${fechaInicio.value}&fechaFin=${fechaFin.value}&tipo=${reporte.value}`;
    })
</script>