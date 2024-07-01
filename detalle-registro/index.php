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

$this->title = 'Listado de producción';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="col-md-6">
        <div class="card card-danger">
            <div class="card-header">
                <h5><i class="fas fa-file"></i> Finalizar producción (Posible varias veces al dia)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- left column -->
                    <div class="col-md-10">
                        <label for="">Seleccione la fecha de producción: </label>
                        <?= DatePicker::widget([
                            'name' => 'fecha-filtro-finalizacion',
                            'id' => 'fecha-filtro-finalizacion',
                            'value' => date("Y-m-d"),
                            'options' => [
                                'placeholder' => 'Seleccionar fecha',
                            ],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' =>
                                'yyyy-m-dd',
                                'todayHighlight' => true,
                                'endDate' => "1d",
                            ],
                        ]); ?>
                    </div>
                    <div class="col-md-2 mt-2">
                        <br>
                        <?= Html::a('<i class="fas fa-exclamation-triangle "></i> Finalizar producción', ['finalizar-produccion'], [
                            'class' => 'btn btn-warning',
                            'data-pjax' => 0,
                            'id' => 'finalizar-produccion',
                            'data' => [
                                'confirm' => 'Estas a punto de finalizar la producción del dia, deseas continuar?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-warning">
            <div class="card-header">
                <h5><i class="fas fa-file"></i> Finalizar dia (SOLO UNA VEZ POR DIA)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-10">
                        <label for="">Seleccione el dia a finalizar: </label>
                        <?= DatePicker::widget([
                            'name' => 'fecha-finalizacion-diaria',
                            'id' => 'fecha-finalizacion-diaria',
                            'value' => date("Y-m-d"),
                            'options' => [
                                'placeholder' => 'Seleccionar fecha',
                            ],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' =>
                                'yyyy-m-dd',
                                'todayHighlight' => true,
                                'endDate' => "0d",
                                'startDate' => "-7d"
                            ],
                        ]); ?>
                    </div>
                    <div class="col-md-2 mt-2">
                        <br>
                        <?= Html::a('<i class="fas fa-exclamation-triangle "></i> Finalizar  dia', ['finalizar-dia-produccion'], [
                            'class' => 'btn btn-danger',
                            'data-pjax' => 0,
                            'id' => 'finalizar-dia-produccion',
                            'data' => [
                                'confirm' => 'Estas a punto de finalizar el dia, deseas continuar?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-md-12">
        <div class="tbl-cat-index">
            <h1><?= Html::encode($this->title) ?></h1>
            <?php // echo $this->render('_search', ['model' => $searchModel]); 
            ?>
            <?php
            $gridColumns = [
                [
                    'class' => 'kartik\grid\SerialColumn',
                    'contentOptions' => ['class' => 'kartik-sheet-style'],
                    'width' => '36px',
                    'header' => '#',
                    'headerOptions' => ['class' => 'kartik-sheet-style']
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '60px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'CodigoBarra',
                    'value' => function ($model, $key, $index, $widget) {

                        return Html::a($model->CodigoBarra, [
                            'view',
                            'codigoBarra' => $model->CodigoBarra,
                            'condicionImprimir' => '',
                        ], ['class' => 'badge bg-light']);
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(RegistroModel::find()->where('IdTipoRegistro = 1 ')->all(), 'CodigoBarra', 'CodigoBarra'),
                    'filterWidgetOptions' => [
                        'options' => ['placeholder' => 'Todos...'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ],
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'Articulo',
                    'value' => function ($model, $key, $index, $widget) {
                        return Html::tag('span', $model->Articulo . ' - ' . $model->Descripcion, ['class' => 'badge bg-green']);
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(RegistroModel::find()->where('IdTipoRegistro = 1')->all(), 'Articulo', 'Articulo'),
                    'filterWidgetOptions' => [
                        'options' => ['placeholder' => 'Todos...'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ],
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '180px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'Clasificacion',
                    'value' => function ($model, $key, $index, $widget) {
                        return Html::tag('span', $model->Clasificacion, ['class' => 'badge bg-info']);
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(RegistroModel::find()
                        ->where('IdTipoRegistro = 1')
                        ->orderBy('Clasificacion')->all(), 'Clasificacion', 'Clasificacion'),
                    'filterWidgetOptions' => [
                        'options' => ['placeholder' => 'Todos...'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ],
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '180px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'Estado',
                    'value' => function ($model, $key, $index, $widget) {
                        if ($model->Estado == "PROCESO") {
                            return Html::tag('span', "En proceso", ['class' => 'badge bg-warning']);
                        } else if ($model->Estado == "FINALIZADO") {
                            return Html::tag('span', "Finalizado", ['class' => 'badge bg-primary']);
                        } else if ($model->Estado == "ELIMINADO") {
                            return Html::tag('span', "Eliminado", ['class' => 'badge bg-danger']);
                        }
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(RegistroModel::find()->orderBy('Estado')->all(), 'Estado', 'Estado'),
                    'filterWidgetOptions' => [
                        'options' => ['placeholder' => 'Todos...'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ],
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '180px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'Libras',
                    'value' => function ($model, $key, $index, $widget) {
                        return Html::tag('span', $model->Libras, ['class' => 'badge bg-purple']);
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(RegistroModel::find()
                        ->where('IdTipoRegistro = 1')
                        ->orderBy('Libras')->all(), 'Libras', 'Libras'),
                    'filterWidgetOptions' => [
                        'options' => ['placeholder' => 'Todos...'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ],
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'FechaCreacion',
                    'value' => function ($model, $key, $index, $widget) {
                        return  $model->FechaCreacion;
                    },
                    'filterType' => GridView::FILTER_DATE,
                    'filterWidgetOptions' => [
                        'options' => ['placeholder' => 'Todos...'],
                        'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd', 'todayHighlight' => true],
                    ],
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '180px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'UsuarioCreacion',
                    'value' => function ($model, $key, $index, $widget) {
                        return  $model->UsuarioCreacion;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(RegistroModel::find()->orderBy('UsuarioCreacion')->all(), 'UsuarioCreacion', 'UsuarioCreacion'),
                    'filterWidgetOptions' => [
                        'options' => ['placeholder' => 'Todos...'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ],
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '180px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'MesaOrigen',
                    'value' => function ($model, $key, $index, $widget) {
                        return  $model->MesaOrigen;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(RegistroModel::find()->orderBy('MesaOrigen')->all(), 'MesaOrigen', 'MesaOrigen'),
                    'filterWidgetOptions' => [
                        'options' => ['placeholder' => 'Todos...'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ],
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '180px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'EmpresaDestino',
                    'value' => function ($model, $key, $index, $widget) {
                        return  $model->EmpresaDestino;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(RegistroModel::find()->orderBy('EmpresaDestino')->all(), 'EmpresaDestino', 'EmpresaDestino'),
                    'filterWidgetOptions' => [
                        'options' => ['placeholder' => 'Todos...'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ],
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'template' => '{view} {edit} {delete}',
                    'buttons' => [
                        'view' => function ($url, $model) {
                            return Html::a(
                                '<span class="fas fa-eye"></span>',
                                [
                                    'view',
                                    'codigoBarra' => $model->CodigoBarra,
                                    'condicionImprimir' => ''
                                ],
                            );
                        },
                        'edit' => function ($url, $model) {
                            if ($model->Estado == 'PROCESO') {
                                return Html::a(
                                    '<span class="fas fa-pencil-alt"></span>',
                                    [
                                        'update',
                                        'codigoBarra' => $model->CodigoBarra,
                                    ],
                                );
                            }
                        },
                        'delete' => function ($url, $model) {
                            if ($model->Estado == 'PROCESO') {
                                return Html::a(
                                    '<span class="fas fa-trash-alt"></span>',
                                    [
                                        'delete-registro',
                                        'codigoBarra' => $model->CodigoBarra,
                                    ],
                                    [
                                        'data' => [
                                            'confirm' => 'Se elimina este registro. Desea continuar?',
                                            'method' => 'post',
                                        ],
                                    ]
                                );
                            }
                        },
                    ]
                ],
            ];

            $exportmenu = ExportMenu::widget([
                'dataProvider' => $dataProvider,
                'filename' => 'PRODUCCION DIA ' . date("d-m-Y"),
                'fontAwesome' => true,
                'columns' => $gridColumns,
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
                'columns' => $gridColumns,
                'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
                'headerRowOptions' => ['class' => 'kartik-sheet-style'],
                'filterRowOptions' => ['class' => 'kartik-sheet-style'],
                'pjax' => true, // pjax is set to always true for this demo
                // set your toolbar
                'toolbar' =>  [
                    $exportmenu,
                    '&nbsp;&nbsp;&nbsp;' .
                        '{toggleData}',
                    [
                        'content' =>
                        ' &nbsp&nbsp ' .
                            Html::a('<i class="fas fa-plus"></i> Agregar', ['create-detalle'], [
                                'class' => 'btn btn-success',
                                'data-pjax' => 0,
                            ]) . ' &nbsp&nbsp ' .
                            Html::a('<i class="fas fa-redo"></i>', ['index'], [
                                'class' => 'btn btn-outline-success',
                                'data-pjax' => 1,
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
                    'heading' => '<i class="fas fa-briefcase"></i> &nbsp;Registros de producción',
                ],
                'persistResize' => false,
            ]);
            ?>
        </div>
    </div>
</div>
<script>
    let botonFinalizar = document.getElementById('finalizar-produccion')
    let rutaFinalizar = botonFinalizar.href
    botonFinalizar.addEventListener('click', (e) => {
        let fechaProduccion = document.getElementById('fecha-filtro-finalizacion').value;
        botonFinalizar.href += `&fecha=${fechaProduccion}`

        setTimeout(() => {
            let botones = document.querySelector('.bootstrap-dialog-footer-buttons')
            botones.childNodes[0].addEventListener('click', (e) => {
                botonFinalizar.href = rutaFinalizar
            })
        }, 1000);

    })

    let botonFinalizarDia = document.getElementById('finalizar-dia-produccion')
    let rutaFinalizarDia = botonFinalizarDia.href
    botonFinalizarDia.addEventListener('click', (e) => {
        let fechaProduccion = document.getElementById('fecha-finalizacion-diaria').value;
        botonFinalizarDia.href += `&fecha=${fechaProduccion}`

        setTimeout(() => {
            let botones = document.querySelector('.bootstrap-dialog-footer-buttons')
            botones.childNodes[0].addEventListener('click', (e) => {
                botonFinalizarDia.href = rutaFinalizarDia
            })
        }, 1000);

    })
</script>