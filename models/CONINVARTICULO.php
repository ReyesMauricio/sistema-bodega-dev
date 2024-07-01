<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "CONINV.ARTICULO".
 *
 * @property string $ARTICULO
 * @property string|null $PLANTILLA_SERIE
 * @property string $DESCRIPCION
 * @property string|null $CLASIFICACION_1
 * @property string|null $CLASIFICACION_2
 * @property string|null $CLASIFICACION_3
 * @property string|null $CLASIFICACION_4
 * @property string|null $CLASIFICACION_5
 * @property string|null $CLASIFICACION_6
 * @property float|null $FACTOR_CONVER_1
 * @property float|null $FACTOR_CONVER_2
 * @property float|null $FACTOR_CONVER_3
 * @property float|null $FACTOR_CONVER_4
 * @property float|null $FACTOR_CONVER_5
 * @property float|null $FACTOR_CONVER_6
 * @property string $TIPO
 * @property string $ORIGEN_CORP
 * @property float $PESO_NETO
 * @property float $PESO_BRUTO
 * @property float $VOLUMEN
 * @property int $BULTOS
 * @property string $ARTICULO_CUENTA
 * @property string $IMPUESTO
 * @property float $FACTOR_EMPAQUE
 * @property float $FACTOR_VENTA
 * @property float $EXISTENCIA_MINIMA
 * @property float $EXISTENCIA_MAXIMA
 * @property float $PUNTO_DE_REORDEN
 * @property string $COSTO_FISCAL
 * @property string $COSTO_COMPARATIVO
 * @property float $COSTO_PROM_LOC
 * @property float $COSTO_PROM_DOL
 * @property float $COSTO_STD_LOC
 * @property float $COSTO_STD_DOL
 * @property float $COSTO_ULT_LOC
 * @property float $COSTO_ULT_DOL
 * @property float $PRECIO_BASE_LOCAL
 * @property float $PRECIO_BASE_DOLAR
 * @property string $ULTIMA_SALIDA
 * @property string $ULTIMO_MOVIMIENTO
 * @property string $ULTIMO_INGRESO
 * @property string $ULTIMO_INVENTARIO
 * @property string $CLASE_ABC
 * @property int $FRECUENCIA_CONTEO
 * @property string|null $CODIGO_BARRAS_VENT
 * @property string|null $CODIGO_BARRAS_INVT
 * @property string $ACTIVO
 * @property string $USA_LOTES
 * @property string $OBLIGA_CUARENTENA
 * @property int $MIN_VIDA_COMPRA
 * @property int $MIN_VIDA_CONSUMO
 * @property int $MIN_VIDA_VENTA
 * @property int $VIDA_UTIL_PROM
 * @property int $DIAS_CUARENTENA
 * @property string|null $PROVEEDOR
 * @property string|null $ARTICULO_DEL_PROV
 * @property float $ORDEN_MINIMA
 * @property int $PLAZO_REABAST
 * @property float $LOTE_MULTIPLO
 * @property string|null $NOTAS
 * @property string $UTILIZADO_MANUFACT
 * @property string|null $USUARIO_CREACION
 * @property string|null $FCH_HORA_CREACION
 * @property string|null $USUARIO_ULT_MODIF
 * @property string|null $FCH_HORA_ULT_MODIF
 * @property string $USA_NUMEROS_SERIE
 * @property string|null $MODALIDAD_INV_FIS
 * @property string|null $TIPO_COD_BARRA_DET
 * @property string|null $TIPO_COD_BARRA_ALM
 * @property string|null $USA_REGLAS_LOCALES
 * @property string $UNIDAD_ALMACEN
 * @property string $UNIDAD_EMPAQUE
 * @property string $UNIDAD_VENTA
 * @property string $PERECEDERO
 * @property string|null $GTIN
 * @property string|null $MANUFACTURADOR
 * @property string|null $CODIGO_RETENCION
 * @property string|null $RETENCION_VENTA
 * @property string|null $RETENCION_COMPRA
 * @property string|null $MODELO_RETENCION
 * @property string|null $ESTILO
 * @property string|null $TALLA
 * @property string|null $COLOR
 * @property string $TIPO_COSTO
 * @property string|null $ARTICULO_ENVASE
 * @property string $ES_ENVASE
 * @property string $USA_CONTROL_ENVASE
 * @property float $COSTO_PROM_COMPARATIVO_LOC
 * @property float $COSTO_PROM_COMPARATIVO_DOLAR
 * @property float $COSTO_PROM_ULTIMO_LOC
 * @property float $COSTO_PROM_ULTIMO_DOL
 * @property string $UTILIZADO_EN_CONTRATOS
 * @property string $VALIDA_CANT_FASE_PY
 * @property string $OBLIGA_INCLUIR_FASE_PY
 * @property string $ES_IMPUESTO
 * @property string|null $TIPO_DOC_IVA
 * @property string|null $NIT
 * @property string $CANASTA_BASICA
 * @property string $ES_OTRO_CARGO
 * @property string $SERVICIO_MEDICO
 * @property string|null $ITEM_HACIENDA
 * @property string|null $CODIGO_HACIENDA
 * @property string|null $ITEM_HACIENDA_COMPRA
 * @property string $TIENDA
 * @property string|null $TIPO_EXISTENCIA
 * @property string|null $CATALOGO_EXISTENCIA
 * @property string|null $TIPO_DETRACCION_VENTA
 * @property string|null $CODIGO_DETRACCION_VENTA
 * @property string|null $TIPO_DETRACCION_COMPRA
 * @property string|null $CODIGO_DETRACCION_COMPRA
 * @property string $CALC_PERCEP
 * @property float|null $PORC_PERCEP
 * @property string $SUGIERE_MIN
 * @property string|null $U_CLAVE_UNIDAD
 * @property string|null $U_CLAVE_PROD_SERV
 * @property string|null $U_CLAVE_PS_PUB
 * @property string|null $TIPO_VENTA
 * @property int $NoteExistsFlag
 * @property string $RecordDate
 * @property string $RowPointer
 * @property string $CreatedBy
 * @property string $UpdatedBy
 * @property string $CreateDate
 * @property string $ES_INAFECTO
 * @property string|null $PARTIDA_ARANCELARIA
 */
