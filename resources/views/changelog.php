<!doctype html>
<html class="no-js" lang="">

<head>
	<meta charset="utf-8">
	<title>COVID-19 - Andalucía | Changelog</title>
	<meta name="description" content="Consulta los datos históricos por provincias y municipios de COVID-19 en Andalucía.">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<link rel="apple-touch-icon" href="favicon.ico">
	
	<meta property="og:url" content="<?= config('app.url') ?>" />
	<meta property="og:type" content="website" />
	<meta property="og:title" content="COVID-19 - Andalucía" />
	<meta property="og:description" content="Consulta los datos históricos por provincias y municipios de COVID-19 en Andalucía." />
	<meta property="og:image" content="<?= config('app.url') ?>images/fb-share.jpg" />
	
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">

	<link rel="stylesheet" href="<?= config('app.url') ?>css/app.css">

	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-KGYVFHZDND"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', 'G-KGYVFHZDND');
	</script>

</head>

<body>

<div class="container">

	<header>
		<h1>COVID-19 en Andalucía</h1>
		<h2>
			Consulta los datos históricos por provincias y municipios de COVID-19 en Andalucía.
		</h2>
	</header>

	<main>

		<section class="changelog row justify-content-center">

			<div class="col-md-8">

				<h3>Cambios recientes</h3>
				<nav>
					<ul>
						<li><a href="<?= route('home') ?>" title="Volver a la página" class="btn btn-default"><i class="fa fa-chevron-left"></i> Volver</a></li>
					</ul>
				</nav>

				<article class="version">
					<h4>2 de febrero de 2022</h4>
					<h5>¡La página cumple un año!</h5>
					<p>Ya ha pasado un año desde que arrancó esta página y muchas cosas han cambiado en esta pandemia. La nueva variante Ómicron ha cambiado por completo las <em>reglas del juego</em> y ha desbordado un precario y falto de recursos sistema de atención primaria y por ello ciertos datos como la incidencia han dejado de ser todo lo fiables que eran antes de esta última sexta ola (o avalancha).</p>
					<p>Para adaptar la página a las nuevas circuntancias se han realizado los siguientes cambios:</p>
					<ul>
						<li>Se han añadido nuevos datos de capacidad asistencial hospitalaria procedentes del <a href="https://www.sanidad.gob.es/profesionales/saludPublica/ccayes/alertasActual/nCov/capacidadAsistencial.htm" title="Ministerio de Sanidad">Ministerio de Sanidad</a>. Con estos datos, disponibles desde el 1 de agosto de 2020, se puede evaluar la ocupación hospitalaria, tanto en hospitalización convencional, como en UCI, por pacientes COVID y no COVID. De esta manera se puede crear una imagen de la presión hospitalaria, que complementa al ya menos fiable dato de incidencia. Estos datos <strong>sólo están disponibles por provincia</strong>.</li>
						<li>Se ha modificado el panel de selección de provincia y municipio buscando una mayor claridad y facilidad de uso.</li>
						<li>Se han <strong>añadido nuevos gráficos</strong>:
							<ul>
								<li>Ocupación hospitalaria: un gráfico general en el que poder observar la progresión de la ocupación de plazas hospitalarias en hospitalización convencional y UCI.</li>
								<li>Ingresados por COVID-19: número total de personas ingresadas por COVID-19 en hospitalización convencional y UCI.</li>
								<li>Incidencia 7 días en municipios: desde el 14 de enero de 2022 la Junta de Andalucía ha añadido a sus datos la incidencia a 7 días por municipios.</li>
								<li>Nuevos hospitalizados: número diario de ingresos por COVID-19.</li>
								<li>Nuevos ingresos UCI: número diario de ingresos en UCI por COVID-19.</li>
							</ul>
						</li>
						<li>También se han <strong>eliminado los siguientes gráficos</strong> principalmente por los cambios de paradigma con las nuevas variantes del virus, que han convertido algunos datos en irrelevantes:
							<ul>
								<li>Confirmados totales, confirmados 14 días y confirmados 7 días: las gráficas eran poco útiles, al tratarse de progresiones muy lineales.</li>
								<li>Porcentaje confirmados con respecto a la población: el dato tenía sentido cuando la reinfección era anecdótica, pero las reinfecciones ya son habituales por lo que no tiene sentido comparar ambos datos.</li>
								<li>Gráficas de curados: se trataba de un dato significativo cuando se daba por hecho que no habría reinfecciones. Ya no tiene sentido.</li>
							</ul>
						</li>
						<li>Se ha añadido un nuevo motor de url, que permite compartir en el enlace directamente los datos de una provincia o municpio.</li>
						<li>Añadido nuevo botón para expandir las gráficas.</li>
					</ul>
					<p>Espero que los cambios os sean de provecho y sigáis encontrando esta página tan útil como hasta ahora.</p>
				</article>

			</div>

		</section>

	</main>

	<footer>
		<div class="row">
			<div class="develop col-md-3">
				Desarrollado por Pablo Fernández (<a href="https://twitter.com/b3rny" title="Visitar perfil en Twitter">@b3rny</a>).
				<br><span>Código fuente: <a href="https://github.com/b3rnyx/covid19-andalucia" title="Ver código fuente en Github">GitHub</a>.</span>
			</div>
			<div class="source col-md-6">
				Fuentes de datos:<br>
				<a href="https://www.juntadeandalucia.es/institutodeestadisticaycartografia/badea/informe/anual?CodOper=b3_2314&idNode=42348">Junta de Andalucía</a>, 
				<a href="https://www.mscbs.gob.es/profesionales/saludPublica/ccayes/alertasActual/nCov/capacidadAsistencial.htm">Ministerio de Sanidad</a> 
				y <a href="https://github.com/Pakillo/COVID19-Andalucia">COVID19-Andalucia</a> (por <a href="https://github.com/Pakillo">Pakillo</a>). 
				</ul>
			</div>
			<div class="share col-md-3">
				¡Comparte!
				<ul>
					<li><a href="https://twitter.com/intent/tweet?text=<?= urlencode(config('app.name') . ' - ' . config('custom.social.share-text') . ': ' . config('app.url')) ?>" class="btn" title="Twitter" target="_blank"><i class="fa fa-twitter"></i></a></li>
					<li><a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(config('app.url')) ?>" class="btn" title="Facebook" target="_blank"><i class="fa fa-facebook"></i></a></li>
					<li class="d-xl-none d-lg-none d-md-none"><a href="whatsapp://send?text=<?= urlencode(config('app.name') . ' - ' . config('custom.social.share-text') . ': ' . config('app.url')) ?>" class="btn" title="Whatsapp" target="_blank"><i class="fa fa-whatsapp"></i></a></li>
					<li><a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode(config('app.url')) ?>&title=<?= urlencode(config('app.name') . ' - ' . config('custom.social.share-text')) ?>&summary=&source=" class="btn" title="Linkedin" target="_blank"><i class="fa fa-linkedin"></i></a></li>
					<li><button type="button" data-clipboard-text="<?= config('app.url') ?>" class="btn copy"><i class="fa fa-link"></i></button></li>
				</ul>
			</div>
		</div>
	</footer>

</div>

<script
			  src="https://code.jquery.com/jquery-3.5.1.min.js"
			  integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
			  crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
<script src="https://use.fontawesome.com/b008463dff.js"></script>

</body>

</html>