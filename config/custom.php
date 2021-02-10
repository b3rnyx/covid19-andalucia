<?php

return [
	
	// Paths
	'paths' => [
		'logs' => env('APP_PATH') . '/logs',
	],

	// Importación
	'import' => [
		// Url con los datos diarios por provincias
		'urls' => [
			'provinces-total' => 'https://www.juntadeandalucia.es/institutodeestadisticaycartografia/intranet/admin/rest/v1.0/consulta/44088',
			'provinces-daily' => 'https://www.juntadeandalucia.es/institutodeestadisticaycartografia/intranet/admin/rest/v1.0/consulta/39409',
			'provinces-accumulated' => 'https://www.juntadeandalucia.es/institutodeestadisticaycartografia/intranet/admin/rest/v1.0/consulta/39464',
			'cities-total' => 'https://www.juntadeandalucia.es/institutodeestadisticaycartografia/intranet/admin/rest/v1.0/consulta/',
		],
		// Listado de querys de provincias
		'provinces' => [
			'38665', // Almería
			'38637', // Cádiz
			'38666', // Córdoba
			'38667', // Granada
			'38668', // Huelva
			'38669', // Jaén
			'38674', // Málaga
			'38676', // Sevilla
		],
		// Casos especiales de municipios sin identificar
		'cities-unknown' => ['04NC','11NC','14NC','18NC','21NC','23NC','29NC','41NC'],
	],

	// Fechas del selector de fechas
	'stats-dates' => [
		'7' => 'Mostrar datos de la ultima semana',
		'14' => 'Mostrar datos de las últimas 2 semanas', 
		'30' => 'Mostrar datos del último mes',
		'90' => 'Mostrar datos de los últimos 3 meses',
		'180' => 'Mostrar datos de los últimos 180 días',
		'365' => 'Mostrar datos del último año',
	],

	// Nombre de la cookie
	'cookie-name' => 'covid19and',

	// Listado de items a mostrar
	'stats-items' => [

		'incidence_14d' => [
			'name' => 'Incidencia 14 días',
			'description' => 'Indice en el que se basa la Junta de Andalucía para establecer las restricciones. Casos en los últimos 14 días de COVID-19 con infección activa confirmados por PDIA (Prueba Diagnóstica de Infección Activa, es decir, técnica PCR o test antigénicos rápidos de última generación) por cada 100.000 habitantes.',
			'allowed' => ['region', 'province', 'district', 'city'],
			'type' => 'number',
			'green' => 'desc',
			'graph' => [
				'type' => 'line',
			],
		],
		'population' => [
			'name' => 'Población',
			'allowed' => ['region', 'province', 'district', 'city'],
			'type' => 'number',
		],
		'increase' => [
			'name' => 'Confirmados diarios',
			'description' => 'Aumento con respecto al día anterior en el número de casos de COVID-19 con infección activa confirmados por PDIA (Prueba Diagnóstica de Infección Activa, es decir, técnica PCR o test antigénicos rápidos de última generación).',
			'allowed' => ['region', 'province'],
			'type' => 'number',
			'green' => 'desc',
			'graph' => [
				'type' => 'columns',
			],
		],
		'confirmed' => [
			'name' => 'Confirmados totales',
			'description' => 'Total acumulado de casos de COVID-19 con infección activa confirmados por PDIA (Prueba Diagnóstica de Infección Activa, es decir, técnica PCR o test antigénicos rápidos de última generación).',
			'allowed' => ['region', 'province', 'district', 'city'],
			'type' => 'number',
			'graph' => [
				'type' => 'line',
			],
		],
		'confirmed_14d' => [
			'name' => 'Confirmados 14 días',
			'description' => 'Número acumulado en los últimos 14 días de casos de COVID-19 con infección activa confirmados por PDIA (Prueba Diagnóstica de Infección Activa, es decir, técnica PCR o test antigénicos rápidos de última generación).',
			'allowed' => ['region', 'province', 'district', 'city'],
			'type' => 'number',
			'green' => 'desc',
			'graph' => [
				'type' => 'line',
			],
		],
		'confirmed_7d' => [
			'name' => 'Confirmados 7 días',
			'description' => 'Número acumulado en los últimos 7 días de casos de COVID-19 con infección activa confirmados por PDIA (Prueba Diagnóstica de Infección Activa, es decir, técnica PCR o test antigénicos rápidos de última generación).',
			'allowed' => ['region', 'province', 'district', 'city'],
			'type' => 'number',
			'green' => 'desc',
			'graph' => [
				'type' => 'line',
			],
		],
		'incidence_7d' => [
			'name' => 'Incidencia 7 días',
			'legend' => 'Datos por municipios disponibles a partir del 29/01/2021.',
			'description' => 'Casos en los últimos 7 días de COVID-19 con infección activa confirmados por PDIA (Prueba Diagnóstica de Infección Activa, es decir, técnica PCR o test antigénicos rápidos de última generación) por cada 100.000 habitantes.',
			'allowed' => ['region', 'province'],
			'type' => 'number',
			'green' => 'desc',
			'graph' => [
				'type' => 'line',
			],
		],
		/*'confirmed_total' => [
			'name' => 'Total confirmados',
			'allowed' => ['region', 'province', 'district', 'city'],
			'type' => 'number',
			'graph' => [
				'type' => 'line',
			],
		],*/
		'confirmed_percent' => [
			'name' => 'Porcentaje confirmados',
			'legend' => 'Respecto a la población.',
			'allowed' => ['region', 'province', 'district', 'city'],
			'type' => 'percent',
			'graph' => [
				'type' => 'line',
			],
		],
		'hospitalized' => [
			'name' => 'Hospitalizados diarios',
			'legend' => 'Datos disponibles a partir del 31/01/2021.',
			'allowed' => ['region', 'province'],
			'type' => 'number',
			'green' => 'desc',
			'graph' => [
				'type' => 'columns',
			],
		],
		'hospitalized_total' => [
			'name' => 'Hospitalizados totales',
			'allowed' => ['region', 'province'],
			'type' => 'number',
			'green' => 'desc',
			'graph' => [
				'type' => 'line',
			],
		],
		'hospitalized_percent' => [
			'name' => 'Porcentaje Hospitalizados',
			'legend' => 'Respecto a confirmados.',
			'allowed' => ['region', 'province'],
			'type' => 'percent',
			'graph' => [
				'type' => 'line',
			],
		],
		'uci' => [
			'name' => 'Ingresados en UCI diarios',
			'legend' => 'Datos disponibles a partir del 31/01/2021.',
			'allowed' => ['region', 'province'],
			'type' => 'number',
			'green' => 'desc',
			'graph' => [
				'type' => 'columns',
			],
		],
		'uci_total' => [
			'name' => 'Ingresados en UCI totales',
			'allowed' => ['region', 'province'],
			'type' => 'number',
			'green' => 'desc',
			'graph' => [
				'type' => 'line',
			],
		],
		'uci_percent' => [
			'name' => 'Porcentaje ingresados UCI',
			'legend' => 'Respecto a hospitalizados totales.',
			'allowed' => ['region', 'province'],
			'type' => 'percent',
			'graph' => [
				'type' => 'line',
			],
		],
		'recovered' => [
			'name' => 'Curados totales',
			'legend' => 'Datos por municipios disponibles a partir del 29/01/2021.',
			'allowed' => ['region', 'province', 'district', 'city'],
			'type' => 'number',
			'graph' => [
				'type' => 'line',
			],
		],
		'recovered_percent' => [
			'name' => 'Porcentaje curados',
			'legend' => 'Respecto a confirmados. Datos por municipios disponibles a partir del 29/01/2021.',
			'allowed' => ['region', 'province', 'district', 'city'],
			'type' => 'percent',
			'graph' => [
				'type' => 'line',
			],
		],
		'dead' => [
			'name' => 'Fallecidos diarios',
			'legend' => 'Datos disponibles a partir del 08/02/2021.',
			'allowed' => ['region', 'province'],
			'type' => 'number',
			'green' => 'desc',
			'graph' => [
				'type' => 'columns',
			],
		],
		'dead_total' => [
			'name' => 'Fallecidos totales',
			'allowed' => ['region', 'province', 'district', 'city'],
			'type' => 'number',
			'graph' => [
				'type' => 'line',
			],
		],
		'dead_percent' => [
			'name' => 'Porcentaje fallecidos',
			'legend' => 'Respecto a confirmados.',
			'allowed' => ['region', 'province', 'district', 'city'],
			'type' => 'percent',
			'graph' => [
				'type' => 'line',
			],
		],

	],

	// Valores de referencia de la incidencia


	// Social
	'social' => [
		'share-url' => config('app.url'),
		'share-text' => 'Consulta de manera sencilla los datos actualizados de COVID-19 en Andalucía',
	],

];