class CONINVARTICULO extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'CONINV.ARTICULO';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->db2;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ARTICULO', 'DESCRIPCION', 'TIPO', 'ORIGEN_CORP', 'PESO_NETO', 'PESO_BRUTO', 'VOLUMEN', 'BULTOS', 'ARTICULO_CUENTA', 'IMPUESTO', 'FACTOR_EMPAQUE', 'FACTOR_VENTA', 'EXISTENCIA_MINIMA', 'EXISTENCIA_MAXIMA', 'PUNTO_DE_REORDEN', 'COSTO_FISCAL', 'COSTO_COMPARATIVO', 'COSTO_PROM_LOC', 'COSTO_PROM_DOL', 'COSTO_STD_LOC', 'COSTO_STD_DOL', 'COSTO_ULT_LOC', 'COSTO_ULT_DOL', 'PRECIO_BASE_LOCAL', 'PRECIO_BASE_DOLAR', 'ULTIMA_SALIDA', 'ULTIMO_MOVIMIENTO', 'ULTIMO_INGRESO', 'ULTIMO_INVENTARIO', 'CLASE_ABC', 'FRECUENCIA_CONTEO', 'ACTIVO', 'USA_LOTES', 'OBLIGA_CUARENTENA', 'MIN_VIDA_COMPRA', 'MIN_VIDA_CONSUMO', 'MIN_VIDA_VENTA', 'VIDA_UTIL_PROM', 'DIAS_CUARENTENA', 'ORDEN_MINIMA', 'PLAZO_REABAST', 'LOTE_MULTIPLO', 'UTILIZADO_MANUFACT', 'USA_NUMEROS_SERIE', 'UNIDAD_ALMACEN', 'UNIDAD_EMPAQUE', 'UNIDAD_VENTA', 'PERECEDERO', 'TIPO_COSTO', 'ES_ENVASE', 'USA_CONTROL_ENVASE', 'COSTO_PROM_COMPARATIVO_LOC', 'COSTO_PROM_COMPARATIVO_DOLAR', 'COSTO_PROM_ULTIMO_LOC', 'COSTO_PROM_ULTIMO_DOL', 'UTILIZADO_EN_CONTRATOS', 'VALIDA_CANT_FASE_PY', 'OBLIGA_INCLUIR_FASE_PY', 'ES_IMPUESTO', 'CANASTA_BASICA', 'ES_OTRO_CARGO', 'SERVICIO_MEDICO', 'TIENDA', 'CALC_PERCEP', 'SUGIERE_MIN', 'ES_INAFECTO'], 'required'],
            [['FACTOR_CONVER_1', 'FACTOR_CONVER_2', 'FACTOR_CONVER_3', 'FACTOR_CONVER_4', 'FACTOR_CONVER_5', 'FACTOR_CONVER_6', 'PESO_NETO', 'PESO_BRUTO', 'VOLUMEN', 'FACTOR_EMPAQUE', 'FACTOR_VENTA', 'EXISTENCIA_MINIMA', 'EXISTENCIA_MAXIMA', 'PUNTO_DE_REORDEN', 'COSTO_PROM_LOC', 'COSTO_PROM_DOL', 'COSTO_STD_LOC', 'COSTO_STD_DOL', 'COSTO_ULT_LOC', 'COSTO_ULT_DOL', 'PRECIO_BASE_LOCAL', 'PRECIO_BASE_DOLAR', 'ORDEN_MINIMA', 'LOTE_MULTIPLO', 'COSTO_PROM_COMPARATIVO_LOC', 'COSTO_PROM_COMPARATIVO_DOLAR', 'COSTO_PROM_ULTIMO_LOC', 'COSTO_PROM_ULTIMO_DOL', 'PORC_PERCEP'], 'number'],
            [['BULTOS', 'FRECUENCIA_CONTEO', 'MIN_VIDA_COMPRA', 'MIN_VIDA_CONSUMO', 'MIN_VIDA_VENTA', 'VIDA_UTIL_PROM', 'DIAS_CUARENTENA', 'PLAZO_REABAST', 'NoteExistsFlag'], 'integer'],
            [['ULTIMA_SALIDA', 'ULTIMO_MOVIMIENTO', 'ULTIMO_INGRESO', 'ULTIMO_INVENTARIO', 'FCH_HORA_CREACION', 'FCH_HORA_ULT_MODIF', 'RecordDate', 'CreateDate'], 'safe'],
            [['NOTAS', 'RowPointer'], 'string'],
            [['ARTICULO', 'CODIGO_BARRAS_VENT', 'CODIGO_BARRAS_INVT', 'PROVEEDOR', 'ARTICULO_ENVASE', 'NIT', 'U_CLAVE_UNIDAD'], 'string', 'max' => 20],
            [['PLANTILLA_SERIE', 'ARTICULO_CUENTA', 'IMPUESTO', 'CODIGO_RETENCION', 'RETENCION_VENTA', 'RETENCION_COMPRA', 'MODELO_RETENCION', 'ITEM_HACIENDA', 'ITEM_HACIENDA_COMPRA', 'TIPO_EXISTENCIA', 'CATALOGO_EXISTENCIA', 'TIPO_DETRACCION_VENTA', 'CODIGO_DETRACCION_VENTA', 'TIPO_DETRACCION_COMPRA', 'CODIGO_DETRACCION_COMPRA', 'U_CLAVE_PS_PUB'], 'string', 'max' => 4],
            [['DESCRIPCION'], 'string', 'max' => 254],
            [['CLASIFICACION_1', 'CLASIFICACION_2', 'CLASIFICACION_3', 'CLASIFICACION_4', 'CLASIFICACION_5', 'CLASIFICACION_6', 'PARTIDA_ARANCELARIA'], 'string', 'max' => 12],
            [['TIPO', 'ORIGEN_CORP', 'COSTO_FISCAL', 'COSTO_COMPARATIVO', 'CLASE_ABC', 'ACTIVO', 'USA_LOTES', 'OBLIGA_CUARENTENA', 'UTILIZADO_MANUFACT', 'USA_NUMEROS_SERIE', 'MODALIDAD_INV_FIS', 'TIPO_COD_BARRA_DET', 'TIPO_COD_BARRA_ALM', 'USA_REGLAS_LOCALES', 'PERECEDERO', 'TIPO_COSTO', 'ES_ENVASE', 'USA_CONTROL_ENVASE', 'UTILIZADO_EN_CONTRATOS', 'VALIDA_CANT_FASE_PY', 'OBLIGA_INCLUIR_FASE_PY', 'ES_IMPUESTO', 'CANASTA_BASICA', 'ES_OTRO_CARGO', 'SERVICIO_MEDICO', 'CALC_PERCEP', 'SUGIERE_MIN', 'TIPO_VENTA', 'ES_INAFECTO'], 'string', 'max' => 1],
            [['ARTICULO_DEL_PROV'], 'string', 'max' => 30],
            [['USUARIO_CREACION', 'USUARIO_ULT_MODIF', 'CODIGO_HACIENDA', 'TIENDA', 'CreatedBy', 'UpdatedBy'], 'string', 'max' => 50],
            [['UNIDAD_ALMACEN', 'UNIDAD_EMPAQUE', 'UNIDAD_VENTA'], 'string', 'max' => 6],
            [['GTIN'], 'string', 'max' => 13],
            [['MANUFACTURADOR'], 'string', 'max' => 35],
            [['ESTILO', 'TALLA', 'COLOR'], 'string', 'max' => 5],
            [['TIPO_DOC_IVA'], 'string', 'max' => 2],
            [['U_CLAVE_PROD_SERV'], 'string', 'max' => 200],
            [['RowPointer'], 'unique'],
            [['ARTICULO'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ARTICULO' => 'Articulo',
            'PLANTILLA_SERIE' => 'Plantilla Serie',
            'DESCRIPCION' => 'Descripcion',
            'CLASIFICACION_1' => 'Clasificacion 1',
            'CLASIFICACION_2' => 'Clasificacion 2',
            'CLASIFICACION_3' => 'Clasificacion 3',
            'CLASIFICACION_4' => 'Clasificacion 4',
            'CLASIFICACION_5' => 'Clasificacion 5',
            'CLASIFICACION_6' => 'Clasificacion 6',
            'FACTOR_CONVER_1' => 'Factor Conver 1',
            'FACTOR_CONVER_2' => 'Factor Conver 2',
            'FACTOR_CONVER_3' => 'Factor Conver 3',
            'FACTOR_CONVER_4' => 'Factor Conver 4',
            'FACTOR_CONVER_5' => 'Factor Conver 5',
            'FACTOR_CONVER_6' => 'Factor Conver 6',
            'TIPO' => 'Tipo',
            'ORIGEN_CORP' => 'Origen Corp',
            'PESO_NETO' => 'Peso Neto',
            'PESO_BRUTO' => 'Peso Bruto',
            'VOLUMEN' => 'Volumen',
            'BULTOS' => 'Bultos',
            'ARTICULO_CUENTA' => 'Articulo Cuenta',
            'IMPUESTO' => 'Impuesto',
            'FACTOR_EMPAQUE' => 'Factor Empaque',
            'FACTOR_VENTA' => 'Factor Venta',
            'EXISTENCIA_MINIMA' => 'Existencia Minima',
            'EXISTENCIA_MAXIMA' => 'Existencia Maxima',
            'PUNTO_DE_REORDEN' => 'Punto De Reorden',
            'COSTO_FISCAL' => 'Costo Fiscal',
            'COSTO_COMPARATIVO' => 'Costo Comparativo',
            'COSTO_PROM_LOC' => 'Costo Prom Loc',
            'COSTO_PROM_DOL' => 'Costo Prom Dol',
            'COSTO_STD_LOC' => 'Costo Std Loc',
            'COSTO_STD_DOL' => 'Costo Std Dol',
            'COSTO_ULT_LOC' => 'Costo Ult Loc',
            'COSTO_ULT_DOL' => 'Costo Ult Dol',
            'PRECIO_BASE_LOCAL' => 'Precio Base Local',
            'PRECIO_BASE_DOLAR' => 'Precio Base Dolar',
            'ULTIMA_SALIDA' => 'Ultima Salida',
            'ULTIMO_MOVIMIENTO' => 'Ultimo Movimiento',
            'ULTIMO_INGRESO' => 'Ultimo Ingreso',
            'ULTIMO_INVENTARIO' => 'Ultimo Inventario',
            'CLASE_ABC' => 'Clase Abc',
            'FRECUENCIA_CONTEO' => 'Frecuencia Conteo',
            'CODIGO_BARRAS_VENT' => 'Codigo Barras Vent',
            'CODIGO_BARRAS_INVT' => 'Codigo Barras Invt',
            'ACTIVO' => 'Activo',
            'USA_LOTES' => 'Usa Lotes',
            'OBLIGA_CUARENTENA' => 'Obliga Cuarentena',
            'MIN_VIDA_COMPRA' => 'Min Vida Compra',
            'MIN_VIDA_CONSUMO' => 'Min Vida Consumo',
            'MIN_VIDA_VENTA' => 'Min Vida Venta',
            'VIDA_UTIL_PROM' => 'Vida Util Prom',
            'DIAS_CUARENTENA' => 'Dias Cuarentena',
            'PROVEEDOR' => 'Proveedor',
            'ARTICULO_DEL_PROV' => 'Articulo Del Prov',
            'ORDEN_MINIMA' => 'Orden Minima',
            'PLAZO_REABAST' => 'Plazo Reabast',
            'LOTE_MULTIPLO' => 'Lote Multiplo',
            'NOTAS' => 'Notas',
            'UTILIZADO_MANUFACT' => 'Utilizado Manufact',
            'USUARIO_CREACION' => 'Usuario Creacion',
            'FCH_HORA_CREACION' => 'Fch Hora Creacion',
            'USUARIO_ULT_MODIF' => 'Usuario Ult Modif',
            'FCH_HORA_ULT_MODIF' => 'Fch Hora Ult Modif',
            'USA_NUMEROS_SERIE' => 'Usa Numeros Serie',
            'MODALIDAD_INV_FIS' => 'Modalidad Inv Fis',
            'TIPO_COD_BARRA_DET' => 'Tipo Cod Barra Det',
            'TIPO_COD_BARRA_ALM' => 'Tipo Cod Barra Alm',
            'USA_REGLAS_LOCALES' => 'Usa Reglas Locales',
            'UNIDAD_ALMACEN' => 'Unidad Almacen',
            'UNIDAD_EMPAQUE' => 'Unidad Empaque',
            'UNIDAD_VENTA' => 'Unidad Venta',
            'PERECEDERO' => 'Perecedero',
            'GTIN' => 'Gtin',
            'MANUFACTURADOR' => 'Manufacturador',
            'CODIGO_RETENCION' => 'Codigo Retencion',
            'RETENCION_VENTA' => 'Retencion Venta',
            'RETENCION_COMPRA' => 'Retencion Compra',
            'MODELO_RETENCION' => 'Modelo Retencion',
            'ESTILO' => 'Estilo',
            'TALLA' => 'Talla',
            'COLOR' => 'Color',
            'TIPO_COSTO' => 'Tipo Costo',
            'ARTICULO_ENVASE' => 'Articulo Envase',
            'ES_ENVASE' => 'Es Envase',
            'USA_CONTROL_ENVASE' => 'Usa Control Envase',
            'COSTO_PROM_COMPARATIVO_LOC' => 'Costo Prom Comparativo Loc',
            'COSTO_PROM_COMPARATIVO_DOLAR' => 'Costo Prom Comparativo Dolar',
            'COSTO_PROM_ULTIMO_LOC' => 'Costo Prom Ultimo Loc',
            'COSTO_PROM_ULTIMO_DOL' => 'Costo Prom Ultimo Dol',
            'UTILIZADO_EN_CONTRATOS' => 'Utilizado En Contratos',
            'VALIDA_CANT_FASE_PY' => 'Valida Cant Fase Py',
            'OBLIGA_INCLUIR_FASE_PY' => 'Obliga Incluir Fase Py',
            'ES_IMPUESTO' => 'Es Impuesto',
            'TIPO_DOC_IVA' => 'Tipo Doc Iva',
            'NIT' => 'Nit',
            'CANASTA_BASICA' => 'Canasta Basica',
            'ES_OTRO_CARGO' => 'Es Otro Cargo',
            'SERVICIO_MEDICO' => 'Servicio Medico',
            'ITEM_HACIENDA' => 'Item Hacienda',
            'CODIGO_HACIENDA' => 'Codigo Hacienda',
            'ITEM_HACIENDA_COMPRA' => 'Item Hacienda Compra',
            'TIENDA' => 'Tienda',
            'TIPO_EXISTENCIA' => 'Tipo Existencia',
            'CATALOGO_EXISTENCIA' => 'Catalogo Existencia',
            'TIPO_DETRACCION_VENTA' => 'Tipo Detraccion Venta',
            'CODIGO_DETRACCION_VENTA' => 'Codigo Detraccion Venta',
            'TIPO_DETRACCION_COMPRA' => 'Tipo Detraccion Compra',
            'CODIGO_DETRACCION_COMPRA' => 'Codigo Detraccion Compra',
            'CALC_PERCEP' => 'Calc Percep',
            'PORC_PERCEP' => 'Porc Percep',
            'SUGIERE_MIN' => 'Sugiere Min',
            'U_CLAVE_UNIDAD' => 'U Clave Unidad',
            'U_CLAVE_PROD_SERV' => 'U Clave Prod Serv',
            'U_CLAVE_PS_PUB' => 'U Clave Ps Pub',
            'TIPO_VENTA' => 'Tipo Venta',
            'NoteExistsFlag' => 'Note Exists Flag',
            'RecordDate' => 'Record Date',
            'RowPointer' => 'Row Pointer',
            'CreatedBy' => 'Created By',
            'UpdatedBy' => 'Updated By',
            'CreateDate' => 'Create Date',
            'ES_INAFECTO' => 'Es Inafecto',
            'PARTIDA_ARANCELARIA' => 'Partida Arancelaria',
        ];
    }
}
