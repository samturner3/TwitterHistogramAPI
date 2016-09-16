<?php

require_once __DIR__.'/../vendor/autoload.php'; 
use Abraham\TwitterOAuth\TwitterOAuth;


class Secrets

{
    public $CONSUMER_KEY = "6iaIOHLvnnw2bIggAFaarMir6";
    public $CONSUMER_SECRET = 'AcxfSCiJ1Zyw6O7YYBqoNjMqsQKsqSoKKxZmEjqBtVQcsKIMhp';

    public $access_token = '4622316804-7iqa0h0UMbAFFlBHZAh7NmFsGnk0q7WD8cRpyPA';
	public $access_token_secret = '6FIwVSmPXiarzexPJVMnyISiFJL0B0WnoJgNiJHKjraDo';
   
}

class Tweets {
	
		public function GetTweets ($name) {
		
			$passwords = new Secrets;
			//Twitter API Connection
			$connection = new TwitterOAuth($passwords->CONSUMER_KEY, $passwords->CONSUMER_SECRET, $passwords->access_token, $passwords->access_token_secret);
			$content = $connection->get("account/verify_credentials");
			//Raw API return data stored in $statuses
			$statuses = $connection->get("statuses/user_timeline", ["count" => 199, "trim_user" => false,  "screen_name" => $name]);

			//date_default_timezone_set("Australia/Sydney");
			//Get current server time for future caculations
			$now = strtotime('now');

			if (empty($statuses)) { //if no tweets/account found

				//echo "Not Empty!";
				//extract time stamps from found tweets, and caculate age of each tweet
				for ($x = 0; $x <= count($statuses); $x++) {
					//echo $x . ': ' . $statuses[$x]->created_at.'  '.strtotime($statuses[$x]->created_at);
					$diff = ($now - strtotime($statuses[$x]->created_at));
					$ago24 = 86400;
					//echo '  '.$diff;
					//echo '  '.$ago24;
					if ($diff < $ago24) { //Determine if tweet is less than 24h old
						//echo " True"; 
					}
					else {
						//echo " false";
					}	
					
					//echo '<br>';
				}
			}

			///////////////

			//Convert matched (less than 24h old tweets) string time stamps to UNIX format
			if (! is_object($statuses)) {
				# code...
			
				foreach ($statuses as $value) {
				 	$items[] = strtotime($value->created_at);
				 } 

			}

			if (!empty($items)) { //If account has no tweets yet
			 	# code...
			 
				foreach ($items as $value) {
					$diff2 = ($now - $value);
					//echo '$diff2 = '.$diff2 ."<br>";
				 	if ($diff2 < 86400) {
				 		//echo "True!";
				 		$items2[] = $value;
				 	}
				 } 

			 } 

			 //Convert items2[] (UNIX) into 24h format hours
			 if (!empty($items2)) { //if no tweets, bomb.
				 foreach ($items2 as $key => $value) {
				 	$items2[$key] = gmdate('G',$value);
				 }
			}

			$array3 = [];
			$y = NULL;
			//Begin construct of final array3, assign 24 positions, with number of matching hours per position.
			 	for ($x=0; $x <= 23 ; $x++) { 
			 		//echo $hour." ";
			 		if (!empty($items2)) {

			 			if ( ! isset($items2[$x])) {
						   $items2[$x] = null;
						}

						if ( ! isset($array3[$x])) {
						   $array3[$x] = null;
						}


				 		foreach ($items2 as $key => $value) {

				 			if ($value == $x) {
				 				$array3[$x] += 1;
				 				$y = $y + 1;
				 			}
				 		}

				 		if ($y == 0) {
			 			//echo "No tweets found for hour: ".$x."<br>";
			 		}
			 		$y = 0;


				 	}

			 	}
			 	//Set 0 for any empty hours when there were no tweets	
			    for($i = 0; $i <= 23; $i++)
				    {
				        if(!isset($array3[$i]))
					        {
					            $array3[$i] = 0;
					        }
				    }

				    //Sort Array by key (hour)
			ksort($array3,SORT_REGULAR);

			//Convert array to JSON OBJECT
			$tweetObject = json_encode($array3, JSON_FORCE_OBJECT);

			//Include raw statuses array in return for GUI processes
			$return = array($tweetObject, $statuses);

			/*//Error handling 
			if(isset($return[1]->errors[0])){
				echo "SOME ERROR";
				//If error code 34 returned, set error code 34
				if(($return[1]->errors[0]->code)=='34'){
					$return[2] = '34';
				else {
					$return[2] = '1';
					}
				}
			}*/

			

			//d($statuses);

			//Error handling.
			if(isset($statuses->errors)){

					$return[0] = json_encode(array('error' => array('msg' => 'Twitter API error: '.($return[1]->errors[0]->message),'code' => ($return[1]->errors[0]->code))));
				}
			elseif (isset($statuses->error)) {
				$return[0] = json_encode(array('error' => array('msg' => 'Twitter API error: '.($return[1]->error))));
			}	

		return $return;

	}

}

class TweetsJSON extends Tweets {
	
		public function GetTweetsJSON ($name) {
			header('Content-Type: application/json');
			$return = parent::GetTweets($name);

		return $return[0]; //$return[0] contains the first included array (our JSON OBJECT)

	}

}

class TweetsGUI extends Tweets {
	
		public function GetTweetsGUI ($name) {

			$return = parent::GetTweets($name);
			//Echo the profile image and username in GUI mode.


			//include 'plotHistogram.php';

			//Error handling.
			//d($return[1]);

			if(isset($return[1]->errors)){ //if twitter API error returned.
				header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
				echo '<b>Twitter API Error '.($return[1]->errors[0]->code).'.</b> '.($return[1]->errors[0]->message).'<br>';
				echo "Ending Script";
				die();
			}
			elseif (isset($return[1]->error)) { //if 2nd error type returned
				header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized", true, 401);
				echo '<b>Twitter API Error: </b>'.($return[1]->error).'<br>';
				echo "Ending Script";
				die();
			}
			elseif (empty($return[1])) { //if user found, but no tweets exist.
				echo 'This user has not yet tweeted, but exists. <br>';
			}
			//d($return[1]);
			//d($statuses);

			echo '<img src="'.$return[1][0]->user->profile_image_url.'"> <br>';
			echo '<h1>'.$return[1][0]->user->name.'</h1> <br>';
			echo "<b>Last Tweet:</b><br>";
			echo $return[1][0]->text;
			echo "<br><br>(Sydney Time)<b> Histogram:</b><br>";

			//echo '<img src="'.$return[1][0]->user->profile_image_url.'"> <br>';




		return $return[0];

	}

}



	