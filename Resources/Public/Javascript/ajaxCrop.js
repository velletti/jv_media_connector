var jcp;


jQuery(window).on('load' , function(){
    showSpinner();
    Jcrop.load('unCroppedImg-0').then(img => {
        var ratio = 4/3;
        var realWidth = img.naturalWidth;
        var realHeight = img.naturalHeight;
        if (realWidth > 600) {
            maxW = 600 ;
        } else {
            maxW = realWidth ;
        }
        if (realHeight > 450) {
            maxH = 450 ;
        } else {
            maxH = parseInt( realWidth / ratio ) ;
            if ( maxH > 450 ) {
                maxH = 450 ;
                maxH = parseInt( maxW * ratio ) ;
            }
        }
        jcp = Jcrop.attach(img,{multi:false,w:maxW,h:maxH, aspectRatio:ratio, handles: ['sw','nw','ne','se']});

        const [w,h] = Jcrop.Rect.getMax(maxW,maxH,ratio);
        var rect = Jcrop.Rect.fromPoints([0,0],[w,h]);
        var widget = jcp.newWidget(rect);
        jcp.focus();

        // console.log("Original width=" + realWidth + ", " + "Original height=" + realHeight + " MaxW: " + maxW + " MaxH: " + maxH );

        jQuery(window).on('dblclick' , function(){
            showSpinner();
            handleCrop();
        });
        hideSpinner() ;
    });



    var button = jQuery('.upload-ctrl');
    var button = jQuery(button).removeClass("d-none");
    button.click(function() {
        showSpinner();
        handleCrop();
    });

});

function handleCrop() {
    if (!jcp.active) { alert("please select an area of the Image") ; }
    var imageElement = jQuery('#unCroppedImg-0');
    var imageSrc = jQuery(imageElement).attr("src");
    var displayH = jQuery(imageElement).height();
    var displayW = jQuery(imageElement).width();


    var cropData = {
        tx_jvmediaconnector_connector: {
            cropData: {
                x: jcp.active.pos.x,
                y: jcp.active.pos.y,
                x2: jcp.active.pos.x2,
                y2: jcp.active.pos.y2,
                w: jcp.active.pos.w,
                h: jcp.active.pos.h ,
                img: imageSrc,
                dw: displayW ,
                dh: displayH ,
            }
        }
    };

    var data = {
        'no_cache': 1,
        'type': 44900073,
        'tx_jvmediaconnector_connector': {
            controller: 'Media',
            action: 'cropImage'
        }
    };

    // var url = window.location.pathname + '?' + jQuery.param(data);
    var url =  '/de/cropImage.json?' + jQuery.param(data);
    jQuery.post(url, cropData, function (response) {

        if (response.meta.success) {
            jQuery('#unCroppedImg-0').attr('src', response.data.image);
        } else {
            alert(response.meta.message);
        }
        if( jQuery("#goListUrl").length ) {
            if( jQuery("#goListUrl").attr("href") ) {
                window.location.href = jQuery("#goListUrl").attr("href") ;
            }
        }


    }, 'json');
}