<?php

namespace app\modelsSearch;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\DetalleRegistroModel;

/**
 * DetalleRegistroModelSearch represents the model behind the search form of `app\models\DetalleRegistroModel`.
 */
class DetalleRegistroModelSearch extends DetalleRegistroModel
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['IdDetalleRegistro', 'IdRegistro', 'Cantidad', 'ContadorImpresiones'], 'integer'],
            [['ArticuloDetalle'], 'safe'],
            [['PrecioUnitario'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = DetalleRegistroModel::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'IdDetalleRegistro' => $this->IdDetalleRegistro,
            'IdRegistro' => $this->IdRegistro,
            'Cantidad' => $this->Cantidad,
            'PrecioUnitario' => $this->PrecioUnitario,
            'ContadorImpresiones' => $this->ContadorImpresiones,
        ]);

        $query->andFilterWhere(['like', 'ArticuloDetalle', $this->ArticuloDetalle]);

        return $dataProvider;
    }
}
