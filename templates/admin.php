<?php
script('user_sql', 'settings');
style('user_sql', 'settings');
$cfgClass =  'section';
?>

<div class="<?php p($cfgClass); ?>">
    <h2><?php p($l->t('SQL User Backend')); ?></h2>

<form id="sqlForm" action="#" method="post" class="<?php p($cfgClass); ?>">

    <div id="sqlDiv" class="<?php p($cfgClass); ?>">
    <label for="sql_domain_chooser"><?php p($l -> t('Settings for Domain')) ?></label>
    <select id="sql_domain_chooser" name="sql_domain_chooser">
        <?php foreach ($_['allowed_domains'] as $domain): ?>
            <option value="<?php p($domain); ?>"><?php p($domain); ?></option>
        <?php endforeach ?>
    </select>
    <ul>
      <li><a id="sqlBasicSettings" href="#sql-1"><?php p($l -> t('Connection Settings')); ?></a></li>
      <li><a id="sqlColSettings" href="#sql-2"><?php p($l -> t('Column Settings')); ?></a></li>
      <li><a id="sqlEmailSettings" href="#sql-3"><?php p($l -> t('E-Mail Settings')); ?></a></li>
      <li><a id="sqlDomainSettings" href="#sql-4"><?php p($l -> t('Domain Settings')); ?></a></li>
      <li><a id="sqlGethomeSettings" href="#sql-5"><?php p($l -> t('getHome Settings')); ?></a></li>
      <li><a id="sqlGroupsSettings" href="#sql-6"><?php p($l -> t('Groups Settings')); ?></a></li>
    </ul>

        <fieldset id="sql-1">
           <p><label for="sql_driver"><?php p($l -> t('SQL Driver')); ?></label>
                <?php $db_driver = array('mysql' => 'MySQL', 'pgsql' => 'PostgreSQL'); ?>
                <select id="sql_driver" name="sql_driver">
                    <?php
                        foreach ($db_driver as $driver => $name):
                            //echo $_['sql_driver'];
                            if($_['sql_driver'] === $driver): ?>
                                <option selected="selected" value="<?php p($driver); ?>"><?php p($name); ?></option>
                            <?php else: ?>
                                <option value="<?php p($driver); ?>"><?php p($name); ?></option>
                            <?php endif;
                        endforeach;
                    ?>
                </select>
            </p>

            <p><label for="sql_hostname"><?php p($l -> t('Host')); ?></label><input type="text" id="sql_hostname" name="sql_hostname" value="<?php p($_['sql_hostname']); ?>"></p>

            <p><label for="sql_database"><?php p($l -> t('Database')); ?></label><input type="text" id="sql_database" name="sql_database" value="<?php p($_['sql_database']); ?>" /></p>

            <p><label for="sql_username"><?php p($l -> t('Username')); ?></label><input type="text" id="sql_username" name="sql_username" value="<?php p($_['sql_username']); ?>" /></p>

            <p><label for="sql_password"><?php p($l -> t('Password')); ?></label><input type="password" id="sql_password" name="sql_password" value="<?php p($_['sql_password']); ?>" /></p>

            <p><input type="submit" id="sqlVerify" value="<?php p($l -> t('Verify Settings')); ?>"></p>

        </fieldset>
        <fieldset id="sql-2">
            <p><label for="sql_table"><?php p($l -> t('Table')); ?></label><input type="text" id="sql_table" name="sql_table" value="<?php p($_['sql_table']); ?>" /></p>

            <p><label for="col_username"><?php p($l -> t('Username Column')); ?></label><input type="text" id="col_username" name="col_username" value="<?php p($_['col_username']); ?>" /></p>

            <p><label for="col_password"><?php p($l -> t('Password Column')); ?></label><input type="text" id="col_password" name="col_password" value="<?php p($_['col_password']); ?>" /></p>

            <p><label for="set_allow_pwchange"><?php p($l -> t('Allow password changing (read README!)')); ?></label><input type="checkbox" id="set_allow_pwchange" name="set_allow_pwchange" value="1"<?php
            if($_['set_allow_pwchange'])
                p(' checked');
 ?>><br>
 <em><?php p($l -> t('Allow changing passwords. Imposes a security risk if password salts are not recreated.')); ?></em></p>
 <em><?php p($l -> t('Only the encryption types "System","password_hash" and "Joomla2" are safe.')); ?></em></p>

            <p><label for="col_displayname"><?php p($l -> t('Real Name Column')); ?></label><input type="text" id="col_displayname" name="col_displayname" value="<?php p($_['col_displayname']); ?>" /></p>

            <p><label for="set_crypt_type"><?php p($l -> t('Encryption Type')); ?></label>
                <?php $crypt_types = array('drupal' => 'Drupal 7', 'md5' => 'MD5', 'md5crypt' => 'MD5 Crypt', 'cleartext' => 'Cleartext', 'mysql_encrypt' => 'mySQL ENCRYPT()', 'system' => 'System (crypt)', 'password_hash' => 'password_hash','mysql_password' => 'mySQL PASSWORD()', 'joomla' => 'Joomla MD5 Encryption', 'joomla2' => 'Joomla > 2.5.18 phpass', 'ssha256' => 'Salted SSHA256', 'redmine' => 'Redmine', 'sha1' => 'SHA1', 'courier_md5' => 'Courier base64-encoded MD5', 'courier_md5raw' => 'Courier hexadecimal MD5', 'courier_sha1' => 'Courier base64-encoded SHA1', 'courier_sha256' => 'Courier base64-encoded SHA256'); ?>
                <select id="set_crypt_type" name="set_crypt_type">
                    <?php
                        foreach ($crypt_types as $driver => $name):
                            //echo $_['set_crypt_type'];
                            if($_['set_crypt_type'] === $driver): ?>
                                <option selected="selected" value="<?php p($driver); ?>"><?php p($name); ?></option>
                            <?php else: ?>
                                <option value="<?php p($driver); ?>"><?php p($name); ?></option>
                            <?php endif;
                        endforeach;
                    ?>
                </select>
            </p>

            <p><label for="col_active"><?php p($l -> t('User Active Column')); ?></label><input type="text" id="col_active" name="col_active" value="<?php p($_['col_active']); ?>" /></p>

            <p><label for="set_active_invert"><?php p($l -> t('Invert Active Value')); ?></label><input type="checkbox" id="set_active_invert" name="set_active_invert" value="1"<?php
            if($_['set_active_invert'])
                p(' checked');
            ?> /><br>
            <em><?php p($l -> t("Invert the logic of the active column (for blocked users in the SQL DB)")); ?></em></p>

        </fieldset>

        <fieldset id="sql-3">

           <p><label for="col_email"><?php p($l -> t('E-Mail Column')); ?></label><input type="text" id="col_email" name="col_email" value="<?php p($_['col_email']); ?>" /></p>

            <p><label for="set_mail_sync_mode"><?php p($l -> t('E-Mail address sync mode')); ?></label>
                <?php $mail_modes = array('none' => 'No Synchronisation', 'initial' => 'Synchronise only once', 'forceoc' => 'Nextcloud always wins', 'forcesql' => 'SQL always wins'); ?>
                <select id="set_mail_sync_mode" name="set_mail_sync_mode">
                    <?php
                    foreach ($mail_modes as $mode => $name):
                        //echo $_['set_mail_sync_mode'];
                        if($_['set_mail_sync_mode'] === $mode): ?>
                            <option selected="selected" value="<?php p($mode); ?>"><?php p($name); ?></option>
                        <?php else: ?>
                            <option value="<?php p($mode); ?>"><?php p($name); ?></option>
                        <?php endif;
                    endforeach;
                    ?>
                </select>
            </p>

        </fieldset>

        <fieldset id="sql-4">

            <p><label for="set_default_domain"><?php p($l -> t('Append Default Domain')); ?></label><input type="text" id="set_default_domain", name="set_default_domain" value="<?php p($_['set_default_domain']); ?>" /><br>
                <em><?php p($l -> t('Append this string, e.g. a domain name, to each user name. The @-sign is automatically inserted.')); ?></em>
            </p>

            <p><label for="set_strip_domain"><?php p($l -> t('Strip Domain Part from Username')); ?></label><input type="checkbox" id="set_strip_domain" name="set_strip_domain" value="1"<?php
            if($_['set_strip_domain'])
                p(' checked');
            ?> /><br>
            <em><?php p($l -> t("Strip Domain Part including @-sign from Username when logging in and retrieving username lists")); ?></em></p>

        </fieldset>

        <fieldset id="sql-5">
           <p><label for="set_enable_gethome"><?php p($l -> t('Enable support for getHome()')); ?></label><input type="checkbox" id="set_enable_gethome", name="set_enable_gethome" value="1" <?php
            if($_['set_enable_gethome'])
                p(' checked');
            ?>/></p>

            <p><label for="set_gethome_mode"><?php p($l -> t('Method for getHome')); ?></label>
                <?php $gethome_modes = array('query' => 'SQL Column', 'static' => 'Static (with Variables)'); ?>
                <select id="set_gethome_mode" name="set_gethome_mode">
                    <?php
                    foreach ($gethome_modes as $mode => $name):
                        //echo $_['set_mail_sync_mode'];
                        if($_['set_gethome_mode'] === $mode): ?>
                            <option selected="selected" value="<?php p($mode); ?>"><?php p($name); ?></option>
                        <?php else: ?>
                            <option value="<?php p($mode); ?>"><?php p($name); ?></option>
                        <?php endif;
                    endforeach;
                    ?>
                </select>
            </p>

            <p><label for="col_gethome"><?php p($l -> t('Home Column')); ?></label><input type="text" id="col_gethome" name="col_gethome" value="<?php p($_['col_gethome']); ?>"></p>

            <p><label for="set_gethome"><?php p($l -> t('Home Dir')); ?></label><input type="text" id="set_gethome" name="set_gethome" value="<?php p($_['set_gethome']); ?>"><br>
            <em><?php p($l -> t('You can use the placeholders %%u to specify the user ID (before appending the default domain), %%ud to specify the user ID (after appending the default domain) and %%d to specify the default domain')); ?></em></p>

        </fieldset>
        <fieldset id="sql-6">
            <p><label for="sql_group_table"><?php p($l -> t('Table')); ?></label><input type="text" id="sql_group_table" name="sql_group_table" value="<?php p($_['sql_group_table']); ?>" /></p>

            <p><label for="col_group_username"><?php p($l -> t('Username Column')); ?></label><input type="text" id="col_group_username" name="col_group_username" value="<?php p($_['col_group_username']); ?>" /></p>

            <p><label for="col_group_name"><?php p($l -> t('Group Name Column')); ?></label><input type="text" id="col_group_name" name="col_group_name" value="<?php p($_['col_group_name']); ?>" /></p>

        </fieldset>

        <input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']); ?>" id="requesttoken" />
        <input type="hidden" name="appname" value="user_sql" />
        <input id="sqlSubmit" type="submit" value="<?php p($l -> t('Save')); ?>" />
        <div id="sql_update_message" class="statusmessage"><?php p($l -> t('Saving...')); ?></div>
        <div id="sql_loading_message" class="statusmessage"><?php p($l -> t('Loading...')); ?></div>
        <div id="sql_verify_message" class="statusmessage"><?php p($l -> t('Verifying...')); ?></div>
        <div id="sql_error_message" class="errormessage"></div>
        <div id="sql_success_message" class="successmessage"></div>
    </div>
</form>
</div>
