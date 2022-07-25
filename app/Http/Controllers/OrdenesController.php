<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use App\Cliente;
use App\Models\Config\Ciudad;
use App\Models\Config\Servicio;
use App\Model\GeoHash;
use App\Driver;
use App\Task;
use App\Factura;
use App\Config;
use App\Notificacion;
use App\Admin;
use App\Conductor;
use App\Models\Ordenes\Orden;
use App\Models\Ordenes\OrdenConductor;
use App\Models\Ordenes\OrdenEstado;
use App\Models\Ordenes\Tarea;
use App\Http\Traits\EstadosOrdenTrait;
use App\Repositories\OrdenRepositorio;
use App\Repositories\OrdenesFirebaseRepositorio;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase;
use Kreait\Firebase\Exception\ApiException;
use Kreait\Firebase\Database\Transaction;
use Illuminate\Support\Facades\DB;
use Gabievi\Promocodes\Models\Promocode;
use Gabievi\Promocodes\Promocodes; 
use GuzzleHttp\Client;


class OrdenesController extends Controller
{

   //use EstadosOrdenTrait;
   //Ojo Falta Crear este archivo 
   


  
  protected $repositorio;
  protected $repFire;

  /**
   * Create a new controller instance.
   *
   * @param  OrdenRepositorio  $repo
   * @return void
   */
  public function __construct(OrdenRepositorio $repo,OrdenesFirebaseRepositorio $repoFire){

      $this->repositorio = $repo;
      $this->repoFire = $repoFire;
  }
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ordenes = Orden::with('tareas')->orderBy('id', 'desc')->get();
        return view('ordenes.index')->with('ordenes',$ordenes)->with('route', 'ordenes');
    }
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        $ordenes = Orden::orderBy('id', 'desc')->with('tareas')->get();
     //   $estados_orden = EstadosOrden::where('nombre_modulo','orden')->get();
        $conductores = Conductor::orderBy('id', 'desc')->get();
        return view('admin.dashboard2')->with('ordenes',$ordenes)->with('conductores',$conductores);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $ciudades = Ciudad::all();
        $clientes = Cliente:: all();
        $servicios = Servicio::all();
        return view('admin.ordenes.create')->with('ciudades', $ciudades)->with('clientes', $clientes)->with('servicios',$servicios);
    }

public function realTime(){
return view('admin.realtimecrud');
}
public function realTime2(){
  return view('admin.realtimecrud2');
  }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,Client $client)
    {
       $request->validate([
        'cliente_id'              => 'required',
        'fecha_realizacion'              => 'required',
        'servicio_id'              => 'required',
        'precio'              => 'required',
        'nombre_contacto'              => 'required',
       ]);

       $req_orden = $request->except(['_token','direccion','direccion_google', 'direc_rec','direc_ent','nombre_contacto','telefono','detalles','lat','lng']);
     //  $req_orden['fecha_realizacion'] = "05/06/06/06";
    //   return $req_orden['fecha_realizacion'];
      $id = 0;
       $req_orden['fecha_realizacion'] = date("Y-m-d H:i:s", strtotime($request['fecha_realizacion']));
       //return $req_orden['fecha_realizacion'];
       $req_orden['creado_por'] = auth()->user()->id;
      $orden = new Orden();
      
   
     //   DB::transaction(function () use($req_orden,$request,$orden,$client,$id) {
        $orden =  Orden::create($req_orden);
        $orden->id;
        
        for($i = 0; $i < count($request['direccion']); ++$i) {
            $tarea = new Tarea();
            $tarea->nombre_contacto     = $request['nombre_contacto'][$i];
            $tarea->direccion           = $request['direccion'][$i];
            $tarea->direccion_google    = $request['direccion_google'][$i];
            $tarea->telefono            = $request['telefono'][$i];
            $tarea->detalles            = $request['detalles'][$i];
            $tarea->lat                 = $request['lat'][$i];
            $tarea->lng                 = $request['lng'][$i];
            $tarea->orden_id            = $orden->id;
            $tarea->save();
        }

        $orden = Orden::where('id',$orden->id)->with('tareas')->with('cliente')->with('servicio')->first();
        $id = $orden->id;
        $id_crm = $this->repositorio->create($orden,$client);
      
        $orden->id_crm = $id_crm;
        $orden->save();
         return redirect()->route('admin.ordenes.edit', ['id' => $orden->id]);
    }

    public function sendForAssign(Request $request){
        $orden = Orden::where('id',$request->id)->with('tareas')->with('cliente')->with('servicio')->first();
       // dd($orden);
        $resp = $this->repoFire->sendForAssign($orden);

        return redirect()->route('admin.ordenes.edit', ['id' => $orden->id]);
    }

    public function crearOrdenCRM(Request $request,Client $client)
    {
      $orden = Orden::where('id',$request->id)->with('tareas')->first();
      if($orden->id_crm == null)
      {
       
      $id_crm = $this->repositorio->create($orden,$client);
      $orden->id_crm = $id_crm;
      $orden->save();
      return redirect()->route('admin.ordenes.edit', ['id' => $orden->id]);
      }
      else
      {
      return 'Esta orden se encuentra sincronizada correctamente';
      }
    }


  

  public function ridesToday(){
  // dd($hoy->toDateTimeString());
   $rides = Ride::whereDay('created_at',date('d'))->get(); 
   for($i = 0; $i < count($rides); ++$i) {
    $rides[$i]['tasks']= $rides[$i]->tasks()->get(); 
   }
 return response()->json(['data' => $rides], 200, [], JSON_NUMERIC_CHECK);
  }

  
  public function ridesToday2(Request $request){
    // dd($hoy->toDateTimeString());
     $rides = Ride::whereBetween('created_at',array($request->dateDesde,date('Y-m-d',strtotime($request->dateHasta."+ 1 days"))))->get(); 
     for($i = 0; $i < count($rides); ++$i) {
      $rides[$i]['tasks']= $rides[$i]->tasks()->get(); 
     }
   return response()->json(['data' => $rides], 200, [], JSON_NUMERIC_CHECK);
    }

    
