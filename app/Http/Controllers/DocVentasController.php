<?php
// declare(strict_types=1);

namespace App\Http\Controllers;

use App\DocReferencia;
use Illuminate\Http\Request;
use App\DocVentas As ObjectModel;
use App\DocVentasDet;
use App\ParametrosCompras;
use Carbon\Carbon;

use function App\Helpers\getIdentity;
use function App\Helpers\getValParametro;
use function App\Helpers\requestJsonDecodeArr;
use function App\Helpers\responseApi;

class DocVentasController extends Controller
{
    public function listado_por_periodo_y_estado(Request $request, $periodo, $estado, $filtroRango, $textoBusqueda)
    {                
        $timeI = Carbon::now();
        $timeI = $timeI->toTimeString();

        $DocVentas = new ObjectModel();        
        $tableDocVentas = $DocVentas->getTable();

        $DocVentasDet = new DocVentasDet();        
        $tableDocVentasDet = $DocVentasDet->getTable();

        $textoBusqueda = $textoBusqueda == 'null' ? '' : $textoBusqueda;

        $user = getIdentity($request);

        $idEmpresa = $user->idEmpresa;
        $idUsuario = $user->idUsuario;

        $montoAprobacion1 = (int)getValParametro(new ParametrosCompras(), $idEmpresa, 'VALIDA_APROBACION_OC_MONTO1_2020');
        
        $filtroRangoEstadoArr = [];

        if($montoAprobacion1 > 0)
        {
            if((int)$filtroRango == 1)
            {
                $montoAprobacion2 = (int)getValParametro(new ParametrosCompras(), $idEmpresa, 'VALIDA_APROBACION_OC_MONTO2_2020');
                $montoAprobacion3 = (int)getValParametro(new ParametrosCompras(), $idEmpresa, 'VALIDA_APROBACION_OC_MONTO3_2020');
                $montoAprobacion4 = (int)getValParametro(new ParametrosCompras(), $idEmpresa, 'VALIDA_APROBACION_OC_MONTO4_2020');

                $filtro = $this->listaOCPorRango($idEmpresa, '1', $estado, $idUsuario, $montoAprobacion1);
                if($filtro != '') array_push($filtroRangoEstadoArr, $filtro);

                $filtro = $this->listaOCPorRango($idEmpresa, '2', $estado, $idUsuario, $montoAprobacion2, $montoAprobacion3);
                if($filtro != '') array_push($filtroRangoEstadoArr, $filtro);

                $filtro = $this->listaOCPorRango($idEmpresa, '3_1', $estado, $idUsuario, $montoAprobacion3, $montoAprobacion4);
                if($filtro != '') array_push($filtroRangoEstadoArr, $filtro);

                $filtro = $this->listaOCPorRango($idEmpresa, '3_2', $estado, $idUsuario, $montoAprobacion3, $montoAprobacion4);
                if($filtro != '') array_push($filtroRangoEstadoArr, $filtro);

                $filtro = $this->listaOCPorRango($idEmpresa, '4_1', $estado, $idUsuario, $montoAprobacion4);
                if($filtro != '') array_push($filtroRangoEstadoArr, $filtro);

                $filtro = $this->listaOCPorRango($idEmpresa, '4_2', $estado, $idUsuario, $montoAprobacion4);
                if($filtro != '') array_push($filtroRangoEstadoArr, $filtro);

                $filtro = $this->listaOCPorRango($idEmpresa, '5_1', $estado, $idUsuario);
                if($filtro != '') array_push($filtroRangoEstadoArr, $filtro);

                $filtro = $this->listaOCPorRango($idEmpresa, '5_2', $estado, $idUsuario);
                if($filtro != '') array_push($filtroRangoEstadoArr, $filtro);

                $filtro = $this->listaOCPorRango($idEmpresa, '6_1', $estado, $idUsuario);
                if($filtro != '') array_push($filtroRangoEstadoArr, $filtro);

                $filtro = $this->listaOCPorRango($idEmpresa, '6_2', $estado, $idUsuario);
                if($filtro != '') array_push($filtroRangoEstadoArr, $filtro);

                $filtro = $this->listaOCPorRango($idEmpresa, '7_1', $estado, $idUsuario);
                if($filtro != '') array_push($filtroRangoEstadoArr, $filtro);

                $filtro = $this->listaOCPorRango($idEmpresa, '7_2', $estado, $idUsuario);
                if($filtro != '') array_push($filtroRangoEstadoArr, $filtro);
                
            }else
            {
                switch ($estado) {
                    case 'P':

                        $filtroRangoEstado = '('.$tableDocVentas.'.indAprobado = "0" Or IfNull('.$tableDocVentas.'.indAprobado, "") = "")';
                    break;
                    
                    case 'A':
                        $filtroRangoEstado = 'If('.$tableDocVentas.'.idDocumento = "94",('.$tableDocVentas.'.indAprobado = "1" OR ('.$tableDocVentas.'.estDocVentas = "APR" OR '.$tableDocVentas.'.estDocVentas = "PAR" OR '.$tableDocVentas.'.estDocVentas = "ING" OR '.$tableDocVentas.'.estDocVentas = "CER")),('.$tableDocVentas.'.indAprobado = "1" And '.$tableDocVentas.'.estDocVentas = "APR"))';
                    break;

                    default:
                        $filtroRangoEstado = ''.$tableDocVentas.'.indAprobado = "0" And '.$tableDocVentas.'.estDocVentas = "DES"';
                    break;
                }
                
            }
        }

        $i = 0;
        $object = null;
        
        foreach ($filtroRangoEstadoArr as $key => $value) {

            $objectModel = new ObjectModel();
            $objectT = $objectModel
            // ->selectRaw(''.$tableDocVentas.'.IdDocVentas, '.$tableDocVentas.'.IdSerie, '.$tableDocVentas.'.GlsCliente, '.$tableDocVentas.'.RucCliente, '.$tableDocVentas.'.FecEmision, '.$tableDocVentas.'.EstDocVentas, '.$tableDocVentas.'.IdMoneda, '.$tableDocVentas.'.TotalPrecioVenta'.$value['select'])
            ->selectRaw("$tableDocVentas.IdDocVentasInt, $tableDocVentas.IdDocVentas, $tableDocVentas.IdSerie, $tableDocVentas.GlsCliente, $tableDocVentas.RucCliente, $tableDocVentas.FecEmision, $tableDocVentas.EstDocVentas, $tableDocVentas.IdMoneda, $tableDocVentas.TotalPrecioVenta{$value['select']}")
            ->where([
                ''.$tableDocVentas.'.idEmpresa' => $idEmpresa,
                ''.$tableDocVentas.'.idDocumento'   => '94'
            ])
            ->whereYear(''.$tableDocVentas.'.FecEmision', substr($periodo, 0, 4))
            ->whereMonth(''.$tableDocVentas.'.FecEmision', (int)substr($periodo, 4, 2))
            ->whereRaw($value['where']);
            
            if ($textoBusqueda !== '') {
                $objectT = $objectT->whereRaw('('.$tableDocVentas.'.IdDocVentas Like "%'.$textoBusqueda.'%" Or '.$tableDocVentas.'.IdSerie Like "%'.$textoBusqueda.'%" Or '.$tableDocVentas.'.GlsCliente Like "%'.$textoBusqueda.'%" Or '.$tableDocVentas.'.RucCliente Like "%'.$textoBusqueda.'%" Or '.$tableDocVentas.'.EstDocVentas Like "%'.$textoBusqueda.'%")');
            }

            if($i == 0){
                $object = $objectT;
            }else{
                $object = $object->union($objectT);
            }

            $i++;
        }

        $object = $object->orderBy('IdDocVentas')->get();        

        $whereOC = '';

        foreach ($object as $key => $value) {
            $whereOC .= '"'.$idEmpresa.'94'.$value->IdSerie.$value->IdDocVentas.'", ';
        }

        // echo $whereOC; die();

        if($whereOC !== ''){
            $whereOC = substr($whereOC, 0, strlen($whereOC)-2);
        
            $objectDetail = DocVentasDet::
            select(['IdSerie', 'IdDocVentas', 'Item', 'IdProducto', 'GlsProducto', 'GlsMarca', 'Cantidad', 'AbreUM', 'PVUnit', 'TotalPVNeto'])
            ->join('UnidadMedida', $tableDocVentasDet.'.IdUM', '=', 'UnidadMedida.IdUM')
            ->whereRaw('ConCat(IdEmpresa, IdDocumento, IdSerie, IdDocVentas) In('.$whereOC.')')
            ->orderBy('IdSerie')
            ->orderBy('IdDocVentas')
            ->orderBy('Item')
            ->get();
        }else{
            $objectDetail = [];
        }

        // echo $objectDetail; die();

        $timeF = Carbon::now();
        $timeF = $timeF->toTimeString();

        $data = array_add(config('global.dataSuccessNoMessage'), 'HoraInicio', $timeI);
        $data['HoraFin'] = $timeF;
        $data['Ordenes'] = $object;
        $data['OrdenesDetalle'] = $objectDetail;

        return responseApi($data);
    }

