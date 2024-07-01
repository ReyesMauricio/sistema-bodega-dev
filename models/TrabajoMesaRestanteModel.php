<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "TRABAJOMESA_RESTANTE".
 *
 * @property int $IdTrabajoMesaRestante
 * @property int|null $mesa
 * @property string|null $fecha
 * @property float|null $libras
 * @property int|null $estado
 * @property string|null $CreateDate
 */
class TrabajoMesaRestanteModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'TRABAJOMESA_RESTANTE';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['NumeroMesa'], 'integer'],
            [['Bodega'], 'string', 'max' => 50],
            [['Fecha', 'CreateDate'], 'safe'],
            [['Libras', 'Costo'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'IdTrabajoMesaRestante' => 'Id Trabajo Mesa Restante',
            'NumeroMesa' => 'Mesa',
            'Fecha' => 'Fecha',
            'Bodega' => 'Bodega',
            'Libras' => 'Libras',
            'Costo' => 'Costo',
            'CreateDate' => 'Create Date',
        ];
    }
}
