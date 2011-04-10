<?php

/*
 *  PayPal IPN message handler.
 *  Tested with PayPal Adaptive API's the same as PayPal NVP API's
 * 
 *  by saulius@x.com (@sauliuz)
 * 
 *  30.05.2010
 * 
 * 
 */
 
// External includes
include_once("functions.php");

   
// If post wasnt submitted it most likely was accessed from web browser
// Let them know.
if (!$_POST) 
{
    echo "hey man, you are not using this right ;)";


} else { // We have $_POST set lets do some work

   // Grab contents of POST and do some work on parsing them
   $raw_post_data = file_get_contents('php://input');               
   $raw_post_array = explode('&', $raw_post_data);
   
   
   // Debuging to check new array contents
   //print_r($raw_post_array);
   
   
   
   // This code section logs received POST message
   // together with IP address it was sent from.

   // Setting vars
   $logFile = 'log.txt'; // Make sure this file exists and is writable
   $stamp = date('H:i:s, j-m-y');
   $ip=$_SERVER['REMOTE_ADDR'];
   
   
   // Writing log
   $file = fopen($logFile, 'a+') or die ("cant open the file");
      
   fwrite($file,"--- Submitted @ ". $stamp . " & Array contents: --- \r\n");
   set_array_to_file($file,$raw_post_array,"\$post_array");
   
   fwrite($file,"--- IP ADDRESS of sender ---: ". $ip ." \r\n");
   fwrite($file,"--- End of array contents --- \r\n");
  
   
   // PayPal Adaptive API specific parsing
   $myPost = array();
   
   foreach ($raw_post_array as $keyval)
      {
      $keyval = explode ('=', $keyval);
      if (count($keyval) == 2)
         $myPost[$keyval[0]] = urldecode($keyval[1]);
      }
      $_req = 'cmd=_notify-validate';
      foreach ($myPost as $key => $value)
         {
         $value = urlencode(stripslashes($value));
         $_req .= "&$key=$value";
         }
         
    
    /* TODO: implement this for nicely formated emails
             
    //Prepare data for sending in email
    $tempArray = explode ('&', $_req);
    $strArrayValues = sprint_r($tempArray);
    */
    
    // Assign values to local variables
    $item_name = $_POST['item_name'];
    $item_number = $_POST['item_number'];
    $payment_status = $_POST['payment_status'];
    $payment_amount = $_POST['mc_gross'];
    $payment_currency = $_POST['mc_currency'];
    $txn_id = $_POST['txn_id'];
    $receiver_email = $_POST['receiver_email'];
    $payer_email = $_POST['payer_email'];
   
     
   
   // We need to post it back to PayPal system to validate
   $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
   //$header .= "Host: www.sandbox.paypal.com:443\r\n";
   $header .= "Content-type: text/html; charset=utf-8\r\n";
   $header .= "Content-Length: " . strlen($_req) . "\r\n\r\n";
   $fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);
   
   //If connection fails
   if (!$fp) {
   
   $mail_From = "From: ipntest@sureprojects.com";
   $mail_To = "saulius.zukauskas@gmail.com";
   $mail_Subject = "HTTP ERROR while confirming IPN";
   $mail_Body = "Failed open socket to PayPal server";

   mail($mail_To, $mail_Subject, $mail_Body, $mail_From);
   
   } else { //Connection sucessful

   fputs ($fp, $header . $_req);
   
   while (!feof($fp)) {
   $res = fgets ($fp, 1024);
   
   if (strcmp ($res, "VERIFIED") == 0) {
   // check the payment_status is Completed
   // check that txn_id has not been previously processed
   // check that receiver_email is your Primary PayPal email
   // check that payment_amount/payment_currency are correct
   // process payment

   $mail_From = "From: ipntest@sureprojects.com";
   $mail_To = "saulius.zukauskas@gmail.com";
   $mail_Subject = "VERIFIED IPN";
   $mail_Body = $_req;

   mail($mail_To, $mail_Subject, $mail_Body, $mail_From);

   } else if (strcmp ($res, "INVALID") == 0) {
     // log for manual investigation

     $mail_From = "From: ipntest@sureprojects.com";
     $mail_To = "saulius.zukauskas@gmail.com";
     $mail_Subject = "INVALID IPN";
     $mail_Body = $_req;

     mail($mail_To, $mail_Subject, $mail_Body, $mail_From);
    }
  }
  fclose ($fp);
  }
}
?>

