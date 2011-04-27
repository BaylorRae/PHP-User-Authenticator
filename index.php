<?php
require 'mysql-wrapper.php';
require 'user.php';

$mysql = new MysqlWrapper('localhost', 'root', 'root', 'demo');

// Create a new user
// $user = new User;
// $user->username = 'BaylorRae';
// $user->set_password('password123');
// 
// $user->save();