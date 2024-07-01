<?php
Yii::$app->language = 'es_ES';

use app\models\RegistroModel;
use app\models\TransaccionModel;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use kartik\export\ExportMenu;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OsigSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Impresión de viñetas';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <!-- left column -->
    <div class="col-md-12">
        <div class="tbl-cat-index">

            <h1><?= Html::encode($this->title) ?></h1>
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

                        return $model->CodigoBarra;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(RegistroModel::find()->orderBy('CodigoBarra')->all(), 'CodigoBarra', 'CodigoBarra'),
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
                    'attribute' => 'Articulo',
                    'value' => function ($model, $key, $index, $widget) {
                        return $model->Articulo;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(RegistroModel::find()->orderBy('Articulo')->all(), 'Articulo', 'Articulo'),
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
                        return $model->Clasificacion;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(RegistroModel::find()->orderBy('Clasificacion')->all(), 'Clasificacion', 'Clasificacion'),
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
                    'attribute' => 'Descripcion',
                    'value' => function ($model, $key, $index, $widget) {
                        return $model->Descripcion;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(RegistroModel::find()->orderBy('Descripcion')->all(), 'Descripcion', 'Descripcion'),
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
                    'attribute' => 'FechaCreacion',
                    'value' => function ($model, $key, $index, $widget) {
                        return  $model->FechaCreacion;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(RegistroModel::find()->orderBy('FechaCreacion')->all(), 'FechaCreacion', 'FechaCreacion'),
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
                    'class' => 'kartik\grid\ActionColumn',
                    'template' => '{print-detalle} {print-registro}',
                    'buttons' => [
                        'print-detalle' => function ($url, $model) {
                            return Html::a(
                                '<span class="fas fa-print text-green"> Fardo</span>',
                                Url::to(
                                    substr(Yii::$app->request->baseUrl, 0, -3) . 'views/detalle-registro/pdf-registro.php?codigoBarra=' . $model->CodigoBarra,
                                    true,
                                ),
                                ['target' => '_blank']
                            );
                        },
                        'print-registro' => function ($url, $model) {
                            return Html::a(
                                '<span class="fas fa-print"> Desglose</span>',
                                Url::to(
                                    substr(Yii::$app->request->baseUrl, 0, -3) . 'views/detalle-registro/pdf-detalle-registro.php?codigoBarra=' . $model->CodigoBarra,
                                    true,
                                ),
                                ['target' => '_blank']
                            );
                        },
                    ]
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
                'id' => 'kv-registro',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => $gridColumns,
                'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
                'headerRowOptions' => ['class' => 'kartik-sheet-style'],
                'filterRowOptions' => ['class' => 'kartik-sheet-style'],
                'pjax' => false, // pjax is set to always true for this demo
                // set your toolbar
                'toolbar' =>  [
                    '{toggleData}',
                    $exportmenu,
                    [
                        'content' =>
                        '&nbsp;&nbsp;' .
                            Html::a('<i class="fas fa-plus"></i> Agregar', ['create-detalle'], [
                                'class' => 'btn btn-success',
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
                    'heading' => '<i class="fas fa-briefcase"></i> &nbsp;Registros de producción',
                ],
                'persistResize' => false,
            ]);
            ?>
        </div>
    </div>
</div>