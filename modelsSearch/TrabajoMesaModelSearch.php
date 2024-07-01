<?php

namespace app\modelsSearch;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\TrabajoMesaModel;

/**
 * TrabajoMesaModelSearch represents the model behind the search form of `app\models\TrabajoMesaModel`.
 */
class TrabajoMesaModelSearch extends TrabajoMesaModel
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_trabajo_mesa', 'NumeroMesa'], 'integer'],
            [['Libras', 'Costo'], 'number'],
            [['ProducidoPor', 'Documento_inv', 'Bodega', 'Fecha', 'CreateDate'], 'safe'],
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
    public function search($params, $where)
    {
        $query = TrabajoMesaModel::find();
        if ($where == 'codigos-mesa') {
            $query =  TrabajoMesaModel::find()
                ->select('NumeroMesa, SUM(Libras) as Libras, SUM(Costo) as Costo, Bodega')
                ->groupBy(['NumeroMesa', 'Bodega'])
                ->orderBy(['NumeroMesa' => SORT_ASC])
                ->limit(100);

        } else if ($where != '') {
            $query = TrabajoMesaModel::find()->where($where);
        }
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
            'id_trabajo_mesa' => $this->id_trabajo_mesa,
            'NumeroMesa' => $this->NumeroMesa,
            'Fecha' => $this->Fecha,
            'CreateDate' => $this->CreateDate,
            'Libras' => $this->Libras,
            'Costo' => $this->Costo,
        ]);

        $query
            ->andFilterWhere(['like', 'ProducidoPor', $this->ProducidoPor])
            ->andFilterWhere(['like', 'Bodega', $this->Bodega])
            ->andFilterWhere(['like', 'Documento_inv', $this->Documento_inv]);

        return $dataProvider;
    }
}
