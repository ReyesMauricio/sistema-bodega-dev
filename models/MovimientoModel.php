<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "MOVIMIENTO".
 *
 * @property int $IdMovimiento
 * @property string $TipoMovimiento
 * @property string $Estado
 * @property string $origen
 * @property string $destino
 * @property int|null $Documento_inv
 * @property string $Fecha
 * @property string $CreateDate
 *
 * @property DETALLEMOVIMIENTO[] $dETALLEMOVIMIENTOs
 */
class MovimientoModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'MOVIMIENTO';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['TipoMovimiento', 'Estado', 'origen', 'destino', 'Fecha', 'CreateDate'], 'required'],
            [['Documento_inv'], 'integer'],
            [['Fecha', 'CreateDate'], 'safe'],
            [['TipoMovimiento'], 'string', 'max' => 1],
            [['Estado', 'origen', 'destino'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'IdMovimiento' => 'Id Movimiento',
            'TipoMovimiento' => 'Tipo Movimiento',
            'Estado' => 'Estado',
            'origen' => 'Origen',
            'destino' => 'Destino',
            'Documento_inv' => 'Documento Inv',
            'Fecha' => 'Fecha',
            'CreateDate' => 'Create Date',
        ];
    }

    /**
     * Gets query for [[DETALLEMOVIMIENTOs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDetalleMovimiento()
    {
        return $this->hasMany(DetalleMovimientoModel::class, ['IdMovimiento' => 'IdMovimiento']);
    }
}
