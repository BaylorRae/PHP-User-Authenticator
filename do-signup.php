<?php
require 'functions.php';

$user = new User($_POST['user']);

if( $user->save() ) {
  header('Location: login.php');
}