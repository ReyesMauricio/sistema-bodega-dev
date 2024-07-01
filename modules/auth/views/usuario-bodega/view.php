<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\UsuarioBodega $model */

$this->title = $model->USUARIO;
$this->params['breadcrumbs'][] = ['label' => 'Usuario Bodegas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="usuario-bodega-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'USUARIO' => $model->USUARIO], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'USUARIO' => $model->USUARIO], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'USUARIO',
            'BODEGA',
            'CAJA',
            'PAQUETE',
            'CORREOSUP',
            'CORREOTIENDA',
            'BASE',
            'HAMACHI',
            'CENTRO_COSTO',
            'TIPO',
            'ESQUEMA',
            'PREFIJODOC',
            'RESOL_ACTUAL',
            'SERIEMAQUINA',
            'NUM_AUTORIZACION',
            'FECHA_AUTORIZACION',
            'CANT_LIMITE_TICKET',
            'RESOLUCION',
            'FECHA_SOLICITUD',
            'SERIE',
        ],
    ]) ?>

</div>
