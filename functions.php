<?php
session_start();

require 'libs/MysqlDB.php';
require 'libs/user.php';

class UserValidator {
  
  public static $uniqueness_of = array('username', 'email_address');
  public static $presence_of = array('username', 'email_address', 'password');
  public static $is_email = 'email_address';
  
  public static function validate($type, $field, $value) {
    if( $type == 'is_email' ) {
      $validation = filter_var($value, FILTER_VALIDATE_EMAIL);
      
      if( !$validation )
        User::add_error($type, $field);
    }
  }
}

MysqlDB::connect('localhost', 'root', 'root', 'demo');

function current_user() {
  return false;
}

function nav() {
  
  $links = array(
    'Home' => 'index.php'
    );
  
  if( current_user() ) {
    $links['Your Account'] = 'account.php';
    $links['Log Out'] = 'logout.php';
  }else { // no user logged in
    $links['Log In'] = 'login.php';
    $links['Sign Up'] = 'sign-up.php';
  }
  
  $class = null;
  foreach( $links as $title => $url ) {
    if( '/' . $url === $_SERVER['PHP_SELF'] )
      $class = ' class="current"';
    echo '<li><a' . $class . ' href="' . $url . '">' . $title . '</a></li>';
    $class = null;
  }
  
}