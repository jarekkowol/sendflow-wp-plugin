<?php

class SendflowAdmin
{
    const OPTION_NAME = 'sendflow_data';
    const NONCE = 'sendflow_admin';

    public static function init()
    {
        add_action('admin_menu', array(__CLASS__, 'addAdminMenu'));
        add_action('wp_ajax_store_admin_data', array(__CLASS__, 'storeAdminData'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'addAdminScripts'));
    }

    /**
     * Returns the saved options data as an array
     *
     * @return array
     */
    private static function getData()
    {
        return get_option(self::OPTION_NAME, array());
    }

    /**
     * Callback for the Ajax request
     *
     * Updates the options data
     *
     * @return void
     */
    public function storeAdminData()
    {
        if (wp_verify_nonce($_POST['security'], self::NONCE) === false) {
            die('Invalid Request! Reload your page please.');
        }
        $data = self::getData();
        foreach ($_POST as $field => $value) {
            if (substr($field, 0, 9) !== "sendflow_") {
                continue;
            }
            if (empty($value)) {
                unset($data[$field]);
            }
            $field = substr($field, 9);
            $data[$field] = esc_attr__($value);
        }

        update_option(self::OPTION_NAME, $data);
        echo __('Saved!', 'sendflow');
        die();
    }

    /**
     * Adds Admin Scripts for the Ajax call
     */
    public function addAdminScripts()
    {
        wp_enqueue_style('sendflow-admin', SENDFLOW_URL . 'assets/css/admin.css', false, 1.0);
        wp_enqueue_script('sendflow-admin', SENDFLOW_URL . 'assets/js/admin.js', array(), 1.0);

        $adminOptions = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            '_nonce' => wp_create_nonce(self::NONCE),
        );

        wp_localize_script('sendflow-admin', 'sendflow_exchanger', $adminOptions);
    }

    /**
     * Adds the Sendflow label to the WordPress Admin Sidebar Menu
     */
    public function addAdminMenu()
    {
        add_menu_page(
            __('Sendflow', 'sendflow'),
            __('Sendflow', 'sendflow'),
            'manage_options',
            'sendflow',
            array(__CLASS__, 'adminLayout'),
            'dashicons-testimonial'
        );
    }

    /**
     * Make an API call to the Sendflow API and returns the response
     *
     * @param $websiteUid
     * @param $apiKey
     * @return array
     */
    private static function checkIntegration($websiteUid, $apiKey)
    {
        $response = wp_remote_get(SENDFLOW_PROTOCOL . '://api.' . SENDFLOW_ENDPOINT . '/v1/websites/' . $websiteUid . '/code/json',
            array(
                'timeout' => 5,
                'headers' => array(
                    'Authorization' => 'Basic ' . $apiKey,
                )
            ));

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        return $data;
    }

    /**
     * Get a Dashicon for a given status
     *
     * @param $valid boolean
     *
     * @return string
     */
    private static function getStatusIcon($valid)
    {
        return ($valid) ? '<span class="dashicons dashicons-yes success-message"></span>' : '<span class="dashicons dashicons-no-alt error-message"></span>';
    }

    /**
     * Outputs the Admin Dashboard layout containing the form with all its options
     *
     * @return void
     */
    public function adminLayout()
    {
        $data = self::getData();
        $apiResponse = self::checkIntegration($data['website_uid'], $data['api_key']);
        $notReady = (empty($data['website_uid']) || empty($data['api_key']) || empty($apiResponse) || !isset($apiResponse['result']));

        ?>

        <div class="wrap">
            <form id="sendflow-admin-form" class="postbox">
                <div class="form-group inside">

                    <img src="https://media.sendflow.pl/sendflow_logo_WEB_black@512.png" style="height: 50px;">

                    <?php
                    /*
                     * --------------------------
                     * API Settings
                     * --------------------------
                     */
                    ?>

                    <h3>
                        <?php echo self::getStatusIcon(!$notReady); ?>
                        <?php _e('Sendflow API Settings', 'sendflow'); ?>
                    </h3>

                    <?php if ($notReady): ?>
                        <p>
                            <?php _e('Make sure you have a Sendflow account first, it\'s free!', 'sendflow'); ?>
                            <?php _e('You can <a href="https://app.sendflow.pl/register" target="_blank">create an account here</a>.',
                                'sendflow'); ?>
                            <br>
                            <?php _e('If so you can find your api keys from your <a href="https://help.sendflow.pl/plugins/wordpress-integration-plugin" target="_blank">integrations page</a>.',
                                'sendflow'); ?>
                            <br>
                            <br>
                            <?php _e('Once the keys set and saved, if you do not see any option, please reload the page.',
                                'sendflow'); ?>
                        </p>
                    <?php else: ?>
                        <?php _e('Access your <a href="https://app.sendflow.pl" target="_blank">Sendflow dashboard here</a>.',
                            'sendflow'); ?>
                    <?php endif; ?>

                    <table class="form-table">
                        <tbody>
                        <tr>
                            <td scope="row">
                                <label><?php _e('API Key', 'sendflow'); ?></label>
                            </td>
                            <td>
                                <input name="sendflow_api_key"
                                       id="sendflow_api_key"
                                       class="regular-text"
                                       type="text"
                                       value="<?php echo (isset($data['api_key'])) ? $data['api_key'] : ''; ?>"/>
                            </td>
                        </tr>
                        <tr>
                            <td scope="row">
                                <label><?php _e('Website UID', 'sendflow'); ?></label>
                            </td>
                            <td>
                                <input name="sendflow_website_uid"
                                       id="sendflow_website_uid"
                                       class="regular-text"
                                       type="text"
                                       value="<?php echo (isset($data['website_uid'])) ? $data['website_uid'] : ''; ?>"/>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($data['api_key']) && !empty($data['website_uid'])): ?>
                    <?php if (empty($apiResponse)) : ?>
                        <p class="notice notice-error">
                            <?php _e('An error happened on the WordPress side. Make sure your server allows remote calls.',
                                'sendflow'); ?>
                        </p>
                    <?php elseif (isset($apiResponse['error'])): ?>
                        <p class="notice notice-error">
                            <?php echo $apiResponse['error']; ?>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>

                <hr>
                <div class="inside">
                    <button class="button button-primary" id="sendflow-admin-save" type="submit">
                        <?php _e('Save', 'sendflow'); ?>
                    </button>
                </div>
            </form>

        </div>

        <?php
    }
}

?>
