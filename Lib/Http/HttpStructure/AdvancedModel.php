<?php
namespace App\Lib\Http\HttpStructure;

use App\Lib\UtilitiesTypes\Parameters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Helpers\SitemapGenerator;

abstract class AdvancedModel extends Model
{
    use HasFactory;
    public $isSiteMap = false;
    
    public function updateSiteMap(){
        if($this->isSiteMap)SitemapGenerator::generate();
    }
    //public array $listedRelations = [];
    public function getAll(Parameters|null $params = null,bool $reverse = false){
        $query = $this;
        if($params)$query = $params->ConcParameters($query);
        if($reverse)$query = $this->latest();
        return $query->get();
    }

    public function getCollection($first , $limit = 10 ,Parameters|null $params = null, bool $reverse = false){
        $query = $this;
        if($first)$query = $query->where('id',$reverse?'<':'>' , $first);
        if($params)$query = $params->ConcParameters($query);
        if($reverse)$query = $this->latest();
        return $query->take($limit)->get();
    }

    public function getPage($page=1 , $pageCap = 10 ,Parameters|null $params = null, bool $reverse = false){
        $query = $this;
        if($params)$query = $params->ConcParameters($query);
        if($reverse)$query = $this->latest();
        return $query->paginate($pageCap, ['*'], 'page', $page);
    }

    public function getOne($id,Parameters $params = null){
        $query = $this;
        if($params)$query = $params->ConcParameters($query);
        return $query->find($id);
    }
}

