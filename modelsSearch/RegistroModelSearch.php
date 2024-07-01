<?php

namespace app\modelsSearch;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

use app\models\RegistroModel;

/**
 * RegistroModelSearch represents the model behind the search form of `app\models\RegistroModel`.
 */
class RegistroModelSearch extends RegistroModel
{
    function __construct()
    {
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['IdRegistro', 'Unidades', 'IdTipoEmpaque', 'IdUbicacion', 'Activo', 'Sesion', 'IdTipoRegistro', 'ContadorImpresiones'], 'integer'],
            [['CodigoBarra', 'Articulo', 'Descripcion', 'Clasificacion', 'EmpacadoPor', 'ProducidoPor', 'BodegaCreacion', 'BodegaActual', 'Observaciones', 'UsuarioCreacion', 'DOCUMENTO_INV', 'Estado', 'FechaCreacion', 'FechaModificacion', 'CreateDate', 'EmpresaDestino', 'MesaOrigen'], 'safe'],
            [['Libras', 'Costo'], 'number'],
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
        $query = RegistroModel::find()->where($where)->limit(100);
        if($where == 'existencias'){
            $query = RegistroModel::find()
            ->select('Articulo, Descripcion, COUNT(Articulo) AS Unidades, SUM(Libras) AS Libras')
            ->where("CreateDate > '2023-10-17 00:00:00:000'
                AND (Activo = 1 OR Activo IS NULL)
                AND Estado NOT LIKE 'ELIMINADO'
                AND UsuarioCreacion NOT LIKE '%AUDITORIA%'
                AND CodigoBarra NOT IN (SELECT CodigoBarra FROM BODEGA.dbo.DETALLEMOVIMIENTO)")
            ->groupBy('Articulo, Descripcion')
            ->orderBy('Articulo')
            ->limit(100);
        }

        // add conditions that should always apply here
        $sort = $where == 'existencias' ? 'Articulo' : 'CreateDate';
        $pagination = $where == 'existencias' ? 500 : 25;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => $pagination,
            ],
            'sort' => [
                'defaultOrder' => [
                    $sort => $sort == 'existencias' ? SORT_DESC : SORT_ASC,
                ],
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'IdRegistro' => $this->IdRegistro,
            'Libras' => $this->Libras,
            'Unidades' => $this->Unidades,
            'IdTipoEmpaque' => $this->IdTipoEmpaque,
            'IdUbicacion' => $this->IdUbicacion,
            'Activo' => $this->Activo,
            'Costo' => $this->Costo,
            'FechaModificacion' => $this->FechaModificacion,
            'Sesion' => $this->Sesion,
            'IdTipoRegistro' => $this->IdTipoRegistro,
            'CreateDate' => $this->CreateDate,
            'ContadorImpresiones' => $this->ContadorImpresiones,
        ]);

        $query->andFilterWhere(['like', 'CodigoBarra', $this->CodigoBarra])
            ->andFilterWhere(['like', 'FechaCreacion', $this->FechaCreacion])
            ->andFilterWhere(['like', 'Articulo', $this->Articulo])
            ->andFilterWhere(['like', 'Descripcion', $this->Descripcion])
            ->andFilterWhere(['like', 'Clasificacion', $this->Clasificacion])
            ->andFilterWhere(['like', 'EmpacadoPor', $this->EmpacadoPor])
            ->andFilterWhere(['like', 'ProducidoPor', $this->ProducidoPor])
            ->andFilterWhere(['like', 'BodegaCreacion', $this->BodegaCreacion])
            ->andFilterWhere(['like', 'BodegaActual', $this->BodegaActual])
            ->andFilterWhere(['like', 'Observaciones', $this->Observaciones])
            ->andFilterWhere(['like', 'UsuarioCreacion', $this->UsuarioCreacion])
            ->andFilterWhere(['like', 'DOCUMENTO_INV', $this->DOCUMENTO_INV])
            ->andFilterWhere(['like', 'Estado', $this->Estado])
            ->andFilterWhere(['like', 'EmpresaDestino', $this->EmpresaDestino])
            ->andFilterWhere(['like', 'MesaOrigen', $this->MesaOrigen]);

        return $dataProvider;
    }

    public function searchFardoPaca($params, $NumeroDocumento)
    {
        $query = RegistroModel::find()
            ->joinWith('transacciones t'); // 'transacciones' es el nombre de la relación en RegistroModel

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // Cargar los datos con los parámetros de búsqueda proporcionados
        $this->load($params);

        if (!$this->validate()) {
            // Si los datos no son válidos, retorna el dataProvider vacío
            return $dataProvider;
        }

        // Filtrar los datos según los parámetros de búsqueda
        $query->andFilterWhere([
            't.NumeroDocumento' => $NumeroDocumento,
            't.Naturaleza' => 'S',
        ]);

        return $dataProvider;
    }

    public function searchBarrilesProduccion($params, $mesa)
    {
        $query = RegistroModel::find()
            ->select(['CodigoBarra', 'Articulo', 'Descripcion', 'Libras'])
            ->where(['Estado' => 'Proceso']) // Usamos el parámetro $uuid en lugar de la cadena estática
            ->andWhere(['IdTipoEmpaque' => '1023'])
            ->andWhere(['MesaOrigenAsignacion' => $mesa]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10, // Número de elementos por página
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        return $dataProvider;
    }
}
