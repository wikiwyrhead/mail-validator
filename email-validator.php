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
    <div class="wrap">
        <h1>TRDS Email Validator</h1>
        <form method="post">
            <label for="email_addresses">Paste email addresses (one per line):</label><br>
            <textarea name="email_addresses" rows="10" cols="50"></textarea><br>
            <input type="submit" name="validate_emails" value="Validate Emails">
        </form>
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

// Function to check if the domain is a known disposable email provider using debounce.io API
function is_disposable_domain($domain)
{
    // API endpoint to check if a domain is disposable
    $api_url = 'https://disposable.debounce.io/?email=' . urlencode('info@' . $domain);

    // Initialize cURL session
    $ch = curl_init();

    // Set the URL and options
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute cURL request
    $response = curl_exec($ch);

    // Check for cURL errors
    if ($response === false) {
        // Log an error message or handle the error as needed
        error_log('cURL error: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }

    // Close cURL session
    curl_close($ch);

    // Decode the JSON response
    $data = json_decode($response, true);

    // Check if JSON decoding was successful and the 'disposable' key exists
    if ($data === null || !isset($data['disposable'])) {
        // Log an error message or handle the error as needed
        error_log('Failed to decode JSON response or "disposable" key not found.');
        return false;
    }

    // Return true if the domain is disposable, otherwise false
    return $data['disposable'] === 'true';
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

        echo '<h2>Validation Results</h2>';
        if (!empty($valid_emails)) {
            echo '<h3>Valid Emails</h3>';
            echo '<ul>';
            foreach ($valid_emails as $email) {
                echo '<li>' . $email . '</li>';
            }
            echo '</ul>';
        }

        if (!empty($unknown_emails)) {
            echo '<h3>Emails with Unknown Status</h3>';
            echo '<ul>';
            foreach ($unknown_emails as $email) {
                echo '<li>' . $email . '</li>';
            }
            echo '</ul>';
        }

        if (!empty($invalid_emails)) {
            echo '<h3>Invalid Emails</h3>';
            echo '<ul>';
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