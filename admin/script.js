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
    myDropzone.UploadingToAlbum = PIG.Session.CurrentAlbum;
    myDropzone.options.url = (PIG.Session.CurrentAlbum == null ? "../ajax_controller.php?action=upload-images" : "../ajax_controller.php?action=upload-images"+"&album="+PIG.Session.CurrentAlbum.id);
    myDropzone.processQueue();
})

myDropzone.on("success", function(file, resp){
    if(myDropzone.UploadingToAlbum == null)
        PIG.Loader.UnassignedImages();
});


var PIG = {};

PIG.Conf = {
    ajax_target: "../ajax_controller.php",
    no_cover: "../no-cover.jpg",
    default_zones: {
        main: "[data-pig-main]",
        header: "[data-pig-header]",
        message: "[data-pig-bottom-message]",
        bottom: "#action-bottom-bar"
    }
};

PIG.Session = {
    NoAlbumImages: null,
    CurrentAlbum: null,
    ImagesSelection: [],
    PendingSelectionAction: null
};


PIG.Action = {};
PIG.Action.UnassignedImage = {}
PIG.Action.Image = {};

PIG.Action.Image.Select = function(image, DomEl){
    if(PIG.Session.PendingSelectionAction)
        return;

    DomEl.toggleClass("selected");

    var id;
    if(image.album != undefined)    //TODO Album_Images doesn't have id
        id = image["id"];  //Album_Image ID
    else
        id = -image["id"]; //UnAassigned, Image ID

    var idx = PIG.Session.ImagesSelection.indexOf(id)
    if(idx != -1)
        PIG.Session.ImagesSelection.splice(idx, 1);
    else
        PIG.Session.ImagesSelection.push(id);

    PIG.UIManager.ActionBar();
}

PIG.Action.Image.ShowDetail = function(image){
    var m = $('#modal-image-detail');
    m.find(".modal-body").empty();
    m.find(".modal-body").append("<img src='../images/"+(image["filename"])+"' /><br/>")

    if(image["album"] != undefined) {
        m.find(".modal-title").text(image["image_name"]);
        m.find(".modal-body").append("<input type='text' data-pig-edit-name value='" + (image["image_name"]) + "' placeholder='Image name' /><br/>")
        m.find(".modal-body").append("<input type='text' data-pig-edit-desc placeholder='Image description' value='" + (image["image_description"] || "") + "' />")

        m.find(".modal-footer").empty()
        m.find(".modal-footer").append(
            "<button type='button' class='btn btn-danger pull-left' data-pig-action-delete>Delete</button>" +
            "<button type='button' class='btn btn-warning pull-left' data-pig-action-remove>Remove from album</button>" +
            "<button type='button' class='btn btn-success' data-pig-action-save>Save</button>" +
            "<button type='button' class='btn btn-primary' data-pig-action-setcover>Set as cover</button>"
        )

        !(function(){
            m.find("[data-pig-action-save]").on("click", function(){
                image["name"] = m.find("[data-pig-edit-name]")[0].value
                image["description"] = m.find("[data-pig-edit-desc]")[0].value
                PIG.Action.Image.UpdateInfo(image, m)
            })
        })()
    }else{
        //UnAssigned Image
        m.find(".modal-title").text(image["name"]);
        m.find(".modal-body").append("<input type='text' data-pig-edit-name value='" + (image["name"]) + "' placeholder='Image name' /><br/>")

        m.find(".modal-footer").empty()
        m.find(".modal-footer").append(
            "<button type='button' class='btn btn-danger pull-left' data-pig-action-delete>Delete</button>" +
            "<button type='button' class='btn btn-success' data-pig-action-save>Save</button>"
        );

        //Dunno if is needed the closure
        !(function(){
            m.find("[data-pig-action-save]").on("click", function(){
                image["name"] = m.find("[data-pig-edit-name]")[0].value
                PIG.Action.UnassignedImage.UpdateInfo(image, m)
            })
            m.find("[data-pig-action-delete]").on("click", function(){
                PIG.Action.UnassignedImage.Delete(image["id"], m)
            })
        })()
    }
    m.modal('show');
}

PIG.Action.Image.UpdateInfo = function(image, modal){
    console.log(image)
    modal.find("[data-pig-action-save]").append(" <span class='glyphicon glyphicon-refresh glyphicon-refresh-animate'></span>")
    $.ajax(PIG.Conf.ajax_target+"?action=updateImageInfo&albumImageId="+image["id"], {
        method: "POST",
        data: {
            info: {"image_name": image["name"], image_description: image["description"]}   //Only this field is supported so far
        },

        success: function(data, status, jqXHR){
            modal.modal("hide")
            //TODO avoid reloading all the images
            PIG.Populator.Album(PIG.Session.CurrentAlbum);
        },

        error: function(jqXHR, status, error){
            console.log(jqXHR);
            PIG.UIManager.Error("ERROR UPLOADING IMAGE INFO FAILED", jqXHR);
        },

        complete: function(){

        }
    })
}

