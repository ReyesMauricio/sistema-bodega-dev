<?php
Yii::$app->language = 'es_ES';

use app\models\RegistroModel;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\widgets\DatePicker;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OsigSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Existencias';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <!-- left column -->
    <div class="col-md-12">
        <div class="card p-0">

            <?php // echo $this->render('_search', ['model' => $searchModel]); 
            ?>
            <?php
            $gridColumns = [
                [
                    'class' => 'kartik\grid\SerialColumn',
                    'contentOptions' => ['class' => 'kartik-sheet-style'],
                    'width' => '36px',
                    'header' => '#',
                    'headerOptions' => ['class' => 'kartik-sheet-style'],
                    'pageSummary' => 'Totales',
                    'pageSummaryOptions' => ['colspan' => 3],
                ],
                [
                    'class' => 'kartik\grid\ExpandRowColumn',
                    'width' => '50px',
                    'value' => function ($model, $key, $index, $column) {
                        return GridView::ROW_COLLAPSED;
                    },
                    'detail' => function ($model) {
                        $informacion = RegistroModel::obtenerCodigosExistentes($model->Articulo);

                        return Yii::$app->controller->renderPartial('_codigoBarra-existentes', [
                            'model' => $informacion,
                        ]);
                    },
                    'headerOptions' => ['class' => 'kartik-sheet-style'],
                    'expandOneOnly' => true
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '80px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'Articulo',
                    'value' => function ($model) {
                        return Html::tag('span', $model->Articulo . " - " . $model->Descripcion, ['class' => 'badge bg-blue']);
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map($articulos, 'Articulo', 'Descripcion'),
                    'filterWidgetOptions' => [
                        'options' => ['placeholder' => 'Todos...'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ],
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '80px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'Unidades',
                    'value' => function ($model) {
                        return Html::tag('span', $model->Unidades, ['class' => 'badge bg-red']);
                    },
                    'filter' => false,
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '80px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'Libras',
                    'value' => function ($model) {
                        return Html::tag('span', $model->Libras . " Lbs.", ['class' => 'badge bg-yellow']);
                    },
                    'filter' => false,
                ],
            ];

            echo GridView::widget([
                'id' => 'datosGrid-detalle',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => $gridColumns,
                'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
                'headerRowOptions' => ['class' => 'kartik-sheet-style'],
                'filterRowOptions' => ['class' => 'kartik-sheet-style'],
                'pjax' => false, // pjax is set to always true for this demo
                'toggleDataContainer' => ['class' => 'btn-group mr-2'],
                'bordered' => true,
                'striped' => true,
                'condensed' => true,
                'responsive' => true,
                'hover' => true,
                'toolbar' =>  [
                    [
                        'content' =>
                        Html::a('<i class="fas fa-redo"></i>', ['mostrar-existencias'], [
                            'class' => 'btn btn-outline-warning',
                            'data-pjax' => 1,
                            'id' => 'redo-index-prod'
                        ]) . '&nbsp;&nbsp;' .
                            Html::a(
                                '<i class="fa fa-download"></i> Descargar reporte',
                                [
                                    Url::to('detalle-registro/crear-reporte-existencias')
                                ],
                                [
                                    'id' => 'boton-generar-reporte',
                                    'class' => 'btn btn-warning'
                                ]
                            ),
                        'options' => ['class' => 'btn-group mr-2']
                    ],
                ],
                'panel' => [
                    'type' => GridView::TYPE_PRIMARY,
                    'heading' => '<i class="fas fa-info-circle"></i> &nbsp;Existencias actuales dia ' . date("F j, Y"),
                    'footer' => false
                ],
                'persistResize' => false,
            ]);
            ?>

        </div>
    </div>
</div>