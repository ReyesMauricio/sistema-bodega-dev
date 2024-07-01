<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RegistroModel $model */

$this->title = 'Create Registro Model';
$this->params['breadcrumbs'][] = ['label' => 'Registro Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="registro-model-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
