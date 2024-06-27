<?php

namespace WP_Datadog_RUM;

class RUM_Integration
{
    public function __construct()
    {
        add_action('wp_head', [$this, 'add_datadog_rum']);

        if (is_admin()) {
            add_action('admin_head', [$this, 'add_datadog_rum']);
        }
    }

    public function add_datadog_rum() {
        if (! get_option('datadog_rum_client_token') || ! get_option('datadog_rum_app_id')) {
            return;
        }
    
        global $current_user;
        $current_user = is_user_logged_in() ? wp_get_current_user() : null;
    
        // Enqueue the Datadog RUM script
        wp_enqueue_script('datadog-rum', 'https://www.datadoghq-browser-agent.com/datadog-rum-v5.js', [], null, false);
    
        // Prepare inline script data
        $rum_init_data = [
            'clientToken' => esc_js(get_option('datadog_rum_client_token')),
            'applicationId' => esc_js(get_option('datadog_rum_app_id')),
            'sampleRate' => esc_js(get_option('datadog_rum_sample_rate', 100)),
            'trackInteractions' => esc_js(get_option('datadog_rum_track_interactions', 'true')),
        ];
    
        $rum_context_data = [
            'logged_in' => $current_user ? 'true' : 'false',
        ];
    
        if ($current_user) {
            $rum_context_data = array_merge($rum_context_data, [
                'id' => esc_js($current_user->ID),
                'login' => esc_js($current_user->user_login),
                'email' => esc_js($current_user->user_email),
                'name' => esc_js($current_user->display_name),
            ]);
        }
    
        // Enqueue inline script
        wp_add_inline_script('datadog-rum', '
            window.DD_RUM && window.DD_RUM.init(' . wp_json_encode($rum_init_data) . ');
            window.DD_RUM && window.DD_RUM.addRumGlobalContext("usr", ' . wp_json_encode($rum_context_data) . ');
        '); 
    }
    
}

