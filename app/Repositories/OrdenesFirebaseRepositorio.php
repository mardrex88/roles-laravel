<?php
namespace App\Repositories;

use App\Models\Ordenes\Orden;
use App\Ride;
use App\Model\GeoHash;
use App\Task;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase;
use Kreait\Firebase\Exception\ApiException;
use Kreait\Firebase\Database\Transaction;


class OrdenesFirebaseRepositorio {


    public function create(Orden $orden)
    {
        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
              $firebase = (new Factory)
              ->withServiceAccount($serviceAccount)
              ->create();
          
          $db = $firebase->getDatabase();

          $db->getReference('RidesForAssign/'.$orden->id)->set($orden);
          return;
    }

    public function create2(Orden $orden){
        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
        $firebase = (new Factory)->withServiceAccount($serviceAccount)->create();
        $db = $firebase->getDatabase();
    

        $ride = new Ride([
            'price'     => $orden->precio,
            'dist'     => $request->dist,
            'id_user' => $request->cliente_id,
            'nameRider' => User::find($request->cliente_id)->name,
            'status' => "creado",
            'date' => $request->date." 00:00:00",
            'uidRider' =>User::find($request->cliente_id)->uid,
            'met_pay' =>$request->met_pay, 
            'typeService' => 1, 
           ]);



                $l[0]=floatval($orden->tareas[0]->lat);
                $l[1]=floatval($orden->tareas[0]->lng);
    
                $g = new GeoHash;
                $db->getReference('RidesForAssign/'.$orden->id.'/emptrega')->set($orden);
            //    $db->getReference('AllRides/'.$orden->id)->set($ride);
                $db->getReference('RideRequest/'.$orden->cliente->uid.'/'.$orden->id)->set($orden);
                
                $db->getReference('RidesForAssign2/'.$orden->id.'/g')->set($g->encode($l[0],$l[1],10));
                $db->getReference('RidesForAssign2/'.$orden->id.'/l')->set($l);
    
                return ;
          }



