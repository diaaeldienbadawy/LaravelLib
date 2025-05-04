<?php

namespace App\Lib\Http\HttpStructure;

use App\Http\Controllers\Controller;
use App\Lib\Encription;
use App\Lib\Http\Exceptions\CustomException;
use App\Lib\Http\HttpStructure\Exceptions\CustomValidationException;
use App\Lib\Http\HttpStructure\Middlewares\AuthMiddleware;
use App\Lib\Lib;
use App\Lib\UtilitiesTypes\Parameters;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Termwind\Components\Li;

class RecourceController extends Controller
{
    //abstract public function index(Request $request);
    protected AdvancedRepository $repository;
    protected JsonResource $resource;
    protected CustomValidator $validator;
    protected Parameters $params;



    public function auth(Request $request, $next)
    {
        try {
            if (Encription::Validate($request->bearerToken())) {
                return $next($request);
            }
            return Lib::returnError('expiredToken');
        } catch (\Exception $e) {
            return Lib::returnError('expiredToken');
        }
    }
    public function index(Request $request)
    {
        return Lib::returnData($this->resource->make($this->repository->index($request)));
    }

    public function show($id)
    {
        if (!is_numeric($id)) return Lib::returnError('invalidData');
        try {
            return Lib::returnData($this->resource->make($this->repository->show($id)));
        } catch (CustomValidationException $e) {
            return $e->getResponse();
        } catch (ValidationException $e) {
            return Lib::returnGeneralError($e->getMessage(), 'RV0000');
        } catch (ModelNotFoundException $e) {
            return Lib::returnGeneralError('العنصر غير مسجل', 'MNF0000');
        } catch (QueryException $e) {
            if ($e->errorInfo[0] == '45000') return Lib::returnError($e->errorInfo[2]);
            else return Lib::returnError('unknown');
        } catch (CustomException $e) {
            return $e->getResponse();
        } catch (Exception $e) {
            return Lib::returnGeneralError($e->getMessage(), '');
        }
    }

    public function store(Request $request)
    {
        try {
            
            $result = $this->repository->store($this->validator->validate($request));
            if ($result) {
                $this->repository->model->updateSiteMap();
                if (is_bool($result)) {
                    return Lib::returnSuccessMessage();
                } else return Lib::returnData($this->resource->make($result));
            }
        } catch (CustomValidationException $e) {
            return $e->getResponse();
        } catch (ValidationException $e) {
            return Lib::returnGeneralError($e->getMessage(), 'RV0000');
        } catch (ModelNotFoundException $e) {
            return Lib::returnGeneralError('العنصر غير مسجل', 'MNF0000');
        } catch (QueryException $e) {
            if ($e->errorInfo[0] == '45000') return Lib::returnError($e->errorInfo[2]);
            else  { return $e->getMessage();  return Lib::returnError('unknown'); }
        } catch (CustomException $e) {
            return $e->getResponse();
        } catch (Exception $e) {
            return Lib::returnGeneralError($e->getMessage(), '');
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = $this->repository->destroy($id);
            if ($deleted->status) {
                $this->repository->model->updateSiteMap();
                return Lib::returnSuccessMessage();
            } else return Lib::returnError($deleted->messege);
        } catch (CustomValidationException $e) {
            return $e->getResponse();
        } catch (ValidationException $e) {
            return Lib::returnGeneralError($e->getMessage(), 'RV0000');
        } catch (ModelNotFoundException $e) {
            return Lib::returnGeneralError('العنصر غير مسجل', 'MNF0000');
        } catch (QueryException $e) {
            return Lib::returnError('unknown');
        } catch (CustomException $e) {
            return $e->getResponse();
        } catch (Exception $e) {
            return Lib::returnGeneralError($e->getMessage(), '');
        }
    }
}
