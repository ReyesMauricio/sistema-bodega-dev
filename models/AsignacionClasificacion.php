<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ASIGNACION_CLASIFICACION".
 *
 * @property int $IdASignacionClasificacion
 * @property float $Libras
 * @property float $Costo
 * @property int $NumeroMesa
 * @property string $Fecha
 * @property string $CreateDate
 *  @property string $Bodega
 *  @property string $ProducidoPor
 */
class AsignacionClasificacion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ASIGNACION_CLASIFICACION';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Libras', 'Costo', 'NumeroMesa', 'Fecha', 'CreateDate', 'Bodega', 'ProducidoPor'], 'required'],
            [['Libras', 'Costo'], 'number'],
            [['NumeroMesa'], 'integer'],
            [['Fecha', 'CreateDate'], 'safe'],
            [['Documento_inv'], 'string', 'max' => 50],
            [['Bodega'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'IdASignacionClasificacion' => 'Id A Signacion Clasificacion',
            'Libras' => 'Libras',
            'Costo' => 'Costo',
            'NumeroMesa' => 'Numero Mesa',
            'Fecha' => 'Fecha',
            'CreateDate' => 'Create Date',
            'Bodega' => 'Bodega',
            'ProducidoPor' => 'Producido Por'
        ];
    }

    
}
