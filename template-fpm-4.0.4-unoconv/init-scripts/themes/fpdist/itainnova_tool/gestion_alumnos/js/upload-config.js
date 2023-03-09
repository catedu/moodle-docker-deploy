$(document).ready(function() {
    /*
     * For the sake keeping the code clean and the examples simple this file
     * contains only the plugin configuration & callbacks.
     * 
     * UI functions ui_* can be located in: demo-ui.js
     */
    $('#drag-and-drop-zone').dmUploader({ //
        url: './ajax/upload.php?ajaxRequest=true',
        maxFileSize: 5000000, // 5 Megs 
        onDragEnter: function() {
            // Happens when dragging something over the DnD area
            this.addClass('active');
        },
        onDragLeave: function() {
            // Happens when dragging something OUT of the DnD area
            this.removeClass('active');
        },
        onInit: function() {
            // Plugin is ready to use
            ui_add_log('Penguin initialized :)', 'info');
        },
        onComplete: function() {
            // All files in the queue are processed (success or error)
            ui_add_log('El proceso se ha efectuado con éxito');
        },
        onNewFile: function(id, file) {
            // When a new file is added using the file selector or the DnD area
            ui_add_log('Nuevo archivo #' + id);
            ui_multi_add_file(id, file);
        },
        onBeforeUpload: function(id) {
            // about tho start uploading a file
            ui_add_log('Iniciando procesado del archivo #' + id);
            ui_multi_update_file_status(id, 'uploading', 'Uploading...');
            ui_multi_update_file_progress(id, 0, '', true);
        },
        onUploadCanceled: function(id) {
            // Happens when a file is directly canceled by the user.
            ui_multi_update_file_status(id, 'warning', 'Cancelado por el usuario');
            ui_multi_update_file_progress(id, 0, 'warning', false);
        },
        onUploadProgress: function(id, percent) {
            // Updating file progress
            ui_multi_update_file_progress(id, percent);
        },
        onUploadSuccess: function(id, data) {

            // A file was successfully uploaded
            ui_add_log('Server Response for file #' + id + ': ' + JSON.stringify(data));
            ui_add_log('Procesado de archivo #' + id + ' COMPLETADO', 'success');
            ui_multi_update_file_status(id, 'success', 'Upload Complete');
            ui_multi_update_file_progress(id, 100, 'success', false);

            // console.log(data);
            // let result = JSON.stringify(data);
            // console.log(result);

            let dataProcess = {
                ajaxRequest: true,
                process: 'tratarDatosImport',
                excelFile: data.path
            }

            $('#uploadFiles').hide();
            $('#loadingFiles').removeClass('d-none');

            $.get("./ajax/process.php", dataProcess).done(function(data) {
                $('#loadingFiles').addClass('d-none');
                $('#filesLoadedOK').removeClass('d-none');
                setTimeout(() => {
                    document.location.href = './index.php?tab=listadosigad';
                }, 1500);
                console.log(data);
            });
        },
        onUploadError: function(id, xhr, status, message) {
            ui_multi_update_file_status(id, 'danger', message);
            ui_multi_update_file_progress(id, 0, 'danger', false);
        },
        onFallbackMode: function() {
            // When the browser doesn't support this plugin :(
            ui_add_log('Plugin cant be used here, running Fallback callback', 'danger');
        },
        onFileSizeError: function(file) {
            ui_add_log('File \'' + file.name + '\' cannot be added: size excess limit', 'danger');
        }
    });
});