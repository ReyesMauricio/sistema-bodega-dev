<?php

use kartik\number\NumberControl;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->title = 'Crear registro';
$this->params['breadcrumbs'][] = ['label' => 'Listado', 'url' => ['index-contenedor']];
$this->params['breadcrumbs'][] = $this->title;


?>
<h1>Crear contenedor</h1>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-truck"></i> &nbsp;Verificar contenedor</h3>
    </div>
    

    <?php $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL]); ?>
    <div class="card-body">
        <form role="form">
            <div class="row">
                <h3>Empresa</h3>
                <?= Html::dropDownList('conjunto', null, ArrayHelper::merge(['' => 'Seleccionar empresa...'], ArrayHelper::map($conjuntos, 'CONJUNTO', 'NOMBRE')), ['class' => 'form-control', 'id' => 'conjunto']) ?>
                </div>
            </div>
            <div class="row" style="margin: 10px;">
                <div class="col-md-4">
                    <?= Html::activeLabel($contenedor, 'fecha_creacion', ['class' => 'control-label']) ?>
                    <?= $form->field($contenedor, 'fecha_creacion', ['showLabels' => false])->widget(DatePicker::class, [
                        'options' => ['placeholder' => 'Seleccionar fecha'],
                        'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-m-dd', 'todayHighlight' => true],
                    ]); ?>
                </div>
                <div class="col-md-3">
                    <?= Html::activeLabel($contenedor, 'contenedor', ['class' => 'control-label']) ?>
                    <?= $form->field($contenedor, 'contenedor', ['showLabels' => false])->textInput(
                        [
                            'maxlength' => true,
                        ]
                    ) ?>
                </div>
                <div class="col-md-4">
                    <?= Html::activeLabel($contenedor, 'bodega', ['class' => 'control-label']) ?>
                    <?= $form->field($contenedor, 'bodega', ['showLabels' => false])->widget(Select2::class, [
                        'data' => ArrayHelper::map($bodegas, 'BODEGA', 'NOMBRE'),
                        'language' => 'es',
                        'options' => ['placeholder' => '- Seleccionar bodega -'],
                        'pluginOptions' => ['allowClear' => true],
                    ]); ?>
                </div>
                <div class="col-md-1 mt-2">
                    <br>
                    <?= Html::submitButton('Verificar', ['class' => 'btn btn-success']) ?>
                </div>
            </div>
        </form>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
// Ruta a la acción de Yii2 que manejará la solicitud AJAX
$url = Yii::$app->urlManager->createUrl(['registro/create-contenedor']);
?>

<script>
$(document).ready(function(){
    var esquema = $('#conjunto').val();
    // Obtener el texto seleccionado
    var textoSeleccionado = $('#conjunto option:selected').text();

    //Por default de deshabilita el dropdown de bodegas
    if (textoSeleccionado === 'Seleccionar empresa...') {
        $('#dynamicmodel-bodega').prop('disabled', true);
    }
    $('#conjunto').change(function(){
        var esquema = $(this).val(); // Obtiene el valor seleccionado
        var textoSeleccionado = $(this).find('option:selected').text(); //Obtiene el texto
        
        $.ajax({
            url: '<?php echo $url ?>',
            method: 'GET',
            data: {esquema: esquema},
            success: function(response){
                //parseamos response para que podamos iterar sobre el 
                data = JSON.parse(response);
                //deshabilita el select en caso que no se selecione una empresa
                if (textoSeleccionado !== 'Seleccionar empresa...') {
                    $('#dynamicmodel-bodega').prop('disabled', false);
                }else{
                    $('#dynamicmodel-bodega').prop('disabled', true);
                }
                // asignamos el id del dropdown a una variable
                var dropdownBodega = $('#dynamicmodel-bodega');
                dropdownBodega.empty(); // Elimina las opciones existentes
                // Agrega las nuevas opciones al dropdown de bodegas
                $.each(data, function(index, item) {
                    console.log("BODEGA: " + item.BODEGA + "NOMBRE: " + item.NOMBRE)
                    dropdownBodega.append($('<option></option>').attr('value', item.BODEGA).text(item.NOMBRE));
                });
                // Actualiza el plugin Select después de cambiar las opciones
                //dropdownBodega.select('destroy').select();
                
                console.log(data);
            },
            error: function(xhr, status, error){
                console.error(xhr.responseText);
                alert("Error: " + xhr.responseText)
            }
        });
    });
});
</script>
