
<?php 

	//html header and footer with bootstrap
	$header = "<!DOCTYPE html>
	<html lang=\"en\">
  	<head>
    <meta charset=\"utf-8\">
    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
	<title>Login</title>
    <link href=\"bootstrap/css/bootstrap.min.css\" rel=\"stylesheet\">
    <link href=\"signin.css\" rel=\"stylesheet\">
  	</head><body>";

	$footer = "</body></html>";

	/*Database and db functions*/
	//connect to db
	$conn = new mysqli("localhost", "root", "", "q2db");

	if ($conn->connect_error) {
	    die("Connection to database failed: " . $conn->connect_error . "</br>");
	}

	function deleteSession($sid) {
		global $conn;
		$sql = "DELETE FROM sessions WHERE sessionID = \"$sid\" LIMIT 1";
		$result = $conn->query($sql);
	    if($result){
	    	return true;
	    }
	    else{
	    	return false;
	    }
	}

	function getMemberId($sid) {
		global $conn;
		$sql = "SELECT memberID FROM sessions WHERE sessionID = \"$sid\" LIMIT 1";
		$result = $conn->query($sql);
	    if($result->num_rows > 0){
	    	return $result->fetch_assoc()["memberID"];
	    }
	    else{
	    	return -1;
	    }
	}

	function getUsername($mid) {
		global $conn;
		$sql = "SELECT username FROM members WHERE memberID = \"$mid\" LIMIT 1";
		$result = $conn->query($sql);
	    if($result){
	    	return $result->fetch_assoc()["username"];
	    }
	    else{
	    	return "";
	    }
	}

	/*Slim*/
		require 'Slim/Slim.php';
		\Slim\Slim::registerAutoloader();

		$app = new \Slim\Slim();

		$app->get('/', function () 
		{
	});
	

	$app->post(
	'/',
	function () use ($app){
		global $header, $footer;

		//get hidden value sessionID
		$sid = $app->request->post('sessionid');
		
		$mid = getMemberId($sid);
		if($mid >= 0) {
			//sessionID does exist
			deleteSession($sid);
			$username = getUsername($mid);

			$acceptedPage = 
			"<img src=\"http://adorablekittens.com/wp-content/uploads/2015/07/cat-waving-goodbye-480x320.jpg\" 
				class=\"img-rounded img-responsive center-block\" width=\"300\"></br>
			 <h2 class=\"text-center\">Ohhh :( Why did you have to leave so soon dear <b>$username</b>? I'll miss you!</h2></br></br></br>
			 <form action=\"login.html\"> 
				<button class=\"btn btn-lg btn-primary center-block\" type=\"submit\">Return to login page</button>
			 </form>
			";


			echo $header.$acceptedPage.$footer;
		}
		else {
			$refusedPage = 
			"<img src=\"https://c1.staticflickr.com/3/2269/2152561318_1ca35956f9.jpg\" 
			class=\"img-circle img-responsive center-block\"></br>
			 <h2 class=\"text-center\">Unfortunately you were an <b>invalid</b> user! For shame brother, for shame!</h2></br></br></br>
			 <form action=\"login.html\"> 
				<button class=\"btn btn-lg btn-primary center-block\" type=\"submit\">Return to login page</button>
			 </form>
			";

			echo $header.$refusedPage.$footer;
		}
	}
	);


	$app->run();


	//close db connection
	$conn->close();
?>