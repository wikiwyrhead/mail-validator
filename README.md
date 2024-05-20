# Email Validator WordPress Plugin

Email Validator is a simple WordPress plugin that allows you to validate email addresses without relying on external APIs. It checks for the validity of email addresses based on MX and SPF records and also detects disposable email addresses.

## Features

- Validates email addresses without external APIs
- Checks for valid MX and SPF records
- Detects disposable email addresses

## Installation

1. Download the plugin ZIP file.
2. Upload the ZIP file through the WordPress admin dashboard (`Plugins > Add New > Upload Plugin`).
3. Activate the plugin.

## Usage

1. Navigate to the "Email Validator" page in the WordPress admin menu.
2. Paste the email addresses you want to validate into the provided textarea, with one email address per line.
3. Click the "Validate Emails" button.
4. View the validation results.

## Notes

- **Valid**: The email address is correctly formatted and active.
- **Invalid**: The email address is incorrectly formatted or does not exist.
- **Unknown**: The email hosting service is blocking the validation process, so the status cannot be determined accurately. In such cases, the email is considered valid.

Please note that emails listed as "unknown" are treated as valid due to limitations imposed by the email hosting service.

## License

This plugin is licensed under the GNU General Public License v3.0 (GPL-3.0). See the [LICENSE](LICENSE) file for details.

## Credits

- The list of disposable email domains used in this plugin is sourced from [Disposable Email Domains](https://github.com/disposable-email-domains/disposable-email-domains).

## Release Notes

### Version 1.1
- Initial release
