<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Region extends Model
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
	
	


	// ============================================
	// Propiedades
	
	
	
	
	// ============================================
	// Métodos

	// Devuelve la lista con el code como índice
	public static function getList()
	{

		return Region::orderBy('name', 'asc')->get()->keyBy('code')->toArray();

	}

	// Devuelve la lista con el name como índice
	public static function getListByName()
	{

		return Region::orderBy('name', 'asc')->get()->keyBy('name')->toArray();

	}
		
}