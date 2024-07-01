<?php

use app\models\UsuarioModel;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Produccion por persona';
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
        <h3 class="card-title"><i class="fas fa-briefcase"></i> &nbsp;Produccion por mesa</h3>

    </div>
    <?php $form = ActiveForm::begin(['id' => 'detalle-form', 'type' => ActiveForm::TYPE_HORIZONTAL]); ?>
    <div class="card-body">
        <form role="form">
            <div class="row">
                <div class="col-md-4">
                    <?= Html::activeLabel($model, 'mesa', ['class' => 'control-label']) ?>
                    <?= $form->field($model, 'mesa',  ['showLabels' => false])->widget(Select2::class, [
                        'id' => 'reporte',
                        'data' => ArrayHelper::map(
                            [
                                ['numero' => 1, 'titulo' => "1 - Mesa de clasificación"],
                                ['numero' => 2, 'titulo' => "2 - Mesa de clasificación"],
                                ['numero' => 3, 'titulo' => "3 - Mesa de clasificación"],
                                ['numero' => 4, 'titulo' => "4 - Mesa de clasificación"],
                                ['numero' => 5, 'titulo' => "5 - Mesa de clasificación"],
                                ['numero' => 6, 'titulo' => "6 - Mesa de producción"],
                                ['numero' => 7, 'titulo' => "7 - Mesa de producción"],
                                ['numero' => 8, 'titulo' => "8 - Mesa de producción"],
                                ['numero' => 9, 'titulo' => "9 - Mesa de producción"],
                                ['numero' => 10, 'titulo' => "10 - Mesa de producción"],
                                ['numero' => 11, 'titulo' => "11 - Mesa de producción"],
                                ['numero' => 12, 'titulo' => "12 - Mesa de producción"],
                                ['numero' => 13, 'titulo' => "13 - Mesa de producción"],
                                ['numero' => 14, 'titulo' => "14 - Mesa de producción"],
                                ['numero' => 15, 'titulo' => "15 - Mesa de producción"],
                                ['numero' => 16, 'titulo' => "16 - Mesa de producción"],
                                ['numero' => 17, 'titulo' => "17 - Mesa de producción"],
                                ['numero' => 18, 'titulo' => "18 - Mesa de producción"],
                                ['numero' => 19, 'titulo' => "19 - Mesa de producción"],
                                ['numero' => 20, 'titulo' => "20 - Mesa de producción"],
                            ],
                            "numero",
                            "titulo"
                        ),
                        'language' => 'es',
                        'options' => ['placeholder' => '- Seleccionar mesa -'],
                        'pluginOptions' => ['allowClear' => true,],
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
            </div>
            <div class="col-md-12 mt-2">
                <br>
                <?= Html::button('<i class="fa fa-plus"></i> Generar reporte', [
                    'value' => Url::to('index.php?r=reportes/crear-reporte-produccion-mesa'),
                    'class' => 'btn btn-warning',
                    'id' => 'modalButton',
                    'disabled' => true,
                ]) ?>
                <?= Html::a('<i class="fas fa-redo">&nbsp;&nbsp;Reiniciar</i>', ['reporte-produccion-mesa'], [
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
    let botonModal = document.getElementById('modalButton')
    let href = botonModal.value

    let fechaInicio = document.getElementById('fechaInicio')
    let fechaFin = document.getElementById('fechaInicio-2')
    let mesa = document.getElementById('dynamicmodel-mesa')

    $('#modalButton').on('click', function(e) {
        let data = $("#dynamicmodel-mesa option:selected").val();
        botonModal.value += `&fechaInicio=${fechaInicio.value}&fechaFin=${fechaFin.value}&mesa=${mesa.value}`;
    })

    $('#dynamicmodel-mesa').on('change', function(e) {
        botonModal.removeAttribute('disabled')
    })
</script>