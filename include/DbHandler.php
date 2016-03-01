<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Vishnoex
 * @link androidhive.info
 */
class DbHandler {

	private $conn;

	function __construct() {
			require_once dirname(__FILE__) . '\DbConnect.php';
			// opening db connection
			$db = new DbConnect();
			$this->conn = $db->connect();
	}

	/* --------------------- CRUD Process ------------------------ */

	/**
	 * Creating new user
	 * @param String $user_status       NOT NULL
	 * @param String $user_name         NULL
	 * @param String $user_address      NULL
	 * @param String $user_phone        NULL
	 * @param String $user_email        NOT NULL
	 * @param String $user_password     NOT NULL
	 * @param String $user_fb           NULL
	 * @param String $user_twitter      NULL
	 * @param Double $user_lat          NOT NULL
	 * @param Double $user_lng          NOT NULL
	 * @param String $user_avatar       NULL
	 */
	
	public function createUser($user_status, $user_name, $user_address, $user_phone, $user_email, $user_password, $user_fb, $user_twitter, $user_lat, $user_lng, $user_avatar) {
		require_once 'PassHash.php';
		$response = array();
		// First check if user already existed in db
		if (!$this->isUserExists($user_email)) {
			// Generating password hash
			// $password_hash = PassHash::hash($user_password);
			// insert query
			$stmt = $this->conn->prepare('SET @user_status = ?, @user_name = ?, @user_address = ?, @user_phone = ?, @user_email = ?, @user_password = ?, @user_fb = ?, @user_twitter = ?, @user_lat = ?, @user_lng = ?, @user_avatar = ?');
			$stmt->bind_param('ssssssssdds', $user_status, $user_name, $user_address, $user_phone, $user_email, $user_password, $user_fb, $user_twitter, $user_lat, $user_lng, $user_avatar);
			$stmt->execute();

			$result = $this->conn->query('CALL proc_add_user(@user_status, @user_name, @user_address, @user_phone, @user_email, @user_password, @user_fb, @user_twitter, @user_lat, @user_lng, @user_avatar)');
			// Check for successful insertion
			if ($result) {
				// User successfully inserted
				return SUCCESS;
			} else {
				// Failed to create user
				return FAILED;
			}
		} else {
			// User with same email already existed in the db
			return ALREADY_EXISTED;
		}
		return $response;
	}

	/**
	* User Login
	* @param String $user_email		NOT NULL
	* @param String $user_password	NOT NULL
	*/
	public function checkLogin($user_email, $password){
		$result = array();
		$data = array();
		// fetching user by email
		$stmt = $this->conn->prepare("SELECT user_id, user_status, user_name, user_address, user_phone, user_email, user_password, user_fb, user_twitter, user_lat, user_lng, user_avatar FROM tb_user WHERE user_email = ?");

		$stmt->bind_param("s", $user_email);
		$stmt->execute();
		$stmt->bind_result($user_id, $user_status, $user_name, $user_address, $user_phone, $user_email, $user_password, $user_fb, $user_twitter, $user_lat, $user_lng, $user_avatar);
		$stmt->store_result();

		if ($stmt->num_rows > 0) {
			$stmt->fetch();
			$stmt->close();
			// print_r($password_hash);die();
			if ($user_password == $password) {
				// User password is correct
				$data["user_id"] = $user_id;
				$data["user_status"] = $user_status;
				$data["user_name"] = $user_name;
				$data["user_address"] = $user_address;
				$data["user_phone"] = $user_phone;
				$data["user_email"] = $user_email;
				$data["user_password"] = $user_password;
				$data["user_fb"] = $user_fb;
				$data["user_twitter"] = $user_twitter;
				$data["user_lat"] = $user_lat;
				$data["user_lng"] = $user_lng;
				$data["user_avatar"] = $user_avatar;

				$result["status"] = true;
				$result["message"] = $data;
				return $result;
			} else {
				// user password is incorrect
				$result["status"] = false;
				$result["message"] = "Password incorrect!";
				return $result;
			}
		} else {
			$stmt->close();
			// user not existed with the email
			$result["status"] = false;
			$result["message"] = "User not exist";
			return $result;
		}
	}

