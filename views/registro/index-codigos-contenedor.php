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

$this->title = 'Listado de codigos de barra de contenedores';
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
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'label' => 'Codigo de barra',
                    'attribute' => 'CodigoBarra',
                    'value' => function ($model, $key, $index, $widget) {
                        return "<span class='badge bg-light'>" . $model->CodigoBarra . "</span>";
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(
                        RegistroModel::find()
                            ->where(
                                "IdTipoRegistro = 2 
                                AND DOCUMENTO_INV IS NOT NULL
                                AND Activo = 1"
                            )->orderBy(['CreateDate' => SORT_DESC])->all(),
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
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'Articulo',
                    'value' => function ($model, $key, $index, $widget) {
                        return $model->Articulo . " - " . $model->Descripcion;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(
                        RegistroModel::find()
                            ->where(
                                "IdTipoRegistro = 2 
                                AND DOCUMENTO_INV IS NOT NULL
                                AND Activo = 1"
                            )->orderBy(['CreateDate' => SORT_DESC])->all(),
                        'Articulo',
                        'Articulo'
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
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'Libras',
                    'value' => function ($model, $key, $index, $widget) {
                        return $model->Libras;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(
                        RegistroModel::find()
                            ->where(
                                "IdTipoRegistro = 2 
                                AND DOCUMENTO_INV IS NOT NULL
                                AND Activo = 1"
                            )->orderBy(['CreateDate' => SORT_DESC])->all(),
                        'Libras',
                        'Libras'
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
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'BodegaCreacion',
                    'value' => function ($model, $key, $index, $widget) {
                        return Html::tag('span', $model->BodegaCreacion, ['class' => 'badge bg-info']);
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(RegistroModel::find()
                        ->where(
                            "IdTipoRegistro = 2 
                                AND DOCUMENTO_INV IS NOT NULL
                                AND Activo = 1"
                        )->orderBy('FechaCreacion')->all(), 'BodegaCreacion', 'BodegaCreacion'),
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
                    'label' => 'Nombre/Clave de contenedor',
                    'attribute' => 'DOCUMENTO_INV',
                    'value' => function ($model, $key, $index, $widget) {
                        return Html::tag('span', $model->DOCUMENTO_INV, ['class' => 'badge bg-red']);
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(RegistroModel::find()
                        ->where(
                            "IdTipoRegistro = 2 
                                AND DOCUMENTO_INV IS NOT NULL
                                AND Activo = 1"
                        )->orderBy('FechaCreacion')->all(), 'DOCUMENTO_INV', 'DOCUMENTO_INV'),
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
                            return Html::tag('span', $model->Estado, ['class' => 'badge bg-success']);
                        } else {
                            return Html::tag('span', $model->Estado, ['class' => 'badge bg-primary']);
                        }
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(RegistroModel::find()
                        ->where(
                            "IdTipoRegistro = 2 
                            AND DOCUMENTO_INV IS NOT NULL
                            AND Activo = 1"
                        )->orderBy('Estado')->all(), 'Estado', 'Estado'),
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
                    'filterType' => GridView::FILTER_DATE,
                    'filterWidgetOptions' => [
                        'options' => ['placeholder' => 'Todos...'],
                        'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-m-dd', 'todayHighlight' => true],

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
                    'filter' => ArrayHelper::map(RegistroModel::find()
                        ->where(
                            "IdTipoRegistro = 2 
                            AND DOCUMENTO_INV IS NOT NULL
                            AND Activo = 1"
                        )
                        ->orderBy('UsuarioCreacion')->all(), 'UsuarioCreacion', 'UsuarioCreacion'),
                    'filterWidgetOptions' => [
                        'options' => ['placeholder' => 'Todos...'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ],
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'template' => '{imprimir}',
                    'buttons' => [
                        'imprimir' => function ($url, $model) {

                            $ruta = substr(Yii::$app->request->baseUrl, 0, -3);
                            return Html::a(
                                '<span class="fas fa-print"></span>',
                                Url::to(
                                    $ruta .'views/registro/pdf-contenedor-barra.php?codigoBarra=' . $model->CodigoBarra . '&IdRegistro=' . $model->IdRegistro,
                                    true,
                                ),

                                ['target' => '_blank', 'data-pjax' => 0,]
                            );
                        }
                    ],
                ],
            ];

            echo GridView::widget([
                'id' => 'kv-contenedor',
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
                        Html::a('<i class="fas fa-plus"></i> Agregar', ['create-contenedor'], [
                            'class' => 'btn btn-success',
                            'data-pjax' => 0,
                        ]) . ' ' .
                            Html::a('<i class="fas fa-redo"></i>', ['index-codigos-contenedor'], [
                                'class' => 'btn btn-outline-success',
                                'data-pjax' => 0,
                            ]),
                        'options' => ['class' => 'btn-group mr-2']
                    ],
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
                    'type' => GridView::TYPE_PRIMARY,
                    'heading' => '<i class="fas fa-truck"></i> &nbsp;Codigos de barra de contenedores',
                ],
                'persistResize' => false,
            ]);
            ?>
        </div>
    </div>
</div>