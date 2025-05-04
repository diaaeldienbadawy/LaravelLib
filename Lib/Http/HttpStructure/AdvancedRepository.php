<?php

namespace App\Lib\Http\HttpStructure;

use App\Lib\FileService;
use App\Lib\Http\HttpStructure\Enums\StoringMode;
use App\Lib\UtilitiesTypes\ModelRelation;
use App\Lib\UtilitiesTypes\Parameters;
use App\Lib\UtilitiesTypes\ProccessStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

abstract class AdvancedRepository
{
    public AdvancedModel $model;

    public array $fileFields = [];

    public array $listedRelations = [];

    public Parameters|null $params;

    public function initialize(AdvancedModel $model , array $fileFields , Parameters|null $params)
    {
        $this->model = $model;
        $this->fileFields = $fileFields;
        $this->params = $params;
    }

    public function __construct(AdvancedModel $model, array $fileFields = [] , Parameters|null $params = null)
    {
        $this->initialize($model, $fileFields ,$params);
    }

    public function CustomConditions(Request $request ,Builder $query):Builder{
        return $query;
    }

    public function getAll( Request $request, bool $reverse = false){
        $query = $this->model->query();
        if($this->params)$query = $this->params->injectParameters($request,$query);
        $query = $this->CustomConditions($request,$query);
        if($reverse)$query = $query->latest();
        return $query->get();
    }

    public function getPage( Request $request, $page=1 , $pageCap = 10,bool $reverse = false)
    {
        $query = $this->model->query();
        if($this->params)$query = $this->params->injectParameters($request,$query);
        $query = $this->CustomConditions($request,$query);
        if($reverse){
            $query = $query->latest();

        }
        $query = $query->oldest();
        return $query->paginate($pageCap, ['*'], 'page', $page);
    }
    public function getCollection(Request $request,$first , $limit = 10, bool $reverse = false,Parameters|null $params = null){

        $query = $this->model->query();
        if($first)$query = $query->where('id',$reverse?'<':'>' , $first);
        if($this->params)$query = $this->params->injectParameters($request,$query);
        $query = $this->CustomConditions($request,$query);
        if($reverse)$query = $query->latest();
        return $query->take($limit)->get();
    }
    public function find($id){
        return $this->model->find($id);
    }
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function delete(string|array $id)
    {
        if(is_array($id)){
            $oldData = $this->model->whereIn('id',$id)->get();
            foreach ($oldData as $item ){
                $this->removeFiles($item);
            }
            return $this->model->whereIn('id' , $id)->delete();
        }else {
            $oldData = $this->model->find($id);
            $this->removeFiles($oldData);

            return $this->model->where('id' , $id)->delete();
        }
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
        if(isset($request->reverse))$reverse = $request->reverse?true:false;
        else $reverse = false;
        if(isset($request->page)){
            $page = $request->page;
            $pageCap = $request->count??10;
            $articals = $this->getPage($request, $page , $pageCap, $reverse);
        }
        else if(isset($request->count) || isset($request->first)) {
            $count = $request->count??10;
            $first = $request->first??false;
            $articals = $this->getCollection($request, $first , $count , $reverse);
        }else $articals = $this->getAll($request, $reverse);
        return $articals;
    }

    public function show($id)
    {
        if (!is_numeric($id)) throw new \Exception('invalidData');
        return $this->find($id);
    }

    protected function insert(array $item){

        if($result = $this->model->create($item)){
            
            if(count($this->listedRelations)){
                foreach($this->listedRelations as $relation){
                    if(array_key_exists($relation->relation , $item)){
                        if(method_exists($this->model , $relation->relation)){
                            if(count($item[$relation->relation])){
                                foreach($item[$relation->relation] as &$i){
                                    $i[$relation->parent_key] = $result->id;
                                }
                                if( count($item[$relation->relation]) == 1 ){
                                    $relation->rep->store(reset($item[$relation->relation]), StoringMode::insert);
                                } else {
                                    $relation->rep->store(["list"=>$item[$relation->relation]], StoringMode::insert);
                                }
                            }else{
                                $method = $relation->relation;
                                $result->$method()->delete();
                            }
                        }
                    }

                }
            }
        }
        return $this->model->find($result->id);
    }

