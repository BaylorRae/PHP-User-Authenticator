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
    $hash = sha1(join('', array($password, $salt)));
    
    return array('salt' => $salt, 'hash' => $hash);
  }
  
}

// Handles all user managing
class User {
  
  public $username;
  protected $password_data;
  
  function __construct(array $info = null) {
    if( !empty($info['username']) && !empty($info['password']) ) {
      $this->username = $info['username'];
      $this->set_password($info['password']);
    }
  }
  
  public function set_password($password) {
    // user salt, user password hash
    $this->password_data = UserCrypt::prepare_password($password);
  }
  
  public function save() {
    global $mysql;
    
    // Insert the row
    $mysql->insert('users', array(
      'username' => $this->username,
      'password_hash' => $this->password_data['hash'],
      'password_salt' => $this->password_data['salt']
    ));
  }
}