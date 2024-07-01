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

$this->title = 'Libras disponibles en mesas de producción';
$this->params['breadcrumbs'][] = $this->title;

?>


<div class="row">
    <!-- left column -->
    <div class="col-md-12">
        <div class="tbl-cat-index">

            <h1><?= Html::encode($this->title) ?></h1>
            <?php // echo $this->render('_search', ['model' => $searchModel]); 
            ?>
            <?php
            $gridColumnsTrabajoMesa = [
                [
                    'class' => 'kartik\grid\SerialColumn',
                    'contentOptions' => ['class' => 'kartik-sheet-style'],
                    'width' => '36px',
                    'header' => '#',
                    'headerOptions' => ['class' => 'kartik-sheet-style']
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '180px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'NumeroMesa',
                    'value' => function ($model, $key, $index, $widget) {
                        return Html::tag('span', $model->NumeroMesa, ['class' => 'badge bg-purple']);
                    },
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '180px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'label' => 'Libras asignadas',
                    'attribute' => 'Libras',
                    'value' => function ($model, $key, $index, $widget) {
                        return Html::tag('span', round($model->Libras, 1)  . ' Lbs.', ['class' => 'badge bg-green']);
                    },
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '180px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'label' => 'Libras disponibles',
                    'attribute' => 'Libras',
                    'value' => function ($model, $key, $index, $widget) {

                        $produccionEnProceso = RegistroModel::find()
                            ->where(['Estado' => 'FINALIZADO', 'MesaOrigen' => $model->NumeroMesa, "IdTipoRegistro" => 1])
                            ->sum('Libras');

                        return Html::tag('span', ($model->Libras - $produccionEnProceso) . " Lbs.", ['class' => 'badge bg-pink']);
                    },
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '180px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'label' => 'Libras en proceso',
                    'attribute' => 'Libras',
                    'value' => function ($model, $key, $index, $widget) {

                        $produccionEnProceso = RegistroModel::find()
                            ->where(['Estado' => 'PROCESO', 'MesaOrigen' => $model->NumeroMesa, "IdTipoRegistro" => 1])
                            ->sum('Libras');

                        return Html::tag('span', ($produccionEnProceso == 0) ? 0 . ' Lbs.' : $produccionEnProceso, ['class' => 'badge bg-warning']);
                    },
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '180px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'label' => 'Libras finalizadas',
                    'attribute' => 'Libras',
                    'value' => function ($model, $key, $index, $widget) {

                        $produccionEnProceso = RegistroModel::find()
                            ->where(['Estado' => 'FINALIZADO', 'MesaOrigen' => $model->NumeroMesa, "IdTipoRegistro" => 1])
                            ->sum('Libras');

                        return Html::tag('span', ($produccionEnProceso == 0) ? 0 . ' Lbs.' : $produccionEnProceso, ['class' => 'badge bg-dark']);
                    },
                ],
                //obtener la bodega hace que se tarde mas en obtener la informacion
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '180px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'label' => 'Bodega de origen',
                    'attribute' => 'Bodega',
                    'value' => function ($model, $key, $index, $widget) {
                        return Html::tag('span', $model->Bodega, ['class' => 'badge bg-primary']);
                    },
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '180px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'label' => 'Ultima fecha de asignacion',
                    'value' => function ($model, $key, $index, $widget) {
                        $fecha = TrabajoMesaModel::find()->where(['NumeroMesa' => $model->NumeroMesa])->orderBy(['Fecha' => SORT_DESC])->one();

                        return Html::tag('span', $fecha->Fecha, ['class' => 'badge bg-green']);
                    },
                ],

            ];

            $exportmenu = ExportMenu::widget([
                'dataProvider' => $dataProvider,
                'columns' => $gridColumnsTrabajoMesa,
                'exportConfig' => [
                    ExportMenu::FORMAT_TEXT => false,
                    ExportMenu::FORMAT_HTML => false,
                    ExportMenu::FORMAT_CSV => false,
                ],
            ]);

            echo GridView::widget([
                'id' => 'kv-registro',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => $gridColumnsTrabajoMesa,
                'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
                'headerRowOptions' => ['class' => 'kartik-sheet-style'],
                'filterRowOptions' => ['class' => 'kartik-sheet-style'],
                'pjax' => true, // pjax is set to always true for this demo
                // set your toolbar
                'toolbar' =>  [
                    '{toggleData}',
                    $exportmenu,
                    [
                        'content' =>
                        '&nbsp;&nbsp;' .
                            Html::a('<i class="fas fa-plus"></i> Agregar', ['create-mesa'], [
                                'class' => 'btn btn-success',
                                'data-pjax' => 0,
                            ]) . ' &nbsp&nbsp ' .
                            Html::a('<i class="fas fa-redo"></i>', ['index-codigos-mesas'], [
                                'class' => 'btn btn-outline-success',
                                'data-pjax' => 0,
                            ]),
                        'options' => ['class' => 'btn-group mr-2']
                    ],


                ],
                'toggleDataContainer' => ['class' => 'btn-group mr-2'],
                // set export properties
                // parameters from the demo form
                'bordered' => true,
                'striped' => true,
                'condensed' => true,
                'responsive' => true,
                'hover' => true,
                //'showPageSummary'=>$pageSummary,
                'panel' => [
                    'type' => GridView::TYPE_PRIMARY,
                    'heading' => '<i class="fas fa-table"></i> &nbsp; Libras en mesas',
                ],
                'persistResize' => false,
            ]);
            ?>
        </div>
    </div>
</div>