// Este es el metodo que Retorna las Ordenes Completadas
public function ridesTodayComplete(){
     $rides = Ride::whereDate('created_at',date('Y-m-d'))->where('status','completado')->get(); 
     for($i = 0; $i < count($rides); ++$i) {
      $rides[$i]['tasks']= $rides[$i]->tasks()->get(); 
     }
   return response()->json(['data' => $rides], 200, [], JSON_NUMERIC_CHECK);
    }

// Este es el metodo que Retorna las Ordenes Completadas
public function ridesTodayComplete2(Request $request){ 
  $rides = Ride::whereBetween('created_at',array($request->dateDesde,date('Y-m-d',strtotime($request->dateHasta."+ 1 days"))))->where('status','completado')->get(); 
  for($i = 0; $i < count($rides); ++$i) {
   $rides[$i]['tasks']= $rides[$i]->tasks()->get(); 
  }
return response()->json(['data' => $rides], 200, [], JSON_NUMERIC_CHECK);
 }


// Este es el metodo que Retorna las Ordenes En Curso
public function ridesTodayInCourse(){
    $rides = Ride::whereDate('created_at',date('Y-m-d'))->where('status','!=','completado')->where('status','!=','creado')->get(); 
    for($i = 0; $i < count($rides); ++$i) { 
     $rides[$i]['tasks']= $rides[$i]->tasks()->get(); 
    }
  return response()->json(['data' => $rides], 200, [], JSON_NUMERIC_CHECK);
   }


// Este es el metodo que Retorna las Ordenes En Curso
public function ridesTodayInCourse2(Request $request){
   $rides = Ride::whereBetween('created_at',array($request->dateDesde,date('Y-m-d',strtotime($request->dateHasta."+ 1 days"))))->where('status','!=','completado')->where('status','!=','creado')->where('status','!=','cancelado')->get(); 
  for($i = 0; $i < count($rides); ++$i) {
    $rides[$i]['tasks']= $rides[$i]->tasks()->get();
   }
 return response()->json(['data' => $rides], 200, [], JSON_NUMERIC_CHECK);
   }


