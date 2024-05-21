<?php
/*
Plugin Name: TRDS Email Validator
Description: Validates email addresses without external APIs.
Version: 1.1
Author: Arnel Go
*/

// Add admin menu item
add_action('admin_menu', 'email_validator_menu');

function email_validator_menu()
{
    add_menu_page('TRDS Email Validator', 'TRDS Email Validator', 'manage_options', 'email-validator', 'email_validator_page', 'dashicons-email');
}

// Create validation page content
function email_validator_page()
{
?>
    <style>
        .email-validator-container {
            display: flex;
            gap: 50px;
            /* Set the gap to 20px */
            /*  justify-content: center; */
            align-items: center;
            padding: 20px;
            /* Optional: to add some space around the container */
        }

        .email-validator-form {
            flex: 1;
            /* Adjust to use remaining space equally */
            max-width: 30%;
            /* Adjust the maximum width as needed */
        }

        .email-validator-instructions {
            flex: 2;
            /* Adjust to use remaining space equally */
            max-width: 45%;
            /* Adjust the maximum width as needed */
        }
    </style>

    <div class="wrap">
        <h1 style="font-weight: bold;">TRDS Email Validator</h1>
        <div class="email-validator-container">
            <div class="email-validator-form">
                <form method="post">
                    <label for="email_addresses">Paste email addresses (one per line):</label><br>
                    <textarea name="email_addresses" rows="10" cols="50"></textarea><br>
                    <input type="submit" name="validate_emails" value="Validate Emails">
                </form>
            </div>
            <div class="email-validator-instructions">
                <h3 style="font-weight: bold;">Note on results:</h3>
                <ul style="list-style-type: square;">
                    <li><strong>Listed under Valid:</strong> The email address is correctly formatted and active.</li>
                    <li><strong>Listed under Invalid:</strong> The email address is incorrectly formatted or does not exist.</li>
                    <li><strong>Listed under Unknown:</strong> The email hosting service is blocking the validation process, so the status cannot be determined accurately. In such cases, the email is considered valid.</li>
                </ul>
                <p>Please note that emails listed as "unknown" are treated as valid due to limitations imposed by the email hosting service.</p>
            </div>
        </div>
        <?php
        if (isset($_POST['validate_emails'])) {
            $email_addresses = isset($_POST['email_addresses']) ? sanitize_textarea_field($_POST['email_addresses']) : '';
            $results = validate_email_addresses($email_addresses);
            display_validation_results($results);
        }
        ?>
    </div>
<?php
}

// Function to check if the domain is a known disposable email provider
function is_disposable_domain($domain)
{
    // Path to the local disposable email list file
    $file_path = plugin_dir_path(__FILE__) . 'list/disposable_email_blocklist.conf';

    // Read the list of disposable email domains from the local file
    $disposable_domains = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Check if the domain is in the list
    return in_array($domain, $disposable_domains);
}

// Validate email addresses
function validate_email_addresses($email_addresses)
{
    $results = array();
    $addresses = preg_split('/\r\n|\r|\n/', $email_addresses);
    foreach ($addresses as $address) {
        $address = trim($address);
        if (!empty($address)) {
            $results[$address] = validate_email($address);
        }
    }
    return $results;
}

// Validate single email address
function validate_email($email)
{
    // Extract the domain from the email address
    $domain = substr(strrchr($email, "@"), 1);

    // Check if the domain has valid MX records
    $mx_records = checkdnsrr($domain, 'MX');

    // Check if the domain has SPF records
    $spf_records = checkdnsrr($domain, 'TXT');

    // Check if the domain is a known disposable email provider
    $is_disposable = is_disposable_domain($domain);

    // Validate based on the checks
    if ($mx_records && $spf_records && !$is_disposable) {
        return 'valid';
    } elseif (!$mx_records && !$spf_records) {
        return 'invalid';
    } else {
        return 'unknown';
    }
}

// Display validation results
function display_validation_results($results)
{
    if (!empty($results)) {
        $valid_emails = [];
        $invalid_emails = [];
        $unknown_emails = [];

        foreach ($results as $email => $status) {
            switch ($status) {
                case 'valid':
                    $valid_emails[] = $email;
                    break;
                case 'invalid':
                    $invalid_emails[] = $email;
                    break;
                case 'unknown':
                    $unknown_emails[] = $email;
                    break;
            }
        }

        echo '<h2 style="margin-left: 20px; font-weight: extrabold;">VALIDATION RESULTS</h2>';
        if (!empty($valid_emails)) {
            echo '<h3 style="font-weight: extrabold; Color: Darkred; margin-left: 20px;">Valid Emails</h3>';
            echo '<ul style="margin-left: 40px;">';
            foreach ($valid_emails as $email) {
                echo '<li>' . $email . '</li>';
            }
            echo '</ul>';
        }

        if (!empty($unknown_emails)) {
            echo '<h3 style="font-weight: extrabold; Color: Darkred; margin-left: 20px;">Emails with Unknown Status</h3>';
            echo '<ul style="margin-left: 40px;">';
            foreach ($unknown_emails as $email) {
                echo '<li>' . $email . '</li>';
            }
            echo '</ul>';
        }

        if (!empty($invalid_emails)) {
            echo '<h3 style="font-weight: extrabold; Color: Darkred; margin-left: 20px;">Invalid Emails</h3>';
            echo '<ul style="margin-left: 40px;">';
            foreach ($invalid_emails as $email) {
                echo '<li>' . $email . '</li>';
            }
            echo '</ul>';
        }
    } else {
        echo '<p>No email addresses provided for validation.</p>';
    }
}
?>