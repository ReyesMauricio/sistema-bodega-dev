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

$this->title = 'Listado de codigos FARDO/PACA';
$this->params['breadcrumbs'][] = $this->title;

?>
<style>
.text-center {
    text-align: center;
    display: flex;
    justify-content: center; /* Centra el contenido horizontalmente */
    align-items: center;    /* Centra el contenido verticalmente */
    height: 100%;           /* Asegura que el contenedor ocupe todo el alto */
}
</style>
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
                        'filter' => false
                    ],
                    [
                        'class' => 'kartik\grid\DataColumn',
                        'attribute' => 'Articulo',
                        'width' => '125px',
                        'hAlign' => 'center',
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $widget) {
                            return $model->Articulo;
                        },
                        'filter' => false
                    ],
                    [
                        'class' => 'kartik\grid\DataColumn',
                        'width' => '180px',
                        'format' => 'raw',
                        'vAlign' => 'middle',
                        'hAlign' => 'center',
                        'attribute' => 'Descripcion',
                        'value' => function ($model) {
                            return $model->Descripcion;
                        },
                        'filter' => false
                    ],
                    [
                        'class' => 'kartik\grid\DataColumn',
                        'attribute' => 'Clasificacion',
                        'width' => '230px',
                        'hAlign' => 'center',
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $widget) {
                            return $model->Clasificacion;
                        },
                        'filter' => false
                    ],
                    
                    [
                        'class' => 'kartik\grid\DataColumn',
                        'attribute' => 'Libras',
                        'width' => '230px',
                        'hAlign' => 'center',
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $widget) {
                            return $model->Libras;
                        },
                        'filter' => false
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
                        'heading' => '<div>LISTADO DE CÓDIGOS FARDOS-PACA-BARRILES EN MESA: '.$mesa.'</div><div style="display: flex; justify-content: center;">
                    </div>',
                    ],
                    'persistResize' => false,
                ]);
            ?>
        </div>
    </div>
</div>