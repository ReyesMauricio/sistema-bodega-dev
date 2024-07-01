<?php

use app\models\UsuarioModel;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Obtener informacion de codigos de barra';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-briefcase"></i> &nbsp;<?= $this->title ?></h3>

    </div>
    <?php $form = ActiveForm::begin([
        'id' => 'detalle-form', 
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'action' => Url::to(['reportes/crear-reporte-busqueda-multiple'])
        ]); ?>
    <div class="card-body">
        <form role="form">
            <div class="row">
                <div class="col-md-12">
                    <?= Html::activeLabel($model, 'codigos', ['class' => 'control-label']) ?>
                    <?= $form->field($model, 'codigos', ['showLabels' => false])->textarea(
                        [
                            'id' => 'codigos',
                            'maxlength' => true,
                            'rows' => 10
                        ]
                    ) ?>
                </div>
                <div class="col-md-12 mt-2">
                    <br>
                    <?= Html::submitButton(
                        '<i class="fa fa-download"></i> Descargar reporte',
                        [
                            'id' => 'boton-generar-reporte',
                            'class' => 'btn btn-warning'
                        ]
                    ) ?>
                    <?= Html::a('<i class="fas fa-redo">&nbsp;&nbsp;Reiniciar</i>', ['reporte-busqueda-multiple'], [
                        'class' => 'btn btn-outline-success',
                        'data-pjax' => 0,
                        'id' => 'btn-reset'
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
    let codigos = document.getElementById('codigos')
    let submitBtnCodigos = document.getElementById('boton-generar-reporte')
    let hrefBaseBtnSubmit = submitBtnCodigos.href

    submitBtnCodigos.addEventListener('click', (e)=>{
        setTimeout(() => {
            document.getElementById('codigos').value = ''
        }, 1000);
        
    })
</script>
