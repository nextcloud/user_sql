// settings.js of user_sql

// declare namespace
var user_sql = user_sql ||
{
};

/**
 * init admin settings view
 */
user_sql.adminSettingsUI = function()
{

    if($('#sqlDiv').length > 0)
    {
        // enable tabs on settings page
        $('#sqlDiv').tabs();
        
        // Attach auto-completion to all column fields
        $('#col_username, #col_password, #col_displayname, #col_active, #col_email, #col_gethome').autocomplete({
            source: function(request, response)
            {
                var post = $('#sqlForm').serializeArray();
                var domain = $('#sql_domain_chooser option:selected').val();
                
                post.push({
                    name: 'function',
                    value: 'getColumnAutocomplete'
                });
                
                post.push({
                    name: 'domain',
                    value: domain
                });
                
                post.push({
                    name: 'request',
                    value: request.term
                });
    
                // Ajax foobar
                $.post(OC.filePath('user_sql', 'ajax', 'settings.php'), post, response, 'json');
            },
            minLength: 0,
            open: function() {
                $(this).attr('state', 'open');
            },
            close: function() {
                $(this).attr('state', 'closed');
            }
        }).focus(function() {
           if($(this).attr('state') != 'open')
           {
               $(this).autocomplete("search");
           } 
        });        
        
        // Attach auto-completion to all table fields
        $('#sql_table').autocomplete({
            source: function(request, response)
            {
                var post = $('#sqlForm').serializeArray();
                var domain = $('#sql_domain_chooser option:selected').val();
                
                post.push({
                    name: 'function',
                    value: 'getTableAutocomplete'
                });
                
                post.push({
                    name: 'domain',
                    value: domain
                });
                
                post.push({
                    name: 'request',
                    value: request.term
                });
    
                // Ajax foobar
                $.post(OC.filePath('user_sql', 'ajax', 'settings.php'), post, response, 'json');
            },
            minLength: 0,
            open: function() {
                $(this).attr('state', 'open');
            },
            close: function() {
                $(this).attr('state', 'closed');
            }
        }).focus(function() {
           if($(this).attr('state') != 'open')
           {
               $(this).autocomplete("search");
           } 
        });
        
        // Verify the SQL database settings
        $('#sqlVerify').click(function(event)
        {
            event.preventDefault();

            var post = $('#sqlForm').serializeArray();
            var domain = $('#sql_domain_chooser option:selected').val();
            
            post.push({
                name: 'function',
                value: 'verifySettings'
            });
            
            post.push({
                name: 'domain',
                value: domain
            });

            $('#sql_verify_message').show();
            $('#sql_success_message').hide();
            $('#sql_error_message').hide();
            $('#sql_update_message').hide();
            // Ajax foobar
            $.post(OC.filePath('user_sql', 'ajax', 'settings.php'), post, function(data)
            {
                $('#sql_verify_message').hide();
                if(data.status == 'success')
                {
                    $('#sql_success_message').html(data.data.message);
                    $('#sql_success_message').show();
                    window.setTimeout(function()
                    {
                        $('#sql_success_message').hide();
                    }, 10000);
                } else
                {
                    $('#sql_error_message').html(data.data.message);
                    $('#sql_error_message').show();
                }
            }, 'json');
            return false;
        });            

        // Save the settings for a domain
        $('#sqlSubmit').click(function(event)
        {
            event.preventDefault();

            var post = $('#sqlForm').serializeArray();
            var domain = $('#sql_domain_chooser option:selected').val();
            
            post.push({
                name: 'function',
                value: 'saveSettings'
            });
            
            post.push({
                name: 'domain',
                value: domain
            });

            $('#sql_update_message').show();
            $('#sql_success_message').hide();
            $('#sql_verify_message').hide();
            $('#sql_error_message').hide();
            // Ajax foobar
            $.post(OC.filePath('user_sql', 'ajax', 'settings.php'), post, function(data)
            {
                $('#sql_update_message').hide();
                if(data.status == 'success')
                {
                    $('#sql_success_message').html(data.data.message);
                    $('#sql_success_message').show();
                    window.setTimeout(function()
                    {
                        $('#sql_success_message').hide();
                    }, 10000);
                } else
                {
                    $('#sql_error_message').html(data.data.message);
                    $('#sql_error_message').show();
                }
            }, 'json');
            return false;
        });

        // Attach event handler to the domain chooser
        $('#sql_domain_chooser').change(function() {
           user_sql.loadDomainSettings($('#sql_domain_chooser option:selected').val());
        });
        
        $('#set_gethome_mode').change(function() {
           user_sql.setGethomeMode();
        });
        
        $('#set_enable_gethome').change(function() {
            user_sql.setGethomeMode();
        });
    }
};

user_sql.setGethomeMode = function()
{
    var enabled = $('#set_enable_gethome').prop('checked');
    if(enabled)
    {
        $('#set_gethome_mode').prop('disabled', false);
        var val = $('#set_gethome_mode option:selected').val();
        if(val === 'query')
        {
            $('#set_gethome').prop('disabled', true);
            $('#col_gethome').prop('disabled', false);
        }
        else if(val === 'static')
        {
            $('#set_gethome').prop('disabled', false);
            $('#col_gethome').prop('disabled', true);
        }
        else
        {
            $('#set_gethome').prop('disabled', true);
            $('#col_gethome').prop('disabled', true);
        }
    }
    else
    {
        $('#set_gethome_mode').prop('disabled', true);
        $('#set_gethome').prop('disabled', true);
        $('#col_gethome').prop('disabled', true);
    }
};

/**
 * Load the settings for the selected domain
 * @param string domain The domain to load
 */
user_sql.loadDomainSettings = function(domain)
{
    $('#sql_success_message').hide();
    $('#sql_error_message').hide();
    $('#sql_verify_message').hide();
    $('#sql_loading_message').show();
    var post = [
        {
            name: 'appname',
            value: 'user_sql'
        },
        {
            name: 'function',
            value: 'loadSettingsForDomain'
        },
        {
            name: 'domain',
            value: domain
        }
    ];
    $.post(OC.filePath('user_sql', 'ajax', 'settings.php'), post, function(data)
        {
            $('#sql_loading_message').hide();
            if(data.status == 'success')
            {
                for(key in data.settings)
                {
                    if(key == 'set_strip_domain')
                    {
                        if(data.settings[key] == 'true')
                            $('#' + key).prop('checked', true);
                        else
                            $('#' + key).prop('checked', false);
                    }
                    else if(key == 'set_allow_pwchange')
                    {
                        if(data.settings[key] == 'true')
                            $('#' + key).prop('checked', true);
                        else
                            $('#' + key).prop('checked', false);
                    }
                    else if(key == 'set_active_invert')
                    {
                        if(data.settings[key] == 'true')
                            $('#' + key).prop('checked', true);
                        else
                            $('#' + key).prop('checked', false);
                    }
                    else if(key == 'set_enable_gethome')
                    {
                        if(data.settings[key] == 'true')
                            $('#' + key).prop('checked', true);
                        else
                            $('#' + key).prop('checked', false);
                    }
                    else
                    {
                        $('#' + key).val(data.settings[key]);
                    }
                }
            }
            else
            {
                $('#sql_error_message').html(data.data.message);
                $('#sql_error_message').show();
            }
            user_sql.setGethomeMode();
        }, 'json'
    );
};

// Run our JS if the SQL settings are present
$(document).ready(function()
{
    if($('#sqlDiv'))
    {
        user_sql.adminSettingsUI();
        user_sql.loadDomainSettings($('#sql_domain_chooser option:selected').val());
        user_sql.setGethomeMode();
    }
});

