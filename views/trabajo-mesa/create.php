<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\TrabajoMesaModel $model */

$this->title = 'Create Trabajo Mesa Model';
$this->params['breadcrumbs'][] = ['label' => 'Trabajo Mesa Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="trabajo-mesa-model-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