    public function listaOCPorRango($idEmpresa, $rango, $estado, $idUsuario, $montoAprobacionD = 0, $montoAprobacionH = 0)
    {
        $VALIDA_APROBACION_OC_USUARIO = getValParametro(new ParametrosCompras(), $idEmpresa, 'VALIDA_APROBACION_OC_USUARIO'.$rango.'_2020');

        $filtroRango = '';
        $filtroEstado = '';

        if($VALIDA_APROBACION_OC_USUARIO == $idUsuario)
        {
            $campoIdUsuarioAprobacion = 'IdUsuarioFirma'.$rango;
            $campoUsuarioEstado = 'UsuarioEstado'.$rango;

            switch ($rango) {
                case '1':
                    $filtroRango = 'IdOrdenTipo = 1 And Round(TotalPrecioVenta * If(IdMoneda = "PEN", 1, TipoCambio), 2) >= '.$montoAprobacionD;
                break;
                
                case '2' || '3_1' || '3_2':
                    $filtroRango = 'IdOrdenTipo = 1 And Round(TotalPrecioVenta * If(IdMoneda = "PEN", 1, TipoCambio), 2) > '.$montoAprobacionD.' And Round(TotalPrecioVenta * If(IdMoneda = "PEN", 1, TipoCambio), 2) <= '.$montoAprobacionH;
                break;

                case '4_1' || '4_2':
                    $filtroRango = 'IdOrdenTipo = 1 And Round(TotalPrecioVenta * If(IdMoneda = "PEN", 1, TipoCambio), 2) > '.$montoAprobacionD;
                break;

                case '5_1' || '5_2':
                    $filtroRango = 'IdOrdenTipo = 2';
                break;

                case '6_1' || '6_2':
                    $filtroRango = 'IdOrdenTipo = 3';
                break;

                case '7_1' || '7_2':
                    $filtroRango = 'IdOrdenTipo = 3';
                break;

            }

            switch ($estado) {
                case 'P': //Pendientes de Aprobacion
                    $filtroEstado = 'IfNull(IdUsuarioFirma'.$rango.', "") = "" And EstDocVentas Not In("DES")';
                    if($rango == '5_1' || $rango == '5_2')// Basta que uno firme y ya no aparece como Pendiente de Aprobar
                    {
                        $filtroEstado .= ' And IfNull(IdUsuarioFirma5_'.($rango == '5_1' ? '2' : '1').', "") = ""';
                    }
                break;
                
                case 'A': //Aprobados
                    $filtroEstado = 'IfNull(IdUsuarioFirma'.$rango.', "") = "'.$idUsuario.'" And UsuarioEstado'.$rango.' = "APR" And EstDocVentas Not In("DES")';
                break;
                
                default:// Desaprobados
                    $filtroEstado = 'IfNull(IdUsuarioFirma'.$rango.', "") = "'.$idUsuario.'" And UsuarioEstado'.$rango.' = "DES" And EstDocVentas = "DES"';
                break;
            }
            
        }

        $return = '';

        if($filtroRango !== '')
        {
            $return = [
                'select'    => ', "'.$campoIdUsuarioAprobacion.'" As GlsCampoIdUsuarioAprobacion, "'.$campoUsuarioEstado.'" As GlsCampoUsuarioEstado',
                'where'     => '('.$filtroRango.' And '.$filtroEstado.')'
            ];
            // $return = '('.$filtroRango.' And '.$filtroEstado.')';
        }
        
        return $return;
    }

