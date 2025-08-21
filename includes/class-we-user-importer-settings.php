<?php

if (!defined('ABSPATH')) exit;

/**
 * Register all actions and filters for the plugin
 *
 * @link       https://google.com
 * @since      1.0.0
 *
 * @package    We_User_Importer
 * @subpackage We_User_Importer/includes
 */

class We_User_Importer_Settings
{
    private $table_name;

    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . WE_USER_IMPORTER_SETTINGS;
    }

    public function create_tables()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $this->table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            setting_key varchar(100) NOT NULL,
            setting_value longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }


    public function save_setting($key, $value)
    {
        global $wpdb;

        if (str_contains($key, 'password')) {
            $encryption_key = WE_ENCRYPTE_KEY;
            $iv = random_bytes(16);
            $encrypted = openssl_encrypt(
                $value,
                'AES-256-CBC',
                $encryption_key,
                0,
                $iv
            );
            $value = base64_encode($iv . $encrypted);
        }

        if (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        $result = $wpdb->replace(
            $this->table_name,
            [
                'setting_key' => sanitize_text_field($key),
                'setting_value' => wp_kses_post($value)
            ],
            ['%s', '%s']
        );

        return $result !== false;
    }

    public function get_setting($key, $default = null)
    {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT setting_value FROM {$this->table_name} WHERE setting_key = %s",
            $key
        );

        $value = $wpdb->get_var($query);

        if ($value === null) {
            return $default;
        }

        $json_value = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $json_value;
        }

        if (str_contains($key, 'password')) {
            $data = base64_decode($value);
            $iv = substr($data, 0, 16);
            $encrypted = substr($data, 16);
            $encryption_key = WE_ENCRYPTE_KEY;
            $decrypted = openssl_decrypt(
                $encrypted,
                'AES-256-CBC',
                $encryption_key,
                0,
                $iv
            );
            $value = $decrypted;
        }

        return $value;
    }

    public function delete_setting($key)
    {
        global $wpdb;

        return $wpdb->delete(
            $this->table_name,
            ['setting_key' => $key],
            ['%s']
        );
    }

    public function get_all_settings()
    {
        global $wpdb;

        $results = $wpdb->get_results("SELECT setting_key, setting_value FROM {$this->table_name}", ARRAY_A);

        $settings = [];
        foreach ($results as $row) {
            $value = $row['setting_value'];
            $json_value = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $settings[$row['setting_key']] = $json_value;
            } else {
                $settings[$row['setting_key']] = $value;
            }
        }

        return $settings;
    }

    public function drop_tables()
    {
        global $wpdb;
        $sql = "DROP TABLE IF EXISTS $this->table_name";
        $wpdb->query($sql);
    }
}

new We_User_Importer_Settings();
