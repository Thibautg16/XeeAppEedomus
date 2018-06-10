<?php

// Version 1 / 24 février 2016		// Initial version
// Version 2 / 30 mai 2016				// multiple Xee modules support
// version 3 / 16 aout 2016       // API v3 update
// version 4 / 9 janvier 2018     // Correction réassociation

$api_url = 'https://cloud.xee.com/v3/';

// Code could change if we link again the eedomusXee app
if ($_GET['mode'] != 'verify')
{
	// first time we didn't have any car_id
	$refresh_token = loadVariable('refresh_token'.$_GET['car_id']);
	if ($refresh_token == '')
	{
		$refresh_token = loadVariable('refresh_token');
	}
	
	$expire_time = loadVariable('expire_time'.$_GET['car_id']);
	// if not expired we can keep the old access_token
  if (time() < $expire_time)
  {
    $access_token = loadVariable('access_token'.$_GET['car_id']);
  }
}

// we need an access token
if ($access_token == '')
{
	if (strlen($refresh_token) > 1)
	{
		// we need to get the refresh token
		$grant_type = 'refresh_token';
		$postdata = 'grant_type='.$grant_type.'&refresh_token='.$refresh_token;
	}
	else
	{
		// Initial code fetching
		$code = $_GET['oauth_code'];
		$grant_type = 'authorization_code';
		$postdata = 'grant_type='.$grant_type.'&code='.$code;
	}
	
	$response = httpQuery($api_url.'auth/access_token', 'POST', $postdata, 'xee_oauth');
	$params = sdk_json_decode($response);

	if ($params['error'] != '')
	{
		// on reessaie avec le token global (l'utilisateur a pu refaire une association)
		if ($params['error'] == 'invalid_request' && $grant_type == 'refresh_token')
		{
			$refresh_token = loadVariable('refresh_token');
			$postdata = 'grant_type='.$grant_type.'&refresh_token='.$refresh_token;
			$response = httpQuery($api_url.'auth/access_token', 'POST', $postdata, 'xee_oauth');
			$params = sdk_json_decode($response);
		}
		
		if ($params['error'] != '')
		{
			var_dump($api_url.'auth/access_token', $postdata);
			die("<br><br>Auth error: <b>".$params['error'].'</b> (grant_type = '.$grant_type.')<br><br>'.$response);
		}
	}

	// save on eedomus gateway for further use
	if (isset($params['refresh_token']))
	{
		$access_token = $params['access_token'];
		saveVariable('access_token'.$_GET['car_id'], $access_token);
		saveVariable('refresh_token'.$_GET['car_id'], $params['refresh_token']);
		saveVariable('expire_time'.$_GET['car_id'], time()+$params['expires_in']);
		saveVariable('code'.$_GET['car_id'], $code);
		
		// sans car_id, dans le cas de plusieurs Xee, permet de récupérer le code principal
		saveVariable('access_token', $access_token);
		saveVariable('refresh_token', $params['refresh_token']);
	}
	else if ($access_token == '')
	{
    //var_dump($api_url.'auth/access_token', $postdata, $response);
		die("Auth error :(<br><br>".$response);
	}
}

$HEADERS = array("Authorization: Bearer $access_token");

if ($_GET['car_id'] == '')
{
  // Fetch user infos
  $response = httpQuery($api_url.'users/me?access_token='.$access_token, 'GET', NULL, NULL, $HEADERS);
  $data = sdk_json_decode($response);
  $user_id = $data['id'];

  // Fetch user cars list
  $response = httpQuery($api_url.'users/'.$user_id.'/cars?access_token='.$access_token, 'GET', NULL, NULL, $HEADERS);
  $data = sdk_json_decode($response);

  // car id selection "menu"
  echo "Car identifiers (Copy & paste on in eedomus) :";
  echo "<br>";
  echo "<ul>";
  foreach($data as $cars)
  {
    echo '<li><b>'.$cars['id'].'</b> : '.$cars['name'].'</li>';
  }
  echo "</ul>";
  die();
}
else
{
  // Fetch car status datas
  $response = httpQuery($api_url.'cars/'.$_GET['car_id'].'/status?access_token='.$access_token, 'GET', NULL, NULL, $HEADERS);
  $data = sdk_json_decode($response);
  
  // force token expiration for next query
  if ($data[0]['message'] == "Token has expired")
  {
    saveVariable('expire_time'.$_GET['car_id'], 0);
  }
 	// "Create a new access token and then retry"
	else if ($data[0]['message'] == "Token has been revoked")
  {
    saveVariable('expire_time'.$_GET['car_id'], 0);
  }
  else if ($data[0]['message'] == "Token does not have the required scope" || $data[0]['message'] == "Token cannot access this car") // Add the status_read scope to your app scopes and reconnect the user
  {
		saveVariable('access_token'.$_GET['car_id'], '');
		saveVariable('refresh_token'.$_GET['car_id'], '');
		saveVariable('expire_time'.$_GET['car_id'], 0);
		saveVariable('code'.$_GET['car_id'], '');
	}

  // updating position channel
  $position_controller_module_id = getArg('position_controller_module_id');
  
  $last_lat_long = loadVariable('last_lat_long'.$_GET['car_id']);
  $last_lat_long_time = loadVariable('last_lat_long_time'.$_GET['car_id']);
	
	foreach($data['signals'] as $signals)
	{
		if ($signals['name'] == 'VehiculeSpeed')
		{
			$speed = $signals['value'];
		}
	}
	
	if ($data['location']['latitude'] != '') // safety
	{
		$lat_long = $data['location']['latitude'].','.$data['location']['longitude'].','.$speed;
		// if no move, update only each 30 minutes
		
		if ($last_lat_long != $lat_long || time() - $last_lat_long_time > 30 /*minutes*/* 60)
		{
			setValue($position_controller_module_id, $lat_long);
			saveVariable('last_lat_long_time'.$_GET['car_id'], time());
			saveVariable('last_lat_long'.$_GET['car_id'], $lat_long);
		}
	}
  
  sdk_header('text/xml');
  echo jsonToXML($response);
}

?>