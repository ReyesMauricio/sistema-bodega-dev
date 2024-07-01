<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\TrabajoMesaModel $model */

$this->title = 'Update Trabajo Mesa Model: ' . $model->id_trabajo_mesa;
$this->params['breadcrumbs'][] = ['label' => 'Trabajo Mesa Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id_trabajo_mesa, 'url' => ['view', 'id_trabajo_mesa' => $model->id_trabajo_mesa]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="trabajo-mesa-model-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
