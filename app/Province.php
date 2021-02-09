<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Province extends Model
{

	// ============================================
	// Definición

	// Campos del modelo que NO pueden ser de asignación masiva
	protected $guarded = [];
	
	// Campos que deberían ocultarse de los arrays
	protected $hidden = [];

	// Definimos atributos por defecto
	protected $attributes = [];

	// Desativamos timestamps
	public $timestamps = false;


	// ============================================
	// Relaciones
	
	// Relación con regions
	public function region() 
	{
		return $this->belongsTo(\App\Region::class);
	}


	// ============================================
	// Propiedades
	
	
	
	
	// ============================================
	// Métodos

	// Devuelve la lista con el code como índice
	public static function getList()
	{

		return Province::orderBy('name', 'asc')->get()->keyBy('code')->toArray();

	}

	// Devuelve la lista con el name como índice
	public static function getListByName()
	{

		return Province::orderBy('name', 'asc')->get()->keyBy('name')->toArray();

	}
		
}