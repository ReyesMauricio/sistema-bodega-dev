<?php
Yii::$app->language = 'es_ES';

use app\models\RegistroModel;
use app\models\TipoEmpaqueModel;
use app\models\TrabajoMesaModel;
use app\models\TransaccionModel;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use kartik\export\ExportMenu;
use yii\helpers\ArrayHelper;
use yii\bootstrap4\Alert;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OsigSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Listado de codigos de barra para asignar a mesa';
$this->params['breadcrumbs'][] = $this->title;

foreach (Yii::$app->session->getAllFlashes() as $key => $message) {
    echo Alert::widget([
        'options' => ['class' => 'alert-' . $key],
        'body' => $message,
    ]);
}
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
                    'width' => '60px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'CodigoBarra',
                    'value' => function ($model, $key, $index, $widget) {

                        return $model->CodigoBarra;
                    },
                    //'filterType' => GridView::FILTER_SELECT2,
                    //'filter' => ArrayHelper::map(RegistroModel::find()
                        //->where("Estado = 'FINALIZADO' AND Activo = 1 AND IdTipoRegistro NOT IN (1, 3)")->all(), 'CodigoBarra', 'CodigoBarra'),
                    //'filterWidgetOptions' => [
                        //'options' => ['placeholder' => 'Todos...'],
                        //'pluginOptions' => [
                            //'allowClear' => true
                        //],
                    //], 
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '180px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'Articulo',
                    'value' => function ($model, $key, $index, $widget) {
                        return Html::tag('span', $model->Articulo . ' - ' . $model->Descripcion, ['class' => 'badge bg-green']);
                    },
                    // 'filterType' => GridView::FILTER_SELECT2,
                    // 'filter' => ArrayHelper::map(RegistroModel::find()
                    //     ->where("Estado = 'FINALIZADO' AND Activo = 1 AND IdTipoRegistro NOT IN (1, 3)")
                    //     ->orderBy('Articulo')->all(), 'Articulo', 'Articulo'),
                    // 'filterWidgetOptions' => [
                    //     'options' => ['placeholder' => 'Todos...'],
                    //     'pluginOptions' => [
                    //         'allowClear' => true
                    //     ],
                    // ],
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
                    // 'filterType' => GridView::FILTER_SELECT2,
                    // 'filter' => ArrayHelper::map(RegistroModel::find()
                    //     ->where("Estado = 'FINALIZADO' AND Activo = 1 AND IdTipoRegistro NOT IN (1, 3)")
                    //     ->orderBy('Clasificacion')->all(), 'Clasificacion', 'Clasificacion'),
                    // 'filterWidgetOptions' => [
                    //     'options' => ['placeholder' => 'Todos...'],
                    //     'pluginOptions' => [
                    //         'allowClear' => true
                    //     ],
                    // ],
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '180px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'Libras',
                    'value' => function ($model, $key, $index, $widget) {
                        return Html::tag('span', $model->Libras, ['class' => 'badge bg-info']);
                    },
                    // 'filterType' => GridView::FILTER_SELECT2,
                    // 'filter' => ArrayHelper::map(RegistroModel::find()
                    //     ->where("Estado = 'FINALIZADO' AND Activo = 1 AND IdTipoRegistro NOT IN (1, 3)")
                    //     ->orderBy('Libras')->all(), 'Libras', 'Libras'),
                    // 'filterWidgetOptions' => [
                    //     'options' => ['placeholder' => 'Todos...'],
                    //     'pluginOptions' => [
                    //         'allowClear' => true
                    //     ],
                    // ],
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '180px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'IdTipoEmpaque',
                    'value' => function ($model, $key, $index, $widget) {
                        return $model->tipoEmpaque->TipoEmpaque;
                    },
                    // 'filterType' => GridView::FILTER_SELECT2,
                    // 'filter' => ArrayHelper::map(TipoEmpaqueModel::find()
                    //     ->orderBy('TipoEmpaque')->all(), 'IdTipoEmpaque', 'TipoEmpaque'),
                    // 'filterWidgetOptions' => [
                    //     'options' => ['placeholder' => 'Todos...'],
                    //     'pluginOptions' => [
                    //         'allowClear' => true
                    //     ],
                    // ],
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
                    // 'filterType' => GridView::FILTER_SELECT2,
                    // 'filter' => ArrayHelper::map(RegistroModel::find()->orderBy('BodegaActual')->all(), 'BodegaActual', 'BodegaActual'),
                    // 'filterWidgetOptions' => [
                    //     'options' => ['placeholder' => 'Todos...'],
                    //     'pluginOptions' => [
                    //         'allowClear' => true
                    //     ],
                    // ],
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
                    // 'filterType' => GridView::FILTER_DATE,
                    // 'filterWidgetOptions' => [
                    //     'options' => ['placeholder' => 'Todos...'],
                    //     'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-m-dd', 'todayHighlight' => true],
                    // ],
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
                    // 'filterType' => GridView::FILTER_SELECT2,
                    // 'filter' => ArrayHelper::map(TransaccionModel::find()->orderBy('UsuarioCreacion')->all(), 'UsuarioCreacion', 'UsuarioCreacion'),
                    // 'filterWidgetOptions' => [
                    //     'options' => ['placeholder' => 'Todos...'],
                    //     'pluginOptions' => [
                    //         'allowClear' => true
                    //     ],
                    // ],
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
                            Html::a('<i class="fas fa-redo"></i>', ['index-mesas'], [
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
                    'heading' => '<i class="fas fa-table"></i> &nbsp; Codigos disponibles',
                ],
                'persistResize' => false,
            ]);
            ?>
        </div>
    </div>
</div>