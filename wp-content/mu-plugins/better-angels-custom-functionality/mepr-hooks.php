<?php
// Memberpress specific action hooks can be found in the following Gist
// https://gist.github.com/cartpauj/256e893ed3de276f8604aba01ef71bb8


add_action( 'wp_head', 'ba_process_membership_data' );

function ba_process_membership_data() {

     if( isset( $_POST['mepr-account-form']) && $_POST['mepr-account-form'] == 'Save Profile' ) {
        // Profile updated
        send_updated_user_data_to_action_network($_POST);

     }
     elseif (isset($_GET['membership_id']) && isset($_GET['membership'])) {
        // Membership saved
        send_new_user_data_to_action_network(get_current_user_id());
     }
}



/*
  After a user registers for a subscription and completes payment
*/

function send_new_user_data_to_action_network($user_id){

  // Bail if the API key is not set
  if (!AN_KEY) {
    return;
  }

  // The url we will eventually query
  $actionnetwork_url = AN_BASE . '/people/';

  // Query the user data and meta info
  $user_data = get_userdata($user_id);
  $user_meta = get_user_meta($user_id);

  // Add the default Member tag
  $tags = ['Member'];

  // Get array of user interests selected in profile and add to tags
  $interests = array_keys(get_user_meta($user_id, 'mepr_interests', true));

  foreach($interests as $interest) {
    $tags[] = 'Interest - ' . ucfirst(str_replace('-', ' ', $interest));
  }

  // add an empty array for "Custom Fields"
  $custom_fields = array();

  // Set tags
  if (isset($user_meta['mepr_lean_red_or_blue']) && $user_meta['mepr_lean_red_or_blue'][0]) {

    $lean = $user_meta['mepr_lean_red_or_blue'][0];
    if ($lean == 'red' || $lean == 'lean-red') {
      $tags[] = 'Red';
      $custom_fields['Master Partisanship'] = 'Red';
    } elseif ($lean == 'blue' || $lean == 'lean-blue') {
      $tags[] = 'Blue';
      $custom_fields['Master Partisanship'] = 'Blue';
    } else {
      $tags[] = 'Declined to say political affiliation';
      $custom_fields['Master Partisanship'] = 'Declined to say political affiliation';
    }

  }
  if (isset($user_meta['mepr_birthday']) && $user_meta['mepr_birthday']) {
    $custom_fields['Birthday'] = $user_meta['mepr_birthday'][0];
  }
  if (isset($user_meta['mepr_why_i_joined']) && $user_meta['mepr_why_i_joined']) {
    $custom_fields['Why I Joined'] = $user_meta['mepr_why_i_joined'][0];
  }
  if (isset($user_meta['mepr_phone']) && $user_meta['mepr_phone']) {
    $custom_fields['Telephone'] = $user_meta['mepr_phone'][0];
  }
  if (isset($user_meta['mepr_profession']) && $user_meta['mepr_profession']) {
    $custom_fields['Profession'] = $user_meta['mepr_profession'][0];
  }



  // Get the "registration date" from user data and convert to proper format
  $custom_fields['Membership Start Date'] = date("Y-m-d", strtotime($user_data->user_registered));

  // Set default donation frequency
  $recurrence = array(
    'recurring' => false,
    'period' => null
  );

  // Add the membership period and amount based on params in the "Welcome page url"
  // This runs on the "Welcome" page after signup or updating subscription
  if (isset($_GET['membership'])) {

    $membership_code = explode ('-', $_GET['membership']);

    if ($membership_code[0] == 'monthly' || $membership_code[0] == 'yearly' ) {
      if (isset($membership_code[1])) {
        $custom_fields['Contribution'] = $membership_code[1];
      }
      $recurrence = array(
        'recurring' => true,
        'period' => ucfirst($membership_code[0])
      );
    } elseif ($membership_code[0] = 'one') {
      $custom_fields['Contribution'] = $membership_code[3];
    } else {
      $custom_fields['Contribution'] = 0;
    }

  }

  // Set the user info
  $person = array(
    "family_name" => $user_meta['last_name'][0],
    "given_name" => $user_meta['first_name'][0],
    "email_addresses" => [
      array(
        'address' => $user_data->user_email,
        'status' => 'subscribed'
      )
    ],
    "postal_addresses" => [
      array(
        'postal_code' => $user_meta['mepr_zipcode'][0]
      )
    ],
    "country" => "US",
    "language" => "en",
    "custom_fields" => $custom_fields,
  );

  // Final fields we will submit to Action Network
  $fields = array(
    'person' => $person,
    'add_tags' => $tags,
    'action_network:recurrence' => $recurrence
  );

  $actionnetwork_response = ba_curl_post($actionnetwork_url, $fields);

}


/*
  After a user registers for a subscription and completes payment
*/