    public function detallePorOrden(Request $request, $idSerie, $idDocVentas)
    {
        $timeI = Carbon::now();
        $timeI = $timeI->toTimeString();

        $tableDocVentasDet = DocVentasDet::getTable();

        $user = getIdentity($request);
        $idEmpresa = $user->idEmpresa;
        $idUsuario = $user->idUsuario;

        $object = DocVentasDet::
        select(['Item', 'IdProducto', 'GlsProducto', 'GlsMarca', 'Cantidad', 'AbreUM', 'PVUnit', 'TotalPVNeto'])
        ->join('UnidadMedida', $tableDocVentasDet.'.IdUM', '=', 'UnidadMedida.IdUM')
        ->where([
            'IdEmpresa'     => $user->idEmpresa,
            'IdDocumento'   => '94',
            'IdSerie'       => $idSerie,
            'IdDocVentas'   => $idDocVentas
        ])->get();
        
        $timeF = Carbon::now();
        $timeF = $timeF->toTimeString();

        $data = array_add(config('global.dataSuccessNoMessage'), 'HoraInicio', $timeI);
        $data['HoraFin'] = $timeF;
        $data['OrdenDetalle'] = $object;

        return responseApi($data);
    }

    public function procesar(Request $request, $accion){

        $mensajesData = [];

        switch ($accion) {
            case 'A':
                $this->aprobar($request);
                // $mensajesData[ count( $mensajesData ) ] = ''
                break;
            
            case 'QA':
                $mensajesData = $this->quitarAprobacion($request);
                break;

            case 'D':
                $this->desaprobar($request);
                break;
            
            case 'QD':
                $this->quitarDesaprobacion($request);
                break;

        }

        $data = config('global.dataSuccessMessage');
        $data['message'] = $mensajesData;

        return responseApi($data);
    }

