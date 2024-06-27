<?php

namespace WP_Datadog_RUM;

class RUM_Admin
{
    public function __construct()
    {
        load_plugin_textdomain('datadog-rum', false, dirname(plugin_basename(__FILE__)) . '/languages');
        add_action('admin_menu', [$this, 'datadog_rum_config']);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_datadog_rum_action_links']);
    }

    public function print_datadogrum_management()
    {
        $clientToken = $applicationId = $sampleRate = $trackInteractions = $site = "";

        // Define the Datadog Site options array based on the data you provided
        $datadog_site_options = [
            'datadoghq.com' => 'US1 (datadoghq.com)',
            'us3.datadoghq.com' => 'US3 (us3.datadoghq.com)',
            'us5.datadoghq.com' => 'US5 (us5.datadoghq.com)',
            'datadoghq.eu' => 'EU1 (datadoghq.eu)',
            'ddog-gov.com' => 'US1-FED (ddog-gov.com)',
            'ap1.datadoghq.com' => 'AP1 (ap1.datadoghq.com)',
        ];
        $rumErrors = [];

        if (isset($_POST['submit'])) {
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to manage options for this blog.'));
            }
            $clientToken = sanitize_text_field(trim($_POST['datadog_rum_client_token']));
            if (strlen($clientToken) >= 35 && preg_match("/^pub/", $clientToken)) {
                update_option('datadog_rum_client_token', $clientToken);
            } else {
                $rumErrors[] = 'Invalid clientToken.';
            }

            $applicationId = sanitize_text_field(trim($_POST['datadog_rum_app_id']));
            if (strlen($applicationId) > 35 && substr_count($applicationId, "-") >= 4) {
                update_option('datadog_rum_app_id', $applicationId);
            } else {
                $rumErrors[] = 'Invalid applicationId.';
            }

            $sampleRate = sanitize_text_field(trim($_POST['datadog_rum_sample_rate']));
            if (filter_var($sampleRate, FILTER_VALIDATE_INT) && $sampleRate >= 0 && $sampleRate <= 100) {
                update_option('datadog_rum_sample_rate', $sampleRate);
            } else {
                $rumErrors[] = 'sampleRate must be an integer between 0 and 100.';
            }

            $site = sanitize_text_field(trim($_POST['datadog_rum_site']));
            update_option('datadog_rum_site', $site);

            $trackInteractions = sanitize_text_field(trim($_POST['datadog_rum_track_interactions']));
            update_option('datadog_rum_track_interactions', $trackInteractions);

            if (empty($rumErrors)) {
                ?>
                <div id="message" class="updated fade"><p><strong>Options saved.</strong></p></div>
                <?php
            }

            if (!empty($rumErrors)) {
                ?>
                <div id="message" class="error fade"><p><strong>
                            <?php foreach ($rumErrors as $error) {
                                echo "Error: " . $error . " <br/>";
                            } ?>
                        </strong></p></div>
                <?php
            }
        }
        ?>
        <div class="wrap">
            <img src="<?php echo plugin_dir_url(__FILE__) . 'datadog.svg'; ?>" width="100" alt="Datadog"/>
            <h2>Datadog RUM</h2>
            <p>Create a <a href="https://app.datadoghq.com/rum/list/">RUM application</a> in Datadog and enter its
                settings below. If you do not yet have an account you can sign up for a <a
                        href="https://www.datadoghq.com/free-datadog-trial/">free trial</a>.</p>
            <form method="post" action="">
                <b>Datadog clientToken</b>
                <input name="datadog_rum_client_token" type="text" id="datadog_rum_client_token"
                       value="<?php echo esc_attr(get_option('datadog_rum_client_token')); ?>" maxlength="40" size="40"
                       placeholder="e.g. pub12345667890"/><br/>
                <b>Datadog RUM applicationId</b>
                <input name="datadog_rum_app_id" type="text" id="datadog_rum_app_id"
                       value="<?php echo esc_attr(get_option('datadog_rum_app_id')); ?>" maxlength="40" size="40"
                       placeholder="e.g. foo-bar-baz-buzz"/><br/>
                <b>Percentage of sessions to track</b> (eg 100 for all, 0 for none)</b>
                <input name="datadog_rum_sample_rate" type="text" id="datadog_rum_sample_rate"
                       value="<?php echo esc_attr(get_option('datadog_rum_sample_rate', '100')); ?>" size="3"
                       maxlength="3"/>
                <br/>
                <b><label for="datadog_rum_site">Datadog Site</label></b>
                <select id="datadog_rum_site" name="datadog_rum_site">
                    <?php foreach ($datadog_site_options as $parameter => $label): ?>
                        <option value="<?php echo esc_attr($parameter); ?>" <?php selected(get_option('datadog_rum_site'), $parameter); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br/>
                <b><label for="datadog_rum_track_interactions">Track Interactions</label></b>
                <select id="datadog_rum_track_interactions" name="datadog_rum_track_interactions">
                    <option value="true" <?php selected(get_option('datadog_rum_track_interactions', 'true'), 'true'); ?>>
                        True
                    </option>
                    <option value="false" <?php selected(get_option('datadog_rum_track_interactions'), 'false'); ?>>
                        False
                    </option>
                </select>
                <br/>
                <input type="submit" name="submit" value="<?php esc_attr_e('Save Changes'); ?>"/>
            </form>
        </div>
        <?php
    }

    public function datadog_rum_config()
    {
        if (function_exists('add_submenu_page')) {
            add_submenu_page(
                'options-general.php',
                __('Datadog RUM', 'datadog-rum'),
                __('Datadog RUM'),
                'manage_options',
                'datadog-rum-config',
                [$this, 'print_datadogrum_management']
            );
        }
    }


    public function add_datadog_rum_action_links($links)
    {
        $settings_link = '<a href="' . admin_url('plugins.php?page=datadog-rum-config') . '">' . __('Settings') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}
