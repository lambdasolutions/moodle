function show_wait() {

    var url_pleasewait = main.params['wwwroot'] + "/local/kaltura/images/Pleasewait.swf";
    var url_checkstatus = main.params['wwwroot'] + "/local/kaltura/kcheck_status.php";

    var param_div = main.params['param_div'];
    var param_field = main.params['param_field'];

    var ksoa = new SWFObject(url_pleasewait, "kwait", "140", "105", "9", "#ffffff");
    var txt_document = "<br/>" + main.params['videoconversion'] + '.<br/><br/><a href="javascript:show_wait()">' + main.params['clickhere'] + '</a> ' + main.params['convcheckdone'];
    var entryId = document.getElementById(param_field).value;

    ksoa.addParam("allowScriptAccess", "always");
    ksoa.addParam("allowFullScreen", "TRUE");
    ksoa.addParam("allowNetworking", "all");
    ksoa.addParam("wmode", "transparent");
    ksoa.addParam("flashVars", "");

    document.getElementById(param_div).style.display = "block";

    if (ksoa.installedVer.major >= 9) {
        ksoa.write(param_div);
    }
    else {
        document.getElementById(param_div).innerHTML = "Flash player version 9 and above is required. <a href=\"http://get.adobe.com/flashplayer/\">Upgrade your flash version</a>";
    }

    YUI().use("io",
    function(Y) {
        Y.io(url_checkstatus, {
            sync: false,
            method: "POST",
            data: "entryid=" + entryId,
            on: {
                success: function(id, o, args) {
                    var data = o.responseText;
                    if (data.substr(0, 2) == "y:") {
                        document.getElementById(param_div).innerHTML = msg.substr(2);
                        do_on_wait();
                    } else {
                        document.getElementById(param_div).innerHTML = txt_document;
                    }
                },
                failure: function(id, o, args) {}
            }
        })
    })
}

function set_entry_type(type) {
    document.getElementById("id_entry_type").value = type;
}

function get_height() {

    var aspecttype_4_3 = main.params['aspecttype_4_3'];
    var sizelarge = main.params['sizelarge'];
    var sizesmall = main.params['sizesmall'];
    var sizecustom = main.params['sizecustom'];

    if (get_field("id_dimensions") == aspecttype_4_3) {
        switch (get_field("id_size")) {
        case sizelarge:
            return 445;
            break;
        case sizesmall:
            return 340;
            break;
        case sizecustom:
            return parseInt(get_field("id_custom_width")) * 3 / 4 + 65 + 80;
            break;
        default:
            return 445;
            break;
        }

    } else {
        switch (get_field("id_size")) {
        case sizelarge:
            return 370;
            break;
        case sizesmall:
            return 291;
            break;
        case sizecustom:
            return parseInt(get_field("id_custom_width")) * 9 / 16 + 65 + 80;
            break;
        default:
            return 370;
            break;
        }

    }

}

function get_width() {
    var sizelarge = main.params['sizelarge'];
    var sizesmall = main.params['sizesmall'];
    var sizecustom = main.params['sizecustom'];

    switch (get_field("id_size")) {
    case sizelarge:
        return 450;
        break;
    case sizesmall:
        return 310;
        break;
    case sizecustom:
        return parseInt(get_field("id_custom_width")) + 50;
        break;
    default:
        return 450;
        break;
    }
}

function onSimpleEditorBackClick(param)
 {
    var thumburl = main.params['thumburl'];

    ts = new Date().getTime();

    try {
        update_img('id_thumb', thumburl + '?t=' + ts, false, '');
    }
    catch(err) {}
    setTimeout("window.parent.kalturaCloseModalBox();", 0);
}

function change_entry_player() {

    var design = document.getElementById("slctDesign");

    show_entry_player(get_page_entry(), design.options[design.selectedIndex].value);
}


function onContributionWizardAfterAddEntry(param) {
    var wwwroot = main.params['wwwroot'];
    var type = main.params['type'];
    var entrymixtype = main.params['entrymixtype'];
    var divprops = main.params['divprops'];
    var divcw = main.params['divcW'];
    var updatefield = main.params['updatefield'];
    var entrymediatype = main.params['entrymediatype'];

    if (type == entrymixtype) {

        var entries = "";
        var name;
        try {
            name = get_field("id_name")
        } catch(ex) {};

        if (divprops != '') {
            document.getElementById(divcw).style.display = "none";
            document.getElementById(divprops).style.display = "block";
        }
        for (i = 0; i < param.length; i++) {
            entryId = (param[i].uniqueID == null ? param[i].entryId: param[i].uniqueID);
            entries += entryId + ",";
        }

        YUI().use("io",
        function(Y) {
            Y.io(wwwroot + "/local/kaltura/kmix.php", {
                method: "POST",
                data: "entries=" + entries + "&name=" + name,
                on: {
                    success: function(id, o, args) {
                        var msg = o.responseText;
                        if (msg.substr(0, 2) == "y:") {
                            entryId = msg.substr(2);
                            set_page_entry(entryId);
                            if (divprops != '') {
                                show_entry_player(entryId, "light");
                                update_field(updatefield, entryId, false, '');
                            } else {
                                setTimeout("window.parent.kalturaCloseModalBox();", 0);
                                update_field(updatefield, entryId, false, 'show_wait');
                            }
                        } else {
                            alert(msg.substr(2));
                        }
                    },
                    failure: function(i, o, a) {}
                }
            });
        });
    } else if (type == entrymediatype) {
        entryId = (param[0].uniqueID == null ? param[0].entryId: param[0].uniqueID);
        if (divprops != '') {
            document.getElementById(divcw).style.display = "none";
            document.getElementById(divprops).style.display = "block";
            set_page_entry(entryId);
            show_entry_player(entryId, "light");
            update_field(updatefield, entryId, false, '');
        } else {
            setTimeout("window.parent.kalturaCloseModalBox();", 0);
            update_field(updatefield, entryId, false, 'show_wait');
        }
    }
}

function onContributionWizardClose(modified) {
    if (modified[0] == 0) {
        setTimeout("window.parent.kalturaCloseModalBox();", 0);
    }
}

function gotoEditorWindow(param1) {
    onPlayerEditClick(param1);
}

function onPlayerEditClick(param1) {
    var wwwroot = main.params['wwwroot'];

    kalturaInitModalBox(wwwroot + '/local/kaltura/keditor.php?entry_id=' + param1, {
        width: 890,
        height: 546
    });
}

