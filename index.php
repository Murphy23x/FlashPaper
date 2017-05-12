<?php


    //DEBUG
    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    error_reporting(-1);


    require_once "functions.php";

    $style = true;

    //Disable all CSS and extra HTML to make automation easier
    if (isset($_POST['nostyle'])) { $style = false; }

    //print_r($_GET); die();

    if (isset($_GET['k'])) {
        $base_pass = $_GET['k'];
        $password = base64_decode_url($base_pass);
        $sha_pass = hash("sha512", $password);
        $enc_text = read_file($sha_pass);
        $dec_text = encrypt_decrypt("decrypt", $password, $enc_text);

        /* DEBUG
        echo "<b>Given Password in Base64 (URL Safe) Form:</b> " . $base_pass . "<br />";
        echo "<b>Decoded Version of Given Password:</b> " . $password . "<br />";
        echo "<b>Hash of Given Password:</b> " . $sha_pass . "<br />";

        echo "Reading secret from file " . $sha_pass . "<br />";
        echo "Encrypted Secret Associated with SHA1 of Given Password: " . $enc_text . "<br />";
        echo "Decrypted Secret Associated with SHA1 of Given Password: " . $dec_text . "<br />";
        */

        //This is to prevent 'preview bots' from automatically viewing the secret and thus destroying it
        if ((isset($_GET['accept']) && $_GET['accept'] == "true") || $style == false) {
            //User has confirmed they'd like to see the secret
            //OR the user has 'nostyle' set in the URL

            if ($style == true) {
                //Build variables that will be displayed on 'message.php' page when included
                $message = htmlentities($dec_text);
                $message_title = "Self-Destructing Message";
                $message_subtitle = "This message has been destroyed";

                include('html/header.html');
                include('pages/message.php');
                include('html/footer.html');
            } else {
                echo $dec_text;
            }

            delete_file($sha_pass);

        } else {
            //Ask user to confirm viewing of secret
            //TODO: http://stackoverflow.com/a/41703064
            echo "View the secret?<br /><a href='?k=" . $_GET['k'] . "&accept=true'>Yes</a>";
        }

    } elseif (isset($_POST['submit'])) || $style == false) {
        $rand_pass = random_str();
        $enc_text = encrypt_decrypt("encrypt", $rand_pass, $_POST['secret']);
        $dec_text = encrypt_decrypt("decrypt", $rand_pass, $enc_text);
        $sha_pass = hash("sha512", $rand_pass);
        $base_pass = base64_encode_url($rand_pass);

        write_file($sha_pass, $enc_text);

        /* DEBUG
        echo "<b>Submitted Text:</b> " . $_POST['secret'] . "<br />";
        echo "<b>Random Pasword:</b> " . $rand_pass . "<br />";
        echo "<b>Hash of Random Password:</b> " . $sha_pass . "<br />";
        echo "<b>Base64 (URL Safe) of Random Password:</b> " . $base_pass . "<br />";
        echo "<hr />";
        echo "<b>Encrypted text:</b> " . $enc_text . "<br />";
        echo "<b>Decrypted text:</b> " . $dec_text . "<br />";
        echo "<hr />";

        echo "<b>Here's how the database entry for this will look:</b>" . "<br /><br />";
        echo "<table style='width: 90%'><tr><td><u>KEY (SHA1 of Random Password)</u></td><td><u>VALUE (Encrypted Secret Text)</u></td></tr><tr><td><b>$sha_pass</b></td><td>$enc_text</td></tr></table>";
        echo "<hr />";

        echo "<b>Here's how the URL you'll share will look:</b>" . "<br />";
        echo "https://password.paglusch.com/?k=" . $base_pass;
        echo "<br /><br /><br />";
	    */

        //Build variables that will be displayed on 'message.php' page when included

        $message = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/?k=" . $base_pass;

        if ($style == true) {
          $message_title = "Self-Destructing URL";
          $message_subtitle = "";

          include('html/header.html');
          include('pages/message.php');
          include('html/footer.html');
        } else {
          echo $message;
        }


	} else {
        if ($style == true) {
          include('html/header.html');
          print_html_form();
          include('html/footer.html');
        } else {
          //User didn't give us the required options, but they passed 'nostyle'
          echo "You have requested that we not show you any style/html by addding the 'nostyle' option to the POST data in this request. ";
          echo "You will need to either remove this option, or submit POST data for 'secret'";
        }
    }
?>
