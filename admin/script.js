/**
 * Created by Michele on 12/05/15.
 */

//Setup dropzone on the whole the page
var myDropzone = new Dropzone("html",
    {
        clickable: false,
        url: "../ajax_controller.php?action=upload-images",
        accept: function(file, done){
            $('#modal-images-update').modal('show');
            done();
        },
        previewsContainer: $(".dropzone-previews")[0],
        autoProcessQueue: false,
        previewTemplate: "<div class='dz-preview dz-file-preview col-xs-4'>" +
                            "<div class='dz-details'>" +
                                "<div class='dz-filename'><span data-dz-name></span></div>" +
                                "<div class='dz-size' data-dz-size></div>" +
                                "<img data-dz-thumbnail />" +
                            "</div>" +
                            "<div class='dz-progress'><span class='dz-upload' data-dz-uploadprogress></span></div>" +
                            //"<div class='dz-success-mark'><span>✔</span></div>" +
                            //"<div class='dz-error-mark'><span>✘</span></div>" +
                            "<button class='btn btn-default' data-dz-remove><span class='glyphicon glyphicon-remove'></span></button>" +
        "<div class='dz-error-message'><span data-dz-errormessage></span></div>" +
                        "</div>"
    });

$("#modal-images-update .btn-primary").on('click', function (e) {
    myDropzone.processQueue();
})

myDropzone.on("success", function(file, resp){
    //$('body')[0].innerHTML = resp;
});


var PIG = {};

PIG.Conf = {
    ajax_target: "../ajax_controller.php"
};

PIG.Populator = {};
PIG.Populator.Albums = function(container){

    var layout = "<div class='col-xs-12 col-sm-6 col-md-4 col-lg-3' data-pig-album-id="'-1'">" +
        "<a href='' class='thumbnail' data-pig-album-link>" +
        "<img src='' data-pig-thumb />" +
        "<h4 data-pig-album-name ></h4>" +
        "<span data-pig-album-description ><span>" +
        "</a></div>";

    //Get data
    $.ajax(PIG.Conf.ajax_target, {
        data: {action: "getAlbums"},
        success: function(data, status, jqXHR){
            data = [1,2];
            for(var key in data){
                var el = $(layout);

                $(container).append(layout);
            }
        },
        error: function(jqXHR, status, error){
            //TODO
            alert(status);
        }
    });
};