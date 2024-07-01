<?php
Yii::$app->language = 'es_ES';

use app\models\DetalleMovimientoModel;
use app\models\RegistroModel;
use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use yii\helpers\ArrayHelper;

?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <?php
            $gridColumns = [
                [
                    'class' => 'kartik\grid\ExpandRowColumn',
                    'width' => '50px',
                    'value' => function ($model, $key, $index, $column) {
                        return GridView::ROW_COLLAPSED;
                    },
                    'detail' => function ($model) {
                        $informacion = RegistroModel::obtenerCodigoBarra($model->CodigoBarra);

                        return Yii::$app->controller->renderPartial('_detalleMovimiento-codigoBarra', [
                            'model' => $informacion['model'],
                            'detalle' => $informacion['detalle']
                        ]);
                    },
                    'headerOptions' => ['class' => 'kartik-sheet-style'],
                    'expandOneOnly' => true
                ],
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
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '80px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'CodigoBarra',
                    'value' => function ($model) {
                        return Html::tag('span', $model->CodigoBarra, ['class' => 'badge bg-light']);
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(
                        DetalleMovimientoModel::find()
                            ->where("IdMovimiento = $model->IdMovimiento")->orderBy(['CodigoBarra' => SORT_ASC])->all(),
                        'CodigoBarra',
                        'CodigoBarra'
                    ),
                    'filterWidgetOptions' => [
                        'options' => ['placeholder' => 'Todos...'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ],
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '50px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'codigoBarra.Articulo',
                    'value' => function ($model) {
                        return Html::tag('span', $model->codigoBarra->Articulo . " - " . $model->codigoBarra->Descripcion, ['class' => 'badge bg-purple']);
                    },
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '50px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'codigoBarra.Clasificacion',
                    'value' => function ($model) {
                        return Html::tag('span', $model->codigoBarra->Clasificacion, ['class' => 'badge bg-warning']);
                    },
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '80px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'codigoBarra.Libras',
                    'value' => function ($model) {
                        return Html::tag('span', $model->codigoBarra->Libras . " Lbs.", ['class' => 'badge bg-info']);
                    },
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '80px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'codigoBarra.Unidades',
                    'value' => function ($model) {
                        return Html::tag('span', $model->codigoBarra->Unidades, ['class' => 'badge bg-success']);
                    },
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '80px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'codigoBarra.UsuarioCreacion',
                    'value' => function ($model) {
                        return $model->codigoBarra->UsuarioCreacion;
                    },
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'template' => '{delete}',
                    'buttons' => [
                        'delete' => function ($url, $model) {
                            if ($model->idMovimiento->Estado == 'PROCESO' && $model->idMovimiento->TipoMovimiento == "D") {
                                return Html::a(
                                    '<span class="fas fa-trash"></span>',
                                    [
                                        'delete-detalle-movimiento',
                                        'IdMovimiento' => $model->IdMovimiento,
                                        'IdDetalleMovimiento' => $model->IdDetalleMovimiento,
                                        'codigoBarra' => $model->CodigoBarra,
                                        'bodegaOrigen' => $model->codigoBarra->BodegaCreacion
                                    ],
                                    [
                                        'data' => [
                                            'confirm' => 'Se eliminara este registro. Desea continuar?',
                                            'method' => 'post',
                                        ],
                                    ]
                                );
                            } else if ($model->idMovimiento->Estado == 'PROCESO' && $model->idMovimiento->TipoMovimiento == "T") {
                                return Html::a(
                                    '<span class="fas fa-trash"></span>',
                                    [
                                        'delete-detalle-movimiento',
                                        'IdMovimiento' => $model->IdMovimiento,
                                        'IdDetalleMovimiento' => $model->IdDetalleMovimiento,
                                        'codigoBarra' => $model->CodigoBarra,
                                        'bodegaOrigen' => substr($model->idMovimiento->origen, 0, 4)
                                    ],
                                    [
                                        'data' => [
                                            'confirm' => 'Se eliminara este registro. Desea continuar?',
                                            'method' => 'post',
                                        ],
                                    ]
                                );
                            }
                        },

                    ],
                ],
            ];

            $exportmenu = ExportMenu::widget([
                'dataProvider' => $dataProvider,
                'columns' => $gridColumns,
                'clearBuffers' => true,
                'exportConfig' => [
                    ExportMenu::FORMAT_TEXT => false,
                    ExportMenu::FORMAT_HTML => false,
                    ExportMenu::FORMAT_CSV => false,
                ],
            ]);
            echo GridView::widget([
                'id' => 'datosGrid-detalle',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => $gridColumns,
                'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
                'headerRowOptions' => ['class' => 'kartik-sheet-style '],
                'filterRowOptions' => ['class' => 'kartik-sheet-style'],
                'pjax' => false, // pjax is set to always true for this demo
                // set your toolbar
                'toolbar' =>  [
                    $exportmenu,
                    Html::a(
                        '<i class="fas fa-redo"></i>',
                        [
                            'view-traslados',
                            'IdMovimiento' => $model->IdMovimiento,
                        ],
                        [
                            'class' => 'btn btn-outline-success'
                        ]
                        ),
                    'options' => ['class' => 'float-left']
                ],
                'toggleDataContainer' => ['class' => 'float-left btn-group mr-2'],
                // set export properties
                // parameters from the demo form
                'bordered' => true,
                'striped' => true,
                'condensed' => true,
                'responsive' => true,
                'hover' => true,
                'panel' => [
                    'type' => GridView::TYPE_PRIMARY,
                    'heading' => "<i class='fas fa-truck'></i> &nbsp;Detalle de $movimiento",
                    'footer' => false
                ],
                'persistResize' => false,
            ]);
            ?>

        </div>
    </div>
</div>
<script>
    let toolbarDownloadXLSX = document.querySelector('.toolbar-container')
    toolbarDownloadXLSX.classList.remove('float-right')
    toolbarDownloadXLSX.classList.add('float-left')
</script>