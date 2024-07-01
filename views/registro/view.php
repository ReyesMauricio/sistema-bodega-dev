<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\RegistroModel $model */

$this->title = $model->IdRegistro;
$this->params['breadcrumbs'][] = ['label' => 'Registro Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="registro-model-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'IdRegistro' => $model->IdRegistro], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'IdRegistro' => $model->IdRegistro], [
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
            'IdRegistro',
            'CodigoBarra',
            'Articulo',
            'Descripcion',
            'Clasificacion',
            'Libras',
            'Unidades',
            'IdTipoEmpaque',
            'IdUbicacion',
            'EmpacadoPor',
            'ProducidoPor',
            'BodegaCreacion',
            'BodegaActual',
            'Observaciones',
            'UsuarioCreacion',
            'DOCUMENTO_INV',
            'Estado',
            'Activo',
            'Costo',
            'FechaCreacion',
            'FechaModificacion',
            'Sesion',
            'IdTipoRegistro',
            'CreateDate',
            'ContadorImpresiones',
            'EmpresaDestino',
        ],
    ]) ?>

</div>
