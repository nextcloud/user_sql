<?php
/**
 * Nextcloud - user_sql
 *
 * @copyright 2020 Marcin Łojewski <dev@mlojewski.me>
 * @author    Marcin Łojewski <dev@mlojewski.me>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace OCA\UserSQL\Constant;

/**
 * The database properties.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
final class DB
{
    const DATABASE = "db.database";
    const DRIVER = "db.driver";
    const HOSTNAME = "db.hostname";
    const PASSWORD = "db.password";
    const SSL_CA = "db.ssl_ca";
    const SSL_CERT = "db.ssl_cert";
    const SSL_KEY = "db.ssl_key";
    const USERNAME = "db.username";

    const GROUP_TABLE = "db.table.group";
    const USER_GROUP_TABLE = "db.table.user_group";
    const USER_TABLE = "db.table.user";

    const GROUP_ADMIN_COLUMN = "db.table.group.column.admin";
    const GROUP_GID_COLUMN = "db.table.group.column.gid";
    const GROUP_NAME_COLUMN = "db.table.group.column.name";

    const USER_GROUP_GID_COLUMN = "db.table.user_group.column.gid";
    const USER_GROUP_UID_COLUMN = "db.table.user_group.column.uid";

    const USER_ACTIVE_COLUMN = "db.table.user.column.active";
    const USER_AVATAR_COLUMN = "db.table.user.column.avatar";
    const USER_DISABLED_COLUMN = "db.table.user.column.disabled";
    const USER_EMAIL_COLUMN = "db.table.user.column.email";
    const USER_HOME_COLUMN = "db.table.user.column.home";
    const USER_NAME_COLUMN = "db.table.user.column.name";
    const USER_PASSWORD_COLUMN = "db.table.user.column.password";
    const USER_QUOTA_COLUMN = "db.table.user.column.quota";
    const USER_SALT_COLUMN = "db.table.user.column.salt";
    const USER_UID_COLUMN = "db.table.user.column.uid";
    const USER_USERNAME_COLUMN = "db.table.user.column.username";
}
