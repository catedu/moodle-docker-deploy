<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('../libreria/checkAdmin.php');
require_once('../libreria/mpdf7/autoload.php');

	$offset = optional_param('offset', 0, PARAM_INT);
	$batch = optional_param('batch', 0, PARAM_INT);

	// RECOJEMOS LOS CENTROS ENVIADOS POR EL FORMULARIO Y LOS UNIMOS
	// EN UN STRING SEPARADOS POR COMAS PARA GENERAR LUEGO POR JAVASCRIPT
	// EL ARRAY centersID
	$centros = required_param('centros', PARAM_INT);
	$centros = implode(',',$centros);

	$title = "Informe de actividad de los profesores";
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	$PAGE->set_cacheable(false);
	$PAGE->navbar->ignore_active();
	$PAGE->navbar->add('ITAINNOVA Tools', new moodle_url('/itainnova_tool'));
	$PAGE->navbar->add($title, new moodle_url(substr($PAGE->url, 0, strpos($PAGE->url, '?'))));
	$PAGE->set_pagelayout('frametop');
	echo $OUTPUT->header();
?>

	<?php // https://developers.google.com/chart/interactive/docs/quick_start // ?>
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

	<script type="text/javascript">

		// Google Charts
		// Cargamos las librerías que necesitamos
		// corechart :  (bar, column, line, area, stepped area, bubble, pie, donut, combo, candlestick, histogram, scatter)
		// calendar : isnt obvious?
		google.charts.load("current", {
			packages: [
				'corechart',
				'calendar'
			]
		});

		// Llamamos a la función una vez cargadas las librerías
		google.charts.setOnLoadCallback(drawChart);

		function drawChart() {

			// Los IDs de los centros los añadimos en forma de texto
			// EN PHP viene dividido en comas
			// En javascript lo printeamos dentro de corchetes para que se tranforme en array
			let centersID = [<?= $centros ?>];

			// El número de centros lo necesitamos para verificar el estado de creación de zips
			var numCentros = centersID.length;

			$(centersID).each(function(i, id){

				let dataSent = {
					idCentro: id
				}

				var numCentro = parseInt(i)+1;

				/**********************************/
				// Recuperamos los datos del centro
				/**********************************/

				// Nos devolverá los ciclos de ese centro
				// Por cada ciclo tendremos los datos teachersData y coursesData
				// teachersData contiene toda la información de cada profesor
				$.get( "./AJAX/get_centros.php", dataSent)
				.done(function(centersData) {

					if(centersData.error != undefined && centersData.error != null){
						alert(centersData.error);
					}else{
						// Número total de ciclos del centro
						var numCiclos = 0;

						$(centersData).each(function(i, center){
							if(center.teachersData.length > 0) {
								numCiclos++
							} else {
								centersData.splice(i, 1)
							}
						})

						$(centersData).each(function(i, center){

							var images = [];
							var calendars = [];

							// dataAdd es una variable que enviaremos a AJAX/informe_coordinadores.php
							// Añadimos center con todos los datos: centro, cursos y profesor
							var dataAdd = {
								'center': center
							}

							let teachersData = center.teachersData;


							// teachersData contiene toda la información de cada profesor
							// Por cada profesor crearemos sus charts en HTML y los añadiremos al dataAdd
							// para enviarlo por ajax e imprimirlo en el PDF
							$(teachersData).each(function(i, teacher) {

								// Contenedores para crear los charts
								$('#imagesPreview').append('<div id="'+teacher.clicks+'"></div><div id="'+teacher.calendar+'"></div>');

								/************************************************/
								/* CHART TIPO COLUMNAS - corechart */
								/************************************************/

								// https://developers.google.com/chart/interactive/docs/reference

								// Constructor de la tabla de datos
								var dataTableCorechart = new google.visualization.DataTable();

								var profesor = teacher.profesor;

								// Declare columns and formats
								dataTableCorechart.addColumn('string', 'Fecha');
								dataTableCorechart.addColumn('number', 'Clicks');
								dataTableCorechart.addColumn({type:'string', role: 'style' });
								dataTableCorechart.addColumn({type:'number', role: 'annotation' });

								// Add Rows
								dataTableCorechart.addRows(teacher.actividad);

								// Create options
								var options = {
									title: "Actividad por semana",
									height: 300,
									titleTextStyle: {
										fontSize: 36
									},
									legend: 'none',
									chartArea: {
										left: 0,
										top: 50,
										width: '100%',
										height: '70%'
									}
								};

								var chart_div = document.getElementById(teacher.clicks);
								var corechart = new google.visualization.ColumnChart(chart_div);

								// Wait for the chart to finish drawing before calling the getImageURI() method.
								google.visualization.events.addListener(corechart, 'ready', function() {
									//let contenedor = document.getElementById(teacher.clicks);

									let image = {
										index: teacher.clicks,
										image: corechart.getImageURI()
									}

									// Añadimos el chart al array de imagenes del ciclo
									images.push(image)
								});

								corechart.draw(dataTableCorechart, options);

								/************************************************/
								/* CHART TIPO CALENDARIO - calendar */
								/************************************************/

								// https://developers.google.com/chart/interactive/docs/reference

								// Constructor de la tabla de datos
								var dataTableCalendar = new google.visualization.DataTable();

								// Declare columns and formats
								dataTableCalendar.addColumn({ type: 'date', id: 'Date' });
								dataTableCalendar.addColumn({ type: 'number', id: 'count' });

								// Las conexiones por día hay que añadirlo al chart formateados
								// v.date contiene un array con la fecha separada en (Año, mes, día)
								// v.num contiene el número de conexiones
								var conexiones = [];								
								$(teacher.conexiones).each(function(i,v){
									// console.log(i,v);
									let conexion = [];
									conexion[0] = new Date(v.date);
									conexion[1] = v.num;
									conexiones.push(conexion)
								});

								dataTableCalendar.addRows(conexiones);

								var chart_div2 = document.getElementById(teacher.calendar);
								var calendarchart = new google.visualization.Calendar(chart_div2);

								google.visualization.events.addListener(calendarchart, 'ready', function () {
									var contenedor = document.getElementById(teacher.calendar);

									let calendar = {
										index: teacher.calendar,
										calendar: contenedor.innerHTML
									}

									// Añadimos el calendario al array de calendarios del ciclo
									calendars.push(calendar)
								});

								var options = {
									title: teacher.profesor.name,
									height: 500,
									tooltip: {isHtml: false},
									calendar: {
										daysOfWeek: 'DLMXJVS',
										cellColor: {
											stroke: '#F5F5F5',      // Color the border of the squares.
											strokeOpacity: 0.5, // Make the borders half transparent.
											strokeWidth: 2      // ...and two pixels thick.
										}
									},
									colorAxis : {
										minValue: 0,
										maxValue: 10,
										colors: ['#000000', '#01bc07']
									}
								};

								calendarchart.draw(dataTableCalendar, options);
							});
							

							/**
							 * AÑADIMOS los charts a dataAdd por cada Ciclo
							 */
							dataAdd.images = images;
							dataAdd.calendars = calendars;

							/**
							 * ENVIAMOS dataCenterPost como dato único vía AJAX
							 */
							let dataCenterPost = {
								'centers': dataAdd
							}

							// console.log(dataCenterPost);

							// numCiclos es el número total de ciclos del centro
							// indexCenter es el indice del iterador de ciclos
							// Si el número es igual es que hemos terminado de crear los PDF
							var indexCenter = i
							$.post( "./AJAX/informe_coordinadores.php", dataCenterPost)
							.done(function( data ) {

								if(data.error != undefined){
									console.log(data)
								}else{
									var zipArchive = {
										'zipname': data.zipname,
										'archives': data.archive
									}

									// HORA DE CREAR EL ZIP
									// informe_coordinadores.php nos envía los datos necesarios para saber
									// los archivos que tiene que añadir al zip
									if(numCiclos > 0 && (parseInt(indexCenter) + 1) == numCiclos){
										$.post( "./AJAX/informe_coordinadores_zip.php", zipArchive)
										.done(function( data ) {
											if(data != null){
												data = JSON.parse(data);
												let zippedDownloadLink = '<a href="'+data.zipfile+'" download class="btn btn-primary">'+data.zipname+'</a>';
												let zippedDownloadIframe = '<iframe id="'+numCentro+'" style="display:none;"></iframe>';
												$('#zippedFiles .row').append('<div class="col">'+zippedDownloadLink+''+zippedDownloadIframe+'</div>');

												anadeArchivosDescarga(numCentro, data.zipfile);

												// Evaluamos si se han cargado todos los centros
												finCargaArchivos(numCentro, numCentros);
											}
										});
									}
								}
							});
						});
					}
				});
			});
		}

		// FIN DE LA CARGA DE ARCHIVOS
		function finCargaArchivos(numCentro, numCentros){
			// console.log(numCentro);
			if(numCentro == numCentros){
				$('#filesLoaded').show();
				$('#loadingFiles').hide();

				// descargaArchivos();
			}
		}

		var dataDownload = [];
		function anadeArchivosDescarga(idIframe, urlDownload){
			let data = {
				'idIframe': idIframe,
				'urlDownload': urlDownload
			}

			// dataDownload.push(data)

			$('#'+idIframe).attr('src', urlDownload);
		}

		function descargaArchivos(){

			// console.log(dataDownload);
			// console.log(dataDownload.length);
			$(dataDownload).each(function(i,v){

				// console.log(i, v);

				$('#'+v.idIframe).attr('src', v.urlDownload);
				// document.getElementById('my_iframe').src = url;
			})
		}
	</script>
	<style>
		#zippedFiles .row {
			display: flex;
			justify-content: center;
			align-items: center;
		}
		#zippedFiles .row .col{
			margin: 1rem;
			float: left;
		}
	</style>
</head>

<body>
	<center id="loadingFiles">
		<h1 id="title">Generando gr&aacute;ficos...</h1>
		<h5>Por favor, espere</h5>
		<div style="margin:15px auto"><img src="images/loading-files.gif" /></div>
		<div id='imagesPreview'></div>
	</center>

	<center id="filesLoaded" style="display:none">
		<h1 id="title">Archivos generados</h1>
		<p>Haz clic para descargar</p>
		<div id="zippedFiles"><div class="row"></div></div>
	</center>
<?php
	echo $OUTPUT->footer();