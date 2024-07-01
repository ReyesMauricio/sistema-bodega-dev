<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\UsuarioModel $model */

$this->title = 'Crear nuevo usuario';
$this->params['breadcrumbs'][] = ['label' => 'Listado', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="usuario-model-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
