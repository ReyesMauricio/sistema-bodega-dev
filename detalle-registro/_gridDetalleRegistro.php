<?php
Yii::$app->language = 'es_ES';

use app\models\DetalleRegistroModel;
use app\models\TblEspecies;
use app\models\TblPacientes;
use app\models\TblRazas;
use app\models\TblRepresentantes;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use kartik\export\ExportMenu;
use yii\bootstrap4\Modal;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OsigSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$clasificacion = $model->Clasificacion;
?>

<?php
Modal::begin([
    'options' => [
        'tabindex' => false,
    ],
    'headerOptions' => ['class' => 'bg-primary'],
    'title' => 'Crear detalle de registro',
    'id' => 'create-modal',
    'size' => 'modal-xl',
    'class' => 'bg-primary'
]);
echo "<div id='createModalContent'></div>";
Modal::end();
?>
<?php Pjax::begin(['id' => 'datosGrid-detalle']); ?>
<div class="row">
    <!-- left column -->
    <div class="col-md-12">
        <div class="card p-0">

            <?php // echo $this->render('_search', ['model' => $searchModel]); 
            ?>
            <?php
            $gridColumns = [
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
                    'attribute' => 'idRegistro.CodigoBarra',
                    'value' => function ($model) {
                        return $model->idRegistro->CodigoBarra;
                    },
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '80px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'idRegistro.Libras',
                    'value' => function ($model) {
                        return  $model->idRegistro->Libras;
                    },
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '80px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'idRegistro.Articulo',
                    'value' => function ($model) {
                        return $model->idRegistro->Articulo;
                    },
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '80px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'idRegistro.Articulo',
                    'value' => function ($model) {
                        return $model->idRegistro->Descripcion;
                    },
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '80px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'ArticuloDetalle',
                    'value' => function ($model) {
                        return Html::tag('span', $model->ArticuloDetalle, ['class' => 'badge bg-purple']);
                    },
                ],

                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '80px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'label' => 'Descripcion del articulo',
                    'attribute' => 'ArticuloDetalle',
                    'value' => function ($model) {
                        $user = new Yii\db\Connection([
                            'dsn'  => 'sqlsrv:Server=192.168.0.44;Database=SOFTLAND',
                            'username' => 'MCAMPOS',
                            'password' =>  'exmcampos',
                            'charset' => 'utf8',
                        ]);
                        $clasificacion = $user->createCommand("SELECT DESCRIPCION, CLASIFICACION_2 FROM CONINV.ARTICULO WHERE ARTICULO = '$model->ArticuloDetalle'")->queryOne();

                        return $clasificacion["DESCRIPCION"] . ' - ' . $clasificacion["CLASIFICACION_2"];
                    },
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '80px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'Cantidad',
                    'value' => function ($model) {
                        return  $model->Cantidad;
                    },
                    'pageSummary' => true,
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'width' => '80px',
                    'format' => 'raw',
                    'vAlign' => 'middle',
                    'hAlign' => 'center',
                    'attribute' => 'PrecioUnitario',
                    'value' => function ($model) {
                        return $model->PrecioUnitario;
                    },

                ],
                [
                    'class' => 'kartik\grid\FormulaColumn',
                    'header' => 'Total',
                    'vAlign' => 'middle',
                    'value' => function ($model, $key, $index, $widget) {
                        $p = compact('model', 'key', 'index');
                        return $widget->col(7, $p) * $widget->col(8, $p);
                    },
                    'headerOptions' => ['class' => 'kartik-sheet-style'],
                    'hAlign' => 'center',
                    'format' => ['currency'],
                    'width' => '7%',
                    'pageSummary' => true,
                    'footer' => true
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'template' => '{edit}  {delete}',
                    'buttons' => [
                        'edit' => function ($url, $model) {
                            if ($model->idRegistro->Estado == 'PROCESO') {
                                return Html::a(
                                    '<span class="fas fa-pencil-alt"></span>',
                                    [
                                        'edit-modal-detalle',
                                        'IdRegistro' => $model->IdRegistro,
                                        'IdDetalleRegistro' => $model->IdDetalleRegistro,

                                    ],
                                );
                            }
                        },
                        'delete' => function ($url, $model) {
                            if ($model->idRegistro->Estado == 'PROCESO') {
                                return Html::a(
                                    '<span class="fas fa-trash"></span>',
                                    [
                                        'delete-detalle',
                                        'IdDetalleRegistro' => $model->IdDetalleRegistro,
                                        'codigoBarra' => $model->idRegistro->CodigoBarra,
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
            $agregar = $model->Estado == 'PROCESO' ? Html::button('<i class="fa fa-plus"></i> Agregar detalle', [
                'value' => Url::to('index.php?r=detalle-registro/create-modal-detalle&IdRegistro=' . $model->IdRegistro . '&clasificacion=' . $model->Clasificacion),
                'class' => 'btn btn-warning', 'id' => 'modalButton'
            ]) : '';
            echo GridView::widget([
                'id' => 'datosGrid-detalle',
                'dataProvider' => $dataProvider,
                //'filterModel' => $searchModel,
                'columns' => $gridColumns,
                'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
                'headerRowOptions' => ['class' => 'kartik-sheet-style'],
                'filterRowOptions' => ['class' => 'kartik-sheet-style'],
                'pjax' => false, // pjax is set to always true for this demo
                // set your toolbar
                'toolbar' =>  [
                    $exportmenu,
                    [
                        'content' =>
                        ' &nbsp&nbsp ' .
                            Html::a(
                                '<span class="fas fa-print"> Imprimir desglose</span>',
                                Url::to(
                                    substr(Yii::$app->request->baseUrl, 0, -3) . 'views/detalle-registro/pdf-detalle-registro.php?codigoBarra=' . $model->CodigoBarra,
                                    true,
                                ),
                                ['target' => '_blank', 'class' => 'btn btn-outline-success', 'data-pjax' => 0]
                            )
                            . ' &nbsp&nbsp ' . $agregar . ' &nbsp&nbsp ' .
                            Html::a('<i class="fas fa-redo"></i>', ['view', 'codigoBarra' => $model->CodigoBarra, 'condicionImprimir' => ''], [
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
                'showPageSummary' => true,
                'floatPageSummary' => true,
                'panel' => [
                    'type' => GridView::TYPE_PRIMARY,
                    'heading' => '<i class="fas fa-briefcase"></i> &nbsp;Detalle de registro',
                    'footer' => false
                ],
                'persistResize' => false,
            ]);
            ?>

        </div>
    </div>
</div>
<?php Pjax::end(); ?>
<?= $imprimir ?>