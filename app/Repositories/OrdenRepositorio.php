<?php
namespace App\Repositories;

use App\Models\Ordenes\Orden;
use GuzzleHttp\Client;
class OrdenRepositorio {

    public function create(Orden $orden,Client $client)
    {
        $response = $client->request('POST',"orders", [
            'headers' => ['DOLAPIKEY' => 'nG40iu2HjNDWo3D6hnq20z6IA7ibSFN7'],
            'form_params'=>[
                    "entity" => 1,
                    "socid"=> $orden->cliente->id_crm,
                    "ref"=>$orden->id,
                    "fk_statut"=> 1,
                    "availability_id"=> "1",
                    "availability_code"=> "AV_NOW",
                    "availability"=> "Immediate",
                    "date"=> $orden->fecha_realizacion,
                    "date_commande"=> $orden->fecha_realizacion,
                    "date_livraison"=> "",
                    "fk_remise_except"=> null,
                    "remise_percent"=> "0",
                    "remise_absolue"=> null,
                    "info_bits"=> null,
                    "rang"=> null,
                    "special_code"=> null,
                    "source"=> null,
                    "extraparams"=> [],
                    "linked_objects"=> [],
                    "user_author_id"=> "1",
                    "user_valid"=> "1",
                    "fk_multicurrency"=> "0",
                    "multicurrency_code"=> "COP",
                    "multicurrency_tx"=> "1.00000000",
                    "multicurrency_total_ht"=> $orden->precio,
                    "multicurrency_total_tva"=> "0.00000000",
                    "multicurrency_total_ttc"=> $orden->precio,
                    "module_source"=> null,
                    "pos_source"=> null,
                    "import_key"=> null,
                    "array_options"=> [],
                    "linkedObjectsIds"=> [],
                    "canvas"=> null,
                    "fk_project"=> null,
                    "contact"=> null,
                    "contact_id"=> null,
                    "thirdparty"=> null,
                    "user"=> null,
                    "origin"=> null,
                    "origin_id"=> null,
                    "ref_ext"=> null,
                    "country"=> null,
                    "country_id"=> null,
                    "country_code"=> null,
                    "state"=> null,
                    "state_id"=> null,
                    "state_code"=> null,
                    "cond_reglement_id"=> null,
                    "cond_reglement"=> null,
                    "shipping_method_id"=> null,
                    "modelpdf"=> "einstein",
                    "note_public"=> "",
                    "note_private"=> "",
                    "total_ht"=> $orden->precio,
                    "total_tva"=> "0.00000000",
                    "total_localtax1"=> "0.00000000",
                    "total_localtax2"=> "0.00000000",
                    "total_ttc"=> $orden->precio,
                    "fk_incoterms"=> "0",
                    "label_incoterms"=> null,
                    "location_incoterms"=> "",
                    "name"=> null,
                    "lastname"=> null,
                    "firstname"=> null,
                    "civility_id"=> null,
                    "lines" =>[
                        [
                          "rang"=> "1",
                          "pa_ht"=> $orden->precio*0.8,
                          "ref"=> "Recoleccion_y_Entrega",
                          "product_ref"=> "Recoleccion_y_Entrega",
                          "libelle"=> "Recolección y Entrega",
                          "product_label"=> "Recolección y Entrega",
                          "product_desc"=> "Servicio de Mensajería donde se recoge y se entrega su paquete, documentos, mercancía.",
                          "qty"=> "1",
                          "price"=> $orden->precio,
                          "subprice"=> $orden->precio,
                          "product_type"=> "1",
                          "desc"=> "Contacto:".$orden->tareas[0]->nombre_contacto."/ Lugar:".$orden->tareas[0]->direccion."/ Detalles:".$orden->tareas[0]->detalles." => Contacto:".$orden->tareas[1]->nombre_contacto."/ Lugar:".$orden->tareas[1]->direccion."/ Detalles:".$orden->tareas[1]->detalles,
                          "fk_product"=> "1",   
                      ]
                    ],
                 //   "date_creation"=> 1592305569,
                 //   "date_validation"=> 1592306966,
                 //   "date_modification"=> 1592306966,
                    "remise"=> "0",
                    "products"=> [],
                    "entity"=> "1",
                    "ref_customer"=> null,
                    "cond_reglement_doc"=> null,
                    "warehouse_id"=> null,
                    "contacts_ids"=> []
            ]
        ]);
        $data =json_decode($response->getBody()->getContents() );
        return $data; 
        }

