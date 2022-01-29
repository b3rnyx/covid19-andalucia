<!doctype html>
<html class="no-js" lang="">

<head>
	<meta charset="utf-8">
	<title>COVID-19 - Andalucía</title>
	<meta name="description" content="Consulta los datos históricos por provincias y municipios de COVID-19 en Andalucía.">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<link rel="apple-touch-icon" href="favicon.ico">
	
	<meta property="og:url" content="<?= config('app.url') ?>" />
	<meta property="og:type" content="website" />
	<meta property="og:title" content="COVID-19 - Andalucía" />
	<meta property="og:description" content="Consulta los datos históricos por provincias y municipios de COVID-19 en Andalucía." />
	<meta property="og:image" content="<?= config('app.url') ?>images/fb-share.jpg" />
	
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css" rel="stylesheet" />

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
		
		<div class="update row">
			<div class="col-md-6">
				Última actualización: <mark><?= $updated ?></mark>
				<span>Los datos suelen actualizarse de lunes a viernes sobre las 13:00 (los fines de semana no hay actualización).</span>
			</div>
			<div class="col-md-6">
				Última actualización datos hospitalarios: <mark><?= $updated_hospitals ?></mark>
				<span>Los datos de ocupación hospitalaria suelen actualizarse con los datos del día anterior de lunes a viernes sobre las 15:30 (los fines de semana no hay actualización).</span>
			</div>
		</div>
	</header>

	<main>

		<section class="selector row">

			<div class="col-md-6">

				<div class="select-province row">
					<div class="col-12">
						<label>Elige Provincia</label>
						<button type="button" data-code="" class="province-button btn<?= ($selected_province == '' && $selected_city != '') ? ' selected' : '' ?>">Andalucía</button>
					</div>
<?php

foreach ($lists['provinces'] as $i) {
	echo '<div class="col-3">
					<button type="button" data-code="' . $i['code'] . '" class="province-button btn' . ($selected_province == $i['code'] ? ' selected' : '')  .'">
						' . $i['name'] . '
					</button>
				</div>';
}

?>
					<small>Los datos de ocupación hospitalaria sólo están disponibles por provincias.</small>
				</div>

			</div>

			<input type="hidden" id="select_district" name="select_district" value="<?= $selected_district ?>">
			
			<div class="col-md-6">

				<div class="select-city form-group">
					<label for="select_city">Elige Municipio</label>
					<select id="select_city" name="select_city" class="form-control">
						<option value=""<?= $selected_city == '' ? ' selected' : '' ?>>Elige municipio</option>
	<?php

	foreach ($lists['cities'] as $i) {
		echo '<option value="' . $i['code'] . '"' . ($selected_city == $i['code'] ? ' selected' : '')  .'>' . $i['name'] . '</option>';
	}

	?>
					</select>
				</div>

				<div class="days">
					<div class="input-group">
						<label for="select_days">Elige fechas para las gráficas</label>
						<div class="input-group-prepend">
							<span class="input-group-text"><i class="fa fa-calendar"></i></span>
						</div>
						<select id="select_days" class="form-control">
<?php

foreach (config('custom.stats-days') as $k => $v) {

	echo '<option value="' . $k . '">' . $v . '</option>';

}

