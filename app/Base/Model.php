<?php

namespace App\Base;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel{
    
    /* Declara aquí tus variables compartidas para todos tus modelos */
    public $columnsJoin;
    
    public function __construct()
    {
        $this->columnsJoin = [$this->table.'.*'];
    }

    public function getColumnsJoin($object, $indShow = false)
    {
        $type = $indShow ? 'first' : 'get';
        return $object->$type($this->columnsJoin);
    }
    
    public function cleanColumnsJoin()
    {
        $this->columnsJoin = [$this->table.'.*'];
    }

    public function baseJoin($object, $tableJoin, $tableOrigin = '', $columnsJoinSelected = null)
    {    
        if(!is_null($columnsJoinSelected)){
            if(is_array($columnsJoinSelected)){
                for($i = 0; $i < count($columnsJoinSelected); $i++)
                {
                    array_push($this->columnsJoin, $tableJoin.'.'.$columnsJoinSelected[$i].' As '.$columnsJoinSelected[$i].'_'.$tableJoin);
                }
            }else{
                if($columnsJoinSelected == '*'){
                    array_push($this->columnsJoin, $tableJoin.'.*');
                }else{
                    array_push($this->columnsJoin, $tableJoin.'.'.$columnsJoinSelected.' As '.$columnsJoinSelected.'_'.$tableJoin);
                }
            }
            
        }        

        return $object
            ->join($tableOrigin == '' ? $this->table : $tableOrigin, $this->table.'.id_'.$tableJoin, '=', $tableJoin.'.id')
        ;        
    }

}
