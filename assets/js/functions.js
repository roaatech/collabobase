var fw = (function() {
    object = {};
    fw = object;
    $.extend(object, {
        "corePath": systemCorePath,
        "ui": {
            "loading": {
                "show": function() {
                    $("#loader_modal").modal("show");
                },
                "hide": function() {
                    $("#loader_modal").modal("hide");
                }
            }
        },
        "libraries": {
            "string": {
                "trim": function(str) {
                    return str.replace(/^\s\s*/, "").replace(/\s\s*$/, "");
                },
            },
        },
        "temporary": {
            sessionCanExpire: true,
        },
        "logic": {
        },
        "core": {
            checkForLoginStatus: function() {
                if (fw.temporary.hasOwnProperty("sessionCanExpire") && fw.temporary.sessionCanExpire) {
                    if (!fw.temporary.hasOwnProperty("sessionExpiryCheckSetup")) {
                        fw.temporary.sessionExpiryCheckSetup = true;
                        window.setInterval("fw.core.checkForLoginStatus()", 60000);
                    }
                    $.ajax(fw.corePath + "account/isAuthenticated", {
                        complete: function(xhr) {
                            if (xhr.status == 401) {
                                window.location.reload();
                            }
                        },
                    });
                }
            },
            setupExpireableSession: function() {
                fw.temporary.sessionCanExpire = true;
                this.checkForLoginStatus();
            },
            setupUnexpireableSession: function() {
                fw.temporary.sessionCanExpire = false;
            },
        },
    });

    return object;
})();

String.prototype.trimSpaces = function() {
    return this.replace(/^\s\s*/, "").replace(/\s\s*$/, "");
}

function trim(str) {
    return fw.libraries.string.trim(str);
}