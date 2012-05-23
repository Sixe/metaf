<?php

$fbcookie = get_facebook_cookie(FACEBOOK_APP_ID, FACEBOOK_SECRET);

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"
      xmlns:fb="http://www.facebook.com/2008/fbml">
  <body>
    <?php if ($fbcookie) { ?>
      Your user ID is <?= $fbcookie['uid'] ?>
    <?php } else { ?>
      <fb:login-button></fb:login-button>
    <?php } ?>

    <div id="fb-root"></div>
    <script src="https://connect.facebook.net/'<?php print($LANG['FB_LANG']);?>'/all.js"></script>
    <script>
      FB.init({appId: '<?php= FACEBOOK_APP_ID ?>', status: true,
               cookie: true, xfbml: true});
      FB.Event.subscribe('auth.login', function(response) {
        window.location.reload();
      });
    </script>
  </body>
</html>