<!doctype html>
<html class="no-js" lang="">

<head>
	<meta charset="utf-8">
	<title>COVID-19 - Andalucía</title>
	<meta name="description" content="Consulta los datos históricos por municipios de COVID-19 en Andalucía.">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<link rel="apple-touch-icon" href="favicon.ico">
	
	<meta property="og:url" content="<?= config('app.url') ?>" />
	<meta property="og:type" content="website" />
	<meta property="og:title" content="COVID-19 - Andalucía" />
	<meta property="og:description" content="Consulta los datos históricos por municipios de COVID-19 en Andalucía." />
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
			Consulta los datos históricos por municipios de COVID-19 en Andalucía.
		</h2>
		
		<div class="update">
			Última actualización: <mark><?= $updated ?></mark>
			<span>Los datos suelen actualizarse de lunes a viernes sobre las 13:00 (los fines de semana no hay actualización).</span>
		</div>
	</header>

	<main>

		<section class="selector row">

			<div class="select-province col-md-6">
				<div class="form-group">
					<label for="select_province">Provincia</label>
					<select id="select_province" name="select_province" class="form-control">
						<option value=""<?php echo $selected_province == '' ? ' selected' : '' ?>>Andalucía</option>
	<?php

	foreach ($lists['provinces'] as $i) {
		echo '<option value="' . $i['code'] . '"' . ($selected_province == $i['code'] ? ' selected' : '')  .'>' . $i['name'] . '</option>';
	}

	?>
					</select>
				</div>
			</div>

			<div class="select-district col-md-4">
				<div class="form-group">
					<label for="select_district">Distrito</label>
					<select id="select_district" name="select_district" class="form-control">
						<option value=""<?php echo $selected_district == '' ? ' selected' : '' ?>>Elige distrito</option>
	<?php

	foreach ($lists['districts'] as $i) {
		echo '<option value="' . $i['code'] . '"' . ($selected_district == $i['code'] ? ' selected' : '')  .'>' . $i['name'] . '</option>';
	}

	?>
					</select>
				</div>
			</div>

			
			<div class="select-city col-md-6">
				<div class="form-group">
					<label for="select_city">Municipio</label>
					<select id="select_city" name="select_city" class="form-control">
						<option value=""<?php echo $selected_city == '' ? ' selected' : '' ?>>Elige municipio</option>
	<?php

	foreach ($lists['cities'] as $i) {
		echo '<option value="' . $i['code'] . '"' . ($selected_city == $i['code'] ? ' selected' : '')  .'>' . $i['name'] . '</option>';
	}

	?>
					</select>
				</div>
			</div>

		</section>

		<section class="info">

			<div class="title">
				<div class="name"></div>
				<div class="population">Población: <strong></strong></div>
			</div>

			<div class="data row">

				<div class="level1 col-lg-2 col-md-4 col-sm-6 col-6">
					<?php echo htmlInfo('incidence_14d', 'Incidencia<br>14 días', 'style1'); ?>
				</div>

				<div class="level2 col-lg-2 col-md-4 col-sm-6 col-6">
					<?php echo htmlInfo('confirmed_increment', 'Nuevos confirmados<span>*</span>', 'style2'); ?>
					<?php echo htmlInfo('confirmed_total', 'Confirmados totales', 'style2'); ?>
					<?php echo htmlInfo('confirmed_14d', 'Confirmados 14 días', 'style3'); ?>
					<?php echo htmlInfo('confirmed_7d', 'Confirmados 7 días', 'style3'); ?>
					<?php echo htmlInfo('incidence_7d', 'Incidencia 7 días', 'style3'); ?>
					<?php echo htmlInfo('confirmed_percent', 'Porcentaje confirmados', 'stylep', 'Respecto a la población'); ?>
				</div>

				<div class="level2 col-lg-2 col-md-4 col-sm-6 col-6">
					<?php echo htmlInfo('recovered_increment', 'Nuevos curados<span>*</span>', 'style2'); ?>
					<?php echo htmlInfo('recovered_total', 'Curados totales', 'style2'); ?>
					<?php echo htmlInfo('recovered_percent', 'Porcentaje curados', 'stylep', 'Respecto a confirmados totales'); ?>
				</div>

				<div class="level2 col-lg-2 col-md-4 col-sm-6 col-6">
					<?php echo htmlInfo('dead_increment', 'Nuevos fallecidos<span>*</span>', 'style2'); ?>
					<?php echo htmlInfo('dead_total', 'Fallecidos totales', 'style2'); ?>
					<?php echo htmlInfo('dead_percent', 'Porcentaje fallecidos', 'stylep', 'Respecto a confirmados totales'); ?>
				</div>

				<div class="level2 col-lg-2 col-md-4 col-sm-6 col-6">
					<?php echo htmlInfo('hospitalized_increment', 'Nuevos hospitalizados<span>*</span>', 'style2'); ?>
					<?php echo htmlInfo('hospitalized_total', 'Hospitalizados totales', 'style3'); ?>
					<?php echo htmlInfo('hospitalized_percent', 'Porcentaje hospitalizados', 'stylep', 'Respecto a confirmados totales'); ?>
				</div>

				<div class="level2 col-lg-2 col-md-4 col-sm-6 col-6">
					<?php echo htmlInfo('uci_increment', 'Nuevos ingresos UCI<span>*</span>', 'style2'); ?>
					<?php echo htmlInfo('uci_total', 'Ingresados UCI totales', 'style3'); ?>
					<?php echo htmlInfo('uci_percent', 'Porcentaje ingresados UCI', 'stylep', 'Respecto a hospitalizados totales'); ?>
				</div>
				
			</div>
		</section>
		<div class="info-note">Los datos marcados con <span>*</span> son <strong>datos no oficiales</strong> mostrados a título orientativo.</div>

		<section class="graphs">

			<div class="dates">
				<div class="input-group">
  				<div class="input-group-prepend">
    				<span class="input-group-text"><i class="fa fa-calendar"></i></span>
  				</div>
					<select id="select_dates" class="form-control">