    public function aprobar($request) {
        $ordenes = requestJsonDecodeArr($request);

        $user = getIdentity($request);
        $idEmpresa = $user->idEmpresa;

        // $mensajesData = [];

        foreach ($ordenes as $orden) {
        
            $idDocVentasInt = $orden['IdDocVentasInt'];

            $docVentas = ObjectModel::
            where([
                'IdDocVentasInt'    => $idDocVentasInt
            ]);

            $docVentasGet = $docVentas->first();
            
            // echo $docVentasGet; die();

            $IdOrdenTipo = (int)$docVentasGet->idOrdenTipo;
            $TotalPrecioVenta = $docVentasGet->TotalPrecioVenta;
            
            $montoAprobacion2 = (int)getValParametro(new ParametrosCompras(), $idEmpresa, 'VALIDA_APROBACION_OC_MONTO2_2020');
            $montoAprobacion3 = (int)getValParametro(new ParametrosCompras(), $idEmpresa, 'VALIDA_APROBACION_OC_MONTO3_2020');
            $montoAprobacion4 = (int)getValParametro(new ParametrosCompras(), $idEmpresa, 'VALIDA_APROBACION_OC_MONTO4_2020');

            $docVentas->update([
                $orden['GlsCampoIdUsuarioAprobacion']   => $user->idUsuario,
                $orden['GlsCampoUsuarioEstado']         => 'APR'
            ]);

            $where = '';

            if ($IdOrdenTipo == 4) {
                $where = 'IfNull(IdUsuarioFirma1, "") <> "" And IfNull(IdUsuarioFirma7_1, "") <> "" And IfNull(IdUsuarioFirma7_2, "") <> "" ';
                $where .= 'And IfNull(UsuarioEstado1, "") = "APR" And IfNull(UsuarioEstado7_1, "") = "APR" And IfNull(UsuarioEstado7_2, "") = "APR"';
            }elseif ($IdOrdenTipo == 3) {
                $where = 'IfNull(IdUsuarioFirma1, "") <> "" And IfNull(IdUsuarioFirma6_1, "") <> "" And IfNull(IdUsuarioFirma6_2, "") <> "" ';
                $where .= 'And IfNull(UsuarioEstado1, "") = "APR" And IfNull(UsuarioEstado6_1, "") = "APR" And IfNull(UsuarioEstado6_2, "") = "APR"';
            }elseif ($IdOrdenTipo == 2) {
                $where = 'IfNull(IdUsuarioFirma1, "") <> "" And (IfNull(IdUsuarioFirma5_1, "") <> "" Or IfNull(IdUsuarioFirma5_2, "") <> "") ';
                $where .= 'And IfNull(UsuarioEstado1, "") = "APR" And IfNull(UsuarioEstado5_1, "") = "APR" And IfNull(UsuarioEstado5_2, "") = "APR"';
            }elseif ($TotalPrecioVenta > $montoAprobacion4) {
                $where = 'IfNull(IdUsuarioFirma1, "") <> "" And IfNull(IdUsuarioFirma4_1, "") <> "" And IfNull(IdUsuarioFirma4_2, "") <> "" ';
                $where .= 'And IfNull(UsuarioEstado1, "") = "APR" And IfNull(UsuarioEstado4_1, "") = "APR" And IfNull(UsuarioEstado4_2, "") = "APR"';
            }elseif ($TotalPrecioVenta > $montoAprobacion3) {
                $where = 'IfNull(IdUsuarioFirma1, "") <> "" And IfNull(IdUsuarioFirma3_1, "") <> "" And IfNull(IdUsuarioFirma3_2, "") <> "" ';
                $where .= 'And IfNull(UsuarioEstado1, "") = "APR" And IfNull(UsuarioEstado3_1, "") = "APR" And IfNull(UsuarioEstado3_2, "") = "APR"';
            }elseif ($TotalPrecioVenta > $montoAprobacion2) {
                $where = 'IfNull(IdUsuarioFirma1, "") <> "" And IfNull(IdUsuarioFirma2, "") <> "" ';
                $where .= 'And IfNull(UsuarioEstado1, "") = "APR" And IfNull(UsuarioEstado2, "") = "APR"';
            }else{
                $where = 'IfNull(IdUsuarioFirma1, "") <> "" And IfNull(UsuarioEstado1, "") = "APR"';
            }

            $docVentas = ObjectModel::
            where([
                'IdDocVentasInt'    => $idDocVentasInt
            ])
            ->whereRaw($where)
            ->update([
                'IndAprobado'   => '1',
                'EstDocVentas'  => 'APR'
            ]);
        }
    }

