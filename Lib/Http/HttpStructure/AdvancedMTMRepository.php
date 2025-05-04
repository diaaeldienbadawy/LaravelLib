<?php

namespace App\Lib\Http\HttpStructure;

use App\Lib\FileService;
use App\Lib\UtilitiesTypes\ProccessStatus;
use Illuminate\Http\Request;

abstract class AdvancedMTMRepository implements IRepository
{
    protected AdvancedModel $model;
    protected CustomValidator $validator;

    public function initialize(AdvancedModel $model, CustomValidator $validator)
    {
        $this->model = $model;
        $this->validator = $validator;
    }
    public function __construct(AdvancedModel $model, CustomValidator $validator)
    {
        $this->initialize($model, $validator );
    }
    public function getAll(bool $reverse = false){
        return $this->model->getAll($reverse);
    }
    public function getPage($page=1 , $pageCap = 10,bool $reverse = false)
    {
        return $this->model->getPage($page , $pageCap, $reverse);
    }
    public function getCollection($first , $limit = 10,bool $reverse = false){
        return $this->model->getCollection($first , $limit , $reverse);
    }
    public function find($id){
        return $this->model->getOne($id);
    }
    public function create(array $data)
    {
        return $this->model->create($data);
    }
    public function delete($id)
    {
        $item = $this->model->find($id);
        if($item){
            $item->delete();
        }
        return $item;
    }
    public function update(array $data)
    {
        $item = $this->model->find($data['id']);
        if ($item) {
            $item->update($data);
            $item->refresh();
            return $item;
        }
        return null;
    }
    public function index(Request $request)
    {
        if(isset($request->reverse))$reverse = $request->reverse;
        else $reverse = false;
        if(isset($request->page)){
            $page = $request->page;
            $pageCap = $request->count??10;
            $articals = $this->getPage($page , $pageCap, $reverse);
        }
        else if(isset($request->count) || isset($request->first)) {
            $count = $request->count??10;
            $first = $request->first??false;
            $articals = $this->getCollection($first , $count , $reverse);
        }else $articals = $this->getAll($reverse);
        return $articals;
    }

    public function show($id)
    {
        if (!is_numeric($id)) throw new \Exception('invalidData');
        return $this->find($id);
    }

    protected function insert(array $data){
        $item = $this->create($data);
        if(!$item){
            $this->recoverFiles(null , $data );
            throw new \Exception('unknown');
        }
        return $item;
    }
    protected function edit(array $data)
    {
        if(!array_key_exists('id' , $data)) throw new \Exception('invalidData');

        $old = $this->model->find($data['id']);
        if($old){
            $this->updateFiles($old , $data );
            $item = $this->update($data);
            if(!$item){
                $this->recoverFiles($old , $data );
                throw new \Exception('unknown');
            }
        }
        return $item;
    }
    protected function editChilds(AdvancedModel $old, array $data){
        if(count($this->model->listedRelations)>0){
            foreach($this->model->listedRelations as $relation){
                if (method_exists($old, $relation)) {
                    if(array_key_exists($relation,$data)){
                        $result = $old->$relation;
                        if(count($result)>0){
                            foreach($old->$relation as $i){
                                $this->removeFiles($i);
                            }

                            $old->$relation()->delete();
                            $old->$relation()->createMany($data[$relation]);
                            foreach($data[$relation] as $i){
                                $this->republishFiles($i);
                            }
                        }
                    }
                } else {
                    throw new \Exception("The relation {$relation} does not exist.");
                }
            }
        }
    }
    public function store(Request $request)
    {
        $data = $this->validator->validate($request);

        if(array_key_exists('list',$data)){

            $ids=[];
            $inserts = [];
            $updates = [];

            foreach($data['list'] as $item){
                if(isset($item['id'])){
                    array_push($ids , $item['id']);
                    array_push($updates,$item);
                }
                else array_push($inserts , $item);
            }

            $this->model->insert($inserts);

            if(array_key_exists('parent' , $data)){
                $foreignKey = $data['parent']['foreign_key'];
                $id = $data['parent']['foreign_id'];
            }

            foreach ($updates as $update) {
                $this->edit($update);
            }

            if($foreignKey){
                $deletes = $this->model->where($foreignKey,$id)->whereNotIn('id',$ids)->get();
                if($deletes){
                    foreach($deletes as $delete){
                        $this->removeFiles($delete);
                    }
                    $this->model->where($foreignKey,$id)->whereNotIn('id',$ids)->delete();
                }
            }

            return true;
        }else{
            if(isset($data['id'])){
                return $this->edit($data);
            }else {
                return $this->insert($data);
            }
        }
    }

    public function destroy($id):ProccessStatus
    {
        if($item = $this->delete($id)){
            $this->removeFiles($item);
            return new ProccessStatus(true , '');
        }else new ProccessStatus(false , 'العنصر غير مسجل');
    }

    public function updateFiles($item,array $data ){
        if(!is_array($item))
        $item = $item->toArray();

        foreach($this->validator->fileFields as $field => $value){
            if(count($item)>0){
                if(array_key_exists($field, $item) && array_key_exists($field, $data) ){
                    if($item[$field] != $data[$field]){
                        $oldFile = new FileService($item[$field],$value);
                        $oldFile->deleteFromPublic();
                    }
                }
            }
        }
    }
    public function removeFiles($item){
        if(!is_array($item))
        $item = $item->toArray();

        if(count($item)>0 && count ($this->validator->fileFields)>0 ){
            foreach($item as $i => $i_value){
                if(is_array($i_value)){
                    $this->removeFiles($i_value);
                }else {
                    if(array_key_exists($i,$this->validator->fileFields)){
                        $oldFile = new FileService($i_value,$this->validator->fileFields[$i]);
                        $oldFile->deleteFromPublic();
                    }
                }
            }
        }

    }
    public function republishFiles($item){
        if(count($item)>0 && count ($this->validator->fileFields)>0 ){
            foreach($this->validator->fileFields as $field => $value){
                if(array_key_exists($field, $item)){
                    $File = new FileService($item[$field],$value);
                    $File->saveInPublic();
                }
            }
        }
    }
    public function recoverFiles($item,array $data){
        $item = $item?$item->toArray():[];
        foreach($this->validator->fileFields as $field => $value){
            if(count($item)>0){
                if(array_key_exists($field, $item) && array_key_exists($field, $data) ){
                    $oldFile = new FileService($item[$field],$value);
                    $oldFile->restore();
                }
            }
            if(count($data)>0){
                if(array_key_exists($data[$field], $item)){
                    $newFile = new FileService($data[$field],$value);
                    $newFile->deleteFromPublic();
                }
            }
        }
    }
}