           public function sendForAssign(Orden $orden){
            $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
            $firebase = (new Factory)->withServiceAccount($serviceAccount)->create();
            $db = $firebase->getDatabase();
        

          $ride = [
            'id'     => intval($orden->id),
            'price'     => intval($orden->precio),
            'dist'     => floatval(0.0),
            'id_user' => intval($orden->cliente_id),
            'nameRider' => $orden->cliente->firstname.' '.$orden->cliente->lastname,
            'status' => "creado",
            'date' => $orden->fecha_realizacion." 00:00:00",
            'uidRider' =>$orden->cliente->uid,
            'met_pay' =>$orden->metodo_pago, 
            'typeService' => 1, 
            'destinos' => [
                0=>  [
                    'address' => $orden->tareas[0]->direccion,
                    'latitude' => floatval($orden->tareas[0]->lat) ,
                    'longitude'=> floatval($orden->tareas[0]->lng) ,
                    'detalles' => $orden->tareas[0]->detalles,
                    'phone_contact'=> $orden->tareas[0]->telefono,
                    'name_contact' => $orden->tareas[0]->nombre_contacto,
                ],
                1=> [
                    'address' => $orden->tareas[0]->direccion,
                    'latitude' => floatval($orden->tareas[0]->lat),
                    'longitude'=> floatval($orden->tareas[0]->lng),
                    'detalles' => $orden->tareas[0]->detalles,
                    'phone_contact'=> $orden->tareas[0]->telefono,
                    'name_contact' => $orden->tareas[0]->nombre_contacto,
                  ]
            ]
           ];

           $l[0]=floatval($orden->tareas[0]->lat);
           $l[1]=floatval($orden->tareas[0]->lng);
           foreach($orden->tareas as $tarea){
            $tarea->lat = (float)$tarea->lat;
            $tarea->lng = (float)$tarea->lng;
        }
        $orden->cliente_id= intval($orden->cliente_id);
        $orden->cliente->ciudad_id= intval($orden->cliente->ciudad_id);
        $orden->servicio_id= intval($orden->servicio_id);
        $orden->id_crm= intval($orden->id_crm);
        $orden->estado_orden_id= intval($orden->estado_orden_id);
        $orden->qty_conductores= intval($orden->qty_conductores);
        $orden->creado_por= intval($orden->creado_por);
        $orden->precio= intval($orden->precio);
        unset($orden->cliente->confirmed);
        unset($orden->cliente->estado_cuenta);
        unset($orden->cliente->id_crm);
        unset($orden->cliente->uid);
        $orden->tareas[0]->orden_id= intval($orden->tareas[0]->orden_id);
        $orden->tareas[0]->cobrado= intval($orden->tareas[0]->cobrado);
        $orden->tareas[0]->estado_tarea_id= intval($orden->tareas[0]->estado_tarea_id);
        $orden->tareas[1]->orden_id= intval($orden->tareas[0]->orden_id);
        $orden->tareas[1]->cobrado= intval($orden->tareas[0]->cobrado);
        $orden->tareas[1]->estado_tarea_id= intval($orden->tareas[0]->estado_tarea_id);
        $orden->servicio->id= intval($orden->servicio->id);
        $orden->servicio->horas_base = intval($orden->servicio->horas_base);
        $orden->servicio->activo= intval($orden->servicio->activo);
        $orden->servicio->km_base= intval($orden->servicio->km_base);
        $orden->servicio->porcentaje_comision= intval($orden->servicio->porcentaje_comision);
        $orden->servicio->precio_base= intval($orden->servicio->precio_base);
        $orden->servicio->precio_hora= intval($orden->servicio->precio_hora);
        $orden->servicio->precio_km= intval($orden->servicio->precio_km);
        $orden->servicio->qty_conductores= intval($orden->servicio->qty_conductores);
           $g = new GeoHash;
           $db->getReference('RidesForAssign/'.$orden->id.'/emptrega')->set($ride);
       //    $db->getReference('AllRides/'.$orden->id)->set($ride);
           $db->getReference('RideRequest/'.$orden->cliente->uid.'/'.$orden->id)->set($ride);
           $db->getReference('RidesForAssign2/'.$orden->id.'/emptrega')->set($ride);
           $db->getReference('RidesForAssign2/'.$orden->id.'/g')->set($g->encode($l[0],$l[1],10));
           $db->getReference('RidesForAssign2/'.$orden->id.'/l')->set($l);
           $db->getReference('OrdenesSinConductor/'.$orden->id.'/orden')->set($orden);
           $db->getReference('OrdenesSinConductor/'.$orden->id.'/l')->set($l);
           $db->getReference('OrdenesSinConductor/'.$orden->id.'/g')->set($g->encode($orden->tareas[0]['lat'],$orden->tareas[0]['lng'],10));

           return "ok";

              }
              public function removeAvaible($id){
                $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
                    $firebase = (new Factory)
                    ->withServiceAccount($serviceAccount)
                    ->create();
                
                $db = $firebase->getDatabase();
                $avaibles = $db->getReference('AvaibleRides/')
                ->getSnapshot();
            
                $value = $avaibles->getValue();
               $cursor = 0;
               if(count($value)){
                foreach($value as $key=>$val){
                    if($cursor ==0){
                    foreach($val as $key2=>$item){
                       if($key2==$id){
                        $pUid = $key;
                        echo $pUid."<br/>";
                        $cursor = 1;
                        $db->getReference('AvaibleRides/'.$pUid.'/'.$id)->remove();
                        $db->getReference('RidesForAssign/'.$ride->id)->remove();
                        $db->getReference('RidesForAssign2/'.$ride->id)->remove();
                        break;
                       }
                    }
                }else{
                    break;
                }
                }
            }
            
            }

            public function destroy(Orden $orden){
                $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
                $firebase = (new Factory)
                ->withServiceAccount($serviceAccount)
                ->create();
            
                $db = $firebase->getDatabase();
                $db->getReference('OrdenesSinConductor/'.$orden->id)->remove();
                $db->getReference('OrdenesEnCurso/'.$orden->id)->remove();
                return;

            }
            public function removerOrdenEnCurso($id){
                $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/bdemp-956d3-firebase-adminsdk-2kj89-09985927f4.json');
                $firebase = (new Factory)
                ->withServiceAccount($serviceAccount)
                ->create();
            
                $db = $firebase->getDatabase();
                //$db->getReference('OrdenesSinConductor/'.$orden->id)->remove();
                $db->getReference('OrdenesEnCurso/'.$id)->remove();
                return;

            }

        }