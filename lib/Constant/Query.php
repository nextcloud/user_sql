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
 * The database query constants.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
final class Query
{
    const BELONGS_TO_ADMIN = "belongs_to_admin";
    const COUNT_GROUPS = "count_groups";
    const COUNT_USERS = "count_users";
    const FIND_GROUP = "find_group";
    const FIND_GROUP_UIDS = "find_group_uids";
    const FIND_GROUP_USERS = "find_group_users";
    const FIND_GROUPS = "find_groups";
    const FIND_USER_BY_UID = "find_user_by_uid";
    const FIND_USER_BY_USERNAME = "find_user_by_username";
    const FIND_USER_BY_USERNAME_CASE_INSENSITIVE = "find_user_by_username_case_insensitive";
    const FIND_USER_BY_USERNAME_OR_EMAIL = "find_user_by_username_or_email";
    const FIND_USER_BY_USERNAME_OR_EMAIL_CASE_INSENSITIVE = "find_user_by_username_or_email_case_insensitive";
    const FIND_USER_GROUPS = "find_user_groups";
    const FIND_USERS = "find_users";
    const UPDATE_DISPLAY_NAME = "update_display_name";
    const UPDATE_EMAIL = "update_email";
    const UPDATE_PASSWORD = "update_password";
    const UPDATE_QUOTA = "update_quota";

    const EMAIL_PARAM = "email";
    const GID_PARAM = "gid";
    const NAME_PARAM = "name";
    const PASSWORD_PARAM = "password";
    const QUOTA_PARAM = "quota";
    const SEARCH_PARAM = "search";
    const UID_PARAM = "uid";
    const USERNAME_PARAM = "username";
}
