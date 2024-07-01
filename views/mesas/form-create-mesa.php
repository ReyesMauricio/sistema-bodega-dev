<?php

use app\models\TipoEmpaqueModel;
use kartik\number\NumberControl;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->title = 'Crear registro';
$this->params['breadcrumbs'][] = ['label' => 'Listado', 'url' => ['index-mesas']];
$this->params['breadcrumbs'][] = $this->title;


?>
<h1>Crear trabajo de mesa</h1>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-table"></i> &nbsp;Crear trabajo de mesa</h3>
    </div>
    <?php $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL]); ?>
    <div class="card-body">
        <form role="form">
            <div class="row">
                <div class="col-md-4">
                    <?= Html::activeLabel($mesa, 'bodega', ['class' => 'control-label']) ?>
                    <?= $form->field($mesa, 'bodega', ['showLabels' => false])->widget(Select2::class, [
                        'data' => ArrayHelper::map($bodegas, 'BODEGA', 'NOMBRE'),
                        'language' => 'es',
                        'disabled' => $detalle,
                        'options' => [
                            'placeholder' => '- Seleccionar bodega -',
                            'value' => $bodegas[1],
                            'readonly' => true
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            
                        ],
                    ]); ?>
                </div>
                <div class="col-md-4">
                    <?= Html::activeLabel($mesa, 'mesa', ['class' => 'control-label']) ?>
                    <?= $form->field($mesa, 'mesa', ['showLabels' => false])->widget(Select2::class, [
                        'data' => ArrayHelper::map([
                            ['numero' => 6, 'titulo' => '6 - Mesa de produccion'],
                            ['numero' => 7, 'titulo' => '7 - Mesa de produccion'],
                            ['numero' => 8, 'titulo' => '8 - Mesa de produccion'],
                            ['numero' => 9, 'titulo' => '9 - Mesa de produccion'],
                            ['numero' => 10, 'titulo' => '10 - Mesa de produccion'],
                            ['numero' => 11, 'titulo' => '11 - Mesa de produccion'],
                            ['numero' => 12, 'titulo' => '12 - Mesa de produccion'],
                            ['numero' => 13, 'titulo' => '13 - Mesa de produccion'],
                            ['numero' => 14, 'titulo' => '14 - Mesa de produccion'],
                            ['numero' => 15, 'titulo' => '15 - Mesa de produccion'],
                            ['numero' => 16, 'titulo' => '16 - Mesa de produccion'],
                            ['numero' => 17, 'titulo' => '17 - Mesa de produccion'],
                            ['numero' => 18, 'titulo' => '18 - Mesa de produccion'],
                            ['numero' => 19, 'titulo' => '19 - Mesa de produccion'],
                            ['numero' => 20, 'titulo' => '20 - Mesa de produccion'],

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
                            'rows' => 10
                        ]
                    ) ?>
                </div>
                <div class="col-md-1 mt-2">
                    <br>
                    <?= Html::submitButton('Verificar', ['class' => 'btn btn-success', 'disabled' => $detalle,]) ?>
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
            <th>Codigo de barra</th>
            <th>Articulo</th>
            <th>Clasificacion</th>
            <th>Tipo de empaque</th>
            <th>Libras</th>
        </thead>
        <tbody>
            <?php
            foreach ($registros as $registro) {
                $registro = explode(", ", $registro);
            ?>
                <tr>
                    <td><?= $registro[0] ?></td>
                    <td><?= $registro[1]  . ' - ' . $registro[2] ?></td>
                    <td><?= $registro[3] ?></td>
                    <td><?= $registro[7] ?></td>
                    <td><?= $registro[4] . 'lb.' ?></td>
                </tr>
            <?php } ?>
            <tr class="bg-dark">
                <td colspan="4" class="text-right">Total</td>
                <td><?= $totalLibras ?></td>
            </tr>
        </tbody>
    </table>
    <div class="row">
        <div class="col-md-12 d-flex justify-content-center my-2">
            <?php
            $form = ActiveForm::begin([
                'action' => Yii::$app->request->baseUrl . '/index.php?r=mesas/salida-inventario-mesa&registros=' . $registrosJson . "&mesa=" . $mesa->mesa . "&productores=" . json_encode($mesa->ProducidoPor) . "&fecha=" . $mesa->fecha,
                'type' => ActiveForm::TYPE_HORIZONTAL,
                'method' => 'POST',
                'id' => 'form-asignar-mesa'
            ]); ?>
            <button class=" btn btn-danger" id="asignar-mesa" type="submit">Finalizar</button>
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
                        formAsignarMesa.submit();
                    }
                })
            })
        </script>
    </div>
<?php } ?>