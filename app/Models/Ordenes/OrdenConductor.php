<?php

namespace App\Models\Ordenes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class OrdenConductor extends Model
{
        /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'orden_conductor';
  protected $primaryKey ="id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 
        'conductor_id', 
        'orden_id',
        'created_at',
        'updated_at',
    ];


         /**
     * The users that belong to the role.
     */
    public function ordenes()
    {
        return $this->belongsTo('App\Models\Ordenes\Orden', 'orden_id');
    }
}