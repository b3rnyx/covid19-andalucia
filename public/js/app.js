var app = {
	
	cfg: {
		
		url: '',
		lists: {},
		mainChart: null,
		items: {},
		cookie_name: '',
		
	},
	
	init: function () {
		
		$('#select_days').val(app.cfg.days_default);

		$('section.selector div.select-province button').on('click', app.selector.select);
		$('section.selector select, section.graphs div.days select').on('change', app.selector.select);
		$('section.selector select[name="select_city"]').select2({
			theme: "bootstrap"
		});

		var copylink = new ClipboardJS('footer .share .copy');
		copylink.on('success', function(e) {
			$('footer .share .copy').tooltip({
				placement: 'top',
				trigger: 'manual',
				title: 'Enlace copiado',
			}).tooltip('show');
			setTimeout(function () {
				$('footer .share .copy').tooltip('hide').tooltip('dispose');
			}, 2000);
		});
		copylink.on('error', function(e) {
			$('footer .share .copy').tooltip({
				placement: 'top',
				trigger: 'manual',
				title: '¡Error al copiar enlace!',
			}).tooltip('show');
			setTimeout(function () {
				$('footer .share .copy').tooltip('hide').tooltip('dispose');
			}, 2000);
		});

		$('button.expand').on('click', app.graphs.heightToggle);

		$('i.tooltip-switch').tooltip({
			placement: 'right',
		});

		app.selector.select();
		
	},

	selector: {

		select: function() {

			var selected, value;

			app.aux.pageLock();

			if ($(this).prop('nodeName') == 'BUTTON') {

				selected = 'province';
				value = $(this).attr('data-code');

			} else if (typeof $(this).attr('name') === 'undefined') {
				
				if ($('#select_city').val() != '') {
					selected = 'city';
					value = $('#select_city').val();
				} else if ($('#select_district').val() != '') {
					selected = 'district';
					value = $('#select_district').val();
				} else {
					selected = 'province';
					value = app.selector.getSelectedProvince();
				}

			} else {

				switch ($(this).attr('name')) {
					case 'select_province':
						selected = 'province';
						value = app.selector.getSelectedProvince();
						break;
					case 'select_district':
						selected = 'district';
						value = $('#select_district').val();
						break;
					case 'select_city':
						selected = 'city';
						value = $('#select_city').val();
						break;
				}

			}

			var call = $.ajax({
				url: app.cfg.url_load,
				method: 'POST',
				dataType: 'json',
				accepts: 'json',
				data: {
					selected: selected,
					value: value,
					days: $('#select_days').val(),
				}
			})
			.done(function (data, textStatus, jqXHR) {

				$('section.selector div.select-province button').removeClass('selected');
				if (data.province != '' || data.city == '') {
					$('section.selector div.select-province button[data-code="' + data.province + '"]').addClass('selected');
				}
				$('#select_district').val(data.district).trigger('change.select2');
				$('#select_city').val(data.city).trigger('change.select2');

				$('.select-selected').removeClass('select-selected');
				var m = (data.mode == 'region') ? 'province' : data.mode;
				$('.select-' + m).addClass('select-selected');

				$('.data-switch').hide();
				$('.switch-' + data.mode).show();

				app.info.show(data);
				app.graphs.show(data);

				app.setCookie();

				var url;

				if (selected == 'province' && (typeof value === 'undefined' || value == 'undefined' || value == '')) {
					url = '/';
				} else {
					url = '?' + selected + '=' + value;
				}
				if ($('#select_days').val() != app.cfg.days_default) {
					url += ((url.indexOf('?') == -1) ? '?days=' : '&days=') + $('#select_days').val();
				}

				window.history.pushState('', '', url);

				app.aux.pageUnlock();

			})
			.fail(function (jqXHR, textStatus, errorThrown) {
				
				alert('HORROR ERROR!');
				app.aux.pageUnlock();

			});

		},

		getSelectedProvince: function () {

			if ($('section.selector div.select-province button.selected').length > 0) {
				return $('section.selector div.select-province button.selected').attr('data-code');
			} else {
				return '';
			}

		}

	},

	info: {

		show: function (data) {

			var title = data.name;

			switch (data.mode) {

				case 'region':
					title += ' <span>(Comunidad)</span>';
				break;

				case 'province':
					title += ' <span>(Provincia)</span>';
				break;

				case 'district':
					title += ' <span>(Distrito)</span>';
				break;

				case 'city':
					title += ' <span>(Municipio)</span>';
				break;

			}

			$('section.info').removeClass().addClass('info').addClass(data.mode);

			$('section.info div.title div.name').html(title);
			$('section.info div.title div.population strong').html(data.info.population);
			
			for (var i in data.info) {

				$('section.info div[data-item="' + i + '"] strong').html(data.info[i]);
				$('section.info div[data-item="' + i + '"] div.num span').html(data.icons[i]);

				$('section.info div.num[data-item="' + i + '"] strong').html(data.info[i]);
				$('section.info div.num[data-item="' + i + '"] span').html(data.icons[i]);

			}

			$('section.info div.num[data-item="uci_beds_total_percent"] strong').append('<em>(UCI)</em>')

			$('section.info').show();

		}

	},

	graphs: {

		charts: {},

		show: function (data) {

			var series = [],
				ylabels = [];

			for (var i in app.cfg.items) {

				if (typeof app.cfg.items[i]['graph'] !== 'undefined') {

					if (typeof app.cfg.items[i]['graph']['series'] !== 'undefined') {
						// Tiene series múltiples

						series = [];
						for (var f in app.cfg.items[i]['graph']['series']) {
							series[f] = {
								name: app.cfg.items[i]['graph']['series'][f]['name'],
								data: [],
							}
						}

					} else {
						// Tiene una sola serie

						series = [];
						series[i] = {
							name: app.cfg.items[i]['name'],
							data: [],
						};

					}
					ylabels = [];

					for (var d in data.data) {

						var insert = false;

						if (typeof app.cfg.items[i]['graph'] !== 'undefined') {

							for (var s in series) {

								if (typeof series[s]['data'] === 'undefined') {
									series[s]['data'] = [];
								}
								
								if (data.data[d][s] !== null) {

									series[s]['data'].push(data.data[d][s]);
									insert = true;

								}

							}

						} else {

							if (data.data[d][i] !== null) {

								series[i]['data'].push(data.data[d][i]);
								insert = true;

							}

						}

						if (insert) {
							ylabels.push(data.data[d]['date']);
						}

					}

					var new_series = [];
					for (var s in series) {
						new_series.push(series[s]);
					}
					
					switch (app.cfg.items[i]['graph']['type']) {

						case 'line':
							var options = {
								series: new_series,
								colors: typeof app.cfg.items[i]['graph']['colors'] === 'undefined' ? ['#2E93fA', '#66DA26', '#546E7A', '#E91E63', '#FF9800'] : app.cfg.items[i]['graph']['colors'],
								chart: {
									height: 350,
									parentHeightOffset: 0,
									type: 'line',
									zoom: {
										enabled: false
									},
									animations: {
										enabled: false,
									},
									toolbar: {
										show: true,
										offsetX: -80,
										offsetY: 0,
										tools: {
											download: '<i class="fa fa-cloud-download"></i> <span>Descargar</span>',
											selection: false,
											zoom: false,
											zoomin: false,
											zoomout: false,
											pan: false,
											reset: false,
										}
									},
								},
								dataLabels: {
									enabled: false
								},
								stroke: typeof app.cfg.items[i]['graph']['stroke'] !== 'undefined' ? app.cfg.items[i]['graph']['stroke'] : {
									width: 4,
									curve: 'straight',
								},
								title: {
									text: app.cfg.items[i]['name'],
									align: 'left',
									style: {
										color: '#28a745',
									}
								},
								grid: {
									row: {
										colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
										opacity: 0.5
									},
									padding: {
										top: 0,
										right: 40,
										bottom: 0,
										left: 10
									}
								},
								xaxis: {
									categories: ylabels,
									tickAmount: 18,
								},
								yaxis: {
									min: 0,
									forceNiceScale: true,
									labels: {
										formatter: function (val, index) {
											return val.toLocaleString('de');
										}
									}
								},
							};
						break;

						case 'area':

							var options = {
								series: new_series,
								colors: typeof app.cfg.items[i]['graph']['colors'] === 'undefined' ? ['#2E93fA', '#66DA26', '#546E7A', '#E91E63', '#FF9800'] : app.cfg.items[i]['graph']['colors'],
								chart: {
									height: 350,
									parentHeightOffset: 0,
									type: 'area',
									zoom: {
										enabled: false
									},
									animations: {
										enabled: false,
									},
									toolbar: {
										show: true,
										offsetX: -80,
										offsetY: 0,
										tools: {
											download: '<i class="fa fa-cloud-download"></i> Descargar',
											selection: false,
											zoom: false,
											zoomin: false,
											zoomout: false,
											pan: false,
											reset: false,
										}
									},
								},
								dataLabels: {
									enabled: false
								},
								stroke: typeof app.cfg.items[i]['graph']['stroke'] !== 'undefined' ? app.cfg.items[i]['graph']['stroke'] : {
									width: 2,
									curve: 'straight'
								},
								fill: {
									opacity: 0.1,
									type: 'solid',
								},
								title: {
									text: app.cfg.items[i]['name'],
									align: 'left',
									style: {
										color: '#28a745',
									}
								},
								grid: {
									row: {
										colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
										opacity: 0.5
									},
									padding: {
										top: 0,
										right: 40,
										bottom: 0,
										left: 10
									}
								},
								xaxis: {
									categories: ylabels,
									tickAmount: 18,
								},
								yaxis: {
									min: 0,
									forceNiceScale: true,
									labels: {
										formatter: function (val, index) {
											return val.toLocaleString('de');
										}
									}
								},
							};
						break;

						case 'columns':
							var options = {
								series: new_series,
								colors: typeof app.cfg.items[i]['graph']['colors'] === 'undefined' ? ['#2E93fA', '#66DA26', '#546E7A', '#E91E63', '#FF9800'] : app.cfg.items[i]['graph']['colors'],
								chart: {
									type: 'bar',
									height: 350,
									animations: {
										enabled: false,
									},
									toolbar: {
										show: true,
										offsetX: -80,
										offsetY: 0,
										tools: {
											download: '<i class="fa fa-cloud-download"></i> Descargar',
											selection: false,
											zoom: false,
											zoomin: false,
											zoomout: false,
											pan: false,
											reset: false,
										}
									}
								},
								plotOptions: {
									bar: {
										horizontal: false,
										columnWidth: '55%',
										endingShape: 'rounded'
									},
								},
								dataLabels: {
									enabled: false
								},
								title: {
									text: app.cfg.items[i]['name'],
									align: 'left',
									style: {
										color: '#28a745',
									}
								},
								grid: {
									row: {
										colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
										opacity: 0.5
									},
									padding: {
										top: 0,
										right: 40,
										bottom: 0,
										left: 10
									}
								},
								xaxis: {
									categories: ylabels,
									tickAmount: 18,
								},
								yaxis: {
									min: 0,
									forceNiceScale: true,
									labels: {
										formatter: function (val, index) {
											return val.toLocaleString('de');
										}
									}
								},
								fill: {
									opacity: 1
								},
							};
			
						break;

					}

					// Anotaciones
					if (options.series.length == 1) {

						options.annotations = {
							points: [
								{
									x: null,
									y: null,
									marker: {
										size: 6,
										fillColor: "#fff",
										strokeColor: "#333",
										strokeWidth: 3,
										shape: "circle",
										radius: 2,
									},
									label: {
										borderColor: '#666666',
										borderWidth: 1.5,
										borderRadius: 4,
										text: null,
										textAnchor: 'middle',
										offsetX: 0,
										offsetY: 0,
										style: {
											background: '#fff',
											color: '#777',
											fontSize: '14px',
											fontWeight: 400,
											padding: {
												left: 5,
												right: 5,
												top: 2,
												bottom: 2,
											}
										},
									},
								}
							]
						};

					}

					switch (i) {

						case 'incidence_14d':
							options.annotations.yaxis = [
								{
									y: 0,
									y2: 250,
									borderColor: '#000000',
									fillColor: '#28a745',
									opacity: 0.1,
									label: {
										offsetX: 24,
										offsetY: 6,
										borderColor: '#ffc107',
										style: {
											color: '#888',
											fontSize: '10px',
											background: '#ffc107',
											padding: {
												left: 2,
												right: 2,
												top: 1,
												bottom: 1,
											}
										},
										text: '250'
									}
								}, {
									y: 250,
									y2: 500,
									borderColor: '#000',
									fillColor: '#ffc107',
									opacity: 0.1,
									label: {
										offsetX: 24,
										offsetY: 6,
										borderColor: '#fd7e14',
										style: {
											color: '#fff',
											fontSize: '10px',
											background: '#fd7e14',
											padding: {
												left: 2,
												right: 2,
												top: 1,
												bottom: 1,
											}
										},
										text: '500'
									}
								}, {
									y: 500,
									y2: 1000,
									borderColor: '#000',
									fillColor: '#fd7e14',
									opacity: 0.1,
									label: {
										offsetX: 24,
										offsetY: 6,
										borderColor: '#dc3545',
										style: {
											color: '#fff',
											fontSize: '10px',
											background: '#dc3545',
											padding: {
												left: 2,
												right: 2,
												top: 1,
												bottom: 1,
											}
										},
										text: '1000'
									}
								}, {
									y: 1000,
									y2: 10000,
									fillColor: '#dc3545',
									opacity: 0.1,
								}
							];
						break;

					}

					options.responsive = [
						{
							breakpoint: 1200,
							options: {
								xaxis: {
									tickAmount: 10,
								}
							}
						},{
							breakpoint: 768,
							options: {
								xaxis: {
									tickAmount: 5,
								}
							}
						}
					];

					if (typeof app.cfg.items[i]['legend'] !== 'undefined') {
						options.subtitle = {
							text: app.cfg.items[i]['legend'],
							margin: 0,
							offsetY: 25,
							style: {
								fontSize: '12px',
								color: '#aaaaaa',
							}
						};
					}

					if (app.cfg.items[i]['type'] == 'percent') {
						options.yaxis.labels.formatter = function (val, index) {
							return val.toLocaleString('de', {maximumFractionDigits: 2}) + '%';
						}
					}

					if (typeof options.annotations !== 'undefined') {

						x = ylabels[ylabels.length - 1];
						text = options.series[0]['data'][options.series[0]['data'].length - 1];
						
						if (text !== null && typeof text !== 'undefined') {
							options.annotations.points[0].x = x;
							if (app.cfg.items[i]['type'] == 'percent') {
								options.annotations.points[0].label.text = parseFloat(text).toLocaleString('de', {maximumFractionDigits: 2}) + '%';
							} else {
								options.annotations.points[0].label.text = parseFloat(text).toLocaleString('de', {maximumFractionDigits: 2});
							}
						}

					}

					if (typeof app.graphs.charts[i] !== 'undefined') {
						app.graphs.charts[i].destroy();
					}

					app.graphs.charts[i] = new ApexCharts(document.querySelector('#graph-' + i), options);
					app.graphs.charts[i].render();

				}

			}

		},

		heightToggle: function () {

			var i = $(this).attr('data-item');
			app.graphs.charts[i].updateOptions({
				chart: {
					height: 550,
				}
			});

		}

	},

	setCookie: function () {
			
		var expires = '';
		var date = new Date();
		date.setTime(date.getTime() + (30 * 24 * 60 * 60 * 1000));
		expires = "; expires=" + date.toGMTString();

		var value = app.selector.getSelectedProvince() + '|' + $('#select_district').val() + '|' + $('#select_city').val();
		
		document.cookie = app.cfg.cookie_name + "=" + value + expires + "; path=/";
		
	},
	
	aux: {
		
		numberFormat: function (number, decimals, decPoint, thousandsSep) {
			// https://locutus.io/php/strings/number_format/
			
			number = (number + '').replace(/[^0-9+\-Ee.]/g, '')
			var n = !isFinite(+number) ? 0 : +number
			var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals)
			var sep = (typeof thousandsSep === 'undefined') ? '.' : thousandsSep
			var dec = (typeof decPoint === 'undefined') ? ',' : decPoint
			var s = ''
			
			var toFixedFix = function (n, prec) {
				if (('' + n).indexOf('e') === -1) {
					return +(Math.round(n + 'e+' + prec) + 'e-' + prec)
				} else {
					var arr = ('' + n).split('e')
					var sig = ''
					if (+arr[1] + prec > 0) {
						sig = '+'
					}
					return (+(Math.round(+arr[0] + 'e' + sig + (+arr[1] + prec)) + 'e-' + prec)).toFixed(prec)
				}
			}
			
			// @todo: for IE parseFloat(0.55).toFixed(0) = 0;
			s = (prec ? toFixedFix(n, prec).toString() : '' + Math.round(n)).split('.')
			if (s[0].length > 3) {
				s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep)
			}
			if ((s[1] || '').length < prec) {
				s[1] = s[1] || ''
				s[1] += new Array(prec - s[1].length + 1).join('0')
			}
			
			return s.join(dec)
		},
		
		dateFormat: function (date) {
			
			var t = date.split('-');
			return t[2] + '/' + t[1] + '/' + t[0];
			
		},
		
		pageLock: function () {
			$('.loader').show();
		},
		
		pageUnlock: function () {
			$('.loader').hide();
		},
		
	},
	
};

$(document).ready(function () {
	
	app.init();
	
});