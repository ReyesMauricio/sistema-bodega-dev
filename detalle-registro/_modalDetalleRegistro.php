<?php

use kartik\number\NumberControl;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

Yii::$app->formatter->locale = 'en-US';
$this->title = 'Actualizar registro';
$this->params['breadcrumbs'][] = ['label' => 'Listado', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Detalle', 'url' => ['view', 'codigoBarra' => $model->idRegistro->CodigoBarra, 'condicionImprimir' => '']];
$this->params['breadcrumbs'][] = $this->title;

?>


<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header bg-primary">
                <h5><?= $model->isNewRecord ? 'Crear' : 'Actualizar' ?> registro</h5>
            </div>
            <?php $form = ActiveForm::begin(['id' => 'modal_datos', 'type' => ActiveForm::TYPE_HORIZONTAL]); ?>
            <div class="card-body">
                <form role="form">
                    <div class="row">
                        <div class="col-md-4">
                            <?= Html::activeLabel($model, 'ArticuloDetalle', ['class' => 'control-label']) ?>
                            <?= $form->field($model, 'ArticuloDetalle', ['showLabels' => false])->widget(Select2::class, [
                                'data' => ArrayHelper::map($articulos, 'ARTICULO', 'ACTIVO'),
                                'language' => 'es',
                                'options' => [
                                    'placeholder' => '-- Seleccionar articulo --',
                                ],
                                'pluginOptions' => ['allowClear' => true],
                            ]); ?>
                            <?= $form->field($model, 'IdRegistro', ['showLabels' => false])->hiddenInput(['maxlength' => true, 'readonly' => true]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= Html::activeLabel($model, 'Cantidad', ['class' => 'control-label']) ?>
                            <?= $form->field($model, 'Cantidad', ['showLabels' => false])->widget(NumberControl::class, [
                                'name' => 'Cantidad',
                            ]); ?>
                        </div>
                        <div class="col-md-4">
                            <?= Html::activeLabel($model, 'PrecioUnitario', ['class' => 'control-label']) ?>
                            <?= $form->field($model, 'PrecioUnitario',  ['showLabels' => false])->widget(NumberControl::class, [
                                'name' => 'PrecioUnitario',
                                'readonly' => true
                            ]);
                            ?>
                        </div>
                    </div>
                    <div class="card-footer">
                        <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-save"></i> Guardar' : '<i class="fa fa-save"></i> Actualizar', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'id' => 'btn-guardar']) ?>

                        <?php
                        if ($model->isNewRecord) {
                            echo Html::button('<i class="fa fa-ban"></i> Cancelar', ['class' => 'btn btn-danger', 'data-dismiss' => 'modal']);
                        } else {
                            echo Html::a(
                                '<i class="fa fa-ban"></i> &nbsp;Regresar',
                                ['view', 'codigoBarra' => $model->idRegistro->CodigoBarra, 'condicionImprimir' => ''],
                                ['class' => "btn btn-danger"]
                            );
                        }
                        ?>
                    </div>
                </form>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<script>
    $('#detalleregistromodel-articulodetalle').on('change', function(e) {
        let data = $("#detalleregistromodel-articulodetalle option:selected").text();
        data = data.split(" $ ")


        $('#detalleregistromodel-preciounitario-disp').val(data[1])
    })

    $('form#modal_datos').on('beforeSubmit', function(e) {
        var $form = $(this);
        $.post($form.attr("action"), $form.serialize())
            .done(function(result) {
                if (result == 1) {
                    $.pjax.reload({
                        container: '#datosGrid-detalle'
                    });
                    $('#modal_datos').trigger('reset');
                    $('#detalleregistromodel-articulodetalle').focus()
                    $('#detalleregistromodel-articulodetalle').click()
                } else {
                    $('#message').html(result);
                }
            }).fail(function() {
                console.log("Server Error");
            });
        return false;
    });

    $('#btn-guardar').on('focus', function (e){
        $('#btn-guardar').removeClass('btn-success')
        $('#btn-guardar').addClass('btn-primary')
    })

    $('#btn-guardar').on('blur', function (e){
        $('#btn-guardar').removeClass('btn-primary')
        $('#btn-guardar').addClass('btn-success')
    })
</script>