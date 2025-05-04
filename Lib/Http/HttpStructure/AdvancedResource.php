<?php
namespace App\Lib\Http\HttpStructure;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use \Illuminate\Support\Collection;

abstract class AdvancedResource extends JsonResource{

    protected function paginationResource($item):array{
        return $item->toArray();
    }
    protected function collectionResource($item):array{
        return $item->toArray();
    }
    protected function arrayResource($item):array{
        return $item;
    }
    protected function singleResource($item):array{
        return $item->toArray();
    }


    public function toArray($request)
    {
        if ($this->resource instanceof LengthAwarePaginatoR) {
            $collection = $this->resource->getCollection();

            return [
                'data' => $collection->map(function($item){ return $this->paginationResource($item);} ),
                'pagination' => [
                    'total' => $this->resource->total(),
                    'count' => $this->resource->count(),
                    'per_page' => $this->resource->perPage(),
                    'current_page' => $this->resource->currentPage(),
                    'total_pages' => $this->resource->lastPage(),
                ]
            ];
        }
        else if($this->resource instanceof Collection){
            $collection = $this->resource;
            return $collection->map(function($item){ return $this->collectionResource($item); });
        }
        else if(is_array($this->resource)){
            try{
                return array_map(function($item){return is_array($item)?$this->arrayResource($item):$this->singleResource($item); }, $this->resource);
            }catch(\Exception $e){
                return $this->singleResource($this->resource);
            }
        }
        else {
            return $this->singleResource($this->resource);
        }
    }
}
