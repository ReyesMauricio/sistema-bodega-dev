<?php

use app\models\UsuarioModel;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Buscar informacion de codigo de barra';
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
        <h3 class="card-title"><i class="fas fa-briefcase"></i> &nbsp;Buscar informacion de codigo de barra</h3>

    </div>
    <?php $form = ActiveForm::begin(['id' => 'detalle-form', 'type' => ActiveForm::TYPE_HORIZONTAL]); ?>
    <div class="card-body">
        <form role="form">
            <div class="row">
                <div class="col-md-6">
                    <label for="codigo-barra">Codigo de barra</label>
                    <input class="form-control" type="text" placeholder="Escriba el codigo de barra" name="codigo-barra" id="codigo-barra">
                </div>
                <div class="col-md-12 mt-2">
                    <br>
                    <?= Html::button('<i class="fa fa-plus"></i> Generar reporte', [
                        'value' => Url::to('index.php?r=reportes/crear-reporte-codigo-barra'),
                        'class' => 'btn btn-warning',
                        'id' => 'modalButton',
                        'disabled' => true
                    ]) ?>
                    <?= Html::a('<i class="fas fa-redo">&nbsp;&nbsp;Reiniciar</i>', ['reporte-buscar-codigo'], [
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

    let codigoBarra = document.getElementById('codigo-barra')

    codigoBarra.addEventListener('input', (e) => {
        if (codigoBarra.value.length > 10) {
            eliminarAtributo(botonModal, 'disabled')
            codigoBarra = codigoBarra.value
            botonModal.value += `&fecha=&codigoBarra=${String(codigoBarra).trim()}&persona=`
        } else {
            botonModal.value = href
            agregarAtributo(botonModal, 'disabled', '')
        }
    })

    codigoBarra.addEventListener("keypress", function(event) {
        if (event.key === "Enter") {
            event.preventDefault();
            botonModal.click()
        }
    });

    function eliminarAtributo(elemento, atributo) {
        elemento.removeAttribute(atributo)
    }

    function agregarAtributo(elemento, atributo, propiedad) {
        elemento.setAttribute(atributo, propiedad)
    }
</script>