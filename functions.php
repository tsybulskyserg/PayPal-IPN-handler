<?php
// Functions to support ipnHandler
//
//

/** 
 * Writes an array to a file.  Can be later used by include/require
 * @param resource $file   : A file resource, (as returned from fopen)
 * @param array    $array  : The array tp be written from
 * @param string   $string : The initial variable name of the array,
 *                           as it will appear in the file
 */
function set_array_to_file($file,$array,$string="\$array") {
   fwrite($file,$string."=array();\r\n");
   foreach ($array as $ind => $val) {
      $str=$string."[".quote($ind)."]";
      if (is_array($val)) {
         if (has_no_sub_arrays($val)) {
            fwrite($file,$str."=".compress_array($val).";\r\n");
         } else {
            set_array_to_file($file,$val,$str);
         }
      } else {
         fwrite($file,$str."=".quote($val).";\r\n");
      }
   }
}
/**
 * Checks if an array contains no arrays
 * @param  arary $array : The array to be checked
 * @return boolean      : true if $array contains no sub arrays
 *                        false if it does
 */
function has_no_sub_arrays($array) {
   if (!is_array($array)) {
      return true;
   }
   foreach ($array as $sub) {
      if (is_array($sub)) {
         return false;
      }
   }
   return true;
}
/**
 * Compresses an array into a string:
 * $array=array();
 * $array[0]=0;
 * $array["one"]="one";
 * compress_array($array) will return 'array(0=>0,"one"=>"one")'
 * @param array $array : the array to be compressed
 * @return string      : the "compressed" string representation of $array
 * @note               : works recursively, so $array can contain arrays
 */
function compress_array($array) {
   if (!is_array($array)) {
      return quote($array);
   }
   $strings=array();
   foreach ($array as $ind => $val) {
      $strings[]=quote($ind)."=>".
                 (is_array($val)?compress_array($val):quote($val));
   }
   return "array(".implode(",",$strings).")";
}
/**
 * Adds quotes to $val if its not an integer
 * @param mixed $val : the value to be tested
 */
function quote($val) {
   return is_int($val)?$val:"\"".$val."\"";
}

?>
