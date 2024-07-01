<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\TrabajoMesaModel $model */

$this->title = $model->id_trabajo_mesa;
$this->params['breadcrumbs'][] = ['label' => 'Trabajo Mesa Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="trabajo-mesa-model-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id_trabajo_mesa' => $model->id_trabajo_mesa], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id_trabajo_mesa' => $model->id_trabajo_mesa], [
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
            'id_trabajo_mesa',
            'NumeroMesa',
            'Libras',
            'Costo',
            'ProducidoPor',
            'Documento_inv',
            'Bodega',
            'Fecha',
            'CreateDate',
        ],
    ]) ?>

</div>
