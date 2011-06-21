<?php require 'common/header.php'; ?>

<?php if( current_user() ) : ?>
  Welcome Back, <?php echo current_user()->full_name() ?>
<?php endif ?>

<?php if( current_user() && current_user()->can('access_admin_area') ) : ?>
  <p><a href="#">Admin Area</a></p>
<?php endif ?>

<?php require 'common/footer.php'; ?>