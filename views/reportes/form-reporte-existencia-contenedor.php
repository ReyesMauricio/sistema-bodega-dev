<?php

use app\models\UsuarioModel;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Obtener existencias de contenedor';
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
                    <?= Html::button(
                        '<i class="fa fa-plus"></i> Generar reporte',
                        [
                            'value' => Url::to('index.php?r=reportes/crear-reporte-existencia-contenedor'),
                            'id' => 'modalButton',
                            'class' => 'btn btn-warning'
                        ]
                    ) ?>
                    <?= Html::a('<i class="fas fa-redo">&nbsp;&nbsp;Reiniciar</i>', ['reporte-existencia-contenedor'], [
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
    let botonGenerarReporte = document.getElementById('modalButton')
    let hrefBase = botonGenerarReporte.value
    botonGenerarReporte.value += `&fecha=${fecha.value}`

    $('#dynamicmodel-fecha').on('change', function(e) {
        let data = $("#dynamicmodel-fecha option:selected").val();
        botonGenerarReporte.value = ''
        botonGenerarReporte.value = hrefBase + `&fecha=${fecha.value}`;
    })
</script>