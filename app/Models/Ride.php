<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Ride 
{
    
  

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'id_user', 'latitude_rec','longitude_rec','id_driver','status','latitude_ent','longitude_ent', 'date','price', 'dist','uidRider','addressRec','addressEnt','met_pay','lugar_pay','status_pay','promocode','factura','total','nameRider','typeService',
    ];

  public function user()
    {
    	return $this->belongsTo('App\User','id_user');
    }

    public function fact()
    {
    	return $this->belongsTo('App\Factura','factura');
    }

  public function driver()
  {
      return $this->belongsTo('App\Driver','id_driver');
  }

    public function tasks()
    {
    	return $this->hasMany('App\Task');
    }
    public function destinos()
    {
    	return $this->hasMany('App\Task');
    }
}