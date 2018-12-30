<?php
/**
 * Nextcloud - user_sql
 *
 * @copyright 2018 Marcin Łojewski <dev@mlojewski.me>
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
 * The option properties names.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
final class Opt
{
    const APPEND_SALT = "opt.append_salt";
    const CASE_INSENSITIVE_USERNAME = "opt.case_insensitive_username";
    const CRYPTO_CLASS = "opt.crypto_class";
    const CRYPTO_PARAM_0 = "opt.crypto_param_0";
    const CRYPTO_PARAM_1 = "opt.crypto_param_1";
    const CRYPTO_PARAM_2 = "opt.crypto_param_2";
    const EMAIL_SYNC = "opt.email_sync";
    const HOME_LOCATION = "opt.home_location";
    const HOME_MODE = "opt.home_mode";
    const NAME_CHANGE = "opt.name_change";
    const PASSWORD_CHANGE = "opt.password_change";
    const PREPEND_SALT = "opt.prepend_salt";
    const PROVIDE_AVATAR = "opt.provide_avatar";
    const QUOTA_SYNC = "opt.quota_sync";
    const REVERSE_ACTIVE = "opt.reverse_active";
    const USE_CACHE = "opt.use_cache";
}
