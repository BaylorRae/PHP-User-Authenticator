<?php require 'common/header.php'; ?>

<header>
  <h2>Sign Up</h2>
  <p>Already have an account? <a href="login.html">Log in</a> here!</p>
</header>
<form method="post" action="do-signup.php">
  <div class="field"><h3>User Info</h3></div>
  <div class="field">
    <label for="first_name">First Name</label>
    <input type="text" id="first_name" name="user[first_name]" value="Baylor" />
  </div>
  <div class="field">
    <label for="last_name">Last Name</label>
    <input type="text" id="last_name" name="user[last_name]" value="Rae'" />
  </div>
  <div class="field">
    <label for="email">Email Address</label>
    <input type="text" id="email" name="user[email_address]" value="baylor@example.com" />
  </div>
  <div class="field"><hr /><h3>Account Info</h3></div>
  <div class="field">
    <label for="username">Username</label>
    <input type="text" id="username" name="user[username]" value="BaylorRae" />
  </div>
  <div class="field">
    <label for="password">Password</label>
    <input type="text" id="password" name="user[password]" value="password123" />
  </div>
  <div class="field">
    <button>Sign Up</button>
  </div>
</form>

<?php require 'common/footer.php'; ?>