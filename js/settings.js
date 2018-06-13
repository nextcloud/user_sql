var user_sql = user_sql || {};
var form_id = "#user_sql";

user_sql.adminSettingsUI = function () {
    var app_id = "user_sql";

    if ($(form_id).length > 0) {

        var click = function (event, path) {
            event.preventDefault();

            var post = $(form_id).serializeArray();
            var msg = $("#user_sql-msg");
            var msg_body = $("#user_sql-msg-body");

            msg_body.html(t(app_id, "Waiting..."));
            msg.addClass("waiting");
            msg.slideDown();

            $.post(OC.generateUrl(path), post, function (data) {
                msg_body.html(data.data.message);
                msg.removeClass("error");
                msg.removeClass("success");
                msg.removeClass("waiting");

                if (data.status === "success") {
                    msg.addClass("success");
                } else {
                    msg.addClass("error");
                }

                window.setTimeout(function () {
                    msg.slideUp();
                }, 10000);
            }, "json");

            return false;
        };

        var autocomplete = function (ids, path) {
            $(ids).autocomplete({
                source: function (request, response) {
                    var post = $(form_id).serializeArray();
                    $.post(OC.generateUrl(path), post, response, "json");
                },
                minLength: 0,
                open: function () {
                    $(this).attr("state", "open");
                },
                close: function () {
                    $(this).attr("state", "closed");
                }
            }).focus(function () {
                if ($(this).attr("state") !== "open") {
                    $(this).autocomplete("search");
                }
            });
        };

        $("#user_sql-db_connection_verify").click(function (event) {
            return click(event, "/apps/user_sql/settings/db/verify");
        });

        $("#user_sql-clear_cache").click(function (event) {
            return click(event, "/apps/user_sql/settings/cache/clear");
        });

        $("#user_sql-save").click(function (event) {
            return click(event, "/apps/user_sql/settings/properties");
        });

        autocomplete(
            "#db-table-user, #db-table-user_group, #db-table-group",
            "/apps/user_sql/settings/autocomplete/table"
        );

        autocomplete(
            "#db-table-user-column-uid, #db-table-user-column-email, #db-table-user-column-home, #db-table-user-column-password, #db-table-user-column-name, #db-table-user-column-avatar",
            "/apps/user_sql/settings/autocomplete/table/user"
        );

        autocomplete(
            "#db-table-user_group-column-uid, #db-table-user_group-column-gid",
            "/apps/user_sql/settings/autocomplete/table/user_group"
        );

        autocomplete(
            "#db-table-group-column-admin, #db-table-group-column-name, #db-table-group-column-gid",
            "/apps/user_sql/settings/autocomplete/table/group"
        );
    }
};

$(document).ready(function () {
    if ($(form_id)) {
        user_sql.adminSettingsUI();
    }
});