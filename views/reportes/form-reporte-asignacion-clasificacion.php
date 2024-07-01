<?php

use app\models\UsuarioModel;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Codigos asignados a mesas de clasificacion';
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
                    <?= Html::button('<i class="fa fa-plus"></i> Generar reporte', [
                        'value' => Url::to('index.php?r=reportes/crear-reporte-asignacion-clasificacion'),
                        'class' => 'btn btn-warning',
                        'id' => 'modalButton',
                    ]) ?>
                    <?= Html::a('<i class="fas fa-redo">&nbsp;&nbsp;Reiniciar</i>', ['reporte-asignacion-clasificacion'], [
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
    let fechaInicio = document.getElementById('fechaInicio')
    let fechaFin = document.getElementById('fechaInicio-2')
    let hrefBase = botonModal.value
    botonModal.value += `&fechaInicio=${fechaInicio.value}&fechaFin=${fechaFin.value}`;

    $('#fechaInicio').on('change', function(e) {
        botonModal.value = ''
        botonModal.value += `${hrefBase}&fechaInicio=${fechaInicio.value}&fechaFin=${fechaFin.value}`;
    })
    $('#fechaInicio-2').on('change', function(e) {
        botonModal.value = ''
        botonModal.value += `${hrefBase}&fechaInicio=${fechaInicio.value}&fechaFin=${fechaFin.value}`;
    })
</script>