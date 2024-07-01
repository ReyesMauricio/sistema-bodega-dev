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

$this->title = 'Listado de transacciones';
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
                        'attribute' => 'IdTipoTransaccion',
                        'width' => '125px',
                        'hAlign' => 'center',
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $widget) {
                            return $model->tipoTransaccion->TipoTransaccion;
                        },
                        'filter' => false
                    ],
                    [
                        'class' => 'kartik\grid\DataColumn',
                        'width' => '180px',
                        'format' => 'raw',
                        'vAlign' => 'middle',
                        'hAlign' => 'center',
                        'attribute' => 'FechaCreacion',
                        'value' => function ($model) {
                            return $model->FechaCreacion;
                        },
                        'filterType' => GridView::FILTER_DATE,
                        'filterWidgetOptions' => [
                            'options' => ['placeholder' => 'Seleccione fecha'],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                                'todayHighlight' => true,
                            ],
                        ],
                    ],
                    [
                        'class' => 'kartik\grid\DataColumn',
                        'attribute' => 'NumeroDocumento',
                        'width' => '230px',
                        'hAlign' => 'center',
                        'format' => 'raw', // Especificamos 'raw' para que Yii2 renderice HTML
                        'value' => function ($model) {
                            // Devolvemos el valor como un enlace
                            return \yii\helpers\Html::a($model->NumeroDocumento, ['barriles/detalle-fardo-asignacion', 'NumeroDocumento' => $model->NumeroDocumento]);
                        },
                        'contentOptions' => ['style' => 'text-align:center'],
                        'headerOptions' => ['style' => 'text-align:center'],
                    ],                    
                    [
                        'class' => 'kartik\grid\DataColumn',
                        'attribute' => 'Documento_Inv',
                        'width' => '230px',
                        'hAlign' => 'center',
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $widget) {
                            return $model->Documento_Inv?? 'N/A';
                        },
                    ],
                    [
                        'class' => 'kartik\grid\DataColumn',
                        'attribute' => 'Estado',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->Estado == 'F'
                                ? '<i class="fa fa-check-circle text-success"></i>'
                                : '<i class="fa fa-hourglass-half text-warning"></i>';
                        },
                        'filterType' => GridView::FILTER_SELECT2,
                        'filter' => [
                            'P' => 'Pendiente',
                            'F' => 'Finalizado',
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
                        'header' => 'Mesas',
                        'template' => '{asignar}',
                        'buttons' => [
                            'asignar' => function ($url, $model) {
                                return Html::a(
                                    '<span class="fas fa-clipboard-check"></span>',
                                    Url::to(['mesas/produccion-mesa',
                                    'NumeroDocumento' => $model->NumeroDocumento]),
                                );
                            }
                        ],
                    ],
                    [
                        'class' => 'kartik\grid\ActionColumn',
                        'header' => 'Acciones',
                        'template' => '{view}',
                        'urlCreator' => function ($action, TransaccionModel $model, $key, $index, $column) {
                            if ($action === 'view') {
                                return Url::to(['barriles/create-nuevo-barril', 'NumeroDocumento' => $model->NumeroDocumento, 'documentoInv' => $model->Documento_Inv]);
                            }
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
                            Html::a('<i class="fas fa-plus"></i> Agregar', ['index-verificar-codigo'], [
                                'class' => 'btn btn-success',
                                'data-pjax' => 0,
                            ]) . ' ' .
                                Html::a('<i class="fas fa-redo"></i>', ['index-verificar-transaccion'], [
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
                        'heading' => 'Listado de transacciones  ',
                    ],
                    'persistResize' => false,
                ]);
            ?>
        </div>
    </div>
</div>