?>
						</select>
					</div>
				</div>

			</div>

		</section>

		<section class="info">

			<div class="title">
				<div class="name"></div>
				<div class="population">Población: <strong></strong></div>
			</div>

			<div class="data row justify-content-center">

				<div class="level1 col-lg-2 col-md-4 col-sm-6">
					<?= htmlInfo('incidence_14d', 'Incidencia<br>14 días', 'style1'); ?>
					<?= htmlInfo(['hosp_beds_total_percent', 'uci_beds_total_percent'], '<small>Ocupación Hospitalaria</small>', 'style1b'); ?>
				</div>

				<div class="level-group col-lg-10 col-md-8 col-sm-12 col-12">

					<div class="row">

						<div class="level2 col-lg-3 col-md-6 col-sm-6">
							<?= htmlInfo('confirmed_increment', 'Nuevos confirmados<span>*</span>', 'style2'); ?>
							<?= htmlInfo('confirmed_total', 'Confirmados totales', 'style2'); ?>
							<?= htmlInfo('confirmed_14d', 'Confirmados 14 días', 'style3'); ?>
							<?= htmlInfo('confirmed_7d', 'Confirmados 7 días', 'style3'); ?>
							<?= htmlInfo('incidence_7d', 'Incidencia 7 días', 'style3'); ?>
						</div>

						<div class="level2 col-lg-3 col-md-6 col-sm-6">
							<?= htmlInfo('dead_increment', 'Nuevos fallecidos<span>*</span>', 'style2'); ?>
							<?= htmlInfo('dead_total', 'Fallecidos totales', 'style2'); ?>
							<?= htmlInfo('dead_percent', 'Porcentaje fallecidos', 'stylep', 'Respecto a confirmados totales'); ?>
						</div>

						<div class="level2 col-lg-3 col-md-6 col-sm-6">
							<?= htmlInfo('hosp_admissions', 'Nuevos hospitalizados', 'style2'); ?>
							<?= htmlInfo('hosp_beds_covid', 'Hospitalizados COVID', 'style3'); ?>
							<?= htmlInfo('hosp_beds_nocovid', 'Hospitalizados No COVID', 'style3'); ?>
							<?= htmlInfo('hosp_beds_covid_percent', 'Ocupación hospitalaria COVID', 'stylep'); ?>
						</div>

						<div class="level2 col-lg-3 col-md-6 col-sm-6">
							<?= htmlInfo('uci_admissions', 'Nuevos ingresos UCI', 'style2'); ?>
							<?= htmlInfo('uci_beds_covid', 'Ingresados UCI COVID', 'style3'); ?>
							<?= htmlInfo('uci_beds_nocovid', 'Ingresados UCI No COVID', 'style3'); ?>
							<?= htmlInfo('hosp_beds_uci_covid_percent', 'Ocupación UCI COVID', 'stylep'); ?>
						</div>

					</div>

				</div>
				
			</div>
		</section>
		<div class="info-note">Los datos marcados con <span>*</span> son <strong>datos no oficiales</strong> mostrados a título orientativo.</div>

		<section class="graphs">

<?php

foreach (config('custom.stats-items') as $k => $v) {

	if (isset($v['graph'])) {
		echo '<div class="graph-container data-switch switch-' . implode(' switch-', $v['allowed']) . ' col-md-12">
						<div id="graph-' . $k . '" class="graph" data-item="' . $k . '"></div>
						' . (isset($v['description']) ? '<div class="description">' . $v['description'] . '</div>' : '') . '
						<div class="graph-expand">
							<button type="button" class="btn expand" title="Expandir este gráfico" data-item="' . $k . '"><i class="fa fa-angle-double-down"></i><span> Expandir</span></button>
						</div>
					</div>';
	}

}

?>

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

<div class="loader loader-bkg">
	<div class="msg"><i class="fa fa-cog fa-spin"></i> Cargando...</div>
</div>

<script
			  src="https://code.jquery.com/jquery-3.5.1.min.js"
			  integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
			  crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://use.fontawesome.com/b008463dff.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script type="text/javascript" src="<?= config('app.url') ?>vendor/clipboard.min.js"></script>
<script src="<?= config('app.url') ?>js/app.js"></script>

<script>
	app.cfg.url = '<?= config('app.url') ?>';
	app.cfg.url_load = '<?= route('load') ?>';
	app.cfg.lists = <?= json_encode($lists, JSON_UNESCAPED_UNICODE) ?>;
	app.cfg.items = <?= json_encode(config('custom.stats-items'), JSON_UNESCAPED_UNICODE) ?>;
	app.cfg.cookie_name = '<?= config('custom.cookie-name') ?>';
	app.cfg.days_default = '<?= config('custom.stats-days-fefault') ?>';
</script>

</body>

</html>