<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "DETALLEREGISTRO".
 *
 * @property int $IdDetalleRegistro
 * @property int $IdRegistro
 * @property string $ArticuloDetalle
 * @property int $Cantidad
 * @property float $PrecioUnitario
 * @property int|null $ContadorImpresiones
 *
 * @property REGISTRO $idRegistro
 */
class DetalleRegistroModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'DETALLEREGISTRO';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['IdRegistro', 'ArticuloDetalle', 'Cantidad', 'PrecioUnitario'], 'required'],
            [['IdRegistro', 'Cantidad', 'ContadorImpresiones'], 'integer'],
            [['PrecioUnitario'], 'number'],
            [['ArticuloDetalle'], 'string', 'max' => 10],
            [['IdRegistro'], 'exist', 'skipOnError' => true, 'targetClass' => RegistroModel::class, 'targetAttribute' => ['IdRegistro' => 'IdRegistro']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'IdDetalleRegistro' => 'Id Detalle Registro',
            'IdRegistro' => 'Id Registro',
            'ArticuloDetalle' => 'Detalle de articulo ',
            'Cantidad' => 'Cantidad',
            'PrecioUnitario' => 'Precio Unitario',
            'ContadorImpresiones' => 'Contador de impresiones',
        ];
    }

    /**
     * Gets query for [[IdRegistro]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdRegistro()
    {
        return $this->hasOne(RegistroModel::class, ['IdRegistro' => 'IdRegistro']);
    }
}