function send_updated_user_data_to_action_network($data){

  // Bail if the API key is not set
  if (!AN_KEY) {
    return;
  }

  // The url we will eventually query
  $actionnetwork_url = AN_BASE . '/people/';

  // Add the default Member tag
  $tags = ['Member'];

  // Get array of user interests selected in profile and add to tags
  if (isset($data['mepr_interests'])) {

    $interests = array_keys($data['mepr_interests']);

    foreach($interests as $interest) {
      $tags[] = 'Interest - ' . ucfirst(str_replace('-', ' ', $interest));
    }
  }

  // add an empty array for "Custom Fields"
  $custom_fields = array();

  // Set tags
  if (isset($data['mepr_lean_red_or_blue']) && $data['mepr_lean_red_or_blue']) {

    $lean = $data['mepr_lean_red_or_blue'];
    if ($lean == 'red' || $lean == 'lean-red') {
      $tags[] = 'Red';
      $custom_fields['Master Partisanship'] = 'Red';
    } elseif ($lean == 'blue' || $lean == 'lean-blue') {
      $tags[] = 'Blue';
      $custom_fields['Master Partisanship'] = 'Blue';
    } else {
      $tags[] = 'Declined to say political affiliation';
      $custom_fields['Master Partisanship'] = 'Declined to say political affiliation';
    }

  }
  if (isset($data['mepr_birthday']) && $data['mepr_birthday']) {
    $custom_fields['Birthday'] = $data['mepr_birthday'];
  }
  if (isset($data['mepr_why_i_joined']) && $data['mepr_why_i_joined']) {
    $custom_fields['Why I Joined'] = $data['mepr_why_i_joined'];
  }
  if (isset($data['mepr_phone']) && $data['mepr_phone']) {
    $custom_fields['Telephone'] = $data['mepr_phone'];
  }
  if (isset($data['mepr_profession']) && $data['mepr_profession']) {
    $custom_fields['Profession'] = $data['mepr_profession'];
  }

  // Set the user info
  $person = array(
    "family_name" => $data['user_last_name'],
    "given_name" => $data['user_first_name'],
    "email_addresses" => [
      array(
        'address' => $data['user_email'],
        'status' => 'subscribed'
      )
    ],
    "postal_addresses" => [
      array(
        'postal_code' => $data['mepr_zipcode']
      )
    ],
    "country" => "US",
    "language" => "en",
    "custom_fields" => $custom_fields,
  );

  // Final fields we will submit to Action Network
  $fields = array(
    'person' => $person,
    'add_tags' => $tags,
  );

  $actionnetwork_response = ba_curl_post($actionnetwork_url, $fields);

}


function get_user_subscription_id() {
  $membership_ids_array = [3706, 3612, 3613, 3614, 3616, 3618, 3620, 3621, 3622, 3623, 3624, 3625, 3626, 3627, 3628, 3629, 3630, 3631, 3632, 4278, 4279];
  $active_membership = null;
  foreach($membership_ids_array as $membership_id) {
    if (current_user_can('mepr-active','memberships: ' + $membership_id)) {
      $active_membership = $membership_id;
    }
  }
  return $active_membership;
}

function get_higher_membership_options() {
  $monthly_memberships = [3612, 3613, 3614, 3616, 4278, 3618, 3620];
  $yearly_memberships = [3621, 4279, 3622, 3623, 3624, 3625, 3626];

  if (in_array(get_user_subscription_id(), $monthly_memberships)) {

    $index = array_search(get_user_subscription_id(), $monthly_memberships);
    $higher_memberships = array_slice($monthly_memberships, $index + 1);

  } elseif (in_array(get_user_subscription_id(), $yearly_memberships)) {

    $index = array_search(get_user_subscription_id(), $yearly_memberships);
    $higher_memberships = array_slice($yearly_memberships, $index + 1);
    // Add the $50 per month option
    $higher_memberships[] = 3620;

  } else {

    $higher_memberships = array_merge($yearly_memberships, $monthly_memberships);

  }

  // Don't return the $25 options
  foreach([3618, 3622] as $membership) {
    if (array_search($membership, $higher_memberships)) {
      unset($higher_memberships[array_search($membership, $higher_memberships)]);
    }
  }

  return $higher_memberships;
}


// Function to add subscribe text to posts and pages
function ba_mepr_join_or_upgrade_text() {
  $membership_ids = '3706, 3612, 3613, 3614, 3616, 3618, 3620, 3621, 3622, 3623, 3624, 3625, 3626, 3627, 3628, 3629, 3630, 3631, 3632, 4278, 4279';

  if (!is_user_logged_in()) {
    return
    '<h2>Welcome to Better Angels</h2>
    <p>
      Please use the form below to tell us about yourself:<br/>
      <strong>Already have an account? <a href="' . home_url() . '/login?redirect_to=' . home_url() . $_SERVER['REQUEST_URI'] . '">Login</a> before completing your purchase.</strong>
      <br/><a href="' . home_url('login/?action=forgot_password') . '">Recover lost password</a>
    </p>';
  } elseif (is_user_logged_in() && current_user_can('mepr-active','memberships: ' . $membership_ids)) {
    return '<h2>Upgrade account</h2>
    <p>Complete the form below to upgrade your membership.
      <br/>
      <strong>Current subscription: ' . get_the_title(get_user_subscription_id()) . '</strong><br/><a href="'. home_url("account/?action=subscriptions").'">View account settings</a>
    </p>
    <p>
      <em>Your membership will renew automatically. Cancel any time.</em>
    </p>';
  }

  return;
}
add_shortcode('join_or_upgrade_text', 'ba_mepr_join_or_upgrade_text');


function ba_mepr_login_before_checkout_reminder() {
  if (!is_user_logged_in()) {
    return '<p><strong>Already have an account? <a href="' . home_url() . '/login?redirect_to=' . home_url() . $_SERVER['REQUEST_URI'] . '">Login</a> before completing your purchase.</strong></p>';
  }
  return;
}
add_shortcode('login_before_checkout_reminder', 'ba_mepr_login_before_checkout_reminder');
