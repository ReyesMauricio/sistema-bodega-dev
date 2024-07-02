<?php

namespace app\controllers;

use app\models\RegistroModel;
use app\models\AsignacionClasificacion;
use app\models\TrabajoMesaRestanteModel;
use app\models\TransaccionModel;
use app\modelsSearch\RegistroModelSearch;
use app\modelsSearch\TrabajoMesaModelSearch;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;
use yii\base\DynamicModel;

class MesasController extends Controller
{

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Crear una nueva conexion hacia la base de datos SOFTLAND
     * @return Yii\db\Connection
     */
    function SoftlandConn()
    {
        return Yii::$app->db2;
    }
    /**
     * Muestra un listado de todos los codigos de barra disponibles
     */
    public function actionIndexMesas()
    {
        //$articulos = $this->getArticulosByEsquema();
        $searchModel = new RegistroModelSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, "Estado = 'FINALIZADO' AND Clasificacion NOT LIKE 'RIPIO' AND Activo = 1 AND IdTipoRegistro NOT IN (1, 3)");
        $dataProvider->sort->defaultOrder = ['CreateDate' => SORT_DESC];

        $searchModelTrabajoMesa = new TrabajoMesaModelSearch();
        $dataProviderTrabajoMesa = $searchModelTrabajoMesa->search($this->request->queryParams, '');

