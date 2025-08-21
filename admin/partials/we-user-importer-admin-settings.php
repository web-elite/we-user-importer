<div id="<?php echo esc_attr(WE_USER_IMPORTER_SLUG); ?>">
    <?php
    $setting = new We_User_Importer_Settings();

    if (isset($_POST["submit_settings"])) {
        $setting->save_setting('sms_gateway', $_POST["sms_gateway"]);
        $setting->save_setting('sms_username', $_POST["sms_username"]);
        $setting->save_setting('sms_password', $_POST["sms_password"]);
        $setting->save_setting('sms_pattern', $_POST["sms_pattern"]);
        echo '<div class="info-box">✅ تنظیمات با موفقیت ذخیره شد.</div>';
    }

    $sms_username = $setting->get_setting('sms_username');
    $sms_password = $setting->get_setting('sms_password');
    $sms_pattern = $setting->get_setting('sms_pattern');
    ?>
    <form method="post">
        <div class="info-box">
            <h3>تنظیمات پنل پیامک</h3>
            <div class="form-group">
                <label for="sms_gateway">پنل پیامک</label>
                <select name="sms_gateway" id="sms_gateway">
                    <option value="melipayamak" selected>ملی پیامک</option>
                </select>
            </div>
            <div class="form-group">
                <label for="sms_username">نام کاربری</label>
                <input type="text" name="sms_username" value="<?= $sms_username ?? '' ?>" id="sms_username">
            </div>
            <div class="form-group">
                <label for="sms_password">رمزعبور</label>
                <input type="text" name="sms_password" value="<?= $sms_password ?? '' ?>" id="sms_password">
            </div>
            <div class="form-group">
                <label for="sms_pattern">کد پترن</label>
                <input type="text" name="sms_pattern" value="<?= $sms_pattern ?? '' ?>" id="sms_pattern">
            </div>
        </div>
        <div class="form-group">
            <input type="submit" name="submit_settings" value="ذخیره" class="button-primary">
        </div>
    </form>

    <?php


    ?>
</div>