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
    myDropzone.options.url = (PIG.Session.CurrentAlbum == null ? "../ajax_controller.php?action=upload-images" : "../ajax_controller.php?action=upload-images"+"&album="+PIG.Session.CurrentAlbum.id);
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
    NoAlbumImages: null,
    CurrentAlbum: null,
    AddingToAlbum: null,
    ImagesSelection: []
};


PIG.Action = {};

PIG.Action.Album = {};
PIG.Action.Album.Add = function(){
    PIG.Session.AddingToAlbum = PIG.Session.CurrentAlbum;
    PIG.UIManager.ActionBar();
}

PIG.Action.Image = {};
PIG.Action.Image.Select = function(image){
    PIG.Session.ImagesSelection.push(image);    //TODO avoid duplicate
    PIG.UIManager.ActionBar();
}

PIG.UIManager = {};

PIG.UIManager.ActionBar = function(){
    var msg = $(PIG.Conf.default_zones.message);

    if(PIG.Session.AddingToAlbum != null)
        msg.text("Selected " + (PIG.Session.ImagesSelection.length) + " images to be added to " + PIG.Session.AddingToAlbum["name"]);
    else if(PIG.Session.ImagesSelection.length > 0)
        msg.text("Selected " + (PIG.Session.ImagesSelection.length) + " images");
    else if(PIG.Session.CurrentAlbum != null)
        msg.text("Drag images everywhere to upload into " + PIG.Session.CurrentAlbum["name"]);
    else
        msg.text("Drag images everywhere to upload");

}

PIG.UIManager.Albums = function (){
    //Session update
    PIG.Session.CurrentAlbum = null;

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
    PIG.UIManager.ActionBar();
}

PIG.UIManager.Album = function(albumData){
    //Session update
    PIG.Session.CurrentAlbum = albumData;

    //Breadcrumb
    var breadcrumbs = $(PIG.Conf.default_zones.header).find(".breadcrumb");
    breadcrumbs.hide();
    var albumBC = breadcrumbs.filter("[data-pig-breadcrumb-album]");
    $(albumBC).find("[data-pig-breadcrumb-album-name]").text(albumData["name"]);
    albumBC.show();

    //Action
    var actions = $(PIG.Conf.default_zones.header).find(".btn-group");
    actions.hide();
    var sactions = actions.filter("[data-pig-action-album]")
    sactions.show();

    //Content
    PIG.Populator.Album(albumData["id"]);

    //Bottom bar
    PIG.UIManager.ActionBar();
}

PIG.UIManager.UnassignedImages = function(){
    //Session update
    PIG.Session.CurrentAlbum = null;

    //Breadcrumb
    var breadcrumbs = $(PIG.Conf.default_zones.header).find(".breadcrumb");
    breadcrumbs.hide();
    var albumBC = breadcrumbs.filter("[data-pig-breadcrumb-album]");
    $(albumBC).find("[data-pig-breadcrumb-album-name]").text("Unassigned images");
    albumBC.show();

    //Action
    var actions = $(PIG.Conf.default_zones.header).find(".btn-group");
    actions.hide();

    //Content
    PIG.Populator.UnassignedImages();

    //Bottom bar
    PIG.UIManager.ActionBar();
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
            var count = 0;
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

                count++;
                if(count % 3 == 0)
                    $(container).append("<div class='clearfix visible-xs-block'></div>");
                if(count % 4 == 0)
                    $(container).append("<div class='clearfix visible-sm-block'></div>");
                if(count % 6 == 0)
                    $(container).append("<div class='clearfix visible-md-block visible-lg-block'></div>");
            }
        },
        error: function(jqXHR, status, error){
            //TODO
            alert(status);
        }
    });
};

PIG.Populator.UnassignedImages = function(container){
    if(container === undefined)
        container = $(PIG.Conf.default_zones.main);

    var layout = "<div class='col-xs-4 col-sm-3 col-md-2 col-lg-2 PIGImage' data-pig-image-id='-1' onClick=''>" +
        "<button class='btn btn-default thumbnail' data-pig-album-link>" +
        "<img src='' data-pig-thumb />" +
        "<h4 data-pig-image-name style='display: inline-block' ></h4><br/>" +
        "</button></div>";

    $.ajax(PIG.Conf.ajax_target, {
        data: {action: "getUnassignedImages"},
        success: function(data, status, jqXHR){
            $(container).empty();

            var count = 0;

            for(var key in data){
                var el = $(layout);

                (function() {
                    var clos = data[key];
                    el.on("click", function () {
                        PIG.Action.Image.Select(clos);
                    })
                })()

                $(el).data("pig-image-id", data[key]["id"]);
                $(el).find("[data-pig-image-name]").text(data[key]["name"]);
                $(el).find("[data-pig-thumb]").attr("src", "../thumbnails/" + data[key]["filename"] );


                $(container).append(el);

                count++;
                if(count % 3 == 0)
                    $(container).append("<div class='clearfix visible-xs-block'></div>");
                if(count % 4 == 0)
                    $(container).append("<div class='clearfix visible-sm-block'></div>");
                if(count % 6 == 0)
                    $(container).append("<div class='clearfix visible-md-block visible-lg-block'></div>");
            }
        },
        error: function(jqXHR, status, error){
            //TODO
            alert(status);
        }
    });
}

PIG.Populator.Album = function(albumId, container){
    if(container === undefined)
        container = $(PIG.Conf.default_zones.main);

    var layout = "<div class='col-xs-4 col-sm-3 col-md-2 col-lg-2' data-pig-album-image-id='-1' onClick=''>" +
        "<button class='btn btn-default thumbnail' data-pig-album-link>" +
        "<img src='' data-pig-thumb />" +
        "<h4 data-pig-image-name style='display: inline-block' ></h4><br/>" +
        "<span data-pig-image-description style='display: inline-block; '><span>" +
        "</button></div>";

    //Get data
    $.ajax(PIG.Conf.ajax_target, {
        data: {action: "getAlbumImages", id: albumId},
        success: function(data, status, jqXHR){
            $(container).empty();
            var count = 0;

            for(var key in data){
                var el = $(layout);

                //(function() {
                //    var clos = data[key];
                //    el.on("click", function () {
                //        PIG.UIManager.Album(clos);
                //    })
                //})()

                $(el).data("pig-album-image-id", data[key]["id"]);
                $(el).find("[data-pig-image-name]").text(data[key]["image_name"]);
                $(el).find("[data-pig-image-description]").text(data[key]["image_description"]);
                $(el).find("[data-pig-thumb]").attr("src", "../thumbnails/" + data[key]["filename"] );

                //TODO link

                $(container).append(el);

                count++;
                if(count % 3 == 0)
                    $(container).append("<div class='clearfix visible-xs-block'></div>");
                if(count % 4 == 0)
                    $(container).append("<div class='clearfix visible-sm-block'></div>");
                if(count % 6 == 0)
                    $(container).append("<div class='clearfix visible-md-block visible-lg-block'></div>");
            }
        },
        error: function(jqXHR, status, error){
            //TODO
            alert(status);
        }
    });
}

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

//Start view
PIG.UIManager.Albums();

//Unassigned images
$.ajax(PIG.Conf.ajax_target, {
    data: {action: "getUnassignedImages"},
    success: function(data, status, jqXHR){
        if(data.length > 0) {
            var bt = $('[data-pig-unassigned]');
            bt.show();
            bt.find('.badge').text(data.length);
        }
        PIG.Session.NoAlbumImages = data.length;
    },
    error: function(jqXHR, status, error){
        //TODO
        alert(status);
    }
});