PIG.Action.UnassignedImage.UpdateInfo = function(image, modal){
    modal.find("[data-pig-action-save]").append(" <span class='glyphicon glyphicon-refresh glyphicon-refresh-animate'></span>")
    $.ajax(PIG.Conf.ajax_target+"?action=updateImageInfo&imageId="+image["id"], {
        method: "POST",
        data: {
            info: {"name": image["name"]}   //Only this field is supported so far
        },

        success: function(data, status, jqXHR){
            modal.modal("hide")
            //TODO avoid reloading all the images
            PIG.Loader.UnassignedImages();
            PIG.Populator.UnassignedImages();
        },

        error: function(jqXHR, status, error){
            console.log(jqXHR);
            PIG.UIManager.Error("ERROR UPLOADING IMAGE INFO FAILED", jqXHR);
        },

        complete: function(){

        }
    })
}


PIG.Action.UnassignedImage.Delete = function(id, modal){
    modal.find("[data-pig-action-save]").append(" <span class='glyphicon glyphicon-refresh glyphicon-refresh-animate'></span>")
    $.ajax(PIG.Conf.ajax_target+"?action=deleteImage&imageId="+id, {
        method: "GET",

        success: function(data, status, jqXHR){
            modal.modal("hide")
            //TODO avoid reloading all the images, if no images left write something
            PIG.Loader.UnassignedImages();
            PIG.Populator.UnassignedImages();
        },

        error: function(jqXHR, status, error){
            console.log(jqXHR);
            PIG.UIManager.Error("ERROR UPLOADING IMAGE INFO FAILED", jqXHR);
        },

        complete: function(){

        }
    })
}

PIG.Action.Selection = {}
PIG.Action.Selection.Cancel = function(){
    PIG.Session.ImagesSelection = [];
    PIG.UIManager.ActionBar();
    $('.PIGImage').removeClass("selected");
};

PIG.Action.Selection.Move = function(){
    PIG.Session.PendingSelectionAction = "Moving selection to " + PIG.Session.CurrentAlbum["name"];
    PIG.UIManager.ActionBar();

    var destAlbum = PIG.Session.CurrentAlbum["id"];
    $.ajax(PIG.Conf.ajax_target+"?action=moveImages&destAlbum="+PIG.Session.CurrentAlbum["id"], {
        method: "POST",
        data: {
            selection: PIG.Session.ImagesSelection
        },

        success: function(data, status, jqXHR){
            PIG.Session.ImagesSelection = [];
            PIG.Loader.UnassignedImages();
            if(destAlbum == PIG.Session.CurrentAlbum["id"])
                PIG.Populator.Album(destAlbum);
        },

        error: function(jqXHR, status, error){
            console.log(jqXHR);
            PIG.UIManager.Error(PIG.Session.PendingSelectionAction + " FAILED", jqXHR);

        },

        complete: function(){
            PIG.Session.PendingSelectionAction = null;
            PIG.UIManager.ActionBar()
        }
    })
}


PIG.UIManager = {};

