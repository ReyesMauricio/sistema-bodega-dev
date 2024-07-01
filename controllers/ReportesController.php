<?php

namespace app\controllers;

use app\models\AsignacionClasificacion;
use app\models\DetalleRegistroModel;
use app\models\RegistroModel;
use app\models\TrabajoMesaModel;
use app\models\UsuarioModel;
use app\modelsSearch\RegistroModelSearch;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Table;
use yii\web\Controller;
use Yii;
use yii\base\DynamicModel;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;

use function PHPSTORM_META\type;

/**
 * DetalleRegistroController implements the CRUD actions for DetalleRegistroModel model.
 */
class ReportesController extends Controller
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
    public function SoftlandConn()
    {
        return Yii::$app->db2;
    }

    public function actionSumaArticulosProduccion()
    {
        if (!isset($_SESSION['user'])) {
            return $this->redirect(Url::to(Yii::$app->request->baseUrl . '/index.php?r=site/login'));
        }

        $searchModel = new RegistroModelSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, 'suma-articulos');
        return $this->render('suma-articulos-produccion', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionReporteBuscarCodigo()
    {
        $model = $this->crearModelReporte();
        return $this->render('form-reporte-buscar-codigo', [
            'model' => $model
        ]);
    }

    public function actionCrearReporteCodigoBarra($codigoBarra)
    {
        $codigoBarra = trim($codigoBarra);
        $model = $this->findRegistroModel($codigoBarra);
        if (!$model) {
            return $this->renderAjax('reporte-codigo-barra', [
                'error' => "<h1>No existe el codigo ingresado $codigoBarra</h1>"
            ]);
        }
        $detalle = DetalleRegistroModel::find()->where("IdRegistro = $model->IdRegistro")->all();
        foreach ($detalle as $d) {
            $clasificacion = $this->SoftlandConn()->createCommand("SELECT DESCRIPCION, CLASIFICACION_2 FROM CONINV.ARTICULO WHERE ARTICULO = '$d->ArticuloDetalle'")->queryOne();
            $d->ArticuloDetalle =  $d->ArticuloDetalle . " - " . $clasificacion["DESCRIPCION"] . ' - ' . $clasificacion["CLASIFICACION_2"];
        }

        return $this->renderAjax('reporte-codigo-barra', [
            'model' => $model,
            'detalle' => $detalle
        ]);
    }

    public function actionReporteProduccionPersona()
    {
        $model = $this->crearModelReporte();
        return $this->render('form-reporte-produccion-persona', [
            'model' => $model
        ]);
    }

    public function actionCrearReporteProduccionPersona($fechaInicio, $fechaFin)
    {
        $reporte = $this->obtenerProduccionPersona($fechaInicio, $fechaFin);
        return $this->renderAjax('reporte-produccion-persona', [
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'produccion' => $reporte
        ]);
    }

    public function actionCrearReporteProduccionPersonaExcel($fechaInicio, $fechaFin)
    {
        $reporte = $this->obtenerProduccionPersona($fechaInicio, $fechaFin);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('DETALLE');

        $headersResumen = ['Nombre', 'Unidades', 'Libras', 'Fardos creados'];
        $column = 'A';
        foreach ($headersResumen as $header) {
            $sheet->setCellValue($column . '1', $header);
            $column++;
        }
        $sheet->fromArray($reporte, null, 'A2');
        $sheet->setAutoFilter('A1:D' . (count($reporte) + 1));

        $sheet->setCellValue('F1', 'rango de fechas');
        $sheet->setCellValue('F2', date("F j, Y", strtotime($fechaInicio)) . " - " . date("F j, Y", strtotime($fechaFin)));

        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }

        $fillHeader = $this->estilosCabezeraReportes();
        $styleArray = $this->estilosContenidoReportes();

        $sheet->getStyle('A1:D1')->applyFromArray($fillHeader);
        $sheet->getStyle('A2:D' . (count($reporte) + 1))->applyFromArray($styleArray);

        $sheet->getStyle('F1')->applyFromArray($fillHeader);
        $sheet->getStyle('F2' . (count($reporte) + 1))->applyFromArray($styleArray);

        $writer = new Xlsx($spreadsheet);
        $filePath = "Informacion de produccion por persona";
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename=' . $filePath. '.xlsx');
        $writer->save('php://output');
        exit;
    }

    public function obtenerProduccionPersona($fechaInicio, $fechaFin)
    {
        $informacionPersonas = UsuarioModel::find()->orderBy(['Nombre' => SORT_ASC])->all();
        $reporte = [];
        $contador = 0;
        foreach ($informacionPersonas as $persona) {
            $produccionPersona = RegistroModel::find()
                ->select('SUM(Libras) as Libras, SUM(Unidades) as Unidades, COUNT(CodigoBarra) as ContadorImpresiones')
                ->where("(ProducidoPor LIKE '%$persona->Nombre%' OR EmpacadoPor LIKE '%$persona->Nombre%')
                        AND FechaCreacion BETWEEN '$fechaInicio' AND '$fechaFin'")
                ->one();

            if (!$produccionPersona->Unidades && !$produccionPersona->Libras) {
                continue;
            }
            $reporte[$contador] = [
                'nombre' => $persona->Nombre,
                'unidades' => $produccionPersona->Unidades,
                'libras' => $produccionPersona->Libras,
                'fardos' => $produccionPersona->ContadorImpresiones,
            ];
            $contador++;
        }
        sort($reporte);
        return $reporte;
    }

    public function actionReporteProduccionMesa()
    {
        $model = $this->crearModelReporte();
        return $this->render('form-reporte-produccion-mesa', [
            'model' => $model
        ]);
    }

    public function actionCrearReporteProduccionMesa($fechaInicio, $fechaFin, $mesa)
    {
        $produccion = RegistroModel::find()
            ->where("MesaOrigen = $mesa AND FechaCreacion BETWEEN '$fechaInicio' AND '$fechaFin'")
            ->sum('Libras');

        $produccionTotal = RegistroModel::find()
            ->where("MesaOrigen = $mesa AND FechaCreacion BETWEEN '$fechaInicio' AND '$fechaFin'")
            ->all();

        return $this->renderAjax('reporte-produccion-mesa', [
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'mesa' => $mesa,
            'libras' => $produccion,
            'produccion' => $produccionTotal,
        ]);
    }

    public function actionReporteBusquedaMultiple()
    {
        $model = $this->crearModelReporte();
        return $this->render('form-reporte-busqueda-multiple', [
            'model' => $model
        ]);
    }

    public function actionCrearReporteBusquedaMultiple()
    {
        $codigosBarra = $this->trimCodigosBarra($_POST['DynamicModel']['codigos']);
        $codigos = '';
        foreach ($codigosBarra as $index => $codigo) {
            if ($index == (count($codigosBarra) - 1)) {
                $codigos .= "'" . trim($codigosBarra[$index]) . "'";
                continue;
            }
            $codigos .= "'" . trim($codigosBarra[$index]) . "', ";
        }

        $detalles = Yii::$app->db->createCommand(
            "SELECT REGISTRO.CodigoBarra, REGISTRO.Articulo, REGISTRO.Descripcion, REGISTRO.Clasificacion,
            REGISTRO.Libras, REGISTRO.FechaCreacion, REGISTRO.UsuarioCreacion, REGISTRO.EmpresaDestino, REGISTRO.Observaciones, 
            CASE 
                WHEN DETALLEREGISTRO.ArticuloDetalle IS NULL
                THEN 'NO POSEE'
                ELSE DETALLEREGISTRO.ArticuloDetalle
            END AS ArticuloDetalle,
            CASE 
                WHEN CONINV.DESCRIPCION IS NULL
                THEN 'NO POSEE'
                ELSE CONINV.DESCRIPCION
            END AS DESCRIPCION,
            CASE 
                WHEN DETALLEREGISTRO.PrecioUnitario IS NULL
                THEN 0
                ELSE DETALLEREGISTRO.PrecioUnitario
            END AS PrecioUnitario,
            CASE
                WHEN DETALLEREGISTRO.Cantidad IS NULL
                THEN 0
                ELSE DETALLEREGISTRO.Cantidad
            END AS Cantidad,
            CASE
                WHEN DETALLEREGISTRO.PrecioUnitario * DETALLEREGISTRO.Cantidad IS NULL
                THEN 0
                ELSE DETALLEREGISTRO.PrecioUnitario * DETALLEREGISTRO.Cantidad
            END AS SUBTOTAL
            FROM BODEGA.dbo.REGISTRO LEFT OUTER JOIN
            BODEGA.dbo.DETALLEREGISTRO ON REGISTRO.IdRegistro = DETALLEREGISTRO.IdRegistro
            LEFT OUTER JOIN
            PRUEBAS.CONINV.ARTICULO CONINV ON DETALLEREGISTRO.ArticuloDetalle = CONINV.ARTICULO
            WHERE CodigoBarra IN ($codigos)
            ORDER BY CodigoBarra ASC"
        )->queryAll();

        $resumen = Yii::$app->db->createCommand(
            "SELECT R.IdRegistro, R.CodigoBarra, R.Articulo, R.Descripcion, R.Clasificacion, R.Libras,
            CASE 
                WHEN (SELECT SUM(Cantidad) FROM BODEGA.dbo.DETALLEREGISTRO DR WHERE DR.IdRegistro = R.IdRegistro) IS NULL
                THEN 0
                ELSE (SELECT SUM(Cantidad) FROM BODEGA.dbo.DETALLEREGISTRO DR WHERE DR.IdRegistro = R.IdRegistro)
            END AS Cantidad,
            CASE 
                WHEN (SELECT SUM(Cantidad * PrecioUnitario) FROM BODEGA.dbo.DETALLEREGISTRO DR WHERE DR.IdRegistro = R.IdRegistro) IS NULL
                THEN 0
                ELSE (SELECT SUM(Cantidad * PrecioUnitario) FROM BODEGA.dbo.DETALLEREGISTRO DR WHERE DR.IdRegistro = R.IdRegistro)
            END AS PrecioUnitario
            FROM BODEGA.dbo.REGISTRO R
            WHERE (R.CodigoBarra IN ($codigos))
            GROUP BY R.IdRegistro, R.CodigoBarra, R.Articulo, R.Descripcion, R.Clasificacion, R.Libras, R.Unidades
            ORDER BY R.CodigoBarra"
        )->queryAll();


        $count = Yii::$app->db->createCommand(
            "SELECT DISTINCT CodigoBarra 
            FROM BODEGA.dbo.REGISTRO
            WHERE CodigoBarra IN ($codigos)"
        )->queryAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('DETALLE');

        $headersResumen = ['Registro', 'CodigoBarra', 'Articulo', 'Descripcion', 'Clasificacion', 'Libras', 'Cantidad', 'Totalfinal'];
        $column = 'A';
        foreach ($headersResumen as $header) {
            $sheet->setCellValue($column . '1', $header);
            $column++;
        }
        $sheet->fromArray($resumen, null, 'A2');

        $sheet->setCellValue('J1', 'Cantidad de fardos');
        $sheet->setCellValue('J2', count($count));

        $headers = [
            'CodigoBarra', 'Articulo', 'Descripcion', 'Clasificacion', 'Libras', 'FechaCreacion',
            'UsuarioCreacion', 'EmpresaDestino', 'Observaciones', 'ArticuloDetalle', 'DescripcionDetalle', 'PrecioUnitario',
            'Cantidad', 'Total'
        ];
        $column2 = 'L';
        foreach ($headers as $header) {
            $sheet->setCellValue($column2 . '1', $header);
            $column2++;
        }

        $sheet->fromArray($detalles, null, 'L2');
        $sheet->setAutoFilter('A1:Y' . (count($resumen) + 1));

        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }

        $fillHeader =  $this->estilosCabezeraReportes();
        $styleArray = $this->estilosContenidoReportes();

        $sheet->getStyle('A1:H1')->applyFromArray($fillHeader);
        $sheet->getStyle('A2:H' . (count($resumen) + 1))->applyFromArray($styleArray);

        $sheet->getStyle('J1')->applyFromArray($fillHeader);
        $sheet->getStyle('J2')->applyFromArray($styleArray);

        $sheet->getStyle('L1:Y1')->applyFromArray($fillHeader);
        $sheet->getStyle('L2:Y' . (count($detalles) + 1))->applyFromArray($styleArray);

        $writer = new Xlsx($spreadsheet);
        $filePath = "Informacion de codigos de barra";
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename=' . $filePath. '.xlsx');
        $writer->save('php://output');
        exit;
    }

    public function actionReporteProduccionDia()
    {
        $model = $this->crearModelReporte();
        return $this->render('form-reporte-produccion-dia', [
            'model' => $model
        ]);
    }

    public function actionCrearReporteProduccionDiaExcel($fecha)
    {
        $this->crearReporteDetalleResumen($fecha);
    }

    public function crearReporteDetalleResumen($fecha)
    {
        $codigos = RegistroModel::find()->where("Estado NOT LIKE 'ELIMINADO' AND IdTipoRegistro = 1 AND FechaCreacion ='$fecha'")->orderBy(['CreateDate' => SORT_DESC])->all();

        $codigosBarra = '';
        foreach ($codigos as $index => $codigo) {
            if ($index == count($codigos) - 1) {
                $codigosBarra .= "'" . $codigo["CodigoBarra"] . "'";
                continue;
            }
            $codigosBarra .= "'" . $codigo["CodigoBarra"] . "',";
        }

        $detalles = Yii::$app->db->createCommand(
            "SELECT REGISTRO.CodigoBarra, REGISTRO.Articulo, REGISTRO.Descripcion, REGISTRO.Clasificacion,
            REGISTRO.Libras, REGISTRO.FechaCreacion, REGISTRO.UsuarioCreacion, REGISTRO.EmpresaDestino, REGISTRO.Observaciones, 
            CASE 
                WHEN DETALLEREGISTRO.ArticuloDetalle IS NULL
                THEN 'NO POSEE'
                ELSE DETALLEREGISTRO.ArticuloDetalle
            END AS ArticuloDetalle,
            CASE 
                WHEN CONINV.DESCRIPCION IS NULL
                THEN 'NO POSEE'
                ELSE CONINV.DESCRIPCION
            END AS DESCRIPCION,
            CASE 
                WHEN DETALLEREGISTRO.PrecioUnitario IS NULL
                THEN 0
                ELSE DETALLEREGISTRO.PrecioUnitario
            END AS PrecioUnitario,
            CASE
                WHEN DETALLEREGISTRO.Cantidad IS NULL
                THEN 0
                ELSE DETALLEREGISTRO.Cantidad
            END AS Cantidad,
            CASE
                WHEN DETALLEREGISTRO.PrecioUnitario * DETALLEREGISTRO.Cantidad IS NULL
                THEN 0
                ELSE DETALLEREGISTRO.PrecioUnitario * DETALLEREGISTRO.Cantidad
            END AS SUBTOTAL
            FROM BODEGA.dbo.REGISTRO LEFT OUTER JOIN
            BODEGA.dbo.DETALLEREGISTRO ON REGISTRO.IdRegistro = DETALLEREGISTRO.IdRegistro
            LEFT OUTER JOIN
            PRUEBAS.CONINV.ARTICULO CONINV ON DETALLEREGISTRO.ArticuloDetalle = CONINV.ARTICULO
            WHERE CodigoBarra IN ($codigosBarra)
            ORDER BY CodigoBarra ASC"
        )->queryAll();

        if (!$detalles) {
            Yii::$app->session->setFlash('info', "No existe produccion en esta fecha : $fecha");
            return $this->redirect(['reporte-produccion-dia']);
        }

        $resumen = Yii::$app->db->createCommand(
            "SELECT R.IdRegistro, R.CodigoBarra, R.Articulo, R.Descripcion, R.Clasificacion, R.Libras,
            CASE 
                WHEN (SELECT SUM(Cantidad) FROM BODEGA.dbo.DETALLEREGISTRO DR WHERE DR.IdRegistro = R.IdRegistro) IS NULL
                THEN 0
                ELSE (SELECT SUM(Cantidad) FROM BODEGA.dbo.DETALLEREGISTRO DR WHERE DR.IdRegistro = R.IdRegistro)
            END AS Cantidad,
            CASE 
                WHEN (SELECT SUM(Cantidad * PrecioUnitario) FROM BODEGA.dbo.DETALLEREGISTRO DR WHERE DR.IdRegistro = R.IdRegistro) IS NULL
                THEN 0
                ELSE (SELECT SUM(Cantidad * PrecioUnitario) FROM BODEGA.dbo.DETALLEREGISTRO DR WHERE DR.IdRegistro = R.IdRegistro)
            END AS PrecioUnitario
            FROM BODEGA.dbo.REGISTRO R
            WHERE (R.CodigoBarra IN ($codigosBarra))
            GROUP BY R.IdRegistro, R.CodigoBarra, R.Articulo, R.Descripcion, R.Clasificacion, R.Libras, R.Unidades
            ORDER BY R.CodigoBarra"
        )->queryAll();

        $headersResumenArticulo = ['Articulo', 'Descripcion', 'Clasificacion', 'Cantidad producida', 'Libras', 'Unidades'];

        $resumenArticulos = Yii::$app->db->createCommand(
            "SELECT R.Articulo, R.Descripcion, R.Clasificacion, COUNT(R.Articulo) CantidadProd, SUM(Libras) Libras, SUM(Unidades) Unidades
            FROM BODEGA.dbo.REGISTRO R
            WHERE (R.CodigoBarra IN ($codigosBarra))
            GROUP BY R.Articulo, R.Descripcion, R.Clasificacion
            ORDER BY R.Articulo"
        )->queryAll();

        $count = Yii::$app->db->createCommand(
            "SELECT DISTINCT CodigoBarra 
            FROM BODEGA.dbo.REGISTRO
            WHERE CodigoBarra IN ($codigosBarra)"
        )->queryAll();

        $spreadsheet = new Spreadsheet();
        // Inicia hoja #1 DETALLE
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('DETALLE');
        $headers = [
            'CodigoBarra', 'Articulo', 'Descripcion', 'Clasificacion', 'Libras', 'FechaCreacion',
            'UsuarioCreacion', 'EmpresaDestino', 'Observaciones', 'ArticuloDetalle', 'DescripcionDetalle', 'PrecioUnitario',
            'Cantidad', 'Total'
        ];
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $column++;
        }
        $sheet->fromArray($detalles, null, 'A2');

        $fillHeader =  $this->estilosCabezeraReportes();
        $styleArray = $this->estilosContenidoReportes();

        $sheet->getStyle('A1:N1')->applyFromArray($fillHeader);
        $sheet->getStyle('A2:N' . (count($detalles) + 1))->applyFromArray($styleArray);

        $sheet->getStyle('P1')->applyFromArray($fillHeader);
        $sheet->getStyle('P2')->applyFromArray($styleArray);

        $sheet->getStyle('R1:Y1')->applyFromArray($fillHeader);
        $sheet->getStyle('R2:Y' . (count($resumen) + 1))->applyFromArray($styleArray);

        $sheet->getStyle('AA1:AF1')->applyFromArray($fillHeader);
        $sheet->getStyle('AA2:AF' . (count($resumenArticulos) + 1))->applyFromArray($styleArray);

        
        $sheet->setCellValue('P1', 'Cantidad de fardos');
        $sheet->setCellValue('P2', count($count));

        $headersResumen = ['Registro', 'CodigoBarra', 'Articulo', 'Descripcion', 'Clasificacion', 'Libras', 'Cantidad', 'Totalfinal'];
        $column2 = 'R';

        foreach ($headersResumen as $header) {
            $sheet->setCellValue($column2 . '1', $header);
            $column2++;
        }

        $sheet->fromArray($resumen, null, 'R2');

        $headersResumenArticulo = ['Articulo', 'Descripcion', 'Clasificacion', 'Cantidad producida', 'Libras', 'Unidades'];
        $column2 = 'AA';

        foreach ($headersResumenArticulo as $header) {
            $sheet->setCellValue($column2 . '1', $header);
            $column2++;
        }
        $sheet->fromArray($resumenArticulos, null, 'AA2');

        /* The above code is generating and exporting an Excel spreadsheet file in PHP. */
        $sheet->setAutoFilter('A1:AF' . (count($resumenArticulos) + 1));

        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filePath = "PRODUCCION DIA $fecha";
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename=' . $filePath. '.xlsx');
        $writer->save('php://output');
        exit;
    }

    public function actionReporteExistenciaContenedor()
    {
        $model = $this->crearModelReporte();
        return $this->render('form-reporte-existencia-contenedor', [
            'model' => $model
        ]);
    }

    public function actionCrearReporteExistenciaContenedor($fecha)
    {
        $existenciaContenedor = Yii::$app->db->createCommand(
            "SELECT Articulo, Descripcion, Clasificacion, BodegaActual, Activo, SUM(Libras) as Libras, count(CodigoBarra) as Cantidad 
            FROM [BODEGA].[dbo].[REGISTRO]  
            WHERE IdTipoRegistro = 2 AND Activo = 1 AND FechaCreacion < '$fecha'
            GROUP BY Articulo, Descripcion, Clasificacion, BodegaActual, Activo
            ORDER BY Articulo, BodegaACtual"
        )->queryAll();

        return $this->renderAjax('reporte-existencia-contenedor', [
            'existencia' => $existenciaContenedor
        ]);
    }

    public function actionReporteAsignacionProduccion()
    {
        $model = $this->crearModelReporte();
        return $this->render('form-reporte-asignacion-produccion', [
            'model' => $model
        ]);
    }

    public function actionCrearReporteAsignacionProduccion($fechaInicio, $fechaFin)
    {
        $transacciones = TrabajoMesaModel::find()->select('Documento_inv')
            ->where("Fecha BETWEEN  '$fechaInicio' AND '$fechaFin'")->all();
        $documentoInv = '';
        foreach ($transacciones as $index => $documento) {
            if ($index == count($transacciones) - 1) {
                $documentoInv .= "'$documento->Documento_inv'";
                continue;
            }
            $documentoInv .= "'$documento->Documento_inv', ";
        }

        if (!$transacciones) {
            return $this->renderAjax('reporte-asignacion-produccion', [
                'transaccion' => "<h1>No existe asignacion en esta fecha: $fechaInicio -  $fechaFin</h1>",
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin
            ]);
        }
        $codigosAsignados = Yii::$app->db->createCommand(
            "SELECT T.CodigoBarra, R.Articulo, R.Descripcion, R.Clasificacion, R.Libras, R.BodegaActual, R.Activo, TE.TipoEmpaque, T.Fecha
            FROM BODEGA.dbo.TRANSACCION T
            INNER JOIN BODEGA.dbo.REGISTRO R
            ON T.CodigoBarra = R.CodigoBarra
            INNER JOIN BODEGA.dbo.TIPOEMPAQUE TE
            ON R.IdTipoEmpaque = TE.IdTipoEmpaque
            WHERE T.Documento_Inv IN ($documentoInv)
            ORDER BY R.Libras, T.Fecha"
        )->queryAll();

        return $this->renderAjax('reporte-asignacion-produccion', [
            'asignacion' => $codigosAsignados,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin
        ]);
    }

    public function actionReporteAsignacionClasificacion()
    {
        $model = $this->crearModelReporte();
        return $this->render('form-reporte-asignacion-clasificacion', [
            'model' => $model
        ]);
    }

    public function actionCrearReporteAsignacionClasificacion($fechaInicio, $fechaFin)
    {
        $transacciones = AsignacionClasificacion::find()->select('Documento_inv')
            ->where("Fecha BETWEEN  '$fechaInicio' AND '$fechaFin' AND Libras > 0 AND Costo > 0")->all();
        $documentoInv = '';
        foreach ($transacciones as $index => $documento) {
            if ($index == count($transacciones) - 1) {
                $documentoInv .= "'$documento->Documento_inv'";
                continue;
            }
            $documentoInv .= "'$documento->Documento_inv', ";
        }

        if (!$transacciones) {
            return $this->renderAjax('reporte-asignacion-clasificacion', [
                'transaccion' => "<h1>No existe asignacion en esta fecha: $fechaInicio -  $fechaFin</h1>",
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin
            ]);
        }
        $codigosAsignados = Yii::$app->db->createCommand(
            "SELECT T.CodigoBarra, R.Articulo, R.Descripcion, R.Clasificacion, R.Libras, R.BodegaActual, R.Activo, TE.TipoEmpaque
            FROM BODEGA.dbo.TRANSACCION T
            INNER JOIN BODEGA.dbo.REGISTRO R
            ON T.CodigoBarra = R.CodigoBarra
            INNER JOIN BODEGA.dbo.TIPOEMPAQUE TE
            ON R.IdTipoEmpaque = TE.IdTipoEmpaque
            WHERE T.Documento_Inv IN ($documentoInv)
            ORDER BY R.Libras"
        )->queryAll();

        return $this->renderAjax('reporte-asignacion-clasificacion', [
            'asignacion' => $codigosAsignados,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin
        ]);
    }

    /**
     * Crea un modelo dinamico que captura la informacion para generar reportes
     * @return DynamicModel un modelo dinamico
     */
    public function crearModelReporte()
    {
        $reporte = new DynamicModel([
            'reporte', 'fecha', 'persona', 'mesa', 'codigos'
        ]);
        $reporte->setAttributeLabels([
            'reporte' => 'Tipo de reporte',
            'fecha' => 'Fecha estimada',
            'persona' => 'Persona',
            'mesa' => 'Mesa',
            'codigos' => 'Codigos de barra'
        ]);
        $reporte->addRule(['reporte', 'fecha', 'persona', 'codigos'], 'required');
        return $reporte;
    }

    /**
     * Encuentra un registro a partir de un codigo de barra
     * @param string $codigoBarra El codigo de barra del registro a buscar
     * @return RegistroModel El modelo encontrado
     * @throws NotFoundHttpException Si no se encuentra ningun registro
     */
    protected function findRegistroModel($codigoBarra)
    {
        if (($model = RegistroModel::findOne(['CodigoBarra' => $codigoBarra])) !== null) {
            return $model;
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

    public function estilosContenidoReportes()
    {
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' =>  ['rgb' => '0685f4']
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'ffffff',
                ],
            ]
        ];

        return $styleArray;
    }

    public function estilosCabezeraReportes()
    {
        $fillHeader =  [
            'font' => [
                'color' =>  ['rgb' => 'ffffff']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '0685f4',
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' =>  ['rgb' => '000000']
                ],
            ],
        ];

        return $fillHeader;
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

    public function obtenerSemanaActual()
    {
        $monday = strtotime("last monday");
        $monday = date('w', $monday) == date('w') ? $monday + 7 * 86400 : $monday;

        $sunday = strtotime(date("Y-m-d", $monday) . " +6 days");

        $inicioSemana = date("Y-m-d", $monday);
        $finSemana = date("Y-m-d", $sunday);

        return "BETWEEN '$inicioSemana' AND '$finSemana'";
    }
}
