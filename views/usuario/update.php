<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\UsuarioModel $model */

$this->title = 'Actualizar usuario';
$this->params['breadcrumbs'][] = ['label' => 'Listado', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Detalle', 'url' => ['view', 'IdUsuario' => $model->IdUsuario]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="usuario-model-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
