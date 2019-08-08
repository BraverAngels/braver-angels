<?php
// Memberpress specific action hooks can be found in the following Gist
// https://gist.github.com/cartpauj/256e893ed3de276f8604aba01ef71bb8


//Capture a new Recurring Subscription created event

//Capture any completed transaction (recurring and non-recurring) event
function ba_log_completed_transaction($event) {

  if (!AN_KEY) {
    return;
  }
  $actionnetwork_url = AN_BASE . '/fundraising_pages/' . AN_FUNDRAISING_ID . '/donations';

  $transaction = $event->get_data();
  $user = $transaction->user();

  if(($subscription = $transaction->subscription_id)) {
    $transaction_number = $subscription->subscr_id;

    //This transaction belongs to a recurring subscription
    $recurrence = array(
      'recurring' => true,
    );
  }
  else {
    $transaction_number = $transaction->trans_num;
    //This is a non-recurring transaction
    $recurrence = null;
  }

  $person = array(
    "email_addresses" => [
      array(
        'address' => $user->user_email
      )
    ]
  );

  $fields = array(
    'identifiers' => [$transaction_number], // $transaction_id
    'recipients' => [array(
      'display_name' => 'Better Angels',
      'amount' => $transaction->total,
    )],
    'person' => $person,
    'action_network:recurrence' => $recurrence,
  );

  //Do what you need
  error_log('Transaction Happened');
  error_log("EMAIL IS " . $user->user_email);
  error_log("TOTAL IS " . $transaction->total);
  error_log("Making API request");

  $actionnetwork_response = ba_curl_post($actionnetwork_url, $fields);

  error_log(print_r($actionnetwork_response,1));

}
// add_action('mepr-event-transaction-completed', 'ba_log_completed_transaction');




add_action( 'wp_head', 'ba_process_membership_data' );

function ba_process_membership_data() {

     if( isset( $_POST['mepr-account-form']) && $_POST['mepr-account-form'] == 'Save Profile' ) {
        // Profile updated
        error_log(print_r($_POST, 1));
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
  if (isset($user_meta['mepr_lean_red_or_blue']) && $user_meta['mepr_lean_red_or_blue']) {
    $custom_fields['LeanRedorBlue'] = ucfirst(str_replace('-', ' ', $user_meta['mepr_lean_red_or_blue'][0]));
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
        'address' => $user_data->user_email
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

  error_log("Making API request");

  $actionnetwork_response = ba_curl_post($actionnetwork_url, $fields);

  error_log(print_r($actionnetwork_response,1));
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
    $custom_fields['LeanRedorBlue'] = ucfirst(str_replace('-', ' ', $data['mepr_lean_red_or_blue']));
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
        'address' => $data['user_email']
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

  error_log("Making API request");

  $actionnetwork_response = ba_curl_post($actionnetwork_url, $fields);

  error_log(print_r($actionnetwork_response,1));
}