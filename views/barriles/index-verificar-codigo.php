<?php

use kartik\number\NumberControl;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->title = 'Verificar Codigos';
$this->params['breadcrumbs'][] = ['label' => 'Listado', 'url' => ['index-barriles', 'condicionImprimir' => '']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="card card-dark">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-database"></i> &nbsp;Verificar código</h3>
    </div>
    <?php $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL]); ?>
    <div class="card-body">
        <form role="form">
            <div class="row">
                <div class="col-md-12">
                    <label for="form-control">Ingrese los códigos de barra</label>
                    <?= $form->field($barril, 'codigo_barra', ['showLabels' => false])->textarea([
                        'maxlength' => true,
                        'rows' => 4,    
                        'class' => 'form-control',
                        'placeholder' => 'Ingrese códigos de barra FARDOS/PACA...',
                        'id' => 'codigo-barra'
                    ])->hint('Ingrese uno o varios códigos de barra.') ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= Html::submitButton('<i class="fas fa-check"></i> Continuar', [
                        'class' => 'btn btn-success btn-block',
                        'title' => 'Continuar con la verificación',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top'
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= Html::a('<i class="fas fa-redo"></i> Reiniciar', ['index-verificar-codigo', 'barril' => $barril], [
                        'class' => 'btn btn-danger btn-block',
                        'title' => 'Reiniciar el formulario',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top'
                    ]) ?>
                </div>
            </div>
        </form>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$script = <<< JS
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip(); // Inicializar tooltips
});
JS;
$this->registerJs($script);
?>
