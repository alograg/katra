<?php

require "Twitter.php";

// Instantiate a Twitter object with a given username and pasword
// If a third argument is set to true, Twitter will be in debug mode which will
// output incoming and outgoing data.
$tweet = new Twitter("username", "password");
// Set a new status. This can be called multiple times on the same object.
// The update() function returns true if the status update was successful or
// false if an error occured. In case of an error, a string describing the error
// is stored in the error variable of the object (in our case $tweet->error)
$success = $tweet->update("PHP rocks my socks!");
if ($success) echo "Tweet successful!";
else echo $tweet->error;

?>
