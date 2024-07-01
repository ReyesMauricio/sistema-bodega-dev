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
                    'width' => '180px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'Articulo',
                    'label' => 'Articulo',
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
                    'attribute' => 'Costo',
                    'label' => 'Cantidad',
                    'hAlign' => 'center',
                    'value' => function ($model, $key, $index, $widget) {
                        return Html::tag('span', $model->Costo, ['class' => 'badge bg-purple']);
                    },
                    'filter' => false,
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
    let ruta = botonFinalizar.href
    botonFinalizar.addEventListener('click', (e) => {
        let fechaProduccion = document.getElementById('fecha-filtro-finalizacion').value;
        botonFinalizar.href += `&fecha=${fechaProduccion}`

        setTimeout(() => {
            let botones = document.querySelector('.bootstrap-dialog-footer-buttons')
            botones.childNodes[0].addEventListener('click', (e) => {
                botonFinalizar.href = ruta
            })
        }, 1000);

    })
</script>