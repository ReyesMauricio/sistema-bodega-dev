<?php

namespace app\models;

use yii\base\Component;

class EsquemaComponent extends Component
{
    public $nombre;

    public function getNombre()
    {
        return $this->nombre;
    }

    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }
}
?>
