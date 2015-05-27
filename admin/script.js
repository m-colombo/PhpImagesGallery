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
                            //"<div class='dz-error-message'><span data-dz-errormessage>aaaa</span></div>" +
                            "<button class='btn btn-default' data-dz-remove><span class='glyphicon glyphicon-remove'></span></button>" +
                        "</div>"
    });

$("#modal-images-update .btn-primary").on('click', function (e) {
    myDropzone.processQueue();
})

myDropzone.on("success", function(file, resp){
    $('body')[0].innerHTML = resp;
});