    public function quitarAprobacion($request){

        $ordenes = requestJsonDecodeArr($request);

        $user = getIdentity($request);
        $idEmpresa = $user->idEmpresa;
        $idDocumento = '94';
        $idSerie = '999';

        $mensajesData = [];

        foreach ($ordenes as $orden) {

            $idDocVentasInt = $orden['IdDocVentasInt'];

            $docVentas = ObjectModel::
            where([
                'IdDocVentasInt'    => $idDocVentasInt
            ]);
            
            $docVentasGet = $docVentas->first();
            
            $idSucursal = $docVentasGet->idSucursal;
            $idDocVentas = $docVentasGet->idDocVentas;

            $mensajes = $this->validaQuitarAprobacion($idEmpresa, $idSucursal, $idDocumento, $idSerie, $idDocVentas);

            if( count($mensajes) == 0 ){
                
                $docVentas->update([
                    $orden['GlsCampoIdUsuarioAprobacion']   => '',
                    $orden['GlsCampoUsuarioEstado']         => '',
                    'IndAprobado'                           => '0',
                    'EstDocVentas'                          => 'GEN'
                ]);
    
            }else{

                foreach ($mensajes as $mensaje) {
                    $mensajesData[count($mensajesData)] = $mensaje;
                }

            }

        }

        return $mensajesData;
    }

