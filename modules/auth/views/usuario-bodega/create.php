<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\UsuarioBodega $model */

$this->title = 'Create Usuario Bodega';
$this->params['breadcrumbs'][] = ['label' => 'Usuario Bodegas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="usuario-bodega-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
