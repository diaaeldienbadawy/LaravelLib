<?php
namespace App\Lib\Http\HttpStructure;

use App\Lib\UtilitiesTypes\ProccessStatus;
use Illuminate\Http\Request;

interface IRepository{
    public function initialize(AdvancedModel $model , CustomValidator $validator );
    public function index(Request $request);
    public function show($id);
    public function store(Request $request);
    public function destroy($id):ProccessStatus;
}
