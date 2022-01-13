$(document).ready(function() {    

    /**
     * DIALOG DE COMPARACIÓN
     */
    var dialog = $("#studentModal").dialog({
        autoOpen: false,
        height: 800,
        width: 950,
        modal: true,
        buttons: {
            Cerrar: function() {
                dialog.dialog("close");
            }
        }
    });

    /**
     * DIALOG DE COMPARACIÓN
     */
    var errorDialog = $("#errorModal").dialog({
        autoOpen: false,
        width: 800,
        height: "auto",
        modal: true,
        position: {
            my: "center center",
            at: "center center",
            of: $('body')
        },
        buttons: {
            Cerrar: function() {
                errorDialog.dialog("close");
            }
        }
    });

    /**
     * Botón de tratar datos manual
     * Hecho para realizar pruebas con los datos ya importados
     * Está comentado en el index.php y se puede descomentar para no hacer nuevas importaciones
     * y solo tratar los datos que hay en bbdd
     */
    $('#processAjax').click(function() {
        let dataProcess = {
            process: 'tratarDatosImport',
            // excelFile: 'Prueba Import.xlsx'
        }
        $.get("./ajax/process.php", dataProcess).done(function(data) {
            console.log(data);
        });
    });

    /**
     * Abre nueva pestaña para añadir altas / bajas de usuario
     */
    $('#gotoAltas').click(function() {
        window.open($(this).attr('data-href'), '_blank');
    });

    /**
     * Método para ver la información del usuario en una nueva página
     */
    $('.searchUser').on('click', function() {
        let searchField = $(this).attr('data-search-field');
        let searchValue = $(this).attr('data-search-value');
        let data = {
            'ajaxRequest': true,
            'searchType': 'user',
            'searchField': searchField,
            'searchValue': searchValue
        }
        $.get('./ajax/students.php', data).done(function(result) {
            if (result.status == 'ko') {
                printError(result.message, errorDialog);
            } else {
                window.open(result.userUrl, '_blank');
            }
        });
    });

    $('#anadirCambiosAltasBajas').click(function() {
        let dataSent = {
            addStudentTo: 'true'
        }

        if ($('#import_usuario').length > 0) dataSent.username = $('#import_usuario').html();
        if ($('#import_email').length > 0) dataSent.email = $('#import_email').html();
        if ($('#import_dni_nif').length > 0) dataSent.dni_nif = $('#import_dni_nif').html();
        if ($('#import_cohorte').length > 0) dataSent.cohort = $('#import_cohorte').html();

        $.post('./ajax/students.php', dataSent).done(function(result) {
            if (result.status == 'ko') {
                printError(result.message, errorDialog);
            } else {
                if (result.updated_record == 'OK') {
                    $('#alumnoCompararAnadirCambios').hide()
                    $('#alumnoCompararCambiosOk').show()
                }
            }
        });
    })

    /**
     * Método para ver la información de cohorte en una nueva página
     */
    $('.searchCohort').on('click', function() {
        let cohort = $(this).attr('data-search');
        let username = $(this).attr('data-username')

        let data = {
            'ajaxRequest': true,
            'searchType': 'cohort',
            'cohort': cohort,
            'username': username
        }
        $.get('./ajax/students.php', data).done(function(result) {
            if (result.status == 'ko') {
                printError(result.message, errorDialog);
            } else {
                window.open(result.cohortUrl, '_blank');
            }
        });
    });

    /**
     * Método para ver la información de alumno actual y el importado
     * Se muestra en una ventana de tipo dialog para ver la comparación
     */
    $('tr[id*="listado-alumnos-"').on('click', function() {
        let searchStudent = $(this).find('.searchStudent').attr('data-search').split('|');
        let username = searchStudent[0];
        let email = searchStudent[1];
        let cohort = searchStudent[2];
        let data = {
            'ajaxRequest': true,
            'searchType': 'all_info',
            'username': username,
            'email': email,
            'cohort': cohort
        }

        // RECUPERACION DE DATOS
        $.get('./ajax/students.php', data).done(function(result) {

            if (result.status != 'ko') {

                // Los datos vienen como un array en el que se divide en un subarray 
                // con "valor" y "error"
                let alumnoActual = result.alumnoActual;
                let alumnoComparar = result.alumnoComparar;
                let alumnoEstadoExport = result.estado_exportar;

                if ($('#alumnoCompararAnadirCambios').length > 0) {
                    if (alumnoEstadoExport != '1') {
                        $('#alumnoCompararAnadirCambios').show()
                        $('#alumnoCompararCambiosOk').hide()
                    } else {
                        $('#alumnoCompararAnadirCambios').hide()
                        $('#alumnoCompararCambiosOk').show()
                    }
                }

                /* console.log('alumnoActual', alumnoActual);
                console.log('alumnoComparar', alumnoComparar); */

                // TABLA DE ALUMNO ACTUAL
                var tablaAlumnoActual = '<table class="table">';
                $.each(alumnoActual, function(i, v) {
                    let classError = '';
                    // Si existe error, añadimos la clase para mostrarlo gráficamente
                    if (v.error != undefined) classError = 'error';
                    tablaAlumnoActual += '<tr><td>' + i + ':</td><td class="' + classError + '">' + v.valor + '</td></tr>';
                });
                tablaAlumnoActual += '</table>';

                // TABLA DE ALUMNO IMPORTADO
                var tablaAlumnoComparar = '<table class="table">';
                $.each(alumnoComparar, function(i, v) {
                    // Si existe error, añadimos la clase para mostrarlo gráficamente
                    let classError = '';
                    if (v.error != undefined) classError = 'ok';

                    let idName = i.toLowerCase().replace('/', '_');
                    tablaAlumnoComparar += '<tr><td>' + i + ':</td><td class="' + classError + '" id="import_' + idName + '">' + v.valor + '</td></tr>';
                });
                tablaAlumnoComparar += '</table>';

                // AÑADIMOS LOS DATOS AL DIALOG
                $('#studentModal').find('#alumnoActual').html(tablaAlumnoActual);
                $('#studentModal').find('#alumnoComparar').html(tablaAlumnoComparar);

                // ABRIMOS EL DIALOG
                dialog.dialog('open');
            } else {
                printError(result.message, errorDialog);
            }
        })
    });
});

function printError(message, errorDialog) {
    // SI HAY UN ERRROR SE MUESTRA CON UN MENSAJE 
    let errorHtml = '<div class="col-12 mt-3 mb-3 alert alert-danger alert-dismissible fade show text-center" role="alert">' +
        '<div class="message">' + message + '</div>' +
        // '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
        // '<span aria-hidden="true">&times;</span>' +
        // '</button>' +
        '</div>';

    // $('#errorModal .addAlert').html(errorHtml);
    $('#errorModal .message').html(errorHtml);
    errorDialog.dialog('open');
}