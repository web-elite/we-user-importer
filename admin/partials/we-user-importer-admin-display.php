<?php
$min_charge_value = '';
if (isset($_SESSION['form_data']['min_charge'])) {
    $min_charge_value = $_SESSION['form_data']['min_charge'];
}
$expire_date_value = '';
if (isset($_SESSION['form_data']['expire_date'])) {
    $expire_date_value = $_SESSION['form_data']['expire_date'];
}
$not_only_wallet_first_time_value = '';
if (isset($_SESSION['form_data']['not_only_wallet_first_time'])) {
    $not_only_wallet_first_time_value = $_SESSION['form_data']['not_only_wallet_first_time'];
}
$continue_if_exists_value = '';
if (isset($_SESSION['form_data']['continue_if_exists'])) {
    $continue_if_exists_value = $_SESSION['form_data']['continue_if_exists'];
}

?>

<div id="<?php echo esc_attr(WE_USER_IMPORTER_SLUG); ?>">
    <div class="info-box">
        <h3>📄 اطلاعات ستون‌ها:</h3>
        <p>
            ستون اول <span class="highlight">شماره موبایل</span> است،<br>
            ستون دوم <span class="highlight">مبلغ خرید قبلی</span>،<br>
            ستون سوم <span class="highlight">درصد شارژ</span>،<br>
            ستون چهارم <span class="highlight">مبلغ شارژ</span>،<br>
            ستون پنجم <span class="highlight">نام</span>،<br>
            ستون ششم <span class="highlight">نام خانوادگی</span>،<br>
            ستون هفتم <span class="highlight">استان</span>،<br>
            ستون هشتم <span class="highlight">شهر</span>،<br>
            ستون نهم <span class="highlight">تاریخ خرید قبلی</span>،<br>
            ستون دهم <span class="highlight">تاریخ انقضا</span>،<br>
            ℹ️ تنها ستون اول <span class="highlight">نام کاربری الزامی میباشد</span>، این ستون میتواند شماره تماس کاربر یا حروف انگلیسی تصادفی باشد.<br>
            ℹ️ مبلغ‌ها باید به تومن باشد..<br>
            ℹ️ درصورتی که میخواهید شارژ حساب کاربر، به صورت درصدی و براساس مبلغ خرید قبلی باشد از فیلد درصد شارژ استفاده کنید..<br>
            ℹ️ ستون تاریخ ها با فرمت YYYY/MM/DD برای مثال 1404/05/24
        </p>
        <a id="downloadCsv" href="https://docs.google.com/spreadsheets/d/1QR-TApGnGa3V6FeziQcueVWWuqdQSqEz/export?ouid=113885461555545291327&format=csv" class="btn" target="_blank" rel="noopener noreferrer">
            <svg width="14px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                <path d="M256 32c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 210.7-41.4-41.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l96 96c12.5 12.5 32.8 12.5 45.3 0l96-96c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 242.7 256 32zM64 320c-35.3 0-64 28.7-64 64l0 32c0 35.3 28.7 64 64 64l320 0c35.3 0 64-28.7 64-64l0-32c0-35.3-28.7-64-64-64l-46.9 0-56.6 56.6c-31.2 31.2-81.9 31.2-113.1 0L110.9 320 64 320zm304 56a24 24 0 1 1 0 48 24 24 0 1 1 0-48z" />
            </svg> دانلود فایل نمونه
        </a>
    </div>
    <form method="post" enctype="multipart/form-data">
        <div class="info-box">
            <h3>افزودن کاربران از فایل اکسل</h3>
            <div class="form-group">
                <label for="csv_file">انتخاب فایل CSV:</label>
                <input type="file" name="csv_file" id="csv_file" required>
            </div>
            <div class="form-group">
                <input type="checkbox" value="<?php echo esc_attr($continue_if_exists_value); ?>" name="if_user_exist_continue" id="if_user_exist_continue">
                <label for="if_user_exist_continue">اگر کاربر از قبل وجود داشت، حسابش شارژ شود؟</label>
            </div>
            <div class="form-group">
                <input type="checkbox" value="<?php echo esc_attr($not_only_wallet_first_time_value); ?>" name="not_only_wallet_first_time" id="not_only_wallet_first_time">
                <label for="not_only_wallet_first_time">کیف پول کسانی که سابقه شارژ شدن داشتند هم شارژ شود؟</label>
            </div>
            <div class="form-group">
                <label for="min_charge">حداقل مبلغ شارژ: </label>
                <input type="number" value="<?php echo esc_attr($min_charge_value); ?>" name="min_charge" id="min_charge" min="0" step="50000">
                <label for="min_charge">تومان </label>
            </div>
        </div>
        <div class="info-box">
            <h3>تاریخ انقضا</h3>
            <p class="input-desc">ℹ️ درصورتی که میخواهید هیچ محدودیتی در استفاده کاربر از کیف پولش وجود نداشته باشد این فیلد را خالی بگذارید.</p>
            <div class="form-group">
                <label for="expire_date">تاریخ انقضا</label>
                <input type="number" value="<?php echo esc_attr($expire_date_value); ?>" name="expire_date" id="expire_date">
                <label for="expire_date">روز</label>
                <p class="input-desc">ℹ️ برای مثال اگر عدد 9 وارد کنید، مهلت استفاده کاربران از کیف پولشان 9 روز پس از شارژ شدن کیف پولشان میباشد.</p>
                <p class="input-desc">⚠️ توجه داشته باشید این فیلد زمانی تاثیر گذار میباشد که داخل اکسل، ستون تاریخ انقضا کاربر خالی باشد.</p>
            </div>
        </div>
        <div class="form-group">
            <input type="submit" name="submit_csv" value="بارگذاری فایل" class="button-primary">
        </div>
    </form>

    <?php
    if (isset($_POST["submit_csv"]) && isset($_FILES["csv_file"])) {
        session_start();
        $_SESSION['form_data'] = [
            'continue_if_exists' => $continue_if_exists,
            'not_only_wallet_first_time' => $not_only_wallet_first_time,
            'min_charge' => $min_charge,
            'expire_date' => $expire_date
        ];

        $continue_if_exists = isset($_POST["if_user_exist_continue"]);
        $not_only_wallet_first_time = isset($_POST["not_only_wallet_first_time"]);
        $min_charge = isset($_POST["min_charge"]);
        $expire_date = isset($_POST["expire_date"]) ? $_POST["expire_date"] : 0;
        $logs = process_csv_file($_FILES["csv_file"], $continue_if_exists, $not_only_wallet_first_time, $expire_date, $min_charge);

        if (!empty($logs)) {
            echo '<div class="info-box"><h3>📝 گزارشات:</h3><ul>';
            foreach ($logs as $log) {
                echo '<li>' . esc_html($log) . '</li>';
            }
            echo '</ul></div>';
        }
    }
    ?>
</div>