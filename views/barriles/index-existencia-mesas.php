<?php
Yii::$app->language = 'es_ES';

use app\models\RegistroModel;
use app\models\TipoEmpaqueModel;
use app\models\TrabajoMesaModel;
use app\models\TrabajoMesaRestanteModel;
use app\models\TransaccionModel;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use kartik\export\ExportMenu;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OsigSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Libras asignadas a mesas de clasificacion';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row">
    <!-- left column -->
    <div class="col-md-12">
        <div class="card-dark border-primary">
            <div class="card-header bg-dark">
                <div class="float-right">
                    <div class="summary">Mostrando <b><?= count($dataProvider) ?></b> elementos.</div>
                </div>
                <div class="m-0">
                    <h6><i class="fas fa-database"></i> &nbsp;<?= $this->title ?></h6>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-bordered">
                    <thead class="thead-light text-center">
                        <th>#</th>
                        <th>Numero de mesa</th>
                        <th>Libras en proceso</th>
                        <th>Acciones</th>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($dataProvider as $index => $registro) { ?>
                            <tr class="text-center">
                                <td><?= ($index + 1) ?></td>
                                <td><?= Html::tag('span', $registro->NumeroMesa, ['class' => 'badge bg-light']); ?></td>
                                <td><?= Html::tag('span', number_format($registro->Libras, 2) . " Lbs.", ['class' => 'badge bg-yellow']); ?></td>
                                <td>
                                    <?= Html::a('<i class="fas fa-plus"></i>', 
                                    Url::to(['/barriles/detalle-mesa-asignacion', 'mesa' => $registro->NumeroMesa]), 
                                    ['class' => 'btn btn-sm btn-primary', 'title' => 'Crear Barril Produccion']); ?>
                                    
                                    <?= Html::a('<i class="fas fa-eye"></i>', 
                                    Url::to(['/barriles/fardo-asignacion-proceso', 'mesa' => $registro->NumeroMesa]), 
                                    ['class' => 'btn btn-sm btn-primary', 'title' => 'Ver FARDOS Asignados']); ?>
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