        return $this->render('index-mesas', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchModelTrabajoMesa' => $searchModelTrabajoMesa,
            'dataProviderTrabajoMesa' => $dataProviderTrabajoMesa,
            //'articulos' => $articulos
        ]);
    }

    public function actionIndexCodigosMesas()
    {
        $articulos = $this->getArticulosByEsquema("CNYCENTER");
        $searchModel = new TrabajoMesaModelSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, 'codigos-mesa');
        return $this->render('index-codigos-mesas', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider, 
            'articulos' => $articulos
        ]);
    }

    /**
     * Crea una salida de inventario a partir de un conjunto de codigos de barra
     */
    public function actionCreateMesa()
    {
        /*$finalizado = $this->validarFinalizarDia(date("Y-m-d"));
        if ($finalizado != 'FINALIZADO') {
            Yii::$app->session->setFlash('warning', "<i class='fas fa-exclamation-triangle'></i> &nbsp;&nbsp;<b>$finalizado!</b>");
            return $this->redirect(['site/index']);
        }*/

        $mesa = $this->crearModelMesa();
        $bodegas = $this->getBodegasByEsquema();
        $productores = $this->getProductores();
        if ($mesa->load($this->request->post())) {
            $codigosBarra = $this->trimCodigosBarra($mesa->codigos_barra);
            $duplicado = $this->verificarCodigosBarraDuplicados($codigosBarra);
            $totalLibras = 0;
            $totalCosto = 0;

            if ($duplicado != false) {
                Yii::$app->session->setFlash('danger', "Codigos de barra repetidos: " . $duplicado);
                $mesa = $this->crearModelMesa();
                return $this->render(
                    'form-create-mesa',
                    [
                        'bodegas' => $bodegas,
                        'mesa' => $mesa,
                        'detalle' => false,
                        'productores' => $productores,
                    ]
                );
            }

            foreach ($codigosBarra as $index => $codigoBarra) {
                $codigoDataREGISTRO = RegistroModel::find()->andWhere(['CodigoBarra' => $codigoBarra])->one();
                $codigoDataTRANSACCION = TransaccionModel::find()->andWhere(['CodigoBarra' => $codigoBarra])->one();

                $validaciones = $this->validarCodigoBarra($codigoDataREGISTRO, $codigoDataTRANSACCION, $mesa->bodega);
                $poseeRestante = TrabajoMesaRestanteModel::find()
                    ->select('Bodega, NumeroMesa, SUM(Libras) as Libras')
                    ->where(['NumeroMesa' => $mesa->mesa])
                    ->groupBy(['Bodega', 'NumeroMesa'])
                    ->one();

                if ($poseeRestante) {
                    if (($poseeRestante->Bodega != $codigoDataREGISTRO->BodegaActual) && $codigoDataREGISTRO->Clasificacion != 'RIPIO') {
                        Yii::$app->session->setFlash('danger', "Mesa posee restante (de bodega $poseeRestante->Bodega), no se puede mezclar de diferentes bodegas!");
                        $mesa = $this->crearModelMesa();
                        return $this->render(
                            'form-create-mesa',
                            [
                                'bodegas' => $bodegas,
                                'mesa' => $mesa,
                                'detalle' => false,
                                'productores' => $productores,
                            ]
                        );
                    }
                }

                if ($validaciones) {
                    $codigosBarra[$index] = $codigoBarra . ', '
                        . $codigoDataREGISTRO->Articulo . ', '
                        . $codigoDataREGISTRO->Clasificacion . ', '
                        . $codigoDataREGISTRO->Descripcion . ', '
                        . $codigoDataREGISTRO->Libras . ', '
                        . $codigoDataREGISTRO->BodegaActual . ', '
                        . $codigoDataREGISTRO->Costo . ', '
                        . $codigoDataREGISTRO->tipoEmpaque->TipoEmpaque;
                    $totalLibras += $codigoDataREGISTRO->Libras;
                    $totalCosto += $codigoDataREGISTRO->Costo;
                } else {
                    Yii::$app->session->setFlash('danger', "Codigos de barra no disponible: " . $codigoBarra);
                    $mesa = $this->crearModelMesa();
                    return $this->render(
                        'form-create-mesa',
                        [
                            'bodegas' => $bodegas,
                            'mesa' => $mesa,
                            'detalle' => false,
                            'productores' => $productores,
                        ]
                    );
                }
            }

            $codigosBarraJson = json_encode($codigosBarra);
            return $this->render(
                'form-create-mesa',
                [
                    'bodegas' => $bodegas,
                    'mesa' => $mesa,
                    'detalle' => true,
                    'registros' => $codigosBarra,
                    'registrosJson' => $codigosBarraJson,
                    'totalLibras' => $totalLibras,
                    'totalCosto' => $totalCosto,
                    'productores' => $productores,
                ]
            );
        } else {
            return $this->render('form-create-mesa', [
                'mesa' => $mesa,
                'bodegas' => $bodegas,
                'productores' => $productores,
                'detalle' => false,
            ]);
        }
    }

    public function actionProduccionMesa()
    {
        /*$finalizado = $this->validarFinalizarDia(date("Y-m-d"));
        if ($finalizado != 'FINALIZADO') {
            Yii::$app->session->setFlash('warning', "<i class='fas fa-exclamation-triangle'></i> &nbsp;&nbsp;<b>$finalizado!</b>");
            return $this->redirect(['site/index']);
        }*/
        $numeroDocumento = $this->request->get('NumeroDocumento');
        $mesa = $this->crearModelMesaBarril();
        //$bodegas = $this->getBodegasByEsquema();
        $productores = $this->getProductores();
        if ($mesa->load($this->request->post())) {
            $codigosBarra = $this->trimCodigosBarra($mesa->codigos_barra);
            $duplicado = $this->verificarCodigosBarraDuplicados($codigosBarra);
            $totalLibras = 0;
            $totalCosto = 0;

            if ($duplicado != false) {
                Yii::$app->session->setFlash('danger', "Codigos de barra repetidos: " . $duplicado);
                $mesa = $this->crearModelMesa();
                return $this->render(
                    'create-mesa-barril',
                    [
                        //'bodegas' => $bodega,
                        'mesa' => $mesa,
                        'detalle' => false,
                        'productores' => $productores,
                    ]
                );
            }

            foreach ($codigosBarra as $index => $codigoBarra) {
                $codigoDataREGISTRO = RegistroModel::find()->andWhere(['CodigoBarra' => $codigoBarra])->one();
                $codigoDataTRANSACCION = TransaccionModel::find()->andWhere(['CodigoBarra' => $codigoBarra])->one();

                $validaciones = $this->validarCodigoBarraMesa($codigoDataREGISTRO, $codigoDataTRANSACCION, $mesa->bodega, $codigoBarra);
                $poseeRestante = TrabajoMesaRestanteModel::find()
                    ->select('Bodega, NumeroMesa, SUM(Libras) as Libras')
                    ->where(['NumeroMesa' => $mesa->mesa])
                    ->groupBy(['Bodega', 'NumeroMesa'])
                    ->one();

                if ($poseeRestante) {
                    if (($poseeRestante->Bodega != $codigoDataREGISTRO->BodegaActual) && $codigoDataREGISTRO->Clasificacion != 'RIPIO') {
                        Yii::$app->session->setFlash('danger', "Mesa posee restante (de bodega $poseeRestante->Bodega), no se puede mezclar de diferentes bodegas!");
                        $mesa = $this->crearModelMesa();
                        return $this->render(
                            'create-mesa-barril',
                            [
                                //'bodegas' => $bodegas,
                                'mesa' => $mesa,
                                'detalle' => false,
                                'productores' => $productores,
                            ]
                        );
                    }
                }

                if ($validaciones) {
                    $codigosBarra[$index] = $codigoBarra . ', '
                        . $codigoDataREGISTRO->Articulo . ', '
                        . $codigoDataREGISTRO->Clasificacion . ', '
                        . $codigoDataREGISTRO->Descripcion . ', '
                        . $codigoDataREGISTRO->Libras . ', '
                        . $codigoDataREGISTRO->BodegaActual . ', '
                        . $codigoDataREGISTRO->Costo . ', '
                        . $codigoDataREGISTRO->tipoEmpaque->TipoEmpaque;
                    $totalLibras += $codigoDataREGISTRO->Libras;
                    $totalCosto += $codigoDataREGISTRO->Costo;
                } else {
                    Yii::$app->session->setFlash('danger', "Codigos de barra no disponible: " . $codigoBarra);
                    $mesa = $this->crearModelMesa();
                    return $this->render(
                        'create-mesa-barril',
                        [
                            //'bodegas' => $bodegas,
                            'mesa' => $mesa,
                            'detalle' => false,
                            'productores' => $productores,
                        ]
                    );
                }
            }

            $codigosBarraJson = json_encode($codigosBarra);
            return $this->render(
                'create-mesa-barril',
                [
                    //'bodegas' => $bodegas,
                    'mesa' => $mesa,
                    'detalle' => true,
                    'registros' => $codigosBarra,
                    'registrosJson' => $codigosBarraJson,
                    'totalLibras' => $totalLibras,
                    'totalCosto' => $totalCosto,
                    'productores' => $productores,
                ]
            );
        } else {
            return $this->render('create-mesa-barril', [
                'numeroDocumento' => $numeroDocumento,
                'mesa' => $mesa,
                //'bodegas' => $bodegas,
                'productores' => $productores,
                'detalle' => false,
            ]);
        }
    }


    public function actionVerificarLibras()
    {
        /*$finalizado = $this->validarFinalizarDia(date("Y-m-d"));
        if ($finalizado != 'FINALIZADO') {
            Yii::$app->session->setFlash('warning', "<i class='fas fa-exclamation-triangle'></i> &nbsp;&nbsp;<b>$finalizado!</b>");
            return $this->redirect(['site/index']);
        }*/
        $numeroDocumento = $this->request->get('NumeroDocumento');
        $mesa = $this->crearModelMesaBarril();
        $bodegas = $this->getBodegasByEsquema();
        $productores = $this->getProductores();
        if ($mesa->load($this->request->post())) 
        {
            $codigosBarra = $this->trimCodigosBarra($mesa->codigos_barra);
            $duplicado = $this->verificarCodigosBarraDuplicados($codigosBarra);
            $totalLibras = 0;
            $totalCosto = 0;
            $mesa->bodega = explode(" -", $mesa->bodega)[0];
            if ($duplicado != false) {
                Yii::$app->session->setFlash('danger', "Codigos de barra repetidos: " . $duplicado);
                $mesa = $this->crearModelMesa();
                return $this->render(
                    'create-mesa-barril',
                    [
                        'bodegas' => $bodegas,
                        'mesa' => $mesa,
                        'detalle' => false,
                        'productores' => $productores,
                    ]
                );
            }

            foreach ($codigosBarra as $index => $codigoBarra) {
                $codigoDataREGISTRO = RegistroModel::find()->andWhere(['CodigoBarra' => $codigoBarra])->one();
                $codigoDataTRANSACCION = TransaccionModel::find()->andWhere(['CodigoBarra' => $codigoBarra])->one();

                $validaciones = $this->validarCodigoBarraMesa($codigoDataREGISTRO, $codigoDataTRANSACCION, $mesa->bodega, $codigoBarra);
                // $poseeRestante = AsignacionClasificacion::find()
                //     ->select('Bodega, NumeroMesa, SUM(Libras) as Libras')
                //     ->where(['NumeroMesa' => $mesa->mesa])
                //     ->groupBy(['Bodega', 'NumeroMesa'])
                //     ->one();

                // if ($poseeRestante) {
                //     if (($poseeRestante->Bodega != $codigoDataREGISTRO->BodegaActual) && $codigoDataREGISTRO->Clasificacion != 'RIPIO') {
                //         Yii::$app->session->setFlash('danger', "Mesa posee restante (de bodega $poseeRestante->Bodega), no se puede mezclar de diferentes bodegas!");
                //         $mesa = $this->crearModelMesa();
                //         return $this->render(
                //             'create-mesa-barril',
                //             [
                //                 'bodegas' => $bodegas,
                //                 'mesa' => $mesa,
                //                 'detalle' => false,
                //                 'productores' => $productores,
                //             ]
                //         );
                //     }
                // }

                if ($validaciones) {
                    $codigosBarra[$index] = $codigoBarra . ', '
                        . $codigoDataREGISTRO->Articulo . ', '
                        . $codigoDataREGISTRO->Descripcion . ', '
                        . $codigoDataREGISTRO->Clasificacion . ', '
                        . $codigoDataREGISTRO->Libras . ', '
                        . $codigoDataREGISTRO->BodegaActual . ', '
                        . $codigoDataREGISTRO->Costo . ', '
                        . $codigoDataREGISTRO->tipoEmpaque->TipoEmpaque;
                    $totalLibras += $codigoDataREGISTRO->Libras;
                    $totalCosto += $codigoDataREGISTRO->Costo;
                } else 
                {
                    Yii::$app->session->setFlash('danger', "Codigos de barra no disponible: " . $codigoBarra. " Bodega: ". $mesa->bodega);
                    $mesa = $this->crearModelMesa();
                    return $this->render(
                        'create-mesa-barril',
                        [
                            'bodegas' => $bodegas,
                            'mesa' => $mesa,
                            'detalle' => false,
                            'productores' => $productores,
                        ]
                    );
                }
            }

            $codigosBarraJson = json_encode($codigosBarra);
            return $this->render(
                'create-mesa-barril',
                [
                    'bodegas' => $bodegas,
                    'mesa' => $mesa,
                    'detalle' => true,
                    'registros' => $codigosBarra,
                    'registrosJson' => $codigosBarraJson,
                    'totalLibras' => $totalLibras,
                    'totalCosto' => $totalCosto,
                    'productores' => $productores,
                ]
            );
        } else {
            return $this->render('create-mesa-barril', [
                'numeroDocumento' => $numeroDocumento,
                'mesa' => $mesa,
                'bodegas' => $bodegas,
                'productores' => $productores,
                'detalle' => false,
            ]);
        }
    }

    /**
     * Crea la salida de inventario, haciendo registros en las tablas [SOFTLAND].[CONINV].[DOCUMENTO_INV], [LINEA_DOC_INV], [CONSECUTIVO_CI] y
     * las tablas [BODEGA].[dbo].[REGISTRO], [TRANSACCION]
     */
    public function actionSalidaInventarioMesa($registros, $mesa, $productores, $fecha)
    {
        $registros = json_decode($registros);

        $productores =  json_decode($productores);
        $productoresString = '';

        foreach ($productores as $index => $productor) {
            if ($index == count($productores) - 1) {
                $productoresString .= $productor;
                continue;
            }
            $productoresString .= $productor . ", ";
        }

        $consecutivoActualCONINV = $this->obtenerConsecutivo('CONSUMO', 'CONINV');
        $this->crearDocumentoInv($consecutivoActualCONINV, 'CONINV');

        $consecutivoActualCNYCENTER = $this->obtenerConsecutivo('CONSUMO', 'CNYCENTER');
        $this->crearDocumentoInv($consecutivoActualCNYCENTER, 'CNYCENTER');

        foreach ($registros as $key => $value) {
            $datos = explode(", ", $value);

            $this->crearLineaDocumentoInvSalida($consecutivoActualCONINV, $datos[1], $datos[5], 'CONINV');
            $this->crearLineaDocumentoInvSalida($consecutivoActualCNYCENTER, $datos[1], $datos[5], 'CNYCENTER');

            $this->actualizarRegistroFardoREGISTRO($datos[0]);
            $this->crearRegistroFardoSalidaTRANSACCION($datos[0], $datos[5], $consecutivoActualCONINV);
            $this->crearRegistroTRABAJOMESA($datos[4], $datos[6], $mesa, $productoresString, $consecutivoActualCONINV, $fecha, $datos[5]);
        }

        $this->actualizarConsecutivo('CONINV');
        $this->actualizarConsecutivo('CNYCENTER');
        Yii::$app->session->setFlash('primary', "Registro finalizado con exito");
        return $this->redirect(['index-mesas']);
    }


    public function actionCreateAsignacion($registros, $mesa, $productores, $fecha)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $registros = json_decode($registros);

            $productores =  json_decode($productores);
            $productoresString = '';

            foreach ($productores as $index => $productor) {
                if ($index == count($productores) - 1) {
                    $productoresString .= $productor;
                    continue;
                }
                $productoresString .= $productor . ", ";
            }

            foreach ($registros as $key => $value) {
                $datos = explode(", ", $value);

                $this->actualizarRegistroFardoAsignacion($datos[0], $mesa);
                $this->creaTransaccionSalida($datos[0], $datos[5]);
                $this->crearRegistroMesaAsignacion($datos[4], $datos[6], $mesa, $productoresString, $fecha, $datos[5]);
            }

            Yii::$app->session->setFlash('primary', "Registro finalizado con exito");

            $transaction->commit();

            return $this->redirect(['index-mesas']);
        }
        catch (\yii\db\Exception $e) 
        {
            Yii::$app->session->setFlash('danger', "Error al asignar: $e");
            $transaction->rollBack();
            return $this->redirect(['index-mesas']);
        }
        catch (\Exception $e) 
        {
            Yii::$app->session->setFlash('danger', "Error al asignar: $e");
            $transaction->rollBack();
            return $this->redirect(['index-mesas']);
        }
        
    }

    /**
     * Limpia los espacios en blanco de los codigos de barra y los separa en lineas
     * @return array un array nuevo con codigos de barra limpios
     */
    public function trimCodigosBarra($codigosBarra)
    {
        $codigosBarra = trim($codigosBarra);
        $codigosBarra = explode("\n", $codigosBarra);

        foreach ($codigosBarra as $index => $codigo) {
            $codigosBarra[$index] = trim($codigosBarra[$index]);
        }

        return $codigosBarra;
    }


    /**
     * Verifica si los codigos de barra enviados estan duplicados
     * @return string|bool Si algun codigo se repite retorna ese codigo, sino retorna false
     */
    public function verificarCodigosBarraDuplicados($codigosBarra)
    {
        $countCodigosBarra = array_count_values($codigosBarra);
        foreach ($codigosBarra as $codigo) {
            if ($countCodigosBarra[$codigo] > 1) {
                return $codigo;
            }
        }

        return false;
    }

    /**
     * Verificar si el codigo de barra enviado es de RIPIO
     * @return bool si el codigo de barra es de ripio retorna true, sino retorna false
     */
    public function verificarCodigoRipio($clasificacion)
    {
        if ($clasificacion == 'RIPIO') {
            return true;
        }

        return false;
    }

    /**
     * Valida que el codigo de barra cumple con ciertas condiciones
     * @return bool si el codigo de barra cumple con dichas condiciones retorna true, sino retorna false
     */
    public function validarCodigoBarra($codigoDataREGISTRO, $codigoDataTRANSACCION, $bodegaActual)
    {
        if (
            //$codigoDataREGISTRO->Estado == 'FINALIZADO'
            $codigoDataREGISTRO->BodegaActual == $bodegaActual
            && ($codigoDataREGISTRO->Activo == 1
                || $codigoDataREGISTRO->Activo == 'NULL')
            //&& $codigoDataTRANSACCION->Estado == 'F'
            && $codigoDataTRANSACCION->Naturaleza == 'E'
        ) {
            return true;
        }

        return false;
    }

    public function validarCodigoBarraMesa($codigoDataREGISTRO, $codigoDataTRANSACCION, $bodega, $codigo)
    {
        $esProduccion = $this->verificarBoolCodigoProduccion($codigo);
        if ($codigoDataREGISTRO->BodegaActual == $bodega && $codigoDataREGISTRO->Activo == 1) {
            if($codigoDataREGISTRO->Estado == 'PENDIENTE' && $codigoDataTRANSACCION->Estado == 'P')
            {
                $esBarril = $this->verificarBoolCodigoBarril($codigo);
                if(!empty($esBarril) && $codigoDataREGISTRO->Costo >= 0){
                    return true;
                }
                return false;
            }else if(!empty($esProduccion) && $codigoDataREGISTRO->Costo > 0){
                return true;
            }else if(!empty($esProduccion) && $codigoDataREGISTRO->Costo == 0){
                return false;
            }else{
                return true;
            }
        }
    }
    
    public function verificarBoolCodigoProduccion($codigoBarra){
        $getDato =  Yii::$app->db->createCommand("SELECT CodigoBarra 
        FROM REGISTRO WHERE CodigoBarra = '" . $codigoBarra . "' AND Articulo like 'P%'")->queryScalar();

        return $getDato;
    }

    public function verificarBoolCodigoBarril($codigoBarra){
        $getDato =  Yii::$app->db->createCommand("SELECT CodigoBarra 
        FROM REGISTRO WHERE CodigoBarra = '" . $codigoBarra . "' AND Articulo like 'BA%'")->queryScalar();

        return $getDato;
    }

    /**
     * Obtiene el consecutivo a trabajar para registrar una mesa
     * @return string Un consecutivo el cual registrara todo movimiento de una mesa en inventario
     */
    public function obtenerConsecutivo($consecutivo, $esquema)
    {
        $getConsecutivo =  $this->SoftlandConn()->createCommand("SELECT SIGUIENTE_CONSEC 
        FROM [PRUEBAS].$esquema.[CONSECUTIVO_CI] WHERE CONSECUTIVO = '" . $consecutivo . "'")->queryOne();

        return $getConsecutivo['SIGUIENTE_CONSEC'];
    }

    /**
     * Crear un documento de inventario a partir de un consecutivo
     * @param int $consecutivo El consecutivo actual 
     * @throws PDOException Si algun campo/valor proporcionado estan fuera de los campos/valores esperados
     */
    public function crearDocumentoInv($consecutivo, $esquema)
    {
        $this->SoftlandConn()->createCommand(
            "INSERT INTO [PRUEBAS].$esquema.[DOCUMENTO_INV]
            (PAQUETE_INVENTARIO, DOCUMENTO_INV, CONSECUTIVO , REFERENCIA, FECHA_HOR_CREACION, FECHA_DOCUMENTO,
            SELECCIONADO, USUARIO, APROBADO) 
            VALUES 
            (
                '" . Yii::$app->session->get('paquete') . "',
                '" . $consecutivo . "',
                'CONSUMO',
                'CONSUMO del dia " .  date("Y-m-d") . "',
                '" . date("Y-m-d H:i:s") . "',
                '" . date("Y-m-d H:i:s") . "',
                'N',
                '" . Yii::$app->session->get('user') . "',
                'N'
            )"
        )->execute();
    }

    /**
     * Cuenta las lineas actuales de un documento de inventario, y le suma 1
     * @param int $consecutivo El consecutivo actual 
     * @return int El numero de la siguiente linea de inventario
     * @throws PDOException Si algun campo/valor proporcionado estan fuera de los campos/valores esperados
     */
    public function obtenerNumeroLineaDocInv($consecutivo, $esquema)
    {
        $ultimaLinea = $this->SoftlandConn()->createCommand("SELECT COUNT(LINEA_DOC_INV) AS linea
        FROM [PRUEBAS].$esquema.[LINEA_DOC_INV] 
        WHERE DOCUMENTO_INV = '" . $consecutivo . "'")->queryOne();

        return $ultimaLinea['linea'] + 1;
    }

    /**
     * Crea una linea de salida de inventario la cual representa un registro de articulo 
     * @param int $consecutivo El consecutivo actual 
     * @throws PDOException Si algun campo/valor proporcionado estan fuera de los campos/valores esperados
     */
    public function crearLineaDocumentoInvSalida($consecutivo, $articulo, $bodega, $esquema)
    {
        $this->SoftlandConn()->createCommand(
            "INSERT INTO [PRUEBAS].$esquema.[LINEA_DOC_INV] 
            (PAQUETE_INVENTARIO, DOCUMENTO_INV, LINEA_DOC_INV, 
            AJUSTE_CONFIG, ARTICULO, BODEGA, TIPO, SUBTIPO, SUBSUBTIPO, CANTIDAD,
            COSTO_TOTAL_LOCAL, COSTO_TOTAL_DOLAR, PRECIO_TOTAL_LOCAL, PRECIO_TOTAL_DOLAR, COSTO_TOTAL_LOCAL_COMP, COSTO_TOTAL_DOLAR_COMP)
            VALUES 
            (
                '" . Yii::$app->session->get('paquete') . "',
                '" . $consecutivo . "',
                " . $this->obtenerNumeroLineaDocInv($consecutivo, $esquema) . ",
                '~CC~', '" . $articulo . "', '" . $bodega . "', 'C', 'D', 'N', 1,
                0,0,0,0,0,0
            )"
        )->execute();
    }

    /**
     * Tomando un codigo de barra, actualiza su estado en la tabla REGISTRO
     * @param string $codigoBarra un codigo de barra de un registro existente
     */
    public function actualizarRegistroFardoREGISTRO($codigoBarra)
    {
        Yii::$app->db->createCommand(
            "UPDATE [REGISTRO] SET Activo = 0, Estado='PROCESO'
            WHERE CodigoBarra = '$codigoBarra'"
        )->execute();
    }

    public function actualizarRegistroFardoAsignacion($codigoBarra, $mesa)
    {
        Yii::$app->db->createCommand(
            "UPDATE [REGISTRO] SET Activo = 0, Estado='PROCESO', MesaOrigenAsignacion = '$mesa'
            WHERE CodigoBarra = '$codigoBarra'"
        )->execute();
    }

    public function actualizarRegistroMesaAsignacion($codigoBarra)
    {
        Yii::$app->db->createCommand(
            "UPDATE [REGISTRO] SET Activo = 0, Estado = 'PROCESO'
            WHERE CodigoBarra = '$codigoBarra'"
        )->execute();
    }

    /**
     * Tomando un codigo de barra, crea un registro en la tabla [BODEGA].[dbo].[TRANSACCION] para representar una salida del inventario
     * @param string $codigoBarra un codigo de barra de un registro existente
     * @param string $bodega la bodega en la que estaba registrado el fardo trabajado
     * @param string $consecutivo un codigo de consecutivo el cual enlaza el inventario con la transaccion
     */
    public function crearRegistroFardoSalidaTRANSACCION($codigoBarra, $bodega)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [TRANSACCION] 
            (CodigoBarra, IdTipoTransaccion, Fecha, Bodega, Naturaleza, Estado,
            UsuarioCreacion, FechaCreacion) 
            VALUES 
            ('" . $codigoBarra . "', 5, '" . date("Y-m-d") . "', '" . $bodega . "', 'S', 'F',
            '" . Yii::$app->session->get('user') . "', '" . date("Y-m-d H:i:s") . "')"
        )->execute();
    }

    public function creaTransaccionSalida($codigoBarra, $bodega)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [TRANSACCION] 
            (CodigoBarra, IdTipoTransaccion, Fecha, Bodega, Naturaleza, Estado,
            UsuarioCreacion, FechaCreacion) 
            VALUES 
            ('" . $codigoBarra . "', 10, '" . date("Y-m-d") . "', '" . $bodega . "', 'S', 'P',
            '" . Yii::$app->session->get('user') . "', '" . date("Y-m-d H:i:s") . "')"
        )->execute();
    }

    /**
     * Crea un registro en la tabla [BODEGA].[dbo].[TRABAJOMESA] para complementar la salida de inventario a traves
     * de mesas de trabajo
     */
    public function crearRegistroTRABAJOMESA($libras, $costo, $mesa, $producidoPor, $fecha, $bodega)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [TRABAJOMESA] 
            (Libras, Costo, NumeroMesa, ProducidoPor, Fecha, CreateDate, Bodega)
            VALUES
            (
                $libras,
                $costo,
                $mesa,
                '$producidoPor',
                '$fecha',
                '" . date("Y-m-d H:i:s") . "',
                '$bodega'
            )"
        )->execute();
    }

    public function crearRegistroMesaAsignacion($libras, $costo, $mesa, $producidoPor, $fecha, $bodega)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [ASIGNACION_CLASIFICACION] 
            (Libras, Costo, NumeroMesa, ProducidoPor, Fecha, CreateDate, Bodega)
            VALUES
            (
                $libras,
                $costo,
                $mesa,
                '$producidoPor',
                '$fecha',
                '" . date("Y-m-d H:i:s") . "',
                '$bodega'
            )"
        )->execute();
    }

    /**
     * Toma el consecutivo actual de CONSUMO de [SOFTLAND].[CONINV].[CONSECUTIVO_CI] para luego aumentarlo en 1
     * @return string el nuevo consecutivo 
     */
    public function crearSiguienteConsecutivo($esquema)
    {
        $getConsecutivoCode =  $this->SoftlandConn()->createCommand("SELECT CONSECUTIVO, SIGUIENTE_CONSEC
        FROM $esquema.[CONSECUTIVO_CI] WHERE CONSECUTIVO = 'CONSUMO'")->queryOne(); 
        //FROM [PRUEBAS].$esquema.[CONSECUTIVO_CI] WHERE CONSECUTIVO = 'CONSUMO'")->queryOne();

        $consecutivoCode = explode("-", $getConsecutivoCode['SIGUIENTE_CONSEC']);
        $ultimoConsecutivoCode  = intval($consecutivoCode[1]);
        $newConsecutivo = $consecutivoCode[0] . '-';

        if ($ultimoConsecutivoCode >= 0 && $ultimoConsecutivoCode < 9) {
            $newConsecutivo = $newConsecutivo . '000000' . ($ultimoConsecutivoCode + 1);
        } else if ($ultimoConsecutivoCode >= 9 && $ultimoConsecutivoCode < 99) {
            $newConsecutivo = $newConsecutivo . '00000' . ($ultimoConsecutivoCode + 1);
        } else if ($ultimoConsecutivoCode >= 99 && $ultimoConsecutivoCode < 999) {
            $newConsecutivo = $newConsecutivo . '0000' . ($ultimoConsecutivoCode + 1);
        } else if ($ultimoConsecutivoCode >= 999 && $ultimoConsecutivoCode < 9999) {
            $newConsecutivo = $newConsecutivo . '000' . ($ultimoConsecutivoCode + 1);
        } else if ($ultimoConsecutivoCode >= 9999 && $ultimoConsecutivoCode < 99999) {
            $newConsecutivo = $newConsecutivo . '00' . ($ultimoConsecutivoCode + 1);
        } else if ($ultimoConsecutivoCode >= 99999 && $ultimoConsecutivoCode < 999999) {
            $newConsecutivo = $newConsecutivo . '0' . ($ultimoConsecutivoCode + 1);
        } else if ($ultimoConsecutivoCode >= 999999 && $ultimoConsecutivoCode < 9999999) {
            $newConsecutivo = $newConsecutivo . ($ultimoConsecutivoCode + 1);
        }

        return $newConsecutivo;
    }

    /**
     * Actualiza el consecutivo CONSUMO de [SOFTLAND].[CONINV].[CONSECUTIVO_CI]
     */
    public function actualizarConsecutivo($esquema)
    {
        $this->SoftlandConn()->createCommand("UPDATE [PRUEBAS].$esquema.[CONSECUTIVO_CI] 
        SET SIGUIENTE_CONSEC = '" . $this->crearSiguienteConsecutivo($esquema) . "' WHERE CONSECUTIVO = 'CONSUMO'")->execute();
    }

    /**
     * Crea un modelo dinamico que captura los codigos de barra que saldran del inventario
     * @return DynamicModel un modelo dinamico
     */
    public function crearModelMesa()
    {
        $mesa = new DynamicModel([
            'codigos_barra', 'bodega', 'mesa', 'fecha', 'ProducidoPor'
        ]);
        $mesa->setAttributeLabels([
            'codigos_barra' => 'Codigos de barra',
            'bodega' => 'Bodega',
            'mesa' => 'Mesa que trabajara el codigo',
            'fecha' => 'Fecha de producción',
            'ProducidoPor' => 'Producido por'
        ]);
        $mesa->addRule(['codigos_barra', 'bodega', 'mesa', 'fecha', 'ProducidoPor'], 'required');
        return $mesa;
    }

    public function crearModelMesaBarril()
    {
        $mesa = new DynamicModel([
            'codigos_barra', 'bodega', 'mesa', 'fecha', 'ProducidoPor'
        ]);
        $mesa->setAttributeLabels([
            'codigos_barra' => 'Codigos de barra',
            'bodega' => 'Bodega',
            'mesa' => 'Mesa que trabajara el codigo',
            'fecha' => 'Fecha de producción',
            'ProducidoPor' => 'Producido por'
        ]);
        $mesa->addRule(['codigos_barra', 'bodega', 'mesa', 'fecha', 'ProducidoPor'], 'required');
        return $mesa;
    }

    /**
     * Obtiene todos los articulos de la tabla [SOFTLAND].[ESQUEMA].[ARTICULO] bajo un filtro especifico
     * @return array un array de datos que contiene los articulos
     */
    // protected function getArticulosByEsquema()
    // {
    //     $articulos = $this->SoftlandConn()->createCommand(
    //         "SELECT ARTICULO, DESCRIPCION, CLASIFICACION_2 AS ARTICULO_DESCRIPCION 
    //         FROM [PRUEBAS]." . $_SESSION['esquema'] . ".[ARTICULO] 
    //         WHERE ACTIVO = 'S' 
    //         AND ARTICULO LIKE '%P%' AND ARTICULO NOT LIKE 'RIPIO'"
    //     )->queryAll();
    //     foreach ($articulos as $index => $articulo) {
    //         $articulos[$index]["ARTICULO_DESCRIPCION"] = $articulo['ARTICULO'] . ' - ' . $articulo["DESCRIPCION"];
    //     }
    //     return $articulos;
    // }

    protected function getArticulosByEsquema($esquema)
    {
        $sql = "SELECT ARTICULO, DESCRIPCION, CLASIFICACION_2 AS ARTICULO_DESCRIPCION 
                FROM [$esquema].[ARTICULO] 
                WHERE ACTIVO = 'S' 
                AND ARTICULO LIKE :filtro_articulo 
                AND ARTICULO NOT LIKE 'RIPIO'";
        
        $articulos = $this->SoftlandConn()->createCommand($sql)
                        ->bindValue(':filtro_articulo', '%P%')
                        ->queryAll();

        foreach ($articulos as &$articulo) {
            $articulo["ARTICULO_DESCRIPCION"] = $articulo['ARTICULO'] . ' - ' . $articulo["DESCRIPCION"];
        }

        return $articulos;
    }

    /**
     * Obtiene todas las bodegas existentes segun el esquema del usuario logeado 
     * @return Array Contiene las bodegas contatenadas a su nombre
     * @throws PDOException Si existe algun error en el query
     */
    public function getBodegasByEsquema()
    {
        $bodegas = $this->SoftlandConn()->createCommand("SELECT BODEGA, NOMBRE FROM " . $_SESSION['esquema'] . ".BODEGA WHERE bodega LIKE '%00'  or BODEGA like 'SM%'")->queryAll();

        foreach ($bodegas as $index => $bodega) {
            $bodegas[$index]["NOMBRE"] = $bodegas[$index]["BODEGA"] . ' - ' . $bodegas[$index]["NOMBRE"];
        }
        return $bodegas;
    }

    /**
     * Obtiene todos los usuarios que puede producir en mesas de trabajo
     * @return array un array que contiene los usuarios que pueden producir
     */
    public function getProductores()
    {
        $productores = Yii::$app->db->createCommand("SELECT * FROM [BODEGA].[dbo].[USUARIO] WHERE Produce=1 AND Activo=1")->queryAll();
        return $productores;
    }

    /**
     * Genera un codigo de barra para identificar de manera unica cada registro que pase en [BODEGA].[dbo]
     * @param date $fecha Una fecha necesaria para generar el codigo de barra
     * @return string un nuevo codigo de barra unico
     */
    public function generateBarCode($fecha)
    {
        $fechaActual = date('m.d.y');
        $fechaComoEntero = strtotime($fechaActual);
        $anio = date("y", $fechaComoEntero);
        $mes = date("m", $fechaComoEntero);
        $dia = date("d", $fechaComoEntero);
        $DesdeLetra = "a";
        $HastaLetra = "z";
        $letraAleatoria = chr(rand(ord($DesdeLetra), ord($HastaLetra)));
        $letraMayuscula = strtoupper($letraAleatoria);

        $devuelve = $this->getSessionDataRegisterDay($fecha) + 1;
        if ($devuelve >= 0 && $devuelve < 10) {
            $numero = "00$devuelve";
        } else if ($devuelve > 9 && $devuelve < 100) {
            $numero = "0$devuelve";
        } else {
            $numero = $devuelve;
        }

        $codigoBarra = ("" . $letraMayuscula . "" . "P" . $dia . "" . $mes . "" . $numero . "" . $anio
        );

        return $codigoBarra;
    }

    /**
     * A partir de una fecha, toma el conteo de todos los registros creados en un dia
     * @param date $fecha una fecha para filtrar los registros 
     * @return int un numero de registros diarios
     */
    public function getSessionDataRegisterDay($fecha)
    {
        $sesionDataDay = Yii::$app->db->createCommand("SELECT ISNULL(MAX(Sesion), 0) AS maximo 
        FROM REGISTRO 
        WHERE FechaCreacion = '" . $fecha . "' AND IdTipoRegistro = 4")->queryOne();

        return $sesionDataDay['maximo'];
    }

    public function validarFinalizarDia()
    {
        $finalizado = Yii::$app->db->createCommand(
            "SELECT * FROM BODEGA.dbo.FINALIZACION_DIA WHERE Estado = 'SIN FINALIZAR' ORDER BY Fecha DESC"
        )->queryAll();

        if (count($finalizado) > 1) {
            return "DIA SIN FINALIZAR: " . $finalizado[1]["Fecha"];
        }

        return "FINALIZADO";
    }
    

    /**
     * Funcion para evaluar valores dentro de un array
     * @throws Array Retorna los valores con etiquetas <pre></pre>
     */
    public function printArrays($datos)
    {
        echo "<pre>";
        print_r($datos);
        echo "</pre>";
    }
}