	/**
	 * Update detail existing user
	 * @param Int    $user_id						NOT NULL
	 * @param String $user_status       NOT NULL
	 * @param String $user_name         NULL
	 * @param String $user_address      NULL
	 * @param String $user_phone        NULL
	 * @param String $user_email        NOT NULL
	 * @param String $user_password     NOT NULL
	 * @param String $user_fb           NULL
	 * @param String $user_twitter      NULL
	 * @param Double $user_lat          NOT NULL
	 * @param Double $user_lng          NOT NULL
	 * @param String $user_avatar       NULL
	 */
	//user_id, user_status, user_name, user_address, user_phone, user_email, user_password, user_fb, user_twitter, user_lat, user_lng, user_avatar
	public function updateUser($user_id, $user_status, $user_name, $user_address, $user_phone, $user_email, $password_hash, $user_fb, $user_twitter, $user_lat, $user_lng, $user_avatar){
		require_once 'PassHash.php';
		$response = array();
		// Generating password hash
		// $password_hash = PassHash::hash($user_password);
		// insert query
		$stmt = $this->conn->prepare('SET @user_id = ?, @user_status = ?, @user_name = ?, @user_address = ?, @user_phone = ?, @user_email = ?, @user_password = ?, @user_fb = ?, @user_twitter = ?, @user_lat = ?, @user_lng = ?, @user_avatar = ?');
		$stmt->bind_param('issssssssdds', $user_id, $user_status, $user_name, $user_address, $user_phone, $user_email, $password_hash, $user_fb, $user_twitter, $user_lat, $user_lng, $user_avatar);
		$stmt->execute();

		$result = $this->conn->query('CALL proc_add_user(@user_id, @user_status, @user_name, @user_address, @user_phone, @user_email, @user_password, @user_fb, @user_twitter, @user_lat, @user_lng, @user_avatar)');
		// Check for successful update user
		if ($result) {
			// User successfully inserted
			return SUCCESS;
		} else {
			// Failed to create user
			return FAILED;
		}
	}

	/**
	* Create new AC
	* @param String $ac_brand					NULL
	* @param String $ac_series				NULL
	* @param String $ac_location			NULL
	* @param String $ac_note 					NULL
	* @param Int 		$user_id					NOT NULL
	* @param String $ac_lastservice		NULL
	* @param String $ac_nextservice		NULL
	* @param String $ac_status				NOT NULL
	* @param String $ac_picture				NULL
	*/
	public function insertAC($ac_brand, $ac_series, $ac_location, $ac_note, $user_id, $ac_lastservice, $ac_nextservice, $ac_status, $ac_picture) {
		//
		$stmt = $this->conn->prepare('SET @ac_brand = ?, @ac_series = ?, @ac_location = ?, @ac_note = ?, @user_id = ?, @ac_lastservice = ?, @ac_nextservice = ?, @ac_status = ?, @ac_picture = ?');
		$stmt->bind_param('ssssissss', $ac_brand, $ac_series, $ac_location, $ac_note, $user_id, $ac_lastservice, $ac_nextservice, $ac_status, $ac_picture);
		$stmt->execute();

		$result = $this->conn->query('CALL proc_add_ac(@ac_brand, @ac_series, @ac_location, @ac_note, @user_id, @ac_lastservice, @ac_nextservice, @ac_status, @ac_picture)');
		if($result) {
			// AC successfully inserted
			return SUCCESS;
		} else {
			// Failed to insert AC
			return FAILED;
		}
		return $response;
	}

