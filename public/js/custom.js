var basePath = "http://localhost/sim/";

$(".nav a").on("click", function () {
    $(".nav").find(".active").removeClass("active");
    $(this).parent().addClass("active");
});


function add_server() {
    if ($("#server_domain").val() == "") {
        alert("Please provide server domain.");
        return false;
    }

    if ($("#server_ip").val() == "") {
        alert("Please provide server ip.");
        return false;
    }

    if ($("#server_id").val() == "") {
        alert("Please provide server id.");
        return false;
    }

    if ($("#private_key").val() == "") {
        alert("Please provide private key.");
        return false;
    }

    $.post(basePath + "?route=create-server/add",
        {
            "server_domain": $("#server_domain").val(),
            "server_ip": $("#server_ip").val(),
            "server_id": $("#server_id").val(),
            "private_key": $("#private_key").val()
        }, function (data) {
            alert(data.msg);
            if (1 == data.flag) {
                update_server_form();
                server_list(data.id);
            }
        }, 'json');
}

function update_server_form() {
    $("#server_domain").val("");
    $("#server_ip").val("");

    $.post(basePath + "?route=create-server/unique", {}, function (data) {
        $("#server_id").val(data.uniqueID);
    }, 'json');

    $.post(basePath + "?route=create-server/key", {}, function (data) {
        $("#private_key").val(data.key);
    }, 'json');
}

function server_list(id) {
    $.post(basePath + "?route=create-server/list", {id: id}, function (data) {
        $("#list").html(data);
    });
}

function _delete(url, id) {

    if (confirm("This action will delete data releted id " + id + " from database. Are you sure?")) {
        $.post(basePath + url, {id: id}, function (data) {

            alert(data.msg);

            server_list();

        }, 'json');
    }
}

//create user
function add_user() {
    if ($("#user_id").val() == "") {
        alert("Please provide user  id. It must be unique but not secret.");
        return false;
    }

    if ($("#password").val() == "") {
        alert("Please provide password. It must be secret but not unique.");
        return false;
    }

    if ($("#recovery_contact").val() == "") {
        alert("Please provide recovery contact.");
        return false;
    }

    if ($("#biometric_key").val() == "") {
        alert("Please provide biometric key.");
        return false;
    }

    $.post(basePath + "?route=create-user/add",
        {
            "user_id": $("#user_id").val(),
            "password": $("#password").val(),
            "recovery_contact": $("#recovery_contact").val(),
            "biometric_key": $("#biometric_key").val()
        }, function (data) {
            alert(data.msg);
            if (1 == data.flag) {
                update_user_form();
                user_list(data.id);
            }
        }, 'json');
}

function update_user_form() {
    $("#user_id").val("");
    $("#password").val("");
    $("#recovery_contact").val("");

    $.post(basePath + "?route=create-user/bio-key", {}, function (data) {
        $("#biometric_key").val(data.bioKey);
    }, 'json');
}

function user_list(id) {
    $.post(basePath + "?route=create-user/list", {id: id}, function (data) {
        $("#list").html(data);
    });
}

function _delete_user(url, id) {

    if (confirm("This action will delete data releted id " + id + " from database. Are you sure?")) {
        $.post(basePath + url, {id: id}, function (data) {

            alert(data.msg);

            user_list();

        }, 'json');
    }
}

$(document).ready(function () {
    if ($("#server_id").length > 0) {
        $("#server_id").focus();
    }
});

function console_write(sel, msg) {
    var id = "";

    switch (sel) {
        case "s":
            id = "server-console";
            break;
        case "r":
            id = "rc-console";
            break;
        case "u":
            id = "user-console";
            break;
        case "a":
            id = "attacker-console";
            break;
    }

    msg = $("#" + id).html() + msg + "<br/>";

    $("#" + id).html(msg);
}

function server_registration_form() {

    $("#srf_server_id").val($("#server_id").val());

    $("#modal_server_form").modal();
}


function save_server_info() {

    if ($("#urf_server_id").val() == "0") {
        alert("Please select a server  id.");
        return false;
    }

    $("#server_id").val($("#srf_server_id").val());

    $("#modal_server_form").modal("hide");

}


function registration_request_by_si() {
    //check server id
    if ($("#server_id").val() == 0) {
        alert("Please select a server id. It must be unique but not secret.");
        $("#server_id").focus();
        return false;
    }

    var server_id = $("#server_id").val();

    $("#server-status").html("{SIDi,Register}");
    $("#process-status").html("Sending Registration Request");
    $("#rc-status").html("");

    console_write("s", "Registration Request by Si: {SIDi,Register}");
    console_write("s", "----------------------");

    $(".fa-youtube-play").removeClass("show").addClass("hidden");
    $(".fa-arrow-right").removeClass("hidden").addClass("show");

    $.post(basePath + "?route=server-registration-rc/receive-request", {
        sid: server_id,
        stat: "Register"
    }, function (data) {

        $("#rc-status").html("{SIDi,Register}");
        $("#server-status").html("");

        console_write("r", "Request Received by R: {SIDi, Register}");

        if (2 == data.flag) {
            $("#process-status").html(data.msg);

            console_write("r", data.msg);

            $(".fa-arrow-right").removeClass("show").addClass("hidden");
            $(".fa-youtube-play").removeClass("hidden").addClass("show");
        } else if (1 == data.flag) {
            $("#process-status").html("Secret Generation");
            $("#rc-status").html("Generating Ks");

            console_write("r", "Secret Generation by R: Ks = h(SIDi||Rn1)");

            server_registration_secret_generation_and_reply(server_id);
        }

    }, 'json');

}

function server_registration_secret_generation_and_reply(server_id) {
    $.post(basePath + "?route=server-registration-rc/secret-generation-and-reply", {sid: server_id}, function (resp) {

        if (2 == resp.flag) {
            $("#process-status").html(resp.msg);

            console_write("r", resp.msg);

            $(".fa-arrow-right").removeClass("show").addClass("hidden");
            $(".fa-youtube-play").removeClass("hidden").addClass("show");
        } else if (1 == resp.flag) {
            $("#process-status").html("Reply by RC");
            $("#rc-status").html("{SIDi,Ks,Accept}");

            console_write("r", "Reply by R: {{SIDi,Ks,Accept}}");
            console_write("r", "----------------------");

            $(".fa-arrow-right").addClass("fa-arrow-left").removeClass("fa-arrow-right");

            server_registration_receive_reply(server_id, resp.sid, resp.Ks, resp.stat);
        }

    }, 'json');
}

function server_registration_receive_reply(server_id, rsid, Ks, stat) {
    $.post(basePath + "?route=register-server/receive-reply", {
        sid: server_id,
        rsid: rsid,
        Ks: Ks,
        stat: stat
    }, function (rep) {

        $("#server-status").html("{SIDi,Ks,Accept}");
        $("#rc-status").html("");

        console_write("s", "Reply Received by Si: {SIDi,Ks,Accept}");

        if (2 == rep.flag) {
            $("#process-status").html(rep.msg);

            console_write("s", rep.msg);

            $(".fa-arrow-left").removeClass("show").addClass("hidden");
            $(".fa-youtube-play").removeClass("hidden").addClass("show");
        } else if (1 == rep.flag) {
            $("#process-status").html("Storing Secret");
            $("#server-status").html("Encrypting and storing Ks");

            console_write("s", "Encrypting and Storing Secret by Si: EKs = Eaes(Ks,SKaes)");

            server_registration_secret_storage_and_ack(server_id, rep.Ks);
        }

    }, 'json');
}

function server_registration_secret_storage_and_ack(server_id, Ks) {
    $.post(basePath + "?route=register-server/secret-storage-and-ack", {sid: server_id, Ks: Ks}, function (ack) {

        if (2 == ack.flag) {
            $("#process-status").html(ack.msg);

            console_write("s", ack.msg);

            $(".fa-arrow-left").removeClass("show").addClass("hidden");
            $(".fa-youtube-play").removeClass("hidden").addClass("show");

            server_registration_server_revert(server_id);
        } else if (1 == ack.flag) {
            $("#process-status").html("Acknowledgement by Server");
            $("#server-status").html("{SIDi,Ack}");

            console_write("s", "Acknowledgement by Si: {SIDi,Ack}");
            console_write("s", "----------------------");

            $(".fa-arrow-left").addClass("fa-arrow-right").removeClass("fa-arrow-left");

            server_registration_receive_ack(server_id, ack.sid, ack.stat, Ks);
        }

    }, 'json');
}

function server_registration_receive_ack(server_id, asid, stat, Ks) {
    $.post(basePath + "?route=server-registration-rc/receive-ack", {
        sid: server_id,
        asid: asid,
        stat: stat
    }, function (rack) {

        $("#rc-status").html("{SIDi,Ack}");
        $("#server-status").html("");

        console_write("r", "Acknowledgement Received by R: {SIDi,Ack}");

        if (2 == rack.flag) {
            $("#process-status").html(rack.msg);

            console_write("r", rack.msg);

            $(".fa-arrow-right").removeClass("show").addClass("hidden");
            $(".fa-youtube-play").removeClass("hidden").addClass("show");

            server_registration_server_revert(server_id);
        } else if (1 == rack.flag) {
            $("#process-status").html("Finalizing");
            $("#rc-status").html("Encrypting and storing Ks");

            console_write("r", "Encrypting and Storing Secret by R: HKs = Eaes(Ks,RKaes)");

            server_registration_finalization(server_id, Ks);
        }

    }, 'json');
}

