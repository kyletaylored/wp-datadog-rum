# WP Datadog RUM

Instrument your WordPress sites with [Datadog Real User Monitoring](https://docs.datadoghq.com/real_user_monitoring/installation/) (RUM) to gain deep insights into user experience and performance. 

## Features

- **Automatic RUM Injection:**  Easily add Datadog RUM to your WordPress pages (both front-end and admin) with minimal configuration.
- **Customizable Settings:** Configure your Datadog client token, application ID, sampling rate, and more.
- **Environment Variable Integration:** Optionally set default options from your deployment environment variables.
- **Clean Deactivation:**  Removes plugin options when deactivated for a tidy uninstall.
- **Composer Support:** Install and manage the plugin using Composer (recommended).

## Installation

### Option 1: Using Composer (Recommended)

1. **Require the Package:** Run the following command in your WordPress project directory:

   ```bash
   composer require kyletaylored/wp-datadog-rum
   ```

2. **Activate**: Activate the "WP Datadog RUM" plugin through the "Plugins" menu in your WordPress dashboard.

### Option 2: Manual Installation

1. **Download**: Download the latest release from the Releases page.
2. **Upload**: Unzip the downloaded file and upload the wp-datadog-rum folder to your `/wp-content/plugins/` directory.
3. **Activate**: Activate the "WP Datadog RUM" plugin through the "Plugins" menu in your WordPress dashboard.

## Configuration

1. Datadog Credentials:
    - Create a new Datadog RUM application, obtain client token and application ID.
2. Plugin Settings:
    - Go to "Settings" -> "Datadog RUM" in your WordPress admin dashboard.
    - Enter your Datadog client token and application ID.
    - Customize other settings as needed (e.g., sampling rate, environment).

## License
This plugin is released under the MIT License.

## Acknowledgements
This plugin is a fork of the original WP Datadog RUM plugin by [Ilan Rabinovitch](https://github.com/irabinovitch).