    protected function edit(array $newData){
        $old = $this->model->find($newData['id']);

        if($old){
            $oldData = $old->toArray();
            $isNew = false;
            foreach($oldData as $key=>$value){
                if(array_key_exists($key , $newData)){
                    if($value != $newData[$key])$isNew = true;
                }
            }

            $arr = $newData;
            foreach($this->listedRelations as $relation){
                if(array_key_exists($relation->relation , $arr))unset($arr[$relation->relation]);
            }

            $this->updateFiles($oldData , $arr );

            //dd();
            if($isNew){
                if(!$this->model->where('id',$newData['id'])->update($arr)){
                    //$this->recoverFiles($oldData , $arr );
                    throw new \Exception('unknown');
                }
            }

            if(count($this->listedRelations)){
                foreach($this->listedRelations as $relation){

                    if(array_key_exists($relation->relation, $oldData)) {
                        if(array_key_exists($relation->relation,$newData)){

                            $oldList = array_values($oldData[$relation->relation])??[];
                            $newList = array_values($newData[$relation->relation])??[];

                            foreach($newList as &$item){
                                $item[$relation->parent_key]= $old['id'];
                            }
                            if(count($oldList) || count($newList)){

                                $meth = $relation->relation;
                                $rel = $old->$meth();

                                if( $rel instanceof BelongsToMany){
                                    $deletes = array_values(array_diff(array_column(array_column($oldList,'pivot'),'id') , array_column($newList,'id')));
                                }else {
                                    $deletes = array_values(array_diff(array_column($oldList,'id') , array_column($newList,'id')));
                                }

                                if($rel instanceof HasOne){
                                    if(count($newList)){
                                        $relation->rep->store(reset($newList));
                                    }else if(count($deletes)) {
                                        $relation->rep->delete(reset($deletes));
                                    }
                                }else{
                                    if(count($deletes)){
                                        $relation->rep->delete($deletes);
                                    }
                                    if(count($newList)){

                                        $relation->rep->store(["list"=>$newList]);
                                    }
                                }
                            }
                        }else {
                            $oldList = array_values($oldData[$relation->relation])??[];
                            if($oldList){
                                $meth = $relation->relation;
                                $rel = $old->$meth();


                                if( $rel instanceof BelongsToMany){
                                    $deletes = array_values(array_column(array_column($oldList,'pivot'),'id'));
                                }else {
                                    $deletes = array_values(array_column($oldList,'id'));
                                }

                                if($rel instanceof HasOne){
                                    if(count($deletes)) {
                                        $relation->rep->delete(reset($deletes));
                                    }
                                }else{
                                    if(count($deletes)){
                                        $relation->rep->delete($deletes);
                                    }
                                }
                            }
                        }
                    }
                }
            }

        }
        return $this->model->find($old->id);
    }

/*
شغالة
    protected function insert(array $item){
        if($result = $this->model->create($item)){
            if(count($this->listedRelations)){
                foreach($this->listedRelations as $relation){
                    if(array_key_exists($relation->relation , $item)){
                        if(method_exists($result , $relation->relation)){
                            if(count($item[$relation->relation])){
                                $meth = $relation->relation;
                                $rel = $result->$meth();
                                if($rel instanceof HasOne){
                                    $rel->create($item[$relation->relation]);
                                }
                                else if($rel instanceof HasMany){
                                    $rel->createMany($item[$relation->relation]);
                                }else if($rel instanceof BelongsToMany){
                                    foreach($item[$relation->relation] as &$i){
                                        $i[$relation->parent_key]= $result->id;
                                    }
                                    DB::table($relation->table)->insert($item[$relation->relation]);
                                }
                            }
                        }
                    }
                }
            }
            return $result->refresh();
        }else {
            $this->recoverFiles(null,$item);
            throw new \Exception('unknown');
        }
    }
    protected function edit(array $newData)
    {
        $old = $this->model->find($newData['id']);
        $oldData = $old->toArray();
        if($oldData){
            $this->updateFiles($oldData , $newData );
            $isNew = false;
            foreach($oldData as $key=>$value){
                if(array_key_exists($key , $newData)){
                    if($value != $newData[$key])$isNew = true;
                }
            }
            if($isNew){
                $arr = $newData;
                foreach($this->listedRelations as $relation){
                    if(array_key_exists($relation->relation , $arr))unset($arr[$relation->relation]);
                }

                if(!$this->model->where('id',$oldData['id'])->update($arr)){

                    $this->recoverFiles($oldData , $newData );
                    throw new \Exception('unknown');
                }
            }
            if(count($this->listedRelations)){
                foreach($this->listedRelations as $relation){
                    if (array_key_exists($relation->relation, $oldData)) {
                        if(array_key_exists($relation->relation,$newData)){
                            $oldList = array_values($oldData[$relation->relation])??[];
                            $newList = array_values($newData[$relation->relation])??[];
                            if(count($oldList) || count($newList)){
                                $inserts = [];
                                $updates = [];
                                $meth = $relation->relation;
                                $rel = $old->$meth();

                                if( $rel instanceof BelongsToMany){
                                    array_map( function($item) use(&$inserts, &$updates){
                                        if(array_key_exists('id',$item))array_push($updates , $item);
                                        else array_push($inserts , $item);
                                    } , $newList);

                                    $inserts = array_values($inserts);
                                    $updates = array_values($updates);

                                    $deletes = array_values(array_diff(array_column(array_column($oldList,'pivot'),'id') , array_column($updates,'id')));
                                }else {
                                    array_map( function($item) use(&$inserts, &$updates){
                                        if(array_key_exists('id',$item))array_push($updates , $item);
                                        else array_push($inserts , $item);
                                    } , $newList);

                                    $inserts = array_values($inserts);
                                    $updates = array_values($updates);

                                    $deletes = array_values(array_diff(array_column($oldList,'id') , array_column($updates,'id')));
                                }

                                if($rel instanceof HasOne){
                                    if(count($updates)){
                                        $rel->update(reset($updates));
                                    }else if(count($inserts)){
                                        $rel->create(reset($inserts));
                                    }else if(count($deletes)) {
                                        $rel->delete(reset($deletes));
                                    }
                                }else if($rel instanceof HasMany){
                                    if(count($deletes)){
                                        $rel->whereIn('id',$deletes)->delete();
                                    }
                                    if(count($updates)){
                                        array_map(function($item) use (&$rel){
                                            $rel->where('id',$item['id'])->update($item);
                                        },$updates);
                                    }
                                    if(count($inserts)){
                                        $rel->createMany($inserts);
                                    }
                                }else if($rel instanceof BelongsToMany){
                                    if(count($deletes)>0){
                                        DB::table($relation->table)->whereIn('id',$deletes)->delete();
                                    }
                                    if(count($updates)){
                                        array_map(function($item) use (&$relation){
                                            DB::table($relation->table)->where('id',$item['id'])->update($item);
                                        },$updates);
                                    }
                                    if(count($inserts)){
                                        foreach($inserts as &$item){
                                            $item[$relation->parent_key]= $old->id;
                                        }

                                        DB::table($relation->table)->insert($inserts);
                                    }
                                }
                            }
                        }
                    } else {
                        throw new \Exception("The relation {$relation} does not exist.");
                    }
                }
            }
            else {
                $this->recoverFiles($old , $newData );
                throw new \Exception('unknown');
            }
        }
        return $old->refresh();
    }
*/
    protected function insertRange(array $list){
        $collection = [];
        if(count($list)){
            foreach($list as $item){
                array_push($collection,$this->insert($item));
            }
        }
        return array_values($collection);
    }
    public function updateRange(array $list){
        $collection = [];
        if(count($list)){
            foreach($list as $item){
                array_push($collection,$this->edit($item));
            }
        }
        return array_values($collection);
    }

