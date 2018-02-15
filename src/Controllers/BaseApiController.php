<?php
/**
 * Created by PhpStorm.
 * User: boing
 * Date: 20/11/2017
 * Time: 23:07
 */

namespace Boing\RestApi\Controllers;

use App\Http\Controllers\Controller;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Route;
use Boing\RestApi\Models\LaraModel;
use Boing\RestApi\Repositories\BaseRepository;

class BaseApiController extends Controller
{
	/**
     * @var array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public $data = [];
    /**
     * @var $repository \Boing\RestApi\Repositories\BaseRepository
     */
    protected $repository;
    
    public function index()
    {
		
        $query = Input::get();
        $this->repository = new BaseRepository($this->getModel());

        if (isset($query['combo'])) {
            return $this->toComboBox($this->getModel());
        }
        $this->data = $this->repository->all($query);
    
        if(isset($query['treeTable']) && $query['treeTable'] == true){
            foreach ($this->data as $key => $value) {
                $value['children'] = $value[$query['with']];
                $this->data[$key] = $value;
            }
        }
        
        $data = [
            'message'      => $query, //'ok',
            'data'         => $this->data,
            'recordsTotal' => $this->data->count(),
        ];

        return response()->json($data);
    }

    public function show($id)
    {
        $this->repository = new BaseRepository($this->getModel());
        $this->data = $this->repository->show($id);
        return $this->response();
    }

    public function create(Request $request)
    {
        $this->repository = new BaseRepository($this->getModel());
        $this->data = $this->repository->save($request->all());
        return $this->response();
    }

    public function update(Request $request, $id)
    {
        $this->repository = new BaseRepository($this->getModel());
        $this->data = $this->repository->save($request->all(), $id);
        return $this->response();
    }

    public function delete($id)
    {
        $this->repository = new BaseRepository($this->getModel());
        $this->data = $this->repository->remove($id);
        return $this->response();
    }

    public function trash($id = 'all') {
        $this->repository = new BaseRepository($this->getModel());
        $this->data = $this->repository->getTrash($id);
        return $this->response();
    }

    public function restoreTrashed($id) {
        $this->repository = new BaseRepository($this->getModel());
        $this->data = $this->repository->restoreTrashed($id);
        return $this->response();
    }

    public function removeTrashed($id) {
        $this->repository = new BaseRepository($this->getModel());
        $this->data = $this->repository->removeTrashed($id);
        return $this->response();
    }
	
	public function toComboBox(LaraModel $model){
        $query = Input::get();
        $this->repository = new BaseRepository($model);
        
        if(is_array($query) && count($query) > 0){
            $this->data = $this->repository->toComboBox($query);
        }
        
        return $this->response();
    }
    
    public function response($code = 200, $message = 'ok')
    {
        $data = [
            'message' => $message,
            'data'  => $this->data,
        ];
        return response()->json($data, $code);
    }

    /**
     * @param $model
     *
     * @return \App\Models\LaraModel
     */
    protected function getModel()
    {
        $model = Route::currentRouteName();
        $model = config('boing-rest-api.baseModel') . config('boing-rest-api.modelPrefix') . ucfirst(camel_case($model));
        return new $model();
    }
}
