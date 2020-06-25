<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Iluminate\Suport\Facades\DB;

class JwtAuth{
    public $key;
    
    public function __construct(){
        $this->key = 'kldfg567f65fd456fdg46545@@@@<<>>Z<Z<z!!!*/-/++8-*1-*11+1';
    }
    
    public function login($idempresa, $varusuario, $varpass){        
        //Buscar si existe el usuario con sus credenciales
        $user = getUserAuth($idempresa, $varusuario, $varpass);
        //echo $user; die();
        //Generar el token con los datos del usuario identificado

        // echo $user; die();

        $signup = [
            'idUsuario'     => $user->idUsuario,
            'idEmpresa'     => $user->idEmpresa,
            'varUsuario'    => $user->varUsuario,
            'iat'           => time(),
            'exp'           => time() + (7 * 24 * 60 * 60) //Una semana de duracion
        ];

        $token = JWT::encode($signup, $this->key, 'HS256');        
        
        $data = $signup;
        $data['token'] = $token;
        
        return $data;         
    }
    
    public function checkToken($jwt, $getIdentity = false){
        $auth = false;
        
        try{
            $jwt = str_replace('"', '', $jwt);            
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $e) {            
            $decoded = 'Token expirado o incorrecto';
        } catch (\DomainException $e){            
            $decoded = 'Token expirado o incorrecto';
        }
        
        if(!empty($decoded) && is_object($decoded) && isset($decoded->idUsuario)) $auth = true;
        
        if($getIdentity){
            $data = $decoded;
        }else{
            $data = $auth;
        }
        
        return $data;            
    }
}