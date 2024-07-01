<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "CNYCENTER.ARTICULO_PROVEEDOR".
 *
 * @property string $ARTICULO
 * @property string $PROVEEDOR
 * @property string|null $CODIGO_CATALOGO
 * @property float $LOTE_MINIMO
 * @property float $LOTE_ESTANDAR
 * @property float $PESO_MINIMO_ORDEN
 * @property float $MULTIPLO_COMPRA
 * @property float $CANT_ECONOMICA_COM
 * @property string $UNIDAD_MEDIDA_COMP
 * @property float $FACTOR_CONVERSION
 * @property int $PLAZO_REABASTECIMI
 * @property float $PORC_AJUSTE_COSTO
 * @property string|null $FECHA_ULT_COTIZACI
 * @property string|null $NOTAS
 * @property string|null $DESCRIP_CATALOGO
 * @property string $PAIS
 * @property string $TIPO
 * @property string|null $IMPUESTO
 * @property int $NoteExistsFlag
 * @property string $RecordDate
 * @property string $RowPointer
 * @property string $CreatedBy
 * @property string $UpdatedBy
 * @property string $CreateDate
 */
class CNYCENTER_ARTICULO_PROVEEDOR extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'CNYCENTER.ARTICULO_PROVEEDOR';
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
            [['ARTICULO', 'PROVEEDOR', 'LOTE_MINIMO', 'LOTE_ESTANDAR', 'PESO_MINIMO_ORDEN', 'MULTIPLO_COMPRA', 'CANT_ECONOMICA_COM', 'UNIDAD_MEDIDA_COMP', 'FACTOR_CONVERSION', 'PLAZO_REABASTECIMI', 'PORC_AJUSTE_COSTO', 'PAIS', 'TIPO'], 'required'],
            [['LOTE_MINIMO', 'LOTE_ESTANDAR', 'PESO_MINIMO_ORDEN', 'MULTIPLO_COMPRA', 'CANT_ECONOMICA_COM', 'FACTOR_CONVERSION', 'PORC_AJUSTE_COSTO'], 'number'],
            [['PLAZO_REABASTECIMI', 'NoteExistsFlag'], 'integer'],
            [['FECHA_ULT_COTIZACI', 'RecordDate', 'CreateDate'], 'safe'],
            [['NOTAS', 'RowPointer'], 'string'],
            [['ARTICULO', 'PROVEEDOR'], 'string', 'max' => 20],
            [['CODIGO_CATALOGO'], 'string', 'max' => 30],
            [['UNIDAD_MEDIDA_COMP'], 'string', 'max' => 10],
            [['DESCRIP_CATALOGO'], 'string', 'max' => 254],
            [['PAIS', 'IMPUESTO'], 'string', 'max' => 4],
            [['TIPO'], 'string', 'max' => 1],
            [['CreatedBy', 'UpdatedBy'], 'string', 'max' => 50],
            [['RowPointer'], 'unique'],
            [['ARTICULO', 'PROVEEDOR'], 'unique', 'targetAttribute' => ['ARTICULO', 'PROVEEDOR']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ARTICULO' => 'Articulo',
            'PROVEEDOR' => 'Proveedor',
            'CODIGO_CATALOGO' => 'Codigo Catalogo',
            'LOTE_MINIMO' => 'Lote Minimo',
            'LOTE_ESTANDAR' => 'Lote Estandar',
            'PESO_MINIMO_ORDEN' => 'Peso Minimo Orden',
            'MULTIPLO_COMPRA' => 'Multiplo Compra',
            'CANT_ECONOMICA_COM' => 'Cant Economica Com',
            'UNIDAD_MEDIDA_COMP' => 'Unidad Medida Comp',
            'FACTOR_CONVERSION' => 'Factor Conversion',
            'PLAZO_REABASTECIMI' => 'Plazo Reabastecimi',
            'PORC_AJUSTE_COSTO' => 'Porc Ajuste Costo',
            'FECHA_ULT_COTIZACI' => 'Fecha Ult Cotizaci',
            'NOTAS' => 'Notas',
            'DESCRIP_CATALOGO' => 'Descrip Catalogo',
            'PAIS' => 'Pais',
            'TIPO' => 'Tipo',
            'IMPUESTO' => 'Impuesto',
            'NoteExistsFlag' => 'Note Exists Flag',
            'RecordDate' => 'Record Date',
            'RowPointer' => 'Row Pointer',
            'CreatedBy' => 'Created By',
            'UpdatedBy' => 'Updated By',
            'CreateDate' => 'Create Date',
        ];
    }
}
