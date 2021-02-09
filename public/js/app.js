var app = {
	
	cfg: {
		
		url: '',
		lists: {},
		mainChart: null,
		items: {},
		cookie_name: '',
		
	},
	
	init: function () {
		
		$('#select_dates').val('30');

		$('section.selector select, section.graphs div.dates select').on('change', app.selector.select);
		$('section.selector select').select2({
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

		app.selector.select();
		
	},

	selector: {

		select: function() {

			var selected, value;

			app.aux.pageLock();

			if (typeof $(this).attr('name') === 'undefined') {
				
				if ($('#select_city').val() != '') {
					selected = 'city';
					value = $('#select_city').val();
				} else if ($('#select_district').val() != '') {
					selected = 'district';
					value = $('#select_district').val();
				} else {
					selected = 'province';
					value = $('#select_province').val();
				}

			} else {

				switch ($(this).attr('name')) {
					case 'select_province':
						selected = 'province';
						value = $('#select_province').val();
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
					dates: $('#select_dates').val(),
				}
			})
			.done(function (data, textStatus, jqXHR) {

				$('#select_province').val(data.province).trigger('change.select2');
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

				app.aux.pageUnlock();

			})
			.fail(function (jqXHR, textStatus, errorThrown) {
				
				alert('HORROR ERROR!');
				app.aux.pageUnlock();

			});

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

			$('section.info div.title div.name').html(title);
			$('section.info div.title div.population strong').html(data.info.population);
			
			for (var i in data.info) {

				$('section.info div[data-item="' + i + '"] strong').html(data.info[i]);
				$('section.info div[data-item="' + i + '"] i').removeClass().addClass(data.icons[i]);

			}

			$('section.info').show();

		}

	},

	graphs: {

		charts: {},

		show: function (data) {

			var serie = [], 
				ylabels = [];
			
			for (var i in app.cfg.items) {

				if (typeof app.cfg.items[i]['graph'] !== 'undefined') {

					serie = [];
					ylabels = [];

					for (var d in data.data) {
						serie.push(data.data[d][i]);
						ylabels.push(data.data[d]['date']);
					}
					
					switch (app.cfg.items[i]['graph']['type']) {

						case 'line':
							var options = {
								series: [{
									name: app.cfg.items[i]['name'],
									data: serie
								}],
								chart: {
									height: 350,
									type: 'line',
									zoom: {
										enabled: false
									},
									toolbar: {
										show: true,
										offsetX: -80,
										offsetY: 0,
										tools: {
											download: '<i class="fa fa-cloud-download"></i>',
											selection: false,
											zoom: false,
											zoomin: false,
											zoomout: false,
											pan: false,
											reset: false,
										}
									}
								},
								dataLabels: {
									enabled: false
								},
								stroke: {
									curve: 'straight'
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
									tickAmount: 12,
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
								annotations: {
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
								}
							};
						break;

						case 'columns':
							var options = {
								series: [{
									name: app.cfg.items[i]['name'],
									data: serie
								}],
								chart: {
									type: 'bar',
									height: 350,
									toolbar: {
										show: true,
										offsetX: -80,
										offsetY: 0,
										tools: {
											download: '<i class="fa fa-cloud-download"></i>',
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
									tickAmount: 12,
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
								annotations: {
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
								}
							};
			
						break;

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

					if (typeof app.graphs.charts[i] !== 'undefined') {
						app.graphs.charts[i].destroy();
					}

					x = ylabels[ylabels.length - 1];
					text = serie[serie.length - 1];
					if (text !== null && typeof text !== 'undefined') {
						options.annotations.points[0].x = x;
						options.annotations.points[0].label.text = parseFloat(text).toLocaleString('de', {maximumFractionDigits: 2});
					}

					app.graphs.charts[i] = new ApexCharts(document.querySelector('#graph-' + i), options);
					app.graphs.charts[i].render();

				}

			}

			$('section.graphs').show();

		},

	},

	setCookie: function () {
			
		var expires = '';
		var date = new Date();
		date.setTime(date.getTime() + (30 * 24 * 60 * 60 * 1000));
		expires = "; expires=" + date.toGMTString();

		var value = $('#select_province').val() + '|' + $('#select_district').val() + '|' + $('#select_city').val();
		console.log(app.cfg.cookie_name + "=" + value + expires + "; path=/");
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