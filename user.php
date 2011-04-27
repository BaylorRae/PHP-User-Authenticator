<?php

/*
  TODO Make sure the password and username are set, before saving
  TODO Login a user (check the database for matching records)
*/

// Handles all password encryption
class UserCrypt {
  
  public static function prepare_password($password) {
    // salt
    $salt = sha1(join('', array(time(), rand())));
    
    // hash (includes the salt)
    $hash = self::encrypt_password($password, $salt);
    
    return array('salt' => $salt, 'hash' => $hash);
  }
  
  public static function encrypt_password($password, $salt) {
    return sha1(join('', array($password, $salt)));
  }
  
}

// Handles all user managing
class User {
  
  protected $user_data = array();
  
  function __construct(array $info = null) {
    if( !empty($info['username']) && !empty($info['password']) ) {
      $this->username = $info['username'];
      $this->set_password($info['password']);
    }
  }
  
  public function __get($name) {
    return (empty($this->user_data[$name])) ? null : $this->user_data[$name];
  }
  
  public function __set($name, $value) {
    $this->user_data[$name] = $value;
  }
  
  public static function instance() {
    static $instance = null;
    if( $instance === null )
      $instance = new User;
    
    return $instance;
  }
  
  public function set_password($password) {
    // user salt, user password hash
    $data = UserCrypt::prepare_password($password);
    $this->password_hash = $data['hash'];
    $this->password_salt = $data['salt'];
  }
  
  public function save() {
    global $mysql;
    
    // Insert the row
    $mysql->insert('users', array(
      'username' => $this->username,
      'password_hash' => $this->password_hash,
      'password_salt' => $this->password_salt
    ));
  }
  
  public static function find_by_username($username) {
    global $mysql;
    $return_value = false;
    
    $mysql->where('username', $username);
    $result = $mysql->get('users');
    
    if( !empty($result[0]) ) {
      $user = User::instance();
      $user->user_data = (array) $result[0];
      
      $return_value = $user;
    }
    
    return $return_value;
  }
  
  public static function authenticate($username, $password) {
    $return_value = false;
    
    if( ($user = User::find_by_username($username)) && // check if the username is found in the database
      
        // Make sure the user password matches the password_hash
        ($user->password_hash == UserCrypt::encrypt_password($password, $user->password_salt))
      ) {
      
      $return_value = $user;
            
    }
    
    return $return_value;
  }
}