// Este es el metodo que Retorna las Ordenes Pendientes de Asignar o Realizar
public function ridesTodayPedientes(){
    $rides = Ride::whereDate('created_at',date('Y-m-d'))->where('status','creado')->get(); 
    for($i = 0; $i < count($rides); ++$i) {
     $rides[$i]['tasks']= $rides[$i]->tasks()->get();
    }
  return response()->json(['data' => $rides], 200, [], JSON_NUMERIC_CHECK);
   }


   public function ridesTodayPedientes2(Request $request){

 // dd($request->dateFilter);
 $rides = Ride::whereBetween('created_at',array($request->dateDesde,date('Y-m-d',strtotime($request->dateHasta."+ 1 days"))))->where('status','creado')->get(); 
    
    for($i = 0; $i < count($rides); ++$i) {
     $rides[$i]['tasks']= $rides[$i]->tasks()->get();
    }
  return response()->json(['data' => $rides], 200, [], JSON_NUMERIC_CHECK);


   }

  public function allRides(Request $request){
    if ($request->provider="users"){
   $rides = Ride::where('id_user',$request->user()->id)->get();
   for($i = 0; $i < count($rides); ++$i) {
    $rides[$i]['tasks']= $rides[$i]->tasks()->get();
   }
    }else {
       $rides = Ride::where('id_driver',$request->user()->id)->get();
    }
    
 return response()->json(['data' => $rides], 200, [], JSON_NUMERIC_CHECK);
  }

  public function adminAllRides(){ 

    $rides = Ride::orderBy('id','desc')->get();
    for($i = 0; $i < count($rides); ++$i) {
      $rides[$i]['tasks']= $rides[$i]->tasks()->get();
     }
    return response()->json(['data' => $rides], 200, [], JSON_NUMERIC_CHECK);
  }




    
    public function show($id)
    {
        $orden = Orden::where('id',$id)->with('tareas')->with('servicio')->first();
        $conductores = Conductor::all();
     //   $estadosOrden = OrdenEstado::all();
         return view('admin.ordenes.ver')->with('orden',$orden)->with('conductores',$conductores);
    }

    /**
     * Show the form for editing the specified resource.
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $orden = Orden::where('id',$id)->with('tareas')->with('servicio')->first();
        $conductores = Conductor::all();
     //   $estadosOrden = OrdenEstado::all();
         return view('admin.ordenes.edit')->with('orden',$orden)->with('conductores',$conductores);
         //->with('estadosOrden',$estadosOrden);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id,Client $client)
    {
        $request->validate([
            'cliente_id'              => 'required',
            'fecha_realizacion'              => 'required',
            'servicio_id'              => 'required',
            'precio'              => 'required',
            'nombre_contacto'              => 'required',
           ]);
      
           $req_orden = $request->except(['_token','direccion', 'direc_rec','direc_ent','nombre_contacto','telefono','detalles','lat','lng','conductores']);
         //  $req_orden['fecha_realizacion'] = "05/06/06/06";
        //   return $req_orden['fecha_realizacion'];

           $req_orden['fecha_realizacion'] = date("Y-m-d H:i:s", strtotime($request['fecha_realizacion']));
           //return $req_orden['fecha_realizacion'];
            $orden = Orden::where('id',$id)->firstOrFail();
        
              
              DB::transaction(function () use($req_orden,$request,$id,$orden,$client) {

            $orden->update($req_orden);


           for($i = 0; $i < count($request['direccion']); ++$i) {

                $tarea = Tarea::where('id', $request['tarea_id'][$i])->first();
                
                $tarea->nombre_contacto     = $request['nombre_contacto'][$i];
                $tarea->direccion           = $request['direccion'][$i];
                $tarea->direccion_google    = $request['direccion_google'][$i];
                $tarea->telefono            = $request['telefono'][$i];
                $tarea->detalles            = $request['detalles'][$i];
                $tarea->lat                 = $request['lat'][$i];
                $tarea->lng                 = $request['lng'][$i];
                $tarea->save();
            }
          //  $orden_crm = $this->repositorio->reopen($orden,$client);
            $orden_crm = $this->repositorio->update($orden,$client);
       //     $orden_crm = $this->repositorio->validar($orden,$client);
      //  $resp =    $this->repositorio->updateLine($orden,$client,$orden_crm);
            });
    
          return redirect()->route('admin.ordenes.edit', ['id' => $id]);
    
    }


    public function destroy($id,Client $client)
    {
        //return $id;
        $orden = Orden::where('id',$id)->first();
        $this->repoFire->destroy($orden);
        $this->repositorio->destroy($orden,$client);
        $orden->delete();
        return redirect()->route('admin.ordenes.index');  
    }

    public function aprobarOrden(Request $request,Client $client)
    {
      $request->validate([
        'idOrden' => 'required',
        'nuevoEstado'=> 'required',
        'estadoActual'=> 'required'
        ]);
        $orden = Orden::where('id',$request['idOrden'])->with('tareas')->with('cliente')->with('servicio')->first();
        
        $this->repositorio->validar($orden,$client);
        $orden->estado_orden_id = 2;
        $orden->save();
          
        $this->repoFire->sendForAssign($orden);
     //   dd($data);
    //    return response()->json('ok', 200); 
         return redirect()->route('admin.ordenes.edit', ['id' => $orden->id]);  
    }



    public function aprobarBorrador(Request $request,Client $client)
    {
          $request->validate([
                      'idOrden' => 'required',
                      'nuevoEstado'=> 'required',
                      'estadoActual'=> 'required'
              ]);
              
              $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
              $firebase = (new Factory)->withServiceAccount($serviceAccount)->create();
              $messaging = $firebase->getMessaging();
              $db = $firebase->getDatabase();
          $orden = Orden::where('id',$request['idOrden'])->first();    
          $this->repositorio->validar($orden,$client);       
              if($orden->estado_orden_id != $request['estadoActual']){
                return response()->json('Estado Actual no valido', 401);
              }
          

          DB::transaction(function () use($orden,$request){          
          $orden->update([
            'estado_orden_id' => (int)$request['nuevoEstado']
          ]);
          });

          $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
          $firebase = (new Factory)->withServiceAccount($serviceAccount)->create();
          $db = $firebase->getDatabase();
  
          foreach($orden->tareas as $tarea){
              $tarea->lat = (float)$tarea->lat;
              $tarea->lng = (float)$tarea->lng;
          }
          $l[0]=(float)$orden->tareas[0]['lat'];
          $l[1]=(float)$orden->tareas[0]['lng'];
          $g = new GeoHash;
          
          $db->getReference('OrdenesSinConductor/'.$orden->id.'/orden')->set($orden);
          $db->getReference('OrdenesSinConductor/'.$orden->id.'/l')->set($l);
          $db->getReference('OrdenesSinConductor/'.$orden->id.'/g')->set($g->encode($orden->tareas[0]['lat'],$orden->tareas[0]['lng'],10));
          if($orden->id_crm!=null){
          $this->repositorio->validar($orden,$client);
          }else{
            $orden = Orden::where('id',$orden->id)->with('tareas')->first();
            $id_crm2 = $this->repositorio->create($orden,$client);
            $orden->id_crm = $id_crm2;
            $orden->save();
            $this->repositorio->validar($orden,$client);
          }
          return response()->json('ok', 200);
    }

    public function asignarConductoresToOrden(Request $request,Client $client){
      $request->validate([
        'conductores'              => 'required',
        'orden_id'              => 'required',
       ]);
       $orden = Orden::where('id',$request['orden_id'])->with('tareas')->firstOrfail();           
       $this->desAsignarConductores($request->orden_id);

      for($a = 0; $a < count($request['conductores']); ++$a) {
      $this->asignarConductor($request['orden_id'],$request['conductores'][$a]);    
        foreach($orden->tareas as $tarea){
            $tarea->conductor_id = $request['conductores'][$a];
            $tarea->estado_tarea_id = 2;
            $tarea->save();
        }  
      }

      $this->repositorio->asignarConductor($orden,$client);
      $orden->update([
        'estado_orden_id' => 3
      ]);
      $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/../../bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
      $firebase = (new Factory)->withServiceAccount($serviceAccount)->create();
      $db = $firebase->getDatabase();
      $db->getReference('OrdenesSinConductor/'.$orden->id)->remove();
      $db->getReference('OrdenesEnCurso/'.$orden->id)->set($orden);
    return response()->json('ok', 200);
    }       
    
    public function asignarConductorToOrdenWithUid(Request $request,Client $client){
        $request->validate([
          'conductor_uid'              => 'required',
          'orden_id'              => 'required',
         ]);
         $conductor = Conductor::where('uid',$request['conductor_uid'])->first();
         $orden = Orden::where('id',$request['orden_id'])->with('tareas')->firstOrfail();           
         $this->desAsignarConductores($request->orden_id);
  
        $this->asignarConductor($request['orden_id'],$conductor->id);    
          foreach($orden->tareas as $tarea){
              $tarea->conductor_id = $conductor->id;
              $tarea->estado_tarea_id = 2;
              $tarea->save();
          }  
  
        $this->repositorio->asignarConductor($orden,$client);
        $orden->update([
          'estado_orden_id' => 3
        ]);
        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/../../bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
        $firebase = (new Factory)->withServiceAccount($serviceAccount)->create();
        $db = $firebase->getDatabase();
        $db->getReference('OrdenesSinConductor/'.$orden->id)->remove();
        $db->getReference('OrdenesEnCurso/'.$orden->id)->set($orden);
      return response()->json('ok', 200);

      }       
      
    public function cancelarOrden(Request $request){

      $request->validate([
        'orden_id'              => 'required',
       ]);
       $orden = Orden::where('id',$request['orden_id'])->first();           

      $orden->update([
        'estado_orden_id' => 6,
        'precio' => 0
      ]);
    return response()->json('ok', 200);
    }
    
   





    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function asignar(Request $request,Client $client)
    {
        $request->validate([
            "idConductor"=> "required",
            "idOrden"=> "required"
        ]);
      
        $orden = Orden::where('id',$request->idOrden)->with('tareas')->with('conductores')->firstOrfail();
        $this->desAsignarConductores($orden->id);  
        $this->asignarConductor($orden->id,$request->idConductor);
       foreach($orden->tareas as $tarea){
        $tarea->conductor_id = $request->idConductor;
        $tarea->estado_tarea_id = 2;
        $tarea->save();
    }  
    $orden = Orden::where('id',$request->idOrden)->with('tareas')->with('conductores')->firstOrfail();
       $this->repositorio->asignarConductor($orden,$client); 
              $orden->update([
        'estado_orden_id' => 3
      ]);

      
      $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/../../bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
      $firebase = (new Factory)->withServiceAccount($serviceAccount)->create();
      $db = $firebase->getDatabase();
      $db->getReference('OrdenesSinConductor/'.$orden->id)->remove();
      $db->getReference('OrdenesEnCurso/'.$orden->id)->set($orden);
      return redirect()->route('admin.ordenes.edit', ['id' => $orden->id]);  

    }

    public function removeAvaible($id){
        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/../../bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
            $firebase = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->create();
        
        $db = $firebase->getDatabase();
        $avaibles = $db->getReference('AvaibleRides/')->getSnapshot();

        $value = $avaibles->getValue();
       $cursor = 0;
       if($avaibles ->exists()){
        foreach($value as $key=>$val){
            foreach($val as $key2=>$item){
               if($key2==$id){
                $pUid = $key;
             //   echo $pUid."<br/>";
                $cursor = 1;
                $db->getReference('AvaibleRides/'.$pUid.'/'.$id)->remove();
                break;
            }
            }
        }
    }
      //  }else{
        //}
        }
    
 
    

    public function autoAssign(){
      return view('admin.ride.autoassign');
    }
    
    public function enviarNotificacion($uid,$title,$body)
    {
        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/../../bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
            $firebase = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->create();
        $messaging = $firebase->getMessaging();
        $db = $firebase->getDatabase();


        $token = $db->getReference('Tokens/'.$uid.'/token')->getValue();

      
        $notification = Notification::fromArray([
            'title' => (string) $title,
            'body' => $body
        ]);
        if(!is_null($token)){
          $message = CloudMessage::withTarget('token', $token)
        ->withNotification($notification);
        $messaging->send($message);
        }

    }

    public function asignarConductor($orden_id,$conductor_id){
      
      DB::transaction(function () use($orden_id,$conductor_id) {
        $asignarOrden = OrdenConductor::firstOrCreate(
        ['conductor_id' => $conductor_id,'orden_id'=> $orden_id],
        ['orden_id' => $orden_id, 'conductor_id' => $conductor_id]
         );
      });
      $conductor = Conductor::where('id',$conductor_id)->first();
      $body='Tienes una nueva orden asignada';
      $this->enviarNotificacion($conductor->uid,$orden_id,$body);
      $this->removeAvaible($orden_id); 
      
    }
    public function desAsignarConductores($orden_id){
      
      DB::transaction(function () use($orden_id) {
        $asignacion =  OrdenConductor::where('orden_id', $orden_id)->delete();
      });
    }


    public function notifyRideAvaibleDriver(Request $request){


              $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
            $firebase = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->create();
        $messaging = $firebase->getMessaging();
        $db = $firebase->getDatabase();


        $ride = Ride::find($request->id);
        
        $driver = Driver::where('uid',$request->driverAvaible)->first();
       
        $notificacion = Notificacion::where('id_order','=',$request->id)
        ->where('id_driver','=',$driver->id)
        ->where('status','=','rechazado')->get();

     // dd($avaibles);
         if(is_null($ride['uidDriver']) || empty($ride['uidDriver'])|| is_null($ride['id_driver']) || empty($ride['id_driver'] && count($notificacion)) ){
      //  $ride->uidDriver = $driver->uid;
    // $ride->id_driver = $driver->id;
        $ride->status = "creado"; 
        $ride->save();
        $ride->destinos = $ride->tasks;
        unset($ride->tasks);

        $body='Tenemos una orden para tí.';
        $this->enviarNotificacion($driver->uid,$ride->id,$body);
  //      $body='Hemos asignado un mensajero a la orden';
  //      $this->enviarNotificacion($ride->uidRider,$ride->id,'Hemos asignado un mensajero a la orden');

        $db->getReference('AvaibleRides/'.$driver->uid.'/'.$ride->id)
        ->set($ride);
        $db->getReference('RideRequest/'.$ride->uidRider.'/'.$ride->id)
        ->set($ride);
        $db->getReference('AllRides/'.$ride->id)
        ->set($ride);
    //    $db->getReference('RidesForAssign/'.$ride->id)->remove();
    //   $this->removeAvaible($ride->id); 
       
       return response()->json('OK', 200);
        }else{
          return response()->json('El servicio ya se encuentra asignado', 201);
        }
    }
    public function assignDriver(Request $request){

        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
      $firebase = (new Factory)
      ->withServiceAccount($serviceAccount)
      ->create();
  $messaging = $firebase->getMessaging();
  $db = $firebase->getDatabase();


  $ride = Ride::find($request->idRide);
  
  $driver = Driver::where('uid',$request->idDriver)->first();
 
// dd($avaibles);
   if(is_null($ride['uidDriver']) || empty($ride['uidDriver'])|| is_null($ride['id_driver']) || empty($ride['id_driver']) ){
  $ride->uidDriver = $driver->uid;
  $ride->id_driver = $driver->id;
  $ride->status = "asignado"; 
  $ride->save();
  $ride->destinos = $ride->tasks;
  unset($ride->tasks);

  $body='Tienes una nueva orden asignada';
  $this->enviarNotificacion($driver->uid,$ride->id,$body);
  $body='Hemos asignado un mensajero a la orden';
  $this->enviarNotificacion($ride->uidRider,$ride->id,'Hemos asignado un mensajero a la orden');

  $db->getReference('RidesInProgress/'.$driver->uid.'/'.$ride->id)
  ->set($ride);
  $db->getReference('RideRequest/'.$ride->uidRider.'/'.$ride->id)
  ->set($ride);
  $db->getReference('AllRides/'.$ride->id)
  ->set($ride);
  $db->getReference('RidesForAssign/'.$ride->id)->remove();
  $db->getReference('RidesForAssign2/'.$ride->id)->remove();
 $this->removeAvaible($ride->id); 
 
 return response()->json('OK', 200);
  }else{
    return response()->json('El servicio ya se encuentra asignado', 201);
  }
}

/*
Este es el metodo de crear servicio para el admin app
*/

