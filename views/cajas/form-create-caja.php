<?php

use app\models\TipoEmpaqueModel;
use kartik\number\NumberControl;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->title = 'Crear registro';
$this->params['breadcrumbs'][] = ['label' => 'Listado', 'url' => ['index-cajas', 'condicionImprimir' => '']];
$this->params['breadcrumbs'][] = $this->title;


?>
<h1>Crear cajas</h1>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-cube"></i> &nbsp;Crear cajas</h3>
    </div>
    <?php $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL]); ?>
    <div class="card-body">
        <form role="form">
            <div class="row">
                <div class="col-md-6">
                    <?= Html::activeLabel($caja, 'bodega', ['class' => 'control-label']) ?>
                    <?= $form->field($caja, 'bodega', ['showLabels' => false])->widget(Select2::class, [
                        'data' => ArrayHelper::map($bodegas, 'BODEGA', 'NOMBRE'),
                        'language' => 'es',
                        'disabled' => $detalle,
                        'options' => ['placeholder' => '- Seleccionar bodega -'],
                        'pluginOptions' => ['allowClear' => true],
                    ]); ?>
                </div>
                <div class="col-md-12">
                    <?= Html::activeLabel($caja, 'codigo_barra', ['class' => 'control-label']) ?>
                    <?= $form->field($caja, 'codigo_barra', ['showLabels' => false])->textarea(
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
    <div class="col-md-6">
        <label for="tipo-empaque">Seleccionar tipo de empaque</label>
        <?= Select2::widget([
            'name' => 'tipo-empaque',
            'id' => 'tipo-empaque',
            'data' => ArrayHelper::map(TipoEmpaqueModel::find()->where('IdTipoEmpaque in (1,3)')->all(), 'IdTipoEmpaque', 'TipoEmpaque'),
            'language' => 'es',
            'options' => ['placeholder' => '- Seleccionar bodega -'],
            'pluginOptions' => ['allowClear' => true],
        ]); ?>
    </div>
    <table class="table table-bordered table-hover text-center mt-2" id="tabla-codigos-barra">
        <thead class="thead-dark">
            <th>Codigo de barra</th>
            <th>Articulo</th>
            <th>Clasificacion</th>
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
                    <td id="libras" data-libras="<?= $registro[4] ?>"><?= $registro[4] . 'lb.' ?></td>
                </tr>
            <?php
                
            } ?>
            <tr class="bg-dark">
                <td colspan="3" class="text-right">Total</td>
                <td><?= $totalLibras ?></td>
            </tr>
        </tbody>
    </table>
    <div class="row">
        <div class="col-md-12 d-flex justify-content-center my-2">
            <?php
            $form = ActiveForm::begin([
                'action' => Yii::$app->request->baseUrl . '/index.php?r=cajas/create-detalle&registros=' . $registrosJson . '&totalLibrasBarriles=' . $totalLibras . '&totalCostoBarriles=' . $totalCosto,
                'type' => ActiveForm::TYPE_HORIZONTAL,
                'method' => 'POST',
                'id' => 'form-crear-caja'
            ]); ?>
            <button class=" btn btn-danger" id="boton-finalizar-caja" type="submit">Finalizar</button>
            <?php ActiveForm::end(); ?>

        </div>
    </div>

    <script>
        let formularioCaja = document.getElementById('form-crear-caja')
        let botonFinalizarCaja = document.getElementById('boton-finalizar-caja')

        botonFinalizarCaja.addEventListener('click', (e) => {
            e.preventDefault();
            let empaque = document.getElementById('tipo-empaque').value
            if (empaque.length == 0) {
                Swal.fire({
                    title: 'Selecciona un empaque',
                    icon: 'warning',
                    confirmButtonText: 'Ok',
                    confirmButtonColor: '#3085d6',
                })
                return
            }
            Swal.fire({
                title: '¿Estas seguro de crear este registro?',
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

                    formularioCaja.action += '&empaque=' + empaque;
                    formularioCaja.submit();
                }
            })
        })
    </script>
<?php } ?>