function server_registration_finalization(server_id, Ks) {
    $.post(basePath + "?route=server-registration-rc/finalize", {sid: server_id, Ks: Ks}, function (fin) {

        if (2 == fin.flag) {
            $("#process-status").html(fin.msg);
            console_write("r", fin.msg);
            $(".fa-arrow-right").removeClass("show").addClass("hidden");
            $(".fa-youtube-play").removeClass("hidden").addClass("show");

            server_registration_server_revert(server_id);

        } else if (1 == fin.flag) {
            $("#process-status").html("Registration Completed");
            $("#rc-status").html("");

            console_write("r", "Registration Completed");
        }

    }, 'json');
}


function server_registration_server_revert(sid) {
    $.post(basePath + "?route=register-server/revert", {sid: sid}, function (sr) {
        console_write("s", sr.msg);
    }, 'json');
}

function user_registration_form() {

    $("#urf_user_id").val($("#user_id").val());
    $("#urf_password").val($("#password").val());
    $("#urf_recovery_contact").val($("#recovery_contact").val());
    $("#urf_biometric_key").val($("#biometric_key").val());
    $("#urf_server_id").val($("#server_id").val());

    $("#modal_user_form").modal();
}

function get_user_info(user_id) {
    $.post(basePath + "?route=user-registration/get-info", {id: user_id}, function (data) {
        $("#urf_password").val(data.password);
        $("#urf_recovery_contact").val(data.recovery_contact);
        $("#urf_biometric_key").val(data.biometric_key);
    }, 'json');
}

function save_user_info() {

    if ($("#urf_user_id").val() == "0") {
        alert("Please select an user  id.");
        return false;
    }

    if ($("#urf_password").val() == "") {
        alert("Please provide password. Password field can't be empty.");
        return false;
    }

    if ($("#urf_recovery_contact").val() == "") {
        alert("Please provide recovery contact. Recovery contact can't be empty.");
        return false;
    }

    if ($("#urf_biometric_key").val() == "") {
        alert("Please provide biometric key. Biometric key can't be empty.");
        return false;
    }

    if ($("#urf_server_id").val() == "0") {
        alert("Please select a server  id.");
        return false;
    }

    $("#user_id").val($("#urf_user_id").val());
    $("#password").val($("#urf_password").val());
    $("#recovery_contact").val($("#urf_recovery_contact").val());
    $("#biometric_key").val($("#urf_biometric_key").val());
    $("#server_id").val($("#urf_server_id").val());

    $("#modal_user_form").modal("hide");

}

function registration_request_by_ci() {
    if ($("#user_id").val() == "") {
        alert("Please select an user  id.");
        return false;
    }

    if ($("#password").val() == "") {
        alert("Please provide password. Password field can't be empty.");
        return false;
    }

    if ($("#recovery_contact").val() == "") {
        alert("Please provide recovery contact. Recovery contact can't be empty.");
        return false;
    }

    if ($("#biometric_key").val() == "") {
        alert("Please provide biometric key. Biometric key can't be empty.");
        return false;
    }

    if ($("#server_id").val() == "0") {
        alert("Please select a server  id.");
        return false;
    }

    var user_id = $("#user_id").val();
    var password = $("#password").val();
    var recovery_contact = $("#recovery_contact").val();
    var biometric_key = $("#biometric_key").val();
    var server_id = $("#server_id").val();

    $.post(basePath + "?route=user-registration/get-bpi", {pwi: password, bi: biometric_key}, function (bpi) {

        $("#user-status").html("{IDi,BPi,SIDi,Rcont,Register}");
        $("#ur-process-status").html("Sending Registration Request");
        $("#rc-status").html("");

        console_write("u", "Registration Request by Ci: {IDi,BPi,SIDi,Rcont,Register}");
        console_write("u", "----------------------");

        $(".fa-youtube-play").removeClass("show").addClass("hidden");
        $(".arrow-ur").removeClass("hidden").addClass("show");

        user_registration_request_received_by_r(user_id, bpi.bpi, server_id, recovery_contact);

    }, 'json');
}

