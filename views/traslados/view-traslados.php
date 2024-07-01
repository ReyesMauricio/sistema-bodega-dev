<?php

use yii\helpers\Html;
use yii\helpers\Url;

Yii::$app->formatter->locale = 'en-US';
$this->title = 'Detalle';
$this->params['breadcrumbs'][] = ['label' => 'Listado', 'url' => ['index-traslados']];
$this->params['breadcrumbs'][] = $this->title;
$movimiento = $model->TipoMovimiento == 'D' ? 'Despacho' : 'Traslado';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header d-flex justify-content-between">
                <h3 class="card-title"><i class="fas fa-briefcase"></i> &nbsp; <?= $movimiento ?></h3>
                <h3 class="card-title"><i class="fas fa-clock"></i> &nbsp; Fecha de traslado: <?= $model->Fecha ?></h3>
                <h3 class="card-title"><i class="fas fa-clock"></i> &nbsp; Fecha de creacion: <?= $model->CreateDate ?></h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-striped table-hover table-bordered">
                    <tr>
                        <td><b>Estado: </b></td>
                        <td><?php
                            if ($model->Estado == "PROCESO") {
                                echo Html::tag('span', "En proceso", ['class' => 'badge bg-warning']);
                            } else if ($model->Estado == "FINALIZADO") {
                                echo Html::tag('span', "Finalizado", ['class' => 'badge bg-primary']);
                            }
                            ?>
                        </td>

                        <td><b>Bodega de origen: </b></td>
                        <td><?= Html::tag('span', $model->origen, ['class' => 'badge bg-danger']); ?></td>
                        <td><b>Bodega destino: </b></td>
                        <td><?= Html::tag('span', $model->destino, ['class' => 'badge bg-success']); ?></td>
                    </tr>
                </table>
                <div class="col-md-4 mt-2">
                    <br>
                    <?= Html::a('<i class="fas fa-plus"></i> Crear despacho', ['create-despacho'], [
                        'class' => 'btn btn-success',
                        'data-pjax' => 0,
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->render('_gridDetalle-movimiento', [
    'model' => $model,
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
    'movimiento' => $movimiento
]) ?>