    public function store(array $data , StoringMode $mode = StoringMode::general){
        if($mode == StoringMode::general){
            if(array_key_exists('list',$data)){
                $inserts = [];
                $updates = [];

                foreach($data['list'] as $item){
                    if(isset($item['id'])){
                        array_push($updates , $item);
                    }
                    else array_push($inserts , $item);
                }

                $inserts = $this->insertRange($inserts);

                $updates = $this->updateRange($updates);

                return array_merge($inserts , $updates);
            }else{
                if(isset($data['id'])){
                    return $this->edit($data);
                }else {
                    return $this->insert($data);
                }
            }
        }else if($mode == StoringMode::insert){
            if(array_key_exists('list',$data)){
                return  $inserts = $this->insertRange($data['list']);
            }else{
                return $this->insert($data);
            }
        }else {
            if(array_key_exists('list',$data)){
                $updates = [];
                foreach($data['list'] as $item){
                    if(isset($item['id'])){
                        array_push($updates , $item);
                    }
                }
                return $updates = $this->updateRange($updates);
            }else{
                if(isset($data['id'])){
                    return $this->edit($data);
                }
            }
        }

    }

    public function destroy($id):ProccessStatus
    {
        if($this->delete($id)){
            return new ProccessStatus(true , '');
        }else return new ProccessStatus(false , 'العنصر غير مسجل');
    }

    public function updateFiles($item,array $data ){
        if(!is_array($item))
        $item = $item->toArray();

        foreach($this->fileFields as $field => $value){
            if(count($item)>0){

                if(array_key_exists($field, $item) && array_key_exists($field, $data) ){
                    $oldFile = new FileService($item[$field],$value);
                    $newFile = new FileService($data[$field],$value);
                    if($oldFile->newfile){
                        if($oldFile->newfile->name != $newFile->newfile->name){
                            $oldFile->deleteFromPublic();
                        }
                    }

                }
            }
        }
    }
    public function removeFiles($item){
        if(!is_array($item))
        $item = $item->toArray();

        if(count($item)>0 && count ($this->fileFields)>0 ){
            foreach($item as $i => $i_value){
                if(is_array($i_value)){
                    $this->removeFiles($i_value);
                }else {
                    if(array_key_exists($i,$this->fileFields)){
                        $oldFile = new FileService($i_value,$this->fileFields[$i]);
                        $oldFile->deleteFromPublic();
                    }
                }
            }
        }

    }
    public function republishFiles($item){
        if($item){
            if(!is_array($item)){
                $item = $item->toArray();
            }
        }
        if(count($item)>0 && count ($this->fileFields)>0 ){
            foreach($this->fileFields as $field => $value){
                if(array_key_exists($field, $item)){
                    $File = new FileService($item[$field],$value);
                    $File->saveInPublic();
                }
            }
        }
    }
    public function recoverFiles($item,array $data){
        $item = $item?$item->toArray():[];
        foreach($this->fileFields as $field => $value){
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