function user_registration_request_received_by_r(id, bpi, sid, rcont, W) {
    $.post(basePath + "?route=user-registration-rc/receive-request", {
        id: id,
        bpi: bpi,
        sid: sid,
        rcont: rcont,
        stat: "Register"
    }, function (rr) {

        $("#user-status").html("");
        $("#ur-process-status").html("Registration Request received");
        $("#rc-status").html("{IDi,BPi,SIDi,Rcont,Register}");

        console_write("r", "Registration Request Received by R: {IDi,BPi,SIDi,Rcont,Register}");

        if (2 == rr.flag) {
            $("#ur-process-status").html(rr.msg);
            $("#rc-status").html("");

            console_write("r", rr.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-ur").removeClass("show").addClass("hidden");
            $(".arrow-rs").removeClass("show").addClass("hidden");

        } else if (1 == rr.flag) {

            $("#ur-process-status").html("");
            $("#rs-process-status").html("Sending Registration Request");
            $("#rc-status").html("{IDi,SIDi,TXs,Register}");
            $("#server-status").html("");

            console_write("r", "Registration Request by R: {IDi,SIDi,TXs,Register}");
            console_write("r", "----------------------");

            $(".arrow-rs").removeClass("hidden").addClass("show");

            user_registration_request_by_r(rr.id, rr.sid, rr.txs, rr.rcont, bpi, rr.W);
        }

    }, 'json');
}


function user_registration_request_by_r(id, sid, txs, rcont, bpi, W) {

    $.post(basePath + "?route=user-registration-server/receive-request", {
        id: id,
        sid: sid,
        txs: txs,
        stat: "Register"
    }, function (rr) {
        $("#rs-process-status").html("Registration Request Received");
        $("#rc-status").html("");
        $("#server-status").html("{IDi,SIDi,TXs,Register}");

        console_write("s", "Registration Request Received by Si: {IDi,SIDi,TXs,Register}");

        if (2 == rr.flag) {

            $("#rs-process-status").html(rr.msg);
            $("#server-status").html("");

            console_write("s", rr.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-ur").removeClass("show").addClass("hidden");
            $(".arrow-rs").removeClass("show").addClass("hidden");

        } else if (1 == rr.flag) {

            $("#rs-process-status").html("Generating Secret");
            $("#server-status").html("Xs = h(Ks||TXs)");

            console_write("s", "Secret Generation by Si: Xs = h(Ks||TXs)");

            user_registration_secrect_generation_by_si(rr.id, rr.sid, rr.txs, rcont, bpi, W);
        }

    }, 'json');
}


function user_registration_secrect_generation_by_si(id, sid, txs, rcont, bpi, W) {

    $.post(basePath + "?route=user-registration-server/secret-generation", {sid: sid, txs: txs}, function (sg) {

        if (2 == sg.flag) {
            $("#rs-process-status").html(sg.msg);
            $("#server-status").html("");

            console_write("s", sg.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-ur").removeClass("show").addClass("hidden");
            $(".arrow-rs").removeClass("show").addClass("hidden");
        } else if (1 == sg.flag) {

            $("#rs-process-status").html("Encrypting and Storing Secret");
            $("#server-status").html("SXi = Eaes(Xs,SKaes)");

            console_write("s", "Encrypting and Storing Secret by Si: SXi = Eaes(Xs,SKaes)");

            user_registration_encrypting_storing_secret_by_si(id, sid, sg.xs, rcont, bpi, txs, W);
        }

    }, 'json');
}

function user_registration_encrypting_storing_secret_by_si(id, sid, xs, rcont, bpi, txs, W) {
    $.post(basePath + "?route=user-registration-server/secret-storage", {id: id, sid: sid, xs: xs}, function (ses) {

        if (2 == ses.flag) {
            $("#rs-process-status").html(ses.msg);
            $("#server-status").html("");

            console_write("s", ses.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-ur").removeClass("show").addClass("hidden");
            $(".arrow-rs").removeClass("show").addClass("hidden");

            user_registration_server_revert(id, sid);

        } else if (1 == ses.flag) {
            $("#rs-process-status").html("Sending Reply");
            $("#server-status").html("{IDi,SIDi,Complete}");

            console_write("s", "Reply by Si: {IDi,SIDi,Complete}");
            console_write("s", "----------------------");

            $(".arrow-rs").removeClass("fa-arrow-right").addClass("fa-arrow-left");

            user_registration_reply_received_by_r(id, sid, ses.id, ses.sid, rcont, bpi, txs, W);
        }

    }, 'json');
}


function user_registration_reply_received_by_r(id, sid, rid, rsid, rcont, bpi, txs, W) {

    $.post(basePath + "?route=user-registration-rc/receive-reply", {
        id: id,
        sid: sid,
        rid: rid,
        rsid: rsid,
        stat: "Complete"
    }, function (rr) {

        $("#rs-process-status").html("Reply Received");
        $("#rc-status").html("{IDi,SIDi,Complete}");
        $("#server-status").html("");

        console_write("r", "Reply Received by R: {IDi,SIDi,Complete}");

        if (2 == rr.flag) {
            $("#rs-process-status").html(rr.msg);
            $("#rc-status").html("");

            console_write("r", rr.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-ur").removeClass("show").addClass("hidden");
            $(".arrow-rs").removeClass("show").addClass("hidden");

            user_registration_server_revert(id, sid);

        } else if (1 == rr.flag) {

            $("#rs-process-status").html("");
            $("#ur-process-status").html("Preparing Card");
            $("#rc-status").html("TCs = Ks||W");

            console_write("r", "Card Preparation by R: TCs = Ks||W");

            user_registration_card_preparation_by_r(rr.id, rr.sid, rcont, bpi, txs, W);
        }


    }, 'json');
}

function user_registration_card_preparation_by_r(id, sid, rcont, bpi, txs, W) {

    $.post(basePath + "?route=user-registration-rc/prepare-card", {id: id, sid: sid, bpi: bpi, W: W}, function (cp) {
        if (2 == cp.flag) {
            $("#ur-process-status").html(cp.msg);
            $("#rc-status").html("");

            console_write("r", cp.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-ur").removeClass("show").addClass("hidden");
            $(".arrow-rs").removeClass("show").addClass("hidden");

            user_registration_server_revert(id, sid);

        } else if (1 == cp.flag) {
            $("#ur-process-status").html("Distributing Smart Card");
            $("#rc-status").html("{IDi,SIDi,BPi,TCs}");

            console_write("r", "Smart Card Delivery by R: {IDi,SIDi,BPi,TCs}");
            console_write("r", "----------------------");

            $(".arrow-ur").removeClass("fa-arrow-right").addClass("fa-arrow-left");

            user_registration_receive_card_by_ci(id, sid, rcont, txs, cp.tcs);
        }
    }, 'json');
}

function user_registration_receive_card_by_ci(id, sid, rcont, txs, tcs) {
    $.post(basePath + "?route=user-registration/receive-card", {
        rid: id,
        rsid: sid,
        id: $("#user_id").val(),
        sid: $("#server_id").val(),
        pwi: $("#password").val(),
        bi: $("#biometric_key").val()
    }, function (cr) {
        $("#ur-process-status").html("Smart Card Received");
        $("#user-status").html("{IDi,SIDi,BPi,TCs}");
        $("#rc-status").html("");

        console_write("u", "Smart Card Received by Ci: {IDi,SIDi,BPi,TCs}");

        if (2 == cr.flag) {
            $("#ur-process-status").html(cr.msg);
            $("#user-status").html("");

            console_write("u", cr.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-ur").removeClass("show").addClass("hidden");
            $(".arrow-rs").removeClass("show").addClass("hidden");

            user_registration_server_revert(id, sid);
            user_registration_user_revert(id);

        } else if (1 == cr.flag) {
            $("#ur-process-status").html("Update Smart Card");
            $("#user-status").html("QXi = Eaes(TCs,Bi)");

            console_write("u", "Card Update by Ci: QXi = Eaes(TCs,Bi)");

            user_registration_update_card_and_ack_by_ci(id, sid, rcont, txs, tcs);
        }
    }, 'json');
}


function user_registration_update_card_and_ack_by_ci(id, sid, rcont, txs, tcs) {

    $.post(basePath + "?route=user-registration/update-card-ack", {
        id: id,
        bi: $("#biometric_key").val()
    }, function (uca) {

        if (2 == uca.flag) {
            $("#ur-process-status").html(uca.msg);
            $("#user-status").html("");

            console_write("u", uca.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-ur").removeClass("show").addClass("hidden");
            $(".arrow-rs").removeClass("show").addClass("hidden");

            user_registration_server_revert(id, sid);
            user_registration_user_revert(id);

        } else if (1 == uca.flag) {
            $("#ur-process-status").html("Sending Acknowledgement");
            $("#user-status").html("{IDi,SIDi,Accept}");

            console_write("u", "Sending Acknowledgement by Ci: {IDi,SIDi,Accept}");
            console_write("u", "----------------------");

            $(".arrow-ur").removeClass("fa-arrow-left").addClass("fa-arrow-right");

            user_registration_acknowledgement_receive_by_r(id, sid, uca.id, uca.sid, rcont, txs, tcs);
        }

    }, 'json');
}

function user_registration_acknowledgement_receive_by_r(id, sid, aid, asid, rcont, txs, tcs) {

    $.post(basePath + "?route=user-registration-rc/receive-ack", {
        id: id,
        sid: sid,
        aid: aid,
        asid: asid,
        stat: "Accept"
    }, function (ra) {

        $("#ur-process-status").html("Acknowledgement Received");
        $("#user-status").html("");
        $("#rc-status").html("{IDi,SIDi,Accept}");

        console_write("r", "Acknowledgement Received by R: {IDi,SIDi,Accept}");

        if (2 == ra.flag) {
            $("#ur-process-status").html(ra.msg);
            $("#rc-status").html("");

            console_write("r", ra.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-ur").removeClass("show").addClass("hidden");
            $(".arrow-rs").removeClass("show").addClass("hidden");

            user_registration_server_revert(id, sid);
            user_registration_user_revert(id);

        } else if (1 == ra.flag) {
            $("#ur-process-status").html("Encrypting and Storing Data");
            $("#rc-status").html("{IDi,SIDi,UXi,EXi,Rcov}");

            console_write("r", "Data Storage and Finalization by R: {IDi,SIDi,UXi,EXi,Rcov}");

            user_registration_data_storage_and_finalization_by_r(ra.id, ra.sid, rcont, txs, tcs);
        }

    }, 'json');
}

function user_registration_data_storage_and_finalization_by_r(id, sid, rcont, txs, tcs) {

    $.post(basePath + "?route=user-registration-rc/store-data-final", {
        id: id,
        sid: sid,
        txs: txs,
        tcs: tcs,
        rcont: rcont
    }, function (sdf) {

        if (2 == sdf.flag) {

            $("#ur-process-status").html(sdf.msg);
            $("#rc-status").html("");

            console_write("r", sdf.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-ur").removeClass("show").addClass("hidden");
            $(".arrow-rs").removeClass("show").addClass("hidden");

            user_registration_server_revert(id, sid);
            user_registration_rc_revert(id, sid);
            user_registration_user_revert(id);

        } else if (1 == sdf.flag) {

            $("#ur-process-status").html("User Registration Completed");
            $("#rc-status").html("");

            console_write("r", "User Registration Completed");
        }

    }, 'json');
}

function user_registration_server_revert(id, sid) {

    //revert server change
    $.post(basePath + "?route=user-registration-server/revert", {id: id, sid: sid}, function (sr) {
        console_write("s", sr.msg);
    }, "json");
}

function user_registration_rc_revert(id, sid) {
    //revert rc change
    $.post(basePath + "?route=user-registration-rc/revert", {id: id, sid: sid}, function (rr) {
        console_write("r", rr.msg);
    }, 'json');
}

function user_registration_user_revert(id) {
    //revert user change
    $.post(basePath + "?route=user-registration/revert", {id: id}, function (ur) {
        console_write("u", ur.msg);
    }, 'json');
}


function user_login_form() {
    $("#urf_user_id").val($("#user_id").val());
    $("#urf_password").val($("#password").val());
    $("#urf_biometric_key").val($("#biometric_key").val());
    $("#urf_server_id").val($("#server_id").val());

    $("#modal_login_form").modal();
}

function get_login_info(user_id) {
    $.post(basePath + "?route=login/get-info", {id: user_id}, function (data) {
        $("#srf_password").val(data.password);
        $("#srf_recovery_contact").val(data.recovery_contact);
        $("#srf_biometric_key").val(data.biometric_key);
    }, 'json');
}

function save_login_info() {

    if ($("#srf_user_id").val() == "0") {
        alert("Please select an user  id.");
        return false;
    }

    if ($("#srf_password").val() == "") {
        alert("Please provide password. Password field can't be empty.");
        return false;
    }

    if ($("#srf_biometric_key").val() == "") {
        alert("Please provide biometric key. Biometric key can't be empty.");
        return false;
    }

    if ($("#srf_server_id").val() == "0") {
        alert("Please select a server  id.");
        return false;
    }

    $("#user_id").val($("#srf_user_id").val());
    $("#password").val($("#srf_password").val());
    $("#biometric_key").val($("#srf_biometric_key").val());
    $("#server_id").val($("#srf_server_id").val());

    $("#modal_login_form").modal("hide");

}


function login_request_by_ci() {
    if ($("#user_id").val() == "0") {
        alert("Please select an user  id.");
        return false;
    }

    if ($("#password").val() == "") {
        alert("Please provide password. Password field can't be empty.");
        return false;
    }

    if ($("#biometric_key").val() == "") {
        alert("Please provide biometric key. Biometric key can't be empty.");
        return false;
    }

    if ($("#server_id").val() == "0") {
        alert("Please select a server  id.");
        return false;
    }

    var id = $("#user_id").val();
    var pwi = $("#password").val();
    var bi = $("#biometric_key").val();
    var sid = $("#server_id").val();

    $("#user-status").html("");
    $("#process-status").html("Login Request Generation");
    $("#server-status").html("");

    console_write("u", "Login Request Generation by Ci: Xs = h(TCs||BPi), M1 = h(Xs||Rn2), M2 = h(IDi||Xs) xor Rn2");

    $.post(basePath + "?route=login/generate-request", {id: id, pwi: pwi, bi: bi}, function (gr) {

        if (2 == gr.flag) {
            $("#process-status").html(gr.msg);
            console_write("u", gr.msg);
        } else if (1 == gr.flag) {
            $("#user-status").html("{IDi, SIDi, M1, M2, Login}");
            $("#process-status").html("Sending Login Request");

            console_write("u", "Login Request Sent by Ci: {IDi, SIDi, M1, M2, Login}");
            console_write("u", "----------------------");

            $(".fa-youtube-play").removeClass("show").addClass("hidden");
            $(".arrow-us").removeClass("hidden").addClass("show");

            login_request_collect_by_ai(id, sid, gr.M1, gr.M2);
            login_request_received_by_si(id, sid, gr.M1, gr.M2, gr.Xs, gr.Rn2);
        }

    }, 'json');
}


function login_request_collect_by_ai(id, sid, M1, M2) {

    $.post(basePath + "?route=data-collection-attacker/collect-login-request", {
        id: id,
        sid: sid,
        M1: M1,
        M2: M2
    }, function (clr) {

        console_write("a", "Trying to Collect Login Request {IDi, SIDi, M1, M2, Login}");

        if (2 == clr.flag) {

            console_write("a", "Failed to Save Login Request!!");

        } else if (1 == clr.flag) {

            $("#lid").val(clr.lid);
            console_write("a", "Login Request Saved Successfully. User ID: " + id);
            console_write("a", "------------------------------");
        }

    }, 'json');
}


function login_request_received_by_si(id, sid, M1, M2, Xs, Rn2) {
    $.post(basePath + "?route=authentication/receive-request", {
        id: id,
        sid: sid,
        M1: M1,
        M2: M2,
        stat: "Login"
    }, function (rr) {

        $("#user-status").html("");
        $("#process-status").html("Login Request Received");
        $("#server-status").html("{IDi, SIDi, M1, M2, Login}");

        console_write("s", "Login Request Received by Si: {IDi, SIDi, M1, M2, Login}");

        if (2 == rr.flag) {

            $("#process-status").html(rr.msg);
            $("#server-status").html("");

            console_write("s", rr.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-us").removeClass("show").addClass("hidden");

        } else if (1 == rr.flag) {

            $("#process-status").html("Verifying and Generating MuA Request");
            $("#server-status").html("M4 = h(Xs||Rn3), M5 = h(IDi||Xs) xor Rn3");

            console_write("s", "Verification and Mutual Authentication Request Generation by Si: M4 = h(Xs||Rn3), M5 = h(IDi||Xs) xor Rn3");

            verification_and_mutual_authentication_generation_by_si(id, sid, M1, M2, Xs, Rn2);
        }

    }, 'json');
}


function verification_and_mutual_authentication_generation_by_si(id, sid, M1, M2, Xs, Rn2) {
    $.post(basePath + "?route=authentication/verification-mua-generation", {
        id: id,
        sid: sid,
        M1: M1,
        M2: M2
    }, function (vmg) {

        if (2 == vmg.flag) {

            $("#process-status").html(vmg.msg);
            $("#server-status").html("");

            console_write("s", vmg.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-us").removeClass("show").addClass("hidden");

        } else if (1 == vmg.flag) {

            $("#process-status").html("Sending Mutual Authentication Request");
            $("#server-status").html("{IDi, SIDi, M4, M5, Auth}");

            console_write("s", "Mutual Authentication Request by Si: {{IDi, SIDi, M4, M5, Auth}}");
            console_write("s", "----------------------");

            setTimeout(function () {
                mutual_authentication_request_collect_by_ai(id, sid, vmg.M4, vmg.M5);
                mutual_authentication_request_by_si(id, sid, vmg.id, vmg.sid, vmg.M4, vmg.M5, Xs, Rn2, vmg.Rn3);
            }, 100);
        }

    }, 'json');
}


function mutual_authentication_request_collect_by_ai(id, sid, M4, M5) {

    $.post(basePath + "?route=data-collection-attacker/collect-mua", {
        id: id,
        sid: sid,
        M4: M4,
        M5: M5,
        lid: $("#lid").val()
    }, function (cmua) {

        console_write("a", "Trying to Collect Mutual Authentication Request {IDi, SIDi, M4, M5, Auth}");

        if (2 == cmua.flag) {

            console_write("a", "Failed to Save Mutual Authentication Request!!");

        } else if (1 == cmua.flag) {

            console_write("a", "Mutual Authentication Request Saved Successfully. User ID: " + id);
            console_write("a", "------------------------------");
        }

    }, 'json');
}

function mutual_authentication_request_by_si(id, sid, aid, asid, M4, M5, Xs, Rn2, Rn3) {

    $.post(basePath + "?route=login/receive-request", {
        id: id,
        sid: sid,
        aid: aid,
        asid: asid,
        M4: M4,
        M5: M5,
        Xs: Xs,
        Rn2: Rn2,
        stat: "Auth"
    }, function (rr) {

        $("#process-status").html("Mutual Authentication Request Received");
        $("#server-status").html("");
        $("#user-status").html("{IDi, SIDi, M4, M5, Auth}");

        console_write("u", "Mutual Authentication Request Received by Ci: {IDi, SIDi, M4, M5, Auth}");

        $(".arrow-us").removeClass("fa-arrow-right").addClass("fa-arrow-left");

        if (2 == rr.flag) {

            $("#process-status").html(rr.msg);
            $("#user-status").html("");

            console_write("u", rr.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-us").removeClass("show").addClass("hidden");

        } else if (1 == rr.flag) {

            $("#process-status").html("Mutually Authenticating and Generating Acknowledgement");
            $("#user-status").html("M7 = h(Xs || Rn2 || Rn3)");

            console_write("u", "Mutual Authentication and Acknowledgement Generation by Ci: M7 = h(Xs || Rn2 || Rn3)");

            mutual_authentication_and_acknowledgement_generation_by_ci(id, sid, rr.M4, rr.M5, Xs, Rn2, Rn3);
        }

    }, 'json');
}


function mutual_authentication_and_acknowledgement_generation_by_ci(id, sid, M4, M5, Xs, Rn2, Rn3) {

    $.post(basePath + "?route=login/mua-auth-ack", {
        id: id,
        sid: sid,
        M4: M4,
        M5: M5,
        Xs: Xs,
        Rn2: Rn2
    }, function (maa) {

        if (2 == maa.flag) {

            $("#process-status").html(maa.msg);
            $("#user-status").html("");

            console_write("u", maa.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-us").removeClass("show").addClass("hidden");

        } else if (1 == maa.flag) {

            $("#process-status").html("Sending Acknowledgement");
            $("#user-status").html("{IDi, SIDi, M7, Auth}");

            console_write("u", "Acknowledgement by Ci: {IDi, SIDi, M7, Auth}");
            console_write("u", "----------------------");

            acknowledgement_collect_by_ai(maa.id, maa.M7);
            acknowledgement_sent_by_ci(maa.id, maa.sid, maa.M7, Xs, Rn2, Rn3);
        }

    }, 'json');
}


function acknowledgement_collect_by_ai(id, M7) {
    $.post(basePath + "?route=data-collection-attacker/collect-ack", {M7: M7, lid: $("#lid").val()}, function (ca) {

        console_write("a", "Trying to Collect Acknowledgement {IDi, SIDi, M7, Auth}");

        if (2 == ca.flag) {

            console_write("a", "Failed to Save Acknowledgement!!");

        } else if (1 == ca.flag) {

            console_write("a", "Acknowledgement Saved Successfully. User ID: " + id);
            console_write("a", "------------------------------");
        }

    }, 'json');
}


function acknowledgement_sent_by_ci(id, sid, M7, Xs, Rn2, Rn3) {

    $.post(basePath + "?route=authentication/receive-ack", {
        id: id,
        sid: sid,
        M7: M7,
        Rn2: Rn2,
        Rn3: Rn3,
        stat: "Auth"
    }, function (ra) {

        $("#process-status").html("Acknowledgement Received");
        $("#server-status").html("{IDi, SIDi, M7, Auth}");
        $("#user-status").html("");

        console_write("s", "Acknowledgement Received by Si: {IDi, SIDi, M7, Auth}");

        $(".arrow-us").removeClass("fa-arrow-left").addClass("fa-arrow-right");

        if (2 == ra.flag) {
            $("#process-status").html(ra.msg);
            $("#server-status").html("");

            console_write("s", ra.flag);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-us").removeClass("show").addClass("hidden");

        } else if (1 == ra.flag) {

            $("#process-status").html("Verifying and Finalizing");
            $("#server-status").html("M8 = h(Xs || Rn2 || Rn3)");

            console_write("s", "Verification and Finalization by Si: M8 = h(Xs || Rn2 || Rn3)");

            verification_and_finalization_by_si(ra.id, ra.sid, ra.M7, Xs, ra.Rn2, ra.Rn3);
        }

    }, 'json');
}


function verification_and_finalization_by_si(id, sid, M7, Xs, Rn2, Rn3) {

    $.post(basePath + "?route=authentication/verification-finalization", {
        id: id,
        sid: sid,
        M7: M7,
        Xs: Xs,
        Rn2: Rn2,
        Rn3: Rn3
    }, function (vf) {

        if (2 == vf.flag) {
            $("#process-status").html(vf.msg);
            $("#server-status").html("");

            console_write("s", vf.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-us").removeClass("show").addClass("hidden");

        } else if (1 == vf.flag) {

            $("#process-status").html("Authentication Completed");
            $("#server-status").html("");

            console_write("s", "Authentication Completed");

            session_key_generation(Rn2, Rn3);
        }

    }, 'json');
}


function session_key_generation(Rn2, Rn3) {

    $.post(basePath + "?route=login/session-key-gen", {Rn2: Rn2, Rn3: Rn3}, function (skg) {

        console_write("u", "Session Key Generation by Ci: Kses = h(Rn2 || Rn3)");
        console_write("u", skg.msg);

    }, 'json');

    $.post(basePath + "?route=authentication/session-key-gen", {Rn2: Rn2, Rn3: Rn3}, function (skg) {

        console_write("s", "Session Key Generation by Si: Kses = h(Rn2 || Rn3)");
        console_write("s", skg.msg);

    }, 'json');

}

function password_change_form() {

    $("#pcf_user_id").val($("#user_id").val());
    $("#pcf_password").val($("#password").val());
    $("#pcf_recovery_contact").val($("#recovery_contact").val());
    $("#pcf_biometric_key").val($("#biometric_key").val());
    $("#pcf_server_id").val($("#server_id").val());
    $("#pcf_password_new").val($("#password_new").val());

    $("#modal_pc_form").modal();
}

function get_user_info_pc(user_id) {
    $.post(basePath + "?route=password-change/get-info", {id: user_id}, function (data) {
        $("#pcf_password").val(data.password);
        $("#pcf_recovery_contact").val(data.recovery_contact);
        $("#pcf_biometric_key").val(data.biometric_key);
    }, 'json');
}

function save_user_info_pc() {

    if ($("#pcf_user_id").val() == "0") {
        alert("Please select an user  id.");
        return false;
    }

    if ($("#pcf_password").val() == "") {
        alert("Please provide password. Password field can't be empty.");
        return false;
    }

    if ($("#pcf_recovery_contact").val() == "") {
        alert("Please provide recovery contact. Recovery contact can't be empty.");
        return false;
    }

    if ($("#pcf_biometric_key").val() == "") {
        alert("Please provide biometric key. Biometric key can't be empty.");
        return false;
    }

    if ($("#pcf_server_id").val() == "0") {
        alert("Please select a server  id.");
        return false;
    }

    if ($("#pcf_password_new").val() == "") {
        alert("Please provide new password. New password field can't be empty.");
        return false;
    }

    $("#user_id").val($("#pcf_user_id").val());
    $("#password").val($("#pcf_password").val());
    $("#recovery_contact").val($("#pcf_recovery_contact").val());
    $("#biometric_key").val($("#pcf_biometric_key").val());
    $("#server_id").val($("#pcf_server_id").val());
    $("#password_new").val($("#pcf_password_new").val());

    $("#modal_pc_form").modal("hide");

}


function password_change_request_by_ci() {

    if ($("#user_id").val() == "0") {
        alert("Please select an user  id.");
        return false;
    }

    if ($("#password").val() == "") {
        alert("Please provide password. Password field can't be empty.");
        return false;
    }

    if ($("#recovery_contact").val() == "") {
        alert("Please provide recovery contact. Recovery contact can't be empty.");
        return false;
    }

    if ($("#biometric_key").val() == "") {
        alert("Please provide biometric key. Biometric key can't be empty.");
        return false;
    }

    if ($("#server_id").val() == "0") {
        alert("Please select a server  id.");
        return false;
    }

    if ($("#password_new").val() == "") {
        alert("Please provide new password. New password field can't be empty.");
        return false;
    }

    var id = $("#user_id").val();
    var pwi = $("#password").val();
    var bi = $("#biometric_key").val();
    var sid = $("#server_id").val();
    var pwni = $("#password_new").val();

    $(".fa-youtube-play").removeClass("show").addClass("hidden");
    $(".arrow-ur").removeClass("hidden").addClass("show");
    $("#ur-process-status").html("Password Change Request Generation");
    $("#rs-process-status").html("");
    $("#user-status").html("Xs = h(TCs||BPi)");
    $("#rc-status").html("");
    $("#server-status").html("");

    console_write("u", "Password Change Request Generation by Ci: BPi = h(PWi||Bi), TCs = Daes(QXi,Bi), Xs = h(TCs||BPi)");

    $.post(basePath + "?route=password-change/generate-request", {id: id, pwi: pwi, bi: bi, pwni: pwni}, function (gr) {

        if (2 == gr.flag) {

            $("#ur-process-status").html(gr.msg);
            $("#user-status").html("");
            console_write("u", gr.msg);

            $(".arrow-ur").removeClass("show").addClass("hidden");
            $(".arrow-rs").removeClass("show").addClass("hidden");
            $(".fa-youtube-play").removeClass("hidden").addClass("show");

        } else if (1 == gr.flag) {

            $("#user-status").html("{IDi, Xs, PBi, SIDi, Passchange}");
            $("#ur-process-status").html("Sending Password Change Request");

            console_write("u", "Password Change Request Sent by Ci: {IDi, Xs, PBi, SIDi, Passchange}");
            console_write("u", "----------------------");

            password_change_request_received_by_r(id, sid, gr.Xs, gr.PBi);
        }

    }, 'json');
}

function password_change_request_received_by_r(id, sid, Xs, PBi) {

    $.post(basePath + "?route=password-change-rc/receive-request", {
        id: id,
        sid: sid,
        Xs: Xs,
        PBi: PBi,
        stat: "Passchange"
    }, function (rr) {

        $("#user-status").html("");
        $("#ur-process-status").html("Password Change Request Received");
        $("#rc-status").html("{IDi, Xs, PBi, SIDi, Passchange}");

        console_write("r", "Password Change Request Received by R: {IDi, Xs, PBi, SIDi, Passchange}");

        if (2 == rr.flag) {

            $("#ur-process-status").html(rr.msg);
            $("#rc-status").html("");
            console_write("r", rr.msg);

            $(".arrow-ur").removeClass("show").addClass("hidden");
            $(".arrow-rs").removeClass("show").addClass("hidden");
            $(".fa-youtube-play").removeClass("hidden").addClass("show");

        } else if (1 == rr.flag) {

            $("#ur-process-status").html("Verifying and Generating Secret Change Request");
            $("#rc-status").html("XTs = Rn4||PBi, TXs = Daes(EXi, RKaes)");

            console_write("r", "Verification and Secret Change Request Generation by R: XTs = Rn4||PBi, TXs = Daes(EXi, RKaes)");

            verify_and_generate_secret_change_request_by_r(rr.id, rr.sid, rr.Xs, rr.PBi);
        }

    }, 'json');
}

function verify_and_generate_secret_change_request_by_r(id, sid, Xs, PBi) {

    $.post(basePath + "?route=password-change-rc/verify-generate-request", {
        id: id,
        sid: sid,
        Xs: Xs,
        PBi: PBi
    }, function (vgr) {

        if (2 == vgr.flag) {

            $("#ur-process-status").html(vgr.msg);
            $("#rc-status").html("");
            console_write("r", vgr.msg);

            $(".arrow-ur").removeClass("show").addClass("hidden");
            $(".arrow-rs").removeClass("show").addClass("hidden");
            $(".fa-youtube-play").removeClass("hidden").addClass("show");

        } else if (1 == vgr.flag) {
            $("#ur-process-status").html("");
            $("#rs-process-status").html("Sending Secret Change Request");
            $("#rc-status").html("{IDi, SIDi, TXs, XTs, Passchange}");

            console_write("r", "Secret Change Request by R: {IDi, SIDi, TXs, XTs, Passchange}");
            console_write("r", "----------------------");

            secret_change_request_by_r(id, sid, vgr.TXs, vgr.XTs, vgr.Rn4, vgr.Ks);
        }

    }, 'json');
}

function secret_change_request_by_r(id, sid, TXs, XTs, Rn4, Ks) {
    $.post(basePath + "?route=password-change-server/receive-request", {
        id: id,
        sid: sid,
        TXs: TXs,
        XTs: XTs,
        stat: "Passchange"
    }, function (rr) {

        $("#rc-status").html("");
        $("#rs-process-status").html("Secret Change Request Received");
        $("#server-status").html("{IDi, SIDi, TXs, XTs, Passchange}");

        console_write("s", "Secret Change Request Received by Si: {IDi, SIDi, TXs, XTs, Passchange}");

        if (2 == rr.flag) {
            $("#rs-process-status").html(rr.msg);
            $("#server-status").html("");
            console_write("s", rr.msg);

            $(".arrow-ur").removeClass("show").addClass("hidden");
            $(".arrow-rs").removeClass("show").addClass("hidden");
            $(".fa-youtube-play").removeClass("hidden").addClass("show");
        } else if (1 == rr.flag) {
            $("#rs-process-status").html("Verifying and Changing Secret");
            $("#server-status").html("XSi = Eaes(Xcs, SKaes)");

            console_write("s", "Verification and Secret Change by Si: XSi = Eaes(Xcs, SKaes)");

            verification_and_secret_change_by_si(rr.id, rr.sid, rr.TXs, rr.XTs, Rn4, Ks);
        }

    }, 'json');
}


function verification_and_secret_change_by_si(id, sid, TXs, XTs, Rn4, Ks) {

    $.post(basePath + "?route=password-change-server/verify-secret-change", {
        id: id,
        sid: sid,
        TXs: TXs,
        XTs: XTs
    }, function (vsc) {

        if (2 == vsc.flag) {
            $("#rs-process-status").html(vsc.msg);
            $("#server-status").html("");

            console_write("s", vsc.msg);

            $(".arrow-ur").removeClass("show").addClass("hidden");
            $(".arrow-rs").removeClass("show").addClass("hidden");
            $(".fa-youtube-play").removeClass("hidden").addClass("show");
        } else if (1 == vsc.flag) {
            $("#rs-process-status").html("Sending Reply");
            $("#server-status").html("{IDi, SIDi, Complete}");

            console_write("s", "Reply Sent by Si: {IDi, SIDi, Complete}");
            console_write("s", "----------------------");

            $(".arrow-rs").removeClass("fa-arrow-right").addClass("fa-arrow-left");

            password_change_reply_received_by_r(id, sid, Rn4, Ks, XTs);
        }

    }, 'json');
}


function password_change_reply_received_by_r(id, sid, Rn4, Ks, XTs) {
    $.post(basePath + "?route=password-change-rc/receive-reply", {id: id, sid: sid, stat: "Complete"}, function (rr) {

        $("#rs-process-status").html("Reply Received");
        $("#rc-status").html("{IDi, SIDi, Complete}");
        $("#server-status").html("");

        console_write("r", "Reply Received by R: {IDi, SIDi, Complete}");

        if (2 == rr.flag) {

            $("#rs-process-status").html(rr.msg);
            $("#rc-status").html("");

            console_write("r", rr.msg);

            $(".arrow-ur").removeClass("show").addClass("hidden");
            $(".arrow-rs").removeClass("show").addClass("hidden");
            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            password_change_revert(id, sid);

        } else if (1 == rr.flag) {

            $("#rs-process-status").html("");
            $("#ur-process-status").html("Generating Smart Card Update Request");
            $("#rc-status").html("CTs = Ks||Rn4, TCs = Daes(UXi, RKaes)");

            console_write("r", "Smart Card Update Request Generation by R: CTs = Ks||Rn4, TCs = Daes(UXi, RKaes)");

            $(".arrow-ur").removeClass("fa-arrow-right").addClass("fa-arrow-left");

            password_change_generate_smart_card_update_request(id, sid, Rn4, Ks, XTs);
        }

    }, 'json');
}


function password_change_generate_smart_card_update_request(id, sid, Rn4, Ks, XTs) {

    $.post(basePath + "?route=password-change-rc/generate-scu-request", {
        id: id,
        sid: sid,
        Rn4: Rn4,
        Ks: Ks
    }, function (gsr) {

        if (2 == gsr.flag) {

            $("#ur-process-status").html(gsr.msg);
            $("#rc-status").html("");

            console_write("r", gsr.msg);

            $(".arrow-rs").removeClass("show").addClass("hidden");
            $(".arrow-ur").removeClass("show").addClass("hidden");
            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            password_change_revert(id, sid);

        } else if (1 == gsr.flag) {
            $("#ur-process-status").html("Sending Smart Card Update Request");
            $("#rc-status").html("{IDi, SIDi, CTs, TCs, Complete}");

            console_write("r", "Smart Card Update Request by R: {IDi, SIDi, CTs, TCs, Complete}");
            console_write("r", "----------------------");

            password_change_smart_card_update_request_by_r(id, sid, gsr.CTs, gsr.TCs, XTs);
        }

    }, 'json');
}


function password_change_smart_card_update_request_by_r(id, sid, CTs, TCs, XTs) {
    $.post(basePath + "?route=password-change/receive-request", {
        id: id,
        sid: sid,
        CTs: CTs,
        TCs: TCs,
        stat: "Complete"
    }, function (rr) {

        $("#ur-process-status").html("Smart Card Update Request Received");
        $("#user-status").html("{IDi, SIDi, CTs, TCs, Complete}");
        $("#rc-status").html("");

        console_write("u", "Smart Card Update Request Received by Ci: {IDi, SIDi, CTs, TCs, Complete}");

        if (2 == rr.flag) {

            $("#ur-process-status").html(rr.msg);
            $("#user-status").html("");

            console_write("u", rr.msg);

            $(".arrow-rs").removeClass("show").addClass("hidden");
            $(".arrow-ur").removeClass("show").addClass("hidden");
            $(".fa-youtube-play").removeClass("hidden").addClass("show");

            password_change_revert(id, sid);

        } else if (1 == rr.flag) {

            $("#ur-process-status").html("Updating Smart Card");
            $("#user-status").html("XQi = Eaes(CTs, Bi)");

            console_write("u", "Smart Card Update by Ci: XQi = Eaes(CTs, Bi)");

            password_change_smart_card_update_by_ci(rr.id, rr.sid, rr.CTs, rr.TCs, XTs);
        }

    }, 'json');

}


function password_change_smart_card_update_by_ci(id, sid, CTs, TCs, XTs) {
    $.post(basePath + "?route=password-change/update-card", {
        id: id,
        sid: sid,
        CTs: CTs,
        TCs: TCs,
        bi: $("#biometric_key").val(),
        pwi: $("#password").val(),
        pwni: $("#password_new").val()
    }, function (uc) {

        if (2 == uc.flag) {
            $("#ur-process-status").html(uc.msg);
            $("#user-status").html("");

            console_write("u", uc.msg);

            $(".arrow-rs").removeClass("show").addClass("hidden");
            $(".arrow-ur").removeClass("show").addClass("hidden");
            $(".fa-youtube-play").removeClass("hidden").addClass("show");

            password_change_revert(id, sid);

        } else if (1 == uc.flag) {
            $("#ur-process-status").html("Sending Acknowledgement");
            $("#user-status").html("{IDi, SIDi, Done}");

            console_write("u", "Acknowledgement Sent by Ci: {IDi, SIDi, Done}");
            console_write("u", "----------------------");

            $(".arrow-ur").removeClass("fa-arrow-left").addClass("fa-arrow-right");

            password_change_acknowledgement_sent_by_ci(id, sid, CTs, XTs);
        }

    }, 'json');
}

function password_change_acknowledgement_sent_by_ci(id, sid, CTs, XTs) {
    $.post(basePath + "?route=password-change-rc/receive-ack", {id: id, sid: sid, stat: "Done"}, function (ra) {

        $("#ur-process-status").html("Acknowledgement Received");
        $("#user-status").html("");
        $("#rc-status").html("{IDi, SIDi, Done}");

        console_write("r", "Acknowledgement Received by R: {IDi, SIDi, Done}");

        if (2 == ra.flag) {

            $("#ur-process-status").html(ra.msg);
            $("#rc-status").html("");

            console_write("r", ra.msg);

            $(".arrow-rs").removeClass("show").addClass("hidden");
            $(".arrow-ur").removeClass("show").addClass("hidden");
            $(".fa-youtube-play").removeClass("hidden").addClass("show");

            password_change_revert(id, sid);

        } else if (1 == ra.flag) {

            $("#ur-process-status").html("Updating Database");
            $("#rc-status").html("XUi = Eaes(CTs, RKaes), XEi = Eaes(XTs, RKaes)");

            console_write("r", "Database Update and Finalization by R: XUi = Eaes(CTs, RKaes), XEi = Eaes(XTs, RKaes)");

            password_change_database_update_and_finalization_by_r(ra.id, ra.sid, CTs, XTs);
        }

    }, 'json');
}


function password_change_database_update_and_finalization_by_r(id, sid, CTs, XTs) {
    $.post(basePath + "?route=password-change-rc/update-database", {
        id: id,
        sid: sid,
        CTs: CTs,
        XTs: XTs
    }, function (ud) {

        if (2 == ud.flag) {
            $("#ur-process-status").html(ud.msg);
            $("#rc-status").html("");

            console_write("r", ud.msg);

            $(".arrow-rs").removeClass("show").addClass("hidden");
            $(".arrow-ur").removeClass("show").addClass("hidden");
            $(".fa-youtube-play").removeClass("hidden").addClass("show");

            password_change_revert(id, sid);

        } else if (1 == ud.flag) {
            $("#ur-process-status").html("Password Change Completed.");
            $("#rc-status").html("");

            console_write("r", "Password Change Completed.");

            password_change_clear_cache(id, sid);
        }

    }, 'json');
}

function password_change_clear_cache(id, sid) {

    $.post(basePath + "?route=password-change/clear-cache", {id: id}, function (ucc) {

    }, 'json');

    $.post(basePath + "?route=password-change-server/clear-cache", {id: id, sid: sid}, function (scc) {

    }, 'json');
}

function password_change_revert(id, sid) {

    $.post(basePath + "?route=password-change/revert", {id: id}, function (ucc) {

    }, 'json');

    $.post(basePath + "?route=password-change-server/revert", {id: id, sid: sid}, function (scc) {

    }, 'json');

}

function show_reply_attack_form()
{
    $("#raf_user_id").val($("#user_id").val());
    $("#raf_server_id").val($("#server_id").val());
    $("#raf_m1").val($("#m1").val());
    $("#raf_m2").val($("#m2").val());

    $("#modal_reply_attack_form").modal();
}

function get_m1m2_set() {

    if ($("#raf_user_id").val() == "0") {
        alert("Please select an user id.");
        return false;
    }

    if ($("#raf_server_id").val() == "0") {
        alert("Please select a server id.");
        return false;
    }

    $.post(basePath + "?route=reply-attack/get-m1m2-set", {id: $("#raf_user_id").val(), sid: $("#raf_server_id").val()}, function (gms) {

        $("#sp_raf_m1_m2").html(gms);
    });
}

function get_m1_m2(id) {

    $.post(basePath + "?route=reply-attack/get-m1m2", {id: id}, function (gm) {

        $("#raf_m1").val(gm.m1);
        $("#raf_m2").val(gm.m2);

    }, 'json');
}

function save_reply_attack_info() {

    if ($("#raf_user_id").val() == "0") {
        alert("Please select an user id.");
        return false;
    }

    if ($("#raf_server_id").val() == "0") {
        alert("Please select a server id.");
        return false;
    }

    if ($("#raf_m1").val() == "" || $("#raf_m2").val() == "") {
        alert("Please select a m1-m2 set.");
        return false;
    }

    $("#user_id").val($("#raf_user_id").val());
    $("#server_id").val($("#raf_server_id").val());
    $("#m1").val($("#raf_m1").val());
    $("#m2").val($("#raf_m2").val());

    $("#modal_reply_attack_form").modal("hide");
}


function reply_attack_login_request_by_ai() {
    if ($("#user_id").val() == "0") {
        alert("Please select an user  id.");
        return false;
    }

    if ($("#server_id").val() == "0") {
        alert("Please select a server  id.");
        return false;
    }

    if ($("#m1").val() == "" || $("#m2").val() == "") {
        alert("Please select a m1-m2 set.");
        return false;
    }

    var id = $("#user_id").val();
    var sid = $("#server_id").val();
    var M1 = $("#m1").val();
    var M2 = $("#m2").val();

    $("#attacker-status").html("{IDi, SIDi, M1, M2, Login}");
    $("#process-status").html("Sending Login Request");

    console_write("a", "Login Request Sent by Ai: {IDi, SIDi, M1, M2, Login}");
    console_write("a", "----------------------");

    $(".fa-youtube-play").removeClass("show").addClass("hidden");
    $(".arrow-as").removeClass("hidden").addClass("show");

    reply_attack_login_request_received_by_si(id, sid, M1, M2);
}


function reply_attack_login_request_received_by_si(id, sid, M1, M2) {
    $.post(basePath + "?route=authentication/receive-request", {
        id: id,
        sid: sid,
        M1: M1,
        M2: M2,
        stat: "Login"
    }, function (rr) {

        $("#attacker-status").html("");
        $("#process-status").html("Login Request Received");
        $("#server-status").html("{IDi, SIDi, M1, M2, Login}");

        console_write("s", "Login Request Received by Si: {IDi, SIDi, M1, M2, Login}");

        if (2 == rr.flag) {

            $("#process-status").html(rr.msg);
            $("#server-status").html("");

            console_write("s", rr.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-as").removeClass("show").addClass("hidden");

        } else if (1 == rr.flag) {

            $("#process-status").html("Verifying and Generating MuA Request");
            $("#server-status").html("M4 = h(Xs||Rn3), M5 = h(IDi||Xs) xor Rn3");

            console_write("s", "Verification and Mutual Authentication Request Generation by Si: M4 = h(Xs||Rn3), M5 = h(IDi||Xs) xor Rn3");

            reply_attack_verification_and_mutual_authentication_generation_by_si(id, sid, M1, M2);
        }

    }, 'json');
}

function reply_attack_verification_and_mutual_authentication_generation_by_si(id, sid, M1, M2) {

    $.post(basePath + "?route=authentication/verification-mua-generation", {
        id: id,
        sid: sid,
        M1: M1,
        M2: M2
    }, function (vmg) {

        if (2 == vmg.flag) {

            $("#process-status").html(vmg.msg);
            $("#server-status").html("");

            console_write("s", vmg.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-as").removeClass("show").addClass("hidden");

        } else if (1 == vmg.flag) {

            $("#process-status").html("Sending Mutual Authentication Request");
            $("#server-status").html("{IDi, SIDi, M4, M5, Auth}");

            console_write("s", "Mutual Authentication Request by Si: {{IDi, SIDi, M4, M5, Auth}}");
            console_write("s", "----------------------");

            setTimeout(function () {
                reply_attack_mutual_authentication_request_by_si(id, sid, vmg.id, vmg.sid, vmg.M4, vmg.M5, vmg.Rn3);
            }, 100);
        }

    }, 'json');
}


function reply_attack_mutual_authentication_request_by_si(id, sid, aid, asid, M4, M5, Rn3) {
    $.post(basePath + "?route=reply-attack/receive-request", {
        id: id,
        sid: sid,
        aid: aid,
        asid: asid,
        M4: M4,
        M5: M5,
        stat: "Auth"
    }, function (rr) {

        $("#process-status").html("Mutual Authentication Request Received");
        $("#server-status").html("");
        $("#attacker-status").html("{IDi, SIDi, M4, M5, Auth}");

        console_write("a", "Mutual Authentication Request Received by Ai: {IDi, SIDi, M4, M5, Auth}");

        $(".arrow-as").removeClass("fa-arrow-right").addClass("fa-arrow-left");

        if (2 == rr.flag) {

            $("#process-status").html(rr.msg);
            $("#attacker-status").html("");

            console_write("a", rr.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-as").removeClass("show").addClass("hidden");

        } else if (1 == rr.flag) {

            $("#process-status").html("Mutually Authenticating and Generating Acknowledgement");
            $("#attacker-status").html("M7 = h(Xs || Rn2 || Rn3)");

            console_write("a", "Mutual Authentication and Acknowledgement Generation by Ai: M7 = h(Xs || Rn2 || Rn3)");

            reply_attack_mutual_authentication_and_acknowledgement_generation_by_ai(id, sid, rr.M4, rr.M5, "", "", Rn3);
        }

    }, 'json');
}

function reply_attack_mutual_authentication_and_acknowledgement_generation_by_ai(id, sid, M4, M5, Xs, Rn2, Rn3) {

    $.post(basePath + "?route=reply-attack/mua-auth-ack", {
        id: id,
        sid: sid,
        M4: M4,
        M5: M5,
        Xs: Xs,
        Rn2: Rn2
    }, function (maa) {

        if (2 == maa.flag) {

            $("#process-status").html(maa.msg);
            $("#attacker-status").html("");

            console_write("a", maa.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-as").removeClass("show").addClass("hidden");

        } else if (1 == maa.flag) {

            $("#process-status").html("Sending Acknowledgement");
            $("#attacker-status").html("{IDi, SIDi, M7, Auth}");

            console_write("a", "Acknowledgement by Ai: {IDi, SIDi, M7, Auth}");
            console_write("a", "----------------------");

            reply_attack_acknowledgement_sent_by_ai(maa.id, maa.sid, maa.M7, Xs, Rn2, Rn3);
        }

    }, 'json');
}

function reply_attack_acknowledgement_sent_by_ai(id, sid, M7, Xs, Rn2, Rn3) {

    $.post(basePath + "?route=authentication/receive-ack", {
        id: id,
        sid: sid,
        M7: M7,
        Rn2: Rn2,
        Rn3: Rn3,
        stat: "Auth"
    }, function (ra) {

        $("#process-status").html("Acknowledgement Received");
        $("#server-status").html("{IDi, SIDi, M7, Auth}");
        $("#attacker-status").html("");

        console_write("s", "Acknowledgement Received by Si: {IDi, SIDi, M7, Auth}");

        $(".arrow-as").removeClass("fa-arrow-left").addClass("fa-arrow-right");

        if (2 == ra.flag) {
            $("#process-status").html(ra.msg);
            $("#server-status").html("");

            console_write("s", ra.flag);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-as").removeClass("show").addClass("hidden");

        } else if (1 == ra.flag) {

            $("#process-status").html("Verifying and Finalizing");
            $("#server-status").html("M8 = h(Xs || Rn2 || Rn3)");

            console_write("s", "Verification and Finalization by Si: M8 = h(Xs || Rn2 || Rn3)");

            reply_attack_verification_and_finalization_by_si(ra.id, ra.sid, ra.M7, Xs, ra.Rn2, ra.Rn3);
        }

    }, 'json');
}


function reply_attack_verification_and_finalization_by_si(id, sid, M7, Xs, Rn2, Rn3) {

    $.post(basePath + "?route=authentication/verification-finalization", {
        id: id,
        sid: sid,
        M7: M7,
        Xs: Xs,
        Rn2: Rn2,
        Rn3: Rn3
    }, function (vf) {

        if (2 == vf.flag) {
            $("#process-status").html(vf.msg);
            $("#server-status").html("");

            console_write("s", vf.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-as").removeClass("show").addClass("hidden");

        } else if (1 == vf.flag) {

            $("#process-status").html("Authentication Completed");
            $("#server-status").html("");

            console_write("s", "Authentication Completed");

            //session_key_generation(Rn2, Rn3);
        }

    }, 'json');
}

function show_imp_attack_form()
{
    $("#iaf_user_id").val($("#user_id").val());
    $("#iaf_server_id").val($("#server_id").val());

    $("#modal_imp_attack_form").modal();
}

function save_imp_attack_info() {

    if ($("#iaf_user_id").val() == "0") {
        alert("Please select an user id.");
        return false;
    }

    if ($("#iaf_server_id").val() == "0") {
        alert("Please select a server id.");
        return false;
    }

    $("#user_id").val($("#iaf_user_id").val());
    $("#server_id").val($("#iaf_server_id").val());

    $("#modal_imp_attack_form").modal("hide");
}

function imp_attack_login_request_by_ai() {
    if ($("#user_id").val() == "0") {
        alert("Please select an user  id.");
        return false;
    }

    if ($("#server_id").val() == "0") {
        alert("Please select a server  id.");
        return false;
    }

    var id = $("#user_id").val();
    var pwi = "";
    var bi = "";
    var sid = $("#server_id").val();

    $("#attacker-status").html("");
    $("#process-status").html("Login Request Generation");
    $("#server-status").html("");

    console_write("a", "Login Request Generation by Ai: M1 = h(Xs||Rn2), M2 = h(IDi||Xs) xor Rn2");

    $.post(basePath + "?route=impersonation-attack/generate-request", {id: id, pwi: pwi, bi: bi}, function (gr) {

        if (2 == gr.flag) {
            $("#process-status").html(gr.msg);
            console_write("a", gr.msg);
        } else if (1 == gr.flag) {
            $("#attacker-status").html("{IDi, SIDi, M1, M2, Login}");
            $("#process-status").html("Sending Login Request");

            console_write("a", "Login Request Sent by Ci: {IDi, SIDi, M1, M2, Login}");
            console_write("a", "----------------------");

            $(".fa-youtube-play").removeClass("show").addClass("hidden");
            $(".arrow-as").removeClass("hidden").addClass("show");

            imp_attack_login_request_received_by_si(id, sid, gr.M1, gr.M2, gr.Xs, gr.Rn2);
        }

    }, 'json');
}


function imp_attack_login_request_received_by_si(id, sid, M1, M2, Xs, Rn2) {
    $.post(basePath + "?route=authentication/receive-request", {
        id: id,
        sid: sid,
        M1: M1,
        M2: M2,
        stat: "Login"
    }, function (rr) {

        $("#attacker-status").html("");
        $("#process-status").html("Login Request Received");
        $("#server-status").html("{IDi, SIDi, M1, M2, Login}");

        console_write("s", "Login Request Received by Si: {IDi, SIDi, M1, M2, Login}");

        if (2 == rr.flag) {

            $("#process-status").html(rr.msg);
            $("#server-status").html("");

            console_write("s", rr.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-as").removeClass("show").addClass("hidden");

        } else if (1 == rr.flag) {

            $("#process-status").html("Verifying and Generating MuA Request");
            $("#server-status").html("M4 = h(Xs||Rn3), M5 = h(IDi||Xs) xor Rn3");

            console_write("s", "Verification and Mutual Authentication Request Generation by Si: M4 = h(Xs||Rn3), M5 = h(IDi||Xs) xor Rn3");

            imp_attack_verification_and_mutual_authentication_generation_by_si(id, sid, M1, M2, Xs, Rn2);
        }

    }, 'json');
}

function imp_attack_verification_and_mutual_authentication_generation_by_si(id, sid, M1, M2, Xs, Rn2) {
    $.post(basePath + "?route=authentication/verification-mua-generation", {
        id: id,
        sid: sid,
        M1: M1,
        M2: M2
    }, function (vmg) {

        if (2 == vmg.flag) {

            $("#process-status").html(vmg.msg);
            $("#server-status").html("");

            console_write("s", vmg.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-as").removeClass("show").addClass("hidden");

        } else if (1 == vmg.flag) {

            $("#process-status").html("Sending Mutual Authentication Request");
            $("#server-status").html("{IDi, SIDi, M4, M5, Auth}");

            console_write("s", "Mutual Authentication Request by Si: {{IDi, SIDi, M4, M5, Auth}}");
            console_write("s", "----------------------");

            setTimeout(function () {
                //mutual_authentication_request_by_si(id, sid, vmg.id, vmg.sid, vmg.M4, vmg.M5, Xs, Rn2, vmg.Rn3);
            }, 100);
        }

    }, 'json');
}


function sma_login_request_by_ci() {

    if ($("#user_id").val() == "0") {
        alert("Please select an user  id.");
        return false;
    }

    if ($("#password").val() == "") {
        alert("Please provide password. Password field can't be empty.");
        return false;
    }

    if ($("#biometric_key").val() == "") {
        alert("Please provide biometric key. Biometric key can't be empty.");
        return false;
    }

    if ($("#server_id").val() == "0") {
        alert("Please select a server  id.");
        return false;
    }

    var id = $("#user_id").val();
    var pwi = $("#password").val();
    var bi = $("#biometric_key").val();
    var sid = $("#server_id").val();

    $("#user-status").html("");
    $("#process-status").html("Login Request Generation");
    $("#attacker-status").html("");

    console_write("u", "Login Request Generation by Ci: Xs = h(TCs||BPi), M1 = h(Xs||Rn2), M2 = h(IDi||Xs) xor Rn2");

    $.post(basePath + "?route=login/generate-request", {id: id, pwi: pwi, bi: bi}, function (gr) {

        if (2 == gr.flag) {
            $("#process-status").html(gr.msg);
            console_write("u", gr.msg);
        } else if (1 == gr.flag) {
            $("#user-status").html("{IDi, SIDi, M1, M2, Login}");
            $("#process-status").html("Sending Login Request");

            console_write("u", "Login Request Sent by Ci: {IDi, SIDi, M1, M2, Login}");
            console_write("u", "----------------------");

            $(".fa-youtube-play").removeClass("show").addClass("hidden");
            $(".arrow-ua").removeClass("hidden").addClass("show");

            sma_login_request_received_by_ai(id, sid, gr.M1, gr.M2, gr.Xs, gr.Rn2);
        }

    }, 'json');

}


function sma_login_request_received_by_ai(id, sid, M1, M2, Xs, Rn2) {
    $.post(basePath + "?route=server-masquerading-attack-attacker/receive-request", {
        id: id,
        sid: sid,
        M1: M1,
        M2: M2,
        stat: "Login"
    }, function (rr) {

        $("#user-status").html("");
        $("#process-status").html("Login Request Received");
        $("#attacker-status").html("{IDi, SIDi, M1, M2, Login}");

        console_write("a", "Login Request Received by Si: {IDi, SIDi, M1, M2, Login}");

        if (2 == rr.flag) {

            $("#process-status").html(rr.msg);
            $("#attacker-status").html("");

            console_write("a", rr.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-us").removeClass("show").addClass("hidden");

        } else if (1 == rr.flag) {

            $("#process-status").html("Verifying and Generating MuA Request");
            $("#attacker-status").html("M4 = h(Xs||Rn3), M5 = h(IDi||Xs) xor Rn3");

            console_write("a", "Verification and Mutual Authentication Request Generation by Si: M4 = h(Xs||Rn3), M5 = h(IDi||Xs) xor Rn3");

            sma_verification_and_mutual_authentication_generation_by_ai(id, sid, M1, M2, Xs, Rn2);
        }

    }, 'json');
}


function sma_verification_and_mutual_authentication_generation_by_ai(id, sid, M1, M2, Xs, Rn2) {
    $.post(basePath + "?route=server-masquerading-attack-attacker/verification-mua-generation", {
        id: id,
        sid: sid,
        M1: M1,
        M2: M2
    }, function (vmg) {

        if (2 == vmg.flag) {

            $("#process-status").html(vmg.msg);
            $("#attacker-status").html("");

            console_write("a", vmg.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-ua").removeClass("show").addClass("hidden");

        } else if (1 == vmg.flag) {

            $("#process-status").html("Sending Mutual Authentication Request");
            $("#attacker-status").html("{IDi, SIDi, M4, M5, Auth}");

            console_write("a", "Mutual Authentication Request by Si: {{IDi, SIDi, M4, M5, Auth}}");
            console_write("a", "----------------------");

            setTimeout(function () {
                sma_mutual_authentication_request_by_ai(id, sid, vmg.id, vmg.sid, vmg.M4, vmg.M5, Xs, Rn2, vmg.Rn3);
            }, 100);
        }

    }, 'json');
}

function sma_mutual_authentication_request_by_ai(id, sid, aid, asid, M4, M5, Xs, Rn2, Rn3) {

    $.post(basePath + "?route=login/receive-request", {
        id: id,
        sid: sid,
        aid: aid,
        asid: asid,
        M4: M4,
        M5: M5,
        Xs: Xs,
        Rn2: Rn2,
        stat: "Auth"
    }, function (rr) {

        $("#process-status").html("Mutual Authentication Request Received");
        $("#attacker-status").html("");
        $("#user-status").html("{IDi, SIDi, M4, M5, Auth}");

        console_write("u", "Mutual Authentication Request Received by Ci: {IDi, SIDi, M4, M5, Auth}");

        $(".arrow-us").removeClass("fa-arrow-right").addClass("fa-arrow-left");

        if (2 == rr.flag) {

            $("#process-status").html(rr.msg);
            $("#user-status").html("");

            console_write("u", rr.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-us").removeClass("show").addClass("hidden");

        } else if (1 == rr.flag) {

            $("#process-status").html("Mutually Authenticating and Generating Acknowledgement");
            $("#user-status").html("M7 = h(Xs || Rn2 || Rn3)");

            console_write("u", "Mutual Authentication and Acknowledgement Generation by Ci: M7 = h(Xs || Rn2 || Rn3)");

            sma_mutual_authentication_and_acknowledgement_generation_by_ci(id, sid, rr.M4, rr.M5, Xs, Rn2, Rn3);
        }

    }, 'json');
}


function sma_mutual_authentication_and_acknowledgement_generation_by_ci(id, sid, M4, M5, Xs, Rn2, Rn3) {

    $.post(basePath + "?route=login/mua-auth-ack", {
        id: id,
        sid: sid,
        M4: M4,
        M5: M5,
        Xs: Xs,
        Rn2: Rn2
    }, function (maa) {

        if (2 == maa.flag) {

            $("#process-status").html(maa.msg);
            $("#user-status").html("");

            console_write("u", maa.msg);

            $(".fa-youtube-play").removeClass("hidden").addClass("show");
            $(".arrow-us").removeClass("show").addClass("hidden");

        } else if (1 == maa.flag) {

            $("#process-status").html("Sending Acknowledgement");
            $("#user-status").html("{IDi, SIDi, M7, Auth}");

            console_write("u", "Acknowledgement by Ci: {IDi, SIDi, M7, Auth}");
            console_write("u", "----------------------");

            //acknowledgement_sent_by_ci(maa.id, maa.sid, maa.M7, Xs, Rn2, Rn3);
        }

    }, 'json');
}