    public function desaprobar($request){

        $ordenes = requestJsonDecodeArr($request);

        $user = getIdentity($request);
        
        foreach ($ordenes as $orden) {

            $idDocVentasInt = $orden['IdDocVentasInt'];

            $docVentas = ObjectModel::
            where([
                'IdDocVentasInt'    => $idDocVentasInt
            ]);            
            
            $docVentas->update([
                $orden['GlsCampoIdUsuarioAprobacion']   => $user->idUsuario,
                $orden['GlsCampoUsuarioEstado']         => 'DES',
                'IndAprobado'                           => '0',
                'EstDocVentas'                          => 'DES'
            ]);
        }
    }

    public function quitarDesaprobacion($request){

        $ordenes = requestJsonDecodeArr($request);

        // $user = getIdentity($request);
        
        foreach ($ordenes as $orden) {

            $idDocVentasInt = $orden['IdDocVentasInt'];

            $docVentas = ObjectModel::
            where([
                'IdDocVentasInt'    => $idDocVentasInt
            ]);            
            
            $docVentas->update([
                $orden['GlsCampoIdUsuarioAprobacion']   => '',
                $orden['GlsCampoUsuarioEstado']         => '',
                'IndAprobado'                           => '0',
                'EstDocVentas'                          => 'GEN'
            ]);
        }
    }

    public function validaQuitarAprobacion($idEmpresa, $idSucursal, $idDocumento, $idSerie, $idDocVentas){

        $DocReferenciaModel = new DocReferencia();
        $tableDocReferencia = $DocReferenciaModel->getTable();

        $referencias = $DocReferenciaModel::
        selectRaw("If($tableDocReferencia.TipoDocOrigen = '88', ConCat('Vale', ' - ', $tableDocReferencia.NumDocOrigen), ConCat(B.AbreDocumento, $tableDocReferencia.SerieDocOrigen, '/', $tableDocReferencia.NumDocOrigen)) As Referencia")
        ->join('Documentos As B', "$tableDocReferencia.TipoDocOrigen", '=', 'B.IdDocumento')
        ->where([
            "$tableDocReferencia.IdEmpresa"             => $idEmpresa,
            "$tableDocReferencia.IdSucursal"            => $idSucursal,
            "$tableDocReferencia.TipoDocReferencia"     => $idDocumento,
            "$tableDocReferencia.SerieDocReferencia"    => $idSerie,
            "$tableDocReferencia.NumDocReferencia"      => $idDocVentas
        ])
        ->get();
        
        $mensajes = [];

        foreach ($referencias as $referencia) {
            // echo $referencia->Referencia; die();
            $mensajes[count($mensajes)] = "La Orden N° $idDocVentas no puede ser desaprobada porque se encuentra importada en $referencia->Referencia";
        };
        
        return $mensajes;
    }

}
