<?php require 'common/header.php'; ?>

<header>
  <h2>Log In</h2>
  <p>Don't have an account? <a href="sign-up.php">Sign up</a> here!</p>
</header>

<?php

if( !empty(User::$errors) && $errors = User::$errors ) {
  if( User::$validiator_class && is_callable(User::$validiator_class . '::get_errors') ) {
    $class = User::$validiator_class;
    $class::get_errors($errors);
  }
}

?>

<form method="post" action="do-login.php">
  <div class="field">
    <label for="username">Username</label>
    <input type="text" id="username" name="user[username]" />
  </div>
  <div class="field">
    <label for="password">Password</label>
    <input type="text" id="password" name="user[password]" />
  </div>
  <div class="field">
    <button>Log In</button>
  </div>
</form>

<?php require 'common/footer.php'; ?>