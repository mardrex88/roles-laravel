<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
class Factura extends Model
{
        /**
     * The table  associated with the model.
     *
     * @var string
     */
    protected $table = 'facturas';
  protected $primaryKey ="id";

  public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','id_cliente','total','status','id_pay', 'limit_date' ,'date' ,
    ];

    public function rides()
    {
    	return $this->hasMany('App\Ride' ,'factura', 'id');
    } 
    public function user()
    {
    	return $this->belongsTo('App\User','id_cliente');
    }
}