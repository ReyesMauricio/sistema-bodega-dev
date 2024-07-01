<?php
Yii::$app->language = 'es_ES';

use app\models\TransaccionModel;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use kartik\export\ExportMenu;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OsigSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Listado de contenedores';
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
                    <h5><i class="fas fa-truck"></i> &nbsp;Contenedores</h5>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="float-right p-2">
                    <?php
                    echo Html::a('<i class="fas fa-plus"></i> Agregar', ['create-contenedor'], [
                        'class' => 'btn btn-success',
                        'data-pjax' => 0,
                    ]) . ' ' .
                        Html::a('<i class="fas fa-redo"></i>', ['index-contenedor'], [
                            'class' => 'btn btn-outline-success',
                            'data-pjax' => 0,
                        ])
                    ?>
                </div>
                <table class="table table-hover table-bordered">
                    <thead class="thead-light text-center">
                        <th>#</th>
                        <th>Contenedor</th>
                        <th>Bodega</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Cantidad de fardos</th>
                        <th>Usuario creacion</th>
                        <th>Acciones</th>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($dataProvider as $index => $registro) { ?>
                            <tr class="text-center">
                                <td><?= ($index + 1) ?></td>
                                <td><?= Html::tag('span', $registro["NumeroDocumento"], ['class' => 'badge bg-purple']); ?></td>
                                <td><?= Html::tag('span', $registro["Bodega"], ['class' => 'badge bg-info']); ?></td>
                                <td><?php
                                    if ($registro["Estado"] == "P") {
                                        echo Html::tag('span', "En proceso", ['class' => 'badge bg-success']);
                                    } else if ($registro["Estado"] == "F") {
                                        echo Html::tag('span', "Finalizado", ['class' => 'badge bg-primary']);
                                    } else if ($registro["Estado"] == "ELIMINADO") {
                                        echo Html::tag('span', "E", ['class' => 'badge bg-danger']);
                                    }
                                    ?></td>
                                <td><?= Html::tag('span', $registro["Fecha"], ['class' => 'badge bg-purple']); ?></td>
                                <td><?= $registro["IdTransaccion"] ?></td>
                                <td><?= $registro["UsuarioCreacion"] ?></td>
                                <td>
                                    <?php
                                    echo Html::a(
                                        '<span class="fas fa-eye"></span>',
                                        [
                                            'view-contenedor',
                                            'contenedor' => $registro["NumeroDocumento"],
                                            'fecha' => $registro["Fecha"],
                                            'bodega' => $registro["Bodega"],
                                            'estado' => $registro["Estado"],
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
