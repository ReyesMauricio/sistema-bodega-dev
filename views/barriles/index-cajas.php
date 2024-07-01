<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Cajas';
$this->params['breadcrumbs'][] = ['label' => 'Listado', 'url' => ['index-lista-cajas']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card card-dark bg-light rounded shadow-sm"> <!-- Añadí clase rounded para bordes redondeados y shadow-sm para una ligera sombra -->
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-box"></i> &nbsp;<?= Html::encode($this->title) ?></h3>
    </div>
    <?php $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL]); ?>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($caja, 'codigo_barra', ['showLabels' => false])->textarea(
                    [
                        'maxlength' => true,
                        'rows' => 4,
                        'class' => 'form-control',
                        'placeholder' => 'Ingrese los códigos de barras: BARRILES/PRODUCCIÓN' // Añadí un placeholder para guiar al usuario
                    ]
                ) ?>
            </div>
            <div class="col-md-6">
                <?= Html::submitButton('<i class="fas fa-arrow-circle-right"></i> Continuar', ['class' => 'btn btn-success btn-block']) ?> <!-- Añadí un ícono al botón -->
            </div>
            <div class="col-md-6">
                <?= Html::a('<i class="fas fa-redo-alt"></i> Reiniciar', ['index-cajas', 'barril' => $caja], ['class' => 'btn btn-danger btn-block'])?> <!-- Añadí un ícono al botón -->
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