public function store3(Request $request)
{
//Checkeamos el promocode que sea valido
if($request->promocode !=""){
$promocode = Promocode::byCode($request->promocode)->first();
if ($promocode === null) {
    return response()->json([
        'message' =>'Codigo Invalido', 201]);
}
if ($promocode->isExpired() || $promocode->users2($request->user()->id)->exists()) {
    return response()->json([
        'message' =>'Codigo Expiró o ya a sido utilizado', 202]);
}
$ride = new Ride([
  'promocode'     => $promocode->id,
  'price'     => $request->price,
'dist'     => $request->dist,
 'id_user' => $request->id_user,
 'nameRider' => $request->nameRider,
 'status' => "creado",
 'date' => date('Y-m-d H:i:s'),
 'uidRider' =>$request->uidRider,
 'met_pay' =>$request->met_pay, 
]);
$promocode->users()->attach(auth()->id(), [
'promocode_id' => $promocode->id,
'used_at' => Carbon::now(),
]);
if($promocode->isDisposable()){
$promocode->expires_at = Carbon::now();
$promocode->status="usado";
}
$promocode->save();
}else{
    $ride = new Ride([
        'price'     => $request->price,
         'dist'     => $request->dist,
         'id_user' => $request->id_user,
         'nameRider' => $request->nameRider,
       'status' => "creado",
       'date' => date('Y-m-d H:i:s'),
       'uidRider' =>$request->uidRider,
       'met_pay' =>$request->met_pay,   
     ]);
}
     $ride->save();
 
 for($i = 0; $i < count($request->tasks); ++$i) {

$task = new Task([
 'address' => $request->tasks[$i]['address'],
  'ride_id' => $ride->id,
 'latitude' => $request->tasks[$i]['latitude'],
 'longitude'=> $request->tasks[$i]['longitude'],
 'detalles' => $request->tasks[$i]['detalles'],
 'phone_contact'=> $request->tasks[$i]['phone_contact'],
 'name_contact' => $request->tasks[$i]['name_contact'],
]);
$task->save();
}
return response()->json([
    'message' =>$ride->id, 200]);
}


