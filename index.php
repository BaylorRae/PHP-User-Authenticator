<?php require 'common/header.php'; ?>

<?php if( current_user() ) : ?>
  Welcome Back, <?php echo current_user()->full_name() ?>
<?php endif ?>

<?php require 'common/footer.php'; ?>