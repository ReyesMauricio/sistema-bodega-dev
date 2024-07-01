<?php

use yii\helpers\Html;

?>
<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-dark">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class=" nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>
    <h5 class="text-white mt-2">USUARIO: <?=Yii::$app->session->get('user')?></h5>
    <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            <?= Html::a('Salir <i class="text-danger fas fa-sign-out-alt"></i>', ['/site/logout'], ['data-method' => 'post', 'class' => 'nav-link']) ?>
        </li>
    </ul>


</nav>
<!-- /.navbar -->