<?php

use kartik\number\NumberControl;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Crear registro';
$this->params['breadcrumbs'][] = ['label' => 'Listado', 'url' => ['index-mesas']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="card card-dark rounded bg-white shadow-lg">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-table"></i> &nbsp;Asignar libras a mesa</h3>
    </div>
    <?php $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL]); ?>
    <div class="card-body">
        <form role="form">
            <div class="row">
                <div class="col-md-4">
                <?= Html::activeLabel($mesa, 'bodega', ['class' => 'control-label']) ?>
                    <?= $form->field($mesa, 'bodega', ['showLabels' => false])->textInput([
                        'value' => 'SM00 - Bodega Principal',
                        'readonly' => true
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= Html::activeLabel($mesa, 'mesa', ['class' => 'control-label']) ?>
                    <?= $form->field($mesa, 'mesa', ['showLabels' => false])->widget(Select2::class, [
                        'data' => ArrayHelper::map([
                            ['numero' => 1, 'titulo' => '1 - Mesa de asignacion'],
                            ['numero' => 2, 'titulo' => '2 - Mesa de asignacion'],
                            ['numero' => 3, 'titulo' => '3 - Mesa de asignacion'],
                            ['numero' => 4, 'titulo' => '4 - Mesa de asignacion'],
                            ['numero' => 5, 'titulo' => '5 - Mesa de asignacion'],

                        ], "numero", "titulo"),                'language' => 'es',
                        'options' => ['placeholder' => '- Seleccionar mesa -'],
                        'pluginOptions' => ['allowClear' => true,],
                        'disabled' => $detalle,
                    ]); ?>
                </div>
                <div class="col-md-4">
                    <?= Html::activeLabel($mesa, 'fecha', ['class' => 'control-label']) ?>
                    <?= $form->field($mesa, 'fecha', ['showLabels' => false])->widget(DatePicker::class, [
                        'disabled' => $detalle,
                        'language' => 'es',
                        'options' => ['placeholder' => '- Seleccionar fecha -', 'value' => date('Y-m-d'),],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'autoclose' => true,
                            'format' =>
                            'yyyy-m-dd',
                            'todayHighlight' => true,
                            'endDate' => "+1d"
                        ],
                    ]); ?>
                </div>
                <div class="col-md-12 mt-2">
                    <label for="productores">Seleccione las personas que producen:</label>
                    <?= $form->field($mesa, 'ProducidoPor', ['showLabels' => false])->widget(Select2::class, [
                        'disabled' => $detalle,
                        'data' => ArrayHelper::map($productores, 'Nombre', 'Nombre'),
                        'language' => 'es',
                        'options' => [
                            'placeholder' => '- Seleccionar productor -',
                            'multiple' => true,
                        ],
                        'pluginOptions' => ['allowClear' => true],
                    ]); ?>
                </div>
                <div class="col-md-12">
                    <?= Html::activeLabel($mesa, 'codigos_barra', ['class' => 'control-label']) ?>
                    <?= $form->field($mesa, 'codigos_barra', ['showLabels' => false])->textarea(
                        [
                            'maxlength' => true,
                            'disabled' => $detalle,
                            'rows' => 3
                        ]
                    ) ?>
                </div>
                <!-- <div class="col-md-12">
                    <label for="" class="control-label">Numero Documento:</label>
                    <input type="text" style="font-weight: bold;" class="form-control text-md-center" value="" disabled>
                </div> -->
                
            </div>
            <br>
            <div class="row">
                    <div class="col-md-12">
                        <?= Html::submitButton('<i class="fas fa-check-circle"></i> Verificar', ['class' => 'btn btn-primary btn-block',
                        'disabled' => $detalle,
                        'formaction' => Url::to(['mesas/verificar-libras'])
                        ]) ?>
                    </div>
            </div>
        </form>
        <?php
        ActiveForm::end();
        ?>
    </div>
</div>

<?php

if ($detalle) { ?>
    <div class="row">

    </div>

    <table class="table table-bordered table-hover text-center mt-4" id="tabla-codigos-barra">
    <thead class="thead-dark">
        <tr>
            <th>Código de barra</th>
            <th>Artículo</th>
            <th>Clasificación</th>
            <th>Tipo de empaque</th>
            <th>Libras</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($registros as $registro) {
            $registro = explode(", ", $registro);
        ?>
            <tr>
                <td><?= htmlspecialchars($registro[0]) ?></td>
                <td><?= htmlspecialchars($registro[1] . ' - ' . $registro[2]) ?></td>
                <td><?= htmlspecialchars($registro[3]) ?></td>
                <td><?= htmlspecialchars($registro[7]) ?></td>
                <td><?= htmlspecialchars($registro[4]) . ' lb' ?></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="text-right"><strong>Total</strong></td>
            <td><strong><?= htmlspecialchars($totalLibras) ?></strong></td>
        </tr>
    </tfoot>
</table>

    <div class="row">
        <div class="col-md-12">
        <?php
            $form = ActiveForm::begin([
                'action' => Url::to(['mesas/create-asignacion', 
                        'registros' => $registrosJson, 
                        'mesa' => $mesa->mesa, 
                        'productores' => json_encode($mesa->ProducidoPor), 
                        'fecha' => $mesa->fecha]),
                'type' => ActiveForm::TYPE_HORIZONTAL,
                'method' => 'POST',
                'id' => 'form-asignar-mesa'
            ]); ?>
            <button class="btn btn-danger btn-block" id="asignar-mesa" type="submit">
                <i class="fa fa-check-circle"></i> Asignar
            </button>
        <?php ActiveForm::end(); ?>
        </div>

        <script>
            let finalizarAsignacion = document.getElementById('asignar-mesa')
            let formAsignarMesa = document.getElementById('form-asignar-mesa')

            finalizarAsignacion.addEventListener('click', (e) => {
                e.preventDefault();
                Swal.fire({
                    title: '¿Estas seguro de asignar estos codigos?',
                    text: 'No podras revertir este registro',
                    showCancelButton: true,
                    icon: 'question',
                    confirmButtonText: 'Continuar',
                    cancelButtonText: `Cancelar`,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    focusCancel: true,
                    cancelButtonColor: '#d33',
                    customClass: {
                        actions: 'vertical-buttons',
                        cancelButton: 'order-1 right-gap',
                        confirmButton: 'order-2',
                    }
                }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                            title: 'Espere por favor',
                            text: 'Procesando transacción...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        formAsignarMesa.submit();
                    }
                })
            })
        </script>
    </div>

<?php } ?>
