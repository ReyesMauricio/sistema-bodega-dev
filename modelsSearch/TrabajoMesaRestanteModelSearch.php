<?php

namespace app\modelsSearch;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\TrabajoMesaRestanteModel;

/**
 * TrabajoMesaRestanteModelSearch represents the model behind the search form of `app\models\TrabajoMesaRestanteModel`.
 */
class TrabajoMesaRestanteModelSearch extends TrabajoMesaRestanteModel
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['IdTrabajoMesaRestante', 'NumeroMesa'], 'integer'],
            [['Fecha', 'CreateDate'], 'safe'],
            [['Libras', 'Costo'], 'number'],
            [['Bodega'], 'string'],
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
        $query = TrabajoMesaRestanteModel::find();

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
            'IdTrabajoMesaRestante' => $this->IdTrabajoMesaRestante,
            'NumeroMesa' => $this->NumeroMesa,
            'Fecha' => $this->Fecha,
            'Libras' => $this->Libras,
            'Costo' => $this->Costo,
            'Bodega' => $this->Bodega,
            'CreateDate' => $this->CreateDate,
        ]);

        return $dataProvider;
    }
}