<?php

foreach (config('custom.stats-dates') as $k => $v) {

	echo '<option value="' . $k . '">' . $v . '</option>';

}

?>
					</select>
				</div>
			</div>

<?php

foreach (config('custom.stats-items') as $k => $v) {

	if (isset($v['graph'])) {
		echo '<div class="graph-container data-switch switch-' . implode(' switch-', $v['allowed']) . ' col-md-12">
						<div id="graph-' . $k . '" class="graph" data-item="' . $k . '"></div>
						' . (isset($v['description']) ? '<div class="description">' . $v['description'] . '</div>' : '') . '
					</div>';
	}

}

?>

		</section>

	</main>

	<footer>
		<div class="row">
			<div class="develop col-md-4">
				Desarrollado por Pablo Fernández (<a href="https://twitter.com/b3rny" title="Visitar perfil en Twitter">@b3rny</a>).
				<br><span>Código fuente disponible en <a href="https://github.com/b3rnyx/covid19-andalucia" title="Ver código fuente en Github">GitHub</a>.</span>
			</div>
			<div class="source col-md-4">
				Fuentes de datos:
				<ul>
					<li>Diario <span>(desde el 01/02/2021)</span>: <a href="https://www.juntadeandalucia.es/institutodeestadisticaycartografia/badea/informe/anual?CodOper=b3_2314&idNode=42348">Junta de Andalucía</a>.</li>
					<li>Histórico <span>(hasta el 31/01/2021)</span>: <a href="https://github.com/Pakillo/COVID19-Andalucia">COVID19-Andalucia</a> por <a href="https://github.com/Pakillo">Pakillo</a>.</li>
				</ul>
			</div>
			<div class="share col-md-4">
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
</script>

</body>

</html>