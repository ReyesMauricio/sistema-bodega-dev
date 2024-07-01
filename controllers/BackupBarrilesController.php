<?php

namespace app\controllers;

use app\models\AsignacionClasificacion;
use app\models\RegistroModel;
use app\models\TransaccionModel;
use app\modelsSearch\RegistroModelSearch;
use Exception;
use yii\web\Controller;
use Yii;
use yii\base\DynamicModel;
use Ramsey\Uuid\Uuid;
use yii\filters\VerbFilter;
use yii\helpers\Url;

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
        // // Aplicar filtros adicionales si es necesario
        // $dataProvider->query->andWhere(['IdTipoRegistro' => 4, 'IdTipoEmpaque' => 23, 'Activo' => 1])
        //                     ->orderBy(['CreateDate' => SORT_DESC]);

        return $this->render('index-barriles', [
            'dataProvider' => $dataProvider,
            'imprimir' => $condicionImprimir,
            'searchModel' => $searchModel
        ]);
    }

    public function actionIndexVerificarCodigo(){
        //jalamos el barril
        $barril = $this->createModelBarriles();
        $barrilDetalle = $this->createModelBarrilDetalle();
        $articulos = $this->getArticulosBarriles();
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
                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Los códigos se han procesado');
                return $this->render(
                    'form-create-detalle-barril',
                    [
                        'barril' => $barril,
                        'barrilDetalle' => $barrilDetalle,
                        'uuid' => $uuidString,
                        'totalLibras' => $totalLibras,
                        'totalCosto' => $totalCosto,
                        'articulos' => $articulos
                    ]
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
        $searchModel = new TransaccionModel();
        $dataProvider = $searchModel->index();

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

                $validaciones = $this->validarCodigoBarra($codigoDataREGISTRO, $codigoDataTRANSACCION, $barril->bodega);
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
                    Yii::$app->session->setFlash('danger', "Codigos de barra \"$codigo\" no disponible en bodega : $barril->bodega ");
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
        return $this->redirect(['index-barriles', 'condicionImprimir' => '']);
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

    public function obtenerLibrasPorRipio($uuid){
        $query = (new \yii\db\Query())
        ->select(['SUM(Libras) as librasPorRipio'])
        ->from('REGISTRO')
        ->where(['and', ['Articulo' => 'BA030'], ['DOCUMENTO_INV' => $uuid]])
        ->scalar();

    return $query;
    }

    public function actionFinalizarTransaccionBarriles()
    {
        $request = Yii::$app->request;

        // Obtener los datos del formulario
        $totalLibras = $request->post('totalLibras');
        $totalCosto = $request->post('totalCosto');
        $uuid = $request->post('uuid');
        // Otras variables necesarias...

        // Llamar a la función y realizar las operaciones necesarias
        //$resultado = $this->finalizarTransaccionBarriles($totalLibras, $totalCosto, $uuid);
        try{
            $resultado = $this->finalizarTransaccionBarriles($totalLibras, $totalCosto, $uuid);
            // if($resultado == "TOLERANCIA"){
            //     Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            //     return ['error' => '¡Error! La cantidad de libras no se encuentra en el margen de tolerancia.'];
            // }
        }catch(Exception $e){
            $resultado = "Error: $e";
        }   
        
        
        // Devolver una respuesta (si es necesario)
        return json_encode($resultado);
    }

    public function finalizarTransaccionBarriles($totalLibras, $totalCosto, $uuid){
        //Inicializamos VARIABLES
        $numeroBarriles = 0;
        //porcentaje asignado para verificar el calculo del margen máximo de tolerancia de diferencia
        $porcentaje = 10;

        //Obtenemos el total de libras de los barriles asignados al GUID contando el RIPIO 
        //Ya que nos va servir para restar las libras de ripio y tener ambos segmentos separados
        //Para futuras intervenciones
        $librasTotalBarriles = $this->obtenerLibrasBarrilles($uuid);
        //<-------------------------------------------------------------------------------->
        //Obtenemos el precio por libra original sin ser afectado por la distribucion
        $precioLibra = $totalCosto/$totalLibras;

        //Obtenemos el numero de barriles sin RIPIO
        $numeroBarriles = $this->obtenerNumeroBarriles($uuid);

        //Este es el costo del numero de barriles sin contar el ripio
        //Aqui sacamos el total del costo segun los barriles que se hayan creado
        //Se piensa utilizar para reporte a futuro.
        $costoOriginal = $precioLibra * $numeroBarriles;
        //<-------------------------------------------------------------------------------->
        //Obtenemos las libras por ripio
        $librasRipio=$this->obtenerLibrasPorRipio($uuid);

        //Obtenemos el nuevo costo por libra, tomando en cuenta las libras de ripio que se encontraron
        $nuevoPrecioLibra = $this->distribuirCostoBarriles($totalCosto, $totalLibras, $librasRipio);

        //Costo nuevo de distribución según la cantidad del peso del ripio encontrado
        $costoDistribuido = $nuevoPrecioLibra * $numeroBarriles;

        $cantidadMaxima = $totalLibras*(1+($porcentaje/100));
        $cantidadMinima = $totalLibras*(1-($porcentaje/100));

        
        if($librasTotalBarriles >= $cantidadMinima && $librasTotalBarriles<=$cantidadMaxima){
            if($totalCosto == $costoDistribuido){
                //Se procede a hacer los insert en PRUEBAS LINEA_DOV_INV, DOC_INV Y UPDATE EN BODEGA
                try{
                    $transaction = Yii::$app->db->beginTransaction();
                    
                    //OBTENER EL CONSECUTIVO DE LA TABLA CONSECUTIVO_CI
                    $consecutivoActualCNYCENTER = $this->obtenerConsecutivo('REC-LBS', 'CNYCENTER');

                    $existeCNYCENTER = $this->SoftlandConn()->createCommand(
                        "SELECT * FROM [CNYCENTER].[LINEA_DOC_INV] WHERE DOCUMENTO_INV = '$consecutivoActualCNYCENTER'"
                        )->queryAll();
                
                    //Verificamos si existe y se hace un delete
                    if ($existeCNYCENTER) {
                        return "El consecutivo se encuentra repetido";
                    }

                //creamos el doc_inv
                $doc_inv = $this->crearDocumentoInv($consecutivoActualCNYCENTER, 'CNYCENTER');
                //Obtenemos la lista de codigos de ENTRADA
                $listaCodigosEntrada = $this->obtenerListaCodigosEntrada($uuid);
                
                //Obtenemos la lista de códigos de salida
                $listaCodigosSalida = $this->obtenerListaCodigosSalida($uuid);

                if (empty($listaCodigosEntrada)) {
                    return "No se encontraron registros para el UUID proporcionado.";
                }
                //Recorremos el listado para obtener los registros
                foreach ($listaCodigosEntrada as $registro) {
                    $codigoBarra = $registro['CodigoBarra'];
                    $articulo = $registro['Articulo'];
                    $libras = intval($registro['Libras']);
                    $costo = $nuevoPrecioLibra * $libras;
                    
                    $this->crearLineaDocumentoInvEntrada($consecutivoActualCNYCENTER,$articulo,'SM00',($costo),'CNYCENTER');

                }
                //OBTENEMOS LA LISTA DE LOS DOCUMENTOS DE SALIDA
                if(empty($listaCodigosSalida)){
                    return "no se encontraron registros";
                }
                foreach ($listaCodigosSalida as $registro) {
                    // Accede a cada campo del registro
                    $codigoBarra = $registro['CodigoBarra'];
                    $libras = $registro['libras'];
                    $articulo = $registro['Articulo'];
                    $bodega = $registro['BodegaActual'];
                    $this->crearLineaDocumentoInvSalida($consecutivoActualCNYCENTER, $articulo, $bodega, 'CNYCENTER');
                    $this->finalizarRegistroFardo($codigoBarra);
                    $this->actualizarTransaccion($codigoBarra, $consecutivoActualCNYCENTER);
                }
                
                //Si se cumplen las consultas
                $transaction->commit();
                }catch(Exception $e){
                    $transaction->rollBack();
                    return $e;
                }

                //Devolvemos una alerta que se realizó con éxito la transacción
                $mensaje = "Se han registrado los datos $costoDistribuido, $nuevoPrecioLibra, $librasTotalBarriles, $cantidadMaxima";
                return $mensaje;
            }else{
                $mensaje = "El costo de los barriles no coincide con el costo final $costoDistribuido, $nuevoPrecioLibra, $librasTotalBarriles, $cantidadMaxima";
                return $mensaje;
            }
        }
        $mensaje = "TOLERANCIA";
        return $mensaje;
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
                't.IdTipoTransaccion' => 9,
                't.Documento_Inv' => $uuid,
            ])
            ->andWhere('t.Documento_Inv = r.DOCUMENTO_INV');
        
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
            't.Documento_Inv' => $uuid,
        ])
        ->andWhere('t.Documento_Inv = r.DOCUMENTO_INV');
        $totalLibras = $query->scalar();    
        //Devolvemos el total de las libras de los barriles Que pertenecen al mismo GUID
        return $totalLibras;
    }

    //Crea un nuevo barril
    public function actionCreateNuevoBarril()
    {
        $articulos = $this->getArticulosBarriles();
        $barrilDetalle = $this->createModelBarrilDetalle();
        $uuid = $this->request->get('NumeroDocumento');
        $result = $this->obtenerLibrasCosto($uuid);
        $totalCosto = $result['totalCosto'];
        $totalLibras = $result['totalLibras'];
        $totalLibrasBarriles = $this->obtenerLibrasBarrilles($uuid);

        if ($barrilDetalle->load($this->request->post())) {
            //Total de libras de los FARDOS/PACAS
            $totalLibras = $this->request->post('totalLibras');
            //Total de libras de los BARRILES
            $totalLibrasBarriles = $this->request->post('totalLibrasBarriles');
            //Total del costo de los FARDOS/PACAS
            $totalCosto = $this->request->post('totalCosto');
            //GUID que se jala siempre IMPORTANTE!
            $uuid = Yii::$app->request->post('uuid');

            //Libras que vienen del input 
            $librasInput = $this->request->post('dynamicmodel-libras-disp');
            //UNION del articulo con la descripcion
            $articuloDescripcionBarril = explode(" -", $barrilDetalle->articulo);
            $articulo = $this->obtenerArticulo($articuloDescripcionBarril[0]);

            //VERIFICAMOS QUE LAS LIBRAS ACUMULADAS DE LOS BARRILES NO EXCEDAN PARA MOSTRAR UNA ADVERTENCIA
            $librasAcumuladas= $totalLibrasBarriles + $librasInput;
            
            if($librasAcumuladas > $totalLibras){
                Yii::$app->session->setFlash('warning', 'ADVERTENCIA. Se han sobrepasado el total de libras');
            }
            //$mesa = explode(" -", $barrilDetalle->mesa_asignacion)[0];

            //$librasAsignadas = AsignacionClasificacion::find()->where(['NumeroMesa' => $mesa])->sum('Libras');
            //$costoAsignado = AsignacionClasificacion::find()->where(['NumeroMesa' => $mesa])->sum('Costo');

            // if ($barrilDetalle->libras > $librasAsignadas) {
            //     Yii::$app->session->setFlash('danger', "Mesa $mesa no posee suficientes libras para generar nuevo barril");

            //     return $this->render(
            //         'form-create-detalle-barril',
            //         [
            //             'articulos' => $articulos,
            //             'barrilDetalle' => $barrilDetalle,
            //         ]
            //     );
            // }
            $transaction = $this->SoftlandConn()->beginTransaction();
            try {
                //$consecutivoActualCONINV = $this->obtenerConsecutivo('REC-LBS', 'CONINV');
                //$consecutivoActualCNYCENTER = $this->obtenerConsecutivo('REC-LBS', 'CNYCENTER');

                // $existeCONINV = $this->SoftlandConn()->createCommand(
                //     "SELECT * FROM [PRUEBAS].[CONINV].[LINEA_DOC_INV] WHERE DOCUMENTO_INV = '$consecutivoActualCONINV'"
                // )->queryAll();
                // $existeCNYCENTER = $this->SoftlandConn()->createCommand(
                //     "SELECT * FROM [CNYCENTER].[LINEA_DOC_INV] WHERE DOCUMENTO_INV = '$consecutivoActualCNYCENTER'"
                // )->queryAll();

                // if ($existeCNYCENTER) {
                //     // $this->SoftlandConn->createCommand(
                //     //     "DELETE FROM [PRUEBAS].[CONINV].[DOCUMENTO_INV] WHERE DOCUMENTO_INV = '$consecutivoActualCONINV'"
                //     // )->execute();
                //     $this->SoftlandConn()->createCommand(
                //         "DELETE FROM [CNYCENTER].[DOCUMENTO_INV] WHERE DOCUMENTO_INV = '$consecutivoActualCNYCENTER'"
                //     )->execute();
                // }

                //$this->crearDocumentoInv($consecutivoActualCONINV, 'CONINV');
                //$this->crearDocumentoInv($consecutivoActualCNYCENTER, 'CNYCENTER');
                $codigoBarra = $this->generateBarCode(date("Y-m-d"));
                //$costo = $costoAsignado / $librasAsignadas;

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
                        //Aqui se calculaba el costo
                        //(costo * $barrilDetalle->libras)
                        0,
                        NULL
                    );
                    $this->crearRegistroTRANSACCIONBarril($codigoBarra, 'SM00', $uuid);
                    // $this->crearLineaDocumentoInvEntrada(
                    //     $consecutivoActualCNYCENTER,
                    //     $articuloDescripcionBarril[0],
                    //     'SM00',
                    //     ($costo * $barrilDetalle->libras),
                    //     'CONINV'
                    // );
                    // $this->crearLineaDocumentoInvEntrada(
                    //     $consecutivoActualCNYCENTER,
                    //     $articuloDescripcionBarril[0],
                    //     'SM00',
                    //     ($costo * $barrilDetalle->libras),
                    //     'CNYCENTER'
                    // );
                    // Yii::$app->db->createCommand(
                    //     "INSERT INTO [ASIGNACION_CLASIFICACION] (Libras, Costo, NumeroMesa, Fecha, CreateDate) 
                    // VALUES (-$barrilDetalle->libras, ($costo * -$barrilDetalle->libras),  '$mesa', '" . date("Y-m-d") . "', getdate())"
                    // )->execute();
                }
                //$this->actualizarConsecutivo('CONINV');
                //$this->actualizarConsecutivo('CNYCENTER');
                $totalLibrasBarriles = $this->obtenerLibrasBarrilles($uuid);
                Yii::$app->session->setFlash('success', 'Barril generado con exito');
                $transaction->commit();
                return $this->render(
                    'form-create-detalle-barril',
                    [
                        'articulos' => $articulos,
                        'barrilDetalle' => $barrilDetalle,
                        'totalLibras' => $totalLibras,
                        'totalCosto' => $totalCosto,
                        'uuid' => $uuid,
                        'totalLibrasBarriles' => $totalLibrasBarriles
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
                'totalLibras'=>$totalLibras,
                'totalCosto'=>$totalCosto,
                'totalLibrasBarriles' =>$totalLibrasBarriles
            ]);
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

    /**
     * Valida que el codigo de barra cumple con ciertas condiciones
     * @return bool si el codigo de barra cumple con dichas condiciones retorna true, sino retorna false
     */
    public function validarCodigoBarra($codigoDataREGISTRO, $codigoDataTRANSACCION, $bodega)
    {
        if (
            $codigoDataREGISTRO->Estado == 'FINALIZADO'
            && $codigoDataREGISTRO->BodegaActual == $bodega
            && $codigoDataREGISTRO->Activo == 1
            && $codigoDataTRANSACCION->Estado == 'F'
            && $codigoDataTRANSACCION->Naturaleza == 'E'
            && $codigoDataTRANSACCION->IdTipoTransaccion == 1
        ) {
            return true;
        }

        return false;
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

    public function actualizarTransaccion($codigoBarra, $consecutivo)
    {
        Yii::$app->db->createCommand(
            "UPDATE [TRANSACCION] SET Documento_Inv = '$consecutivo' 
            WHERE CodigoBarra = '$codigoBarra' 
            AND IdTipoTransaccion = 10"
        )->execute();
    }
    //Finaliza el registro de los fardos agregados
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

    /**
     * Crea un registro de barril en la tabla [BODEGA].[dbo].[TRANSACCCION] 
     * @param string $codigoBarra un codigo unico irrepetible asignado a cada registro
     * @param string $bodega donde esta almacenado el barril
     * @param string $consecutivo el documento de inventario al que pertenece el registro del barril
     */
    public function crearRegistroTRANSACCIONBarril($codigoBarra, $bodega, $consecutivo)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [TRANSACCION] 
            (CodigoBarra, IdTipoTransaccion, Fecha, Bodega, Naturaleza, Estado, UsuarioCreacion, FechaCreacion, Documento_Inv)
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
                '" . $consecutivo . "'
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
                'F',
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

    public function obtenerArticulo($articulo)
    {
        $articuloEncontrado = $this->SoftlandConn()->createCommand(
            "SELECT ARTICULO, DESCRIPCION, CLASIFICACION_2
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
}
