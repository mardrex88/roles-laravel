<?php

namespace App\Models\Ordenes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Orden extends Model
{
        /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ordenes';
  protected $primaryKey ="id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 
        'cliente_id', 
        'fecha_realizacion',
        'hora','creado_por',
        'servicio_id',
        'estado_orden_id',
        'precio', 
        'qty_conductores',
        'metodo_pago',
        'condicion_pago',
        'created_at',
        'updated_at',
        'id_crm'
    ];
    protected $hidden = array('pivot');

    public function cliente()
    {
    	return $this->belongsTo('App\Cliente');
    }
    public function tareas()
    {
    	return $this->hasMany('App\Models\Ordenes\Tarea');
    }
    public function servicio()
    {
    	return $this->belongsTo('App\Models\Config\Servicio');
    }
     /**
     * The users that belong to the role.
     */
    public function conductores()
    {
        return $this->belongsToMany('App\Conductor', 'orden_conductor');
    }
 
      public function estado()
    {
    	return $this->belongsTo('App\Models\Config\OrdenEstado','estado_orden_id');
    }
  /*
    public function fact()
    {
    	return $this->belongsTo('App\Factura','factura');
    }

  public function driver()
  {
      return $this->belongsTo('App\Driver','id_driver');
  }

   
    public function destinos()
    {
    	return $this->hasMany('App\Task');
    } */
}