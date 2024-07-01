<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "TIPOEMPAQUE".
 *
 * @property int $IdTipoEmpaque
 * @property string|null $TipoEmpaque
 *
 * @property REGISTRO[] $rEGISTROs
 */
class TipoEmpaqueModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'TIPOEMPAQUE';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['TipoEmpaque'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'IdTipoEmpaque' => 'Id Tipo Empaque',
            'TipoEmpaque' => 'Tipo de empaque',
        ];
    }

    /**
     * Gets query for [[REGISTROs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getREGISTROs()
    {
        return $this->hasMany(REGISTRO::class, ['IdTipoEmpaque' => 'IdTipoEmpaque']);
    }
}
