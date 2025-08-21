<?php

/**
 * Converts a Shamsi (Jalali) date string to a Unix timestamp.
 * * @param string $shamsi_date_str The Shamsi date string in 'YYYY/MM/DD' format.
 * @return int|false A Unix timestamp on success, or false on failure.
 */
function shamsi_to_timestamp(string $shamsi_date_str): int|false
{
    if (empty($shamsi_date_str)) {
        return false;
    }

    $parts = explode('/', $shamsi_date_str);
    if (count($parts) !== 3) {
        return false;
    }

    $year = intval($parts[0]);
    $month = intval($parts[1]);
    $day = intval($parts[2]);

    // Check for valid Shamsi date
    if ($year < 1300 || $month < 1 || $month > 12 || $day < 1 || $day > 31) {
        return false;
    }

    // Convert to Gregorian date using a simple algorithm without external libraries
    $shamsi_days_in_month = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
    $gregorian_start_timestamp = 946684800; // 2000-01-01 00:00:00 UTC for calculation
    $gregorian_start_shamsi = [1378, 10, 11]; // Shamsi equivalent of 2000-01-01

    $diff_days = 0;

    // Calculate total days from Gregorian start date
    for ($y = $gregorian_start_shamsi[0]; $y < $year; $y++) {
        $is_leap = (((($y - 1343) % 33) % 4) == 0 && (($y - 1343) % 33) != 1);
        $diff_days += 365 + ($is_leap ? 1 : 0);
    }

    for ($m = 1; $m < $month; $m++) {
        $diff_days += $shamsi_days_in_month[$m - 1];
    }
    $diff_days += $day - $gregorian_start_shamsi[2];

    $timestamp = $gregorian_start_timestamp + ($diff_days * 86400);

    return $timestamp;
}

/**
 * Converts a Unix timestamp to a Shamsi (Jalali) date string.
 *
 * @param int $timestamp The Unix timestamp.
 * @return string The Shamsi date string in 'YYYY/MM/DD' format.
 */
function timestamp_to_shamsi(int $timestamp): string
{
    $gregorian_date = date('Y-m-d', $timestamp);
    $parts = explode('-', $gregorian_date);
    $year = intval($parts[0]);
    $month = intval($parts[1]);
    $day = intval($parts[2]);

    // Simple conversion without external libraries
    $shamsi_months_days = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
    $shamsi_year_start = 1379;

    // This is a simplified conversion and might not be perfectly accurate for all dates.
    // A full algorithm without a library is complex.
    // This is a basic approach for demonstration.

    $days_since_start = round(($timestamp - strtotime('1999-03-20')) / 86400); // approx start of 1378

    $shamsi_year = $shamsi_year_start + floor($days_since_start / 365.25);
    $day_of_year = $days_since_start % 365;

    $shamsi_month = 1;
    for ($i = 0; $i < 12; $i++) {
        if ($day_of_year <= $shamsi_months_days[$i]) {
            $shamsi_month = $i + 1;
            break;
        }
        $day_of_year -= $shamsi_months_days[$i];
    }

    $shamsi_day = $day_of_year;

    return sprintf("%04d/%02d/%02d", $shamsi_year, $shamsi_month, $shamsi_day);
}

/**
 * Check if a value is not empty (filled)
 * 
 * This function checks if a value exists and is not empty. It considers:
 * - null values as empty
 * - empty arrays as empty
 * - empty strings as empty
 * - strings containing only whitespace as empty
 * - zero values as NOT empty (different from empty())
 *
 * @param mixed $input The value to check
 * @return bool True if the value is filled, false otherwise
 */
function filled($input): bool
{
    if (is_null($input)) {
        return false;
    }
    
    if (is_string($input)) {
        $input = trim($input);
        return $input !== '';
    }
    
    if (is_array($input)) {
        return count($input) > 0;
    }
    
    // For other types (numbers, objects, resources, etc.)
    return !empty($input);
}

/**
 * update_user_wc_address
 *
 * @param  mixed $user_id
 * @param  mixed $state
 * @param  mixed $city
 * @return bool
 */
function update_user_wc_address($user_id, $state, $city): bool
{
    if (empty($user_id) || empty($state) || empty($city)) return false;

    update_user_meta($user_id, 'billing_state', $state);
    update_user_meta($user_id, 'billing_city', $city);
    update_user_meta($user_id, 'shipping_state', $state);
    update_user_meta($user_id, 'shipping_city', $city);

    return true;
}

/**
 * add_wallet_balance
 *
 * @param  mixed $user_id
 * @param  mixed $add_balance
 * @param  mixed $expire_expire_timestampdate
 * @return bool
 */
function add_wallet_balance($user_id, $add_balance, $expire_timestamp = null): bool
{
    global $wpdb;

    $existing_balance = get_user_meta($user_id, 'nirweb_wallet_balance', true);
    $new_balance = $existing_balance ? $existing_balance + $add_balance : $add_balance;

    update_user_meta($user_id, 'nirweb_wallet_balance', $new_balance);

    $wpdb->insert($wpdb->prefix . "nirweb_wallet_op", [
        "user_id"      => $user_id,
        "user_created" => 0,
        "amount"       => $new_balance,
        "description"  => "شارژ حساب برای خریداران حضوری",
        "type_op"      => "credit",
        "type_v"       => "register",
        "created"      => current_time("mysql"),
    ]);

    $wpdb->insert($wpdb->prefix . "nirweb_wallet_cashback", [
        "user_id"      => $user_id,
        "order_id"     => 0,
        "amount"       => $add_balance,
        "expire_time"  => $expire_timestamp ?? null,
        "start_time"   => null,
        "status_start" => null,
    ]);

    return true;
}

