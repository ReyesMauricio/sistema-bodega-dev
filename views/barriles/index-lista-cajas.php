<?php
use app\models\RegistroModel;
use app\models\TransaccionModel;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use kartik\export\ExportMenu;
use kartik\widgets\DatePicker;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OsigSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Listado de cajas';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="col-md-12">
        <div class="tbl-cajas-index">
            <?php
                $gridColumns = [
                    [
                        'class' => 'kartik\grid\SerialColumn',
                        'contentOptions' => ['class' => 'kartik-sheet-style'],
                        'width' => '45px',
                        'header' => '#',
                        'headerOptions' => ['class' => 'kartik-sheet-style']
                    ],
                    [
                        'class' => 'kartik\grid\DataColumn',
                        'attribute' => 'CodigoBarra',
                        'width' => '150px',
                        'hAlign' => 'center',
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $widget) {
                            return Html::a($model->CodigoBarra);
                        },
                    ],
                    [
                        'class' => 'kartik\grid\DataColumn',
                        'attribute' => 'libras',
                        'width' => '125px',
                        'hAlign' => 'center',
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $widget) {
                            return $model->registro->Libras .' Lbs';
                        },
                    ],
                    [
                        'class' => 'kartik\grid\DataColumn',
                        'attribute' => 'Articulo',
                        'width' => '100px',
                        'format' => 'raw',
                        'hAlign' => 'center',
                        'value' => function ($model, $key, $index, $widget) {
                            return $model->registro->Articulo ?? 'N/A';
                        },
                    ],
                    [
                        'class' => 'kartik\grid\DataColumn',
                        'attribute' => 'Descripcion',
                        'width' => '230px',
                        'hAlign' => 'center',
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $widget) {
                            return $model->registro->Descripcion;
                        },
                    ],
                    [
                        'class' => 'kartik\grid\DataColumn',
                        'attribute' => 'IdTipoTransaccion',
                        'label' => 'Transaccion',
                        'width' => '325px',
                        'hAlign' => 'center',
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $widget) {
                            return $model->tipoTransaccion->TipoTransaccion;
                        },
                    ],
                    [
                        'class' => 'kartik\grid\DataColumn',
                        'hAlign' => 'center',
                        'attribute' => 'Documento_Inv',
                        'label' => 'Documento Inventario',
                        'width' => '200px',
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $widget) {
                            return $model->Documento_Inv;
                        },
                    ],
                    [
                        'class' => 'kartik\grid\DataColumn',
                        'attribute' => 'Estado',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->Estado == 'FINALIZADO'
                                ? '<i class="fa fa-check-circle text-success"></i>'
                                : '<i class="fa fa-hourglass-half text-warning"></i>';
                        },
                        'filterType' => GridView::FILTER_SELECT2,
                        'filter' => [
                            'PENDIENTE' => 'Pendiente',
                            'FINALIZADO' => 'Finalizado',
                        ],
                        'filterWidgetOptions' => [
                            'options' => ['placeholder' => 'Todos...'],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ],
                        'width' => '120px',
                        'vAlign' => 'middle',
                        'hAlign' => 'center'
                    ],
                    [
                        'class' => 'kartik\grid\ActionColumn',
                        'template' => '{imprimir}',
                        'buttons' => [
                            'imprimir' => function ($url, $model) {
                                return Html::a(
                                    '<span class="fas fa-print"></span>',
                                    Url::to(['barriles/generate-pdf',
                                    'NumeroDocumento' => $model->NumeroDocumento]),
    
                                    ['target' => '_blank', 'data-pjax' => 0,]
                                );
                            }
                        ],
                    ],
                    // [
                    //     'class' => 'kartik\grid\DataColumn',
                    //     'hAlign' => 'center',
                    //     'attribute' => 'NumeroDocumento',
                    //     'label' => 'Documento Inventario',
                    //     'width' => '300px',
                    //     'format' => 'raw',
                    //     'value' => function ($model, $key, $index, $widget) {
                    //         return $model->NumeroDocumento;
                    //     },
                    // ],
                    [
                        'class' => 'kartik\grid\ActionColumn',
                        'header' => 'Acciones',
                        'template' => '{view} {delete}',
                        //Es visible nada mas si tiene un documento de inventario
                        'visibleButtons' => [
                            'delete' => function ($model, $key, $index) {
                                return empty($model->Documento_Inv);
                            }
                        ],
                        'urlCreator' => function ($action, TransaccionModel $model, $key, $index, $column) {
                            if ($action === 'view') {
                                return Url::to(['barriles/edit-caja', 'CodigoBarra' => $model->CodigoBarra, 'NumeroDocumento' => $model->NumeroDocumento, 'Articulo' => $model->registro->Articulo, 'Descripcion' => $model->registro->Descripcion, 'Rec' => $model->Documento_Inv]);
                            }
                            if ($action === 'delete') {
                                return Url::to(['barriles/delete-caja', 'CodigoBarra' => $model->CodigoBarra]);
                            }
                            return Url::toRoute([$action, 'CodigoBarra' => $model->CodigoBarra]);
                        }
                    ],
                ];

                $exportmenu = ExportMenu::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => $gridColumns,
                    'exportConfig' => [
                        ExportMenu::FORMAT_TEXT => false,
                        ExportMenu::FORMAT_HTML => false,
                        ExportMenu::FORMAT_CSV => false,
                    ],
                ]);

                echo GridView::widget([
                    'id' => 'kv-cajas',
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => $gridColumns,
                    'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
                    'headerRowOptions' => ['class' => 'kartik-sheet-style'],
                    'filterRowOptions' => ['class' => 'kartik-sheet-style'],
                    'pjax' => true, // pjax is set to always true for this demo
                    // set your toolbar
                    'toolbar' =>  [
                        [
                            'content' =>
                            Html::a('<i class="fas fa-plus"></i> Agregar', ['index-cajas'], [
                                'class' => 'btn btn-success',
                                'data-pjax' => 0,
                            ]) . ' ' .
                                Html::a('<i class="fas fa-redo"></i>', ['index-lista-cajas'], [
                                    'class' => 'btn btn-outline-success',
                                    'data-pjax' => 0,
                                ]),
                            'options' => ['class' => 'btn-group mr-2']
                        ],
                        $exportmenu,
                        '{toggleData}',
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
                        'type' => 'dark',
                        'heading' => 'Listado de cajas',
                    ],
                    'persistResize' => false,
                ]);
            ?>
        </div>
    </div>
</div>