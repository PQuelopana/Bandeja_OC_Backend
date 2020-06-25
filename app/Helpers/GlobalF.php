<?php
namespace App\Helpers;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\System\AccountController;
use App\Http\Controllers\System\HostNameController;
use App\Http\Controllers\System\UnitMeasureController;
use App\Http\Controllers\System\KardexMotifController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\EstablishmentController;
use App\Http\Controllers\WarehouseController;

use App\Http\Controllers\UserController;
// use App\Helpers\JwtAuth;
use Illuminate\Validation\Rule;

// class GlobalF{
    
//     function __construct(){
//     }
    
    function validateObject($object, $objectName, $message = '', $key = ''){
        validateObjectConstruct($object, $objectName, 'dataErrorNotFound', $message, $key);
    }
    
    function validateObjectAuth($object, $objectName){
        validateObjectConstruct($object, $objectName, 'dataErrorAuth');
    }
    
    function validateObjectConstruct($object, $objectName, $configGlobal, $message = '', $key = ''){
        
        //if(!is_object($object) || (!key_exists($key, $object) && $key != '')){        
        if(!is_object($object)){
            $data = config('global.'.$configGlobal);
            if($message == ''){
                $data['message'] = str_replace(':object', $objectName, $data['message']);
                $data['message'] = str_replace(':key', trans('objects.'.$key), $data['message']);
            }else{
                $data['message'] = $message;
            }
        }
        
        if(isset($data)) responseHttpExceptionApi($data);
    }
    
    function requestJsonDecodeArr($request){
        $json = $request->input('json', null);
        
        if(!is_null($json)){
            $paramArr = json_decode($json, true);            
            if(!is_array($paramArr)){
                //$paramArr = array_map('trim', $paramArr);
            //}else{
                $data = config('global.dataErrorRequest');
                $data['message'] = trans('messages.json');
                responseHttpExceptionApi($data);
            }
        }else{
            $paramArr = null;
        }
        
        return $paramArr;
    }
    
    function responseApi($data){
        return response()->json($data, $data['code']);
    }
    
    function responseHttpExceptionApi($data){
        throw new HttpResponseException(response()->json($data, $data['code']));
    }
    
    function getIdentity($request){
        $jwtAuth = new JwtAuth();
        
        $token = getTokenFromRequest($request);
        
        $user = $jwtAuth->checkToken($token, true);
        
        return $user;
    }
    
    function getTokenFromRequest($request){
        return getOfAuthorizationFromRequest($request, 'token');
    }
    
    function getOfAuthorizationFromRequest($request, $get){
    
        $authorization = $request->header('Authorization', null);
        
        if(!is_null($authorization)){
            $authorization = json_decode($authorization, true);           
            if(!is_array($authorization)){
                $authorization = null;
            }
        }else{
            $authorization = null;
        }                
        
        if(is_null($authorization)){
            $data = null;
        }else{
            if(array_key_exists($get, $authorization)){
                $data = $authorization[$get];
            }else{
                $data = null;
            }                
        }
        
        return $data;
    }
    
    // function getIdBusinessFromRequest($request){
    //     return getOfAuthorizationFromRequest($request, 'idBusiness');
    // }
    
    function getObjectAndActionOfRequest($request){
        $objectAction = Route::getCurrentRoute()->getActionName();
        
        $slash = strrpos($objectAction, '\\');
        if($slash > 0){
            $objectAction = strtolower(str_replace('Controller', '', substr($objectAction, $slash + 1, strlen($objectAction))));
        }        
        
        return str_replace('@', '', $objectAction);
    }        
    
    function generateCodeRandom(){
        return mt_rand(1, 9999);
    }
    
    // function getAccountAuth($email, $password){
    //     $accountController = new AccountController();
    //     return $accountController->getAccountAuth($email, $password);
    // }
    
    function getUserAuth($idempresa, $varusuario, $varpass){
        $userController = new UserController();
        return $userController->getUserAuth($idempresa, $varusuario, $varpass);
    }
    
    // function getHostNameGeneral(){
    //     $hostNameController = new HostNameController();
        
    //     $url = env('ENVIROMENT') == 'DEV' ? 'client1.erpbackend.com.devel' : 'urlproduccion';
        
    //     return $hostNameController->getByfqdn($url);
    // }
    
    // function saveTablesNewAccount($idAccount){
    //     $unitMeasureController = new UnitMeasureController();
    //     $unitMeasureController->newAccount($idAccount);
                
    //     $kardexMotifController = new KardexMotifController();
    //     $kardexMotifController->newAccount($idAccount);
        
    //     $businessController = new BusinessController();
    //     $idBusiness = $businessController->newAccount($idAccount);
        
    //     $establishmentController = new EstablishmentController();
    //     $idEstablishment = $establishmentController->newAccount($idBusiness);
        
    //     $warehouseController = new WarehouseController();
    //     $warehouseController->newAccount($idEstablishment);
    // }
    
    // function storeNewUser($paramArr){
    //     $userController = new UserController();
    //     $userController->newAccountUser($paramArr);
    // }
    
    function ruleConfig($rules, $request)
    {
        if($rules == '')
        {
            $data = config('global.dataErrorNotFound');
            $data['message'] = 'Configurar Reglas de Validación.';
            
            return responseHttpExceptionApi($data);
        }
        
        foreach($rules as $key => $value) 
        {
            if(is_array($rules[$key]))
            {
                foreach($rules[$key] as $key2 => $value2) 
                {
                    
                    $ruleOri = $rules[$key][$key2];
                    
                    $rules[$key][$key2] = ruleUniqueAddConditions($rules[$key][$key2], $request);
                    
                    $rules[$key][$key2] = ruleUniqueAddIgnore($rules[$key][$key2], $ruleOri, $request);
                }                
            }
        }
        
        return $rules;
    }    
    
    function ruleUniqueAddConditions($rule, $request)
    {    
        $pos = strpos($rule, 'unique&');
        if($pos !== false)
        {
            $posSF = strpos($rule, '&') + 1;
            $posEF = strpos($rule, ':') - $posSF;
            $field = substr($rule, $posSF, $posEF);

            $posST = strpos($rule, ':') + 1;
            $posET = strpos($rule, '/') - $posST;
            $table = substr($rule, $posST, $posET);
            
            $rule = Rule::unique($table)->where(function ($query) use($request, $field) 
            {                
                if($field == 'idAccount'){//Si quieres validar que la regla sea unica en base a un dato enviado desde el front, puedes agregarlo aquí y dentro del if la lógica para recuperar el dato del request enviado por el front
                    // $idField = getIdentity($request)->$field;
                }else if($field == 'idBusiness'){//Si quieres validar que la regla sea unica en base a un dato enviado desde el front, puedes agregarlo aquí y dentro del if la lógica para recuperar el dato del request enviado por el front
                    // $idField = getIdBusinessFromRequest($request);                   
                }else{
                    $paramArr = requestJsonDecodeArr($request);
                    $idField = $paramArr[$field];
                }
                
                return $query->where($field, $idField);
            });
            
        }
        
        return $rule;
    }
    
    function ruleUniqueAddIgnore($rule, $ruleOri, $request){
        
        $pos = strpos($ruleOri, 'ignore#');
        if($pos !== false){
            $posSF = strpos($ruleOri, '#') + 1;
            $posEF = strlen($ruleOri) - $posSF + 1;
            $field = substr($ruleOri, $posSF, $posEF);

            if($field == 'idRequest'){
                $path = $request->path();
                
                $posSIF = strripos($path, '/') + 1;
                $posEIF = strlen($path) - $posSIF + 1;
                $idField = substr($path, $posSIF, $posEIF);
                
            }
            
            $rule = $rule->ignore($idField);
        }
        
        return $rule;
    }
    
    function getValParametro($objectModel, $idEmpresa, $glsParametro)
    {
        $valParametro = $objectModel::where([
            'idEmpresa'     => $idEmpresa,
            'GlsParametro'  => $glsParametro
        ])->first()->ValParametro;

        return $valParametro;
    }
// }