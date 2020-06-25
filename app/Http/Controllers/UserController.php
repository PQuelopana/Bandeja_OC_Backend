<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use App\User As ObjectModel;

use function App\Helpers\requestJsonDecodeArr;
use function App\Helpers\responseApi;
use function App\Helpers\validateObject;
use function App\Helpers\validateObjectAuth;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $jwtAuth = new JwtAuth();

        $paramArr = requestJsonDecodeArr($request);      
                
        $login = $jwtAuth->login('01', $paramArr['varusuario'], $paramArr['varpass']);

        $data= array_add(config('global.dataSuccessNoMessage'), 'login', $login);
        
        return responseApi($data);
    }

    public function getUserAuth($idempresa, $varusuario, $varpass)
    {
        $object = ObjectModel::where([
            'idempresa'     => $idempresa,
            'varusuario'    => $varusuario,
            'varpass'       => $varpass
        ])->first();

        validateObjectAuth($object, 'user');
        
        return $object;
    }
}
