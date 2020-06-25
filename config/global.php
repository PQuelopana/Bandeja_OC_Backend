<?php
//use Illuminate\Support\Facades\Lang as LangGlobal;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$codeSuccess = 200;
$codeError = 400;
$codeNotFound = 404;

return [
    'codeSuccess'           => $codeSuccess,
    'codeError'             => $codeError,
    'codeNotFound'          => $codeNotFound,
    'dataSuccessNoMessage'  => [
        'code'                  => $codeSuccess
    ],
    'dataSuccessMessage'    => [
        'code'                  => $codeSuccess,
        'message'               => ''
    ],
    'dataErrorValidate'     => [
        'code'                  => $codeError,
        'errors'                => ''
    ],
    'dataErrorRequest'      => [
        'code'                  => $codeError,
        'message'               => ''//trans('validation.requestFailed')
    ],
    'dataErrorAuth'         => [
        'code'                  => $codeError,
        'message'               => ''//trans['auth.failed']
    ],
    'dataErrorNotFound'     => [
        'code'                  => $codeNotFound,
        'message'               => ''
    ],
    'accountstoreValidator'    => [
        'email'                 => 'required|email|unique:account',
        'name'                  => 'required',
        'password'              => 'required|is_alpha_num'
    ],
    'accountupdateValidator'   => [
        'name'                  => 'required',
        'email'                 => 'required|email|unique:account,email,',
    ],
    'accountloginValidator'    => [
        'email'                 => 'required|email'
    ],
    'userloginValidator'    => [
        'idAccount'             => 'required',
        'name'                  => 'required',
        'password'              => 'required'        
    ],
    'accountpasswordrestoreValidator'  => [
        'password'                      => 'required|is_alpha_num',
        'codeRestoration'              => 'required|numeric'
    ],
    'unitmeasurestoreValidator'    => [
        'idOfficial'                    => ['unique&idAccount:client1.unit_measure/', 'required'],
        'name'                          => ['unique&idAccount:client1.unit_measure/', 'required'],
        'abbreviation'                  => ['unique&idAccount:client1.unit_measure/', 'required'],
        'indActivated'                  => 'required'
    ],
    'unitmeasureupdateValidator'    => [
        'idOfficial'                    => ['unique&idAccount:client1.unit_measure/ignore#idRequest', 'required'],
        'name'                          => ['unique&idAccount:client1.unit_measure/ignore#idRequest', 'required'],
        'abbreviation'                  => ['unique&idAccount:client1.unit_measure/ignore#idRequest', 'required'],
        'indActivated'                  => 'required'
    ],
    'businessstoreValidator'    => [
        'identityDocumentNumber'        => ['unique&idAccount:client1.business/', 'required'],
        'name'                          => ['unique&idAccount:client1.business/', 'required']
    ],
    'businessupdateValidator'    => [
        'identityDocumentNumber'        => ['unique&idAccount:client1.business/ignore#idRequest', 'required'],
        'name'                          => ['unique&idAccount:client1.business/ignore#idRequest', 'required']
    ],
    'productstoreValidator'    => [
        'idInternal'                    => ['unique&idBusiness:client1.product/', 'required'],
        'name'                          => ['unique&idBusiness:client1.product/', 'required'],
        'idUnitMeasure'                 => 'required',
        'stockMinimun'                  => 'required'        
    ],
    'productupdateValidator'    => [
        'idInternal'                    => ['unique&idBusiness:client1.product/ignore#idRequest', 'required'],
        'name'                          => ['unique&idBusiness:client1.product/ignore#idRequest', 'required'],
        'idUnitMeasure'                 => 'required',
        'stockMinimun'                  => 'required'        
    ],
    'kardexstoreValidator'    => [
        'idWarehouse'                    => 'required',
        'idKardexMotif'                  => 'required',
        //'idInternal'                     => 'unique&idWareHouse:client1.kardex/', 'required',
        'date'                           => 'required',
        'hour'                           => 'required'
    ],
    'kardexupdateValidator'    => [
        'idWarehouse'                    => 'required',
        'idKardexMotif'                  => 'required',
        //'idInternal'                     => ['unique&idWareHouse:client1.kardex/ignore#idRequest', 'required'],
        'date'                           => 'required',
        'hour'                           => 'required'
    ],
    'kardexstoredetailvalidator'    => [
        'item'                              => 'required',
        'idProduct'                         => 'required',
        'quantity'                          => 'required',
        'unitPrice'                         => 'required',
        'totalPrice'                        => 'required'
    ],
    'kardexupdatedetailvalidator'    => [
        'item'                              => 'required',
        'idProduct'                         => 'required',
        'quantity'                          => 'required',
        'unitPrice'                         => 'required',
        'totalPrice'                        => 'required',
    ],
    'warehousestoreValidator'    => [
        'idEstablishment'               => 'required',
        'name'                          => ['unique&idEstablishment:client1.warehouse/', 'required']
    ],
    'warehouseupdateValidator'    => [
        'idEstablishment'               => 'required',
        'name'                          => ['unique&idEstablishment:client1.warehouse/ignore#idRequest', 'required']
    ],
    'kardexmotifstoreValidator'    => [
        'type'                          => 'required',
        'name'                          => ['unique&idAccount:client1.kardex_motif/', 'required']
    ],
    'kardexmotifupdateValidator'    => [
        'type'                          => 'required',
        'name'                          => ['unique&idAccount:client1.kardex_motif/ignore#idRequest', 'required']
    ],
    'establishmentstoreValidator'    => [
        'name'                          => ['unique&idBusiness:client1.establishment/', 'required'],
    ],
    'establishmentupdateValidator'    => [
        'name'                          => ['unique&idBusiness:client1.establishment/ignore#idRequest', 'required'],
    ],
    
    
];