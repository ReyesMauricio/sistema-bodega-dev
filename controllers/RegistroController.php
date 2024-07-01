<?php

namespace app\controllers;

use app\models\RegistroModel;
use app\models\TransaccionModel;
use app\modelsSearch\RegistroModelSearch;
use app\modelsSearch\TransaccionModelSearch;
use Exception;
use Yii;
use yii\base\DynamicModel;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;

/**
 * RegistroController es un controlador complejo, permite la creacion de registros para ingreso de contenedor, sus detalles y su respectivo 
 * retaceo, como tambien eliminar detalles de contenedor.
 */
class RegistroController extends Controller
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
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Crear una nueva conexion hacia la base de datos SOFTLAND
     * @return YiiDbConnection Una conexion de base de datos
     */

    function SoftlandConn()
    {
        return Yii::$app->db2;
    }

    /**
     * Crea un ActiveRecord que contiene toda la informacion acerca de contenedores
     * @return Array|Object|string Un array asociativo con el conjunto de campos necesarios para los contenedores
     */
    public function actionIndexContenedor()
    {
        if (!isset($_SESSION['user'])) {
            return $this->redirect(Url::to(Yii::$app->request->baseUrl . '/index.php?r=site/login'));
        }

        $dataProvider = TransaccionModel::find()->distinct()
            ->select('NumeroDocumento, Bodega, Estado, Fecha, COUNT(IdTransaccion) as IdTransaccion, UsuarioCreacion')
            ->where(
                "Naturaleza = 'E' 
            AND IdTipoTransaccion = 1 
            AND NumeroDocumento IS NOT NULL"
            )->groupBy('NumeroDocumento, Bodega, Estado, Fecha, UsuarioCreacion')->orderBy(['Fecha' => SORT_DESC])->asArray()->all();

        return $this->render('index-contenedor', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Crea un ActiveRecord que contiene toda la informacion acerca de contenedores
     * @return Array|Object|string Un array asociativo con el conjunto de campos necesarios para los contenedores
     */
    public function actionIndexCodigosContenedor()
    {
        if (!isset($_SESSION['user'])) {
            return $this->redirect(Url::to(Yii::$app->request->baseUrl . '/index.php?r=site/login'));
        }

        $searchModel = new RegistroModelSearch();
        $dataProvider = $searchModel->search(
            $this->request->queryParams,
            "DOCUMENTO_INV IS NOT NULL AND IdTipoRegistro = 2 AND Activo = 1"
        );
        
        $dataProvider->sort->defaultOrder = ['CreateDate' => SORT_DESC];

        return $this->render('index-codigos-contenedor', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Funcion para verificar la existencia del contenedor a crear
     * @return View|Message redirecciona a una vista si el contenedor no existe o esta en proceso, sino retorna un mensaje de alerta
     */
    public function actionCreateContenedor()
    {
        if (!isset($_SESSION['user'])) {
            return $this->redirect(Url::to(Yii::$app->request->baseUrl . '/index.php?r=site/login'));
        }
        $bodegas = ["",""];
        $contenedor = $this->crearModelContenedor();
        $conjuntos = $this->getConjuntos();
        try{
            //Solicitud que se realiza en cuanto se seleccione una bodega
            if (Yii::$app->request->isAjax) {
                $esquema = Yii::$app->request->get('esquema');
                $bodegas = $this->getBodegasByEsquema2($esquema);
                //convertimos el dato a un json
                return json_encode($bodegas);
            }
        }catch(Exception $err){
            $errorMessage = $err->getMessage();
            $response = ['error' => $errorMessage];
            return json_encode($response);
        }
        if ($contenedor->load($this->request->post())) {
            $contenedorExist = $this->validarExistenciaContenedor($contenedor->fecha_creacion, $contenedor->contenedor);
            $empresaSeleccionada = Yii::$app->request->post('conjunto');
            echo $empresaSeleccionada;
            if ($contenedorExist == 0) {
                return $this->redirect(
                    [
                        'view-contenedor',
                        'contenedor' => $contenedor->contenedor,
                        'fecha' => $contenedor->fecha_creacion,
                        'bodega' => $contenedor->bodega,
                        'estado' => 'P',
                        'empresa'=> $empresaSeleccionada
                    ]
                );
            } else {
                $contenedor = $this->crearModelContenedor();
                Yii::$app->session->setFlash('danger', "Contenedor ya existe o esta finalizado, ingrese otros valores!");
                return $this->render('create-contenedor-form', ['bodegas' => $bodegas, 'contenedor' => $contenedor]);
            }
        } else {
            return $this->render('create-contenedor-form', ['bodegas' => $bodegas, 'contenedor' => $contenedor, 'conjuntos'=>$conjuntos]);
        }
    }

    public function getEsquema($contenedor, $bodega){
        $esquema = Yii::$app->esquema0->nombre;
        $data = Yii::$app->db->createCommand(
            "SELECT Empresa
            FROM $esquema.[dbo].[REGISTRO]  
            WHERE DOCUMENTO_INV = '$contenedor' 
            AND BodegaActual = '$bodega'"
        )->queryOne();

        return $data;
    }

    /**
     * Crea registros en las tablas [BODEGA].[dbo].[REGISTRO] y [BODEGA].[dbo].[TRANSACCION] que detallan el contenido de un fardo
     * @param string $contenedor el nombre del contenedor
     * @param date $fecha la fecha de creacion del contenedor
     * @param string $bodega la bodega donde esta el contenido del contenedor
     * @param string $estado el estado del contenedor, puede ser F (FINALIZADO) o P (PROCESO)
     * @return view Una vista donde se pueden registrar detalles de contenedor
     */
    public function actionViewContenedor($contenedor, $fecha, $bodega, $estado, $empresa=null)
    {
        set_time_limit(300);
        //Este método solo aplica si intentamos acceder al detalle del contenedor y solicitamos el nombre de la empresa
        //segun el contenedor y bodega a la que pertenece
        if($empresa==null){
            echo $empresa;
            //Obtenemos el esquema
            $esquema = $this->getEsquema($contenedor, $bodega);
            //pasamos los valores del esquema a un array para obtener solo un dato
            $valores = array_values($esquema);
            //Obtenemos los articulos por la consulta
            $articulos = $this->getArticulosByEsquema($valores[0]);
        }
        //Si la petición viene del create solo recibe la variable como parámetro
        //Ya que $empresa no debería llegar null
        else
        {
            $articulos = $this->getArticulosByEsquema($empresa);
        }
        $detalleContenedor = $this->crearModelDetalleContenedor();
        $data = $this->obtenerDataContenedor($contenedor, $fecha, $bodega, $empresa);
        
        if ($detalleContenedor->load($this->request->post())) {

            $transaction = Yii::$app->db2->beginTransaction();
            try {
                for ($i = 1; $i <= $detalleContenedor->cantidad; $i++) {

                    $sesionDataDay = $this->getSessionDataRegisterDay($fecha) + 1;
                    $codigoBarra = $this->generateBarCode($fecha);
                    $this->crearRegistroREGISTRODetalleContenedor(
                        $codigoBarra,
                        $detalleContenedor->articulo,
                        $detalleContenedor->peso,
                        $bodega,
                        $contenedor,
                        $sesionDataDay,
                        $fecha,
                        //Para crear el registro primero verificamos desde que acción nos están llegando los datos
                        //Si es desde el index pasamos valores[0], si es desde el create pasamos $empresa
                        $empresa == null ? $valores[0] : $empresa
                    );

                    $this->crearRegistroTRANSACCIONDetalleContenedor($codigoBarra, $bodega, $contenedor, $fecha);
                }
                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('warning', "Error: " . $e->getMessage());
                return $this->redirect(['index-contenedor']);
            }

            $detalleContenedor = $this->crearModelDetalleContenedor();
            Yii::$app->session->setFlash('info', "Detalle ingresado.");
            $data = $this->obtenerDataContenedor($contenedor, $fecha, $bodega);

            return $this->render(
                'view-contenedor',
                [
                    'fecha' => $fecha,
                    'contenedor' => $contenedor,
                    'bodega' => $bodega,
                    'detalleContenedor' => $detalleContenedor,
                    'articulos' => $articulos,
                    'estado' => $estado,
                    'data' => $data,
                ]
            );
        } else {
            return $this->render('view-contenedor', [
                'fecha' => $fecha,
                'contenedor' => $contenedor,
                'bodega' => $bodega,
                'detalleContenedor' => $detalleContenedor,
                'articulos' => $articulos,
                'estado' => $estado,
                'data' => $data,
            ]);
        }
    }

    /**
     * Elimina un detalle de contenedor
     * @param string $contenedor el nombre del contenedor
     * @param string $fecha la fecha de creacion del fardo
     * @param string $bodega la bodega donde esta el fardo
     * @param string $articulo el articulo al que pertenece el fardo
     * @param int $libras la cantidad de libras que posee el fardo
     * @return view Una vista donde se pueden registrar detalles de contenedor
     */
    public function actionDeleteContenedor($contenedor, $fecha, $articulo, $bodega, $libras, $empresa)
    {
        $codigosBarra = $this->obtenerCodigosBarraBorrarDetalleContenedor($articulo, $libras, $fecha, $contenedor, $bodega);

        foreach ($codigosBarra as $codigoBarra) {
            $this->borrarRegistroTRANSACCIONDetalleContenedor($codigoBarra["CodigoBarra"], $fecha, $contenedor);
            $this->borrarRegistroREGISTRODetalleContenedor($codigoBarra["CodigoBarra"], $articulo, $libras, $fecha, $contenedor, $bodega);
        }

        Yii::$app->session->setFlash('danger', "Detalle de contenedor eliminado.");
        return $this->redirect(
            [
                'view-contenedor',
                'fecha' => $fecha,
                'contenedor' => $contenedor,
                'bodega' => $bodega,
                'estado' => 'P',
                'empresa' => $empresa   
            ]
        );
    }
    public function obtenerNombreEmpresa($contenedor)
    {
        $nombreEmpresa = Yii::$app->db->createCommand(
            "SELECT Empresa 
            FROM REGISTRO 
            WHERE DOCUMENTO_INV = :contenedor"
        )->bindValue(':contenedor', $contenedor)
        ->queryScalar();

        // Verificar si se obtuvo un nombre de empresa
        if ($nombreEmpresa !== false) {
            return $nombreEmpresa;
        } else {
            // Si no se encontró ningún nombre de empresa, devolver null o algún valor predeterminado
            return null;
        }
    }


    /**
     * Permite evaluar y calcular el retaceo de un contenedor en proceso
     * @return view Una vista con la informacion del contenedor, si este se encuentra en proceso
     */
    public function actionValorContenedor()
    {
        $valorContenedor = $this->crearModelValorContenedor();
        $data = $this->obtenerDetalleValorContenedor($valorContenedor->contenedor, $valorContenedor->fecha);
        if ($valorContenedor->load($this->request->post())) {
            $contenedorExist = $this->validarExistenciaContenedor($valorContenedor->fecha, $valorContenedor->contenedor);
            if ($contenedorExist == 0) {
                $data = $this->obtenerDetalleValorContenedor($valorContenedor->contenedor, $valorContenedor->fecha);
                $nombreEmpresa = $this->obtenerNombreEmpresa($valorContenedor->contenedor);
                return $this->render(
                    'valor-contenedor-form',
                    [
                        'valorContenedor' => $valorContenedor,
                        'detalle' => true,
                        'data' => $data,
                        'nombreEmpresa'=>$nombreEmpresa
                    ]
                );
            } else {
                Yii::$app->session->setFlash('danger', "Contenedor no existe o esta finalizado, ingrese otros valores!");
                $valorContenedor = $this->crearModelValorContenedor();
                return $this->render(
                    'valor-contenedor-form',
                    [
                        'valorContenedor' => $valorContenedor,
                        'detalle' => false,
                        'data' => $data,
                    ]
                );
            }
        } else {
            return $this->render(
                'valor-contenedor-form',
                [
                    'valorContenedor' => $valorContenedor,
                    'detalle' => false,
                    'data' => $data
                ]
            );
        }
    }

    /**
     * Establece un conjunto de valores contable y administrativos sobre el contenedor trabajado
     * @return view Una vista donde se veran todos los contenedores registrados
     */
    public function actionSetValorContenedor()
    {
        $valores = json_decode($_POST['valoresPost']);
        $empresa = Yii::$app->request->post('nombreEmpresa');
        $bodega = Yii::$app->request->post('contenedor2');
        $fechaContenedor = explode(", ", $_POST['fecha-contenedor']);
        $fecha = $fechaContenedor[0];
        $contenedor = $fechaContenedor[1];
        $subtotalFinal = 0;

        //Validar si existe compra anterior y proveedor en esquema asignado a usuario
        if($this->validarGlobalesCO($empresa)){
            
            if(!$this->validarProveedorExiste($empresa)){
                Yii::$app->session->setFlash('danger', 'El proveedor no existe');
                return $this->redirect(['index-contenedor']);
            }else{
                //Usamos rollback en caso de que el algunas consultas se genere un error, los insert hechos anteriormente
                //Se van a devolver a su estado anterior.
                //Transaction para la bd Softland
                $transaction = Yii::$app->db2->beginTransaction();
                //Transaction para la bd BODEGA
                $transactionBodega = Yii::$app->db->beginTransaction();
                //Crear registro newyork
                $ordenCompra = $this->generarCodigoUltimaOrdenCompra($empresa);
                try{
                    //$this->generarUltimaOrdenCompra($ordenCompra, $empresa);
                    if(!$this->generarUltimaOrdenCompra($ordenCompra, $empresa, $bodega)){
                        Yii::$app->session->setFlash('error', "Error en generar ultima compra,$ordenCompra, $empresa, $bodega");
                        return $this->redirect(['index-contenedor']);
                    }    
                    //Crear registro coninv
                    //$consecutivo = $this->obtenerConsecutivo();
                    //$this->crearDocumentoInv($consecutivo);

                    foreach ($valores as $valor) {
                        $datos = explode(", ", $valor);
                        /*  Referencias de datos a ser procesados
                            $datos[0] => FARD0-0001 -> Articulo
                            $datos[1] => 5.735714 -> Precio unitario
                            $datos[2] => 500.00 -> libras
                            $datos[3] => CARTERA 110 AT -> descricion
                            $datos[4] => CA00 -> bodega
                            $datos[5] => 5 -> cantidad fardos
                            $datos[6] => 725 -> subtotal antes del gasto
                        */
                        
                        //Verificar que el articulo enviado esta asociado al proveedor
                        if(!$this->validarArticuloProveedor($datos[0], $empresa, $datos[3])){
                            Yii::$app->session->setFlash('error', "Error en validar articulo de proveedor,$datos[0], $empresa, $datos[3]");
                            return $this->redirect(['index-contenedor']);
                        }  

                        //Crear detalle d   e contenedor en DOC INV LINEA
                        //$this->crearLineaDocInv($consecutivo, $datos[0], $datos[4], $datos[5], $datos[1], $datos[2]);

                        //Crear detalle de contenedor en ORDEN COMPRA LINEA
                        if(!$this->generarLineaOrdenCompra($ordenCompra,$empresa, $datos[0], $datos[4], $datos[3], $datos[5], $datos[6])){
                            Yii::$app->session->setFlash('error', "Error en generar linea de orden de compra-- datos: ,$ordenCompra,$empresa, $datos[0], $datos[4], $datos[3], $datos[5], $datos[6]");
                            return $this->redirect(['index-contenedor']);
                        }
                        $subtotalFinal =  $subtotalFinal + $datos[6];

                        if(!$this->actualizarCostoFardoREGISTRO($datos[0], $contenedor, $fecha, $datos[1], $datos[2], $datos[5])){
                            Yii::$app->session->setFlash('error', "Error en actualizar el costo del fardo dentro de REGISTRO-- datos: ,$datos[0], $contenedor, $fecha, $datos[1], $datos[2], $datos[5]");
                            return $this->redirect(['index-contenedor']);
                        }
                    }
                    //$this->actualizarConsecutivoCompra();
                    $this->finalizarOrdenCompra($ordenCompra,$empresa,$subtotalFinal);
                    $this->actualizarRegistroFardoTRANSACCION($fecha, $contenedor, $ordenCompra);
                    $transaction->commit();
                    $transactionBodega->commit();
                    Yii::$app->session->setFlash('success', 'Contenedor finalizado con exito!');
                    return $this->redirect(['index-contenedor']);
                }catch (\Exception $e) {
                    $transaction->rollBack();
                    $transactionBodega->rollBack();
                    Yii::$app->session->setFlash('error', "Error al validar registros de GLOBALES_CO: " . $e->getMessage());
                    return $this->redirect(['index-contenedor']);
                }
            }
        }else{
            var_dump( $empresa);
            Yii::$app->session->setFlash('error', "Error al crear contenedor!.'Empresa: $empresa'");
            return $this->redirect(['index-contenedor']);
        }
    }
        

    /**
     * Crea un model dinamico para registrar informacion de contenedores
     * @return DynamicModel Un modelo dinamico
     */
    public function crearModelContenedor()
    {
        $contenedor = new DynamicModel([
            'fecha_creacion', 'contenedor', 'bodega'
        ]);
        $contenedor->setAttributeLabels(['fecha_creacion' => 'Fecha', 'contenedor' => 'Contenedor', 'bodega' => 'Bodega']);
        $contenedor->addRule(['fecha_creacion', 'contenedor', 'bodega'], 'required');

        return $contenedor;
    }

    /**
     * Crea un model dinamico para registrar informacion de los detalles de un contenedor
     * @return DynamicModel Un modelo dinamico
     */
    public function crearModelDetalleContenedor()
    {
        $detalleContenedor = new DynamicModel([
            'articulo', 'cantidad', 'peso'
        ]);

        $detalleContenedor->setAttributeLabels(['articulo' => 'Articulo', 'cantidad' => 'Cantidad', 'peso' => 'Peso']);
        $detalleContenedor->addRule(['articulo', 'cantidad', 'peso'], 'required');

        return $detalleContenedor;
    }

    /**
     * Crea un model dinamico para registrar, evaluar y calcular costeos y retaceos de un contenedor
     * @return DynamicModel Un modelo dinamico
     */
    public function crearModelValorContenedor()
    {
        $valorContenedor = new DynamicModel([
            'fecha', 'contenedor', 'gasto'
        ]);

        $valorContenedor->setAttributeLabels(['fecha' => 'Fecha de ingreso', 'contenedor' => 'Contenedor', 'gasto' => 'Gasto']);
        $valorContenedor->addRule(['fecha', 'contenedor', 'gasto'], 'required');

        return $valorContenedor;
    }

    /**
     * Obtiene las bodegas segun el esquema del usuario logeado
     * @return array contiene las bodegas encontradas
     */
    public function getBodegasByEsquema()
    {
        $bodegas = $this->SoftlandConn()->createCommand(
            "SELECT BODEGA, NOMBRE FROM " . $_SESSION['esquema'] . ".[BODEGA] 
            WHERE BODEGA LIKE '%00' OR BODEGA like 'SM%' "
        )->queryAll();

        foreach ($bodegas as $index => $bodega) {
            $bodegas[$index]["NOMBRE"] = $bodegas[$index]["BODEGA"] . ' - ' . $bodegas[$index]["NOMBRE"];
        }
        return $bodegas;
    }

    public function getBodegasByEsquema2($esquema)
    {
        $bodegas = $this->SoftlandConn()->createCommand(
            "SELECT BODEGA, NOMBRE FROM " . $esquema . ".BODEGA"
        )->queryAll();

        foreach ($bodegas as $index => $bodega) {
            $bodegas[$index]["NOMBRE"] = $bodegas[$index]["BODEGA"] . ' - ' . $bodegas[$index]["NOMBRE"];
        }
        return $bodegas;
    }

    //OBTENEMOS LOS NOMBRES DE LAS EMPRESAS
    public function getNombreEmpresa($esquema)
    {
        $empresa = $this->SoftlandConn()->createCommand(
            "SELECT NOMBRE FROM " . $esquema . ".BODEGA"
        )->queryAll();
        return $empresa;
    }

    public function getConjuntos(){
        $esquemas = $this->SoftlandConn()->createCommand(
            "SELECT CONJUNTO, NOMBRE FROM ERPADMIN.CONJUNTO")->queryAll();
        foreach ($esquemas as $index => $bodega) {
            $esquemas[$index]["NOMBRE"] = $esquemas[$index]["CONJUNTO"] . ' - ' . $esquemas[$index]["NOMBRE"];
        }
        return $esquemas;
    }
    
    

    /**
     * Obtiene los articulos existentes en dependiendo del esuqema para fardos
     * @return array contiene los articulos encontrados
     */
    public function getArticulosByEsquema($esquema)
    {
        //obtenemos el nombre de la bd
        $esquemaDB = Yii::$app->esquema3->nombre;
        //Concatenamos la consulta de la bd a nuestro esquema
        $esquemaCompleto =$esquemaDB.".".$esquema;  
        //Se realiza la consulta apuntando al esquema completo
        $articulos = $this->SoftlandConn()->createCommand(
            "SELECT ARTICULO, DESCRIPCION, CLASIFICACION_1 AS ARTICULO_DESCRIPCION 
            FROM $esquemaCompleto.[ARTICULO] 
            WHERE (ARTICULO like 'F%') or (ARTICULO LIKE '%BA%')
            and activo='S'
            order by ARTICULO"
        )->queryAll();
        foreach ($articulos as $index => $articulo) {
            $articulos[$index]["ARTICULO_DESCRIPCION"] = $articulo['ARTICULO'] . ' - ' . $articulo["DESCRIPCION"];
        }
        return $articulos;
    }

    /**
     * Permite evaluar si un contenedor esta en estado FINALIZADO
     * @param string $fecha la fecha del contenedor a buscar
     * @param string $contenedor el nombre o codigo del contenedor
     * @return int la cantidad de contenedores encontrados
     */
    public function validarExistenciaContenedor($fecha, $contenedor)
    {
        $contenedorExiste = Yii::$app->db->createCommand(
            "SELECT COUNT(*) AS Contador FROM [BODEGA].[dbo].[TRANSACCION] 
            WHERE IdTipoTransaccion = 1
            AND Fecha = '$fecha'
            AND Estado = 'F'
            AND NumeroDocumento='$contenedor'"
        )->queryOne();
        return $contenedorExiste["Contador"];
    }

    /**
     * Obtiene todo el registro de un articulo
     * @return array la informacion del articulo encontrado
     */
    public function obtenerInformacionArticulo($articulo, $empresa)
    {
        $informacionArticulo = $this->SoftlandConn()->createCommand(
            "SELECT DESCRIPCION, CLASIFICACION_2 
            FROM $empresa.ARTICULO 
            WHERE (articulo LIKE '" . $articulo . "')
            and activo = 'S' "
        )->queryOne();

        return $informacionArticulo;
    }

    /**
     * Crea un registro en la tabla [BODEGA].[dbo].[REGISTRO] acerca de un fardo de contenedor
     * @param string $codigoBarra un codigo de barra nuevo para el nuevo registro
     * @param string $articulo el articulo al que pertenece el fardo de contenedor
     * @param string $libras la cantidad de libras que posee el fardo de contenedor
     * @param string $bodega la bodega donde quedara el fardo de contenedor
     * @param string $contenedor el contenedor de origen del fardo
     * @param string $session la cantidad de registros por dia
     */
    public function crearRegistroREGISTRODetalleContenedor($codigoBarra, $articulo, $libras, $bodega, $contenedor, $session, $fecha, $empresa)
    {
        $informacionArticulo = $this->obtenerInformacionArticulo($articulo, $empresa);
        Yii::$app->db->createCommand(
            "INSERT INTO REGISTRO
            (
                CodigoBarra, Articulo, Descripcion, Clasificacion, Libras, IdTipoEmpaque,
                IdUbicacion, BodegaCreacion, BodegaActual, UsuarioCreacion, DOCUMENTO_INV, 
                Estado, Activo, FechaCreacion, Sesion, IdTipoRegistro, Empresa
            ) VALUES 
            (
                '$codigoBarra', '$articulo', '" . $informacionArticulo["DESCRIPCION"] . "', '" . $informacionArticulo["CLASIFICACION_2"] . "', $libras, 6, 
                1, '$bodega', '$bodega', '" . Yii::$app->session->get('user') . "', '$contenedor', 
                'PROCESO', 1, '$fecha', $session, 2, '$empresa'
            )"
        )->execute();
    }

    /**
     * Crea un registro en la tabla [BODEGA].[dbo].[TRANSACCION] acerca de un fardo de contenedor
     * @param string $codigoBarra un codigo de barra nuevo para el nuevo registro
     * @param string $bodega la bodega donde quedara el fardo de contenedor
     * @param string $contenedor el contenedor de origen del fardo
     */
    public function crearRegistroTRANSACCIONDetalleContenedor($codigoBarra, $bodega, $contenedor, $fecha)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO TRANSACCION    
            (
                CodigoBarra, IdTipoTransaccion, Fecha, Bodega, Naturaleza, Estado, UsuarioCreacion, NumeroDocumento
            ) VALUES 
            (
                '$codigoBarra', 1, '$fecha', '$bodega', 'E', 'P', '" . Yii::$app->session->get('user') . "', '$contenedor'
            )"
        )->execute();;
    }

    /**
     * Obtiene todos los fardos de contenedor de un solo contenedor
     * @param string $bodega la bodega donde se despacho el contenedor
     * @param string $contenedor el nombre o codigo del contenedor 
     * @param string $fecha la fecha de creacion del contenedor
     * @return array todos los registros de fardos asociados al contenedor
     */
    public function obtenerDataContenedor($contenedor, $fecha, $bodega)
    {
        $esquema = Yii::$app->esquema0->nombre;
        $data = Yii::$app->db->createCommand(
            "SELECT Articulo,Empresa, Descripcion, Clasificacion, Libras, count(CodigoBarra) as Cantidad, FechaCreacion, MAX(CreateDate)
            FROM $esquema.[dbo].[REGISTRO] 
            WHERE IdTipoRegistro = 2 
            AND DOCUMENTO_INV = '$contenedor' 
            AND FechaCreacion = '$fecha' 
            AND BodegaActual = '$bodega' 
            GROUP BY Articulo,Empresa, Descripcion, Clasificacion, Libras, FechaCreacion, DOCUMENTO_INV, BodegaActual
            ORDER BY MAX(CreateDate) DESC"
        )->queryAll();

        return $data;
    }

    /**
     * Obtiene un conjunto de codigos de barra asociados a un contenedor y articulo
     * @param string $articulo el articulo al que pertenecen los registros
     * @param string $libras la cantidad de libras que poseen los registros
     * @param string $fecha la fecha del registro
     * @param string $contenedor el nombre del contenedor al que pertenecen los registros
     * @param string $bodega la bodega a la que pertenecen los registros
     * @return array contiene todos los codigos de barra asociados
     */
    public function obtenerCodigosBarraBorrarDetalleContenedor($articulo, $libras, $fecha, $contenedor, $bodega)
    {
        $codigosBarra = Yii::$app->db->createCommand(
            "SELECT CodigoBarra 
            FROM [BODEGA].[dbo].[REGISTRO] 
            WHERE Articulo = '$articulo' 
            AND IdTipoEmpaque = 6
            AND IdTipoRegistro = 2 
            AND Libras = $libras
            AND Estado = 'PROCESO' 
            AND FechaCreacion = '$fecha'
            AND BodegaActual = '$bodega' 
            AND DOCUMENTO_INV = '$contenedor'"
        )->queryAll();

        return $codigosBarra;
    }

    /**
     * Borra un registro de la tabla [BODEGA].[dbo].[TRANSACCION] que representa un fardo de contenedor
     * @param string $codigoBarra un codigo de barra asociado al registro
     * @param string $fecha la fecha del registro
     * @param string $contenedor el contenedor de origen del registro
     */
    public function borrarRegistroTRANSACCIONDetalleContenedor($codigoBarra, $fecha, $contenedor)
    {
        Yii::$app->db->createCommand(
            "DELETE FROM [TRANSACCION] 
            WHERE NumeroDocumento = '$contenedor' 
            AND Fecha = '$fecha' 
            AND IdTipoTransaccion = 1 
            AND Naturaleza = 'E'
            AND Estado = 'P'
            AND CodigoBarra= '$codigoBarra' "
        )->execute();
    }

    /**
     * Borra un registro de la tabla [BODEGA].[dbo].[REGISTRO] que representa un fardo de contenedor
     * @param string $codigoBarra un codigo de barra asociado al registro
     * @param string $articulo el articulo al que pertenece el fardo de contenedor
     * @param string $libras la cantidad de libras que posee el fardo de contenedor
     * @param string $fecha la fecha del registro
     * @param string $contenedor el contenedor de origen del fardo
     * @param string $bodega la bodega donde esta el fardo
     */
    public function borrarRegistroREGISTRODetalleContenedor($codigoBarra, $articulo, $libras, $fecha, $contenedor, $bodega)
    {
        Yii::$app->db->createCommand(
            "DELETE FROM [BODEGA].[dbo].[REGISTRO] 
            WHERE CodigoBarra = '$codigoBarra' 
            AND Articulo = '$articulo' 
            AND IdTipoEmpaque = 6 
            AND FechaCreacion = '$fecha' 
            AND Libras = '$libras' 
            AND BodegaActual = '$bodega' 
            AND DOCUMENTO_INV = '$contenedor'"
        )->execute();
    }

    /**
     * Obtiene los detalles de cada fardo de un contenedor para calcular un retaceo
     * @param string $contenedor el contenedor al que pertenecen los fardos
     * @param string $fecha la fecha de registro del contenedor
     * @return array todos los fardos del contenedor buscado
     */
    public function obtenerDetalleValorContenedor($contenedor, $fecha)
    {
        $detalleValorContenedor = Yii::$app->db->createCommand(
            "SELECT Articulo, Empresa, BodegaCreacion, Descripcion, SUM(Libras) as Libras, count(CodigoBarra) as Cantidad 
            FROM [BODEGA].[dbo].[REGISTRO]  
            WHERE IdTipoRegistro = 2 
            AND DOCUMENTO_INV = '$contenedor' 
            AND FechaCreacion = '$fecha'
            GROUP BY Articulo, Empresa, Descripcion, Libras, BodegaCreacion
            ORDER BY SUM(Libras)"
        )->queryAll();

        return $detalleValorContenedor;
    }

    /**
     * Valida si existe un registro contable en la tabla [EXIMP600].[NEWYORK].[GLOBALES_CO], si no existe nos retorna al index de contenedores
     */
    public function validarGlobalesCO($empresa)
    {
        try {
            $ORDEN = $this->SoftlandConn()->createCommand(
                "SELECT COUNT(*) as contador FROM [$empresa].[GLOBALES_CO]"
            )->queryOne();
    
            if ($ORDEN["contador"] == 0) {
                Yii::$app->session->setFlash('error', "No existe orden de compra!");
                return false; // No se encontraron registros
            }
    
            return true; // Se encontraron registros
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', "Error al validar registros de GLOBALES_CO: " . $e->getMessage());
            return false; // Error de consulta
        }
    }

    /**
     * Valida si existe el proveedor EX0002 en la tabla [EXIMP600].[NEWYORK].[PROVEEDOR], si no existe nos retorna al index de contenedores
     */
    public function validarProveedorExiste($empresa)
    {
        try {
            $proveedor = $this->SoftlandConn()->createCommand(
                "SELECT COUNT(PROVEEDOR) as proveedor 
                FROM $empresa.[PROVEEDOR] 
                WHERE PROVEEDOR = 'EX0002'"
            )->queryOne();
    
            if ($proveedor["proveedor"] == 0) {
                Yii::$app->session->setFlash('danger', "No existe el proveedor proporcionado!");
                return false; // El proveedor no existe
            }
    
            return true; // El proveedor existe
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', "Error al validar la existencia del proveedor: " . $e->getMessage());
            return false; // Error de consulta
        }
        //$esquema = Yii::$app->esquema1->nombre;
        // $proveedor = $this->SoftlandConn()->createCommand(
        //     "SELECT COUNT(PROVEEDOR) as proveedor 
        //     FROM [PRUEBAS].[CNYCENTER].[PROVEEDOR] 
        //     WHERE PROVEEDOR = 'EX0002'"
        // )->queryOne();

        // $proveedor = $this->SoftlandConn()->createCommand(
        //     "SELECT COUNT(PROVEEDOR) as proveedor 
        //     FROM $empresa.[PROVEEDOR] 
        //     WHERE PROVEEDOR = 'EX0002'"
        // )->queryOne();

        // if ($proveedor["proveedor"] == 0) {
        //     Yii::$app->session->setFlash('danger', "No existe el proveedor proporcionado!");
        //     return $this->redirect('index-contenedor');
        // }
    }

    /**
     * Valida si el articulo proporcionado pertenece al proveedor existente, si no existe nos retorna al index de contenedores
     * @param string $articulo el articulo enviado desde el retaceo
     */
    public function validarArticuloProveedor($articulo, $empresa, $descripcion)
    {
        $transaction = Yii::$app->db2->beginTransaction(); // Iniciar transacción
        try{
            $articulo_pertenece_proveedor = Yii::$app->db2->createCommand(
                "SELECT COUNT(ARTICULO) AS articulo 
                FROM $empresa.[ARTICULO_PROVEEDOR]
                WHERE ARTICULO = '$articulo'"
            )->queryOne();

            if ($articulo_pertenece_proveedor['articulo'] == 0) {
                Yii::$app->db2->createCommand(
                "INSERT INTO $empresa.ARTICULO_PROVEEDOR 
                (
                    ARTICULO, PROVEEDOR, CODIGO_CATALOGO, LOTE_MINIMO, LOTE_ESTANDAR, PESO_MINIMO_ORDEN, MULTIPLO_COMPRA, CANT_ECONOMICA_COM, UNIDAD_MEDIDA_COMP, FACTOR_CONVERSION, PLAZO_REABASTECIMI, PORC_AJUSTE_COSTO, DESCRIP_CATALOGO, PAIS, TIPO, NoteExistsFlag
                )
                VALUES (
                    '$articulo', 'EX0002', '$articulo', 1, 1, 1, 1, 1, 'UND', 1, 1, 0, '$descripcion', 'USA', 'P', '0'
                )"
                )->execute();
            }
            $transaction->commit(); // Confirmar la transacción

            return true;
        }catch(\Exception $e){
            $transaction->rollBack(); // Rollback en caso de error
            Yii::$app->session->setFlash('error', "Error al validar la existencia del articulo del proveedor: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Genera un nuevo codigo de orden de compra a partir del registro anterior
     * @return string el nuevo codigo de orden de compra
     */
    public function generarCodigoUltimaOrdenCompra($empresa)
    {
        $ULT_ORDEN_COMPRA = $this->SoftlandConn()->createCommand(
            "SELECT ULT_ORDEN_COMPRA 
            FROM $empresa.[GLOBALES_CO]"
        )->queryOne();

        $ULT_ORDEN = substr($ULT_ORDEN_COMPRA['ULT_ORDEN_COMPRA'], 2);
        $ULT_ORDEN_NUM  = intval($ULT_ORDEN);

        $NEW_ORDEN_COMPRA = '';

        if ($ULT_ORDEN_NUM >= 0 && $ULT_ORDEN_NUM < 9) {
            $NEW_ORDEN_COMPRA = 'CO000000' . ($ULT_ORDEN_NUM + 1);
        } else if ($ULT_ORDEN_NUM >= 9 && $ULT_ORDEN_NUM < 99) {
            $NEW_ORDEN_COMPRA = 'CO00000' . ($ULT_ORDEN_NUM + 1);
        } else if ($ULT_ORDEN_NUM >= 99 && $ULT_ORDEN_NUM < 999) {
            $NEW_ORDEN_COMPRA = 'CO0000' . ($ULT_ORDEN_NUM + 1);
        } else if ($ULT_ORDEN_NUM >= 999 && $ULT_ORDEN_NUM < 9999) {
            $NEW_ORDEN_COMPRA = 'CO000' . ($ULT_ORDEN_NUM + 1);
        } else if ($ULT_ORDEN_NUM >= 9999 && $ULT_ORDEN_NUM < 99999) {
            $NEW_ORDEN_COMPRA = 'CO00' . ($ULT_ORDEN_NUM + 1);
        } else if ($ULT_ORDEN_NUM >= 99999 && $ULT_ORDEN_NUM < 999999) {
            $NEW_ORDEN_COMPRA = 'CO0' . ($ULT_ORDEN_NUM + 1);
        } else if ($ULT_ORDEN_NUM >= 999999 && $ULT_ORDEN_NUM < 9999999) {
            $NEW_ORDEN_COMPRA = 'CO' . ($ULT_ORDEN_NUM + 1);
        }

        return $NEW_ORDEN_COMPRA;
    }

    /**
     * Genera un nueva orden de compra 
     * @param string $ordenCompra el codigo de la nueva y ultima orden de compra
     */
    public function generarUltimaOrdenCompra($ordenCompra, $empresa, $bodega)
    {
        $transaction = Yii::$app->db2->beginTransaction();
        try{

        $insertado = $this->SoftlandConn()->createCommand(
            "INSERT INTO $empresa.[ORDEN_COMPRA] 
            (
                [ORDEN_COMPRA], [USUARIO], [PROVEEDOR], [BODEGA], [CONDICION_PAGO], [MONEDA], [PAIS], 
                [MODULO_ORIGEN], [FECHA], [FECHA_COTIZACION], [FECHA_OFRECIDA], [FECHA_REQ_EMBARQUE], 
                [FECHA_REQUERIDA], [TIPO_DESCUENTO], [PORC_DESCUENTO], [MONTO_DESCUENTO], [TOTAL_MERCADERIA], [TOTAL_IMPUESTO1], 
                [TOTAL_IMPUESTO2], [MONTO_FLETE], [MONTO_SEGURO], [MONTO_DOCUMENTACIO], [MONTO_ANTICIPO], [TOTAL_A_COMPRAR], 
                [PRIORIDAD], [ESTADO], [IMPRESA], [FECHA_HORA], [REQUIERE_CONFIRMA], [CONFIRMADA], [ORDEN_PROGRAMADA], 
                [RECIBIDO_DE_MAS], [CreatedBy], [UpdatedBy]
            ) 
            VALUES 
            (
                '$ordenCompra', '" . Yii::$app->session->get('user') . "', 'EX0002', '$bodega', '2', 'DOL', 'ESA',
                'CO', '" . date("Y-m-d H:i:s") . "', '" . date("Y-m-d H:i:s") . "', '" . date("Y-m-d H:i:s") . "', '" . date("Y-m-d H:i:s") . "', 
                '" . date("Y-m-d H:i:s") . "',' ', 0, 0, 0, 0,
                0, 0, 0, 0, 0, 0,
                'M', 'A', 'N', '" . date("Y-m-d H:i:s") . "', 'S', 'N', 'P',
                'N', 'sa', 'sa'
            )"
        )->execute();
        
        if (!$insertado) {
            throw new \Exception('Error al insertar la orden de compra.');
        }
        $this->SoftlandConn()->createCommand(
            "UPDATE $empresa.[GLOBALES_CO] 
            SET [ULT_ORDEN_COMPRA]='" . $ordenCompra . "'"
        )->execute();
        $transaction->commit();
        return true; // Devuelve true si se insertó correctamente
    }catch(\Exception $e) {
        $transaction->rollBack();
        Yii::error($e->getMessage());
        return false;
    }
    }

    /**
     * Obtener el numero de registros de una orden compra, para luego aumentarlos en 1
     * @return int el nuevo numero de registros de una orden de compra
     */
    public function obtenerOrdenCompraLinea($empresa)
    {
        $ULT_ORDEN_COMPRA = $this->SoftlandConn()->createCommand(
            "SELECT ULT_ORDEN_COMPRA from $empresa.[GLOBALES_CO]"
        )->queryOne();

        $ultimaLinea = $this->SoftlandConn()->createCommand(
            "SELECT COUNT(ORDEN_COMPRA_LINEA) AS linea
            FROM $empresa.[ORDEN_COMPRA_LINEA] 
            WHERE ORDEN_COMPRA = '" . $ULT_ORDEN_COMPRA['ULT_ORDEN_COMPRA'] . "'"
        )->queryOne();

        return $ultimaLinea['linea'] +  1;
    }

    /**
     * Genera una linea de orden de compra
     * @param string $ordenCompra la orden de compra asociada a la linea de orden de compra
     * @param string $articulo el articulo enviado desde el retaceo
     * @param string $bodega la bodega enviada desde el retaceo
     * @param string $descripcion la descripcion del articulo enviado
     * @param string $cantidad la cantidad de fardos enviados por articulo
     * @param string subtotalBase el subtotal del articulo antes del retaceo
     */
    public function generarLineaOrdenCompra($ordenCompra,$empresa, $articulo, $bodega, $descripcion, $cantidad, $subtotalBase)
    {
        $cuentas = $this->obtenerCuentasContablesLineaOrden($articulo, $empresa);
        $transaction = Yii::$app->db2->beginTransaction();
        try{
            $this->SoftlandConn()->createCommand(
                "INSERT INTO [$empresa].[ORDEN_COMPRA_LINEA] 
                (
                    [ORDEN_COMPRA], [ORDEN_COMPRA_LINEA], [ARTICULO], [BODEGA], 
                    [LINEA_USUARIO], [DESCRIPCION], [CANTIDAD_ORDENADA], [CANTIDAD_EMBARCADA], [CANTIDAD_RECIBIDA], [CANTIDAD_RECHAZADA], 
                    [PRECIO_UNITARIO], [IMPUESTO1], [IMPUESTO2], [TIPO_DESCUENTO], [PORC_DESCUENTO], [MONTO_DESCUENTO], 
                    [FECHA], [ESTADO], [FACTOR_CONVERSION], [FECHA_REQUERIDA], [DIAS_PARA_ENTREGA], [CANTIDAD_ACEPTADA], 
                    [IMP2_POR_CANTIDAD], [IMP1_AFECTA_COSTO], [IMP1_ASUMIDO_DESC], [IMP1_ASUMIDO_NODESC], [IMP1_RETENIDO_DESC], [IMP1_RETENIDO_NODESC], 
                    [PRECIO_ART_PROV], [PORC_EXONERACION], [MONTO_EXONERACION], [ES_CANASTA_BASICA], [SUBTOTAL_BIENES], [SUBTOTAL_SERVICIOS], 
                    [IMP1_POR_CANTIDAD], [CreatedBy], [UpdatedBy]
                ) 
                VALUES
                (
                    '" . $ordenCompra . "', " . $this->obtenerOrdenCompraLinea($empresa) . ", '" . $articulo . "', '" . $bodega . "', 
                    " . $this->obtenerOrdenCompraLinea($empresa) . ", '" . $descripcion . "', " . $cantidad . ", 0, 0, 0,
                    " . ($subtotalBase / $cantidad) . ", 0, 0, 'P', 0, 0,
                    '" . date("Y-m-d H:i:s") . "', 'A', 1, '" . date("Y-m-d H:i:s") . "', 0, 0,
                    'N', 'N', 0, 0, 0, 0,
                    " . ($subtotalBase / $cantidad) . ", 0, 0, 'N', 0, 0,
                    'N', '" . Yii::$app->session->get('user') . "', '" . Yii::$app->session->get('user') . "'
                )
                "
            )->execute();
            $transaction->commit();
            return true;
        }catch(\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            return false;
        }
        
    }

    /**
     * Obtiene las cuentas contables a las que pertenecen los articulos 
     * @param string $articulo el articulo enviado desde retaceo
     * @return array la cuenta contable a la que pertenece el articulo
     */
    public function obtenerCuentasContablesLineaOrden($articulo, $empresa)
    {
        $cuentas = $this->SoftlandConn()->createCommand(
            "SELECT ARTICULO.ARTICULO, ARTICULO.DESCRIPCION, 
            ARTICULO_CUENTA.CTR_INVENTARIO, ARTICULO_CUENTA.CTA_INVENTARIO
            FROM $empresa.ARTICULO 
            INNER JOIN $empresa.ARTICULO_CUENTA 
            ON ARTICULO.ARTICULO_CUENTA = ARTICULO_CUENTA.ARTICULO_CUENTA
            WHERE ARTICULO.ARTICULO = '" . $articulo . "'"
        )->queryOne();
        return $cuentas;
    }

    /**
     * Finaliza la orden de compra actual, estableciendole un total de costo de fardos
     * @param $ordenCompra la orden de compra que se trabajo
     * @param $total el total de costo de los fardos SIN CONSIDERAR GASTO
     */
    public function finalizarOrdenCompra($ordenCompra,$empresa, $total)
    {
        $this->SoftlandConn()->createCommand(
            "UPDATE [$empresa].[ORDEN_COMPRA] 
            SET TOTAL_MERCADERIA = $total, TOTAL_A_COMPRAR = $total 
            WHERE ORDEN_COMPRA = '" . $ordenCompra . "'"
        )->execute();
    }

    /**
     * Obtiene el consecutivo actual de la tabla [SOFTLAND].[CONINV].[CONSECUTIVO_CI]
     * @return string el consecutivo actual para trabajar
     */
    // public function obtenerConsecutivo()
    // {
    //     $getConsecutivo =  $this->SoftlandConn()->createCommand(
    //         "SELECT SIGUIENTE_CONSEC 
    //         FROM [PRUEBAS].[CONINV].[CONSECUTIVO_CI] WHERE CONSECUTIVO = 'COMPRA'"
    //     )->queryOne();

    //     return $getConsecutivo['SIGUIENTE_CONSEC'];
    // }

    /**
     * Crea un nuevo documento de inventario en la tabla [SOFTLAND].[CONINV].[DOCUMENTO_INV] 
     * @param string $consecutivo el consecutivo actual
     */
    // public function crearDocumentoInv($consecutivo)
    // {
    //     $this->SoftlandConn()->createCommand("INSERT INTO [PRUEBAS].[CONINV].[DOCUMENTO_INV] 
    //     (
    //         [PAQUETE_INVENTARIO], [DOCUMENTO_INV], [CONSECUTIVO], [REFERENCIA], 
    //         [FECHA_HOR_CREACION], [FECHA_DOCUMENTO], [SELECCIONADO], [USUARIO], [APROBADO]
    //     )
    //     VALUES
    //     (
    //         '" . Yii::$app->session->get('paquete') . "', '" . $consecutivo . "', 'COMPRA', 'Compra del dia " .  date("Y-m-d") . "',
    //         '" . date("Y-m-d H:i:s") . "', '" . date("Y-m-d H:i:s") . "', 'N', '" . Yii::$app->session->get('user') . "', 'N'
    //     )")->execute();
    // }

    /**
     * Obtiene un conteo de las lineas de un documento de inventario
     * @param string @consecutivo el documento de inventario
     * @return int el conteo de las lineas mas uno
     */
    // public function obtenerLineaDocInv($consecutivo)
    // {
    //     $ultimaLinea = $this->SoftlandConn()->createCommand("SELECT COUNT(LINEA_DOC_INV) AS linea
    //     FROM [PRUEBAS].[CONINV].[LINEA_DOC_INV] 
    //     WHERE DOCUMENTO_INV = '" . $consecutivo . "'")->queryOne();

    //     return $ultimaLinea['linea'] + 1;
    // }

    /**
     * Crea una linea de documento de inventario
     * @param string $consecutivo el consecutivo actual
     * @param string $articulo el articulo enviado desde el retaceo
     * @param string $bodega la bodega donde se encuentra el fardo
     * @param string $cantidad la cantidad de fardos por articulo
     * @param string $precioUnitario el precio por libra de fardo
     * @param string $libras la cantidad de libras
     */
    // public function crearLineaDocInv($consecutivo, $articulo, $bodega, $cantidad, $precioUnitario, $libras)
    // {
    //     $this->SoftlandConn()->createCommand(
    //         "INSERT INTO [PRUEBAS].[CONINV].[LINEA_DOC_INV] 
    //         (
    //             [PAQUETE_INVENTARIO], [DOCUMENTO_INV], [LINEA_DOC_INV], 
    //             [AJUSTE_CONFIG], [ARTICULO], [BODEGA], [TIPO], [SUBTIPO], [SUBSUBTIPO], 
    //             [CANTIDAD], [COSTO_TOTAL_LOCAL], [COSTO_TOTAL_DOLAR], 
    //             [PRECIO_TOTAL_LOCAL], [PRECIO_TOTAL_DOLAR], [COSTO_TOTAL_LOCAL_COMP], [COSTO_TOTAL_DOLAR_COMP]
    //         )
    //         VALUES
    //         (
    //             '" . Yii::$app->session->get('paquete') . "', '" . $consecutivo . "', " . $this->obtenerLineaDocInv($consecutivo) . ",
    //             '~OO~', '" . $articulo . "', '" . $bodega . "', 'O', 'D', 'L',
    //             " . $cantidad . ", " . ($precioUnitario * $libras) . ", " . ($precioUnitario * $libras) . ",
    //             0, 0, 0, 0
    //         )"
    //     )->execute();
    // }

    /**
     * Actualiza el costo de todos los fardos asociados a un articulo y libras especificos
     * @param string $articulo el articulo enviado desde el retaceo
     * @param string $contenedor el contenedor que se esta trabajando desde retaceo
     * @param string $fecha la fecha del contenedor
     * @param string $precioUnitario el precio de cada libra 
     * @param string $libras la cantidad de libras enviadas desde el retaceo
     * @param string $cantidadFardos la cantidad de fardos trabajados por articulo
     */
    public function actualizarCostoFardoREGISTRO($articulo, $contenedor, $fecha, $precioUnitario, $libras, $cantidadFardos)
    {
        $transaction = yii::$app->db->beginTransaction();
        try{
            Yii::$app->db->createCommand(
                "UPDATE REGISTRO
                SET costo = " . (($precioUnitario * $libras) / $cantidadFardos) . ", Estado = 'FINALIZADO'
                WHERE DOCUMENTO_INV = '" . $contenedor . "' 
                AND FechaCreacion= '" . $fecha . "' 
                AND Libras = " . ($libras / $cantidadFardos) . "
                AND Articulo = '" . $articulo . "'"
            )->execute();
            $transaction->commit();
            return true;
        }catch(\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el siguiente consecutivo que estara disponible para trabajarse
     * @return string el siguiente consecutivo disponible
     */
    // public function crearSiguienteConsecutivo()
    // {

    //     $getConsecutivoCode =  $this->SoftlandConn()->createCommand("SELECT CONSECUTIVO, SIGUIENTE_CONSEC 
    //     FROM [PRUEBAS].[CONINV].[CONSECUTIVO_CI] WHERE CONSECUTIVO = 'COMPRA'")->queryOne();

    //     $consecutivoCode = explode("-", $getConsecutivoCode['SIGUIENTE_CONSEC']);
    //     $ultimoConsecutivoCode  = intval($consecutivoCode[1]);
    //     $newConsecutivo = $consecutivoCode[0] . '-';

    //     if ($ultimoConsecutivoCode >= 0 && $ultimoConsecutivoCode < 9) {
    //         $newConsecutivo = $newConsecutivo . '000000' . ($ultimoConsecutivoCode + 1);
    //     } else if ($ultimoConsecutivoCode >= 9 && $ultimoConsecutivoCode < 99) {
    //         $newConsecutivo = $newConsecutivo . '00000' . ($ultimoConsecutivoCode + 1);
    //     } else if ($ultimoConsecutivoCode >= 99 && $ultimoConsecutivoCode < 999) {
    //         $newConsecutivo = $newConsecutivo . '0000' . ($ultimoConsecutivoCode + 1);
    //     } else if ($ultimoConsecutivoCode >= 999 && $ultimoConsecutivoCode < 9999) {
    //         $newConsecutivo = $newConsecutivo . '000' . ($ultimoConsecutivoCode + 1);
    //     } else if ($ultimoConsecutivoCode >= 9999 && $ultimoConsecutivoCode < 99999) {
    //         $newConsecutivo = $newConsecutivo . '00' . ($ultimoConsecutivoCode + 1);
    //     } else if ($ultimoConsecutivoCode >= 99999 && $ultimoConsecutivoCode < 999999) {
    //         $newConsecutivo = $newConsecutivo . '0' . ($ultimoConsecutivoCode + 1);
    //     } else if ($ultimoConsecutivoCode >= 999999 && $ultimoConsecutivoCode < 9999999) {
    //         $newConsecutivo = $newConsecutivo . ($ultimoConsecutivoCode + 1);
    //     }
    //     return $newConsecutivo;
    // }

    /**
     * Actualiza la tabla [SOFTLAND].[CONINV].[CONSECUTIVO_CI] con un consecutivo disponible para trabajar
     */
    // public function actualizarConsecutivoCompra()
    // {
    //     $this->SoftlandConn()->createCommand(
    //         "UPDATE [PRUEBAS].[CONINV].[CONSECUTIVO_CI] 
    //         SET SIGUIENTE_CONSEC = '" . $this->crearSiguienteConsecutivo() . "' WHERE CONSECUTIVO = 'COMPRA'"
    //     )->execute();
    // }

    /**
     * Finaliza todos los registros que pertenecen al contenedor y los pone a disposicion para otro proceso
     * @param string $fecha la fecha del contenedor
     * @param string $contenedor el nombre o codigo del contenedor 
     */
    public function actualizarRegistroFardoTRANSACCION($fecha, $contenedor, $ordenCompra)
    {
        Yii::$app->db->createCommand(
            "UPDATE TRANSACCION SET Estado = 'F', Documento_inv = '$ordenCompra' 
            WHERE Fecha = '" . $fecha . "' 
            AND NumeroDocumento = '" . $contenedor . "'"
        )->execute();
    }

    /**
     * Funcion dedicada solamente a la creacion dinamica y unica de codigos de barra de manera aleatoria
     * @param string $fecha la fecha del dia
     * @return string el nuevo codigo de barra
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
        $letraAleatoria2 = chr(rand(ord($DesdeLetra), ord($HastaLetra)));
        $letraMayuscula2 = strtoupper($letraAleatoria2);

        $devuelve = $this->getSessionDataRegisterDay($fecha) + 1;
        if ($devuelve >= 0 && $devuelve < 10) {
            $numero = "00$devuelve";
        } else if ($devuelve > 9 && $devuelve < 100) {
            $numero = "0$devuelve";
        } else {
            $numero = $devuelve;
        }

        $codigoBarra = ("" . $letraMayuscula . "" . $letraMayuscula2 . $dia . "" . $mes . "" . $numero . "" . $anio
        );

        return $codigoBarra;
    }

    /**
     * Obtiene la cantidad de registros de tipo contenedor realizados en el dia
     * @param string $fecha la fecha del dia o de algun registro
     * @return string la cantidad de registros
     */
    public function getSessionDataRegisterDay($fecha)
    {
        $sesionDataDay = Yii::$app->db->createCommand(
            "SELECT ISNULL(MAX(Sesion), 0) AS maximo 
            FROM REGISTRO 
            WHERE FechaCreacion = '" . $fecha . "' AND IdTipoRegistro = 2"
        )->queryOne();

        return $sesionDataDay['maximo'];
    }

    /**
     * Funcion generica, solo imprime un array de manera bonita, solamente usada para analizar y depurar codigo
     * @param string $datos el array que se desea imprimir
     */
    public function printArrays($datos)
    {
        echo "<pre>";
        print_r($datos);
        echo "</pre>";
        die;
    }
}
