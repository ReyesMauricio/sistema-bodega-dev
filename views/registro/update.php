<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RegistroModel $model */

$this->title = 'Update Registro Model: ' . $model->IdRegistro;
$this->params['breadcrumbs'][] = ['label' => 'Registro Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->IdRegistro, 'url' => ['view', 'IdRegistro' => $model->IdRegistro]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="registro-model-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
