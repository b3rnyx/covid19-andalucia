<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
		// Aumentamos tiempo de ejecución del script a 5 minutos
		set_time_limit(0);
		// Aumento de memoria
		ini_set('memory_limit', '1024M');
	}

	public function import()
	{

		$log = "IMPORT START: " . date('Y-m-d H:i:s') . "\n";

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

			$data_provinces[$province] = [
				'date' => $date,
				'region' => 'C01',
				'province' => $province,
				'district' => null,
				'city' => null,
				'confirmed_total' => isset($d[5]) ? $d[5]['val'] : null,
				'confirmed_14d' => isset($d[7]) ? $d[7]['val'] : null,
				'incidence_14d' => isset($d[8]) ? $d[8]['val'] : null,
				'confirmed_7d' => isset($d[9]) ? $d[9]['val'] : null,
				'incidence_7d' => isset($d[10]) ? $d[10]['val'] : null,
				'recovered_total' => isset($d[11]) ? $d[11]['val'] : null,
				'dead_total' => isset($d[12]) ? $d[12]['val'] : null,
				'legacy_confirmed_total' => isset($d[3]) ? $d[3]['val'] : null,
				'created_at' => date('Y-m-d H:i:s'),
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

			$data_provinces[$province]['legacy_hospitalized'] = isset($d[4]) ? $d[4]['val'] : null;
			$data_provinces[$province]['legacy_uci'] = isset($d[5]) ? $d[5]['val'] : null;
			$data_provinces[$province]['legacy_dead'] = isset($d[6]) ? $d[6]['val'] : null;

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

			$data_provinces[$province]['hospitalized_total'] = isset($d[7]) ? $d[7]['val'] : null;
			$data_provinces[$province]['uci_total'] = isset($d[8]) ? $d[8]['val'] : null;
			$data_provinces[$province]['legacy_increase'] = isset($d[3]) ? $d[3]['val'] : null;

		}

		$log .= "File 'provinces-accumulated' loaded and parsed (" . round(microtime(true) - $lapse) . " seconds).\n";

		// Comprobamos si los datos son nuevos

		$lapse = microtime(true);

		$q = Data::where('region', 'C01')
			->where('province', null)
			->where('district', null)
			->where('city', null)
			->where('confirmed_total', $data_provinces[null]['confirmed_total'])
			->where('confirmed_14d', $data_provinces[null]['incidence_14d'])
			->where('hospitalized_total', $data_provinces[null]['hospitalized_total'])
			->where('recovered_total', $data_provinces[null]['recovered_total'])
			->where('dead_total', $data_provinces[null]['dead_total'])
			->get();
		
		if (count($q) == 0) {
			// Son datos nuevos, insertamos

			// Primero borramos los datos antiguos (si los hubiese)
			$delete = Data::where('date', $date)
									->whereNull('city')
									->delete();

			// Insertamos datos
			foreach ($data_provinces as $d) {

				$increments = Data::getIncrements($d);

				Data::create(array_merge($d, $increments));

			}

			$log .= "Data stored, " . count($data_provinces) . " items (" . round(microtime(true) - $lapse) . " seconds).\n";

		} else {

			$log .= "Data storage SKIPPED (same data) (" . round(microtime(true) - $lapse) . " seconds).\n";

		}
		
		// --------------
		// Importación de archivo de municipios

		/*
		Índices usados:
		1 -> Población
		2 -> Confirmados PDIA
		3 -> Confirmados PDIA 14 días
		4 -> Tasa PDIA 14 días
		5 -> Confirmados PDIA 7 días
		6 -> Tasa PDIA 7 días
		7 -> Total confirmados
		8 -> Curados
		9 -> Fallecidos
		*/

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

					// Insertamos datos
					array_push($data_cities, [
						'date' => $date,
						'region' => 'C01',
						'province' => $province,
						'district' => $district,
						'city' => $city,
						'confirmed_total' => isset($d[2]) ? $d[2]['val'] : null,
						'confirmed_14d' => isset($d[3]) ? $d[3]['val'] : null,
						'incidence_14d' => isset($d[4]) ? $d[4]['val'] : null,
						'confirmed_7d' => isset($d[5]) ? $d[5]['val'] : null,
						'incidence_7d' => isset($d[6]) ? $d[6]['val'] : null,
						'recovered_total' => isset($d[8]) ? $d[8]['val'] : null,
						'dead_total' => isset($d[9]) ? $d[9]['val'] : null,
						'legacy_confirmed_total' => isset($d[7]) ? $d[7]['val'] : null,
						'created_at' => date('Y-m-d H:i:s'),
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
				->where('confirmed_total', $sample['confirmed_total'])
				->where('confirmed_14d', $sample['confirmed_14d'])
				->where('recovered_total', $sample['recovered_total'])
				->where('dead_total', $sample['dead_total'])
				->get();
			
			if (count($q) == 0) {
				$insert = true;
				break;
			}

		}
		
		if ($insert) {
			// Son datos nuevos, insertamos

			// Primero borramos los datos antiguos (si los hubiese)
			$delete = Data::where('date', $date)
									->whereNotNull('city')
									->delete();

			// Insertamos datos
			foreach ($data_cities as $d) {

				$increments = Data::getIncrements($d);

				Data::create(array_merge($d, $increments));

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
		
		die($log);

	}

	// Importa datos de ocupación hospitalaria de la web del ministerio
	public function importHospitals()
	{

		$log = "IMPORT HOSPITALS START: " . date('Y-m-d H:i:s') . "\n";

		$start = microtime(true);

		// Datos iniciales
		$date_file = date('Y-m-d');
		$date_data = date('Y-m-d', strtotime('yesterday'));
		$province_codes = array_map(function ($v) { return intval($v); }, Province::all()->pluck('code')->toArray());

		// Comprobación
		$i = Data::where('date', $date_data)
							->whereNotNull('hosp_beds')
							->first();

		if ($i) {
			// Ya se han eimportado los datos

			$log .= "Skipped: data already imported.\n";

			\DB::table('log')->insert([
				'date' => date('Y-m-d H:i:s'),
				'action' => 'import_hospitals',
				'text' => $log,
			]);
			
			die($log);

		}


		// --------------
		// Importación de archivo

		$file_path = str_replace('[dmY]', date('dmY', strtotime($date_file)), config('custom.import.urls.hospitals'));

		$log .= "Starting load of file '" . $file_path . "'.\n";

		$data = [];

		$lapse = microtime(true);

		try {

			$file = fopen($file_path, 'r');

		} catch (\Exception $e) {
			// ERROR: No se encontró el archivo
			
			$log .= "ERROR: " . $e->getMessage() . "\n";

			\DB::table('log')->insert([
				'date' => date('Y-m-d H:i:s'),
				'action' => 'import_hospitals',
				'text' => $log,
			]);
			
			die($log);
			
		}

		$inserts = [];
		$row = 0;
		
		while (($data = fgetcsv($file, 0, ";")) !== false) {
			
			if ($row > 0 && strpos($data['0'], '/') !== false) {

				$t = explode('/', trim($data[0]));
				$d = $t[2] . '-' . $t[1] . '-' . $t[0];
				$unit = utf8_encode(trim($data[1]));
				$province = trim($data[4]);

				if ($d != $date_data || !in_array($province, $province_codes)) {
					continue;
				}
				
				$p = str_pad($province, 2, '0', STR_PAD_LEFT);

				if (!isset($inserts[$p])) {
					$inserts[$p] = [];
				}

				switch ($unit) {

					case 'Hospitalización convencional':
						$inserts[$p]['hosp_beds'] = intval(trim($data[6]));
						$inserts[$p]['hosp_beds_covid'] = intval(trim($data[7]));
						$inserts[$p]['hosp_beds_nocovid'] = intval(trim($data[8]));
						$inserts[$p]['hosp_admissions'] = intval(trim($data[9]));
						$inserts[$p]['hosp_discharges'] = intval(trim($data[10]));
						break;

					case 'U. Críticas CON respirador':
						$inserts[$p]['hosp_uci_resp_beds'] = intval(trim($data[6]));
						$inserts[$p]['hosp_uci_resp_beds_covid'] = intval(trim($data[7]));
						$inserts[$p]['hosp_uci_resp_beds_nocovid'] = intval(trim($data[8]));
						$inserts[$p]['hosp_uci_resp_admissions'] = intval(trim($data[9]));
						$inserts[$p]['hosp_uci_resp_discharges'] = intval(trim($data[10]));
						break;

					case 'U. Críticas SIN respirador':
						$inserts[$p]['hosp_uci_beds'] = intval(trim($data[6]));
						$inserts[$p]['hosp_uci_beds_covid'] = intval(trim($data[7]));
						$inserts[$p]['hosp_uci_beds_nocovid'] = intval(trim($data[8]));
						$inserts[$p]['hosp_uci_admissions'] = intval(trim($data[9]));
						$inserts[$p]['hosp_uci_discharges'] = intval(trim($data[10]));
						break;

				}

			}

			$row++;

		}

		fclose($file);

		$log .= "File '" . $file_path . "' loaded and parsed (" . round(microtime(true) - $lapse) . " seconds).\n";

		// Actualizamos/insertamos los datos

		$lapse = microtime(true);

		foreach ($inserts as $p => $d) {

			$increments = Data::getHospitalIncrements($date_data, $p, $d);

			Data::updateOrCreate(
				[
					'date' => $date_data,
					'region' => 'C01',
					'province' => $p, 
					'city' => null
				],
				[
					'hosp_beds' => $d['hosp_beds'],
					'hosp_beds_covid' => $d['hosp_beds_covid'],
					'hosp_beds_covid_increment' => $increments['hosp_beds_covid_increment'],
					'hosp_beds_nocovid' => $d['hosp_beds_nocovid'],
					'hosp_admissions' => $d['hosp_admissions'],
					'hosp_admissions_increment' => $increments['hosp_admissions_increment'],
					'hosp_discharges' => $d['hosp_discharges'],
					'hosp_uci_resp_beds' => $d['hosp_uci_resp_beds'],
					'hosp_uci_resp_beds_covid' => $d['hosp_uci_resp_beds_covid'],
					'hosp_uci_resp_beds_covid_increment' => $increments['hosp_uci_resp_beds_covid_increment'],
					'hosp_uci_resp_beds_nocovid' => $d['hosp_uci_resp_beds_nocovid'],
					'hosp_uci_resp_admissions' => $d['hosp_uci_resp_admissions'],
					'hosp_uci_resp_admissions_increment' => $increments['hosp_uci_resp_admissions_increment'],
					'hosp_uci_resp_discharges' => $d['hosp_uci_resp_discharges'],
					'hosp_uci_beds' => $d['hosp_uci_beds'],
					'hosp_uci_beds_covid' => $d['hosp_uci_beds_covid'],
					'hosp_uci_beds_covid_increment' => $increments['hosp_uci_beds_covid_increment'],
					'hosp_uci_beds_nocovid' => $d['hosp_uci_beds_nocovid'],
					'hosp_uci_admissions' => $d['hosp_uci_admissions'],
					'hosp_uci_admissions_increment' => $increments['hosp_uci_admissions_increment'],
					'hosp_uci_discharges' => $d['hosp_uci_discharges'],
					'created_at' => date('Y-m-d H:i:s'),
				]
			);

		}

		$log .= "Data stored, " . count($inserts) . " items (" . round(microtime(true) - $lapse) . " seconds).\n";

		// Import finished
		
		$log .= "IMPORT FINISHED: " . date('Y-m-d H:i:s') . " (" . round(microtime(true) - $start) . " seconds).\n\n";
		
		\DB::table('log')->insert([
			'date' => date('Y-m-d H:i:s'),
			'action' => 'import_hospitals',
			'text' => $log,
		]);
		
		die($log);

	}

	// Carga de las listas iniciales de regiones, provincias, distritos y municipios
	public function loadLists()
	{

		$log = "IMPORT (LOAD LISTS) START: " . date('Y-m-d H:i:s') . "\n";

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
		
		die($log);

	}

	// Realiza la carga inicial de datos
	// Datos procedentes de:
	// https://github.com/Pakillo/COVID19-Andalucia
	public function init()
	{

		// Contexto para evitar la comprobación de certificados en el file_get_contents()
		$arrContextOptions = [
			'ssl' => [
				'verify_peer' => false,
				'verify_peer_name' => false,
			],
		];

		$start = microtime(true);

		// Limpiamos
		//\DB::table('data')->truncate();

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
					case 'Confirmados PDIA': $field = 'confirmed_total'; break;
					case 'Aumento': $field = 'legacy_increase'; break;
					case 'Confirmados PDIA 14 días': $field = 'confirmed_14d'; break;
					case 'Confirmados PDIA 7 días': $field = 'confirmed_7d'; break;
					case 'Total confirmados': $field = 'legacy_confirmed_total'; break;
					case 'Hospitalizados': $field = 'hospitalized_total'; break;
					case 'Total UCI': $field = 'uci_total'; break;
					case 'Fallecidos': $field = 'dead_total'; break;
					case 'Curados': $field = 'recovered_total'; break;
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
					'confirmed_total' => $d['confirmed_total'],
					'legacy_increase' => $d['legacy_increase'],
					'confirmed_7d' => $d['confirmed_7d'],
					'confirmed_14d' => $d['confirmed_14d'],
					'incidence_14d' => $d['confirmed_14d'] / ($population / 100000),
					'legacy_confirmed_total' => $d['legacy_confirmed_total'],
					'hospitalized_total' => $d['hospitalized_total'],
					'uci_total' => $d['uci_total'],
					'recovered_total' => $d['recovered_total'],
					'dead_total' => $d['dead_total'],
					'created_at' => date('Y-m-d H:i:s'),
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
					'confirmed_total' => $data[4] == 'NA' ? 0 : $data[4],
					'confirmed_14d' => $data[5] == 'NA' ? 0 : $data[5],
					'incidence_14d' => $data[6] == 'NA' ? 0 : $data[6],
					'legacy_confirmed_total' => $data[7] == 'NA' ? 0 : $data[7],
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
				'confirmed_total' => $d['confirmed_total'],
				'legacy_increase' => null,
				'confirmed_7d' => null,
				'confirmed_14d' => $d['confirmed_14d'],
				'incidence_14d' => $d['incidence_14d'],
				'legacy_confirmed_total' => $d['legacy_confirmed_total'],
				'hospitalized_total' => null,
				'uci_total' => null,
				'recovered_total' => null,
				'dead_total' => $d['dead_total'],
				'created_at' => date('Y-m-d H:i:s'),
			]);
			
		}

		die('ok');

	}

	// Inicializa los datos de ocupación hospitalaria
	public function initHospitals()
	{

		$log = "IMPORT HOSPITALS START: " . date('Y-m-d H:i:s') . "\n";

		$start = microtime(true);

		// Datos iniciales
		$date_file = '2022-01-11';
		$province_codes = array_map(function ($v) { return intval($v); }, Province::all()->pluck('code')->toArray());

		// --------------
		// Importación de archivo

		$file_path = str_replace('[dmY]', date('dmY', strtotime($date_file)), config('custom.import.urls.hospitals'));

		$log .= "Starting load of file '" . $file_path . "'.\n";

		$data = [];

		$lapse = microtime(true);

		try {

			$file = fopen($file_path, 'r');

		} catch (\Exception $e) {
			// ERROR: No se encontró el archivo
			
			$log .= "ERROR: " . $e->getMessage() . "\n";
			
			die($log);
			
		}

		$inserts = [];
		$row = 0;
		
		while (($data = fgetcsv($file, 0, ";")) !== false) {
			
			if ($row > 0 && strpos($data['0'], '/') !== false) {

				$t = explode('/', trim($data[0]));
				$d = $t[2] . '-' . $t[1] . '-' . $t[0];
				$unit = utf8_encode(trim($data[1]));
				$province = trim($data[4]);

				if (!in_array($province, $province_codes)) {
					continue;
				}
				
				$p = str_pad($province, 2, '0', STR_PAD_LEFT);

				if (!isset($inserts[$d])) {
					$inserts[$p] = [];
				}
				if (!isset($inserts[$d][$p])) {
					$inserts[$d][$p] = [];
				}

				switch ($unit) {

					case 'Hospitalización convencional':
						$inserts[$d][$p]['hosp_beds'] = intval(trim($data[6]));
						$inserts[$d][$p]['hosp_beds_covid'] = intval(trim($data[7]));
						$inserts[$d][$p]['hosp_beds_nocovid'] = intval(trim($data[8]));
						$inserts[$d][$p]['hosp_admissions'] = intval(trim($data[9]));
						$inserts[$d][$p]['hosp_discharges'] = intval(trim($data[10]));
						break;

					case 'U. Críticas CON respirador':
						$inserts[$d][$p]['hosp_uci_resp_beds'] = intval(trim($data[6]));
						$inserts[$d][$p]['hosp_uci_resp_beds_covid'] = intval(trim($data[7]));
						$inserts[$d][$p]['hosp_uci_resp_beds_nocovid'] = intval(trim($data[8]));
						$inserts[$d][$p]['hosp_uci_resp_admissions'] = intval(trim($data[9]));
						$inserts[$d][$p]['hosp_uci_resp_discharges'] = intval(trim($data[10]));
						break;

					case 'U. Críticas SIN respirador':
						$inserts[$d][$p]['hosp_uci_beds'] = intval(trim($data[6]));
						$inserts[$d][$p]['hosp_uci_beds_covid'] = intval(trim($data[7]));
						$inserts[$d][$p]['hosp_uci_beds_nocovid'] = intval(trim($data[8]));
						$inserts[$d][$p]['hosp_uci_admissions'] = intval(trim($data[9]));
						$inserts[$d][$p]['hosp_uci_discharges'] = intval(trim($data[10]));
						break;

				}

			}

			$row++;

		}

		fclose($file);

		$log .= "File '" . $file_path . "' loaded and parsed (" . round(microtime(true) - $lapse) . " seconds).\n";

		// Actualizamos/insertamos los datos

		$lapse = microtime(true);

		foreach ($inserts as $date => $data) {
			foreach ($data as $p => $d) {

				//$increments = Data::getHospitalIncrements($date, $p, $d);

				$item = Data::where('date', $date)
									->where('region', 'C01')
									->where('province', $p)
									->whereNull('city')
									->orderBy('id', 'asc')
									->first();
				
				if ($item) {

					$item->update([
						'hosp_beds' => $d['hosp_beds'],
						'hosp_beds_covid' => $d['hosp_beds_covid'],
						'hosp_beds_nocovid' => $d['hosp_beds_nocovid'],
						'hosp_admissions' => $d['hosp_admissions'],
						'hosp_discharges' => $d['hosp_discharges'],
						'hosp_uci_resp_beds' => $d['hosp_uci_resp_beds'],
						'hosp_uci_resp_beds_covid' => $d['hosp_uci_resp_beds_covid'],
						'hosp_uci_resp_beds_nocovid' => $d['hosp_uci_resp_beds_nocovid'],
						'hosp_uci_resp_admissions' => $d['hosp_uci_resp_admissions'],
						'hosp_uci_resp_discharges' => $d['hosp_uci_resp_discharges'],
						'hosp_uci_beds' => $d['hosp_uci_beds'],
						'hosp_uci_beds_covid' => $d['hosp_uci_beds_covid'],
						'hosp_uci_beds_nocovid' => $d['hosp_uci_beds_nocovid'],
						'hosp_uci_admissions' => $d['hosp_uci_admissions'],
						'hosp_uci_discharges' => $d['hosp_uci_discharges'],
					]);

				}

			}
		}

		// Actualizamos incrementos
		$i = Data::whereNotNull('hosp_beds')
								->orderBy('id', 'asc')
								->get();

		foreach ($i as $item) {

			$increments = Data::getHospitalIncrements($item->date, $item->province, $item->toArray());

			$item->update([
				'hosp_beds_covid_increment' => $increments['hosp_beds_covid_increment'],
				'hosp_admissions_increment' => $increments['hosp_admissions_increment'],
				'hosp_uci_resp_beds_covid_increment' => $increments['hosp_uci_resp_beds_covid_increment'],
				'hosp_uci_resp_admissions_increment' => $increments['hosp_uci_resp_admissions_increment'],
				'hosp_uci_beds_covid_increment' => $increments['hosp_uci_beds_covid_increment'],
				'hosp_uci_admissions_increment' => $increments['hosp_uci_admissions_increment'],
			]);

		}

		$log .= "Data stored, " . count($inserts) . " items (" . round(microtime(true) - $lapse) . " seconds).\n";

		// Import finished
		
		$log .= "IMPORT FINISHED: " . date('Y-m-d H:i:s') . " (" . round(microtime(true) - $start) . " seconds).\n\n";
		
		die($log);

	}

	// Restaura un día perdido
	// Datos procedentes de:
	// https://github.com/Pakillo/COVID19-Andalucia
	public function restore(Request $request)
	{

		// Contexto para evitar la comprobación de certificados en el file_get_contents()
		$arrContextOptions = [
			'ssl' => [
				'verify_peer' => false,
				'verify_peer_name' => false,
			],
		];

		$start = microtime(true);

		$restore_date = $request->date;

		// Primero borramos los datos antiguos (si los hubiese)
		$delete = Data::where('date', $restore_date)
								->delete();

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

				if ($date != $restore_date) {
					continue;
				}
				
				if (!isset($inserts[$date])) {
					$inserts[$date] = [];
				}
				if (!isset($inserts[$date][$data[1]])) {
					$inserts[$date][$data[1]] = [];
				}

				switch (trim($data[2])) {
					case 'Confirmados PDIA': $field = 'confirmed_total'; break;
					case 'Aumento': $field = 'legacy_increase'; break;
					case 'Confirmados PDIA 14 días': $field = 'confirmed_14d'; break;
					case 'Confirmados PDIA 7 días': $field = 'confirmed_7d'; break;
					case 'Total confirmados': $field = 'legacy_confirmed_total'; break;
					case 'Hospitalizados': $field = 'hospitalized_total'; break;
					case 'Total UCI': $field = 'uci_total'; break;
					case 'Fallecidos': $field = 'dead_total'; break;
					case 'Curados': $field = 'recovered_total'; break;
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
					'confirmed_total' => $d['confirmed_total'],
					'legacy_increase' => $d['legacy_increase'],
					'confirmed_7d' => $d['confirmed_7d'],
					'confirmed_14d' => $d['confirmed_14d'],
					'incidence_14d' => $d['confirmed_14d'] / ($population / 100000),
					'legacy_confirmed_total' => $d['legacy_confirmed_total'],
					'hospitalized_total' => $d['hospitalized_total'],
					'uci_total' => $d['uci_total'],
					'recovered_total' => $d['recovered_total'],
					'dead_total' => $d['dead_total'],
					'created_at' => date('Y-m-d H:i:s'),
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

				if ($data[0] != $restore_date) {
					continue;
				}

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
					'confirmed_total' => $data[4] == 'NA' ? 0 : $data[4],
					'confirmed_14d' => $data[5] == 'NA' ? 0 : $data[5],
					'incidence_14d' => $data[6] == 'NA' ? 0 : $data[6],
					'legacy_confirmed_total' => $data[7] == 'NA' ? 0 : $data[7],
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
				'confirmed_total' => $d['confirmed_total'],
				'legacy_increase' => null,
				'confirmed_7d' => null,
				'confirmed_14d' => $d['confirmed_14d'],
				'incidence_14d' => $d['incidence_14d'],
				'legacy_confirmed_total' => $d['legacy_confirmed_total'],
				'hospitalized_total' => null,
				'uci_total' => null,
				'recovered_total' => null,
				'dead_total' => $d['dead_total'],
				'created_at' => date('Y-m-d H:i:s'),
			]);
			
		}

		// Actualización de incrementos
		$this->updateIncrements($restore_date);

		die('ok');

	}

	// Actualizamos los incrementos de la base de datos
	public function updateIncrements($date = null)
	{

		if ($date == null) {

			$data = Data::orderBy('id', 'asc')->get();

		} else {

			$data = Data::where('date', $date)->orderBy('id', 'asc')->get();

		}

		foreach ($data as $d) {

			$increments = Data::getIncrements($d->toArray());

			$d->update($increments);

		}

		if ($date == null) {
			die('ok');
		}

	}

	// Borra los elementos duplicados (se queda con los últimos insertados)
	public function deleteDuplicated()
	{

		echo "Iniciando borrado de duplicados...<br>";

		// Fechas
		$dates = Data::whereNotNull('date')
									->groupBy('date')
									->orderBy('date', 'asc')
									->get()
									->pluck('date')
									->toArray();
		
		// Elementos
		$regions = Region::all();
		$provinces = Province::all();
		$cities = City::all();

		foreach ($dates as $date) {

			echo $date . ": ";

			// Regiones
			$num = 0;
			foreach ($regions as $element) {
				$last = Data::where('region', $element->code)
										->whereNull('province')
										->where('date', $date)
										->orderBy('id', 'desc')
										->first();
				if ($last) {
					$deleted = Data::where('region', $element->code)
													->whereNull('province')
													->where('date', $date)
													->where('id', '!=', $last->id)
													->delete();
					$num += $deleted;
				}
			}
			echo $num . " en Regiones. ";

			// Provincias
			$num = 0;
			foreach ($provinces as $element) {
				$last = Data::where('province', $element->code)
										->whereNull('city')
										->where('date', $date)
										->orderBy('id', 'desc')
										->first();
				if ($last) {
					$deleted = Data::where('province', $element->code)
													->whereNull('city')
													->where('date', $date)
													->where('id', '!=', $last->id)
													->delete();
					$num += $deleted;
				}
			}
			echo $num . " en Provincias.";

			// Municipios
			$num = 0;
			foreach ($cities as $element) {
				$last = Data::where('city', $element->code)
										->where('date', $date)
										->orderBy('id', 'desc')
										->first();
				if ($last) {
					$deleted = Data::where('city', $element->code)
													->where('date', $date)
													->where('id', '!=', $last->id)
													->delete();
					$num += $deleted;
				}
			}
			echo $num . " en Ciudades.<br>";

		}

		die('ok');

	}

}
