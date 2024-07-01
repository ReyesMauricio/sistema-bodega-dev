<?php

use app\models\RegistroModel;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\modelsSearch\RegistroModelSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Registro Models';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="registro-model-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Registro Model', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'IdRegistro',
            'CodigoBarra',
            'Articulo',
            'Descripcion',
            'Clasificacion',
            //'Libras',
            //'Unidades',
            //'IdTipoEmpaque',
            //'IdUbicacion',
            //'EmpacadoPor',
            //'ProducidoPor',
            //'BodegaCreacion',
            //'BodegaActual',
            //'Observaciones',
            //'UsuarioCreacion',
            //'DOCUMENTO_INV',
            //'Estado',
            //'Activo',
            //'Costo',
            //'FechaCreacion',
            //'FechaModificacion',
            //'Sesion',
            //'IdTipoRegistro',
            //'CreateDate',
            //'ContadorImpresiones',
            //'EmpresaDestino',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, RegistroModel $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'IdRegistro' => $model->IdRegistro]);
                 }
            ],
        ],
    ]); ?>


</div>
