<?php
/**
 * Created by PhpStorm.
 * User: boing
 * Date: 21/11/2017
 * Time: 14:21
 */

namespace Boing\RestApi\Repositories;


use Boing\RestApi\Models\LaraModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

class BaseRepository
{
    /**
     * @var $model LaraModel
     */
    protected $model;

    public function __construct(LaraModel $model) {
        $this->model = $model;
    }

    /**
     * @param array $query
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all($query = [], $columns = []) {
        $model = $this->model->newQuery();

        if(isset($query['first']) && isset($query['rows'])){
            $model->limit($query['rows'])->offset($query['first']);
        }

        if(isset($query['orders']) && count($query['orders']) > 0){
            foreach ($query['orders'] as $order) {
                $model->orderBy($order['name'], $order['dir']);
            }
        }

        if(isset($query['filters'])){
            $filters = ($query['filters']);
            foreach ($filters as $fkey => $fvalue){
                $model->orWhere($fkey, 'like', "%{$fvalue}%");
            }
        }

        if(isset($query['globalFilter']) && $query['globalFilter'] !== 'null'){
            foreach ($this->model->getColumns() as $column) {
                $model->orWhere($column, 'like', '%' . $query['globalFilter']. '%');
            }
        }
    
        if(isset($query['with'])){
            $model->with($query['with']);
        }
    
        if(isset($query['nullable'])){
            $model->whereNull($query['nullable']);
        }

        return $model->get();
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function show($id) {
       return $this->model->find($id);
    }

    public function save($data, $id = null) {
        $model = $this->model;
        if(!is_null($id)){
            $model = $this->model->find($id);
        }
        $model->fill($data);
        $res = $model->saveRecursive($data);
        if(!$res){
            return $this->model->getErrors();
        }
        return $res;
    }

    public function remove($id) {
        return $this->model->find($id)->delete();
    }

    /**
     * @param null $id
     *
     * @return mixed
     */
    public function getTrash($id = null) {
        if($id === 'all'){
            $ret = $this->model->onlyTrashed()->whereNotNull($this->model->getPrimaryKey())->get();
        }else {
            $ret = $this->model->onlyTrashed()->where($this->model->getPrimaryKey(), $id)->get();
        }
        return $ret;
    }

    public function restoreTrashed($id) {
        return $this->model->onlyTrashed()->where($this->model->getPrimaryKey(), $id)->restore();
    }

    public function removeTrashed($id) {
        return $this->model->onlyTrashed()->where($this->model->getPrimaryKey(), $id)->forceDelete();
    }

    /**
     * @param array $query
     *
     * @return array
     */
    public function toComboBox($query = []) {
        $label = isset($query['label']) ? $query['label'] : 'nome';
        $id = isset($query['id']) ? $query['id'] : 'id';

        $label = explode(',', $label);
        $data = [];
		
		$data[] = ['value' => '', 'label' => 'Selecione'];

        foreach ($this->all($query) as $item){
            if(count($label) > 1){
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
                $data[] = ['value' => $item->$id, 'label' => $aux];
            }else if (count($label) == 1){
                $label = is_array($label) ? $label[0] : $label;
                $data[] = ['value' => $item->$id, 'label' => $item->$label];
            }
        }
        return $data;
    }

    public function getRules() {
        return $this->model->rules;
    }

    public function getErrors() {
        return $this->model->getErrors();
    }
}
