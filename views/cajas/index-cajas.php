<?php
Yii::$app->language = 'es_ES';

use app\models\RegistroModel;
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

                        return $model->CodigoBarra;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(RegistroModel::find()
                        ->where('IdTipoRegistro = 4 AND IdTipoEmpaque IN (1, 2, 3, 4) AND Activo = 1')
                        ->orderBy('CodigoBarra')
                        ->all(), 'CodigoBarra', 'CodigoBarra'),
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
                        return Html::tag('span', $model->Articulo . ' - ' . $model->Descripcion, ['class' => 'badge bg-info']);
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map($articulos, 'ARTICULO', 'ARTICULODESCRIPCION'),
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
                        return  $model->Libras . ' Lbs';
                    },
                    'filter' => true
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '180px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'BodegaActual',
                    'value' => function ($model, $key, $index, $widget) {
                        return Html::tag('span', $model->BodegaActual, ['class' => 'badge bg-primary']);
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => ArrayHelper::map(RegistroModel::find()->orderBy('BodegaActual')->all(), 'BodegaActual', 'BodegaActual'),
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
                    'attribute' => 'IdTipoEmpaque',
                    'value' => function ($model, $key, $index, $widget) {

                        return  $model->tipoEmpaque->TipoEmpaque;
                    },
                    'filter' => false
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '180px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'DOCUMENTO_INV',
                    'value' => function ($model, $key, $index, $widget) {

                        return  $model->DOCUMENTO_INV;
                    },
                    'filter' => true
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
                    'template' => '{print-barril}',
                    'buttons' => [
                        'print-barril' => function ($url, $model) {
                            return Html::a(
                                '<span class="fas fa-print text-green">Caja</span>',
                                Url::to(
                                    'yii2-prod/views/cajas/pdf-caja.php?codigoBarra=' . $model->CodigoBarra . "&consecutivo=" . $model->DOCUMENTO_INV,
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
                'id' => 'kv-cajas',
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
                            Html::a('<i class="fas fa-plus"></i> Agregar', ['create-caja'], [
                                'class' => 'btn btn-success',
                                'data-pjax' => 0,
                            ]) . ' ' .
                            Html::a('<i class="fas fa-redo"></i>', ['index-cajas', 'condicionImprimir' => ''], [
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
                    'heading' => '<i class="fas fa-recycle"></i> &nbsp;Registros de cajas',
                ],
                'persistResize' => false,
            ]);
            ?>
        </div>
    </div>
</div>
<script>
    let formularioImprimirBarriles = document.getElementById('form-imprimir-barril')
    let botonFormularioImprimirBarriles = document.getElementById('boton-imprimir-barriles')
    let fechaImprimirBarriles = document.getElementById('fecha-imprimir-barriles')
    let consecutivoImprimirBarriles = document.getElementById('consecutivo-imprimir-barriles')

    botonFormularioImprimirBarriles.addEventListener('click', (e) => {
        e.preventDefault();
        if (consecutivoImprimirBarriles.value.length == 0 || fechaImprimirBarriles.value.length == 0) {
            retrun
        }
        window.open(`http://localhost/yii2-prod/views/barriles/pdf-barriles.php?consecutivo=${consecutivoImprimirBarriles.value}&fecha=${fechaImprimirBarriles.value}`)
    })
</script>
<?= $imprimir?>