<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\DetalleRegistroModel $model */

$this->title = 'Create Detalle Registro Model';
$this->params['breadcrumbs'][] = ['label' => 'Detalle Registro Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="detalle-registro-model-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
