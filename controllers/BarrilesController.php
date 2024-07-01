<?php

namespace app\controllers;

use app\models\AsignacionClasificacion;
use app\models\RegistroModel;
use app\models\TransaccionModel;
use app\modelsSearch\RegistroModelSearch;
use app\modelsSearch\TransaccionModelSearch;
use Exception;
use yii\web\Controller;
use Yii;
use yii\base\DynamicModel;
use Ramsey\Uuid\Uuid;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\db\Query;
use Mpdf\Mpdf;

/**
 * BarrrilesController es un controlador que permite manipular la separacion de productos provenientes de fardos en barriles.
 */
class BarrilesController extends Controller
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
     * @return YiiDbConnection
     */
    function SoftlandConn()
    {
        return Yii::$app->db2;
    }


    /**
     * Crea un ActiveRecord que contiene toda la informacion acerca de barriles
     * @param string $condicionImprimir es un parametro opcional, este parametro solo es usado al momento de finalizar el registro de barriles
     * @return Array|Object|string Un array asociativo con el conjunto de campos necesarios para los barriles
     */
    // public function actionIndexBarriles($condicionImprimir)
    // {
    //     if (!isset($_SESSION['user'])) {
    //         return $this->redirect(Url::to(Yii::$app->request->baseUrl . '/index.php?r=site/login'));
    //     }

    //     $dataProvider =  RegistroModel::find()
    //         ->where('IdTipoRegistro = 4 and IdTipoEmpaque = 23 AND Activo = 1')->orderBy(['CreateDate' => SORT_DESC])->all();

    //     return $this->render('index-barriles', [
    //         'dataProvider' => $dataProvider,
    //         'imprimir' => $condicionImprimir
    //     ]);
    // }

    public function actionIndexBarriles($condicionImprimir)
    {
        if (!isset($_SESSION['user'])) {
            return $this->redirect(Url::to(Yii::$app->request->baseUrl . '/index.php?r=site/login'));
        }

        $searchModel = new RegistroModelSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, "IdTipoRegistro = 4 AND IdTipoEmpaque = 23 AND Activo = 1");
        $dataProvider->query->orderBy(['CreateDate' => SORT_ASC]);

        return $this->render('index-barriles', [
            'dataProvider' => $dataProvider,
            'imprimir' => $condicionImprimir,
            'searchModel' => $searchModel
        ]);
    }

    public function actionIndexListaCajas(){
        $searchModel = new TransaccionModelSearch();
        $dataProvider = $searchModel->searchCajas(Yii::$app->request->queryParams);

        return $this->render('index-lista-cajas', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionIndexVerificarCodigo(){
        //jalamos el barril
        $barril = $this->createModelBarriles();
        if ($barril->load($this->request->post())) {
            $codigosBarra = $this->trimCodigosBarra($barril->codigo_barra);
            $duplicados = $this->verificarCodigosBarraDuplicados($codigosBarra);

            if ($duplicados != false) {
                Yii::$app->session->setFlash('danger', "Codigos de barra repetidos: " . $duplicados);
                $barril = $this->createModelBarriles();
                return $this->render(
                    'index-verificar-codigo',
                    [
                        'barril' => $barril,
                    ]
                );
            }
            //Generamos el codigo para unir los códigos de barra a un solo documento
            $uuid = Uuid::uuid4();
            //Pasamos el uuid a un string
            $uuidString = $uuid->toString();
            $totalLibras = 0;
            $totalCosto = 0;
            $transaction = $this->SoftlandConn()->beginTransaction();
            try{ 
                foreach ($codigosBarra as $index => $codigo) {
                    $codigoDataREGISTRO = RegistroModel::find()->andWhere(['CodigoBarra' => $codigo])->one();
                    $codigoDataTRANSACCION = TransaccionModel::find()->andWhere(['CodigoBarra' => $codigo])->one();

                    $validaciones = $this->validarCodigoBarraRegistroTransaccion($codigoDataREGISTRO, $codigoDataTRANSACCION);
                    if ($validaciones) {
                        if(is_string($validaciones)){
                            if($validaciones == "ROPA"){
                                Yii::$app->session->setFlash('warning', "$codigo No es clasificacion ROPA");
                                return $this->render(
                                    'index-verificar-codigo',
                                    [
                                        'barril' => $barril
                                    ]
                                );
                            }
                            else if($validaciones == "BODEGA"){
                                Yii::$app->session->setFlash('warning', "$codigo No pertenece a BODEGA SM00");
                                return $this->render(
                                    'index-verificar-codigo',
                                    [
                                        'barril' => $barril
                                    ]
                                );
                            }
                            else if($validaciones == "PROCESO"){
                                Yii::$app->session->setFlash('warning', "$codigo pertenece a una transaccion en proceso");
                                return $this->render(
                                    'index-verificar-codigo',
                                    [
                                        'barril' => $barril
                                    ]
                                );
                            }
                        }
                        //$parts = explode(', ', $codigo);
                        // $parts[0] es el código de barras, $parts[1] es el artículo, $parts[2] es la clasificación, etc.
                        //$codigoDeBarras = $parts[0];
                        $codigosBarra[$index] = $codigo . ', '
                        . $codigoDataREGISTRO->Articulo . ', '
                        . $codigoDataREGISTRO->Clasificacion . ', '
                        . $codigoDataREGISTRO->Libras . ', '
                        . $codigoDataREGISTRO->BodegaActual . ', '
                        . $codigoDataREGISTRO->Costo . ', '
                        . $codigoDataREGISTRO->FechaCreacion;
                        $totalLibras += $codigoDataREGISTRO->Libras;
                        $totalCosto += $codigoDataREGISTRO->Costo;
                    }
                    else{
                        Yii::$app->session->setFlash('warning', "Algunas validaciones no se han cumplido");
                        return $this->render(
                            'index-verificar-codigo',
                            [
                                'barril' => $barril
                            ]
                        );
                    }
                    //INSERT EN TRANSACCION Y UPDATE EN REGISTROS
                    $this->createTransaccionCodigo($codigo, 'SM00', $uuidString);
                    $this->actualizarRegistroCodigo($codigo);
                    //AM211105523,AP010807123
                }
                $totalLibrasBarriles = $this->obtenerLibrasBarrilles($uuid);
                //Si las libras son nulas(No hay libras asignadas aún) entonces se asigna 0 como valor.
                if($totalLibrasBarriles == null){
                    $totalLibrasBarriles = 0;
                }
                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Los códigos se han procesado');
                return $this->redirect(
                    'index-verificar-transaccion'
                );
            }catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('warning', "Error: " . $e->getMessage());
            }
        }
        return $this->render(
            'index-verificar-codigo',
            [
                'barril' => $barril
            ]
        );
    }

    public function actionIndexVerificarTransaccion(){
        $searchModel = new TransaccionModelSearch();
        $dataProvider = $searchModel->searchTransaccion(Yii::$app->request->queryParams);

        return $this->render('index-verificar-transaccion', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    

    public function actionIndexExistenciaMesas()
    {
        if (!isset($_SESSION['user'])) {
            return $this->redirect(Url::to(Yii::$app->request->baseUrl . '/index.php?r=site/login'));
        }

        $dataProvider = AsignacionClasificacion::find()->select('NumeroMesa, SUM(Libras) as Libras, SUM(Costo) as Costo')
            ->groupBy('NumeroMesa')->all();

        return $this->render('index-existencia-mesas', [
            'dataProvider' => $dataProvider,
        ]);
    }
    /**
     * Verifica un conjunto de codigos de barra para luego ser distribuidos en barriles
     * Si los codigos de barra existen, muestra los valores asociados a los codigos de barra
     * @return Array Que contiene los valores asociados a los codigos de barra
     */
    public function actionProduccionMesa()
    {
        $bodegas = $this->getBodegasByEsquema();
        $barril = $this->createModelBarrilesProducccion();
        if ($barril->load($this->request->post())) {
            $codigosBarra = $this->trimCodigosBarra($barril->codigo_barra);
            $duplicados = $this->verificarCodigosBarraDuplicados($codigosBarra);

            if ($duplicados != false) {
                Yii::$app->session->setFlash('danger', "Codigos de barra repetidos: " . $duplicados);
                $barril = $this->createModelBarriles();
                return $this->render(
                    'produccion-mesa',
                    [
                        'bodegas' => $bodegas,
                        'barril' => $barril,
                        'detalle' => false,
                    ]
                );
            }
            $totalLibras = 0;
            $totalCosto = 0;
            $barril->bodega = explode(" -", $barril->bodega)[0];
            foreach ($codigosBarra as $index => $codigo) {
                $codigoDataREGISTRO = RegistroModel::find()->andWhere(['CodigoBarra' => $codigo])->one();
                $codigoDataTRANSACCION = TransaccionModel::find()->andWhere(['CodigoBarra' => $codigo])->one();

                $validaciones = $this->validarCodigoBarra($codigoDataREGISTRO, $codigoDataTRANSACCION, $barril->bodega, $codigo);
                if ($validaciones) {
                    $codigosBarra[$index] = $codigo . ', '
                        . $codigoDataREGISTRO->Articulo . ', '
                        . $codigoDataREGISTRO->Clasificacion . ', '
                        . $codigoDataREGISTRO->Descripcion . ', '
                        . $codigoDataREGISTRO->Libras . ', '
                        . $codigoDataREGISTRO->BodegaActual . ', '
                        . $codigoDataREGISTRO->Costo . ', '
                        . $codigoDataREGISTRO->FechaCreacion;
                    $totalLibras += $codigoDataREGISTRO->Libras;
                    $totalCosto += $codigoDataREGISTRO->Costo;
                } else {
                    Yii::$app->session->setFlash('danger', "Codigos de barra \"$codigo\" No ha pasado las validaciones");
                    $barril = $this->createModelBarriles();
                    return $this->render(
                        'produccion-mesa',
                        [
                            'bodegas' => $bodegas,
                            'barril' => $barril,
                            'detalle' => false,
                        ]
                    );
                }   
            }
            $datosJson = json_encode($codigosBarra);

            return $this->render(
                'produccion-mesa',
                [
                    'bodegas' => $bodegas,
                    'barril' => $barril,
                    'detalle' => true,
                    'registros' => $codigosBarra,
                    'registrosJson' => $datosJson,
                    'totalLibras' => $totalLibras,
                    'totalCosto' => $totalCosto,
                    'numeroMesa' => $barril->mesa_asignacion
                ]
            );
        } else {
            return $this->render(
                'produccion-mesa',
                [
                    'bodegas' => $bodegas,
                    'barril' => $barril,
                    'detalle' => false
                ]
            );
        }
    }


    /**
     * Verifica un conjunto de codigos de barra para luego ser distribuidos en barriles
     * Si los codigos de barra existen, muestra los valores asociados a los codigos de barra
     * @return Array Que contiene los valores asociados a los codigos de barra
     */
    public function actionCreateBarril()
    {
        $bodegas = $this->getBodegasByEsquema();
        $barril = $this->createModelBarriles();
        if ($barril->load($this->request->post())) {
            $codigosBarra = $this->trimCodigosBarra($barril->codigo_barra);
            $duplicados = $this->verificarCodigosBarraDuplicados($codigosBarra);

            if ($duplicados != false) {
                Yii::$app->session->setFlash('danger', "Codigos de barra repetidos: " . $duplicados);
                $barril = $this->createModelBarriles();
                return $this->render(
                    'form-create-barril',
                    [
                        'bodegas' => $bodegas,
                        'barril' => $barril,
                        'detalle' => false,
                    ]
                );
            }
            $totalLibras = 0;
            $totalCosto = 0;
            $barril->bodega = explode(" -", $barril->bodega)[0];
            foreach ($codigosBarra as $index => $codigo) {
                $codigoDataREGISTRO = RegistroModel::find()->andWhere(['CodigoBarra' => $codigo])->one();
                $codigoDataTRANSACCION = TransaccionModel::find()->andWhere(['CodigoBarra' => $codigo])->one();

                $validaciones = $this->validarCodigoBarra($codigoDataREGISTRO, $codigoDataTRANSACCION, $barril->bodega, $codigo);
                if ($validaciones) {
                    $codigosBarra[$index] = $codigo . ', '
                        . $codigoDataREGISTRO->Articulo . ', '
                        . $codigoDataREGISTRO->Clasificacion . ', '
                        . $codigoDataREGISTRO->Descripcion . ', '
                        . $codigoDataREGISTRO->Libras . ', '
                        . $codigoDataREGISTRO->BodegaActual . ', '
                        . $codigoDataREGISTRO->Costo . ', '
                        . $codigoDataREGISTRO->FechaCreacion;
                    $totalLibras += $codigoDataREGISTRO->Libras;
                    $totalCosto += $codigoDataREGISTRO->Costo;
                } else {
                    Yii::$app->session->setFlash('danger', "Codigos de barra \"$codigo\" No ha pasado las validaciones");
                    $barril = $this->createModelBarriles();
                    return $this->render(
                        'form-create-barril',
                        [
                            'bodegas' => $bodegas,
                            'barril' => $barril,
                            'detalle' => false,
                        ]
                    );
                }   
            }
            $datosJson = json_encode($codigosBarra);

            return $this->render(
                'form-create-barril',
                [
                    'bodegas' => $bodegas,
                    'barril' => $barril,
                    'detalle' => true,
                    'registros' => $codigosBarra,
                    'registrosJson' => $datosJson,
                    'totalLibras' => $totalLibras,
                    'totalCosto' => $totalCosto,
                    'numeroMesa' => $barril->mesa_asignacion
                ]
            );
        } else {
            return $this->render(
                'form-create-barril',
                [
                    'bodegas' => $bodegas,
                    'barril' => $barril,
                    'detalle' => false
                ]
            );
        }
    }

    /**
     * 
     */
    public function actionCreateDetalle($registros, $totalLibrasFardos, $totalCostoFardos, $numeroMesa)
    {
        $transaction = Yii::$app->db2->beginTransaction();
        try{
            $registros = json_decode($registros);
            $mesa = explode(" -", $numeroMesa)[0];

            //$consecutivoActualCONINV = $this->obtenerConsecutivo('REC-LBS', 'CONINV');
            $consecutivoActualCNYCENTER = $this->obtenerConsecutivo('REC-LBS', 'CNYCENTER');

            //$this->crearDocumentoInv($consecutivoActualCONINV, 'CONINV');
            $this->crearDocumentoInv($consecutivoActualCNYCENTER, 'CNYCENTER');

            /**
             * $registros => array asociativo que contiene informacion sobre fardos a trabajar 
             * $registros[i][0] => VP200918523 => codigo de barra de fardo
             * $registros[i][1] => FARD0-0795 => articulo del fardo
             * $registros[i][2] => ROPA => clasificacion_2 del fardo
             * $registros[i][3] => PACA MIXTA PREMIUM => descripcion del fardo
             * $registros[i][4] => 1108.84 => libras del fardo
             * $registros[i][5] => SM00 => bodega actual del fardo
             * $registros[i][6] =>  1790.385421 => costo del fardo
             * $registros[i][7] => 2023-09-20 => Fecha de registro del fardo
             */
            foreach ($registros as $key => $value) {
                $datos = explode(", ", $value);

                //$this->crearLineaDocumentoInvSalida($consecutivoActualCONINV, $datos[1], $datos[5], 'CONINV');
                $this->crearLineaDocumentoInvSalida($consecutivoActualCNYCENTER, $datos[1], $datos[5], 'CNYCENTER');

                $this->actualizarRegistroFardoREGISTRO($datos[0]);
                $this->crearRegistroFardoSalidaTRANSACCION($datos[0], $datos[5], $consecutivoActualCNYCENTER);
            }
            Yii::$app->db->createCommand(
                "INSERT INTO [ASIGNACION_CLASIFICACION] (Libras, Costo, NumeroMesa, Fecha, CreateDate, Documento_inv) 
                VALUES ($totalLibrasFardos, $totalCostoFardos,  '$mesa', '" . date("Y-m-d") . "', getdate(), '$consecutivoActualCNYCENTER')"
            )->execute();

            //$this->actualizarConsecutivo('CONINV');
            $this->actualizarConsecutivo('CNYCENTER');
            $transaction->commit();
            Yii::$app->session->setFlash('success', "Se han realizado los cambios con exito");
            return $this->redirect(['index-barriles', 'condicionImprimir' => '']);
        }catch (\yii\db\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('danger', "Error $e");
            return $this->redirect(['index-barriles', 'condicionImprimir' => '']);
        }catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('danger', "Error $e");
            return $this->redirect(['index-barriles', 'condicionImprimir' => '']);
        }
        
    }

    public function obtenerLibrasCosto($uuid){
        $db = Yii::$app->db;
        $sql = "SELECT SUM(r.Costo) AS totalCosto, SUM(r.libras) AS totalLibras
                FROM REGISTRO r
                WHERE r.CodigoBarra IN (
                    SELECT t.CodigoBarra
                    FROM TRANSACCION t
                    WHERE t.IdTipoTransaccion = 10 
                    AND t.NumeroDocumento = '$uuid'
                )";
        $command = $db->createCommand($sql);

        // Ejecutar el comando y obtener los resultados
        $result = $command->queryOne();
        return $result;
    }

    //Se obtiene el numero de barriles sin contal el RIPIO
    public function obtenerNumeroBarriles($uuid){
        $query = (new \yii\db\Query())
        ->select(['COUNT(IdRegistro) as numeroBarriles'])
        ->from('REGISTRO')
        ->where(['and', ['<>', 'Articulo', 'BA030'], ['DOCUMENTO_INV' => $uuid]])
        ->scalar();

        return $query;
    }

    public function obtenerLibrasPorRipio($uuid)
    {
        $query = (new \yii\db\Query())
        ->select(['SUM(Libras) as librasPorRipio'])
        ->from('REGISTRO')
        ->where(['and', ['Articulo' => 'BA030'], ['DOCUMENTO_INV' => $uuid]])
        ->scalar();

    return $query;
    }

    public function obtenerLibrasSinRipio($documentoInv, $articuloExcluido){
        $libras = RegistroModel::find()
            ->alias('r')
            ->innerJoin('TRANSACCION t', 't.CodigoBarra = r.CodigoBarra AND t.NumeroDocumento = r.DOCUMENTO_INV')
            ->where([
                't.IdTipoTransaccion' => 9,
                't.NumeroDocumento' => $documentoInv
            ])
            ->andWhere(['not in', 'r.Articulo', [$articuloExcluido]])
            ->sum('r.Libras'); 

        // Retornamos el resultado
        return $libras;
    }

    //ACCION QUE RECIBE LA PETICIÓN AJAX  PARA FINALIZAR LA TRANSACCION DE UN BARRIL
    public function actionFinalizarTransaccionBarriles()
    {
        if (Yii::$app->request->isAjax) {
            $totalLibras = Yii::$app->request->post('totalLibras');
            $totalCosto = Yii::$app->request->post('totalCosto');
            $uuid = Yii::$app->request->post('uuid');

            if (empty($totalLibras) || empty($uuid) || empty($totalCosto)) {
                return $this->asJson(['error' => true, 'message' => 'Una de las variables está vacía.']);
            }

            $porcentaje = 10;
            $librasTotalBarriles = $this->obtenerLibrasBarrilles($uuid);
            $precioLibra = $totalCosto / $totalLibras;
            $numeroBarriles = $this->obtenerNumeroBarriles($uuid);
            $costoOriginal = $precioLibra * $numeroBarriles;
            $librasRipio = $this->obtenerLibrasPorRipio($uuid);
            $librasBarriles = $this->obtenerLibrasSinRipio($uuid, 'BA030');

            if ($librasTotalBarriles == 0) {
                return $this->asJson(['error' => true, 'message' => 'No hay libras aún']);
            }

            $nuevoPrecioLibra = $this->distribuirCostoBarriles($totalCosto, $librasTotalBarriles, $librasRipio);
            $costoDistribuido = $nuevoPrecioLibra * $librasBarriles;

            $cantidadMaxima = $totalLibras * (1 + ($porcentaje / 100));
            $cantidadMinima = $totalLibras * (1 - ($porcentaje / 100));

            if ($librasTotalBarriles >= $cantidadMinima && $librasTotalBarriles <= $cantidadMaxima) {
                if ($totalCosto == $costoDistribuido) {
                    try {
                        $transaction = Yii::$app->db2->beginTransaction();

                        $consecutivoActualCNYCENTER = $this->obtenerConsecutivo('REC-LBS', 'CNYCENTER');
                        $existeCNYCENTER = $this->SoftlandConn()->createCommand(
                            "SELECT 1 FROM [CNYCENTER].[LINEA_DOC_INV] WHERE DOCUMENTO_INV = :documento"
                        )->bindValue(':documento', $consecutivoActualCNYCENTER)->queryScalar();

                        if ($existeCNYCENTER) {
                            return $this->asJson(['error' => true, 'message' => 'El consecutivo se encuentra repetido']);
                        }

                        $this->crearDocumentoInv($consecutivoActualCNYCENTER, 'CNYCENTER');

                        $listaCodigosEntrada = $this->obtenerListaCodigosEntrada($uuid);
                        if (empty($listaCodigosEntrada)) {
                            return $this->asJson(['error' => true, 'message' => 'No se encontraron registros para el UUID proporcionado.']);
                        }

                        foreach ($listaCodigosEntrada as $registro) {
                            $codigoBarra = $registro['CodigoBarra'];
                            $articulo = $registro['Articulo'];
                            $libras = intval($registro['Libras']);
                            $costo = $nuevoPrecioLibra * $libras;
                            if ($articulo != 'BA030') {
                                $this->crearLineaDocumentoInvEntrada($consecutivoActualCNYCENTER, $articulo, 'SM00', $costo, 'CNYCENTER');
                                $this->actualizarRegistroCostoBarriles($codigoBarra, $uuid, $costo);
                                $this->actualizarTransaccion($codigoBarra, $consecutivoActualCNYCENTER, $uuid);
                            }
                        }

                        $listaCodigosSalida = $this->obtenerListaCodigosSalida($uuid);
                        if (empty($listaCodigosSalida)) {
                            return $this->asJson(['error' => true, 'message' => 'No se encontraron registros de salida']);
                        }

                        foreach ($listaCodigosSalida as $registro) {
                            $codigoBarra = $registro['CodigoBarra'];
                            $libras = $registro['libras'];
                            $articulo = $registro['Articulo'];
                            $bodega = $registro['BodegaActual'];
                            $this->crearLineaDocumentoInvSalida($consecutivoActualCNYCENTER, $articulo, $bodega, 'CNYCENTER');
                            $this->finalizarRegistroFardo($codigoBarra);
                            $this->actualizarTransaccionSalida($codigoBarra, $consecutivoActualCNYCENTER, $uuid);
                        }

                        $this->actualizarConsecutivo('CNYCENTER');

                        $transaction->commit();
                        return $this->asJson(['success' => true, 'message' => 'Se han registrado los datos']);
                    } catch (\yii\db\Exception $e) {
                        $transaction->rollBack();
                        Yii::error("Error en actionFinalizarTransaccionBarriles: " . $e->getMessage(), __METHOD__);
                        return $this->asJson(['error' => true, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        Yii::error("Error en actionFinalizarTransaccionBarriles: " . $e->getMessage(), __METHOD__);
                        return $this->asJson(['error' => true, 'message' => 'Error al procesar la transacción: ' . $e->getMessage()]);
                    }
                } else {
                    return $this->asJson(['error' => true, 'message' => "El costo no coincide: $totalCosto, $costoDistribuido"]);
                }
            } else {
                return $this->asJson(['error' => true, 'message' => 'Las libras no están dentro del rango de tolerancia']);
            }
        }

        return $this->asJson(['error' => true, 'message' => 'Solicitud no válida']);
    }

    public function actionDetalleFardoAsignacion($NumeroDocumento){
        $searchModel = new RegistroModelSearch();
        $dataProvider = $searchModel->searchFardoPaca(Yii::$app->request->queryParams, $NumeroDocumento);
        return $this->render('detalle-fardo-asignacion',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'numeroDocumento' => $NumeroDocumento
        ]);
    }
    /**
     * Actualiza los datos del registro de una caja, actualiza el barril en registro
     *  y elimina la transaccion de ese barril
     * @return json|message retorna un mensaje Json para enviar en la alerta
     */
    public function actionEliminarBarrilCaja($codigoBarra, $uuid, $libras, $costo, $codigoBarraCaja){
        //Eliminamos la transaccion del barril que pertenece a esa caja
        $this->eliminarBarrilTransaccion($codigoBarra, $uuid);
        //Actualiza el barril
        $this->actualizarBarrilRegistroCaja($codigoBarra);

        //Actualizo las libras de la caja.
        $this->actualizarLibrasCaja($codigoBarraCaja, $libras);
        //Acualiza el costo de la caja
        $this->actualizarCostoCaja($codigoBarraCaja, $costo);
        
        //Mensaje personalizado
        $message = "El barril con código de barra $codigoBarra ha sido eliminado exitosamente.";

        // Devuelve el mensaje como JSON
        return $this->asJson(['success' => true, 'message' => $message]);
        
    }
    //Falta pensar mejor esta parte !!IMPORTANTE!!
    //ACCION PARA ELIMINAR LOS BARRILES ASIGNADOS A UNA PACA O A UNA SERIE DE FARDOS
    public function actionEliminarBarrilFardo($codigoBarra, $libras)
    {
        try
        {
            $uuid = Yii::$app->request->post('uuid');

            $totalLibrasBarriles = Yii::$app->request->post('totalLibrasBarriles');

            $totalLibrasBarriles -=$libras;
            $this->eliminarBarrilTransaccion($codigoBarra, $uuid);
            //Borra el barril de la tabla registro
            $this->deleteBarrilFardoPaca($codigoBarra, $uuid);
            // Mensaje de exito
            $message = "El barril con código de barra $codigoBarra ha sido eliminado exitosamente.";
            // Devuelve el mensaje como JSON
            return $this->asJson([
                'success' => true, 
                'message' => $message,
                'totalLibrasBarriles' => $totalLibrasBarriles
            ]);
        }catch(Exception $e){
            return $this->asJson(['success' => false, 'message' => $e]);
        }
    }
    //Action para eliminar el ultimo barril junto con la caja
    //Se agrega otro metodo igual porque este metodo recibe una peticion AJAX
    public function actionEliminarCaja()
    {
        $codigoBarraCaja = Yii::$app->request->post('codigoBarra');
        $uuid = Yii::$app->request->post('uuid');
        
        try{
            //Manejo de rollback en caso de excepciones
            $transaction = Yii::$app->db->beginTransaction();

            //Parametros necesarios para eliminar la caja y el barril que se ingreso
            $barriles=$this->obtenerListaCodigosBarriles($uuid);

            foreach ($barriles as $registro) {
                $codigoBarra = $registro['CodigoBarra'];
                $this->actualizarBarrilRegistroCaja($codigoBarra);

                //Eliminar la transaccion del barril
                $this->eliminarBarrilTransaccion($codigoBarra, $uuid);
            }
            //Eliminamos las transacciones hasta este punto, deberia ser solamente una
            $obtenerFilas =$this->eliminarCajaTransaccion($uuid);

            //Eliminamos el registro de la caja
            $this->eliminarCajaRegistro($codigoBarraCaja);
            
            // Mensaje personalizado.
            $message = "Se ha eliminado la caja,  Filas: $obtenerFilas";
            $transaction->commit();
        }catch(Exception $e){
            $transaction->rollBack();
            return $this->asJson(['success' => false, 'message' => $e]);
        }   
        

        // Devuelve el mensaje como JSON
        return $this->asJson(['success' => true, 'message' => $message]);
        
    }
    //Funcion de borrar caja que recibe como parametro el codigo de barra de la caja.
    public function actionDeleteCaja($CodigoBarra){
        $obtenerGUID = $this->obtenerGUIDCaja($CodigoBarra);
        try{
            if(empty($obtenerGUID)){
                Yii::$app->session->setFlash('danger', "El codigo de barra no contiene un numero de documento");
                list($searchModel, $dataProvider) = $this->prepareData();
                return $this->render('index-lista-cajas', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]);
            }

            //Obtenemos los barriles
            $barriles=$this->obtenerListaCodigosBarriles($obtenerGUID);
            //$codigoCaja = $this->obtenerCodigoCaja($obtenerGUID);

            foreach ($barriles as $registro) {
                $codigoBarra = $registro['CodigoBarra'];
                //actualizamos los barriles
                $this->actualizarBarrilRegistroCaja($codigoBarra);

            }
            //Eliminamos las transacciones
            $obtenerFilas =$this->eliminarCajaTransaccion($obtenerGUID);
            ///Eliminamos el registro de la caja
            $this->eliminarCajaRegistro($CodigoBarra);

            //Retornamos un mensaje de exito
            Yii::$app->session->setFlash('success', "Se han eliminado las transacciones, filas:$obtenerFilas");
            list($searchModel, $dataProvider) = $this->prepareData();
            return $this->render('index-lista-cajas', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }catch(Exception $e){
            Yii::$app->session->setFlash('danger', "Ha ocurrido un error, $e");
            list($searchModel, $dataProvider) = $this->prepareData();
                return $this->render('index-lista-cajas', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
            ]);
        }
        
    }

    private function prepareData()
    {
        $searchModel = new TransaccionModelSearch();
        $dataProvider = $searchModel->searchCajas(Yii::$app->request->queryParams);

        return [$searchModel, $dataProvider];
    }

    //ACCION PARA EDITAR UNA CAJA
    public function actionEditCaja(){
        $searchModel = new TransaccionModelSearch();
        $db = $this->softlandConn();
    
        $caja = $this->createModelCajas();
        $uuid = $this->request->get('NumeroDocumento');
        $codigoBarra = $this->request->get('CodigoBarra');
        $Articulo = $this->request->get('Articulo');
        $descripcion = $this->request->get('Descripcion');
        $rec = $this->request->get('Rec');
    
        if (!$uuid) {
            $uuid = '0';
        }
    
        $dataProvider = $searchModel->searchBarrilesCaja(Yii::$app->request->queryParams, $uuid, 1010, 'S');
    
        if ($caja->load($this->request->post())) {
            $uuid = $this->request->post('uuid');
            $articulo = $this->request->post('Articulo');
            $codigoBarraCaja = $this->request->post('codigoBarra');
            $totalCosto = $this->request->post('totalCosto');
            $totalLibras = $this->request->post('totalLibras');
    
            $codigosBarra = $this->trimCodigosBarra($caja->codigo_barra);
            $duplicados = $this->verificarCodigosBarraDuplicados($codigosBarra);
    
            if ($duplicados != false) {
                Yii::$app->session->setFlash('danger', "Códigos de barra duplicados: $duplicados");
                $caja = $this->createModelCajas();
                return $this->render('index-cajas', ['caja' => $caja]);
            }
    
            $transaction = $db->beginTransaction();
            try {
                foreach ($codigosBarra as $index => $codigo) {
                    $codigoDataREGISTRO = RegistroModel::find()->andWhere(['CodigoBarra' => $codigo])->one();
                    $codigoDataTRANSACCION = TransaccionModel::find()->andWhere(['CodigoBarra' => $codigo])->one();
    
                    $validarCodigosBarrilesProduccion = $this->validarCodigoBarraBarrilProduccion($codigo);
    
                    if (!empty($validarCodigosBarrilesProduccion)) {
                        if ($validarCodigosBarrilesProduccion != $articulo) {
                            Yii::$app->session->setFlash('danger', "El código, $codigo no pertenece al mismo artículo");
                            $caja = $this->createModelCajas();
                            return $this->render('index-cajas', ['caja' => $caja]);
                        }
    
                        $validarActivo = $this->validarActivoCodigoBarrraRegistro($codigoDataREGISTRO, $codigoDataTRANSACCION);
    
                        if (!$validarActivo) {
                            Yii::$app->session->setFlash('danger', "El código de barras $codigo no está activo, está en otro proceso o no pertenece a bodega SM00");
                            $caja = $this->createModelCajas();
                            return $this->render('index-cajas', ['caja' => $caja]);
                        }
    
                        $codigosBarra[$index] = $codigo . ', ' . $codigoDataREGISTRO->Articulo . ', ' . $codigoDataREGISTRO->Clasificacion . ', ' . $codigoDataREGISTRO->Descripcion . ', ' . $codigoDataREGISTRO->Libras . ', ' . $codigoDataREGISTRO->BodegaActual . ', ' . $codigoDataREGISTRO->Costo . ', ' . $codigoDataREGISTRO->FechaCreacion;
                        $totalLibras += $codigoDataREGISTRO->Libras;
                        $totalCosto += $codigoDataREGISTRO->Costo;
                        $this->actualizarRegistroCodigo($codigo);
                        $this->crearTransaccionSalida($codigo, $codigoDataREGISTRO->BodegaActual, null, $uuid);
    
                    } else {
                        Yii::$app->session->setFlash('danger', "El código no pertenece a PRODUCCIÓN/BARRILES: " . $codigo);
                        $caja = $this->createModelCajas();
                        return $this->render('index-cajas', ['caja' => $caja]);
                    }
                }
    
                $this->actualizarRegistroRegistroCaja($codigoBarraCaja, $totalLibras, $totalCosto);
                //  $this->crearTransaccionCaja($codigoBarraCaja, $codigoDataREGISTRO->BodegaActual, null, $uuid);
    
                $transaction->commit();
    
                Yii::$app->session->setFlash('success', "Datos agregados con éxito");
                $caja = $this->createModelCajas();
                $searchModel = new TransaccionModelSearch();
                $dataProvider = $searchModel->searchCajas(Yii::$app->request->queryParams);
                return $this->redirect(['index-lista-cajas']);
    
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('danger', "Error: " . $e->getMessage());
                return $this->render('index-cajas', ['caja' => $caja]);
            }
        }
    
        return $this->render('edit-caja', [
            'caja' => $caja,
            'uuid' => $uuid,
            'Articulo' => $Articulo,
            'rec' => $rec,
            'Descripcion' => $descripcion,
            'codigoBarra' => $codigoBarra,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    

    public function obtenerListaCodigosSalida($uuid){
        $registros = RegistroModel::find()
            ->select([
                'r.CodigoBarra',
                'r.libras',
                'r.Articulo',
                'r.BodegaActual',
            ])
            ->alias('r')
            ->innerJoin('TRANSACCION t', 't.CodigoBarra = r.CodigoBarra')
            ->where(['t.NumeroDocumento' => $uuid])
            ->andWhere(['t.Naturaleza' => 'S'])
            ->asArray()
            ->all();

        return $registros;
    }

    //Obtiene los codigos unicamente de barriles
    public function obtenerListaCodigosBarriles($uuid){
        $registros = RegistroModel::find()
            ->select([
                'r.CodigoBarra'
            ])
            ->alias('r')
            ->innerJoin('TRANSACCION t', 't.CodigoBarra = r.CodigoBarra')
            ->where(['t.NumeroDocumento' => $uuid])
            ->andWhere(['t.Naturaleza' => 'S'])
            ->asArray()
            ->all();

        return $registros;
    }

    //Obtenemos el codigo de la caja mediante innerjoin las cajas son aquellas
    //Que tienen naturaleza E en este punto.
    public function obtenerCodigoCaja($uuid){
        $registros = RegistroModel::find()
            ->select([
                'r.CodigoBarra'
            ])
            ->alias('r')
            ->innerJoin('TRANSACCION t', 't.CodigoBarra = r.CodigoBarra')
            ->where(['t.NumeroDocumento' => $uuid])
            ->andWhere(['t.Naturaleza = E'])
            ->asArray()
            ->all();

        return $registros;
    }

    public function obtenerListaCodigosEntrada($uuid)
    {
        // Creamos la consulta utilizando Query Builder de Yii2
        $query = RegistroModel::find()
            ->select([
                'r.CodigoBarra',
                'r.Articulo',
                'r.Descripcion',
                'r.Libras',
            ])
            ->from(['r' => 'REGISTRO']) // Alias para la tabla principal
            ->innerJoin('TRANSACCION t', 't.CodigoBarra = r.CodigoBarra')
            ->where([
                't.Naturaleza' => 'E',
                't.NumeroDocumento' => $uuid,
            ])
            ->andWhere('t.NumeroDocumento = r.DOCUMENTO_INV');
        
        // Ejecutamos la consulta y obtenemos todos los registros
        $registros = $query->asArray()->all(); // Convertimos los registros a array para facilitar su uso

        return $registros;
    }

    public function distribuirCostoBarriles($costo, $libras, $librasRipio) {
        // Calculamos el nuevo precio por libra
        $totalCosto = $costo/($libras-$librasRipio);
    
        // Devolvemos el nuevo precio
        return $totalCosto;
    }

    public function obtenerLibrasBarrilles($uuid){
        $query = RegistroModel::find()
        ->select(['SUM(libras) AS totalLibras'])
        ->innerJoin('TRANSACCION t', 't.CodigoBarra = r.CodigoBarra')
        ->alias('r') // Definir el alias 'r' aquí
        ->where([
            't.IdTipoTransaccion' => 9,
            't.NumeroDocumento' => $uuid,
        ])
        ->andWhere('t.NumeroDocumento = r.DOCUMENTO_INV');
        $totalLibras = $query->scalar();    
        //Devolvemos el total de las libras de los barriles Que pertenecen al mismo GUID
        return $totalLibras;
    }

    //FUNCION PARA LA CREACION DE BARRILES
    public function actionCreateNuevoBarril()
    {
        //Se jalan los artículos para crear los barriles
        $articulos = $this->getArticulosBarriles();

        //Carga el modelo dinamico para crear el barril
        $barrilDetalle = $this->createModelBarrilDetalle();

        //GET obtenemos el numeroDucumento que contiene el GUID que necesitamos para las transacciones
        $uuid = $this->request->get('NumeroDocumento');

        //Obtenemos el documento inventario que nos sirve para validar si es una transaccion finalizada
        $documentoInv = $this->request->get('documentoInv');

        //Obtenemos las libras y costo de los FARDOS/PACAS ingresados
        $result = $this->obtenerLibrasCosto($uuid);
        //Asignamos los resultados a las variables
        $totalCosto = $result['totalCosto'];
        $totalLibras = $result['totalLibras'];

        //Se obtienen las libras que existen en la BD de BARRILES
        $totalLibrasBarriles = $this->obtenerLibrasBarrilles($uuid);

        //Si las libras son nulas(No hay libras asignadas aún) entonces se asigna 0 como valor.
        if($totalLibrasBarriles == null){
            $totalLibrasBarriles = 0;
        }
        $searchModel = new TransaccionModelSearch();
        $dataProvider = $searchModel->searchTransactionOne(Yii::$app->request->queryParams, $uuid);

        //Recibimos los datos del POST y pasamos las variables para poder seguir jalando la información.
        if ($barrilDetalle->load($this->request->post())) {
            //Total de libras de los FARDOS/PACAS
            $totalLibras = $this->request->post('totalLibras');
            //Total de libras de los BARRILES
            $totalLibrasBarriles = $this->request->post('totalLibrasBarriles');
            //Total del costo de los FARDOS/PACAS
            $totalCosto = $this->request->post('totalCosto');
            //GUID que se jala siempre IMPORTANTE!
            $uuid = Yii::$app->request->post('uuid');

            //Libras del INPUT
            $librasInput = $this->request->post('dynamicmodel-libras-disp');
            
            //UNION del articulo con la descripcion
            $articuloDescripcionBarril = explode(" -", $barrilDetalle->articulo);
            $articulo = $this->obtenerArticulo($articuloDescripcionBarril[0]);

            //VERIFICAMOS QUE LAS LIBRAS ACUMULADAS DE LOS BARRILES NO EXCEDAN PARA MOSTRAR UNA ADVERTENCIA
            $librasAcumuladas= $totalLibrasBarriles + $librasInput;
            
            if($librasAcumuladas > $totalLibras){
                Yii::$app->session->setFlash('warning', 'ADVERTENCIA. Se han sobrepasado el total de libras');
            }
            
            try {
                //Manejamos transaccions en caso de que reviente en algún punto, no se insertan datos en la bd.
                $transaction = $this->SoftlandConn()->beginTransaction();
                $codigoBarra = $this->generateBarCode(date("Y-m-d"));

                if (($articuloDescripcionBarril[0] == "BA030")) {
                    $this->crearRegistroREGISTROBarril(
                        $codigoBarra,
                        $articuloDescripcionBarril[0],
                        $articuloDescripcionBarril[1],
                        $articulo["CLASIFICACION_2"],
                        $barrilDetalle->libras,
                        'SM00',
                        $uuid,
                        0,
                        NULL
                    );
                    $this->crearRegistroTRANSACCIONBarril($codigoBarra, 'SM00', $uuid);
                } else {
                    $this->crearRegistroREGISTROBarril(
                        $codigoBarra,
                        $articuloDescripcionBarril[0],
                        $articuloDescripcionBarril[1],
                        $articulo["CLASIFICACION_2"],
                        $barrilDetalle->libras,
                        'SM00',
                        $uuid,
                        0,
                        NULL
                    );
                    $this->crearRegistroTRANSACCIONBarril($codigoBarra, 'SM00', $uuid);
                    
                }
                $totalLibrasBarriles = $this->obtenerLibrasBarrilles($uuid);
                Yii::$app->session->setFlash('success', 'Barril generado con exito');
                $transaction->commit();

                // Llamamos a la función para obtener artículos desde la base de datos
                $articulos = $this->getArticulosBarriles();
                //Enviamos la lista de barriles 
                $searchModel = new TransaccionModelSearch();
                $dataProvider = $searchModel->searchTransactionOne(Yii::$app->request->queryParams, $uuid);
                // Restablecemos el modelo de barrilDetalle
                $barrilDetalle = $this->createModelBarrilDetalle();
                return $this->render(
                    'form-create-detalle-barril',
                    [
                        'articulos' => $articulos,
                        'barrilDetalle' => $barrilDetalle,
                        'totalLibras' => $totalLibras,
                        'totalCosto' => $totalCosto,
                        'uuid' => $uuid,
                        'totalLibrasBarriles' => $totalLibrasBarriles,
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                    ]
                );
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('warning', "Error: " . $e->getMessage());
                return $this->render(
                    'form-create-detalle-barril',
                    [
                        'articulos' => $articulos,
                        'barrilDetalle' => $barrilDetalle,
                        'uuid' => $uuid,
                        'totalLibras' => $totalLibras,
                        'totalCosto' => $totalCosto,
                        'totalLibrasBarriles' => $totalLibrasBarriles
                    ]
                );
            }
        } else {
            return $this->render('form-create-detalle-barril', [
                'articulos' => $articulos,
                'barrilDetalle' => $barrilDetalle,
                'uuid' => $uuid,
                'documentoInv' => $documentoInv,
                'totalLibras'=>$totalLibras,
                'totalCosto'=>$totalCosto,
                'totalLibrasBarriles' =>$totalLibrasBarriles,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    //FUNCION PARA LA CREACION DE BARRILES
    public function actionDetalleMesaAsignacion()
    {
        //Se jalan los artículos para crear los barriles
        $articulos = $this->consultarArticulos();

        //Carga el modelo dinamico para crear el barril
        $barrilDetalle = $this->createModelBarrilDetalle();

        $mesa = $this->request->get('mesa');

        $searchModel = new RegistroModelSearch();
        $dataProvider = $searchModel->searchBarrilesProduccion(Yii::$app->request->queryParams, $mesa);

        //Recibimos los datos del POST y pasamos las variables para poder seguir jalando la información.
        if ($barrilDetalle->load($this->request->post())) {

            $mesaAsignacion = $this->request->post('mesa');
            
            //UNION del articulo con la descripcion
            $articuloDescripcionBarril = explode(" -", $barrilDetalle->articulo);
            $articulo = $this->obtenerArticuloAsignacion($articuloDescripcionBarril[0]);
            
            try {
                //Manejamos transaccions en caso de que reviente en algún punto, no se insertan datos en la bd.
                $transaction = $this->SoftlandConn()->beginTransaction();
                $codigoBarra = RegistroModel::getNextCodigoBarra();
                
                $this->crearRegistroMesaAsignacion(
                    $codigoBarra,
                    $articuloDescripcionBarril[0],
                    $articulo['DESCRIPCION'],
                    $articulo['CLASIFICACION_1'],
                    $barrilDetalle->libras,
                    'SM00',
                    0,
                    $mesaAsignacion
                );
                //Se crea la transaccion de la mesa
                $this->crearTransaccionAsignacionMesa($codigoBarra, 'SM00');

                $transaction->commit();

                Yii::$app->session->setFlash('success', "Barril generado con éxito");


                // Llamamos a la función para obtener artículos desde la base de datos
                $articulos = $this->consultarArticulos();
                // Restablecemos el modelo de barrilDetalle
                $barrilDetalle = $this->createModelBarrilDetalle();
                return $this->render(
                    'detalle-mesa-asignacion',
                    [
                        'articulos' => $articulos,
                        'barrilDetalle' => $barrilDetalle,
                        'mesa' => $mesaAsignacion
                    ]
                );
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('warning', "Error: " . $e->getMessage());
                return $this->render(
                    'detalle-mesa-asignacion',
                    [
                        'articulos' => $articulos,
                        'barrilDetalle' => $barrilDetalle,
                        'mesa' => $mesaAsignacion
                    ]
                );
            }
        } else {
            return $this->render('detalle-mesa-asignacion', [
                'articulos' => $articulos,
                'barrilDetalle' => $barrilDetalle,
                'mesa' => $mesa,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider
            ]);
        }
    }

    public function actionIndexCajas(){
        $caja = $this->createModelCajas();
        $db = $this->softlandConn();
        if ($caja->load($this->request->post())) {
            //Inicializamos variables
            $totalLibras = 0;
            $totalCosto = 0;

            //Eliminamos los espacios en blaco al momento de recibir los códigos
            $codigosBarra = $this->trimCodigosBarra($caja->codigo_barra);

            //Verificamos si entre los códigos de barra existe un duplicado
            $duplicados = $this->verificarCodigosBarraDuplicados($codigosBarra);

            if ($duplicados != false) {
                Yii::$app->session->setFlash('danger', "Codigos de barra duplicados: $duplicados");
                $caja = $this->createModelCajas();
                return $this->render(
                    'index-cajas',
                    [
                        'caja' => $caja,
                    ]
                );
            }
            //Emprezamos la transaccion
            $transaction = $db->beginTransaction();
            $codigoBarra = $this->generateBarCode(date("Y-m-d"));
            $uuid = Uuid::uuid4();
            //Pasamos el uuid a un string
            $uuidString = $uuid->toString();
            try{
                //obtenemos el primero codigo de la lista
                $primerCodigo = $codigosBarra[0];
                $articuloPrimerCodigo = $this->validarCodigoBarraBarrilProduccion($primerCodigo);
                if (!empty($articuloPrimerCodigo)) {
                    //Recorremos el array de codigos para verificar que se cumplan ciertas condiciones.
                    foreach($codigosBarra as $index => $codigo){
                        $codigoDataREGISTRO = RegistroModel::find()->andWhere(['CodigoBarra' => $codigo])->one();
                        $codigoDataTRANSACCION = TransaccionModel::find()->andWhere(['CodigoBarra' => $codigo])->one();

                        $validarCodigosBarrilesProduccion = $this->validarCodigoBarraBarrilProduccion($codigo);
                        
                        if (!empty($validarCodigosBarrilesProduccion)) {
                            if($validarCodigosBarrilesProduccion != $articuloPrimerCodigo){
                                Yii::$app->session->setFlash('danger', "El codigo, $codigo no pertenece al mismo artículo");
                                $caja = $this->createModelCajas();
                                return $this->render(
                                    'index-cajas',
                                    [
                                        'caja' => $caja,
                                    ]
                                );
                            }

                            //Verificamos que los codigos de barra esten activos y que se encuentren finalizados
                            $validarActivo = $this->validarActivoCodigoBarrraRegistro($codigoDataREGISTRO, $codigoDataTRANSACCION);
                            
                            if(!$validarActivo){
                                Yii::$app->session->setFlash('danger', "El codigo de barras $codigo no está activo, está en otro proceso o no pertenece a bodega SM00");
                                $caja = $this->createModelCajas();
                                return $this->render(
                                    'index-cajas',
                                    [
                                        'caja' => $caja,
                                    ]
                                );
                            }

                            //Obtenemos algunos datos de los codigos para utilizarlos
                            $codigosBarra[$index] = $codigo . ', '
                            . $codigoDataREGISTRO->Articulo . ', '
                            . $codigoDataREGISTRO->Clasificacion . ', '
                            . $codigoDataREGISTRO->Descripcion . ', '
                            . $codigoDataREGISTRO->Libras . ', '
                            . $codigoDataREGISTRO->BodegaActual . ', '
                            . $codigoDataREGISTRO->Costo . ', '
                            . $codigoDataREGISTRO->FechaCreacion;
                            //asignamos el total de libras de los codigos de BARRIL/PRODUCCION que se encontraron
                            $totalLibras += $codigoDataREGISTRO->Libras;
                            $totalCosto += $codigoDataREGISTRO->Costo;
                            //Actualizamos el estado de los códigos de barra
                            $this->actualizarRegistroCodigo($codigo);
                            
                            //Actualizamos las transacciones
                            //Aqui es un insert
                            $this->crearTransaccionSalidaBarril($codigo, $codigoDataREGISTRO->BodegaActual, $uuidString);
                            
                        } else {
                            Yii::$app->session->setFlash('danger', "El codigo no pertenece a PRODUCCION/BARRILES: " . $codigo);
                            $caja = $this->createModelCajas();
                            return $this->render(
                                'index-cajas',
                                [
                                    'caja' => $caja,
                                ]
                            );
                        }
                    }
                    
                    //Creamos el registro de la caja
                    $this->crearRegistroRegistroCaja($codigoBarra, $codigoDataREGISTRO->Articulo,
                    $codigoDataREGISTRO->Descripcion, $codigoDataREGISTRO->Clasificacion, $totalLibras, $codigoDataREGISTRO->BodegaActual,
                    null, $totalCosto);

                    $this->crearTransaccionCaja($codigoBarra, $codigoDataREGISTRO->BodegaActual, null, $uuidString);
                    
                    // Confirmo la transacción
                    $transaction->commit();

                    //Enviamos peticion TRUE
                    Yii::$app->session->setFlash('success', "Los códigos han sido procesados!");
                    $searchModel = new TransaccionModelSearch();
                    $dataProvider = $searchModel->searchCajas(Yii::$app->request->queryParams);
                    return $this->render('index-lista-cajas', [
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                    ]);
                }
                else {
                    Yii::$app->session->setFlash('danger', "El codigo no pertenece a PRODUCCION/BARRILES: " . $primerCodigo);
                    $caja = $this->createModelCajas();
                    return $this->render(
                        'index-cajas',
                        [
                            'caja' => $caja,
                            //Enviar suma de libras de los barriles de una caja en especifico
                            //Enviar el codigo del artículo tambien
                        ]
                    );
                }
            }catch(\Exception $e){
                $transaction->rollBack();
                throw $e;
            }catch (\Throwable $e) {
                // En caso de error, revertir la transacción
                $transaction->rollBack();
                throw $e;
            }
            }
            return $this->render('index-cajas', [
                'caja' => $caja
            ]);
        }
    
    
    public function actionFinalizarTransaccionCajas() {
        $db = $this->softlandConn();
        if (Yii::$app->request->isAjax) {
            $postData = Yii::$app->request->post();
            $codigoBarraCaja = $postData['codigoBarra'] ?? null;
            $libras = $postData['libras'] ?? null;
            $costo = $postData['costo'] ?? null;
            $articulo = $postData['articulo'] ?? null;
            $uuid = $postData['uuid'] ?? null;
            $data = json_decode($postData['gridData'] ?? '[]', true);
    
            if (!$codigoBarraCaja || !$libras || !$costo || !$articulo || !$uuid || empty($data)) {
                return $this->asJson(['success' => false, 'message' => 'Datos incompletos.']);
            }
    
            $transaction = $db->beginTransaction();
            try {
                $consecutivoActualCNYCENTER = $this->obtenerConsecutivo('REC-LBS', 'CNYCENTER');
    
                $existeCNYCENTER = $db->createCommand(
                    "SELECT 1 FROM [CNYCENTER].[LINEA_DOC_INV] WHERE DOCUMENTO_INV = :documento"
                )->bindValue(':documento', $consecutivoActualCNYCENTER)->queryScalar();
    
                if ($existeCNYCENTER) {
                    return $this->asJson(['success' => false, 'message' => 'LINEA_DOC_INV repetida']);
                }
    
                $this->crearDocumentoInv($consecutivoActualCNYCENTER, 'CNYCENTER');
    
                foreach ($data as $item) {
                    $codigoBarra = $item['CodigoBarra'] ?? null;
                    if ($codigoBarra) {
                        $this->finalizarSalidaTransaccionBarriles($codigoBarra, $uuid, $consecutivoActualCNYCENTER);
                        $this->crearLineaDocumentoInvSalida($consecutivoActualCNYCENTER, $articulo, 'SM00', 'CNYCENTER');
                    }
                }
    
                $this->finalizarSalidaTransaccionCaja($codigoBarraCaja, $uuid, $consecutivoActualCNYCENTER);
                $this->finalizarRegistroFardo($codigoBarraCaja);
                $this->crearLineaDocumentoInvEntrada($consecutivoActualCNYCENTER, $articulo, 'SM00', $costo, 'CNYCENTER');
                $this->actualizarConsecutivo('CNYCENTER');
    
                $transaction->commit();
                return $this->asJson(['success' => true, 'message' => "Datos actualizados exitosamente, $codigoBarraCaja"]);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error("Error en actionFinalizarTransaccionCajas: " . $e->getMessage(), __METHOD__);
                return $this->asJson(['success' => false, 'message' => 'Error al procesar la transacción.']);
            }
        }
    
        return $this->asJson(['success' => false, 'message' => 'Solicitud no válida.']);
    }
        

    public function verificarCodigosBarraDuplicados2($codigosBarra)
    {
        $countCodigosBarra = array_count_values($codigosBarra);
        $codigosDuplicados = [];

        foreach ($codigosBarra as $codigo) {
            if ($countCodigosBarra[$codigo] > 1 && !in_array($codigo, $codigosDuplicados)) {
                $codigosDuplicados[] = $codigo;
            }
        }

        return $codigosDuplicados;
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
     * Valida que los codigos de barra esten disponibles
     * @return bool si el codigo de barra cumple con dichas condiciones retorna true, sino retorna false
     */
    public function validarCodigoBarraRegistroTransaccion($codigoDataREGISTRO, $codigoDataTRANSACCION)
    {
        if ($codigoDataREGISTRO->Estado == 'FINALIZADO' && $codigoDataREGISTRO->Activo == 1 && 
        $codigoDataTRANSACCION->Estado == 'F' && $codigoDataREGISTRO->BodegaActual == 'SM00' && 
        $codigoDataREGISTRO->Clasificacion == 'ROPA')
        {
            return true;
        }else if($codigoDataTRANSACCION->Estado == 'P'){
            return "PROCESO";
            //si estado en transaccion es P entonces retorna un mensaje 
        }else if($codigoDataREGISTRO->Clasificacion != 'ROPA'){  
            return "ROPA";
        }else if($codigoDataREGISTRO->BodegaActual != 'SM00'){
            return "BODEGA";
        }
        else{
            return false;
        }
    }

    public function validarCodigoBarraBarrilProduccion($codigoBarra)
    {
        $query = new Query();
        $resultados = $query->select('Articulo')
        ->from('REGISTRO')
        ->where(['CodigoBarra' => "$codigoBarra"])
        ->andWhere([
            'or', 
            "LEFT(Articulo, 2) = 'BA'", 
            "LEFT(Articulo, 1) = 'P'"
        ])
        ->scalar();
        
        return $resultados;
    }

    public function validarActivoCodigoBarrraRegistro($codigoDataREGISTRO, $codigoDataTransaccion)
    {
        if ($codigoDataREGISTRO->Estado == 'FINALIZADO' && $codigoDataREGISTRO->Activo == 1 && $codigoDataREGISTRO->BodegaActual == 'SM00'
        && $codigoDataTransaccion->Estado == 'F')
        {
            return true;
        }
        return false;
    }

    /**
     * Valida que el codigo de barra cumple con ciertas condiciones
     * @return bool si el codigo de barra cumple con dichas condiciones retorna true, sino retorna false
     */
    public function validarCodigoBarra($codigoDataREGISTRO, $codigoDataTRANSACCION, $bodega, $codigo)
    {
        $esProduccion = $this->verificarBoolCodigoProduccion($codigo);
        if ($codigoDataREGISTRO->BodegaActual == $bodega && $codigoDataREGISTRO->Activo == 1) {
            if($codigoDataREGISTRO->Estado == 'PENDIENTE' && $codigoDataTRANSACCION->Estado == 'P')
            {
                $esBarril = $this->verificarBoolCodigoBarril($codigo);
                if(!empty($esBarril) && $codigoDataREGISTRO->Costo > 0){
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

    public function verificarBoolCodigoBarril($codigoBarraBarril){
        $getDato =  Yii::$app->db->createCommand("SELECT CodigoBarra 
        FROM REGISTRO WHERE CodigoBarra = '" . $codigoBarraBarril . "' AND Articulo like 'BA%'")->queryScalar();

        return $getDato;
    }

    public function verificarBoolCodigoProduccion($codigoBarra){
        $getDato =  Yii::$app->db->createCommand("SELECT CodigoBarra 
        FROM REGISTRO WHERE CodigoBarra = '" . $codigoBarra . "' AND Articulo like 'P%'")->queryScalar();

        return $getDato;
    }

    /**
     * Obtiene el consecutivo a trabajar al momento de la creacion de barriles
     * @return string Un consecutivo el cual registrara todo movimiento de barriles en inventario
     */
    public function obtenerConsecutivo($consecutivo, $esquema)
    {
        $getConsecutivo =  $this->SoftlandConn()->createCommand("SELECT SIGUIENTE_CONSEC 
        FROM $esquema.[CONSECUTIVO_CI] WHERE CONSECUTIVO = '" . $consecutivo . "'")->queryOne();

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
            "INSERT INTO $esquema.[DOCUMENTO_INV]
            (PAQUETE_INVENTARIO, DOCUMENTO_INV, CONSECUTIVO , REFERENCIA, FECHA_HOR_CREACION, FECHA_DOCUMENTO,
            SELECCIONADO, USUARIO, APROBADO) 
            VALUES 
            (
                '" . Yii::$app->session->get('paquete') . "',
                '" . $consecutivo . "',
                'REC-LBS',
                'REC-LBS del dia " .  date("Y-m-d") . "',
                '" . date("Y-m-d H:i:s") . "',
                '" . date("Y-m-d H:i:s") . "',
                'N',
                '" . Yii::$app->session->get('user') . "',
                'N'
            )"
        )->execute();
    }

    public function obtenerCodigosPDF($NumeroDocumento)
    {
        $query = RegistroModel::find()
        ->alias('r')
        ->innerJoin(TransaccionModel::tableName() . ' t', 't.CodigoBarra = r.CodigoBarra')
        ->where(['t.NumeroDocumento' => $NumeroDocumento])
        ->select(['r.CodigoBarra', 'r.Descripcion', 'r.Libras', 't.Fecha', 't.Naturaleza'])
        ->asArray();

        $resultados = $query->all();
        return $resultados;
    }

    //Imprime la viñeta de la caja y los codigos de los barriles que este contiene.
    public function actionGeneratePdf($NumeroDocumento)
    {
        $codigos = $this->obtenerCodigosPDF($NumeroDocumento);

        // Crear una instancia de mPDF
        $mpdf = new \Mpdf\Mpdf();

        // Verificar si hay códigos y recorrer la lista
        if (empty($codigos)) {
            echo "No se encontraron códigos de barra para el documento especificado.\n";
            Yii::$app->end();
        }

        // Reordenar el arreglo para que el primer elemento tenga Naturaleza = 'E'
        usort($codigos, function($a, $b) {
            return $a['Naturaleza'] === 'E' ? -1 : 1;
        });

        $htmlContent = '
        <head>
            <meta charset="UTF-8">
            <title>CodigoBarra-Caja</title>
            <style>
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                th, td {
                    border: 0.5px solid black;
                    padding: 10px;
                    text-align: left;
                    font-family: "Nova Mono", monospace;
                }
                .left-column {
                    width: 60%; /* Ancho de la columna izquierda */
                    vertical-align: top;
                }
                .right-column {
                    vertical-align: top;
                }
                .no-border {
                    border: none; /* Sin borde */
                }
            </style>
        </head>
        <body>';

        // Obtener el primer código (Naturaleza = 'E')
        $codigoE = array_shift($codigos);

        // Etiqueta principal
        $htmlContent .= '
        <table>
            <tr>
                <th class="left-column" rowspan="3">
                    <img src="' . $this->generateBarcodeImage($codigoE['CodigoBarra']) . '">
                    <br>
                    <br>
                    <center><strong>' . $codigoE['CodigoBarra'] . '</strong></center>
                </th>
                <th class="right-column" style="padding: 5%;">
                    <center>
                    <strong>' . $codigoE['Descripcion'] . '</strong>
                    </center>
                </th>
            </tr>
            <tr>
                <td class="right-column" style="padding: 3%;">
                    <center>
                        <strong>Fecha: </strong>
                        ' . $codigoE['Fecha'] . '
                    </center>
                </td>
            </tr>
        </table>';

        // Agregar los demás códigos en una nueva columna que abarca dos columnas sin bordes
        if (!empty($codigos)) {
            $htmlContent .= '<table style="border: 0.5px solid black;">';

            foreach ($codigos as $index => $codigo) {
                if ($index % 2 == 0) {
                    $htmlContent .= '<tr>';
                }

                $htmlContent .= '
                <td colspan="2" class="no-border">
                    <center><strong>' . $codigo['CodigoBarra'] . '</strong></center>
                    <br>
                </td>';

                if ($index % 2 != 0 || $index == count($codigos) - 1) {
                    $htmlContent .= '</tr>';
                }
            }

            $htmlContent .= '</table>';
        }

        $htmlContent .= '</body>';

        // Agregar contenido al PDF
        $mpdf->WriteHTML($htmlContent);

        // Nombre del archivo de salida
        $fileName = 'codigosBarraCaja.pdf';

        // Salida del PDF: Descargar en el navegador (D) o mostrar en el navegador (I)
        $mpdf->Output($fileName, 'I');

        // Detener la ejecución de Yii2 después de generar el PDF
        Yii::$app->end();
    }


    private function generateBarcodeImage($codigoBarra)
    {
        require_once Yii::getAlias('@vendor/picqer/php-barcode-generator/src/BarcodeGeneratorPNG.php');

        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
        $barcodeImage = $generator->getBarcode($codigoBarra, $generator::TYPE_CODE_128, 3, 80); // Tipo de código, escala y ancho

        // Guardar la imagen en un directorio temporal y devolver la URL de la imagen
        $imagePath = Yii::getAlias('@webroot') . '/images/barcodes/' . $codigoBarra . '.png';
        file_put_contents($imagePath, $barcodeImage);

        // Devolver la URL de la imagen
        return Yii::getAlias('@webroot') . '/images/barcodes/' . $codigoBarra . '.png';
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
     * Crea una linea de salida de inventario la cual representa un registro de articulo barril/fardo
     * @param string $consecutivo El consecutivo actual 
     * @throws PDOException Si algun campo/valor proporcionado estan fuera de los campos/valores esperados
     */
    public function crearLineaDocumentoInvSalida($consecutivo, $articulo, $bodega, $esquema)
    {
        $this->SoftlandConn()->createCommand(
            "INSERT INTO $esquema.[LINEA_DOC_INV] 
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

    public function actualizarLineaDocumentoInvSalida($consecutivo, $articulo, $bodega, $esquema)
    {
        // Obtener el número de línea de documento
        $lineaDocInv = $this->obtenerNumeroLineaDocInv($consecutivo, $esquema);

        // Crear el comando SQL de actualización
        $command = $this->SoftlandConn()->createCommand(
            "UPDATE $esquema.[LINEA_DOC_INV] 
            SET 
                PAQUETE_INVENTARIO = :paquete_inventario,
                LINEA_DOC_INV = :linea_doc_inv,
                AJUSTE_CONFIG = :ajuste_config,
                ARTICULO = :articulo,
                BODEGA = :bodega,
                TIPO = :tipo,
                SUBTIPO = :subtipo,
                SUBSUBTIPO = :subsubtipo,
                CANTIDAD = :cantidad,
                COSTO_TOTAL_LOCAL = :costo_total_local,
                COSTO_TOTAL_DOLAR = :costo_total_dolar,
                PRECIO_TOTAL_LOCAL = :precio_total_local,
                PRECIO_TOTAL_DOLAR = :precio_total_dolar,
                COSTO_TOTAL_LOCAL_COMP = :costo_total_local_comp,
                COSTO_TOTAL_DOLAR_COMP = :costo_total_dolar_comp
            WHERE DOCUMENTO_INV = :consecutivo"
        );

        // Establecer los parámetros
        $command->bindValues([
            ':paquete_inventario' => Yii::$app->session->get('paquete'),
            ':linea_doc_inv' => $lineaDocInv,
            ':ajuste_config' => '~CC~',
            ':articulo' => $articulo,
            ':bodega' => $bodega,
            ':tipo' => 'C',
            ':subtipo' => 'D',
            ':subsubtipo' => 'N',
            ':cantidad' => 1,
            ':costo_total_local' => 0,
            ':costo_total_dolar' => 0,
            ':precio_total_local' => 0,
            ':precio_total_dolar' => 0,
            ':costo_total_local_comp' => 0,
            ':costo_total_dolar_comp' => 0,
            ':consecutivo' => $consecutivo,
        ]);

        // Ejecutar el comando
        $command->execute();
    }


    /**
     * Tomando un codigo de barra, actualiza su estado en la tabla REGISTRO
     * @param string $codigoBarra un codigo de barra de un registro existente
     */
    public function actualizarRegistroFardoREGISTRO($codigoBarra)
    {
        Yii::$app->db->createCommand(
            "UPDATE [BODEGA].[dbo].[REGISTRO] SET Activo = 0 
            WHERE CodigoBarra = '$codigoBarra' 
            AND IdTipoRegistro = 2"
        )->execute();
    }

    public function actualizarRegistroCostoBarriles($codigoBarra, $uuid, $costo)
    {
        Yii::$app->db->createCommand(
            "UPDATE [REGISTRO] SET Costo = '$costo' 
            WHERE CodigoBarra = '$codigoBarra' 
            AND DOCUMENTO_INV = '$uuid'"
        )->execute();
    }
    //ACTUALIZAR TRANSACCION AGREGAR LOS BARRILES A UN CONSECUTIVO
    public function actualizarTransaccion($codigoBarra, $consecutivo, $uuid)
    {
        Yii::$app->db->createCommand(
            "UPDATE [TRANSACCION] SET Documento_Inv = :consecutivo, Estado = 'F'
            WHERE CodigoBarra = :codigoBarra 
            AND Naturaleza = 'E' 
            AND NumeroDocumento = :uuid"
        )->bindValues([
            ':consecutivo' => $consecutivo,
            ':codigoBarra' => $codigoBarra,
            ':uuid' => $uuid,
        ])->execute();
    }

    //ACTUALIZAR TRANSACCION AGREGAR LOS FARDOS A UN CONSECUTIVO
    public function actualizarTransaccionSalida($codigoBarra, $consecutivo, $uuid)
    {
        Yii::$app->db->createCommand(
            "UPDATE [TRANSACCION] SET Documento_Inv = :consecutivo, Estado = 'F'
            WHERE CodigoBarra = :codigoBarra 
            AND Naturaleza = 'S' 
            AND NumeroDocumento = :uuid"
        )->bindValues([
            ':consecutivo' => $consecutivo,
            ':codigoBarra' => $codigoBarra,
            ':uuid' => $uuid,
        ])->execute();
    }

    //ACTUALIZA LA TABLA DE TRANSACCIONES PARA LOS CODIGOS DE BARRILES/PRODUCCION
    public function actualizarTransaccionBarriles($codigoBarra, $uuid)
    {
        Yii::$app->db->createCommand(
            "UPDATE [TRANSACCION] SET Naturaleza = 'S',
            NumeroDocumento = '$uuid'
            WHERE CodigoBarra = '$codigoBarra'"
        )->execute();
    }

    //ACTUALIZA LA TABLA DE TRANSACCIONES PARA LOS CODIGOS DE BARRILES/PRODUCCION O CAJAS
    public function finalizarSalidaTransaccionBarriles($codigoBarra, $uuid, $consecutivo)
    {
        Yii::$app->db->createCommand(
            "UPDATE [TRANSACCION] SET Documento_Inv = '$consecutivo'
            WHERE CodigoBarra = '$codigoBarra' AND NumeroDocumento = '$uuid' AND idTipoTransaccion ='1010'"
        )->execute();
    }

    public function finalizarSalidaTransaccionCaja($codigoBarra, $uuid, $consecutivo)
    {
        Yii::$app->db->createCommand(
            "UPDATE [TRANSACCION] SET Documento_Inv = '$consecutivo', Estado = 'F'
            WHERE CodigoBarra = '$codigoBarra' AND NumeroDocumento = '$uuid' AND idTipoTransaccion ='1010'"
        )->execute();
    }

    //Finaliza el registro de los fardos agregados o barriles
    public function finalizarRegistroFardo($codigoBarra)
    {
        Yii::$app->db->createCommand(
            "UPDATE [REGISTRO] SET Estado = 'FINALIZADO'
            WHERE CodigoBarra = '$codigoBarra'"
        )->execute();
    }
    //Actualizamos el código en la tabla registros y pasamos a inactivo durante el proceso.
    public function actualizarRegistroCodigo($codigoBarra)
    {
        Yii::$app->db->createCommand(
            "UPDATE [REGISTRO] SET Estado = 'PROCESO', Activo = 0
            WHERE CodigoBarra = '$codigoBarra'"
        )->execute();
    }

    // //ELIMINAR BARRIL DE LAS PACAS
    // public function eliminarBarrilPacaFardo($codigoBarra, $uuid){

    //     Yii::$app->db->createCommand(
    //         "DELETE FROM [TRANSACCION] WHERE CodigoBarra = '$codigoBarra' AND NumeroDocumento = '$uuid'"
    //     )->execute();
    // }

    //ELIMINAR EL BARRIL DE LAS TRANSACCIONES EN PACAS/FARDOS
    public function deleteBarrilFardoPaca($codigoBarra, $uuid){
        Yii::$app->db->createCommand(
            "DELETE FROM [REGISTRO] WHERE CodigoBarra = '$codigoBarra' AND DOCUMENTO_INV = '$uuid'"
        )->execute();
    }

    //ELIMINAR BARRIL DE UNA CAJA AL DARLE AL BOTON DE ELIMINAR BARRIL EN EDICION CAJA.
    public function eliminarBarrilTransaccion($codigoBarra, $uuid){

        Yii::$app->db->createCommand(
            "DELETE FROM [TRANSACCION] WHERE CodigoBarra = '$codigoBarra' AND NumeroDocumento = '$uuid'"
        )->execute();
    }

    //ACTUALIZAR BARRIL DE UNA CAJA AL BORRARLO DE LA VISTA DE EDITAR CAJA.
    public function actualizarBarrilRegistroCaja($codigoBarra){
        Yii::$app->db->createCommand(
            "UPDATE [REGISTRO] SET Estado = 'FINALIZADO', Activo = 1 WHERE CodigoBarra = '$codigoBarra'"
        )->execute();
    }

    public function actualizarLibrasCaja($codigoBarraCaja, $libras){
        //Actualizamos
        Yii::$app->db->createCommand(
            "UPDATE [REGISTRO] SET Libras = $libras WHERE CodigoBarra = '$codigoBarraCaja'"
        )->execute();
    }

    public function actualizarCostoCaja($codigoBarraCaja, $costo){
        //Actualizamos
        Yii::$app->db->createCommand(
            "UPDATE [REGISTRO] SET Costo = $costo WHERE CodigoBarra = '$codigoBarraCaja'"
        )->execute();
    }

    public function eliminarcajaRegistro($codigoBarra){
        Yii::$app->db->createCommand(
            "DELETE FROM [REGISTRO] WHERE CodigoBarra = '$codigoBarra'"
        )->execute();
    }
    /**
     * Tomando un codigo de barra, crea un registro en la tabla [BODEGA].[dbo].[TRANSACCION] para representar una salida del inventario
     * @param string $codigoBarra un codigo de barra de un registro existente
     * @param string $bodega la bodega en la que estaba registrado el fardo trabajado
     * @param string $consecutivo un codigo de consecutivo el cual enlaza el inventario con la transaccion
     */
    public function crearRegistroFardoSalidaTRANSACCION($codigoBarra, $bodega, $consecutivo)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [TRANSACCION] 
            (CodigoBarra, IdTipoTransaccion, Fecha, Bodega, Naturaleza, Estado,
            UsuarioCreacion, FechaCreacion, Documento_inv) 
            VALUES 
            ('" . $codigoBarra . "', 9, '" . date("Y-m-d") . "', '" . $bodega . "', 'S', 'F',
            '" . Yii::$app->session->get('user') . "', '" . date("Y-m-d H:i:s") . "', '" . $consecutivo . "')"
        )->execute();
    }



    /**
     * Crea una linea de entrada de inventario la cual representa un registro de articulo barril/fardo
     * @param int $consecutivo El consecutivo actual 
     * @throws PDOException Si algun campo/valor proporcionado estan fuera de los campos/valores esperados
     */
    public function crearLineaDocumentoInvEntrada($consecutivo, $articulo, $bodega, $costo, $esquema)
    {
        Yii::$app->db2->createCommand(
            "INSERT INTO $esquema.[LINEA_DOC_INV] 
            (PAQUETE_INVENTARIO, DOCUMENTO_INV, LINEA_DOC_INV, AJUSTE_CONFIG, ARTICULO, 
            BODEGA, TIPO, SUBTIPO, SUBSUBTIPO, CANTIDAD,
            COSTO_TOTAL_LOCAL, COSTO_TOTAL_DOLAR, PRECIO_TOTAL_LOCAL, PRECIO_TOTAL_DOLAR, COSTO_TOTAL_LOCAL_COMP, COSTO_TOTAL_DOLAR_COMP)
            VALUES 
            (
                '" . Yii::$app->session->get('paquete') . "',
                '" . $consecutivo . "',
                " . $this->obtenerNumeroLineaDocInv($consecutivo, $esquema) . ",
                '~OO~',
                '" . $articulo . "',
                '" . $bodega . "',
                'O',
                'D',
                'L',
                1,
                $costo,
                0,
                0,
                0,
                0,
                0
            )"
        )->execute();
    }

    public function actualizarLineaDocumentoInvEntrada($consecutivo, $articulo, $bodega, $costo, $esquema)
    {
        // Obtener el número de línea de documento
        $lineaDocInv = $this->obtenerNumeroLineaDocInv($consecutivo, $esquema);

        // Crear el comando SQL de actualización
        $command = Yii::$app->db2->createCommand(
            "UPDATE $esquema.[LINEA_DOC_INV]
            SET
                PAQUETE_INVENTARIO = :paquete_inventario,
                LINEA_DOC_INV = :linea_doc_inv,
                AJUSTE_CONFIG = :ajuste_config,
                ARTICULO = :articulo,
                BODEGA = :bodega,
                TIPO = :tipo,
                SUBTIPO = :subtipo,
                SUBSUBTIPO = :subsubtipo,
                CANTIDAD = :cantidad,
                COSTO_TOTAL_LOCAL = :costo_total_local,
                COSTO_TOTAL_DOLAR = :costo_total_dolar,
                PRECIO_TOTAL_LOCAL = :precio_total_local,
                PRECIO_TOTAL_DOLAR = :precio_total_dolar,
                COSTO_TOTAL_LOCAL_COMP = :costo_total_local_comp,
                COSTO_TOTAL_DOLAR_COMP = :costo_total_dolar_comp
            WHERE DOCUMENTO_INV = :consecutivo"
        );

        // Establecer los parámetros
        $command->bindValues([
            ':paquete_inventario' => Yii::$app->session->get('paquete'),
            ':linea_doc_inv' => $lineaDocInv,
            ':ajuste_config' => '~OO~',
            ':articulo' => $articulo,
            ':bodega' => $bodega,
            ':tipo' => 'O',
            ':subtipo' => 'D',
            ':subsubtipo' => 'L',
            ':cantidad' => 1,
            ':costo_total_local' => $costo,
            ':costo_total_dolar' => 0,
            ':precio_total_local' => 0,
            ':precio_total_dolar' => 0,
            ':costo_total_local_comp' => 0,
            ':costo_total_dolar_comp' => 0,
            ':consecutivo' => $consecutivo,
        ]);

        // Ejecutar el comando
        $command->execute();
    }


    /**
     * Crea un registro de barril en la tabla [BODEGA].[dbo].[REGISTRO]
     * @param string $codigoBarra un codigo unico irrepetible asignado a cada registro
     * @param string $articulo articulo al que pertenece el barril
     * @param string $descripcion descripcion del articulo al que pertenece el barril
     * @param string $clasificacion clasificacion del articulo al que pertenece el barril
     * @param int $libras cantidad de libras que pesa el barril
     * @param string $bodega bodega donde esta almacenado el barril
     * @param string $consecutivo consecutivo del documento de inventario al que pertenece el registro del barril
     * @param int $costo valor monetario del barril
     */
    public function crearRegistroREGISTROBarril($codigoBarra, $articulo, $descripcion, $clasificacion, $libras, $bodega, $consecutivo, $costo, $mesa)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [REGISTRO] 
            (CodigoBarra, Articulo, Descripcion, Clasificacion, Libras, IdTipoEmpaque, IdUbicacion, BodegaCreacion, BodegaActual, UsuarioCreacion, 
            DOCUMENTO_INV, Estado, Activo, Costo, FechaCreacion, Sesion, IdTipoRegistro, CreateDate, MesaOrigen)
            VALUES
            (
                '" . $codigoBarra . "',
                '" . $articulo . "',
                '" . $descripcion . "',
                '" . $clasificacion . "',
                '" . $libras . "',
                23,
                1,
                '" . $bodega . "',
                '" . $bodega . "',
                '" . Yii::$app->session->get('user') . "',
                '" . $consecutivo . "',
                'FINALIZADO',
                1,
                " . $costo . ",
                '" . date("Y-m-d ") . "',
                '" . ($this->getSessionDataRegisterDay(date("Y-m-d")) + 1) . "',
                4,
                '" . date("Y-m-d H:i:s") . "',
                '$mesa'
            )"
        )->execute();
    }

    public function crearRegistroMesaAsignacion($codigoBarra, $articulo, $descripcion, $clasificacion, $libras, $bodega, $costo, $mesa)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [REGISTRO] 
            (CodigoBarra, Articulo, Descripcion, Clasificacion, Libras, IdTipoEmpaque, IdUbicacion, BodegaCreacion, BodegaActual, UsuarioCreacion, 
            Estado, Activo, Costo, FechaCreacion, Sesion, IdTipoRegistro, CreateDate, MesaOrigenAsignacion)
            VALUES
            (
                '" . $codigoBarra . "',
                '" . $articulo . "',
                '" . $descripcion . "',
                '" . $clasificacion . "',
                '" . $libras . "',
                1023,
                1,
                '" . $bodega . "',
                '" . $bodega . "',
                '" . Yii::$app->session->get('user') . "',
                'PROCESO',
                1,
                " . $costo . ",
                '" . date("Y-m-d ") . "',
                '" . ($this->getSessionDataRegisterDay(date("Y-m-d")) + 1) . "',
                4,
                '" . date("Y-m-d H:i:s") . "',
                '$mesa'
            )"
        )->execute();
    }

    public function crearRegistroRegistroCaja($codigoBarra, $articulo, $descripcion, $clasificacion, $libras, $bodega, $consecutivo, $costo)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [REGISTRO] 
            (CodigoBarra, Articulo, Descripcion, Clasificacion, Libras, IdTipoEmpaque, IdUbicacion, BodegaCreacion, BodegaActual, UsuarioCreacion, 
            DOCUMENTO_INV, Estado, Activo, Costo, FechaCreacion, Sesion, IdTipoRegistro, CreateDate, MesaOrigen)
            VALUES
            (
                '" . $codigoBarra . "',
                '" . $articulo . "',
                '" . $descripcion . "',
                '" . $clasificacion . "',
                '" . $libras . "',
                1,
                1,
                '" . $bodega . "',
                '" . $bodega . "',
                '" . Yii::$app->session->get('user') . "',
                '" . $consecutivo . "',
                'PENDIENTE',
                1,
                " . $costo . ",
                '" . date("Y-m-d ") . "',
                '" . ($this->getSessionDataRegisterDay(date("Y-m-d")) + 1) . "',
                4,
                '" . date("Y-m-d H:i:s") . "',
                ''
            )"
        )->execute();
    }

    public function actualizarRegistroRegistroCaja($codigoBarra, $libras, $costo)
    {
        // Crear el comando SQL de actualización
        $command = Yii::$app->db->createCommand(
            "UPDATE [REGISTRO]
            SET
                Libras = :libras,
                Costo = :costo
            WHERE CodigoBarra = :codigo_barra"
        );

        // Establecer los parámetros
        $command->bindValues([
            ':libras' => $libras,
            ':costo' => $costo,
            ':codigo_barra' => $codigoBarra,
        ]);

        // Ejecutar el comando
        $command->execute();
    }


    /**
     * Crea un registro de barril en la tabla [BODEGA].[dbo].[TRANSACCCION] 
     * @param string $codigoBarra un codigo unico irrepetible asignado a cada registro
     * @param string $bodega donde esta almacenado el barril
     * @param string $consecutivo el documento de inventario al que pertenece el registro del barril
     */
    public function crearRegistroTRANSACCIONBarril($codigoBarra, $bodega, $uuid)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [TRANSACCION] 
            (CodigoBarra, IdTipoTransaccion, Fecha, Bodega, Naturaleza, Estado, UsuarioCreacion, FechaCreacion, NumeroDocumento)
            VALUES
            (
                '" . $codigoBarra . "',
                9,
                '" . date("Y-m-d") . "',
                '" . $bodega . "',
                'E',
                'F',
                '" . Yii::$app->session->get('user') . "',
                '" . date("Y-m-d H:i:s") . "',
                '" . $uuid . "'
            )"
        )->execute();
    }

    public function crearTransaccionAsignacionMesa($codigoBarra, $bodega)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [TRANSACCION] 
            (CodigoBarra, IdTipoTransaccion, Fecha, Bodega, Naturaleza, Estado, UsuarioCreacion, FechaCreacion)
            VALUES
            (
                '" . $codigoBarra . "',
                10,
                '" . date("Y-m-d") . "',
                '" . $bodega . "',
                'E',
                'P',
                '" . Yii::$app->session->get('user') . "',
                '" . date("Y-m-d H:i:s") . "'
            )"
        )->execute();
    }

    public function crearTransaccionSalidaBarril($codigoBarra, $bodega, $uuid)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [TRANSACCION] 
            (CodigoBarra, IdTipoTransaccion, Fecha, Bodega, Naturaleza, Estado, UsuarioCreacion, FechaCreacion, NumeroDocumento)
            VALUES
            (
                '" . $codigoBarra . "',
                1010,
                '" . date("Y-m-d") . "',
                '" . $bodega . "',
                'S',
                'F',
                '" . Yii::$app->session->get('user') . "',
                '" . date("Y-m-d H:i:s") . "',
                '$uuid'
            )"
        )->execute();
    }

    public function crearTransaccionCaja($codigoBarra, $bodega, $consecutivo, $uuid)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [TRANSACCION] 
            (CodigoBarra, IdTipoTransaccion, Fecha, Bodega, Naturaleza, Estado, UsuarioCreacion, FechaCreacion, Documento_Inv, NumeroDocumento)
            VALUES
            (
                '" . $codigoBarra . "',
                1010,
                '" . date("Y-m-d") . "',
                '" . $bodega . "',
                'E',
                'P',
                '" . Yii::$app->session->get('user') . "',
                '" . date("Y-m-d H:i:s") . "',
                '" . $consecutivo . "',
                '" . $uuid ."'
            )"
        )->execute();
    }

    public function crearTransaccionSalida($codigoBarra, $bodega, $consecutivo, $uuid)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [TRANSACCION] 
            (CodigoBarra, IdTipoTransaccion, Fecha, Bodega, Naturaleza, Estado, UsuarioCreacion, FechaCreacion, Documento_Inv, NumeroDocumento)
            VALUES
            (
                '" . $codigoBarra . "',
                1010,
                '" . date("Y-m-d") . "',
                '" . $bodega . "',
                'S',
                'F',
                '" . Yii::$app->session->get('user') . "',
                '" . date("Y-m-d H:i:s") . "',
                '" . $consecutivo . "',
                '" . $uuid ."'
            )"
        )->execute();
    }

    public function createTransaccionCodigo($codigoBarra, $bodega, $uuid)
    {
        
        Yii::$app->db->createCommand(
            "INSERT INTO [TRANSACCION] 
            (CodigoBarra, IdTipoTransaccion, Fecha, Bodega, Naturaleza, Estado, UsuarioCreacion, FechaCreacion, NumeroDocumento)
            VALUES
            (
                '" . $codigoBarra . "',
                10,
                '" . date("Y-m-d") . "',
                '" . $bodega . "',
                'S',
                'P',
                '" . Yii::$app->session->get('user') . "',
                '" . date("Y-m-d H:i:s") . "',
                '" . $uuid . "'
            )"
        )->execute();
    }

    /**
     * Toma el consecutivo actual de REC-LBS de [SOFTLAND].[CONINV].[CONSECUTIVO_CI] para luego aumentarlo en 1
     * @return string el nuevo consecutivo 
     */
    public function crearSiguienteConsecutivo($esquema)
    {
        $getConsecutivoCode =  $this->SoftlandConn()->createCommand("SELECT CONSECUTIVO, SIGUIENTE_CONSEC 
        FROM [$esquema].[CONSECUTIVO_CI] WHERE CONSECUTIVO = 'REC-LBS'")->queryOne();

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
     * Actualiza el consecutivo REC-LBS de [SOFTLAND].[CONINV].[CONSECUTIVO_CI]
     */
    public function actualizarConsecutivo($esquema)
    {
        $this->SoftlandConn()->createCommand("UPDATE $esquema.[CONSECUTIVO_CI] 
        SET SIGUIENTE_CONSEC = '" . $this->crearSiguienteConsecutivo($esquema) . "' WHERE CONSECUTIVO = 'REC-LBS'")->execute();
    }

    /**
     * Genera un modelo dinamico para consultar la disponibilidad de un fardo a procesar
     * @return DynamicModel Un nuevo modelo con las especificaciones necesarias
     */
    public function createModelBarriles()
    {
        $barril = new DynamicModel([
            'codigo_barra', 'bodega', 'mesa_asignacion'
        ]);

        $barril->setAttributeLabels(['codigo_barra' => 'Codigo de barra de contenedor', 'bodega' => 'Bodega', 'mesa_asignacion' => 'Mesa asignada']);
        $barril->addRule(['codigo_barra', 'bodega', 'mesa_asignacion'], 'required');

        return $barril;
    }

    public function createModelBarrilesProducccion()
    {
        $barrilProduccion = new DynamicModel([
            'codigo_barra', 'bodega', 'mesa_asignacion', 'libras'
        ]);

        $barrilProduccion->setAttributeLabels(['codigo_barra' => 'Codigo de Barra Articulo', 'bodega' => 'Bodega', 'mesa_asignacion' => 'Mesa asignada', 'libras' => 'Libras']);
        $barrilProduccion->addRule(['codigo_barra', 'bodega', 'mesa_asignacion', 'libras'], 'required');

        return $barrilProduccion;
    }

    public function createModelCajas()
    {
        $barril = new DynamicModel([
            'codigo_barra', 'bodega'
        ]);

        $barril->setAttributeLabels(['codigo_barra' => 'Codigo de barra de contenedor', 'bodega' => 'Bodega']);
        $barril->addRule(['codigo_barra', 'bodega'], 'required');

        return $barril;
    }

    /**
     * Genera un modelo dinamico para procesar los detalles de los barriles a crear
     * @return DynamicModel Un nuevo modelo con las especificaciones necesarias
     */
    public function createModelBarrilDetalle()
    {
        $barrilDetalle = new DynamicModel([
            'articulo', 'libras', 'mesa_asignacion'
        ]);

        $barrilDetalle->setAttributeLabels(['articulo' => 'Articulo', 'libras' => 'Libras']);
        $barrilDetalle->addRule(['articulo', 'libras', 'mesa_asignacion', 'mesa_asignacion' => 'Mesa asignada'], 'required');

        return $barrilDetalle;
    }

    /**
     * Obtiene todas las bodegas existentes segun el esquema del usuario logeado 
     * @return Array Contiene las bodegas contatenadas a su nombre
     * @throws PDOException Si existe algun error en el query
     */
    public function getBodegasByEsquema()
    {
        $bodegas = $this->SoftlandConn()->createCommand("SELECT BODEGA, NOMBRE FROM " . $_SESSION['esquema'] . ".BODEGA WHERE bodega LIKE'%00'  or BODEGA like 'SM%'")->queryAll();

        foreach ($bodegas as $index => $bodega) {
            $bodegas[$index]["NOMBRE"] = $bodegas[$index]["BODEGA"] . ' - ' . $bodegas[$index]["NOMBRE"];
        }
        return $bodegas;
    }

    /**
     * Obtiene todos los articulos de barriles ('BA000') segun la clasificacion de los fardos a trabajar, incluyendo ripio
     * @return Array Contiene todos los articulos de barriles, concatenados con su clasificacion, descripcion y codigo de articulo
     * @throws PDOEXception si la clasificacion dada no existe
     */
    public function getArticulosBarriles()
    {
        $articulos = $this->SoftlandConn()->createCommand("SELECT ARTICULO, DESCRIPCION, CLASIFICACION_2, ARTICULO AS ARTICULODESCRIPCION, ARTICULO as ARTICULODESCRIPIONCLASIFICACION 
        FROM [CNYCENTER].[ARTICULO] 
        WHERE ARTICULO Like '%BA%' 
        AND ACTIVO = 'S'
        OR CLASIFICACION_1 = 'RIPIO'
        ORDER BY ARTICULO")->queryAll();

        foreach ($articulos as $index => $articulo) {
            $articulos[$index]["ARTICULODESCRIPIONCLASIFICACION"] = $articulos[$index]["ARTICULO"] . ' - ' . $articulos[$index]["DESCRIPCION"] . ' - ' . $articulos[$index]["CLASIFICACION_2"];
            $articulos[$index]["ARTICULODESCRIPCION"] = $articulos[$index]["ARTICULO"] . ' - ' . $articulos[$index]["DESCRIPCION"];
        }
        return $articulos;
    }

    public function consultarArticulos()
    {
        $query = Yii::$app->db2->createCommand("SELECT a.ARTICULO, a.DESCRIPCION
        FROM CNYCENTER.ARTICULO a
        INNER JOIN CNYCENTER.EXISTENCIA_BODEGA b
        ON
        a.ARTICULO = b.ARTICULO
        WHERE a.DESCRIPCION not like '%$%'
        AND (a.ARTICULO like 'P%' OR a.ARTICULO like 'T%' or a.ARTICULO like 'BA030%')
        AND b.BODEGA = 'SM00'")->queryAll();

        $articulos = [];
        foreach ($query as $index => $articulo) {
            $articulos[$index]["ARTICULO"] = $articulo["ARTICULO"];
            $articulos[$index]["DESCRIPCION"] = $articulo["DESCRIPCION"];
            $articulos[$index]["ARTICULODESCRIPCION"] = $articulo["ARTICULO"] . ' - ' . $articulo["DESCRIPCION"];
        }

        return $articulos;
    }

    public function obtenerArticulo($articulo)
    {
        $articuloEncontrado = $this->SoftlandConn()->createCommand(
            "SELECT ARTICULO, DESCRIPCION, CLASIFICACION_2
        FROM [CNYCENTER].[ARTICULO] 
        WHERE ARTICULO = '$articulo' "
        )->queryOne();

        return $articuloEncontrado;
    }

    public function obtenerArticuloAsignacion($articulo)
    {
        $articuloEncontrado = $this->SoftlandConn()->createCommand(
            "SELECT ARTICULO, DESCRIPCION, CLASIFICACION_1
        FROM [CNYCENTER].[ARTICULO] 
        WHERE ARTICULO = '$articulo' "
        )->queryOne();

        return $articuloEncontrado;
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
    
    public function obtenerGUIDCaja($codigoBarra){
        $numeroDocumento = TransaccionModel::find()
        ->select('NumeroDocumento')
        ->where(['CodigoBarra' => $codigoBarra])
        ->scalar(); // Utilizamos scalar() para obtener directamente el valor del campo

        return $numeroDocumento;
    }

    private function eliminarCajaTransaccion($numeroDocumento){
        $filasEliminadas = TransaccionModel::deleteAll(['NumeroDocumento' => $numeroDocumento]);
        return $filasEliminadas;
    }
}
