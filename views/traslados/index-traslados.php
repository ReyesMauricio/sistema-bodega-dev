<?php
Yii::$app->language = 'es_ES';

use app\models\RegistroModel;
use app\models\TransaccionModel;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use kartik\export\ExportMenu;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OsigSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Listado de movimientos';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <div class="float-right">
                    <div class="summary">Mostrando <b><?= count($dataProvider) ?></b> elementos.</div>
                </div>
                <div class="m-0">
                    <h5><i class="fas fa-briefcase"></i> &nbsp; <?= $this->title ?></h5>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="float-right p-2">
                    <?php
                    echo Html::a('<i class="fas fa-plus"></i> Crear traslado', ['create-traslado'], [
                        'class' => 'btn btn-success',
                        'data-pjax' => 0,
                    ]) . '&nbsp;&nbsp;';
                    echo Html::a('<i class="fas fa-plus"></i> Crear despacho', ['create-despacho'], [
                        'class' => 'btn btn-primary',
                        'data-pjax' => 0,
                    ]) . '&nbsp;&nbsp;';
                    echo Html::a('<i class="fas fa-redo"></i>', ['index-traslados'], [
                        'class' => 'btn btn-outline-warning',
                        'data-pjax' => 1,
                        'id' => 'redo-index-prod'
                    ]);
                    ?>
                </div>
                <table class="table table-hover table-bordered">
                    <thead class="thead-light text-center">
                        <th>#</th>
                        <th>Tipo de movimiento</th>
                        <th>Estado</th>
                        <th>Bodega de origen</th>
                        <th>Bodega destino</th>
                        <th>Total fardos</th>
                        <th>Documento inventario</th>
                        <th>Fecha de movimiento</th>
                        <th>Fecha de creacion</th>
                        <th>Acciones</th>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($dataProvider as $index => $registro) { ?>
                            <tr class="text-center">
                                <td><?= ($index + 1) ?></td>
                                <td><?php
                                    if ($registro->TipoMovimiento == "D") {
                                        echo Html::tag('span', "Despacho", ['class' => 'badge bg-primary']);
                                    } else if ($registro->TipoMovimiento == "T") {
                                        echo Html::tag('span', "Traslado", ['class' => 'badge bg-primary']);
                                    }
                                    ?>
                                </td>
                                <td><?php
                                    if ($registro->Estado == "PROCESO") {
                                        echo Html::tag('span', "En proceso", ['class' => 'badge bg-warning']);
                                    } else if ($registro->Estado == "FINALIZADO") {
                                        echo Html::tag('span', "Finalizado", ['class' => 'badge bg-primary']);
                                    }
                                    ?>
                                </td>
                                <td><?= Html::tag('span', $registro->origen, ['class' => 'badge bg-danger']); ?></td>
                                <td><?= Html::tag('span', $registro->destino, ['class' => 'badge bg-success']); ?></td>
                                <td><b><?= count($registro->detalleMovimiento)?></b></td>
                                <td><?= $registro->Documento_inv == NULL ? 'No definido' : $registro->Documento_inv ?></td>
                                <td><?= $registro->Fecha ?></td>
                                <td><?= $registro->CreateDate ?></td>
                                <td>
                                    <?php
                                    echo Html::a(
                                        '<span class="fas fa-eye"></span>',
                                        [
                                            'view-traslados',
                                            'IdMovimiento' => $registro->IdMovimiento,
                                        ],
                                    );
                                    ?>
                                </td>
                            </tr>
                        <?php }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    let borrarProd = document.querySelectorAll("#delete-registro-prod")
    let recargarPagina = document.getElementById('redo-index-prod')

    borrarProd.forEach((elemento) => {
        elemento.addEventListener("click", (e) => {
            e.preventDefault()
            Swal.fire({
                title: 'Estas seguro de eliminar este registro?',
                showCancelButton: true,
                icon: 'error',
                confirmButtonText: 'Continuar',
                cancelButtonText: `Cancelar`,
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        method: "GET",
                        url: `${elemento.href}`,
                        success: function() {
                            recargarPagina.click
                        }
                    })
                }
            })
        })
    })
</script>