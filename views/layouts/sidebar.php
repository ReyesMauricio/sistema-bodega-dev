<?php

use yii\helpers\Url;
?>
<style>
    .brand-link {
        border-bottom: none !important;
    }
</style>

<aside class="main-sidebar sidebar-dark-warning elevation-4" style="z-index: 1040 !important;">
    <!-- Brand Logo -->
    <a href="<?= Url::home() ?>" class="brand-link">
        <img src="/sistema-bodega/web/logo.png" alt="Logo" class="brand-image" style="opacity: .8">
        <span class="brand-text font-weight-light">
            New York Center
        </span>
        </br>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) >
        <div-- class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="<?= $assetDir ?>/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">Alexander Pierce</a>
            </div>
        </div-->

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent nav-compact" data-widget="treeview" role="menu" data-accordion="false">

                <!------- DASHBOARD ------->
                <?php if (Yii::$app->controller->id == 'site' && in_array(\Yii::$app->controller->action->id, ['index'])) {
                    $li = "nav-item active";
                    $a = "nav-link active";
                } else {
                    $li = "nav-item ";
                    $a = "nav-link ";
                }
                ?>
                <li class="<?= $li ?>"><a class="<?= $a ?>" href="<?php echo Url::toRoute(['/site/index']); ?>"><i class="nav-icon fas fa-home"></i>
                        <p>Home</p>
                    </a>
                </li>
                <!------- END DASHBOARD ------->

                <!------- MENU CONTENEDORES ------->
                <?php if (Yii::$app->controller->id == 'registro') {
                    $li = "nav-item has-treeview active menu-open";
                    $a = "nav-link active";
                } else {
                    $li = "nav-item has-treeview";
                    $a = "nav-link";
                } ?>
                <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="#"><i class="nav-icon fas fa-truck"></i>
                        <p>Contenedores <i class="right fas fa-angle-left"></i> </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'registro' && in_array(\Yii::$app->controller->action->id, ['index-contenedor'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/registro/index-contenedor']); ?>"><i class="nav-icon far fa-circle text-red"></i>
                                <p>Ver contenedores</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'registro' && in_array(\Yii::$app->controller->action->id, ['index-codigos-contenedor'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/registro/index-codigos-contenedor']); ?>"><i class="nav-icon far fa-circle text-blue"></i>
                                <p>Ver codigos de barra</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'registro' && in_array(\Yii::$app->controller->action->id, ['create-contenedor'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/registro/create-contenedor']); ?>"><i class="nav-icon far fa-circle text-green"></i>
                                <p>Crear registro</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'registro' && in_array(\Yii::$app->controller->action->id, ['valor-contenedor'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/registro/valor-contenedor']); ?>"><i class="nav-icon far fa-circle text-yellow"></i>
                                <p>Valor de contenedor</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                    </ul>
                </li>
                <!------- FIN MENU #1 ------->
                <!------- MENU SEPARACION ------->
                <?php if (Yii::$app->controller->id == 'barriles') {
                    $li = "nav-item has-treeview active menu-open";
                    $a = "nav-link active";
                } else {
                    $li = "nav-item has-treeview";
                    $a = "nav-link";
                } ?>
                <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="#"><i class="nav-icon fas fa-recycle"></i>
                        <p>Separación <i class="right fas fa-angle-left"></i> </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <!--------------------------------------------------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'barriles' && in_array(\Yii::$app->controller->action->id, ['index-verificar-codigo'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/barriles/index-verificar-codigo']); ?>"><i class="nav-icon far fa-circle text-blue"></i>
                                <p>Separar barriles</p>
                            </a>
                        </li>
                        <!---------------------------------------------------------------------------------------->
                        <!---------------------------------------------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'barriles' && in_array(\Yii::$app->controller->action->id, ['index-barriles'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/barriles/index-barriles', 'condicionImprimir' => '']); ?>"><i class="nav-icon far fa-circle text-red"></i>
                                <p>Barriles</p>
                            </a>
                        </li>
                        <!---------------------------------------------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'barriles' && in_array(\Yii::$app->controller->action->id, ['index-lista-cajas'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/barriles/index-lista-cajas']); ?>"><i class="nav-icon far fa-circle text-muted"></i>
                                <p>Cajas</p>
                            </a>
                        </li>
                        <!---------------------------------------------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'barriles' && in_array(\Yii::$app->controller->action->id, ['index-cajas'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/barriles/index-cajas', 'condicionImprimir' => '']); ?>"><i class="nav-icon far fa-circle text-purple"></i>
                                <p>Crear caja</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <!---------------------------------------------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'barriles' && in_array(\Yii::$app->controller->action->id, ['index-verificar-transaccion'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/barriles/index-verificar-transaccion']); ?>"><i class="nav-icon far fa-circle text-white"></i>
                                <p>Transacciones</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'barriles' && in_array(\Yii::$app->controller->action->id, ['index-existencia-mesas'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/barriles/index-existencia-mesas']); ?>"><i class="nav-icon far fa-circle text-yellow"></i>
                                <p>Existencia en mesa</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'cajas' && in_array(\Yii::$app->controller->action->id, ['index-cajas'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <!--li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/cajas/index-cajas', 'condicionImprimir' => '']); ?>"><i class="nav-icon far fa-circle text-yellow"></i>
                                <p>Ver cajas</p>
                            </a>
                        </li>

                        <-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'cajas' && in_array(\Yii::$app->controller->action->id, ['create-caja'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <!--li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/cajas/create-caja']); ?>"><i class="nav-icon far fa-circle text-pink"></i>
                                <p>Separar en cajas</p>
                            </a>
                        </li>

                        <-------------------------------------------------->
                    </ul>
                </li>
                <!------- FIN MENU #1 ------->
                <!------- MENU SALIDA DE INVENTARIO ------->
                <?php if (Yii::$app->controller->id == 'mesas') {
                    $li = "nav-item has-treeview active menu-open";
                    $a = "nav-link active";
                } else {
                    $li = "nav-item has-treeview";
                    $a = "nav-link";
                } ?>
                <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="#"><i class="nav-icon fas fa-table"></i>
                        <p>Trabajo de mesa <i class="right fas fa-angle-left"></i> </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'mesas' && in_array(\Yii::$app->controller->action->id, ['index-mesas'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/mesas/index-mesas']); ?>"><i class="nav-icon far fa-circle text-red"></i>
                                <p>Ver disponibles</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'mesas' && in_array(\Yii::$app->controller->action->id, ['produccion-mesa'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/mesas/produccion-mesa']); ?>"><i class="nav-icon far fa-circle text-grey"></i>
                                <p>Asignar a mesa</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'mesas' && in_array(\Yii::$app->controller->action->id, ['index-codigos-mesas'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/mesas/index-codigos-mesas']); ?>"><i class="nav-icon far fa-circle text-blue"></i>
                                <p>Ver mesas</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'mesas' && in_array(\Yii::$app->controller->action->id, ['create-mesa'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/mesas/create-mesa']); ?>"><i class="nav-icon far fa-circle text-green"></i>
                                <p>Crear registro</p>
                            </a>
                        </li>

                    </ul>
                </li>
                <!------- FIN MENU ------->
                <!------- MENU PRODUCCION ------->
                <?php if (Yii::$app->controller->id == 'detalle-registro') {
                    $li = "nav-item has-treeview active menu-open";
                    $a = "nav-link active";
                } else {
                    $li = "nav-item has-treeview";
                    $a = "nav-link";
                } ?>
                <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="#"><i class="nav-icon fas fa-briefcase"></i>
                        <p>Produccion <i class="right fas fa-angle-left"></i> </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'detalle-registro' && in_array(\Yii::$app->controller->action->id, ['index'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/detalle-registro/index']); ?>"><i class="nav-icon far fa-circle text-red"></i>
                                <p>Ver produccion</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'detalle-registro' && in_array(\Yii::$app->controller->action->id, ['create-detalle'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/detalle-registro/create-detalle']); ?>"><i class="nav-icon far fa-circle text-blue"></i>
                                <p>Crear registro</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'detalle-registro' && in_array(\Yii::$app->controller->action->id, ['consultar'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/detalle-registro/consultar']); ?>"><i class="nav-icon far fa-circle text-green"></i>
                                <p>Consultar</p></p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'detalle-registro' && in_array(\Yii::$app->controller->action->id, ['crear-articulo'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/detalle-registro/crear-articulo']); ?>"><i class="nav-icon far fa-circle text-yellow"></i>
                                <p>Crear articulo</p></p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'detalle-registro' && in_array(\Yii::$app->controller->action->id, ['mostrar-existencias'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/detalle-registro/mostrar-existencias']); ?>"><i class="nav-icon far fa-circle text-white"></i>
                                <p>Ver existencias</p></p>
                            </a>
                        </li>
                    </ul>
                </li>
                <!------- FIN MENU ------->
                <!------- MENU SALIDA DE INVENTARIO ------->
                <?php if (Yii::$app->controller->id == 'traslados') {
                    $li = "nav-item has-treeview active menu-open";
                    $a = "nav-link active";
                } else {
                    $li = "nav-item has-treeview";
                    $a = "nav-link";
                } ?>
                <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="#"><i class="nav-icon fas fa-truck"></i>
                        <p>Movimientos <i class="right fas fa-angle-left"></i> </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'traslados' && in_array(\Yii::$app->controller->action->id, ['index-traslados'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/traslados/index-traslados']); ?>"><i class="nav-icon far fa-circle text-red"></i>
                                <p>Ver movimientos</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'traslados' && in_array(\Yii::$app->controller->action->id, ['create-traslado'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/traslados/create-traslado']); ?>"><i class="nav-icon far fa-circle text-blue"></i>
                                <p>Crear traslado</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'traslados' && in_array(\Yii::$app->controller->action->id, ['create-despacho'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/traslados/create-despacho']); ?>"><i class="nav-icon far fa-circle text-green"></i>
                                <p>Crear despacho</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'traslados' && in_array(\Yii::$app->controller->action->id, ['validar-movimiento'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/traslados/validar-movimiento']); ?>"><i class="nav-icon far fa-circle text-yellow"></i>
                                <p>Validar movimiento</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'traslados' && in_array(\Yii::$app->controller->action->id, ['consultar-movimientos'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/traslados/consultar-movimientos']); ?>"><i class="nav-icon far fa-circle text-purple"></i>
                                <p>Consultar movimientos</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <!------- FIN MENU ------->
                <!------- MENU REPORTES ------->
                <?php if (Yii::$app->controller->id == 'reportes') {
                    $li = "nav-item has-treeview active menu-open";
                    $a = "nav-link active";
                } else {
                    $li = "nav-item has-treeview";
                    $a = "nav-link";
                } ?>
                <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="#"><i class="nav-icon fas fa-file"></i>
                        <p>Reportes <i class="right fas fa-angle-left"></i> </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <!-------------------------------------------------->
                        
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'reportes' && in_array(\Yii::$app->controller->action->id, ['reporte-buscar-codigo'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/reportes/reporte-buscar-codigo']); ?>"><i class="nav-icon far fa-circle text-blue"></i>
                                <p>Buscar codigo</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'reportes' && in_array(\Yii::$app->controller->action->id, ['reporte-busqueda-multiple'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/reportes/reporte-busqueda-multiple']); ?>"><i class="nav-icon far fa-circle text-green"></i>
                                <p>Buscar varios codigos</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'reportes' && in_array(\Yii::$app->controller->action->id, ['reporte-produccion-persona'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/reportes/reporte-produccion-persona']); ?>"><i class="nav-icon far fa-circle text-yellow"></i>
                                <p>Produccion por persona</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'reportes' && in_array(\Yii::$app->controller->action->id, ['reporte-produccion-mesa'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/reportes/reporte-produccion-mesa']); ?>"><i class="nav-icon far fa-circle text-pink"></i>
                                <p>Produccion por mesa</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'reportes' && in_array(\Yii::$app->controller->action->id, ['reporte-produccion-dia'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/reportes/reporte-produccion-dia']); ?>"><i class="nav-icon far fa-circle text-purple"></i>
                                <p>Produccion por dia</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'reportes' && in_array(\Yii::$app->controller->action->id, ['reporte-existencia-contenedor'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/reportes/reporte-existencia-contenedor']); ?>"><i class="nav-icon far fa-circle text-orange"></i>
                                <p>Existencia de contenedores</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'reportes' && in_array(\Yii::$app->controller->action->id, ['reporte-asignacion-produccion'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/reportes/reporte-asignacion-produccion']); ?>"><i class="nav-icon far fa-circle text-white"></i>
                                <p>Asignacion en produccion</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'reportes' && in_array(\Yii::$app->controller->action->id, ['reporte-asignacion-clasificacion'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/reportes/reporte-asignacion-clasificacion']); ?>"><i class="nav-icon far fa-circle text-red"></i>
                                <p>Asignacion en clasificacion</p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                    </ul>
                </li>
                <!------- MENU USUARIOS BODEGA ------->
                <?php if (Yii::$app->controller->id == 'usuario') {
                    $li = "nav-item has-treeview active menu-open";
                    $a = "nav-link active";
                } else {
                    $li = "nav-item has-treeview";
                    $a = "nav-link";
                } ?>
                <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="#"><i class="nav-icon fas fa-users"></i>
                        <p>Usuarios bodega <i class="right fas fa-angle-left"></i> </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (Yii::$app->controller->id == 'usuario' && in_array(\Yii::$app->controller->action->id, ['index'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/usuario/index']); ?>"><i class="nav-icon far fa-circle text-danger"></i>
                                <p>Ver usuarios </p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                        <?php if (Yii::$app->controller->id == 'usuario' && in_array(\Yii::$app->controller->action->id, ['create'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/usuario/create']); ?>"><i class="nav-icon far fa-circle text-blue"></i>
                                <p>Crear usuarios </p>
                            </a>
                        </li>
                        <!-------------------------------------------------->
                    </ul>
                </li>
                <!------- FIN MENU USUARIOS ------->
                <!------- MENU USUARIOS ------->
                <?php if (Yii::$app->controller->id == 'usuarios' || Yii::$app->controller->id == 'route' || Yii::$app->controller->id == 'permission' || Yii::$app->controller->id == 'role' || Yii::$app->controller->id == 'assignment') {
                    $li = "nav-item has-treeview active menu-open";
                    $a = "nav-link active";
                } else {
                    $li = "nav-item has-treeview";
                    $a = "nav-link";
                } ?>
                <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="#"><i class="nav-icon fas fa-users"></i>
                        <p>Usuarios <i class="right fas fa-angle-left"></i> </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (Yii::$app->controller->id == 'usuarios' && in_array(\Yii::$app->controller->action->id, ['index', 'signup'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/usuarios/index']); ?>"><i class="nav-icon far fa-circle text-danger"></i>
                                <p>Gestionar usuarios </p>
                            </a></li>

                        <?php if (Yii::$app->controller->id == 'route' && in_array(\Yii::$app->controller->action->id, ['index'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/rbac/route']); ?>"><i class="nav-icon far fa-circle text-blue"></i>
                                <p>Gestionar rutas </p>
                            </a></li>

                        <?php if (Yii::$app->controller->id == 'permission' && in_array(\Yii::$app->controller->action->id, ['index'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/rbac/permission']); ?>"><i class="nav-icon far fa-circle text-purple"></i>
                                <p>Gestionar permisos </p>
                            </a></li>

                        <?php if (Yii::$app->controller->id == 'role' && in_array(\Yii::$app->controller->action->id, ['index'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/rbac/role']); ?>"><i class="nav-icon far fa-circle text-green"></i>
                                <p>Gestionar roles </p>
                            </a></li>

                        <?php if (Yii::$app->controller->id == 'assignment' && in_array(\Yii::$app->controller->action->id, ['index'])) {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/rbac/assignment']); ?>"><i class="nav-icon far fa-circle text-yellow"></i>
                                <p>Asignar rol </p>
                            </a></li>
                    </ul>
                </li>
                <!------- FIN MENU USUARIOS ------->

                <!------- MENU DEVS ------->
                <?php if (Yii::$app->controller->id == 'gii' || Yii::$app->controller->id == 'debug') {
                    $li = "nav-item has-treeview active menu-open";
                    $a = "nav-link active";
                } else {
                    $li = "nav-item has-treeview";
                    $a = "nav-link";
                } ?>
                <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="#"><i class="nav-icon fas fa-file-code"></i>
                        <p>Devs <i class="right fas fa-angle-left"></i> </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (Yii::$app->controller->id == 'gii') {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/gii']); ?>"><i class="nav-icon far fa-circle text-danger"></i>
                                <p>Gii </p>
                            </a></li>

                        <?php if (Yii::$app->controller->id == 'debug') {
                            $li = "nav-item active";
                            $a = "nav-link active";
                        } else {
                            $li = "nav-item";
                            $a = "nav-link";
                        }
                        ?>
                        <li class="<?= $li; ?>"><a class="<?= $a; ?>" href="<?php echo Url::toRoute(['/debug']); ?>"><i class="nav-icon far fa-circle text-blue"></i>
                                <p>Debug </p>
                            </a></li>
                    </ul>
                </li>
                <!------- FIN MENU DEVS ------->
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>