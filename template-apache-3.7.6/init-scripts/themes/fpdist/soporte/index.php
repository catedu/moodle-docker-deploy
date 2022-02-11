<?php


session_start();

$sessionid = session_id();
?>
<!DOCTYPE html>
    <html  dir="ltr" lang="es" xml:lang="es">
    <head>
        <title>FP a distancia - Aragón</title>
        <link rel="shortcut icon" href="https:/<?php echo $_SERVER['HTTP_HOST'] ?>/pluginfile.php/1/theme_moove/favicon/1622547960/1_FP%20APP%20blanco.ico" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="keywords" content="moodle, FP a distancia - Aragón" />
        <link rel="stylesheet" type="text/css" href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/theme/yui_combo.php?rollup/3.17.2/yui-moodlesimple-min.css" /><script id="firstthemesheet" type="text/css">/** Required in order to fix style inclusion problems in IE with YUI **/</script><link rel="stylesheet" type="text/css" href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/theme/styles.php/moove/1615997395_1/all" />
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
        <link rel="stylesheet" href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/theme/moove/style/aragon/aragon-wrapper.css" type="text/css">
        
        <!-- Matomo -->
        <script type="text/javascript">
        var _paq = window._paq = window._paq || [];
        /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function() {
            var u="https://analytics.catedu.es/";
            _paq.push(['setTrackerUrl', u+'matomo.php']);
            _paq.push(['setSiteId', '1']);
            var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
            g.type='text/javascript'; g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
        })();
        </script>
        <!-- End Matomo Code -->
    </head>
    <body  id="page-site-index" class="format-site course path-site chrome dir-ltr lang-es yui-skin-sam yui3-skin-sam test-adistanciafparagon-es pagelayout-frontpage course-1 context-2 notloggedin ">
        <section  class="ita-sectionguia aragob_header_wrapper">
            <!-- aragob bar  -->
            <div class="aragob_header"></div>
            <!-- fin aragon bar -->
        </section>
        <nav class="navbar nav-inicio">
        </nav>
        <div id="page" class="container-fluid">
            <div id="page-header" class="frontpage-guest-header">
                <div class="d-flex flex-wrap">
                    <div id="page-navbar">
                        <nav>
                            <ol class="breadcrumb"><li class="breadcrumb-item"><a href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/" >Página Principal</a></li>
                                <li class="breadcrumb-item"><a href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/soporte/" >Soporte</a></li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div id="page-content" class="row pb-3 d-print-block">
                    <div id="region-main-box" class="col-12">
                        <section id="region-main">
                            <div class="card">
                                <div class="card-body">
                                    <!--span class="notifications" id="user-notifications"></span-->
                                    <div role="main">
                                        <span id="maincontent"></span>
                                        <form action="accion.php" method="post" id="form_soporte" name="form_soporte" >
                                            <div class="settingsform">
                                                <h2>Soporte</h2>
                                                <p class="box py-3 generalbox alert alert-error alert alert-danger">Asegúrese de introducir su correo electrónico correctamente.</p>
                                                <p>Desde el Equipo de Soporte de la Formación Profesional a Distancia del Gobierno de Aragón queremos darte el mejor servicio posible ante cualquier incidencia que puedas tener.</p>
                                                <p>En el Bloque de <a href="https://www.adistanciafparagon.es/course/view.php?id=2" title="Ayuda" target="_blank" >"AYUDA"</a> de este Portal , encontrarás varios enlaces con información útil que te puede ayudar a resolver las principales dudas o incidencias técnicas que te surjan.</p>
                                                <p>Si no has encontrado la respuesta que buscabas, contacta con nosotros a través de este formulario. Aconsejamos que intentes explicar la situación de la manera más concreta y completa posible y que uses el correo asociado a la plataforma para reportar tu problema. Así podremos identificarte fácilmente e intentaremos resolver tu consulta en la mayor brevedad posible.</p>
                                                <fieldset>
                                                    <div class="clearer"><!-- --></div>
                                                    <!-- -->
                                                    <div class="form-item row">
                                                        <div class="form-label col-sm-3 text-sm-right">
                                                            <label for="rol">Rol <i class="icon fa slicon-exclamation text-danger fa-fw "  title="Obligatorio" aria-label="Obligatorio"></i></label>
                                                        </div>
                                                        <div class="form-setting col-sm-9">
                                                            <div class="form-select defaultsnext">
                                                                <select  id="rol" name="rol" class="custom-select" required>
                                                                    <option value="" selected>Selecciona una opción</option>
                                                                    <option value="e" >Estudiante</option>
                                                                    <option value="p" >Profesorado</option>
                                                                    <option value="c" >Coordinadores/as</option>
                                                                    <option value="o" >Otros</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- -->
                                                    <div class="form-item row">
                                                        <div class="form-label col-sm-3 text-sm-right">
                                                            <label for="nombre_solicitante">Nombre solicitante <i class="icon fa slicon-exclamation text-danger fa-fw "  title="Obligatorio" aria-label="Obligatorio"></i></label>
                                                        </div>
                                                        <div class="form-setting col-sm-9">
                                                            <div class="form-text defaultsnext">
                                                                <input type="text" name="nombre_solicitante" value="" size="30" id="nombre_solicitante" class="form-control text-ltr" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- -->
                                                    <div class="form-item row">
                                                        <div class="form-label col-sm-3 text-sm-right">
                                                            <label for="pape_solicitante">1er apellido solicitante <i class="icon fa slicon-exclamation text-danger fa-fw "  title="Obligatorio" aria-label="Obligatorio"></i></label>
                                                        </div>
                                                        <div class="form-setting col-sm-9">
                                                            <div class="form-text defaultsnext">
                                                                <input type="text" name="pape_solicitante" value="" size="30" id="pape_solicitante" class="form-control text-ltr" required >
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- -->
                                                    <div class="form-item row">
                                                        <div class="form-label col-sm-3 text-sm-right">
                                                            <label for="sape_solicitante">2º apellido solicitante</label>
                                                        </div>
                                                        <div class="form-setting col-sm-9">
                                                            <div class="form-text defaultsnext">
                                                                <input type="text" name="sape_solicitante" value="" size="30" id="sape_solicitante" class="form-control text-ltr" >
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- -->
                                                    <div class="form-item row">
                                                        <div class="form-label col-sm-3 text-sm-right">
                                                            <label for="email_solicitante">E-mail solicitante <i class="icon fa slicon-exclamation text-danger fa-fw "  title="Obligatorio" aria-label="Obligatorio"></i></label>
                                                        </div>
                                                        <div class="form-setting col-sm-9">
                                                            <div class="form-text defaultsnext">
                                                                <input type="email" name="email_solicitante" req value="" size="30" id="email_solicitante" class="form-control text-ltr" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- -->
                                                    <div class="form-item row">
                                                        <div class="form-label col-sm-3 text-sm-right">
                                                            <label for="ciclo">Ciclo <i class="icon fa slicon-exclamation text-danger fa-fw "  title="Obligatorio" aria-label="Obligatorio"></i></label>
                                                        </div>
                                                        <div class="form-setting col-sm-9">
                                                            <div class="form-select defaultsnext">
                                                                <select  id="ciclo" name="ciclo" class="custom-select" required>
                                                                    <option value="">Selecciona el centro</option>
                                                                    <option value="CPIFP Bajo Aragón: Desarrollo de Aplicaciones Multiplataforma">CPIFP Bajo Aragón: Desarrollo de Aplicaciones Multiplataforma</option>

                                                                    <option value="CPIFP Corona de Aragón: Administración y Finanzas">CPIFP Corona de Aragón: Administración y Finanzas</option>
                                                                    <option value="CPIFP Corona de Aragón: Laboratorio de Análisis y de Control de Calidad">CPIFP Corona de Aragón: Laboratorio de Análisis y de Control de Calidad</option>
                                                                    <option value="CPIFP Corona de Aragón: Asistencia a la dirección">CPIFP Corona de Aragón: Asistencia a la dirección</option>

                                                                    <option value="CPIFP Los Enlaces: Comercio Internacional">CPIFP Los Enlaces: Comercio Internacional</option>
                                                                    <option value="CPIFP Los Enlaces: Desarrollo de Aplicaciones">CPIFP Los Enlaces: Desarrollo de Aplicaciones Web</option>
                                                                    <option value="CPIFP Los Enlaces: Sistemas Microinformáticos">CPIFP Los Enlaces: Sistemas Microinformáticos</option>
                                                                    <option value="CPIFP Los Enlaces: Transporte y Logística">CPIFP Los Enlaces: Transporte y Logística</option>
                                                                    <option value="CPIFP Los Enlaces: Gestión de ventas y espacios comerciales">CPIFP Los Enlaces: Gestión de ventas y espacios comerciales</option>
                                                                    <option value="CPIFP Los Enlaces: Producción de audiovisuales y espectáculos">CPIFP Los Enlaces: Producción de audiovisuales y espectáculos</option>

                                                                    <option value="CPIFP Montearagón: Atención a Personas en Situación de Dependencia">CPIFP Montearagón: Atención a Personas en Situación de Dependencia</option>

                                                                    <option value="CPIFP Pirámide: Instalaciones Eléctricas y Automáticas">CPIFP Pirámide: Instalaciones Eléctricas y Automáticas</option>

                                                                    <option value="CPIFP San Blas: Educación y Control Ambiental">CPIFP San Blas: Educación y Control Ambiental</option>

                                                                    <option value="IES Avempace: Educación Infantil">IES Avempace: Educación Infantil</option>

                                                                    <option value="IES Luis Buñuel: Atención a Personas en Situación de Dependencia">IES Luis Buñuel: Atención a Personas en Situación de Dependencia</option>

                                                                    <option value="IES María Moliner: Integración social">IES María Moliner: Integración social</option>

                                                                    <option value="IES Martínez Vargas: Educación Infantil">IES Martínez Vargas: Educación Infantil</option>

                                                                    <option value="IES Miralbueno: Agencias de viajes y gestión de eventos">IES Miralbueno: Agencias de viajes y gestión de eventos</option>

                                                                    <option value="IES Pablo Serrano: Administración de Sistemas Informáticos en Red">IES Pablo Serrano: Administración de Sistemas Informáticos en Red</option>

                                                                    <option value="IES Río Gállego: Farmacia y Parafarmacia">IES Río Gállego: Farmacia y Parafarmacia</option>
                                                                    <option value="IES Río Gállego: Emergencias Sanitarias">IES Río Gállego: Emergencias Sanitarias</option>

                                                                    <option value="IES Santa Emerenciana: Gestión Administrativa">IES Santa Emerenciana: Gestión Administrativa</option>

                                                                    <option value="IES Sierra de Guara: Gestión Administrativa">IES Sierra de Guara: Gestión Administrativa</option>

                                                                    <option value="IES Tiempos Modernos: Gestión Administrativa">IES Tiempos Modernos: Gestión Administrativa</option>

                                                                    <option value="IES Vega del Turia: Emergencias sanitarias">IES Vega del Turia: Emergencias sanitarias</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- -->
                                                    <div class="form-item row">
                                                        <div class="form-label col-sm-3 text-sm-right">
                                                            <label for="motivo">Motivo/Problema <i class="icon fa slicon-exclamation text-danger fa-fw "  title="Obligatorio" aria-label="Obligatorio"></i></label>
                                                        </div>
                                                        <div class="form-setting col-sm-9">
                                                            <div class="form-select defaultsnext">
                                                                <select  id="motivo" name="motivo" class="custom-select" required>
                                                                    <option value="" selected>Selecciona una opción</option>
                                                                    <option value="1" >Plataforma caída</option>
                                                                    <option value="2" >Acceso a plataforma. Problemas con el usuario y contraseña</option>
                                                                    <option value="3" >Acceso a los contenidos o módulos</option>
                                                                    <option value="6" >Cambio / Actualización de Materiales</option>
                                                                    <option id="motivo4" value="4" >Modificación de profesorado</option>
                                                                    <option value="5" >Otros</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- -->
                                                    <div id="capaMateriales">
                                                        <!-- -->
                                                        <div class="form-item row">
                                                            <div class="form-label col-sm-3 text-sm-right">
                                                                <label for="modulo_afectado">
                                                                Módulo profesional afectado
                                                                <i class="icon fa slicon-exclamation text-danger fa-fw "  title="Obligatorio" aria-label="Obligatorio"></i>
                                                                <a class="btn btn-link p-0" role="button"
                                                                    data-container="body" data-toggle="popover"
                                                                    data-placement="right" data-content="&lt;div class=&quot;no-overflow&quot;&gt;&lt;p&gt;Explicación detallada del cambio, actualización o error detectado. Incluye la información de manera que pueda ser fácilmente identificable: URL, número de unidad del módulo, párrafo concreto, etc)&lt;/p&gt;
                                                                &lt;/div&gt; "
                                                                    data-html="true" tabindex="0" data-trigger="focus">
                                                                <i class="icon fa slicon-question text-info fa-fw"  title="Incluye la información de manera que pueda ser fácilmente identificable: URL, número de unidad del módulo, párrafo concreto, etc." aria-label="Ayuda con Explicación detallada del cambio"></i>
                                                                </a>
                                                            </label>
                                                            </div>
                                                            <div class="form-setting col-sm-9">
                                                                <div class="form-text defaultsnext">
                                                                    <input type="text" name="modulo_afectado" value="" size="240" id="modulo_afectado" class="form-control text-ltr" >
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- -->
                                                        <div class="form-item row">
                                                            <div class="form-label col-sm-3 text-sm-right">
                                                                <label for="explicacion_modulo_afectado">
                                                                Explicación detallada del cambio, actualización o error detectado
                                                                <i class="icon fa slicon-exclamation text-danger fa-fw "  title="Obligatorio" aria-label="Obligatorio"></i>
                                                                <a class="btn btn-link p-0" role="button"
                                                                    data-container="body" data-toggle="popover"
                                                                    data-placement="right" data-content="&lt;div class=&quot;no-overflow&quot;&gt;&lt;p&gt;Explicación detallada del cambio, actualización o error detectado. Incluye la información de manera que pueda ser fácilmente identificable: URL, número de unidad del módulo, párrafo concreto, etc)&lt;/p&gt;
                                                                &lt;/div&gt; "
                                                                    data-html="true" tabindex="0" data-trigger="focus">
                                                                <i class="icon fa slicon-question text-info fa-fw "  title="Incluye la información de manera que pueda ser fácilmente identificable: URL, número de unidad del módulo, párrafo concreto, etc." aria-label="Ayuda con Explicación detallada del cambio"></i>
                                                                </a>
                                                            </label>
                                                            </div>
                                                            <div class="form-setting col-sm-9">
                                                                <div class="form-text defaultsnext">
                                                                    <input type="text" name="explicacion_modulo_afectado" value="" size="240" id="explicacion_modulo_afectado" class="form-control text-ltr" >
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- -->
                                                        <div class="form-item row">
                                                            <div class="form-label col-sm-3 text-sm-right">
                                                                <label for="otros_modulo_afectado">
                                                                Otros comentarios
                                                                <a class="btn btn-link p-0" role="button"
                                                                    data-container="body" data-toggle="popover"
                                                                    data-placement="right" data-content="&lt;div class=&quot;no-overflow&quot;&gt;&lt;p&gt;Explicación detallada del cambio, actualización o error detectado. Incluye la información de manera que pueda ser fácilmente identificable: URL, número de unidad del módulo, párrafo concreto, etc)&lt;/p&gt;
                                                                &lt;/div&gt; "
                                                                    data-html="true" tabindex="0" data-trigger="focus">
                                                                <i class="icon fa slicon-question text-info fa-fw "  title="Por ejemplo, puedes comentar cuál crees que sería la solución a ese error, etc." aria-label="Ayuda con Explicación detallada del cambio"></i>
                                                                </a>
                                                            </label>
                                                            </div>
                                                            <div class="form-setting col-sm-9">
                                                                <div class="form-text defaultsnext">
                                                                    <input type="text" name="otros_modulo_afectado" value="" size="240" id="otros_modulo_afectado" class="form-control text-ltr" >
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <!-- -->
                                                    <div id="capaDocente">
                                                        <div class="form-item row">
                                                            <div class="form-label col-sm-3 text-sm-right">
                                                                <label for="cod_coordinacion">Código de coordinación <i class="icon fa slicon-exclamation text-danger fa-fw "  title="Obligatorio" aria-label="Obligatorio"></i></label>
                                                            </div>
                                                            <div class="form-setting col-sm-9">
                                                                <div class="form-text defaultsnext">
                                                                    <input type="password" name="cod_coordinacion" value="" size="30" id="cod_coordinacion" class="form-control text-ltr" >
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- -->
                                                        <div class="form-item row">
                                                            <div class="form-label col-sm-3 text-sm-right">
                                                                <label for="tipo_modificacion">Tipo de modificación <i class="icon fa slicon-exclamation text-danger fa-fw "  title="Obligatorio" aria-label="Obligatorio"></i></label>
                                                            </div>
                                                            <div class="form-setting col-sm-9">
                                                                <div class="form-select defaultsnext">
                                                                    <select  id="tipo_modificacion" name="tipo_modificacion" class="custom-select">
                                                                        <option value="" selected>Selecciona una opción</option>
                                                                        <option value="Cambiar módulos del docente" >Cambiar módulos del docente</option>
                                                                        <option value="Borrar docente" >Borrar docente</option>
                                                                        <option value="Alta docente" >Alta docente</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- -->
                                                        <div class="form-item row">
                                                            <div class="form-label col-sm-3 text-sm-right">
                                                                <label for="nombre_docente">Nombre de docente a modificar <i class="icon fa slicon-exclamation text-danger fa-fw "  title="Obligatorio" aria-label="Obligatorio"></i></label>
                                                            </div>
                                                            <div class="form-setting col-sm-9">
                                                                <div class="form-text defaultsnext">
                                                                    <input type="text" name="nombre_docente" value="" size="30" id="nombre_docente" class="form-control text-ltr" >
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- -->
                                                        <div class="form-item row">
                                                            <div class="form-label col-sm-3 text-sm-right">
                                                                <label for="pape_docente">1er apellido de docente a modificar <i class="icon fa slicon-exclamation text-danger fa-fw "  title="Obligatorio" aria-label="Obligatorio"></i></label>
                                                            </div>
                                                            <div class="form-setting col-sm-9">
                                                                <div class="form-text defaultsnext">
                                                                    <input type="text" name="pape_docente" value="" size="30" id="pape_docente" class="form-control text-ltr" >
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- -->
                                                        <div class="form-item row">
                                                            <div class="form-label col-sm-3 text-sm-right">
                                                                <label for="sape_docente">2º apellido de docente a modificar</label>
                                                            </div>
                                                            <div class="form-setting col-sm-9">
                                                                <div class="form-text defaultsnext">
                                                                    <input type="text" name="sape_docente" value="" size="30" id="sape_docente" class="form-control text-ltr" >
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- -->
                                                        <div class="form-item row">
                                                            <div class="form-label col-sm-3 text-sm-right">
                                                                <label for="dni_docente">DNI/NIE de docente a modificar <i class="icon fa slicon-exclamation text-danger fa-fw "  title="Obligatorio" aria-label="Obligatorio"></i></label>
                                                            </div>
                                                            <div class="form-setting col-sm-9">
                                                                <div class="form-text defaultsnext">
                                                                    <input type="text" name="dni_docente" value="" size="30" id="dni_docente" class="form-control text-ltr" >
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- -->
                                                        <div class="form-item row">
                                                            <div class="form-label col-sm-3 text-sm-right">
                                                                <label for="email_docente">E-mail de docente a modificar <i class="icon fa slicon-exclamation text-danger fa-fw "  title="Obligatorio" aria-label="Obligatorio"></i></label>
                                                            </div>
                                                            <div class="form-setting col-sm-9">
                                                                <div class="form-text defaultsnext">
                                                                    <input type="email" name="email_docente" value="" size="30" id="email_docente" class="form-control text-ltr" >
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- -->
                                                        <div class="form-item row">
                                                            <div class="form-label col-sm-3 text-sm-right">
                                                                <label for="modulo1_docente">Módulo 1 <i class="icon fa slicon-exclamation text-danger fa-fw "  title="Obligatorio" aria-label="Obligatorio"></i></label>
                                                            </div>
                                                            <div class="form-setting col-sm-9">
                                                                <div class="form-text defaultsnext">
                                                                    <input type="text" name="modulo1_docente" value="" size="30" id="modulo1_docente" class="form-control text-ltr" >
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- -->
                                                        <div class="form-item row">
                                                            <div class="form-label col-sm-3 text-sm-right">
                                                                <label for="modulo2_docente">Módulo 2</label>
                                                            </div>
                                                            <div class="form-setting col-sm-9">
                                                                <div class="form-text defaultsnext">
                                                                    <input type="text" name="modulo2_docente" value="" size="30" id="modulo2_docente" class="form-control text-ltr" >
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- -->
                                                        <div class="form-item row">
                                                            <div class="form-label col-sm-3 text-sm-right">
                                                                <label for="modulo3_docente">Módulo 3</label>
                                                            </div>
                                                            <div class="form-setting col-sm-9">
                                                                <div class="form-text defaultsnext">
                                                                    <input type="text" name="modulo3_docente" value="" size="30" id="modulo3_docente" class="form-control text-ltr" >
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div> <!-- capaDocente -->
                                                    <!-- -->
                                                    <div class="form-item row">
                                                        <div class="form-label col-sm-3 text-sm-right">
                                                            <label for="adjunto">Adjunte una imagen si lo desea</label>
                                                        </div>
                                                        <div class="form-setting col-sm-9">
                                                            <input type="file" id="adjunto" name="adjunto">
                                                        </div>

                                                    </div>
                                                    <div class="form-item row">
                                                        <div class="form-label col-sm-3 text-sm-right">
                                                            <label for="otros">Explicación de la situación <i class="icon fa slicon-exclamation text-danger fa-fw" title="Obligatorio" aria-label="Obligatorio"></i></label>
                                                        </div>
                                                        <div class="form-setting col-sm-9">
                                                            <div class="form-textarea">
                                                                <textarea required rows="8" cols="60" id="otros" name="otros" spellcheck="true" class="form-control text-ltr"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- -->
                                                    <!-- -->
                                                    <!-- -->
                                                </fieldset>
                                                <div class="row">
                                                    <div class="offset-sm-3 col-sm-3">
                                                        <img src="captcha.php" alt="CAPTCHA" class="captcha-image">
                                                        <p>¿No puedes leer la imagen? <a href='javascript: refreshCaptcha();'>click aquí</a> para refrescar</p>
                                                    </div>
                                                </div>
                                                <div class="form-item row">
                                                    <div class="form-label col-sm-3 text-sm-right">
                                                        <label for="captcha">Captcha <i class="icon fa slicon-exclamation text-danger fa-fw "  title="Obligatorio" aria-label="Obligatorio"></i></label>
                                                    </div>
                                                    <div class="form-setting col-sm-9">
                                                        <div class="form-text defaultsnext">
                                                            <input type="text" name="captcha_challenge" value=""  pattern="[A-Z]{6}" id="captcha_challenge" class="form-control text-ltr" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="offset-sm-3 col-sm-3">
                                                        <button type="submit" class="btn btn-primary">Enviar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div><!-- end of .card-body -->
                            </div> <!-- card -->
                        </section>
                    </div>
                </div>
            </div>
        </div>
        <script>
            // Funciones
            function resetForm(){
                capaDocente.style.display = 'none';
                document.getElementById("tipo_modificacion").required = false;
                document.getElementById("nombre_docente").required = false;
                document.getElementById("pape_docente").required = false;
                document.getElementById("dni_docente").required = false;
                document.getElementById("email_docente").required = false;
                document.getElementById("modulo1_docente").required = false;
                capaMateriales.style.display = 'none';
                document.getElementById("modulo_afectado").required = false;
                document.getElementById("explicacion_modulo_afectado").required = false;
                document.getElementById("motivo4").style.display = 'none';
            }

            function repaint(rol, motivo){
                resetForm();

                if(motivo == "6"){
                    capaMateriales.style.display = 'block';
                    document.getElementById("modulo_afectado").required = true;
                    document.getElementById("explicacion_modulo_afectado").required = true;

                }else if(rol == "c" && motivo == "4"){
                    capaDocente.style.display = 'block';
                    document.getElementById("tipo_modificacion").required = true;
                    document.getElementById("nombre_docente").required = true;
                    document.getElementById("pape_docente").required = true;
                    document.getElementById("dni_docente").required = true;
                    document.getElementById("email_docente").required = true;
                    document.getElementById("modulo1_docente").required = true;
                }

                if(rol == "c"){
                    document.getElementById("motivo4").style.display = 'block';
                }
            }
            //eventos            
            var comprobacionesPreEnvio = function(event) {
                event.preventDefault();
                var enviar = true;
                var errores = "";
                
                rol = document.getElementById('rol').value;
                motivo = document.getElementById('motivo').value;
                if(rol == "c" && motivo == "4"){
                    //nombre_docente
                    var nombreDocente = document.getElementById("nombre_docente").value;
                    if(nombreDocente == ""){
                        enviar = false;
                        errores = errores + "- El nombre del docente a modificar es obligatorio\n";
                    }
                    //pape_docente
                    var papeDocente = document.getElementById("pape_docente").value;
                    if(papeDocente == ""){
                        enviar = false;
                        errores = errores + "- El 1er apellido del docente a modificar es obligatorio\n";
                    }
                    //dni_docente
                    var dniDocente = document.getElementById("dni_docente").value;
                    if(dniDocente == ""){
                        enviar = false;
                        errores = errores + "- El DNI/NIE del docente a modificar es obligatorio\n";
                    }
                    //email_docente
                    var emailDocente = document.getElementById("email_docente").value;
                    if(emailDocente == ""){
                        enviar = false;
                        errores = errores + "- El email del docente a modificar es obligatorio\n";
                    }
                    //modulo1_docente
                    var modulo1Docente = document.getElementById("modulo1_docente").value;
                    if(modulo1Docente == ""){
                        enviar = false;
                        errores = errores + "- Debe indicar al menos 1 módulo a asignar al nuevo docente\n";
                    }
                }                

                if( ! errores ){
                    miFormulario.submit();
                }else{
                    alert( errores )
                }

            };

            document.getElementById('rol').onchange = function(){
                rol = this.value;
                motivo = document.getElementById('motivo').value;
                
                repaint(rol, motivo);
            }
            
            document.getElementById('motivo').onchange = function(){
                motivo = this.value;
                rol = document.getElementById('rol').value;

                repaint(rol, motivo);
            }

           //
            var capaDocente = document.getElementById('capaDocente');
            var capaMateriales = document.getElementById('capaMateriales');
            var miFormulario = document.getElementById("form_soporte");
            miFormulario.addEventListener("submit", comprobacionesPreEnvio, true);

            repaint();
            
            //Refresh Captcha
            function refreshCaptcha(){
                document.querySelector(".captcha-image").src = 'captcha.php?' + Date.now();
            }
            // Fichero adjunto
            document.getElementById("adjunto").onchange = function(){

                var myFile = document.getElementById("adjunto");
                var files = myFile.files;
                var formData = new FormData();
                var file = files[0]; 
                // Check the file type
                if (!file.type.match('image.*')) {
                    alert('The file selected is not an image.');
                    return;
                }
                //
                formData.append('fileAjax', file, file.name);

                // Set up the request
                var xhr = new XMLHttpRequest();

                // Open the connection
                xhr.open('POST', 'https://test.adistanciafparagon.es/soporte/upload.php', true);

                // Set up a handler for when the task for the request is complete
                xhr.onload = function () {
                    if (xhr.status == 200) {
                        //statusP.innerHTML = 'Upload copmlete!';
                        console.log("respuesta: " + xhr.responseText);
                    } else {
                        //statusP.innerHTML = 'Upload error. Try again.';
                        console.log("Error: " + xhr.responseText);
                    }
                };

                // Send the data.
                xhr.send(formData);

            }
            

        </script>
    </body>
</html>
