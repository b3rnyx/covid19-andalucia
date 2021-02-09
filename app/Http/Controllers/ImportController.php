<?php

namespace App\Http\Controllers;

use \App\Region;
use \App\Province;
use \App\District;
use \App\City;
use \App\Data;


class ImportController extends Controller
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

	public function import()
	{

		$log = "IMPORT START: " . date('Y-m-d H:i:s') . "\n";

		// Aumentamos tiempo de ejecución del script a 5 minutos
		set_time_limit(0);
		// Aumento de memoria
		ini_set('memory_limit', '1024M');
		// Contexto para evitar la comprobación de certificados en el file_get_contents()
		$arrContextOptions = [
			'ssl' => [
				'verify_peer' => false,
				'verify_peer_name' => false,
			],
		];

		$start = microtime(true);

		$date = date('Y-m-d');

		// --------------
		// Importamos datos de provincias desde distintas fuentes

		$data_provinces = [];

		// Archivo total de provincias

		$log .= "Starting load of file 'provinces-total'.\n";

		$lapse = microtime(true);

		$json = file_get_contents(
			config('custom.import.urls.provinces-total'),
			false,
			stream_context_create($arrContextOptions)
		);

		$import = json_decode($json, true);

		foreach ($import['data'] as $d) {

			$province = isset($d[0]['cod'][1]) ? $d[0]['cod'][1] : null;

			$c = $province == null ? Region::where('code', 'C01')->firstOrFail() : Province::where('code', $province)->firstOrFail();
			$confirmed_total = isset($d[1]) ? $d[1]['val'] : 0;
			$recovered = isset($d[9]) ? $d[9]['val'] : 0;

			$data_provinces[$province] = [
				'date' => $date,
				'region' => 'C01',
				'province' => $province,
				'district' => null,
				'city' => null,
				'confirmed' => isset($d[3]) ? $d[3]['val'] : null,
				'confirmed_7d' => isset($d[7]) ? $d[7]['val'] : null,
				'incidence_7d' => isset($d[8]) ? $d[8]['val'] : null,
				'confirmed_14d' => isset($d[5]) ? $d[5]['val'] : null,
				'incidence_14d' => isset($d[6]) ? $d[6]['val'] : null,
				'confirmed_total' => isset($d[1]) ? $d[1]['val'] : null,
				'recovered' => isset($d[9]) ? $d[9]['val'] : null,
				'dead_total' => isset($d[10]) ? $d[10]['val'] : null,
			];

		}

		$log .= "File 'provinces-total' loaded and parsed (" . round(microtime(true) - $lapse) . " seconds).\n";

		// Archivo diario de provincias

		$log .= "Starting load of file 'provinces-daily'.\n";

		$lapse = microtime(true);

		$json = file_get_contents(
			config('custom.import.urls.provinces-daily'),
			false,
			stream_context_create($arrContextOptions)
		);

		$import = json_decode($json, true);

		// Obtenemos la fecha que necesitamos
		$t = explode('/', $import['data'][0][0]['des']);
		$date_ok = $t[2] . '-' . $t[1] . '-' . $t[0];

		// Importamos las 9 primeras filas (CA + provincias)
		for ($n=0; $n<9; $n++) {

			$d = $import['data'][$n];

			$province = isset($d[1]['cod'][1]) ? $d[1]['cod'][1] : null;

			$data_provinces[$province]['hospitalized'] = isset($d[4]) ? $d[4]['val'] : null;
			$data_provinces[$province]['uci'] = isset($d[5]) ? $d[5]['val'] : null;
			$data_provinces[$province]['dead'] = isset($d[6]) ? $d[6]['val'] : null;

		}

		$log .= "File 'provinces-daily' loaded and parsed (" . round(microtime(true) - $lapse) . " seconds).\n";

		// Archivo acumulado de provincias

		$log .= "Starting load of file 'provinces-accumulated'.\n";

		$lapse = microtime(true);

		$json = file_get_contents(
			config('custom.import.urls.provinces-accumulated'),
			false,
			stream_context_create($arrContextOptions)
		);

		$import = json_decode($json, true);

		// Obtenemos la fecha que necesitamos
		$t = explode('/', $import['data'][0][0]['des']);
		$date_ok = $t[2] . '-' . $t[1] . '-' . $t[0];

		// Importamos las 9 primeras filas (CA + provincias)
		for ($n=0; $n<9; $n++) {

			$d = $import['data'][$n];

			$province = isset($d[1]['cod'][1]) ? $d[1]['cod'][1] : null;

			$data_provinces[$province]['increase'] = isset($d[3]) ? $d[3]['val'] : null;
			$data_provinces[$province]['hospitalized_total'] = isset($d[7]) ? $d[7]['val'] : null;
			$data_provinces[$province]['uci_total'] = isset($d[8]) ? $d[8]['val'] : null;

		}

		$log .= "File 'provinces-accumulated' loaded and parsed (" . round(microtime(true) - $lapse) . " seconds).\n";
		
		// Comprobamos si los datos son nuevos

		$lapse = microtime(true);

		$q = Data::where('region', 'C01')
			->where('province', null)
			->where('district', null)
			->where('city', null)
			->where('confirmed', $data_provinces[null]['confirmed'])
			->where('confirmed_total', $data_provinces[null]['confirmed_total'])
			->where('recovered', $data_provinces[null]['recovered'])
			->where('dead_total', $data_provinces[null]['dead_total'])
			->where('hospitalized_total', $data_provinces[null]['hospitalized_total'])
			->get();
		
		if (count($q) == 0) {

			foreach ($data_provinces as $d) {

				Data::create($d);

			}

			$log .= "Data stored, " . count($data_provinces) . " items (" . round(microtime(true) - $lapse) . " seconds).\n";

		} else {

			$log .= "Data storage SKIPPED (same data) (" . round(microtime(true) - $lapse) . " seconds).\n";

		}
		
		// --------------
		// Importación de archivo de municipios

		$log .= "Starting load of files 'cities-total'.\n";

		$data_cities = [];

		$lapse = microtime(true);

		foreach (config('custom.import.provinces') as $p) {

			$json = file_get_contents(
				config('custom.import.urls.cities-total') . $p,
				false,
				stream_context_create($arrContextOptions)
			);

			$import = json_decode($json, true);

			foreach ($import['data'] as $d) {

				$province = isset($d[0]['cod'][1]) ? $d[0]['cod'][1] : null;
				$district = isset($d[0]['cod'][2]) ? $d[0]['cod'][2] : null;
				$city = isset($d[0]['cod'][4]) ? $d[0]['cod'][4] : null;

				if ($city !== null && !in_array($city, config('custom.import.cities-unknown'))) { // Casos especiales
					// Es un municipio

					$c = City::where('code', $city)->firstOrFail();
					$confirmed_total = isset($d[6]) ? $d[6]['val'] : 0;
					$recovered = isset($d[7]) ? $d[7]['val'] : 0;

					// Insertamos datos
					array_push($data_cities, [
						'date' => $date,
						'region' => 'C01',
						'province' => $province,
						'district' => $district,
						'city' => $city,
						'confirmed' => isset($d[2]) ? $d[2]['val'] : null,
						'confirmed_7d' => isset($d[5]) ? $d[5]['val'] : null,
						'confirmed_14d' => isset($d[3]) ? $d[3]['val'] : null,
						'incidence_14d' => isset($d[4]) ? $d[4]['val'] : null,
						'confirmed_total' => isset($d[6]) ? $d[6]['val'] : null,
						'recovered' => isset($d[7]) ? $d[7]['val'] : null,
						'dead_total' => isset($d[8]) ? $d[8]['val'] : null,
					]);

				}

			}

		}

		$log .= "Files 'cities-total' loaded and parsed (" . round(microtime(true) - $lapse) . " seconds).\n";

		// Comprobamos si los datos son nuevos

		$insert = false;

		$lapse = microtime(true);

		// Elegimos 10 municipios al azar

		for ($i=0; $i<10; $i++) {

			$sample = $data_cities[array_rand($data_cities)];

			$q = Data::where('region', $sample['region'])
				->where('province', $sample['province'])
				->where('district', $sample['district'])
				->where('city', $sample['city'])
				->where('confirmed', $sample['confirmed'])
				->where('confirmed_total', $sample['confirmed_total'])
				->where('recovered', $sample['recovered'])
				->where('dead_total', $sample['dead_total'])
				->get();
			
			if (count($q) == 0) {
				$insert = true;
				break;
			}

		}
		
		if ($insert) {

			foreach ($data_cities as $d) {

				Data::create($d);

			}

			$log .= "Data stored, " . count($data_cities) . " items (" . round(microtime(true) - $lapse) . " seconds).\n";

		} else {

			$log .= "Data storage SKIPPED (same data) (" . round(microtime(true) - $lapse) . " seconds).\n";

		}

		// Import finished
		
		$log .= "IMPORT FINISHED: " . date('Y-m-d H:i:s') . " (" . round(microtime(true) - $start) . " seconds).\n\n";
		
		\DB::table('log')->insert([
			'date' => date('Y-m-d H:i:s'),
			'action' => 'import',
			'text' => $log,
		]);
		
		die('<pre>' . $log);

	}

	// Carga de las listas iniciales de regiones, provincias, distritos y municipios
	public function loadLists()
	{

		$log = "IMPORT (LOAD LISTS) START: " . date('Y-m-d H:i:s') . "\n";

		// Aumentamos tiempo de ejecución del script a 5 minutos
		set_time_limit(0);
		// Aumento de memoria
		ini_set('memory_limit', '1024M');
		// Contexto para evitar la comprobación de certificados en el file_get_contents()
		$arrContextOptions = [
			'ssl' => [
				'verify_peer' => false,
				'verify_peer_name' => false,
			],
		];

		$start = microtime(true);

		$regions = [
			[
				'code' => 'C01',
				'name' => 'Andalucía',
				'population' => 8464411,
			]
		];
		$provinces = [];
		$districts = [];
		$cities = [];
		
		$lapse = microtime(true);

		foreach (config('custom.import.provinces') as $c) {

			$json = file_get_contents(
				config('custom.import.urls.cities-total') . $c,
				false,
				stream_context_create($arrContextOptions)
			);

			$import = json_decode($json, true);

			foreach ($import['data'] as $d) {

				$province = isset($d[0]['cod'][1]) ? $d[0]['cod'][1] : null;
				$district = isset($d[0]['cod'][2]) ? $d[0]['cod'][2] : null;
				$city = isset($d[0]['cod'][4]) ? $d[0]['cod'][4] : null;
				$name = isset($d[0]['des']) ? $d[0]['des'] : null;
				$population = isset($d[1]) ? $d[1]['val'] : null;

				if ($city !== null) {
					// Es un municipio

					if (!isset($cities[$city])) {

						$cities[$city] = [
							'code' => $city,
							'province' => $province,
							'district' => $district,
							'name' => $name,
							'population' => $population,
						];

					}

				} else {

					if ($district !== null) {
						// Es un distrito

						if (!isset($districts[$district])) {

							$districts[$district] = [
								'code' => $district,
								'province' => $province,
								'name' => $name,
								'population' => $population,
							];

						}

					} else {
						// Es una provincia

						if (!isset($provinces[$province])) {

							$provinces[$province] = [
								'code' => $province,
								'name' => $name,
								'population' => $population,
							];

						}

					}

				}

			}

			$log .= "File " . $c . " loaded.\n";

		}

		$log .= "Files loades and parsed (" . round(microtime(true) - $lapse) . " seconds).\n";

		// Actualizamos las listas

		$lapse = microtime(true);
		
		\DB::statement('SET FOREIGN_KEY_CHECKS = 0');
		\DB::table('regions')->truncate();
		\DB::table('provinces')->truncate();
		\DB::table('districts')->truncate();
		\DB::table('cities')->truncate();
		\DB::statement('SET FOREIGN_KEY_CHECKS = 1');

		foreach ($regions as $i) {
			\DB::table('regions')->insert([
				'code' => $i['code'],
				'name' => $i['name'],
				'population' => $i['population'],
			]);
		}

		foreach ($provinces as $i) {
			\DB::table('provinces')->insert([
				'code' => $i['code'],
				'region' => 'C01',
				'name' => $i['name'],
				'population' => $i['population'],
			]);
		}
		
		foreach ($districts as $i) {
			\DB::table('districts')->insert([
				'code' => $i['code'],
				'region' => 'C01',
				'province' => $i['province'],
				'name' => $i['name'],
				'population' => $i['population'],
			]);
		}
		
		foreach ($cities as $i) {
			if (!in_array($i['code'], config('custom.import.cities-unknown'))) { // Casos especiales
				\DB::table('cities')->insert([
					'code' => $i['code'],
					'region' => 'C01',
					'province' => $i['province'],
					'district' => $i['district'],
					'name' => $i['name'],
					'population' => $i['population'],
				]);
			}
		}

		$log .= "Lists inserted (" . round(microtime(true) - $lapse) . ") seconds).\n";
		$log .= count($regions) . " regions.\n";
		$log .= count($provinces) . " provinces.\n";
		$log .= count($districts) . " districts.\n";
		$log .= count($cities) . " cities.\n";

		// Import finished
		
		$log .= "IMPORT FINISHED: " . date('Y-m-d H:i:s') . " (" . round(microtime(true) - $start) . " seconds).\n\n";
		
		// Write log
		
		\DB::table('log')->insert([
			'date' => date('Y-m-d H:i:s'),
			'action' => 'import-lists',
			'text' => $log,
		]);
		
		die('<pre>' . $log);

	}

	// Realiza la carga inicial de datos
	// Datos procedentes de:
	// https://github.com/Pakillo/COVID19-Andalucia
	public function init()
	{

		// Aumentamos tiempo de ejecución del script a 5 minutos
		set_time_limit(0);
		// Aumento de memoria
		ini_set('memory_limit', '1024M');
		// Contexto para evitar la comprobación de certificados en el file_get_contents()
		$arrContextOptions = [
			'ssl' => [
				'verify_peer' => false,
				'verify_peer_name' => false,
			],
		];

		$start = microtime(true);

		// Limpiamos
		\DB::table('data')->truncate();

		// Obtenemos los datos de comunidades, provincias, distritos y municipios
		// Key por nombre

		$regions = Region::getListByName();
		$provinces = Province::getListByName();
		$districts = District::getListByName();
		$cities = City::getListByName();

		// Importación de datos por CA y provincias
		// https://github.com/Pakillo/COVID19-Andalucia/raw/master/datos/acumulados.csv

		$inserts = [];

		$file = fopen('https://github.com/Pakillo/COVID19-Andalucia/raw/master/datos/acumulados.csv', 'r');
		$row = 0;
		
		while (($data = fgetcsv($file, 0, ";")) !== false) {
			
			if ($row > 0 && strpos($data['0'], '/') !== false) {

				$t = explode('/', $data[0]);
				$date = $t[2] . '-' . $t[1] . '-' . $t[0];

				if (!isset($inserts[$date])) {
					$inserts[$date] = [];
				}
				if (!isset($inserts[$date][$data[1]])) {
					$inserts[$date][$data[1]] = [];
				}

				switch (trim($data[2])) {
					case 'Confirmados PDIA': $field = 'confirmed'; break;
					case 'Aumento': $field = 'increase'; break;
					case 'Confirmados PDIA 14 días': $field = 'confirmed_14d'; break;
					case 'Confirmados PDIA 7 días': $field = 'confirmed_7d'; break;
					case 'Total confirmados': $field = 'confirmed_total'; break;
					case 'Hospitalizados': $field = 'hospitalized_total'; break;
					case 'Total UCI': $field = 'uci'; break;
					case 'Fallecidos': $field = 'dead_total'; break;
					case 'Curados': $field = 'recovered'; break;
				}

				$inserts[$date][$data[1]][$field] = $data[3] == '' ? 0 : $data[3];

			}

			$row++;

		}

		fclose($file);

		$inserts = array_reverse($inserts);

		// Insertamos datos en base de datos

		foreach ($inserts as $date => $v) {
			foreach ($v as $loc => $d) {

				$population = $loc == 'Andalucía' ? $regions[$loc]['population'] : $provinces[$loc]['population'];

				\DB::table('data')->insert([
					'date' => $date,
					'region' => 'C01',
					'province' => $loc == 'Andalucía' ? null : $provinces[$loc]['code'],
					'district' => null,
					'city' => null,
					'confirmed' => $d['confirmed'],
					'increase' => $d['increase'],
					'confirmed_7d' => $d['confirmed_7d'],
					'confirmed_14d' => $d['confirmed_14d'],
					'incidence_14d' => $d['confirmed_14d'] / ($population / 100000),
					'confirmed_total' => $d['confirmed_total'],
					'hospitalized_total' => $d['hospitalized_total'],
					'uci' => $d['uci'],
					'recovered' => $d['recovered'],
					'dead_total' => $d['dead_total'],
				]);

			}
		}
		
		// Importación de datos por municipios
		// https://github.com/Pakillo/COVID19-Andalucia/raw/master/datos/municipios.csv

		$inserts = [];

		$file = fopen('https://github.com/Pakillo/COVID19-Andalucia/raw/master/datos/municipios.csv', 'r');
		$row = 0;
		
		while (($data = fgetcsv($file, 0, ",")) !== false) {

			if ($row > 0) {

				if (!isset($cities[$data[3]])) {
					die('Ciudad no reconocida: ' . $data[3]);
				}

				// Casos especiales
				if (in_array($data[3], ['Castellar de la Frontera', 'Jimena de la Frontera', 'Línea de la Concepción (La)', 'San Roque', 'San Martín del Tesorillo'])) {
					$data[2] = 'Campo de Gibraltar Este';
				}
				if (in_array($data[3], ['Algeciras', 'Barrios (Los)', 'Tarifa'])) {
					$data[2] = 'Campo de Gibraltar Oeste';
				}

				if (!isset($districts[$data[2]])) {
					die('Distrito no reconocido: ' . $data[2]);
				}
				if (!isset($provinces[$data[1]])) {
					die('Provincia no reconocida: ' . $data[1]);
				}

				array_push($inserts, [
					'date' => $data[0],
					'province' => $provinces[$data[1]]['code'],
					'district' => $districts[$data[2]]['code'],
					'city' => $cities[$data[3]]['code'],
					'population' => $cities[$data[3]]['population'],
					'confirmed' => $data[4] == 'NA' ? 0 : $data[4],
					'confirmed_14d' => $data[5] == 'NA' ? 0 : $data[5],
					'incidence_14d' => $data[6] == 'NA' ? 0 : $data[6],
					'confirmed_total' => $data[7] == 'NA' ? 0 : $data[7],
					'dead_total' => $data[8] == 'NA' ? 0 : $data[8],
				]);

			}

			$row++;

		}

		fclose($file);

		foreach ($inserts as $d) {

			\DB::table('data')->insert([
				'date' => $d['date'],
				'region' => 'C01',
				'province' => $d['province'],
				'district' => $d['district'],
				'city' => $d['city'],
				'confirmed' => $d['confirmed'],
				'increase' => null,
				'confirmed_7d' => null,
				'confirmed_14d' => $d['confirmed_14d'],
				'incidence_14d' => $d['incidence_14d'],
				'confirmed_total' => $d['confirmed_total'],
				'hospitalized_total' => null,
				'uci' => null,
				'recovered' => null,
				'dead_total' => $d['dead_total'],
			]);
			
		}

		die('ok');

	}

}