/**
 * user_has_ever_been_charged
 *
 * @param  mixed $user_id
 * @return bool
 */
function user_has_ever_been_charged($user_id): bool
{
    global $wpdb;
    $has_been_charged = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}nirweb_wallet_op WHERE user_id = %d AND type_op = %s",
            $user_id,
            'credit'
        )
    );

    return $has_been_charged > 0 ? true : false;
}

/**
 * process_csv_file
 *
 * @param  mixed $file
 * @param  mixed $continue_if_exists
 * @param  mixed $not_only_wallet_first_time
 * @param  mixed $expire_days
 * @param  mixed $min_charge
 * @return array
 */
function process_csv_file(array $file, bool $continue_if_exists = false, bool $not_only_wallet_first_time = false, int $expire_days = 0, int $min_charge = 0): array
{
    $log_messages = [];

    if (!in_array($file["type"], ['text/csv'])) {
        $log_messages[] = "❌ لطفاً یک فایل CSV معتبر انتخاب کنید.";
        return $log_messages;
    }

    try {
        $handle = fopen($file["tmp_name"], "r");
        fgetcsv($handle);

        while (($data = fgetcsv($handle)) !== false) {
            $username           = trim($data[0] ?? '');
            $amount             = floatval(trim($data[1] ?? ''));
            $percent_charge     = floatval(trim($data[2] ?? ''));
            $fixed_charge       = floatval(trim($data[3] ?? ''));
            $first_name         = trim($data[4] ?? '');
            $last_name          = trim($data[5] ?? '');
            $state              = trim($data[6] ?? '');
            $city               = trim($data[7] ?? '');
            $buy_date           = trim($data[8] ?? '');
            $expire_date_shamsi = trim($data[9] ?? '');
            $charge             = ($percent_charge > 0) ? (($percent_charge / 100) * $amount) : $fixed_charge;
            $charge             = $charge > 1 ? $charge : $min_charge;

            $wallet_timestamp = 0;
            $persian_expire_date = '';

            if (!empty($expire_date_shamsi)) {
                $wallet_timestamp = shamsi_to_timestamp($expire_date_shamsi);
                $persian_expire_date = $expire_date_shamsi;
            } else {
                $expire_days = intval($expire_days);
                if ($expire_days > 0) {
                    $wallet_timestamp = time() + ($expire_days * 86400);
                    $persian_expire_date = timestamp_to_shamsi($wallet_timestamp);
                } else {
                    $wallet_timestamp = 0;
                    $persian_expire_date = '';
                }
            }

            if (empty($username)) {
                $log_messages[] = "⛔ نام کاربری خالی است.";
                continue;
            }

            $user = get_user_by('login', $username);

            if ($user) {
                $user_id = $user->ID;
                $log_messages[] = "🔁 کاربر $username از قبل وجود داشت (ID: $user_id).";

                if (!$continue_if_exists) {
                    $log_messages[] = "⏩ حساب این کاربر شارژ نمیشود.";
                    continue;
                }
            } else {
                $password = wp_generate_password(12, false);
                $email = $username . '@' . parse_url(get_site_url(), PHP_URL_HOST) . '.com';

                $user_id = wp_create_user($username, $password, $email);
                if (is_wp_error($user_id)) {
                    $log_messages[] = "❌ خطا در ایجاد کاربر $username: " . $user_id->get_error_message();
                    continue;
                }
                $log_messages[] = "✅ کاربر $username ایجاد شد (ID: $user_id)";
            }

            wp_update_user([
                'ID'         => $user_id,
                'first_name' => $first_name,
                'last_name'  => $last_name,
            ]);

            if (user_has_ever_been_charged($user_id) && $not_only_wallet_first_time) {
                if (add_wallet_balance($user_id, $charge, $wallet_timestamp)) {
                    $log_messages[] = "✅ شارژ کیف پول کاربر $username به مبلغ $charge انجام شد.";
                } else {
                    $log_messages[] = "❌ خطا در شارژ کیف پول $username.";
                }
            }

            if (filled($state) && filled($city)) {
                if (update_user_wc_address($user_id, $state, $city)) {
                    $log_messages[] = "✅ آدرس کاربر $username به روز شد.";
                } else {
                    $log_messages[] = "❌ خطا در به‌روزرسانی آدرس کاربر $username.";
                }
            } else {
                $log_messages[] = "به دلیل خالی بودن فیلد شهر یا استان ";
            }

            $log_messages[] = "Wallet time stamp: $wallet_timestamp | date: $persian_expire_date";
            $log_messages[] = "charge is : $charge | buy date: $buy_date";

            // $sms = new We_SMS_Manager();
            // $wallet_expire_date = timestamp_to_shamsi($wallet_timestamp);
            // if ($sms->sendPatternSMS(" $buy_date;$charge;$persian_expire_date", $username)) {
            //     $log_messages[] = "✅ پیامک به شماره $username ارسال شد.";
            // } else {
            //     $log_messages[] = "❌ پیامک به شماره $username ارسال نشد.";
            // }
        }
    } catch (\Throwable $th) {
        $log_messages[] = "❌ خطا - " . $th->getMessage();
    }

    return $log_messages;
}
