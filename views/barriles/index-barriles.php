<?php
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

$this->title = 'Listado de barriles';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="col-md-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <div class="float-right">
                    
                </div>
                <div class="m-0">
                    <h5><i class="fas fa-briefcase"></i> &nbsp;<?= $this->title ?></h5>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="float-right p-2">
                    <?php
                    echo Html::a('<i class="fas fa-plus"></i> Asignar libras a mesas', ['create-barril'], [
                        'class' => 'btn btn-success',
                        'data-pjax' => 0,
                    ]) . '&nbsp;&nbsp; ' .
                        Html::a('<i class="fas fa-redo"></i>', ['index-barriles', 'condicionImprimir' => ''], [
                            'class' => 'btn btn-outline-success',
                            'data-pjax' => 0,
                        ]);
                    ?>
                </div>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                        'class' => 'kartik\grid\DataColumn',
                        'width' => '300px',
                        'format' => 'raw',
                        'vAlign' => 'middle',
                        'hAlign' => 'center',
                        'attribute' => 'CodigoBarra',
                        'value' => function ($model, $key, $index, $widget) {
                            return Html::tag('span', $model->CodigoBarra, ['class' => 'badge bg-purple']);
                        },
                        'filter' => true,
                        ],
                        [
                        'class' => 'kartik\grid\DataColumn',
                        'width' => '200px',
                        'format' => 'raw',
                        'vAlign' => 'middle',
                        'hAlign' => 'center',
                        'attribute' => 'Articulo',
                        'value' => function ($model, $key, $index, $widget) {
                            return Html::tag('span', $model->Articulo, ['class' => 'badge bg-green']);
                        },
                        'filter' => true,
                        ],
                        [
                        'class' => 'kartik\grid\DataColumn',
                        'width' => '400px',
                        'format' => 'raw',
                        'vAlign' => 'middle',
                        'hAlign' => 'center',
                        'attribute' => 'Descripcion',
                        'value' => function ($model, $key, $index, $widget) {
                            return Html::tag('span', $model->Descripcion);
                        },
                        'filter' => false,
                        ],
                        [
                        'class' => 'kartik\grid\DataColumn',
                        'width' => '100px',
                        'format' => 'raw',
                        'vAlign' => 'middle',
                        'hAlign' => 'center',
                        'attribute' => 'Libras',
                        'value' => function ($model, $key, $index, $widget) {
                            return Html::tag('span', $model->Libras);
                        },
                        'filter' => false,
                        ],
                        [
                        'class' => 'kartik\grid\DataColumn',
                        'width' => '80px',
                        'format' => 'raw',
                        'vAlign' => 'middle',
                        'hAlign' => 'center',
                        'attribute' => 'BodegaActual',
                        'value' => function ($model, $key, $index, $widget) {
                            return Html::tag('span', $model->BodegaActual);
                        },
                        'filter' => false,
                        ],
                        [
                        'class' => 'kartik\grid\DataColumn',
                        'width' => '400px',
                        'format' => 'raw',
                        'vAlign' => 'middle',
                        'hAlign' => 'center',
                        'attribute' => 'CreateDate',
                        'value' => function ($model, $key, $index, $widget) {
                            return Html::tag('span', $model->CreateDate);
                        },
                        'filter' => false,
                        ],
                        [
                        'class' => 'kartik\grid\DataColumn',
                        'width' => '80px',
                        'format' => 'raw',
                        'vAlign' => 'middle',
                        'hAlign' => 'center',
                        'attribute' => 'UsuarioCreacion',
                        'value' => function ($model, $key, $index, $widget) {
                            return Html::tag('span', $model->UsuarioCreacion);
                        },
                        'filter' => false,
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view}',
                            'buttons' => [
                                'view' => function ($url, $model, $key) {
                                $ruta = substr(Yii::$app->request->baseUrl, 0, -3);    
                                $url = Url::to(
                                    $ruta .'views/barriles/pdf-barril.php?codigoBarra=' . $model->CodigoBarra . '&IdRegistro=' . $model->IdRegistro,
                                    true,
                                );
                                //Url::to(['barriles/pdf-barril.php', 'codigoBarra' => $model->CodigoBarra, 'IdRegistro' => $model->IdRegistro]);
                                return Html::a('<span class="fas fa-print text-green">Barril</span>', $url, ['target' => '_blank']);
                            },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
<?= $imprimir ?>