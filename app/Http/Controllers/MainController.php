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

		public function index()
		{

			// Cargamos listas
			$lists = [
				'regions' => Region::getListByName(),
				'provinces' => Province::getListByName(),
				'districts' => District::getListByName(),
				'cities' => City::getListByName(),
			];
			
			// Última actualización
			$updated = date('d/m/Y', strtotime(Data::select('date')->whereNotNull('city')->orderBy('date', 'desc')->limit(1)->first()->date));

			$selected_province = '';
			$selected_district = '';
			$selected_city = '';
			
			// Cargamos información de la cookie
			if (isset($_COOKIE[config('custom.cookie-name')])) {
				$t = explode('|', $_COOKIE[config('custom.cookie-name')]);
				$selected_province = isset($t[0]) && $t[0] != '' ? filter_var($t[0], FILTER_SANITIZE_STRING) : '';
				$selected_district = isset($t[1]) && $t[1] != '' ? filter_var($t[1], FILTER_SANITIZE_STRING) : '';
				$selected_city = isset($t[2]) && $t[2] != '' ? filter_var($t[2], FILTER_SANITIZE_STRING) : '';
			}
			
			return view('index', compact('lists', 'updated', 'selected_province', 'selected_district', 'selected_city'));

		}

		public function load(Request $request)
		{

			$days = isset(config('custom.stats-dates')[$request->input('dates')]) ? $request->input('dates') : 30;

			if ($request->input('selected') == 'city') {
				// Es una ciudad

				$city = City::where('code', $request->input('value'))->firstOrFail();

				// Info
				$info = Data::where('city', $city->code)
					->orderBy('date', 'desc')
					->take(1)
					->first()
					->toArray();
				
				$info['population'] = $city->population;

				// Last
				$last = Data::where('city', $city->code)
					->where('date', '<', $info['date'])
					->orderBy('date', 'desc')
					->take(1)
					->first()
					->toArray();

				// Data
				$data = Data::where('city', $city->code)
					->orderBy('date', 'desc')
					->take($days)
					->get()
					->toArray();

				$output = [
					'province' => $city->province,
					'district' => $city->district,
					'city' => $city->code,
					'mode' => 'city',
					'name' => $city->name,
					'info' => $info,
					'last' => $last,
					'icons' => Data::compareIcons($info, $last),
					'data' => array_reverse($data),
					'updated' => date('d/m/Y', strtotime($info['date'])),
				];

			} else if ($request->input('selected') == 'district') {
				// Es un distrito

				$district = District::where('code', $request->input('value'))->firstOrFail();

				// Info
				$info = Data::where('district', $district->code)
					->whereNull('city')
					->orderBy('date', 'desc')
					->take(1)
					->first()
					->toArray();
				
				$info['population'] = $district->population;

				// Last
				$last = Data::where('district', $district->code)
					->whereNull('city')
					->where('date', '<', $info['date'])
					->orderBy('date', 'desc')
					->take(1)
					->first()
					->toArray();

				// Data
				$data = Data::where('district', $district->code)
					->whereNull('city')
					->orderBy('date', 'desc')
					->take($days)
					->get()
					->toArray();

				$output = [
					'province' => $district->province,
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
				
			} else if ($request->input('selected') == 'province' && $request->input('value') != '') {
				// Es una provincia

				$province = Province::where('code', $request->input('value'))->firstOrFail();

				// Info
				$info = Data::where('province', $province->code)
					->whereNull('district')
					->whereNull('city')
					->orderBy('date', 'desc')
					->take(1)
					->first()
					->toArray();
				
				$info['population'] = $province->population;

				// Last
				$last = Data::where('province', $province->code)
					->whereNull('district')
					->whereNull('city')
					->where('date', '<', $info['date'])
					->orderBy('date', 'desc')
					->take(1)
					->first()
					->toArray();

				// Data
				$data = Data::where('province', $province->code)
					->whereNull('district')
					->whereNull('city')
					->orderBy('date', 'desc')
					->take($days)
					->get()
					->toArray();

				$output = [
					'province' => $province->code,
					'district' => '',
					'city' => '',
					'mode' => 'province',
					'name' => $province->name,
					'info' => $info,
					'last' => $last,
					'icons' => Data::compareIcons($info, $last),
					'data' => array_reverse($data),
					'updated' => date('d/m/Y', strtotime($info['date'])),
				];

			} else {
				// Es una región
				
				$region = Region::where('code', 'C01')->firstOrFail();

				// Info
				$info = Data::where('region', 'C01')
					->whereNull('province')
					->whereNull('district')
					->whereNull('city')
					->orderBy('date', 'desc')
					->take(1)
					->first()
					->toArray();
				
				$info['population'] = $region->population;

				// Last
				$last = Data::where('region', 'C01')
					->whereNull('province')
					->whereNull('district')
					->whereNull('city')
					->where('date', '<', $info['date'])
					->orderBy('date', 'desc')
					->take(1)
					->first()
					->toArray();

				// Data
				$data = Data::where('region', 'C01')
					->whereNull('province')
					->whereNull('district')
					->whereNull('city')
					->orderBy('date', 'desc')
					->take($days)
					->get()
					->toArray();
				
				$output = [
					'province' => '',
					'district' => '',
					'city' => '',
					'mode' => 'region',
					'name' => $region->name,
					'info' => $info,
					'last' => $last,
					'icons' => Data::compareIcons($info, $last),
					'data' => array_reverse($data),
					'updated' => date('d/m/Y', strtotime($info['date'])),
				];

			}

			// Añadimos los porcentajes
			
			$output['info']['confirmed_percent'] = $output['info']['population'] == 0 ? 0 : ($output['info']['confirmed'] / $output['info']['population']) * 100;
			$output['info']['hospitalized_percent'] = $output['info']['confirmed'] == 0 ? 0 : ($output['info']['hospitalized_total'] / $output['info']['confirmed']) * 100;
			$output['info']['uci_percent'] = $output['info']['hospitalized_total'] == 0 ? 0 : ($output['info']['uci_total'] / $output['info']['hospitalized_total']) * 100;
			$output['info']['recovered_percent'] = $output['info']['confirmed'] == 0 ? 0 : ($output['info']['recovered'] / $output['info']['confirmed']) * 100;
			$output['info']['dead_percent'] = $output['info']['confirmed'] == 0 ? 0 : ($output['info']['dead_total'] / $output['info']['confirmed']) * 100;

			// Formateamos los datos de las gráficas
			foreach ($output['data'] as $k => $v) {

				$output['data'][$k]['date'] = date('d/m/Y', strtotime($v['date']));
				$output['data'][$k]['confirmed_percent'] = $output['info']['population'] == 0 ? 0 : ($v['confirmed'] / $output['info']['population']) * 100;
				$output['data'][$k]['hospitalized_percent'] = $v['confirmed'] == 0 ? 0 : ($v['hospitalized_total'] / $v['confirmed']) * 100;
				$output['data'][$k]['uci_percent'] = $v['hospitalized_total'] == 0 ? 0 : ($v['uci_total'] / $v['hospitalized_total']) * 100;
				$output['data'][$k]['recovered_percent'] = $v['confirmed'] == 0 ? 0 : ($v['recovered'] / $v['confirmed']) * 100;
				$output['data'][$k]['dead_percent'] = $v['confirmed'] == 0 ? 0 : ($v['dead_total'] / $v['confirmed']) * 100;

			}

			$output['info'] = Data::infoFormat($output['info']);
			
			return $output;

		}

}
