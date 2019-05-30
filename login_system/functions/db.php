<?php 

// establish the database connection 

$con  = mysqli_connect('localhost','root', 'password', 'login_db');

// this function is used to make sure
// that a record exist in the user table
 
function row_count($result){

    return mysqli_num_rows($result);
}

// This function  is used to prevent sql injection 
//  It is used to make sure to make sure that 
//  the data entered is real string 

function escape($string)
{
    global $con;
    return mysqli_real_escape_string($con, $string);
}

// Since the connection is established
//  this function makes sure that we getting 
//  the appropriate result
 
function confirm($result)
{
    global $con;
    if (!$result) {

        die("QUERY FAILED" . mysqli_error($con));
    }
}


function query($query){

    global $con;
    $result = mysqli_query($con,$query);
    confirm($result);
    return $result;

}


function fetch_array($result){
    global $con; 
    return mysqli_fetch_array($result);
}

?>
