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
    ajax_target: "../ajax_controller.php",
    no_cover: "../no-cover.jpg",
    default_zones: {
        main: "[data-pig-main]",
        header: "[data-pig-header]",
        message: "[data-pig-bottom-message]"
    }
};

PIG.Session = {
    NoAlbumImages: 0,
    CurrentAlbum: null,
    AddingToAlbum: null
};

PIG.UIManager = {};
PIG.UIManager.Albums = function (){
    //Breadcrumb
    var breadcrumbs = $(PIG.Conf.default_zones.header).find(".breadcrumb");
    breadcrumbs.hide();
    breadcrumbs.filter("[data-pig-breadcrumb-albums]").show();

    //Action
    var actions = $(PIG.Conf.default_zones.header).find(".btn-group");
    actions.hide();
    actions.filter("[data-pig-action-albums]").show();

    //Content
    PIG.Populator.Albums();

    //Bottom bar
    $(PIG.Conf.default_zones.message).text("Drag images everywhere to upload");
}

PIG.UIManager.Album = function(albumData){
    //Breadcrumb
    var breadcrumbs = $(PIG.Conf.default_zones.header).find(".breadcrumb");
    breadcrumbs.hide();
    var albumBC = breadcrumbs.filter("[data-pig-breadcrumb-album]");
    $(albumBC).find("[data-pig-breadcrumb-album-name]").text(albumData["name"]);
    albumBC.show();

    //Action
    var actions = $(PIG.Conf.default_zones.header).find(".btn-group");
    actions.hide();
    actions.filter("[data-pig-action-album]").show();


    //Bottom bar
    $(PIG.Conf.default_zones.message).text("Drag images everywhere to upload");
}


PIG.Populator = {};


PIG.Populator.Albums = function(container){

    if(container === undefined)
        container = $(PIG.Conf.default_zones.main);

    var layout = "<div class='col-xs-4 col-sm-3 col-md-2 col-lg-2' data-pig-album-id='-1' onClick=''>" +
        "<button class='btn btn-default thumbnail' data-pig-album-link>" +
        "<img src='"+(PIG.Conf.no_cover)+"' data-pig-thumb />" +
        "<h4 data-pig-album-name style='display: inline-block' ></h4><br/>" +
        "<span data-pig-album-description style='display: inline-block; '><span>" +
        "</button></div>";

    //Get data
    $.ajax(PIG.Conf.ajax_target, {
        data: {action: "getAlbums"},
        success: function(data, status, jqXHR){
            $(container).empty();

            for(var key in data){
                var el = $(layout);

                (function() {
                    var clos = data[key];
                    el.on("click", function () {
                        PIG.UIManager.Album(clos);
                    })
                })()

                $(el).data("pig-album-id", data[key]["id"]);
                $(el).find("[data-pig-album-name]").text(data[key]["name"]);
                $(el).find("[data-pig-album-description]").text(data[key]["description"]);
                if(data[key]["cover_filename"] != null )
                    $(el).find("[data-pig-thumb]").attr("src", data[key]["cover_filename"] );

                //TODO link

                $(container).append(el);
            }
        },
        error: function(jqXHR, status, error){
            //TODO
            alert(status);
        }
    });
};

PIG.Creator = {};
PIG.Creator.Album = function(formRoot){

    var name = $(formRoot).find("[data-pig-create-name]")[0].value;
    var desc = $(formRoot).find("[data-pig-create-desc]")[0].value;

    if(name === undefined){
        //TODO handle
    }

    if(desc === undefined){
        desc = "";
    }

    //Lock dialog waiting for server to response.
    var closes = $(formRoot).find("[data-dismiss='modal']");
    closes.attr("disabled", "disabled");
    var submit = $(formRoot).find("[data-submit]");
    submit.attr("disabled", "disabled");

    var output = $(formRoot).find("[data-output]");
    output.text("Creating album..");

    $.ajax(PIG.Conf.ajax_target+"?action=createAlbum", {
        method: "POST",
        data: {
            album: {
                name: name,
                description: desc
            }
        },

        success: function(data, status, jqXHR){
            closes.removeAttr("disabled");
            submit.removeAttr("disabled");
            output.text("Album successfully created");
            PIG.Populator.Albums();
        },

        error: function(jqXHR, status, error){
            console.log(jqXHR);
            //TODO comunicate the error; ie. duplicated album name
            closes.removeAttr("disabled");
            submit.removeAttr("disabled");
            output.text("Something gone wrong");
        }
    })
}




//Clear modal content
$('#modal_album_create').on('show.bs.modal', function (e) {
    $('#modal_album_create').find("[data-output]").text("");
    $('#modal_album_create').find("[data-pig-create-name]")[0].value = "";
    $('#modal_album_create').find("[data-pig-create-desc]")[0].value = "";
})



//Page init //TODO in pageReady ?

PIG.UIManager.Albums();