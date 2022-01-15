<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use \App\Region;
use \App\Province;
use \App\District;
use \App\City;
use \App\Data;


class MainController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        //
    }

		public function index(Request $request)
		{

			// Cargamos listas
			$lists = [
				'regions' => Region::getListByName(),
				'provinces' => Province::getListByName(),
				'districts' => District::getListByName(),
				'cities' => City::getListByName(),
			];
			
			// Última actualización
			// No disponemos en servidor del locale es_ES
			$time = strtotime(Data::select('date')->whereNotNull('city')->whereNotNull('confirmed_total')->orderBy('date', 'desc')->limit(1)->first()->date);
			$updated = config('custom.weekdays')[date('N', $time)] . ' ' . date('d/m/Y', $time);
			// Datos hospitalarios
			$time = strtotime(Data::select('date')->whereNotNull('hosp_beds')->orderBy('date', 'desc')->limit(1)->first()->date);
			$updated_hospitals = config('custom.weekdays')[date('N', $time)] . ' ' . date('d/m/Y', $time);

			$selected_province = '';
			$selected_district = '';
			$selected_city = '';
			
			// Cargamos información de la cookie
			if (isset($_COOKIE[config('custom.cookie-name')])) {
				$t = explode('|', $_COOKIE[config('custom.cookie-name')]);
				$selected_province = isset($t[0]) && $t[0] != '' && $t[0] != 'undefined' ? filter_var($t[0], FILTER_SANITIZE_STRING) : '';
				$selected_district = isset($t[1]) && $t[1] != '' && $t[1] != 'undefined' ? filter_var($t[1], FILTER_SANITIZE_STRING) : '';
				$selected_city = isset($t[2]) && $t[2] != '' && $t[2] != 'undefined' ? filter_var($t[2], FILTER_SANITIZE_STRING) : '';
			}
			
			// Cargamos información de la url
			if ($request->input('province')) {
				$selected_province = filter_var($request->input('province'), FILTER_SANITIZE_STRING);
				$selected_district = '';
				$selected_city = '';
			}
			if ($request->input('district')) {
				$selected_province = '';
				$selected_district = filter_var($request->input('district'), FILTER_SANITIZE_STRING);
				$selected_city = '';
			}
			if ($request->input('city')) {
				$selected_province = '';
				$selected_district = '';
				$selected_city = filter_var($request->input('city'), FILTER_SANITIZE_STRING);
			}

			// Comprobación de validez de los datos
			if ($selected_province != '') {
				$ok = false;
				foreach ($lists['provinces'] as $item) {
					if ($selected_province == $item['code']) {
						$ok = true;
					}
				}
				if (!$ok) { die('Ooooops!'); }
			}
			if ($selected_district != '') {
				$ok = false;
				foreach ($lists['districts'] as $item) {
					if ($selected_district == $item['code']) {
						$ok = true;
					}
				}
				if (!$ok) { die('Ooooops!'); }
			}
			if ($selected_city != '') {
				$ok = false;
				foreach ($lists['cities'] as $item) {
					if ($selected_city == $item['code']) {
						$ok = true;
					}
				}
				if (!$ok) { die('Ooooops!'); }
			}
			
			return view('index', compact('lists', 'updated', 'updated_hospitals', 'selected_province', 'selected_district', 'selected_city'));

		}

		// Carga de datos para el front
		public function load(Request $request)
		{

			$days = isset(config('custom.stats-days')[$request->input('days')]) ? $request->input('days') : 30;
			$request_type = $request->input('selected');
			$request_value = $request->input('value');


			if ($request_type == 'city' && $request_value == '') {
				$request_type = 'province';
			}

			if ($request_type == 'city' && $request_value != '') {
				// Es una ciudad

				$city = City::where('code', $request_value)->firstOrFail();

				// Info
				$info = Data::where('city', $city->code)
					->whereNotNull('confirmed_total')
					->orderBy('date', 'desc')
					->take(1)
					->first()
					->toArray();
				
				$info['population'] = $city->population;

				// Last
				$last = Data::where('city', $city->code)
					->whereNotNull('confirmed_total')
					->where('date', '<', $info['date'])
					->orderBy('date', 'desc')
					->take(1)
					->first()
					->toArray();

				// Data
				$data = Data::where('city', $city->code)
					->whereNotNull('confirmed_total')
					->orderBy('date', 'desc')
					->take($days)
					->get()
					->toArray();

				$output = [
					'province' => '',
					'district' => '',
					'city' => $city->code,
					'mode' => 'city',
					'name' => $city->name,
					'info' => $info,
					'last' => $last,
					'icons' => Data::compareIcons($info, $last),
					'data' => array_reverse($data),
					'updated' => date('d/m/Y', strtotime($info['date'])),
				];

			} else if ($request_type == 'district') {
				// Es un distrito

				$district = District::where('code', $request_value)->firstOrFail();

				// Info
				$info = Data::where('district', $district->code)
					->whereNull('city')
					->whereNotNull('confirmed_total')
					->orderBy('date', 'desc')
					->take(1)
					->first()
					->toArray();
				
				$info['population'] = $district->population;

				// Last
				$last = Data::where('district', $district->code)
					->whereNull('city')
					->whereNotNull('confirmed_total')
					->where('date', '<', $info['date'])
					->orderBy('date', 'desc')
					->take(1)
					->first()
					->toArray();

				// Data
				$data = Data::where('district', $district->code)
					->whereNull('city')
					->whereNotNull('confirmed_total')
					->orderBy('date', 'desc')
					->take($days)
					->get()
					->toArray();

				$output = [
					'province' => '',
					'district' => $district->code,
					'city' => '',
					'mode' => 'district',
					'name' => $district->name,
					'info' => $info,
					'last' => $last,
					'icons' => Data::compareIcons($info, $last),
					'data' => array_reverse($data),
					'updated' => date('d/m/Y', strtotime($info['date'])),
				];
				
			} else if ($request_type == 'province') {
				// Es una provincia

				$item = ($request_value == '')
					? Region::where('code', 'C01')->firstOrFail() // Es una región
					: Province::where('code', $request_value)->firstOrFail(); // Es una provincia

				$province_code = $request_value == '' ? null : $request_value;
				$mode = $request_value == '' ? 'region' : 'province';

				// Info
				$info = Data::where('province', $province_code)
										->whereNull('district')
										->whereNull('city')
										->whereNotNull('confirmed_total')
										->orderBy('date', 'desc')
										->take(1)
										->first()
										->toArray();
				
				$info['population'] = $item->population;

				if ($info['hosp_beds'] == null) {
					// No hay datos hospitalarios este día, cogemos el último

					$info_hospitals = Data::where('province', $province_code)
																	->whereNull('district')
																	->whereNull('city')
																	->whereNotNull('hosp_beds')
																	->orderBy('date', 'desc')
																	->take(1)
																	->first()
																	->toArray();

					$info = Data::addHospitalData($info, $info_hospitals);

				}

				// Last
				$last = Data::where('province', $province_code)
										->whereNull('district')
										->whereNull('city')
										->whereNotNull('confirmed_total')
										->where('date', '<', $info['date'])
										->orderBy('date', 'desc')
										->take(1)
										->first()
										->toArray();

				if ($last['hosp_beds'] == null) {
					// No hay datos hospitalarios este día, cogemos el último

					$last_hospitals = Data::where('province', $province_code)
																->whereNull('district')
																->whereNull('city')
																->whereNotNull('hosp_beds')
																->where('date', '<', $last['date'])
																->orderBy('date', 'desc')
																->take(1)
																->first()
																->toArray();

					$last = Data::addHospitalData($last, $last_hospitals);

				}

				// Data
				$data = Data::where('province', $province_code)
										->whereNull('district')
										->whereNull('city')
										->whereNotNull('confirmed_total')
										->orderBy('date', 'desc')
										->take($days)
										->get()
										->toArray();

				// Operaciones especiales para datos hospitalarios
				$info = Data::getHospitalSums($info);
				$last = Data::getHospitalSums($last);
				foreach ($data as $n => $d) {
					$data[$n] = Data::getHospitalSums($d);
				}

				$output = [
					'province' => $province_code == null ? '' : $province_code,
					'district' => '',
					'city' => '',
					'mode' => $mode,
					'name' => $item->name,
					'info' => $info,
					'last' => $last,
					'icons' => Data::compareIcons($info, $last),
					'data' => array_reverse($data),
					'updated' => date('d/m/Y', strtotime($info['date'])),
				];

			}

			// Añadimos los porcentajes
			
			$output['info']['confirmed_percent'] = $output['info']['population'] == 0 ? 0 : ($output['info']['confirmed_total'] / $output['info']['population']) * 100;
			$output['info']['hospitalized_percent'] = $output['info']['confirmed_total'] == 0 ? 0 : ($output['info']['hospitalized_total'] / $output['info']['confirmed_total']) * 100;
			$output['info']['uci_percent'] = $output['info']['hospitalized_total'] == 0 ? 0 : ($output['info']['uci_total'] / $output['info']['hospitalized_total']) * 100;
			$output['info']['recovered_percent'] = $output['info']['confirmed_total'] == 0 ? 0 : ($output['info']['recovered_total'] / $output['info']['confirmed_total']) * 100;
			$output['info']['dead_percent'] = $output['info']['confirmed_total'] == 0 ? 0 : ($output['info']['dead_total'] / $output['info']['confirmed_total']) * 100;

			// Formateamos los datos de las gráficas
			foreach ($output['data'] as $k => $v) {

				$output['data'][$k]['date'] = date('d/m/Y', strtotime($v['date']));
				$output['data'][$k]['confirmed_percent'] = $output['info']['population'] == 0 ? 0 : ($v['confirmed_total'] / $output['info']['population']) * 100;
				$output['data'][$k]['hospitalized_percent'] = $v['confirmed_total'] == 0 ? 0 : ($v['hospitalized_total'] / $v['confirmed_total']) * 100;
				$output['data'][$k]['uci_percent'] = $v['hospitalized_total'] == 0 ? 0 : ($v['uci_total'] / $v['hospitalized_total']) * 100;
				$output['data'][$k]['recovered_percent'] = $v['confirmed_total'] == 0 ? 0 : ($v['recovered_total'] / $v['confirmed_total']) * 100;
				$output['data'][$k]['dead_percent'] = $v['confirmed_total'] == 0 ? 0 : ($v['dead_total'] / $v['confirmed_total']) * 100;

				// Caso especial para municipios
				if ($output['info']['city'] !== null && $v['date'] <= '2021-01-28') {
					$output['data'][$k]['recovered_percent'] = null;
				}

			}

			$output['info'] = Data::infoFormat($output['info']);
			
			return $output;

		}

}
