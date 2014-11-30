<?php 
/**
* @description Class for reset admin password (opencart)
* @author Shashakhmetov Talgat <talgatks@gmail.com>
*/
$username 	= 	'admin';
$password 	= 	'123';
$email 		= 	'talgatks@gmail.com';

new ResetPassword($username, $password, $email);
class ResetPassword{
	private $db;
	public $versions = array('1.1.1','1.1.2','1.1.3','1.1.4','1.1.5','1.1.6','1.1.7','1.1.8','1.1.9','1.2.0','1.2.1','1.2.2','1.2.3','1.2.4','1.2.5','1.2.6','1.2.7','1.2.8','1.2.9','1.3.0','1.3.1','1.3.2','1.3.3','1.3.4','1.4.0','1.4.1','1.4.2','1.4.3','1.4.4','1.4.5','1.4.6','1.4.7','1.4.8','1.4.8b','1.4.9','1.4.9.1','1.4.9.2','1.4.9.3','1.4.9.4','1.4.9.5','1.4.9.6','1.5.0','1.5.0.1','1.5.0.2','1.5.0.3','1.5.0.4','1.5.0.5','1.5.0_rc2','1.5.1','1.5.1.1','1.5.1.2','1.5.1.3.1','1.5.2','1.5.2.1','1.5.3','1.5.3.1','1.5.4','1.5.4.1','1.5.5','1.5.5.1','1.5.6','1.5.6.1','1.5.6.2','1.5.6.3','1.5.6.4');

	function __construct($username, $password, $email){
		header('Content-Type: text/html; charset=utf-8');
		if (!is_file('config.php')) {
			die('Can\'t find file:'.'config.php');
		}
		if (!is_file('system/library/db.php')) {
			die('Can\'t find file:'.'system/library/db.php');
		}
		require_once('config.php');	
		require_once('system/library/db.php');
		$this->db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

		$version = $this->detectVersion();
		if (!empty($version)) {
			$query = $this->getPassQuery($username, $password, $version, $email);
			$this->db->query($query[0]);
			$this->db->query($query[1]);
			echo 'Определена версия:<b>' . $version . '</b><br>';
			echo 'Пароль был успешно изменен. <br>';
			echo 'Логин: <b>' . $username . '</b><br>';
			echo 'Пароль: <b>' . $password . '</b><br>';
			echo 'Е-mail для восстановления: <b>' .  $email . '</b><br>';
			unlink(__FILE__);
		}else{
			?>
			Скрипт не смог определить версию автоматически, выберите ее вручную. 
			<form action="<?php echo basename(__FILE__); ?>" method="POST">
				<select name="version" id="version">
				<?php foreach ($this->versions as $key => $value): ?>
					<option value="<?php echo $value ?>"><?php echo $value ?></option>
				<?php endforeach ?>
				</select>
				<button type="submit">Сбросить пароль</button>
			</form>
			<?php 
		}
	}
	
	public function getPassQuery($username, $password, $version, $email){
		if (version_compare($version, '1.2.8', '<=')) {
			return array("DELETE FROM `user` WHERE user_id = '1'", "INSERT INTO `user` SET user_id = '1', user_group_id = '1', username = '" . $this->db->escape($username) . "', password = '" . $this->db->escape(md5($password)) . "', date_added = NOW()");
		}
		//1.4.8 added version into index.php
		if (version_compare($version, '1.4.9.3', '<=')) {
			return array("DELETE FROM `" . DB_PREFIX . "user` WHERE user_id = '1'", "INSERT INTO `" . DB_PREFIX . "user` SET user_id = '1', user_group_id = '1', username = '" . $this->db->escape($username) . "', password = '" . $this->db->escape(md5($password)) . "', status = '1', date_added = NOW()");
		}
		if (version_compare($version, '1.5.3', '<=')) {
			return array("DELETE FROM `" . DB_PREFIX . "user` WHERE user_id = '1'", "INSERT INTO `" . DB_PREFIX . "user` SET user_id = '1', user_group_id = '1', username = '" . $this->db->escape($username) . "', password = '" . $this->db->escape(md5($password)) . "', status = '1', email = '" . $this->db->escape($email) . "', date_added = NOW()");
		}
		if (version_compare($version, '1.5.6.4', '<=')) {
			return array("DELETE FROM `" . DB_PREFIX . "user` WHERE user_id = '1'", "INSERT INTO `" . DB_PREFIX . "user` SET user_id = '1', user_group_id = '1', username = '" . $this->db->escape($username) . "', salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($password)))) . "', status = '1', email = '" . $this->db->escape($email) . "', date_added = NOW()");
		}
	}
	static function detectVersion(){
		if (isset($_POST['version'])){
			return $_POST['version'];
		}
		$handle = @fopen("index.php", "r");
		$result = '';
		if ($handle) {
		    while (($buffer = fgets($handle, 4096)) !== false) {
		        if (stripos($buffer, '\'version\'')) {
		        	$result .= $buffer;
		        }
		    }
		    if (!feof($handle)) {
		        echo "Error: unexpected fgets() fail\n";
		    }
		    fclose($handle);
		}
		
		@eval($result);
		
		if (!defined('VERSION')) {
			return;
		}
		
		return VERSION;
	}
}
?>