<?php
session_start();

require 'libs/MysqlDB.php';
require 'libs/user.php';

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