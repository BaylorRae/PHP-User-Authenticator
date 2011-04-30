<?php
require 'MysqlDB.php';
require 'user.php';

MysqlDB::connect('localhost', 'root', 'root', 'demo');

// Create a new user
// $user = new User;
// $user->username = 'BaylorRae';
// $user->set_password('password123');
// 
// $user->save();

// Find a user
// if( $user = User::find_by_username('BaylorRae') ) {
//   echo '<pre>';
//   print_r($user);
//   echo '</pre>';
// }else
//   echo 'user not found';

// Authenticate a user
if( $user = User::authenticate('BaylorRae', 'password123') ) {
  echo '<pre>';
  print_r($user);
  echo '</pre>';
}else
  echo 'failed to authenticate user';