PIG.UIManager.ActionBar = function(){
    var msg = $(PIG.Conf.default_zones.message);

    var selectActions = $(PIG.Conf.default_zones.bottom).find("[data-pig-action-selecting]");
    selectActions.hide();

    if(PIG.Session.PendingSelectionAction){
        msg.empty();
        msg.append("<span class='glyphicon glyphicon-refresh glyphicon-refresh-animate'></span> <span data-pig-pending-msg>"+(PIG.Session.PendingSelectionAction)+"</span>");
    }
    else if(PIG.Session.ImagesSelection.length > 0) {
        msg.text("Selected " + (PIG.Session.ImagesSelection.length) + " images");
        selectActions.show();
        if(!PIG.Session.CurrentAlbum)
            selectActions.find("[data-pig-action-inalbum]").hide();
        else
            selectActions.find("[data-pig-action-inalbum]").show();
    }
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

PIG.UIManager.Error = function(title, body){
    var mod = $('#modal-error');
    mod.find(".modal-title").text(title);
    //mod.find(".modal-body").empty().append(body);
    mod.find(".modal-body").text(body);
    mod.modal("show");
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

    var layout = "<div class='col-xs-4 col-sm-3 col-md-2 col-lg-2 PIGImage' data-pig-image-id='-1'>" +
        "<div class='overlay-actions btn-group thumbnail' data-pig-thumb>" +
        "<button data-pig-action-select title='Add this image to selection'><span class='glyphicon glyphicon-check'></span></button>" +
        "<button data-pig-action-edit title='Edit information'><span class='glyphicon glyphicon-edit'></span></button>" +
        "</div>" +
        "<span data-pig-image-name ></span><br/>" +
        "</div>";

    $(container).empty();

    var count = 0;
    var data = PIG.Session.NoAlbumImages
    for(var key in data){
        var el = $(layout);

        (function() {
            var clos = data[key];
            var elClos = el;
            $(el).find("[data-pig-action-edit]").on("click", function () {
                PIG.Action.Image.ShowDetail(clos);
            });
            $(el).find("[data-pig-action-select]").on("click", function () {
                PIG.Action.Image.Select(clos, elClos);
            })
        })()

        $(el).data("pig-image-id", data[key]["id"]);
        $(el).find("[data-pig-image-name]").text(data[key]["name"]);
        $(el).find("[data-pig-thumb]").css("background-image", "url('../thumbnails/" + data[key]["filename"] +"')" );

        if(PIG.Session.ImagesSelection.indexOf(-data[key]["id"]) != -1)
            el.toggleClass("selected");

        $(container).append(el);

        count++;
        if(count % 3 == 0)
            $(container).append("<div class='clearfix visible-xs-block'></div>");
        if(count % 4 == 0)
            $(container).append("<div class='clearfix visible-sm-block'></div>");
        if(count % 6 == 0)
            $(container).append("<div class='clearfix visible-md-block visible-lg-block'></div>");
    }

}

PIG.Populator.Album = function(albumId, container){
    if(container === undefined)
        container = $(PIG.Conf.default_zones.main);

    var layout = "<div class='col-xs-4 col-sm-3 col-md-2 col-lg-2 PIGImage' data-pig-album-image-id='-1'>" +
            "<div class='overlay-actions btn-group pig_thumb' data-pig-thumb>" +
                "<button data-pig-action-select><span class='glyphicon glyphicon-check'></span></button>" +
                "<button data-pig-action-edit><span class='glyphicon glyphicon-edit'></span></button>" +
            "</div>" +
            "<span data-pig-image-name ></span><br/>" +
            "<span data-pig-image-description '></span>" +
        "</div>";

    //Get data
    $.ajax(PIG.Conf.ajax_target, {
        data: {action: "getAlbumImages", id: albumId},
        success: function(data, status, jqXHR){
            $(container).empty();
            var count = 0;

            for(var key in data){
                var el = $(layout);

                (function() {
                    var clos = data[key];
                    var elClos = el;
                    $(el).find("[data-pig-action-edit]").on("click", function () {
                        PIG.Action.Image.ShowDetail(clos);
                    });
                    $(el).find("[data-pig-action-select]").on("click", function () {
                        PIG.Action.Image.Select(clos, elClos);
                    })
                })()

                $(el).data("pig-album-image-id", data[key]["id"]);
                $(el).find("[data-pig-image-name]").text(data[key]["image_name"]);
                $(el).find("[data-pig-image-description]").text(data[key]["image_description"] || "" );
                $(el).find("[data-pig-thumb]").css("background-image", "url('../thumbnails/" + data[key]["filename"] +"')" );

                if(PIG.Session.ImagesSelection.indexOf(data[key]["id"]) != -1)
                    el.toggleClass("selected");


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


PIG.Loader = {};
PIG.Loader.UnassignedImages = function(){
    $.ajax(PIG.Conf.ajax_target, {
        data: {action: "getUnassignedImages"},
        success: function(data, status, jqXHR){
            if(data.length > 0) {
                var bt = $('[data-pig-unassigned]');
                bt.show();
                bt.find('.badge').text(data.length);
            }else{
                var bt = $('[data-pig-unassigned]');
                bt.hide();
            }
            PIG.Session.NoAlbumImages = data;
        },
        error: function(jqXHR, status, error){
            console.log(jqXHR)
            PIG.UIManager.Error("Failed loading unassigned images", jqXHR);
        }
    });
}


//Clear modal content
$('#modal_album_create').on('show.bs.modal', function (e) {
    var mod = $('#modal_album_create');
    mod.find("[data-output]").text("");
    mod.find("[data-pig-create-name]")[0].value = "";
    mod.find("[data-pig-create-desc]")[0].value = "";
})



//Page init //TODO in pageReady ?

//Start view
PIG.UIManager.Albums();
PIG.Loader.UnassignedImages(); //TODO update when needed
