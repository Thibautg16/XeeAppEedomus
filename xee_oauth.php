<?php

// Version 1 / 24 février 2016		// Initial version
// Version 2 / 30 mai 2016				// multiple Xee modules support
// version 3 / 16 aout 2016       // API v3 update
// version 4 / 9 janvier 2018     // Correction réassociation
// version 5 / 1 avril 2019       // API V4

// https://dev.xee.com/v4-openap
$api_url = 'https://api.xee.com/v4/';

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
	if ($expire_time == '')
	{
		$expire_time = loadVariable('expire_time');
	}
	// if not expired we can keep the old access_token
  if (time() < $expire_time)
  {
    $access_token = loadVariable('access_token'.$_GET['car_id']);
    if ($access_token == '')
		{
			$access_token = loadVariable('access_token');
		}
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
		//.'&redirect_uri='.("https://secure.eedomus.com/sdk/plugins/xee/callback.php");
	}
	
	$response = httpQuery($api_url.'oauth/token', 'POST', $postdata, 'xee_oauth_v4');
	$params = sdk_json_decode($response);
	//var_dump($api_url.'oauth/token', $postdata, $response);

	if ($params['error'] != '' && !isset($params['refresh_token']))
	{
		// on reessaie avec le token global (l'utilisateur a pu refaire une association)
		if ($params['error'] == 'invalid_request' && $grant_type == 'refresh_token' && $_GET['car_id'] != '')
		{
			$refresh_token = loadVariable('refresh_token'.$_GET['car_id']);
			$postdata = 'grant_type='.$grant_type.'&refresh_token='.$refresh_token;
			$response = httpQuery($api_url.'oauth/token', 'POST', $postdata, 'xee_oauth_v4');
			$params = sdk_json_decode($response);
			
			if ($params['error'] == 'invalid_request') // le refresh token est périmé ou invalide
			{
				// on essaie avec le token "global" s'il existe
				$refresh_token = loadVariable('refresh_token');
				if ($refresh_token != '')
				{
					// après une réassociation suite à une perte d'association
					$postdata = 'grant_type='.$grant_type.'&refresh_token='.$refresh_token;
					$response = httpQuery($api_url.'oauth/token', 'POST', $postdata, 'xee_oauth_v4');
					$params = sdk_json_decode($response);
				}
			}
		}
		
		if ($params['error'] != '')
		{
			echo "<br>Auth error: <b>".$params['error'].'</b> (grant_type = '.$grant_type.')<br>'.$response.'<br>'."\n";
			echo "url:".$api_url.'oauth/token'.'<br>'; 
			echo "postdata:".$postdata.'<br>'; 
			die();
		}
	}

	// save on eedomus gateway for further use
	// on firt association $_GET['car_id'] is empty
	if (isset($params['refresh_token']))
	{
		$access_token = $params['access_token'];
		saveVariable('access_token'.$_GET['car_id'], $access_token);
		saveVariable('refresh_token'.$_GET['car_id'], $params['refresh_token']);
		saveVariable('expire_time'.$_GET['car_id'], time()+$params['expires_in']);
		saveVariable('code'.$_GET['car_id'], $code);
		
		// we must not delete a code if this is the polling of another working eedomus script
		if ($_GET['car_id'] != '' && $refresh_token == loadVariable('refresh_token'))
		{
			// so we won't reuse it again
			// this global code is only used once, just after the first oauth association
			saveVariable('code', '');
			saveVariable('access_token', '');
			saveVariable('refresh_token', '');
		}
		else if ($_GET['car_id'] == '')
		{
			// sans car_id, dans le cas de plusieurs Xee, permet de récupérer le code principal
			// où éventuellement de faire une réassociation si on l'a perdue
			saveVariable('access_token', $access_token);
			saveVariable('refresh_token', $params['refresh_token']);
		}
	}
	else if ($access_token == '')
	{
    //var_dump($api_url.'oauth/token', $postdata, $response);
		die("No access token :(<br>".$response);
	}
}

$HEADERS = array("Authorization: Bearer $access_token");

if ($_GET['car_id'] == '')
{
  // Fetch user infos
  $response = httpQuery($api_url.'users/me?access_token='.$access_token, 'GET', NULL, NULL, $HEADERS);
  $data = sdk_json_decode($response);
  $user_id = $data['id'];
  $user_name = $data['firstName'].' '.$data['lastName'];

  // Fetch user vehicles list
  $response = httpQuery($api_url.'users/me/vehicles?access_token='.$access_token, 'GET', NULL, NULL, $HEADERS);
  $data = sdk_json_decode($response);
  
	if (strpos($response, '"error":') !== false)
	{
		die($response);
	}

  // car id selection "menu"
  echo "Car identifiers (Copy & paste on in eedomus) :";
  echo "<br>";
  echo "<ul>";
  foreach($data as $vehicles)
  {
    echo '<li><input onclick="this.select();" type="text" size="40" readonly="readonly" value="'.$vehicles['id'].'"> : '.$vehicles['brand'].' '.$vehicles['name'].' ('.$user_name.')</li>';
  }
  echo "</ul>";
  die();
}
else
{
  // Fetch car status datas
  $response = httpQuery($api_url.'vehicles/'.$_GET['car_id'].'/status?access_token='.$access_token, 'GET', NULL, NULL, $HEADERS);
  $data = sdk_json_decode($response);
  
  // force token expiration for next query
  if ($data[0]['message'] == "Token has expired" && $_GET['car_id'] != '')
  {
    saveVariable('expire_time'.$_GET['car_id'], 0);
  }
 	// "Create a new access token and then retry"
	else if ($data[0]['message'] == "Token has been revoked" && $_GET['car_id'] != '')
  {
    saveVariable('expire_time'.$_GET['car_id'], 0);
  }
  else if ($data[0]['message'] == "Token does not have the required scope" || $data[0]['message'] == "Token cannot access this car") // Add the status_read scope to your app scopes and reconnect the user
  {
		/*saveVariable('access_token'.$_GET['car_id'], '');
		saveVariable('refresh_token'.$_GET['car_id'], '');
		saveVariable('expire_time'.$_GET['car_id'], 0);
		saveVariable('code'.$_GET['car_id'], '');*/
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