<?php

use kartik\number\NumberControl;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->title = 'Agregar detalle';
$this->params['breadcrumbs'][] = ['label' => 'Listado', 'url' => ['index-contenedor']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Verificar contenedor</h3>
    </div>
    <?php $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL]); ?>
    <div class="card-body">
        <form role="form">
            <div class="row">
                    
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label for="fecha_creacion">Fecha</label>
                    <?= DatePicker::widget([
                        'name' => 'fecha_creacion',
                        'disabled' =>  true,
                        'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                        'value' => $fecha,
                        'pluginOptions' => [
                            'autoclose' => true,
                        ]
                    ]); ?>
                </div>
                <div class="col-md-4">
                    <label for="contenedor">Contenedor</label>
                    <input type="text" class="form-control" disabled name="contenedor" value="<?= $contenedor ?>">
                </div>
                <div class="col-md-4">
                    <label for="bodega">Bodega</label>
                    <?= Select2::widget([
                        'name' => 'bodega',
                        'language' => 'es',
                        'disabled' =>  true,
                        'value' => $bodega,
                        'options' => ['placeholder' => '- Seleccionar Representante -'],
                        'pluginOptions' => ['allowClear' => true],
                    ]); ?>
                </div>
            </div>
        </form>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
if ($estado == 'P') {
    $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'method' => 'POST'
    ]); ?>
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Agregar detalle de contenedor</h3>
        </div>
        <div class="card-body">
            <form role="form">
                <div class="row">
                    <div class="col-md-6">
                        <?= Html::activeLabel($detalleContenedor, 'articulo', ['class' => 'control-label']) ?>
                        <?= $form->field($detalleContenedor, 'articulo', ['showLabels' => false])->widget(Select2::class, [
                            'name' => 'datatry',
                            'data' => ArrayHelper::map($articulos, 'ARTICULO', 'ARTICULO_DESCRIPCION'),
                            'language' => 'es',
                            'options' => ['placeholder' => '- Seleccionar Artículo -'],
                            'pluginOptions' => ['allowClear' => true],
                        ]); ?>
                    </div>
                    <div class="col-md-2">
                        <?= Html::activeLabel($detalleContenedor, 'cantidad', ['class' => 'control-label']) ?>
                        <?= $form->field($detalleContenedor, 'cantidad',  ['showLabels' => false])->widget(NumberControl::class, [
                            'name' => 'cantidad',
                        ]);
                        ?>
                    </div>
                    <div class="col-md-2">
                        <?= Html::activeLabel($detalleContenedor, 'peso', ['class' => 'control-label']) ?>
                        <?= $form->field($detalleContenedor, 'peso',  ['showLabels' => false])->widget(NumberControl::class, [
                            'name' => 'peso',
                        ]);
                        ?>
                    </div>
                    <div class="col-md-2 mt-2 ">
                        <br>
                        <?= Html::submitButton('Generar', ['class' => 'btn btn-success']) ?>
                        <?= $estado == 'F'
                            ? Html::a('Regresar', ['index-contenedor'], ['class' => 'btn btn-danger'])
                            : Html::a('Reiniciar', ['view-contenedor', 'fecha' => $fecha, 'contenedor' => $contenedor, 'bodega' => $bodega, 'estado' => $estado, ''], ['class' => 'btn btn-danger'])
                        ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php ActiveForm::end();
} ?>

<?php

if (count($data) > 0 && ($estado == 'P' || $estado == 'F')) { ?>
    <?= count($data);?>
    <div class="row">
    <table class="table table-bordered table-hover text-center bg-light">
        <thead class="thead-dark">
            <th>Articulo</th>
            <th>Empresa</th>
            <th>Descripcion</th>
            <th>Clasificacion</th>
            <th>Cantidad</th>
            <th>Peso</th>
            <th>Total</th>
            <th>Fecha de creación</th>
            <th>Acciones</th>
        </thead>
        <tbody>
            <?php foreach ($data as $detalle) { ?>
                <tr>
                    <?= '<td>' . $detalle['Articulo'] . '</td>' ?>
                    <?= '<td>' . $detalle['Empresa'] . '</td>' ?>
                    <?= '<td>' . $detalle['Descripcion'] . '</td>' ?>
                    <?= '<td>' . $detalle['Clasificacion'] . '</td>' ?>
                    <?= '<td>' . $detalle['Cantidad'] . '</td>' ?>
                    <?= '<td>' . $detalle['Libras'] . ' Lbs.</td>' ?>
                    <?= '<td>' . ($detalle['Cantidad'] * $detalle['Libras']) . ' Lbs.</td>' ?>
                    <?= '<td>' . $detalle['FechaCreacion'] . '</td>' ?>

                    <td class="d-flex justify-content-around">
                        <?php if ($estado == 'P') { ?>
                            <?php

                            $form = ActiveForm::begin([
                                'action' => Yii::$app->request->baseUrl . '/index.php?r=registro/delete-contenedor&contenedor=' . $contenedor . '&fecha=' . $detalle['FechaCreacion'] . '&articulo=' . $detalle['Articulo'] . '&bodega=' . $bodega . '&libras=' . $detalle['Libras']. '&empresa=' . $detalle['Empresa'] .'',
                                'type' => ActiveForm::TYPE_HORIZONTAL,
                                'method' => 'POST',
                                'id' => 'deleteContenedor'
                            ]); ?>
                            <button class="btn text-blue" type="submit"><i class="fas fa-trash"></i></button>
                            <?php ActiveForm::end(); ?>

                        <?php } ?>
                        <div class="p-2">
                            <a href="<?= substr(Yii::$app->request->baseUrl, 0, -3)?>views/registro/pdf-contenedor.php?contenedor=<?= $contenedor ?>&fecha=<?= $detalle['FechaCreacion'] ?>&articulo=<?= $detalle['Articulo'] ?>" target="_blank">
                                <i class="fas fa-print"></i>
                            </a>
                        </div>
                    </td>

                <?php } ?>
                </tr>
        </tbody>
    </table>
    </div>
<?php } ?>
<script>
    let registro = document.querySelectorAll("#deleteContenedor");
    registro.forEach((item) => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            Swal.fire({
                title: '¿Estas seguro de eliminar este registro?',
                showCancelButton: true,
                icon: 'question',
                confirmButtonText: 'Eliminar',
                cancelButtonText: `Cancelar`,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
            }).then((result) => {
                if (result.isConfirmed) {
                    item.submit();
                }
            })
        })
    })
</script>