public function cancelRide(Request $request)
{
      $request->validate([
                  'idRide' => 'required',
          ]);

          $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
          $firebase = (new Factory)
          ->withServiceAccount($serviceAccount)
          ->create();
      
      $db = $firebase->getDatabase();

      $ride = Ride::where('id',$request['idRide'])->firstOrFail();

// dd($ride->uidDriver);

      if($ride['status']!="completado" && $ride['status']!="recogiendo" &&$ride['status']!="encurso" &&$ride['status']!="entregando"&&$ride['status']!="cancelado"){
      $ride['status'] = 'cancelado';  
      $ride->save();  
      $db->getReference('RideRequest/'.$ride->uidRider.'/'.$ride->id)->set($ride);
      if(!is_null($ride->uidDriver)){
      $db->getReference('RideRequest/'.$ride['uidDriver'].'/'.$ride['id'])->set($ride);
      $db->getReference('RidesInProgress/'.$ride->uidDriver.'/'.$ride->id)->remove();
      $db->getReference('RideRequest/'.$ride->uidDriver.'/'.$ride->id.'/status')->set($ride->status);
    }
      $db->getReference('AllRides/'.$ride->id.'/status')->set($ride->status);;
      $db->getReference('RidesForAssign/'.$ride->id)->remove();
      $db->getReference('RidesForAssign2/'.$ride->id)->remove();
    // $this->removeAvaible($ride->id); 
        return response()->json($ride, 200);
      }elseif ($ride['status']=="cancelado" )
      {
        return response()->json("La orden ya se encuentra cancelada", 202);
      }else{
        return response()->json("no se puede cancelar el servicio en el estado actual", 201);
      }


      
}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Cross\Rides  $rides
     * @return \Illuminate\Http\Response
     */
    public function asignarApiAdmin(Request $request)
    {
        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
            $firebase = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->create();
        $messaging = $firebase->getMessaging();
        $db = $firebase->getDatabase();


        $ride = Ride::find($request->idRide);
        $driver = Driver::where('uid',$request->idDriver)->first();
     // dd($avaibles); 
    //     if(is_null($ride['uidDriver']) || empty($ride['uidDriver'])|| is_null($ride['id_driver']) || empty($ride['id_driver']) ){
        $ride->uidDriver = $driver->uid;
        $ride->id_driver = $driver->id;
        $ride->status = "asignado"; 
        $ride->save();
        $ride->destinos = $ride->tasks;
        unset($ride->tasks);

        $db->getReference('RidesInProgress/'.$driver->uid.'/'.$ride->id)
        ->set($ride);
        $db->getReference('AllRides/'.$ride->id)
        ->set($ride);
        $db->getReference('RideRequest/'.$ride->uidRider.'/'.$ride->id)
        ->set($ride);
        $db->getReference('RideRequest/'.$ride->uidDriver.'/'.$ride->id)
        ->set($ride);
        $db->getReference('RidesForAssign/'.$ride->id)->remove();
        $db->getReference('RidesForAssign2/'.$ride->id)->remove();
       $this->removeAvaible($ride->id); 
       // $rides = Ride::orderBy('id','desc')->get();
       $drivers = Driver::where('status_account','=','ok')->get();
       $body='Tienes una nueva orden asignada';
       $this->enviarNotificacion($driver->uid,$ride->id,$body);
       $body='Hemos asignado un mensajero a la orden';
       $this->enviarNotificacion($ride->uidRider,$ride->id,'Hemos asignado un mensajero a la orden');
       return response()->json([
        'message' =>'ok', 200]);
   //   }else{
   //     return response()->json(['message' =>'El servicio ya se encuentra asignado', 201]);
    //  }
    }

    public function changeUserForAdmin(Request $request){

      $ride = Ride::find($request->idRide);
      $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
            $firebase = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->create();

        $db = $firebase->getDatabase();
      
      $beforeIdRider = $ride->id_user;
      $beforeUidRider =$ride->uidRider;
      $beforeNameRider = $ride->nameRider;

      $ride->id_user = (Integer)$request->newIdRider;
      $ride->uidRider = $request->newUidRider;
      $ride->nameRider = $request->newNameRider;
      $ride->save(); 
      $ride->destinos = $ride->tasks;
      unset($ride->tasks);


      $db->getReference('AllRides/'.$ride->id)
      ->set($ride);
      $db->getReference('RideRequest/'.$beforeUidRider .'/'.$ride->id)
      ->remove();
      $db->getReference('RideRequest/'.$ride->uidRider.'/'.$ride->id)
      ->set($ride);

if(!is_null($ride->uidDriver)){
      $db->getReference('RidesInProgress/'.$ride->uidDriver.'/'.$ride->id)
      ->set($ride); 
      $db->getReference('RideRequest/'.$ride->uidDriver.'/'.$ride->id)
      ->set($ride);
}   
if($ride->status =="creado")
{ $db->getReference('RidesForAssign/'.$ride->id)
  ->set($ride);
}
      return response()->json([
        'message' =>'ok', 200]);
    }




    public function finishRide(Request $request)
    {
          $request->validate([
                      'idRide' => 'required',
              ]);
    
              $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
              $firebase = (new Factory)
              ->withServiceAccount($serviceAccount)
              ->create();
          
          $db = $firebase->getDatabase();

          $ride = Ride::where('id',$request['idRide'])->firstOrFail();

    if(is_null($ride['id_driver'])){
            return response()->json([
              'message' => 'Orden sin Conductor'], 404);
          }
          if($ride['status'] != "completado"){
          $ride['status'] = "completado";  
          $ride->save();  
          $driver = Driver::find($ride['id_driver']); 
          $gastos = 0;
          $ganancia = $ride['price'] * 0.85;
          $comision = $ride['price'] * 0.15;
        
          switch ($ride['met_pay']) {
            case "efectivo":
            $efectivo = $ride['price'];
            $liquidado = $ganancia + $gastos - $efectivo;
            $fact = new Factura([
              'id_cliente' => $ride->id_user,
              'total' => $ride['price'],
              'status' => 'creado',
              'date' =>date('Y-m-d'),
            ]);
            $fact->save();
            $ride->factura = $fact->id;
            $transaction = $driver->deposit($liquidado,'deposit',['efectivo' => $efectivo,'metPay'=>'efectivo','gastos'=>$gastos,'ganancia'=> $ganancia,'detalles' =>'Pago Servicio Efectivo','id_order'=>$ride->id, 'factura'=>$fact->id]);
            $admin = Admin::find(1);
            $admin->registrarComision($comision,'comision',['id_order'=>$ride->id]);
            $fact->id_pay = $transaction->id;
            $fact->status = 'pagada';
            $fact->save();
            $ride->status_pay = "pagada"; 
            $ride->save();
    
                break;
            case "transferencia":
            $efectivo = 0; 
            $liquidado = $ganancia + $gastos - $efectivo;
            $transaction = $driver->deposit($liquidado,'deposit',['efectivo' => $efectivo,'gastos'=>$gastos,'ganancia'=> $ganancia,'detalles' =>'Pago Servicio Transferencia','id_order'=>$ride->id]);
            $admin = Admin::find(1);
            $admin->registrarComision($comision,'comision',['id_order'=>$ride->id]);
          //  $admin->forceWithdraw($liquidado,'withdraw',['efectivo' => 0,'gastos'=>0,'ganancia'=> 0,'detalles' =>'Pago Servicio a Driver','id_order'=>$ride->id,'id_transac_driver'=>$transaction->id]);
                break;
            case "tarjeta":
            $liquidado = $ganancia + $gastos - $efectivo;
            $driver->deposit($liquidado,'deposit',['efectivo' => $efectivo,'gastos'=>$gastos,'ganancia'=> $ganancia,'detalles' =>'','id_factura'=>null]);
                break;
        }

        $db->getReference('RideRequest/'.$ride->uidRider.'/'.$ride->id)->set($ride);
        if(!is_null($ride->uidDriver)){
        $db->getReference('RideRequest/'.$ride['uidDriver'].'/'.$ride['id'])->set($ride);
        $db->getReference('RidesInProgress/'.$ride->uidDriver.'/'.$ride->id)->remove();
        $db->getReference('RideRequest/'.$ride->uidDriver.'/'.$ride->id.'/status')->set("completado");
      }
        $db->getReference('AllRides/'.$ride->id.'/status')->set("completado");;
        $db->getReference('RidesForAssign/'.$ride->id)->remove();
        $db->getReference('RidesForAssign2/'.$ride->id)->remove();
    
          return response()->json([
            'message' => 'Cobro realizado Correctamente'], 200);
        }else{
           return response()->json([
                'message' => 'La orden ya a sido cobrada'], 201);
        }
          
    }


      public function getRides(Request $request){

        if(isset($request->idRider)){

          switch ($request->status) {
            case "Todos":
           
                $rides = Ride::whereBetween('date',array($request->dateDesde,date('Y-m-d',strtotime($request->dateHasta."+ 1 days"))))
                ->where('id_user',$request->idRider)
                ->get(); 
                break;
                case "Pendientes":
                $rides = Ride::whereBetween('date',array($request->dateDesde,date('Y-m-d',strtotime($request->dateHasta."+ 1 days"))))
                                    ->where('status','creado')
                                    ->where('id_user',$request->idRider)->get();    
                
                break;
                case "En Curso":  
                $rides = Ride::whereBetween('date',array($request->dateDesde,date('Y-m-d',strtotime($request->dateHasta."+ 1 days"))))  
                ->where('status','!=','completado')->where('status','!=','cancelado')   
                ->where('id_user',$request->idRider)->get();   
                break;
                   
                case "Completadas": 
                $rides = Ride::whereBetween('date',array($request->dateDesde,date('Y-m-d',strtotime($request->dateHasta."+ 1 days"))))
                                    ->where('status','completado')
                                    ->where('id_user',$request->idRider)->get();    

                break;
            }
        }else{
          switch ($request->status) {
            case "Todos":
           
                $rides = Ride::whereBetween('date',array($request->dateDesde,date('Y-m-d',strtotime($request->dateHasta."+ 1 days"))))
              
                ->get(); 
                break;
                case "Pendientes":
                $rides = Ride::whereBetween('date',array($request->dateDesde,date('Y-m-d',strtotime($request->dateHasta."+ 1 days"))))
                                    ->where('status','creado')
                                   ->get();     
                 
                break;
                case "En Curso":  
                $rides = Ride::whereBetween('date',array($request->dateDesde,date('Y-m-d',strtotime($request->dateHasta."+ 1 days"))))  
                ->where('status','!=','completado')->where('status','!=','cancelado')   
                ->get();   
                break;
                   
                case "Completadas": 
                $rides = Ride::whereBetween('date',array($request->dateDesde,date('Y-m-d',strtotime($request->dateHasta."+ 1 days"))))
                                    ->where('status','completado')
                                    ->get();    

                break;
            }
        }
            // dd($hoy->toDateTimeString());
     for($i = 0; $i < count($rides); ++$i) {
      $rides[$i]['tasks']= $rides[$i]->tasks()->get(); 
     }
        return response()->json(['data' => $rides], 200, [], JSON_NUMERIC_CHECK);
    }

    public function getRide(Request $request){
      $ride = Ride::find($request->idRide);
      $ride->tasks = $ride->tasks()->get();
      return $ride;
    }
    
    public function updateDate(Request $request){
/*
      $ride = Ride::find($request->idRide);

      $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/../bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
      $firebase = (new Factory)
      ->withServiceAccount($serviceAccount)
      ->create();

      $db = $firebase->getDatabase();

      $ride->date = $request->newDate;

      $ride->save();

      $ride->destinos = $ride->tasks;
      unset($ride->tasks);

      $db->getReference('RideRequest/'.$ride->uidRider.'/'.$ride->id)
      ->set($ride);
      $db->getReference('AllRides/'.$ride->id)
      ->set($ride);

      if(!is_null($ride->uidDriver)){
        $db->getReference('RidesInProgress/'.$ride->uidDriver.'/'.$ride->id)
        ->set($ride); 
        $db->getReference('RideRequest/'.$ride->uidDriver.'/'.$ride->id)
        ->set($ride);
  }   

      return response()->json(['data' => $ride], 200, [], JSON_NUMERIC_CHECK);
*/
    }   

    public function checkActionNotify(Request $request) {

      //  return response()->json($request, 200, [], JSON_NUMERIC_CHECK);

//return $request;
/*
$driver = Driver::where('uid',$request->idDriver)->first();

        $notificacion = Notificacion::where('id_order','=',$request->id)
                                        ->where('id_driver','=',$driver->id)
                                        ->where('status','=','rechazado')->get();
        if(count($notificacion)){
            return response()->json($notificacion, 200, [], JSON_NUMERIC_CHECK);  
        }else{
            return false;
        }
*/
    }

    public function delete(Request $request) {

        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
        $firebase = (new Factory)
        ->withServiceAccount($serviceAccount)
        ->create();
        $messaging = $firebase->getMessaging();
        $db = $firebase->getDatabase();
        
    $ride = Ride::find($request->idRideDelete2);
   
    if($ride->status == "creado" || $ride->status == "cancelado"){

        if(!is_null($request->idRideDelete2)){
            try {
              //code...
              $db->getReference('AllRides/'.$request->idRideDelete2)->remove();
              $db->getReference('RidesForAssign/'.$request->idRideDelete2)->remove();
              $db->getReference('RidesForAssign2/'.$request->idRideDelete2)->remove();
            } catch (Exception $e) {
              echo $e->getMessage();
              die();
            }
          }
          if(!is_null($ride->uidRider)){
              try {
                //code...
                $db->getReference('RideRequest/'.$ride->uidRider.'/'.$request->idRideDelete2)->remove();
                $db->getReference('RideInProgress/'.$ride->uidRider.'/'.$request->idRideDelete2)->remove();
              } catch (Exception $e) {
                echo $e->getMessage();
                die();
              }
            }
          if(!is_null($ride->uidDriver)){
              try {
                //code...
                $db->getReference('RideRequest/'.$ride->uidDriver.'/'.$request->idRideDelete2)->remove();
                $db->getReference('RideInProgress/'.$ride->uidDriver.'/'.$request->idRideDelete2)->remove();
              } catch (Exception $e) {
                echo $e->getMessage();
                die();
              }
            }
          $ride->delete();
      
            $rides = Ride::orderBy('id','desc')->get();
             $drivers = Driver::where('status_account','=','ok')->get();
            return back()->with('success',['rides' => $rides,
            'drivers' => $drivers]);
      
          }else{$rides = Ride::orderBy('id','desc')->get();
            $drivers = Driver::where('status_account','=','ok')->get();
            return back()->withErrors('success','La orden no se puede eliminar por que ya a sido asignada o esta en curso.')
            ->with(['rides' => $rides,
            'drivers' => $drivers]);
          }
    }
  
    public function ridesRange(Request $request) {


        if($request->userid ==""){
            $rides = Ride::whereBetween('date',array(date('Y-m-d',strtotime($request->desde)),date('Y-m-d',strtotime($request->hasta))))
            ->paginate(25);  
        }else{
            $rides = Ride::whereBetween('date',array(date('Y-m-d',strtotime($request->desde)),date('Y-m-d',strtotime($request->hasta))))
        ->where('id_user',$request->userid)
        ->paginate(25); 
        }
           $drivers = Driver::where('status_account','=','ok')->get();
           $users = User::all();

           return view('admin.rides_filter', ['rides' => $rides,
      'users' => $users,
      'userid' => $request->userid,
      'drivers' => $drivers,
      'desde'=>$request->desde,
      'hasta' => $request->hasta]);

    }

    public function download2(Request $request) {
        if($request->userid2 ==""){

            $rides =  Ride::whereBetween('date',array(date('Y-m-d',strtotime($request->desde2)),date('Y-m-d',strtotime($request->hasta2))))
            ->paginate(1000);

            foreach ($rides as $ride) {
                $ride->origen = $ride->tasks[0]['address'];
                $ride->destino = $ride->tasks[1]['address'];
                $ride->detalles = $ride->tasks[0]['detalles']." / ".$ride->tasks[1]['detalles'];
              }
            return $rides->downloadExcel(
                'rides2.xlsx',
                $writerType = null,
                $headings = false
            );  
        }else{
           $rides = Ride::whereBetween('date',array(date('Y-m-d',strtotime($request->desde2)),date('Y-m-d',strtotime($request->hasta2))))
        ->where('id_user',$request->userid2)
        ->paginate(1000);
        foreach ($rides as $ride) {
            $ride->origen = $ride->tasks[0]['address'];
            $ride->destino = $ride->tasks[1]['address'];
            $ride->detalles = $ride->tasks[0]['detalles']." / ".$ride->tasks[1]['detalles'];
          }
        return $rides->downloadExcel(
            'rides2.xlsx',
            $writerType = null,
            $headings = false
        ); 
    }

    }

    public function download(Request $request){

        if($request->userid ==""){
           Ride::whereBetween('date',array(date('Y-m-d',strtotime($request->desde)),date('Y-m-d',strtotime($request->hasta."+ 1 days"))))
            ->get()->downloadExcel(
                'rides2.xlsx',
                $writerType = null,
                $headings = false
            );  
        }else{
            Ride::whereBetween('date',array(date('Y-m-d',strtotime($request->desde)),date('Y-m-d',strtotime($request->hasta."+ 1 days"))))
        ->where('id_user',$request->userid)
        ->get()->downloadExcel(
            'rides2.xlsx',
            $writerType = null,
            $headings = false
        );  
        }
       // return Excel::download(new RidesExport,'rides.xlsx');
    }


    public function procesarOrden(Request $request, Client $client)
    {
        $orden = Orden::where('id', $request->id)->first();

        if($orden->estado_orden_id < 3){
            return redirect()->route('admin.ordenes.edit', ['id' => $orden->id]);
        }
        $orden->update([
            'estado_orden_id' => 5
        ]);

        $this->repositorio->finalizar($orden,$client);
        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/../../bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
        $firebase = (new Factory)->withServiceAccount($serviceAccount)->create();
        $db = $firebase->getDatabase();
        try {
            $db->getReference('OrdenesSinConductor/'.$orden->id)->remove();
            $db->getReference('OrdenesEnCurso/'.$orden->id)->remove();
        } catch (ApiException $e) {
            /** @var \Psr\Http\Message\RequestInterface $request */
            $request = $e->getRequest();
            /** @var \Psr\Http\Message\ResponseInterface|null $response */
            $response = $e->getResponse();
        
            echo $request->getUri().PHP_EOL;
            echo $request->getBody().PHP_EOL;
        
            if ($response) {
                echo $response->getBody();
            }
        }

        return redirect()->route('admin.ordenes.edit', ['id' => $orden->id]); 
    }

}