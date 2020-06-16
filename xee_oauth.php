<?php

// Version 1 / 24 février 2016		// Initial version
// Version 2 / 30 mai 2016				// multiple Xee modules support
// version 3 / 16 aout 2016       // API v3 update
// version 4 / 9 janvier 2018     // Correction réassociation
// version 5 / 1 avril 2019       // API V4
// version 6 / 15 juin 2020       // Amélioration multi-comptes/multi-Xee

// https://dev.xee.com/v4-openap
$api_url = 'https://api.xee.com/v4/';

// Code could change if we link again the eedomusXee app
if ($_GET['mode'] != 'verify')
{
	// first time we didn't have any car_id
  $user_id = loadVariable('user_id'.$_GET['car_id']);
  
	$refresh_token = loadVariable('refresh_token'.$user_id);
	$expire_time = loadVariable('expire_time'.$user_id);
	// if not expired we can keep the old access_token
	if (time() < $expire_time)
	{
		$access_token = loadVariable('access_token'.$user_id);
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
      $user_id = loadVariable('user_id'.$_GET['car_id']);
        
			$refresh_token = loadVariable('refresh_token'.$user_id);
			$postdata = 'grant_type='.$grant_type.'&refresh_token='.$refresh_token;
			$response = httpQuery($api_url.'oauth/token', 'POST', $postdata, 'xee_oauth_v4');
			$params = sdk_json_decode($response);
			
			if ($params['error'] == 'invalid_request') // le refresh token est périmé ou invalide
			{
				// do nothing
			}
		}
		
		if ($params['error'] != '')
		{
			echo "<br>Auth error: [".$params['error'].'] (grant_type='.$grant_type.')<br>'.$response.'<br>'."\n";
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
		
		if ($_GET['car_id'] != '')
		{
      $user_id = loadVariable('user_id'.$_GET['car_id']);
			saveVariable('access_token'.$user_id, $access_token);
			saveVariable('refresh_token'.$user_id, $params['refresh_token']);
			saveVariable('expire_time'.$user_id, time()+$params['expires_in']);
			saveVariable('code'.$user_id, $code);
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
  $GLOBALS['car_id_list'] = array();
  foreach($data as $vehicles)
  {
  	$car_id = $vehicles['id'];
    saveVariable('user_id'.$car_id, $user_id);
    saveVariable('access_token'.$user_id, $access_token);
    saveVariable('refresh_token'.$user_id, $params['refresh_token']);
    saveVariable('expire_time'.$user_id, time()+$params['expires_in']);
    saveVariable('code'.$user_id, $code);

    echo '<li><input onclick="this.select();" type="text" size="40" readonly="readonly" value="'.$car_id.'"> : '.$vehicles['brand'].' '.$vehicles['name'].' ('.$user_name.')</li>';
  }
  echo "</ul>";
  die();
}
else
{
  // Fetch car status datas
  $response = httpQuery($api_url.'vehicles/'.$_GET['car_id'].'/status?access_token='.$access_token, 'GET', NULL, NULL, $HEADERS);
  $data = sdk_json_decode($response);
  
  $user_id = loadVariable('user_id'.$_GET['car_id']);
  
  // force token expiration for next query
  if ($data[0]['message'] == "Token has expired" && $_GET['car_id'] != '' && $user_id != '')
  {
    saveVariable('expire_time'.$user_id, 0);
  }
 	// "Create a new access token and then retry"
	else if ($data[0]['message'] == "Token has been revoked" && $_GET['car_id'] != '' && $user_id != '')
  {
    saveVariable('expire_time'.$user_id, 0);
  }
  else if ($data[0]['message'] == "Token does not have the required scope" || $data[0]['message'] == "Token cannot access this car") // Add the status_read scope to your app scopes and reconnect the user
  {
    // do nothing
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