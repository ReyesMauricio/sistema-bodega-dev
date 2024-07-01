<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "TRABAJOMESA".
 *
 * @property int $id_trabajo_mesa
 * @property int $NumeroMesa
 * @property float $Libras
 * @property float $Costo
 * @property string $ProducidoPor
 * @property string $Documento_inv
 * @property string $ProducidoPor
 * @property string $Fecha
 * @property string $CreateDate
 *
 * @property REGISTRO $codigoBarra
 */
class TrabajoMesaModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'TRABAJOMESA';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['NumeroMesa'], 'integer'],
            [['Fecha', 'CreateDate'], 'safe'],
            [['Libras', 'Costo'], 'number'],
            [['ProducidoPor'], 'string', 'max' => 255],
            [['Documento_inv', 'Bodega'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_trabajo_mesa' => 'Id Trabajo Mesa',
            'NumeroMesa' => 'Numero de mesa',
            'Libras' => 'Libras',
            'Costo' => 'Costo',
            'ProducidoPor' => 'Producido por',
            'Documento_inv' => 'Documento Inv',
            'Bodega' => 'Bodega',
            'Fecha' => 'Fecha',
            'CreateDate' => 'Create Date',
        ];
    }
}