        public function validar(Orden $orden,Client $client){
           
           $response =   $client->request('POST',"orders/".$orden->id_crm."/validate", [
                'headers' => ['DOLAPIKEY' => 'nG40iu2HjNDWo3D6hnq20z6IA7ibSFN7']
            ]);
            $data =json_decode($response->getBody());
            return $data; 
        }

        public function reopen(Orden $orden,Client $client){
           
            $response =   $client->request('POST',"orders/".$orden->id_crm."/settodraft", [
                 'headers' => ['DOLAPIKEY' => 'nG40iu2HjNDWo3D6hnq20z6IA7ibSFN7']
             ]);
             $data =json_decode($response->getBody());
             return $data; 
         }
        public function finalizar(Orden $orden,Client $client){
           
            $response =   $client->request('POST',"orders/".$orden->id_crm."/close", [
                 'headers' => ['DOLAPIKEY' => 'nG40iu2HjNDWo3D6hnq20z6IA7ibSFN7'],
                 'form_params'=>[
                    "notrigger"=> 0
                 ]
             ]);
             $data =json_decode($response->getBody());
             return $data; 
         }


         public function update(Orden $orden,Client $client)
        {
            $responseOrden = $client->request('PUT',"orders/".$orden->id_crm, [
                'headers' => ['DOLAPIKEY' => 'nG40iu2HjNDWo3D6hnq20z6IA7ibSFN7'],
                'form_params'=>[
                        "date"=> $orden->fecha_realizacion,
                        "date_commande"=> $orden->fecha_realizacion,
                        "multicurrency_total_ht"=> $orden->precio,
                        "multicurrency_total_ttc"=> $orden->precio,
                        "total_ht"=> $orden->precio,
                        "total_ttc"=> $orden->precio,
                ]
            ]);
          
            $data = utf8_encode ($responseOrden->getBody()->getContents());
            $data2 = json_decode($data,true);
         //  $resp =  $this->updateLine($orden,$client,$data2['lines'][0]['id']);
            //        dd($data2['lines'][0]);
            $this->reopen($orden,$client);
         $responseLine = $client->request('PUT',"orders/".$orden->id_crm."/lines/".$data2['lines'][0]['id'], [
            'headers' => ['DOLAPIKEY' => 'nG40iu2HjNDWo3D6hnq20z6IA7ibSFN7'],
            'form_params'=>[
                "pa_ht"=> $orden->precio*0.8,
                "ref"=> "Recoleccion_y_Entrega",
                "product_ref"=> "Recoleccion_y_Entrega",
                "libelle"=> "Recolección y Entrega",
                "product_label"=> "Recolección y Entrega",
                "product_desc"=> "Servicio de Mensajería donde se recoge y se entrega su paquete, documentos, mercancía.",
                "qty"=> "1",
                "price"=> $orden->precio,
                "subprice"=> $orden->precio,
                "product_type"=> "1",
                "desc"=> "Contacto:".$orden->tareas[0]->nombre_contacto."
                          Lugar:".$orden->tareas[0]->direccion."
                          Detalles:".$orden->tareas[0]->detalles."\r\n
                          Contacto:".$orden->tareas[1]->nombre_contacto."
                          Lugar:".$orden->tareas[1]->direccion."
                          Detalles:".$orden->tareas[1]->detalles,
                "fk_product"=> "1",   
            ]
        ]);
        $data3 = utf8_encode ($responseLine->getBody()->getContents());
        $data4 = json_decode($data3,true);
      //  dd($responseLine->getBody()->getContents());
      $this->validar($orden,$client);
           return $responseLine->getBody()->getContents();

       }

       public function updateLine(Orden $orden,Client $client,$id)
       {
        $responseLine = $client->request('PUT',"orders/".$orden->id_crm."/lines/".$id, [
            'headers' => ['DOLAPIKEY' => 'nG40iu2HjNDWo3D6hnq20z6IA7ibSFN7'],
            'form_params'=>[
                "rang"=> "1",
                "pa_ht"=> $orden->precio*0.8,
                "ref"=> "Recoleccion_y_Entrega",
                "product_ref"=> "Recoleccion_y_Entrega",
                "libelle"=> "Recolección y Entrega",
                "product_label"=> "Recolección y Entrega",
                "product_desc"=> "Servicio de Mensajería donde se recoge y se entrega su paquete, documentos, mercancía.",
                "qty"=> "1",
                "price"=> $orden->precio,
                "subprice"=> $orden->precio,
                "product_type"=> "1",
                "desc"=> "Contacto:".$orden->tareas[0]->nombre_contacto."
                          Lugar:".$orden->tareas[0]->direccion."
                          Detalles:".$orden->tareas[0]->detalles."\r\n
                          Contacto:".$orden->tareas[1]->nombre_contacto."
                          Lugar:".$orden->tareas[1]->direccion."
                          Detalles:".$orden->tareas[1]->detalles,
                "fk_product"=> "1",   
            ]
        ]);
        $data3 = utf8_encode ($responseLine->getBody()->getContents());
        $data4 = json_decode($data3,true);
        return $data4;

       }



