<?php
require 'functions.php';

if( $user = User::authenticate(_post('user.username'), _post('user.password')) ) {
  $_SESSION['login_hash'] = $user->generate_login_hash();
  header('Location: /');
}else
  require 'login.php';