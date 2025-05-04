<?php

namespace App\Lib\Http\HttpStructure\Middlewares;

use App\Lib\Encription;
use App\Lib\Lib;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use \Closure;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, \Closure $next): Response
    {
        try{
            if($request->method() == 'GET')return $next($request);
            if($id = Encription::Validate($request->bearerToken())){
                return $next($request);
            }
            return Lib::returnError('expiredToken');
        }catch(\Exception $e){
            return Lib::returnError('expiredToken');
        }
        /*try{
            $token = JWTAuth::parseToken()->getToken();
            $user =  JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return $this->returnError('invalidAuthToken');
            }
            app()->instance('verifiedUser', $user);
            return $next($request);
        }catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            list($header, $payload, $signature) = explode (".", $token);
            $payload =base64_decode($payload);
            $payload = json_decode($payload);
            $user = User::find($payload->sub);
            $tokenType = $payload->token_type ?? '';

            switch ($tokenType){
                case 'acs' :{ return $this->returnError('expiredAccessToken'); break; }
                default :{ return $this->returnError('expiredToken'); break; }
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return $this->returnError('invalidAuthToken');
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            //لازم نشيل دي لما نخلص تطوير
            return $next($request);
            return $this->returnError('invalidAuthToken');
        }catch(\Exception $e){
            return ['messege'=>$e->getMessage()];
        }*/

    }
}







?>
