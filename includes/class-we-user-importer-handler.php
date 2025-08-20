<?php

if (!defined('ABSPATH')) exit;

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
        "description"  => "شارژ حساب به صورت دستی برای خریداران حضوری",
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

    $sms = new We_SMS_Manager('09909063778', 'EZQTS');
    $user = get_user_by('id', $user_id);
    $sms->sendPatternSMS($add_balance, $user->username, 228529);

    return true;
}

/**
 * Convert days to expiration or Jalali date to Unix timestamp
 *
 * @param mixed $input Integer days or Jalali date string (format: YYYY/MM/DD)
 * @return int Unix timestamp
 */
function convert_to_timestamp($input) {
    if (is_int($input)) {
        // Input is number of days - calculate future timestamp
        return time() + ($input * 24 * 60 * 60);
    } elseif (is_string($input) && preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $input)) {
        // Input is Jalali date - convert to Gregorian then to timestamp
        return jalali_to_timestamp($input);
    }
    
    // Invalid input - return current timestamp as fallback
    return time();
}

/**
 * Convert Jalali (Shamsi) date to Unix timestamp
 * Uses algorithm for conversion without external dependencies
 *
 * @param string $jalali_date Date in format YYYY/MM/DD
 * @return int Unix timestamp
 */
function jalali_to_timestamp($jalali_date) {
    // Split Jalali date into components
    list($j_year, $j_month, $j_day) = explode('/', $jalali_date);
    
    // Convert Jalali to Gregorian
    $g_date = jalali_to_gregorian($j_year, $j_month, $j_day);
    
    // Create DateTime object from Gregorian date
    $datetime = DateTime::createFromFormat('Y-m-d', sprintf('%d-%d-%d', $g_date[0], $g_date[1], $g_date[2]));
    
    // Return Unix timestamp
    return $datetime->getTimestamp();
}

/**
 * Convert Jalali (Solar Hijri) date to Gregorian date
 * Based on algorithm from https://jdf.scr.ir/
 * 
 * @param int $j_y Jalali year
 * @param int $j_m Jalali month
 * @param int $j_d Jalali day
 * @return array Gregorian date as [year, month, day]
 */
function jalali_to_gregorian($j_y, $j_m, $j_d) {
    $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
    
    $jy = $j_y - 979;
    $jm = $j_m - 1;
    $jd = $j_d - 1;
    
    $j_day_no = 365 * $jy + floor($jy / 33) * 8 + floor(($jy % 33 + 3) / 4);
    
    for ($i = 0; $i < $jm; ++$i) {
        $j_day_no += $j_days_in_month[$i];
    }
    
    $j_day_no += $jd;
    
    $g_day_no = $j_day_no + 79;
    
    $gy = 1600 + 400 * floor($g_day_no / 146097);
    $g_day_no = $g_day_no % 146097;
    
    $leap = true;
    if ($g_day_no >= 36525) {
        $g_day_no--;
        $gy += 100 * floor($g_day_no / 36524);
        $g_day_no = $g_day_no % 36524;
        
        if ($g_day_no >= 365) {
            $g_day_no++;
        } else {
            $leap = false;
        }
    }
    
    $gy += 4 * floor($g_day_no / 1461);
    $g_day_no %= 1461;
    
    if ($g_day_no >= 366) {
        $leap = false;
        $g_day_no--;
        $gy += floor($g_day_no / 365);
        $g_day_no = $g_day_no % 365;
    }
    
    for ($i = 0; $i < 11 && $g_day_no >= $g_days_in_month[$i] + ($i == 1 && $leap); $i++) {
        $g_day_no -= $g_days_in_month[$i] + ($i == 1 && $leap);
    }
    
    $gm = $i + 1;
    $gd = $g_day_no + 1;
    
    return array($gy, $gm, $gd);
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
 * @param  array $file
 * @param  bool $continue_if_exists
 * @param  bool $not_only_wallet_first_time
 * @return array
 */
function process_csv_file(array $file, bool $continue_if_exists = false, bool $not_only_wallet_first_time = false, int $expire_date = 0): array
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
            $wallet_expire_date = trim($data[9] ?? $expire_date);
            $expire_timestamp = convert_to_timestamp(20);
            $wallet_timestamp = convert_to_timestamp('1404/04/25');
            $charge             = ($percent_charge > 0) ? (($percent_charge / 100) * $amount) : $fixed_charge;
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

            if (update_user_wc_address($user_id, $state, $city)) {
                $log_messages[] = "✅ آدرس کاربر $username به روز شد.";
            } else {
                $log_messages[] = "❌ خطا در به‌روزرسانی آدرس کاربر $username.";
            }
        }
    } catch (\Throwable $th) {
        $log_messages[] = "❌ خطا - " . $th->getMessage();
    }

    return $log_messages;
}