         public function eliminar($id,Client $client)
        {
           // return 'id recibido en repositorio';
           try {
            $response = $client->request('DELETE',"orders/".$id, [
                'headers' => ['DOLAPIKEY' => 'nG40iu2HjNDWo3D6hnq20z6IA7ibSFN7'],
                'form_params'=>[ "id" => $id ]
            ]);
            $data =json_decode($response->getBody()->getContents() );
            return 'ok'; 
            } catch (\Exception $e) {
                
                return $e;

            }
           
        }
 public function asignarConductor(Orden $orden,Client $client){
           
    $response =   $client->request('POST',"orders/".$orden->id_crm."/contact/".$orden->conductores[0]->id_crm."/CONDUCTOR", [
         'headers' => ['DOLAPIKEY' => 'nG40iu2HjNDWo3D6hnq20z6IA7ibSFN7']
     ]);
     $data =json_decode($response->getBody());
     return $data; 
 }

 
 public function destroy($id)
 {
     $orden = Orden::where('id',$id)->firstOrFail();
     $orden->delete();
    return; 
 }

}




/*

'form_params'=>[
    "socid"=> $orden->cliente->id_crm,
    "ref"=>$orden->id,
    "ref_client"=> null,
    "contactid"=> null,
    "fk_statut"=> 1,
    "billed"=> "0",
    "brouillon"=> null,
    "cond_reglement_code"=> null,
    "fk_account"=> null,
    "mode_reglement"=> "Cash",
    "mode_reglement_id"=> "4",
    "mode_reglement_code"=> "LIQ",
    "availability_id"=> "1",
    "availability_code"=> "AV_NOW",
    "availability"=> "Immediate",
    "demand_reason_id"=> null,
    "demand_reason_code"=> null,
    "date"=> $orden->fecha_realizacion,
    "date_commande"=> $orden->fecha_realizacion,
    "date_livraison"=> "",
    "fk_remise_except"=> null,
    "remise_percent"=> "0",
    "remise_absolue"=> null,
    "info_bits"=> null,
    "rang"=> null,
    "special_code"=> null,
    "source"=> null,
    "extraparams"=> [],
    "linked_objects"=> [],
    "user_author_id"=> "1",
    "user_valid"=> "1",
    "fk_multicurrency"=> "0",
    "multicurrency_code"=> "COP",
    "multicurrency_tx"=> "1.00000000",
    "multicurrency_total_ht"=> $orden->precio,
    "multicurrency_total_tva"=> "0.00000000",
    "multicurrency_total_ttc"=> $orden->precio,
    "module_source"=> null,
    "pos_source"=> null,
  //  "id"=> "9",
    "import_key"=> null,
    "array_options"=> [],
    "linkedObjectsIds"=> [],
    "canvas"=> null,
    "fk_project"=> null,
    "contact"=> null,
    "contact_id"=> null,
    "thirdparty"=> null,
    "user"=> null,
    "origin"=> null,
    "origin_id"=> null,
    "ref_ext"=> null,
    "country"=> null,
    "country_id"=> null,
    "country_code"=> null,
    "state"=> null,
    "state_id"=> null,
    "state_code"=> null,
    "cond_reglement_id"=> null,
    "cond_reglement"=> null,
    "shipping_method_id"=> null,
    "modelpdf"=> "einstein",
    "note_public"=> "",
    "note_private"=> "",
    "total_ht"=> "7000.00000000",
    "total_tva"=> "0.00000000",
    "total_localtax1"=> "0.00000000",
    "total_localtax2"=> "0.00000000",
    "total_ttc"=> "7000.00000000",
    "fk_incoterms"=> "0",
    "label_incoterms"=> null,
    "location_incoterms"=> "",
    "name"=> null,
    "lastname"=> null,
    "firstname"=> null,
    "civility_id"=> null,
 //   "date_creation"=> 1592305569,
 //   "date_validation"=> 1592306966,
 //   "date_modification"=> 1592306966,
    "remise"=> "0",
    "products"=> [],
    "entity"=> "1",
    "ref_customer"=> null,
    "cond_reglement_doc"=> null,
    "warehouse_id"=> null,
    "contacts_ids"=> [] */