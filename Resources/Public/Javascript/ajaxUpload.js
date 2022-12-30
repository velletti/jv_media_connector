$('#imgUpload').on('change', function() {
    if ( checkUploadFile( this.files[0] ) ) {
        imgUploadFile() ;

    }
});

function checkUploadFile(file) {
    let size = ( file.size / ( 1024 * 1024) ) ;
    if ( size > 16 ) {
        alert( 'Max upload size is 16 MB, file has: ' + size.toFixed(2) + "Mb" );
        return false ;
    } else {
        let allowed = ['image/jpeg', 'image/png', 'image/gif'];
        if (allowed.indexOf(file.type) < 0) {
            alert("Filetype : " + file.type + " not allowed! (only Images) ");
            return false;
        }
    }
    return true ;
}

function imgUploadFile() {
    $.ajax({
        // Your server script to process the upload
        url: $('#imgUploadForm').attr('action'),
        type: 'POST',

        // Form data
        data: new FormData($('#imgUploadForm')[0]),

        // Tell jQuery not to process data or worry about content-type
        // You *must* include these options!
        cache: false,
        contentType: false,
        processData: false,

        beforeSend: function() {
            $('#imgUploadSubmit').attr("disabled" , true ) ;
        } ,
        // Custom XMLHttpRequest
        xhr: function () {
            var myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) {
                // For handling the progress of the upload
                myXhr.upload.addEventListener('progress', function (e) {
                    if (e.lengthComputable) {
                        $('#fileProgress').attr({
                            value: e.loaded,
                            max: e.total,
                        });
                    }
                }, false);
            }
            return myXhr;
        },
        success: function(response ) {
            // nothing. in case of success page should redirect to resize
            $('#imgUploadSubmit').attr("disabled" , false ) ;
            $('#fileProgress').attr("progress" , false ) ;

            if( response.success ) {
                var target = $('#imgUploadForm').attr('action').replace("=new&" , "=resize&tx_jvmediaconnector_connector%5Bext%5D=" + response.ext +"&tx_jvmediaconnector_connector%5Brnd%5D=" + response.rnd +"&" )  ;

                showSpinner();
                window.location.replace( target ) ;
            } else {
                alert( "Sorry, File could not be stored!") ;
                $('#fileProgress').attr({
                    value: 0,
                    max: 0,
                });
            }

        },
        error: function() {
            alert( "Sorry, an undefined error occured!") ;
            $('#fileProgress').attr({
                value: 0,
                max: 0,
            });
        }
    });
}