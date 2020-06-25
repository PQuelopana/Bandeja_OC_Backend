<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Throwable;
use function App\Helpers\validateObject;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function indexController(
        $objectModel, 
        $object_p, 
        $loads = '', 
        $filter = null/*['campo' => 'valor']*/, 
        $orderBy = null
    ){
        
        if(is_null($orderBy)){
            $orderBy = [
                $objectModel->getTable().'.id',
                'Asc'
            ];
        }
        
        if(is_array($filter)){
            $object = $objectModel::where($filter)->orderBy($orderBy[0], $orderBy[1]);
        }else{
            $object = $objectModel::orderBy($orderBy[0], $orderBy[1]);
        }
        
        $object = $this->loads($object, $loads, $objectModel);
        
        $data = array_add(config('global.dataSuccessNoMessage'), $object_p, $object);
        
        return $this->responseApi($data);
    }
    
    public function showControllerById(
        $request, $id, $objectModel, $object_s, $objectName, $loads = '', $filterAccountId = false, 
        $filterBusinessId = false
    ){
        $object = $this->getObjectById($id, $objectModel);                

        return $this->showController(
            $request, 'id', $object, $objectModel, $object_s, $objectName, $loads, $filterAccountId, $filterBusinessId
        );
    }
    
    public function showControllerByOther(
        $request, $where, $objectModel, $object_s, $objectName, $loads = '', $filterAccountId = false, 
        $filterBusinessId = false
    ){
        $object = $this->getObjectByOther($where, $objectModel);
        return $this->showController(
            $request, array_keys($where)[0], $object, $objectModel, $object_s, $objectName, $loads, $filterAccountId, 
            $filterBusinessId
        );
    }
    
    public function showController(
        $request, $key, $object, $objectModel, $object_s, $objectName, $loads = '', $filterAccountId = false, 
        $filterBusinessId = false
    ){
        
        $objectValidate = $object->first();
        validateObject($objectValidate, $objectName, '', $key);
        
        $objectWhere = $object;
        
        if($filterAccountId) $this->objectValidateByAccount($request, $objectWhere, $objectName, $objectModel);
        if($filterBusinessId) $this->objectValidateByBusiness($request, $objectWhere, $objectName, $objectModel);
                
        $object = $this->loads($object, $loads, $objectModel, true);
        
        $data = array_add(config('global.dataSuccessNoMessage'),$object_s, $object);
        
        return $this->responseApi($data);
    }
    
    public function getObjectById($id, $objectModel){
        return $objectModel::where($objectModel->getTable().'.id', $id);
    }
    
    public function getObjectByOther($where, $objectModel){
        return $objectModel::where($where);
    }
            
    public function getAllController($objectModel){
        return $objectModel::all();
    }
    
    public function storeController(
        $request, $objectModel, $object_s, $objectNameArr, 
        $fieldsAddStore = null/*['campo1' => 'valor1', 'campo2' => 'valor2']*/,
        $objectModelDetail = null, 
        $columnIdForeignKey = '',
        $objectModelClone = null
    ){
        $paramArr = $this->requestJsonDecodeArr($request);
        
        $object = $objectModel;
        
        if(!is_null($fieldsAddStore)){
            foreach($fieldsAddStore as $key => $value) {
                // if($key == 'idAccount'){
                //     $paramArr[$key] = $this->getIdentity($request)->idAccount;
                // }else if($key == 'idBusiness'){
                //     $paramArr[$key] = $this->getIdBusinessFromRequest($request);
                // }else{
                    $paramArr[$key] = $value;
                // }
            }
        }
        
        try{
            DB::beginTransaction();
            $object->newObject($paramArr);
            $object->save();
            
            $this->saveModelClone($objectModelClone, $object);
            
            // if($object_s == 'account'){
            //     $this->saveTablesNewAccount($object->id);
            //     $paramArr['idAccount'] = $object->id;
            //     $this->storeNewUser($paramArr);
            // }
            
            $data = array_add(config('global.dataSuccessMessage'), $object_s, $object);

            if(!is_null($objectModelDetail)){
                $data['details'] = $this->saveDetails(
                    $object->id, $paramArr['details'], $objectModelDetail, $columnIdForeignKey
                );
            }
            
            DB::commit();            
        } catch (Throwable $e){
            DB::rollback();
        }
        
        $data['message'] = trans('messages.store', $objectNameArr);
        return $this->responseApi($data);
    }
    
    public function saveModelClone($objectModelClone, $object){
        if(!is_null($objectModelClone)){
            $objectClone = $objectModelClone;            
            $objectClone->newObject($object->toArray());
            $objectClone->save();
        }
    }
    
    // public function objectValidateByBusiness(
    //     $request, $objectWhere, $objectName, $objectModel
    // ) {
    //     $idBusiness = $this->getIdBusinessFromRequest($request);
        
    //     if($objectModel->indBusiness){
    //         $objectWhereFilterBusiness = $objectWhere->where('idBusiness', $idBusiness);
    //     }else{
    //         $objectWhereFilterBusiness = $objectModel->objectByIdBusiness($objectWhere, $idBusiness);
    //     }
              
    //     $object = $objectModel->getColumnsJoin($objectWhereFilterBusiness, true);
        
    //     $this->validateObject($object, $objectName, trans('messages.notFoundByBusiness', ['object' => $objectName]));        
    // }
    
    public function updateController(
        $id, $request, $objectModel, $objectName, $object_s, $objectNameArr, 
        $filterAccountId = false, 
        $objectModelDetail = null, 
        $columnIdForeignKey = '',
        $filterBusinessId = false
    ) {
        $objectWhere = $objectModel::where($objectModel->getTable().'.id', $id);
        $object = $objectWhere->first();
        $this->validateObject($object, $objectName, '', 'id');
        
        $changes = $objectModel->editObject($object, $this->requestJsonDecodeArr($request));
                    
        if($filterAccountId) $this->objectValidateByAccount($request, $objectWhere, $objectName, $objectModel);
        if($filterBusinessId) $this->objectValidateByBusiness($request, $objectWhere, $objectName, $objectModel);
        
        try{
            DB::beginTransaction();
            $objectWhere->update($changes);

            $data = array_add(config('global.dataSuccessMessage'), $object_s, $object);
            $data['changes'] = $changes;

            if(!is_null($objectModelDetail)){
                $paramArr = $this->requestJsonDecodeArr($request);
                $data['details'] = $this->saveDetails($object->id, $paramArr['details'], $objectModelDetail, $columnIdForeignKey, true);
            }
            
            DB::commit();            
        } catch (Throwable $e){
            DB::rollback();
        }
        
        $data['message'] = trans('messages.update', $objectNameArr);
        
        return $this->responseApi($data);
    }
    
    public function destroyController(
        $id, $request, $objectModel, $objectName, $object_s, $objectNameArr, $filterAccountId = false, 
        $objectModelDetail = null, $columnIdForeignKey = '', $filterBusinessId = false
    ) {
        $objectWhere = $objectModel::where($objectModel->getTable().'.id', $id);
        $object = $objectWhere->first();
        $this->validateObject($object, $objectName, '', 'id');
        
        if($filterAccountId) $this->objectValidateByAccount($request, $objectWhere, $objectName, $objectModel);
        if($filterBusinessId) $this->objectValidateByBusiness($request, $objectWhere, $objectName, $objectModel);
        
        try{
            DB::beginTransaction();
            if(!is_null($objectModelDetail)){
                $objectModelDetail::where($columnIdForeignKey, $id)->delete();
            }

            $objectWhere->delete();
            DB::commit();            
        } catch (Throwable $e){
            DB::rollback();
        }
        
        $data = array_add(config('global.dataSuccessMessage'), $object_s, $object);
        $data['message'] = trans('messages.destroy', $objectNameArr);
        
        return $this->responseApi($data);
    }
    
    public function objectValidateByOther(
        $validate, $request, $objectWhere, $objectModel
    ) {
        list($key, $value) = array_divide($validate);

        $objectWhereFilter = $objectWhere->where($key, $value);

        $object = $objectModel->getColumnsJoin($objectWhereFilter, true);

        $objectName = $objectModel->getTable();

        $this->validateObject($object, $objectName, trans('messages.notFoundByAccount', ['object' => $objectName]));        
    }

    // public function objectValidateByAccount(
    //     $request, $objectWhere, $objectName, $objectModel
    // ) {
    //     $user = $this->getIdentity($request);       
        
    //     if($objectModel->indAccount){
    //         $objectWhereFilterAccount = $objectWhere->where('idAccount', $user->idAccount);
    //     }else{
    //         $objectWhereFilterAccount = $objectModel->objectByIdAccount($objectWhere, $user->idAccount);
    //     }
        
    //     $object = $objectModel->getColumnsJoin($objectWhereFilterAccount, true);

    //     $this->validateObject($object, $objectName, trans('messages.notFoundByAccount', ['object' => $objectName]));        
    // }
    
    public function loads($object, $loads, $objectModel, $indShow = false)
    {
        if($loads !== ''){
            foreach($loads as $load){
                $object = $objectModel->$load($object);
            }                    
        }
        
        $object = $objectModel->getColumnsJoin($object, $indShow);
            
        return $object;
    }
    
    public function saveDetails($id, $paramDetailArr, $objectModelDetail, $columnIdForeignKey, $deleteDetail = false){
        try{
            if($deleteDetail){
                $objectModelDetail::where($columnIdForeignKey, $id)->delete();
            }

            $index = 0;
            foreach ($paramDetailArr as $detail){
                $detail[$columnIdForeignKey] = $id;
                $objectDetail = new $objectModelDetail;
                $objectDetail->newObject($detail);
                $objectDetail->save();
                $detailsSave[$index] = $objectDetail;
                $index++;
            }
            return $detailsSave;
        } catch (Throwable $e){
            DB::rollback();
        }
    }
    
    // public function newAccountController($idAccount, $objectModel, $objectClient) {
    //     $objects = $this->getAll(
    //         $objectModel
    //     );
        
    //     foreach($objects as $object){
    //         $objectClient = new $objectClient;
            
    //         $paramArr = array_add($object->toArray(), 'idAccount', $idAccount);
    //         $objectClient->newObject($paramArr);
    //         $objectClient->save();
    //     }        
        
    // }
}
