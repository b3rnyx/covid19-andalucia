<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Data extends Model
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
	
	// Relación con provinces
	public function province()
	{
		return $this->belongsTo(\App\Province::class);
	}

	// Relación con districts
	public function district()
	{
		return $this->belongsTo(\App\District::class);
	}


	// ============================================
	// Propiedades
	
	
	
	
	// ============================================
	// Métodos

	// Calcula los incrementos
	public static function getIncrements($data)
	{

		$previous = Data::where('region', $data['region'])
			->where('province', $data['province'])
			->where('district', $data['district'])
			->where('city', $data['city'])
			->whereNotNull('confirmed_total')
			->where('date', '<', $data['date'])
			->orderBy('date', 'desc')
			->first();

		if (count($previous) > 0) {

			return [
				'confirmed_increment' => isset($data['confirmed_total']) && $data['confirmed_total'] != null ? $data['confirmed_total'] - $previous->confirmed_total : null,
				'hospitalized_increment' => isset($data['hospitalized_total']) && $data['hospitalized_total'] != null ? $data['hospitalized_total'] - $previous->hospitalized_total : null,
				'uci_increment' => isset($data['uci_total']) && $data['uci_total'] != null ? $data['uci_total'] - $previous->uci_total : null,
				'recovered_increment' => isset($data['recovered_total']) && $data['recovered_total'] != null ? $data['recovered_total'] - $previous->recovered_total : null,
				'dead_increment' => isset($data['dead_total']) && $data['dead_total'] != null ? $data['dead_total'] - $previous->dead_total : null,
			];

		} else {

			return [
				'confirmed_increment' => isset($data['confirmed_total']) && $data['confirmed_total'] != null ? 0 : null,
				'hospitalized_increment' => isset($data['hospitalized_total']) && $data['hospitalized_total'] != null ? 0 : null,
				'uci_increment' => isset($data['uci_total']) && $data['uci_total'] != null ? 0 : null,
				'recovered_increment' => isset($data['recovered_total']) && $data['recovered_total'] != null ? 0 : null,
				'dead_increment' => isset($data['dead_total']) && $data['dead_total'] != null ? 0 : null,
			];

		}

	}

	// Añade la información hospitalaria al array de datos
	public static function addHospitalData($data, $hospital_data)
	{

		$data['hosp_beds'] = $hospital_data['hosp_beds'];
		$data['hosp_beds_covid'] = $hospital_data['hosp_beds_covid'];
		$data['hosp_beds_covid_increment'] = $hospital_data['hosp_beds_covid_increment'];
		$data['hosp_beds_nocovid'] = $hospital_data['hosp_beds_nocovid'];
		$data['hosp_admissions'] = $hospital_data['hosp_admissions'];
		$data['hosp_admissions_increment'] = $hospital_data['hosp_admissions_increment'];
		$data['hosp_discharges'] = $hospital_data['hosp_discharges'];
		$data['hosp_uci_resp_beds'] = $hospital_data['hosp_uci_resp_beds'];
		$data['hosp_uci_resp_beds_covid'] = $hospital_data['hosp_uci_resp_beds_covid'];
		$data['hosp_uci_resp_beds_covid_increment'] = $hospital_data['hosp_uci_resp_beds_covid_increment'];
		$data['hosp_uci_resp_beds_nocovid'] = $hospital_data['hosp_uci_resp_beds_nocovid'];
		$data['hosp_uci_resp_admissions'] = $hospital_data['hosp_uci_resp_admissions'];
		$data['hosp_uci_resp_admissions_increment'] = $hospital_data['hosp_uci_resp_admissions_increment'];
		$data['hosp_uci_resp_discharges'] = $hospital_data['hosp_uci_resp_discharges'];
		$data['hosp_uci_beds'] = $hospital_data['hosp_uci_beds'];
		$data['hosp_uci_beds_covid'] = $hospital_data['hosp_uci_beds_covid'];
		$data['hosp_uci_beds_covid_increment'] = $hospital_data['hosp_uci_beds_covid_increment'];
		$data['hosp_uci_beds_nocovid'] = $hospital_data['hosp_uci_beds_nocovid'];
		$data['hosp_uci_admissions'] = $hospital_data['hosp_uci_admissions'];
		$data['hosp_uci_admissions_increment'] = $hospital_data['hosp_uci_admissions_increment'];
		$data['hosp_uci_discharges'] = $hospital_data['hosp_uci_discharges'];

		return $data;

	}

	// Calcula los incrementos de hospitalización
	public static function getHospitalIncrements($date, $province, $data)
	{

		$previous = Data::where('region', 'C01')
			->where('province', $province)
			->whereNull('district')
			->whereNull('city')
			->whereNotNull('hosp_beds')
			->where('date', '<', $date)
			->orderBy('date', 'desc')
			->first();

		if (count($previous) > 0) {

			return [
				'hosp_beds_covid_increment' => isset($data['hosp_beds_covid']) && $data['hosp_beds_covid'] !== null ? $data['hosp_beds_covid'] - $previous->hosp_beds_covid : null,
				'hosp_admissions_increment' => isset($data['hosp_admissions']) && $data['hosp_admissions'] !== null ? $data['hosp_admissions'] - $previous->hosp_admissions : null,
				'hosp_uci_resp_beds_covid_increment' => isset($data['hosp_uci_resp_beds_covid']) && $data['hosp_uci_resp_beds_covid'] !== null ? $data['hosp_uci_resp_beds_covid'] - $previous->hosp_uci_resp_beds_covid : null,
				'hosp_uci_resp_admissions_increment' => isset($data['hosp_uci_resp_admissions']) && $data['hosp_uci_resp_admissions'] !== null ? $data['hosp_uci_resp_admissions'] - $previous->hosp_uci_resp_admissions : null,
				'hosp_uci_beds_covid_increment' => isset($data['hosp_uci_beds_covid']) && $data['hosp_uci_beds_covid'] !== null ? $data['hosp_uci_beds_covid'] - $previous->hosp_uci_beds_covid : null,
				'hosp_uci_admissions_increment' => isset($data['hosp_uci_admissions']) && $data['hosp_uci_admissions'] !== null ? $data['hosp_uci_admissions'] - $previous->hosp_uci_admissions : null,
			];

		} else {

			return [
				'hosp_beds_covid_increment' => isset($data['hosp_beds_covid']) && $data['hosp_beds_covid'] !== null ? 0 : null,
				'hosp_admissions_increment' => isset($data['hosp_admissions']) && $data['hosp_admissions'] !== null ? 0 : null,
				'hosp_uci_resp_beds_covid_increment' => isset($data['hosp_uci_resp_beds_covid']) && $data['hosp_uci_resp_beds_covid'] !== null ? 0 : null,
				'hosp_uci_resp_admissions_increment' => isset($data['hosp_uci_resp_admissions']) && $data['hosp_uci_resp_admissions'] !== null ? 0 : null,
				'hosp_uci_beds_covid_increment' => isset($data['hosp_uci_beds_covid']) && $data['hosp_uci_beds_covid'] !== null ? 0 : null,
				'hosp_uci_admissions_increment' => isset($data['hosp_uci_admissions']) && $data['hosp_uci_admissions'] !== null ? 0 : null,
			];

		}

	}

	// Crea los sumatorios de datios hospitalarios
	public static function getHospitalSums($item)
	{

		$item['hosp_beds_total'] = $item['hosp_beds'] == null ? null : intval($item['hosp_beds']) + intval($item['hosp_uci_resp_beds']) + intval($item['hosp_uci_beds']);
		$item['hosp_beds_covid_total'] = $item['hosp_beds'] == null ? null : intval($item['hosp_beds_covid']) + intval($item['hosp_uci_resp_beds_covid']) + intval($item['hosp_uci_beds_covid']);
		$item['hosp_beds_covid_increment_total'] = $item['hosp_beds'] == null ? null : intval($item['hosp_beds_covid_increment']) + intval($item['hosp_uci_resp_beds_covid_increment']) + intval($item['hosp_uci_beds_covid_increment']);
		$item['hosp_beds_nocovid_total'] = $item['hosp_beds'] == null ? null : intval($item['hosp_beds_nocovid']) + intval($item['hosp_uci_resp_beds_nocovid']) + intval($item['hosp_uci_beds_nocovid']);
		$item['hosp_admissions_total'] = $item['hosp_beds'] == null ? null : intval($item['hosp_admissions']) + intval($item['hosp_uci_resp_admissions']) + intval($item['hosp_uci_admissions']);
		$item['hosp_admissions_increment_total'] = $item['hosp_beds'] == null ? null : intval($item['hosp_admissions_increment']) + intval($item['hosp_uci_resp_admissions_increment']) + intval($item['hosp_uci_admissions_increment']);
		$item['hosp_discharges_total'] = $item['hosp_beds'] == null ? null : intval($item['hosp_discharges']) + intval($item['hosp_uci_resp_discharges']) + intval($item['hosp_uci_discharges']);
		$item['uci_beds'] = $item['hosp_beds'] == null ? null : intval($item['hosp_uci_resp_beds']) + intval($item['hosp_uci_beds']);
		$item['uci_beds_covid'] = $item['hosp_beds'] == null ? null : intval($item['hosp_uci_resp_beds_covid']) + intval($item['hosp_uci_beds_covid']);
		$item['uci_beds_covid_increment'] = $item['hosp_beds'] == null ? null : intval($item['hosp_uci_resp_beds_covid_increment']) + intval($item['hosp_uci_beds_covid_increment']);
		$item['uci_beds_nocovid'] = $item['hosp_beds'] == null ? null : intval($item['hosp_uci_resp_beds_nocovid']) + intval($item['hosp_uci_beds_nocovid']);
		$item['uci_admissions'] = $item['hosp_beds'] == null ? null : intval($item['hosp_uci_resp_admissions']) + intval($item['hosp_uci_admissions']);
		$item['uci_admissions_increment'] = $item['hosp_beds'] == null ? null : intval($item['hosp_uci_resp_admissions_increment']) + intval($item['hosp_uci_admissions_increment']);
		$item['uci_discharges'] = $item['hosp_beds'] == null ? null : intval($item['hosp_uci_resp_discharges']) + intval($item['hosp_uci_discharges']);

		$item['hosp_beds_covid_percent'] = $item['hosp_beds'] == null ? null : ($item['hosp_beds'] == 0 ? 0 : ($item['hosp_beds_covid'] / $item['hosp_beds']) * 100);
		$item['hosp_beds_uci_covid_percent'] = $item['hosp_beds'] == null ? null : (($item['hosp_uci_resp_beds'] + $item['hosp_uci_beds']) == 0 ? 0 : (($item['hosp_uci_resp_beds_covid'] + $item['hosp_uci_beds_covid']) / ($item['hosp_uci_resp_beds'] + $item['hosp_uci_beds'])) * 100);
		$item['hosp_beds_total_percent'] = $item['hosp_beds'] == null ? null : ($item['hosp_beds'] == 0 ? 0 : (($item['hosp_beds_covid'] + $item['hosp_beds_nocovid']) / $item['hosp_beds']) * 100);
		$item['uci_beds_total_percent'] = $item['hosp_beds'] == null ? null : ($item['uci_beds'] == 0 ? 0 : (($item['uci_beds_covid'] + $item['uci_beds_nocovid']) / $item['uci_beds']) * 100);

		return $item;

	}

	// Genera los iconos de comparación
	public static function compareIcons($info, $last) {

		$icons = [];

		foreach (config('custom.stats-items') as $k => $v) {

			if (isset($info[$k]) && isset($v['green'])) {

				if ($info[$k] < $last[$k]) {
					// Se ha reducido

					$icons[$k] = '<i class="fa fa-caret-down';

					if ($v['green'] == 'desc') {
						$icons[$k] .= ' text-success';
					} else {
						$icons[$k] .= ' text-danger';
					}

					$icons[$k] .= '" title="Baja con respecto a día anterior"></i>';

				} else if ($info[$k] > $last[$k]) {
					// Ha aumentado

					$icons[$k] = '<i class="fa fa-caret-up';

					if ($v['green'] == 'asc') {
						$icons[$k] .= ' text-success';
					} else {
						$icons[$k] .= ' text-danger';
					}

					$icons[$k] .= '" title="Sube con respecto a día anterior"></i>';

				} else {
					// Se mantiene

					$icons[$k] = '<i class="fa fa-check text-black-50" title="Se mantiene con respecto a día anterior"></i>';

				}

			}

		}

		return $icons;

	}

	// Formatea los datos para info
	public static function infoFormat($info)
	{

		foreach (config('custom.stats-items') as $k => $v) {

			if (isset($info[$k])) {
				
				switch ($v['type']) {

					case 'number':
						$info[$k] = number_format(round($info[$k]), 0, ',', '.');
						break;

					case 'decimal':
						$info[$k] = number_format($info[$k], 2, ',', '.');
						break;

					case 'percent':
						$info[$k] = number_format($info[$k], 2, ',', '.') . '%';
						break;

				}

			}

		}

		return $info;

	}
		
}