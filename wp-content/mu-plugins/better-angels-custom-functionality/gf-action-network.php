<?php

define('AN_BASE', "https://actionnetwork.org/api/v2");
define('AN_FUNDRAISING_ID', "4b5466f8-885f-432c-8b18-b491aa3ec4ab");


add_action( 'gform_after_submission_25', 'record_user_in_action_network_after_donation', 10, 2 );
// California campaign form

add_action( 'gform_after_submission_28', 'record_user_in_action_network_after_donation', 10, 2 );
// Iowa campaign form

add_action( 'gform_after_submission_29', 'record_user_in_action_network_after_donation', 10, 2 );
// "Support Us" donate form (Donate V2)


/*
 * Submit a user's basic information to Action Network after they complete a donation
*/
function record_user_in_action_network_after_donation($entry) {

  if (!AN_KEY) {
    return;
  }

  $actionnetwork_url = AN_BASE . '/people/';

  $transaction_id = $entry['transaction_id'];

  if (!$transaction_id) {
    return;
  }

  $tags = [];

  if ( strpos($entry['source_url'], 'twenty-twenty') !== false ) {
    $tags[] = '2020 Campaign Donor';
  }

  $person = array(
    "family_name" =>  $entry['41.6'],
    "given_name" =>  $entry['41.3'],
    "email_addresses" => [
      array(
        'address' => $entry['8'],
        'status' => 'subscribed'
      )
    ],
    "postal_addresses" => [
      array(
        'postal_code' => $entry['3.5']
      )
    ],
    "country" => "US",
    "language" => "en",
  );

  $fields = array(
    'person' => $person,
    'add_tags' => $tags,
  );

  $actionnetwork_response = ba_post_to_action_network($actionnetwork_url, $fields);

  return $actionnetwork_response;
}
