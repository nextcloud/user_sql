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
                    post.push({name: "input", value: request["term"]});
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

        var cryptoParams = function () {
            var cryptoChanged = function () {
                var content = $("#opt-crypto_params_content");
                var loading = $("#opt-crypto_params_loading");

                content.hide();
                loading.show();

                $.get(OC.generateUrl("/apps/user_sql/settings/crypto/params"), {cryptoClass: $("#opt-crypto_class").val()},
                    function (data) {
                        content.empty();
                        loading.hide();

                        if (data.status === "success") {
                            for (var index = 0, length = data.data.length; index < length; ++index) {
                                var param = $("<div></div>");
                                var label = $("<label></label>").attr({for: "opt-crypto_param_" + index});
                                var title = $("<span></span>").text(data.data[index]["name"]);

                                var input = null;
                                switch (data.data[index]["type"]) {
                                    case "choice":
                                        input = $("<select/>").attr({
                                            id: "opt-crypto_param_" + index,
                                            name: "opt-crypto_param_" + index,
                                        });
                                        data.data[index]["choices"].forEach(
                                            function (item) {
                                                if (data.data[index]["value"] === item) {
                                                    input.append($("<option/>").attr({
                                                        value: item,
                                                        selected: "selected"
                                                    }).text(item));
                                                } else {
                                                    input.append($("<option/>").attr({value: item}).text(item));
                                                }
                                            }
                                        );
                                        break;
                                    case "int":
                                        input = $("<input/>").attr({
                                            type: "number",
                                            id: "opt-crypto_param_" + index,
                                            name: "opt-crypto_param_" + index,
                                            step: 1,
                                            min: data.data[index]["min"],
                                            max: data.data[index]["max"],
                                            value: data.data[index]["value"]
                                        });
                                        break;
                                    default:
                                        break;
                                }

                                label.append(title);
                                param.append(label);
                                param.append(input);
                                content.append(param);
                                content.show();
                            }
                        }
                    }, "json");
            };
            $("#opt-crypto_class").change(function () {
                cryptoChanged();
            });
            cryptoChanged();
        };

        $("#db-driver").change(function () {
            var ssl_ca = $("#db-ssl_ca").parent().parent();
            var ssl_cert = $("#db-ssl_cert").parent().parent();
            var ssl_key = $("#db-ssl_key").parent().parent();
            if ($("#db-driver").val() === 'mysql') {
                ssl_ca.show();
                ssl_cert.show();
                ssl_key.show();
            } else {
                ssl_ca.hide();
                ssl_cert.hide();
                ssl_key.hide();
            }
        });

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
            "#db-table-user-column-uid, #db-table-user-column-username, #db-table-user-column-email, #db-table-user-column-quota, #db-table-user-column-home, #db-table-user-column-password, #db-table-user-column-name, #db-table-user-column-active, #db-table-user-column-disabled, #db-table-user-column-avatar, #db-table-user-column-salt",
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

        cryptoParams();
    }
};

$(document).ready(function () {
    if ($(form_id)) {
        user_sql.adminSettingsUI();
    }
});
