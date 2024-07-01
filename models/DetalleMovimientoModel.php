<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "DETALLEMOVIMIENTO".
 *
 * @property int $IdDetalleMovimiento
 * @property int $IdMovimiento
 * @property string $CodigoBarra
 *
 * @property REGISTRO $codigoBarra
 * @property MOVIMIENTO $idMovimiento
 */
class DetalleMovimientoModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'DETALLEMOVIMIENTO';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['IdMovimiento', 'CodigoBarra'], 'required'],
            [['IdMovimiento'], 'integer'],
            [['CodigoBarra'], 'string', 'max' => 25],
            [['CodigoBarra'], 'exist', 'skipOnError' => true, 'targetClass' => RegistroModel::class, 'targetAttribute' => ['CodigoBarra' => 'CodigoBarra']],
            [['IdMovimiento'], 'exist', 'skipOnError' => true, 'targetClass' => MovimientoModel::class, 'targetAttribute' => ['IdMovimiento' => 'IdMovimiento']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'IdDetalleMovimiento' => 'Id Detalle Movimiento',
            'IdMovimiento' => 'Id Movimiento',
            'CodigoBarra' => 'Codigo Barra',
        ];
    }

    /**
     * Gets query for [[CodigoBarra]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoBarra()
    {
        return $this->hasOne(RegistroModel::class, ['CodigoBarra' => 'CodigoBarra']);
    }

    /**
     * Gets query for [[IdMovimiento]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdMovimiento()
    {
        return $this->hasOne(MovimientoModel::class, ['IdMovimiento' => 'IdMovimiento']);
    }
}
