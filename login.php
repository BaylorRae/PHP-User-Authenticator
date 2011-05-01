<?php require 'common/header.php'; ?>

<header>
  <h2>Log In</h2>
  <p>Don't have an account? <a href="sign-up.html">Sign up</a> here!</p>
</header>
<form method="post">
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