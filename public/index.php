
<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Vishnoex
 */
	require_once '../include/DbHandler.php';
	require_once '../include/PassHash.php';
	require '../libs/Slim/Slim.php';

	\Slim\Slim::registerAutoloader();
	$app = new \Slim\Slim();

	//testing GET
	$app->get('/hello', function () use ($app) {
		$name = $app->request->get('name');
		$response = $app->response();
		$response->header('Access-Control-Allow-Origin', '*');
	    $response->write(json_encode("Hello World, I am " . $name));
	});

	/* REGISTER USER */
	$app->post('/register', function() use($app) {
		$response = array();
		$data = json_decode($app->request->getBody());
		verifyRequiredParams(array('user_status', 'user_email', 'user_password', 'user_lat', 'user_lng'));
		$checkEmail = json_decode(validateEmail($data->user_email));

		$user_status = $data->user_status;
		$user_name = $data->user_name;
		$user_address = $data->user_address;
		$user_phone = $data->user_phone;
		$user_email = $data->user_email;
		$user_password = $data->user_password;
		$user_fb = $data->user_fb;
		$user_twitter = $data->user_twitter;
		$user_lat = $data->user_lat;
		$user_lng = $data->user_lng;
		$user_avatar = $data->user_avatar;

		if($checkEmail->status == true){
			$db = new DbHandler();
			$query = $db->createUser($user_status, $user_name, $user_address, $user_phone, $user_email, $user_password, $user_fb, $user_twitter, $user_lat, $user_lng, $user_avatar);

			if($query == SUCCESS) {
				$response["status"] = true;
			} else {
				$response["status"] = false;
			}
		} else {
			$response["status"] = false;
		}
		echo json_encode($response);
	});

	$app->post('/login', function() use($app) {
		$response = array();
		$data = json_decode($app->request->getBody());
		$db = new DbHandler();
		$query = $db->checkLogin($data->user_email, $data->user_password);
		if($query["status"]){
			$response["status"] = true;
			$response["message"] = $query["message"];
		} else {
			$response["status"] = false;
			$response["message"] = $query["message"];
		}
		echo json_encode($response);
	});

	$app->post('/insert_ac', function() use($app) {
		$response = array();
		$db = new DbHandler();
		$data = json_decode($app->request->getBody());

		$ac_brand = $data->ac_brand;
		$ac_series = $data->ac_series;
		$ac_location = $data->ac_location;
		$ac_note = $data->ac_note;
		$user_id = $data->user_id;
		$ac_lastservice = $data->ac_lastservice;
		$ac_nextservice = $data->ac_nextservice;
		$ac_status = $data->ac_status;
		$ac_picture = $data->ac_picture;
		verifyRequiredParams(array('user_id', 'ac_status'));

		$query = $db->insertAC($ac_brand, $ac_series, $ac_location, $ac_note, $user_id, $ac_lastservice, $ac_nextservice, $ac_status, $ac_picture);
		if($query){
			$response["status"] = true;
			// $response["message"] = $query["message"];
		} else {
			$response["status"] = false;
			// $response["message"] = $query["message"];
		}
		echo json_encode($response);
	});

	$app->put('/update_ac', function() use($app) {
		$response = array();
		$db = new DbHandler();
		$data = json_decode($app->request->getBody());
		verifyRequiredParams(array('ac_id', 'user_id', 'ac_status'));

		$ac_id = $data->ac_id;
		$ac_brand = $data->ac_brand;
		$ac_series = $data->ac_series;
		$ac_location = $data->ac_location;
		$ac_note = $data->ac_note;
		$user_id = $data->user_id;
		$ac_lastservice = $data->ac_lastservice;
		$ac_nextservice = $data->ac_nextservice;
		$ac_status = $data->ac_status;
		$ac_picture = $data->ac_picture;

		$query = $db->updateAC($ac_id, $ac_brand, $ac_series, $ac_location, $ac_note, $user_id, $ac_lastservice, $ac_nextservice, $ac_status, $ac_picture);
		if($query){
			$response["status"] = true;
			// $response["message"] = $query["message"];
		} else {
			$response["status"] = false;
			// $response["message"] = $query["message"];
		}
		echo json_encode($response);
	});

	$app->post('/get_all_ac', function() use($app) {
		$response = array();
		$db = new DbHandler();
		$data = json_decode($app->request->getBody());
		$query = $db->getAllAC($data->user_id);
		if($query["status"]) {
			$response["status"] = true;
			$response["message"] = $query["message"];
		} else {
			$response["status"] = false;
			$response["message"] = "Oops, we have a problem";
		}
		echo json_encode($response);
	});

	$app->get('/get_ac_by_id', function() use($app) {
		$response = array();
		$acid = $app->request->get('ac_id');
		$db = new DbHandler();
		$query = $db->getAcById($acid);
		if($query["status"]) {
			$response["status"] = true;
			$response["message"] = $query["message"];
		} else {
			$response["status"] = false;
			$response["message"] = "Oops, we have a problem";
		}
		echo json_encode($response);
	});

	$app->delete('/remove_ac', function() use($app) {
		$response = array();
		$data = json_decode($app->request->getBody());
		$db = new DbHandler();
		$query = $db->deleteAC($data->ac_id);
		if($query){
			$response["status"] = true;
		} else {
			$response["status"] = false;
		}
		echo json_encode($response);
	});

	$app->run();

	function verifyRequiredParams($required_fields) {
	    $error = false;
	    $error_fields = "";
	    $request_params = array();
	    // $request_params = $_REQUEST;

	    // Handling PUT request params
	    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
	        $app = \Slim\Slim::getInstance();
	        $request_params = $app->request()->getBody();
	    }

	    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	        $app = \Slim\Slim::getInstance();
	        // parse_str($app->request()->getBody(), $request_params);
	        $request_params = $app->request()->getBody();
	    }

	    foreach ($required_fields as $field) {
		    $newData = json_decode($request_params);

	        if (!isset($newData->$field) || strlen($field) <= 0 || $newData->$field == "" || $newData->$field == null) {
	            $error = true;
	            $error_fields .= $field . ', ';
	        }
	    }

	    if ($error) {
	        // Required field(s) are missing or empty & echo error json and stop the app
	        $response = array();
	        $app = \Slim\Slim::getInstance();
	        $response["status"] = 0;
	        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
	        echo json_encode($response);
	        $app->stop();
	    }
	}

	function validateEmail($email) {
	    $app = \Slim\Slim::getInstance();
	    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	        $response["status"] = false;
	        $response["message"] = 'Email address is not valid';
    	} else {
	        $response["status"] = true;
	        $response["message"] = 'Email address is valid';
    	}
    	return json_encode($response);
	}
?>