	/**
	* Update existing AC detail or else
	* @param Int 		$ac_id 						NOT NULL
	* @param String $ac_brand					NULL
	* @param String $ac_series				NULL
	* @param String $ac_location			NULL
	* @param String $ac_note 					NULL
	* @param Int 		$user_id					NOT NULL
	* @param String $ac_lastservice		NULL
	* @param String $ac_nextservice		NULL
	* @param String $ac_status				NOT NULL
	* @param String $ac_picture				NULL
	*/
	public function updateAC($ac_id, $ac_brand, $ac_series, $ac_location, $ac_note, $user_id, $ac_lastservice, $ac_nextservice, $ac_status, $ac_picture) {
		//
		$stmt = $this->conn->prepare('SET @ac_id = ?, @ac_brand = ?, @ac_series = ?, @ac_location = ?, @ac_note = ?, @user_id = ?, @ac_lastservice = ?, @ac_nextservice = ?, @ac_status = ?, @ac_picture = ?');
		$stmt->bind_param('issssissss', $ac_id, $ac_brand, $ac_series, $ac_location, $ac_note, $user_id, $ac_lastservice, $ac_nextservice, $ac_status, $ac_picture);
		$stmt->execute();

		$result = $this->conn->query('CALL proc_edit_ac(@ac_id, @ac_brand, @ac_series, @ac_location, @ac_note, @user_id, @ac_lastservice, @ac_nextservice, @ac_status, @ac_picture)');
		if($result) {
			// AC successfully inserted
			return SUCCESS;
		} else {
			// Failed to insert AC
			return FAILED;
		}
		return $response;
	}

	/**
	* Get data's of all AC that user_id own
	* @param Int $user_id			NOT NULL
	*/
	public function getAllAC($user_id) {
		$result = array();
		//
		$stmt = $this->conn->prepare("SELECT ac_id, ac_brand, ac_series, ac_location, ac_note, user_id, ac_lastservice, ac_nextservice, ac_status, ac_picture from tb_ac WHERE user_id = ?");
		$stmt->bind_param("i", $user_id);
		
		if($stmt->execute()) {
			$data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
			$stmt->fetch();
			$stmt->close();

			$result["status"] = true;
			$result["message"] = $data;

			return $result;
		} else {
			$result["status"] = true;
			$result["message"] = "We have no result for you";

			return $result;
		}
	}

	/**
	* Get one AC data
	* @param Int $ac_id 	NOT NULL
	*/
	public function getAcById($ac_id) {
		$result = array();

		$stmt = $this->conn->prepare("SELECT ac_id, ac_brand, ac_series, ac_location, ac_note, user_id, ac_lastservice, ac_nextservice, ac_status, ac_picture from tb_ac WHERE ac_id = ?");
		$stmt->bind_param("i", $ac_id);

		if($stmt->execute()) {
			$data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
			$stmt->fetch();
			$stmt->close();

			$result["status"] = true;
			$result["message"] = $data;

			return $result;
		} else {
			$result["status"] = true;
			$result["message"] = "We have no result for you";

			return $result;
		}
	}

	/**
	* Delete one AC
	* @param Int $ac_id 		NOT NULL
	*/
	public function deleteAC($ac_id) {
		$stmt = $this->conn->prepare('SET @ac_id = ?');
		$stmt->bind_param('i', $ac_id);
		$stmt->execute();

		$result = $this->conn->query('CALL proc_remove_ac(@ac_id)');
		// Check for successful removal AC
		if ($result) {
			// AC successfully removed
			return SUCCESS;
		} else {
			// Failed to remove AC
			return FAILED;
		}
	}

	/* --------------------- End of CRUD Process ----------------- */

	/**
	 * Checking for duplicate user by email address
	 * @param String $email email to check in db
	 * @return boolean
	 */
	private function isUserExists($email) {
		$stmt = $this->conn->prepare("SELECT user_id from tb_user WHERE user_email = ?");
		$stmt->bind_param("s", $email);
		$stmt->execute();
		$stmt->store_result();
		$num_rows = $stmt->num_rows;
		$stmt->close();
		return $num_rows > 0;
	}
}

?>
