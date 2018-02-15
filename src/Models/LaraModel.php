<?php
/**
 * Created by PhpStorm.
 * User: boing
 * Date: 29/08/2017
 * Time: 15:02
 */

namespace Boing\RestApi\Models;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Sjdaws\Vocal\Vocal;
use Symfony\Component\PropertyAccess\PropertyAccess;

class LaraModel extends Vocal
{
    private static $operators = array(
        '_not' => '!=',
        '_in' => 'IN',
        '_not_in' => 'NOT IN',
        '_equal' => '=',
        '_equals' => '=',
        '_null' => 'null',
        '_not_null' => 'notnull',
        '_not_equal' => '!=',
        '_less_than' => '<',
        '_more_than' => '>',
        '_like' => 'LIKE',
        '_not_like' => 'NOT LIKE',
        '_between' => 'BW'
    );
    
    protected function getMomentObject($value){
        $date = Carbon::createFromFormat('Y-m-d', $value);
        $ret = [
            "stringDate" => $date->format('d/m/Y'),
            "momentDate" => $date->format(Carbon::RFC3339)
        ];
        return $ret;
    }
    
    public function getColumns(){
        return $this->fillable;
    }

    public function getPrimaryKey() {
        return $this->primaryKey;
    }
    
    public function total(){
        return self::get()->count();
    }
    
    public static function all($columns = ['*'], $where = [], $limit = [], $order = [], $join = []){
        $model = self::query();
        
        if ($where || $limit || $order || $join){
            if (is_array($where) && count($where) > 0) {
                foreach ($where as $key => $value) {
                    self::renderCondition($key, $value, $model);
                }
            }
            
            if (is_array($limit) && count($limit) > 0) {
                $model->skip($limit['offset'])->take($limit['limit']);
            }
            
            if (is_array($order) && count($order) > 0) {
                $model->orderBy($order['orderBy'], ($order['order']));
            }
            
            if (!is_null($join) && count($join) > 0 && !is_string($join)) {
                foreach ($join as $value) {
                    $model->join($value['table'], $value['localkey'], $value['op'], $value['foreignkey']);
                }
            }
        }
        
        
        
        return $model->get($columns);
    }
    
    /**
     * @param $arg_key
     * @param $arg_value
     * @param Builder $model
     */
    private static function renderCondition($arg_key, $arg_value, $model)
    {
        $start = FALSE;
        $operator = FALSE;
        foreach (self::$operators as $opstring => $op) {
            $a = strpos($arg_key, $opstring);
            if ($a !== FALSE) {
                $operator = $op;
                if ($start === FALSE) {
                    $start = $a;
                } else {
                    $start = ($start > $a) ? $a : $start;
                }
            }
        }
        $arg_key = ($start) ? substr($arg_key, 0, $start) : $arg_key;
        $operator = ($operator) ? $operator : '=';
        $value = FALSE;
        $checkInOperator = strpos($operator, 'IN');
        if ($checkInOperator !== FALSE) {
            //IN OR NOT IN
            if (is_array($arg_value) && count($arg_value) > 0) {
                $value = $arg_value;
            }
        } else {
            if (isset($arg_value) && $arg_value !== '') {
                $checkLikeOperator = strpos($operator, 'LIKE');
                if ($checkLikeOperator !== FALSE) {
                    $value = '%' . $arg_value . '%';
                } else {
                    //= != < >
                    $value = $arg_value;
                }
            }
        }
        if ($value !== FALSE) {
            if (strpos($operator, 'IN') !== FALSE) {
                $values = $value;
                foreach ($values as $key => $value) {
                    $values[$key] = $value;
                }
                // $values = "('" . implode("','", $values) . "')";
                if ($operator == 'IN') {
                    $model->whereIn($arg_key, $values);
                } else if ($operator == 'NOT IN') {
                    $model->whereNotIn($arg_key, $values);
                }
            } else {
                //$value = $value;
                if ($operator == 'BW') {
                    $model->whereBetween($arg_key, $value);
                } else if($operator === 'null'){
                    $model->whereNull($arg_key);
                } else if($operator === 'notnull'){
                    $model->whereNotNull($arg_key);
                } else {
                    $model->where($arg_key, $operator, $value);
                }
            }
        }
    }
    
    public static function table(){
        $that = new static;
        
        return $that->table;
    }
    
    public static function toComboboxValidator(array $values){
        $ret = [];
        
        foreach ($values as $value) {
            $ret[] = $value[0];
        }
        
        return $ret;
    }
    
    public static function toCombobox($key = 'id', $label = 'name', $where = [], $default = false, $groupBy = '', $angular = false){
        $ret = [];
        $select = ['*'];
        $model = self::query();
        
        if (is_array($where) && count($where) > 0) {
            foreach ($where as $k => $v) {
                self::renderCondition($k, $v, $model);
            }
        }
        
        try{
            $data = $model->get($select);
        }catch (\PDOException $e){
            return $e->getMessage();
        }
        
        
        if(!empty($default)){
            if($default === true){
                $default = __('Select', 'Ip-admin', false);
            }
            if($angular){
                $ret[0] = ['name' => 'Selecione', 'value' => null];
            }else {
                $ret[0] = [0, $default];
            }
        }
        
        foreach ($data as $item) {
            if(is_array($label)){
                $item = json_decode($item->toJson());
                $accessor = PropertyAccess::createPropertyAccessor();
                $aux = '';
                $i = 1;
                foreach ($label as $lbl) {
                    $value = $accessor->getValue($item, $lbl);
                    if(is_array($value)){
                        $aux .= implode(' ', $value);
                    }else{
                        $aux .= $value;
                    }
                    
                    if($i != count($label)){
                        $aux .= ' - ';
                    }
                    $i++;
                }
                if($angular){
                    if(!empty($groupBy)){
                    }
                    $ret[] = ['value' => $item->$key, 'name' => $aux];
                }else{
                    $ret[] = [$item->$key, $aux];
                }
            }else{
                if($angular){
                    $ret[] = ['value' => $item->$key, 'name' => $item->$label];
                    if(!empty($groupBy)){
                        $aux = '  ↳  ';
                        foreach ($item->$groupBy as $subitem){
                            $ret[] = ['value' => $subitem->$key, 'name' => $aux . $subitem->$label];
                        }
                    }
                }else{
                    $ret[] = [$item->$key, $item->$label];
                }
                
            }
        }
        return $ret;
    }
    
}