<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://google.com
 * @since      1.0.0
 *
 * @package    We_User_Importer
 * @subpackage We_User_Importer/admin
 */

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    We_User_Importer
 * @subpackage We_User_Importer/admin
 * @author     AlirezaYaghouti <webelitee@gmail.com>
 */
class We_User_Importer_Admin
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in We_User_Importer_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The We_User_Importer_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . "css/we-user-importer-admin.css",
            [],
            $this->version,
            "all"
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in We_User_Importer_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The We_User_Importer_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . "js/we-user-importer-admin.js",
            ["jquery"],
            $this->version,
            false
        );
    }

    /**
     * Register the admin menu.
     *
     * @since    1.0.0
     */
    public function admin_menu()
    {
        add_menu_page(
            WE_USER_IMPORTER_NAME,
            WE_USER_IMPORTER_NAME,
            "manage_options",
            WE_USER_IMPORTER_SLUG,
            [$this, "admin_menu_content"],
            "dashicons-upload",
            76
        );
    }

    /**
     * fallback function for admin menu content.
     *
     * @since    1.0.0
     */
    public function admin_menu_content()
    {
        include_once plugin_dir_path(__FILE__) .
            "/partials/we-user-importer-admin-display.php";
    }

    /**
     * Admin notice about how can import users from csv file.
     *
     * @since    1.0.0
     */
    public function admin_notice_how_to_import()
    {
        $class = "notice notice-warn";
        $message = __(
            "The first column is the username, the second column is the email, the third column is the name, the fourth column is the last name, and the fifth column is the user's password",
            WE_USER_IMPORTER_SLUG
        );

        printf(
            '<div class="%1$s"><p>%2$s</p></div>',
            esc_attr($class),
            esc_html($message)
        );

        $class = "notice notice-warn";
        $message = __(
            "The only required field is the username.",
            WE_USER_IMPORTER_SLUG
        );

        printf(
            '<div class="%1$s"><p>%2$s</p></div>',
            esc_attr($class),
            esc_html($message)
        );
    }


    function we_export_excel_headers() {}
}
