 <?php 
 	/*Database and db functions*/

	//connect to db
	$conn = new mysqli("localhost", "root", "", "q2db");

	if ($conn->connect_error) {
	    die("Connection to database failed: " . $conn->connect_error . "</br>");
	}

	function getKey($username) {
		global $conn;
		$sql = "SELECT sharedKey FROM members WHERE username = \"$username\" LIMIT 1";
		$result = $conn->query($sql);
        if($result){
        	return $result->fetch_assoc()["sharedKey"];
        }
        else{
        	return -1;
        }
	}

	function comparePassword($user, $pwd) {
		global $conn;
		$sql = "SELECT * FROM members WHERE username = \"$user\" LIMIT 1";
		$result = $conn->query($sql);
        if($result){
        	$goodpwd = $result->fetch_assoc()["password"];
        	//echo $goodpwd." - ".$pwd;
        	return (strcmp($pwd, $goodpwd) == 0);
        }
        else{
        	return false;
        }
	}

	function createSession($user) {
		global $conn, $sessionCount;
		$sql = "SELECT memberID FROM members WHERE username = \"$user\" LIMIT 1";
		$result = $conn->query($sql);
        if($result){
        	$id = $result->fetch_assoc()["memberID"];
        	//find max session id yet in db
        	$sql = "SELECT MAX(sessionID) FROM sessions";
        	$result2 = $conn->query($sql);
        	$sessionID = $result2->fetch_assoc()["MAX(sessionID)"] + 1;
        	//insert session to db
        	$sql = "INSERT INTO sessions (memberID, sessionID) VALUES ($id, $sessionID)";
        	$result3 = $conn->query($sql);
        	if($result3) {
        		return $sessionID;
        	}
        	else {
        		return -2;
        	}

        }
        else{
        	return -1;
        }
	}


	function decrypt($key, $password)
	{
		$result = "";
		for ($i = 0; $i < strlen($password); $i++) {
			//var c = password.charCodeAt(i);
			$c = ord($password[$i]);
			if ($c >= 33 && $c <= 64) {
				//number or symbol (33 to 64)
				$result = $result.chr(((($c - 33 - $key) % 32)+32)%32 + 33);
			}
			else if ($c >= 65 && $c <=  90) {
				//upper case letter  
				$result = $result.chr(((($c - 65 - $key) % 26)+26)%26 + 65);  
			}
			else if ($c >= 97 && $c <= 122) {
				//lower case letter 
				$result = $result.chr(((($c - 97 - $key) % 26)+26)%26 + 97);
			}
			else {
				//not a letter, we leave it like that
				$result = $result.chr($c);
			}
		}
		return $result;
	}

 	

	/*Slim*/
 	require 'Slim/Slim.php';
 	\Slim\Slim::registerAutoloader();

 	//instantiate slim app (might need to provide arguments)
 	$app = new \Slim\Slim();

 	$app->get('/', function () 
 	{
    	echo "error";
	});
 	
 	$app->get(
    '/getkey/:name',
    function ($name) {
    	echo "".getKey($name);
    }
	);
	
 	$app->post(
    '/',
    function () use ($app){
    	$json = $app->request->getBody();
    	$creds = json_decode($json, true);
    	$username = $creds["username"];
    	$password = decrypt(getKey($username), $creds["password"]);
    	//echo $creds["password"]." --> $password";
    	

    	if(comparePassword($username, $password)) {
    		//password is valid
    		
    		$sessionID = createSession($username);

    		$acceptedPage = 
    		"<img src=\"success.jpg\" 
    			class=\"img-circle img-responsive center-block\" width=\"300\"></br>
    		 <h2 class=\"text-center\">Hi there $username! Wonderful to see you again!</h2></br></br></br>
    		 <form action=\"logout.php\" method=\"post\"> 
    		 	<input type=\"hidden\" name=\"sessionid\" value=\"$sessionID\">
				<button class=\"btn btn-lg btn-warning center-block\" type=\"submit\">Log out</button>
			 </form>
    		";

    		echo $acceptedPage;
    	}
    	else {
    		$refusedPage = 
    		"<img src=\"fail.jpg\" 
    		class=\"img-circle img-responsive center-block\"></br>
    		 <h2 class=\"text-center\">Unfortunately your credentials were <b>wrong</b>! Don't be sad, just try again!</h2></br></br></br>
    		 <form action=\"login.html\"> 
				<button class=\"btn btn-lg btn-primary center-block\" type=\"submit\">Return to login page</button>
			 </form>
    		";

    		echo $refusedPage;
    	}

    	
    }
	);
	

 	$app->run();


 	//close db connection
 	$conn->close();
 ?>