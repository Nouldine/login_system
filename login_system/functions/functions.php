<?php


// Use php mailing library to test 
// the password recovery via email 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './vendor/autoload.php';


/*************************** helper  functions ************************/

function clean($string){

    return htmlentities($string);
}

// this function is used to redirect 
// to any location 

function redirect($location){

    return header("Location: {$location} ");

}

function set_message($message){

    if(!empty($message)){

        $_SESSION['message'] = $message;
    }
    else {
        $message = "";
    }
}


function display_message(){
    
    if(isset($_SESSION['message'])){

        echo $_SESSION['message']; 
        unset($_SESSION['message']);
    }
    
}

function  token_generator(){

   $token = $_SESSION['token'] = md5(uniqid(mt_rand(), true));
   return $token;

}

function  validation_errors($error_message){

     $error_message = <<<DELIMITER
     <div class="alert alert-danger alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <strong>Warning!</strong> $error_message
 </div>
DELIMITER;

return $error_message;

}


function email_exists($email){

    $email = $_POST['email'];

    if(!empty($_POST['email'])){
    $sql = "select id from users  where email = '".escape($email)."'";
    $result = query($sql);
    }
    if(row_count($result)==1) {

        return true;

    } else {

        return false; 
    }


}
    function username_exists($username){

    $sql = "select id from users where username = '$username' ";

    $result = query($sql);

    if(row_count($result)== 1) {

        return true;

    } else {

        return false; 
    }
    

    }

// This function is used to test the password recovery system via 
//  email using the smtp protocol 
//  

function send_email($email = null, $subject = null, $msg = null, $headers = null) {

    $mail = new PHPMailer();    
    //Server settings
    //$mail->SMTPDebug = 2; 
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = Config::SMTP_HOST;                        // Specify main and backup SMTP servers
    $mail->Username = Config::SMTP_USER;                           // SMTP username
    $mail->Password = Config::SMTP_PASSWORD;                           // SMTP password
    $mail->Port = Config::SMTP_PORT;                                    // TCP port to connect to
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->SMTPSecure = 'tls';                         // Enable TLS encryption, `ssl` also accepted


    $mail->isHTML(true);
    $email->Charset = "UTF-8";
    
    $mail->setFrom("user10@gmail.com", 'Nouldine'); // sender email address 
    $mail->addAddress('user1@gmail.com'); // receiver email address 
    
    
    $mail->Subject = $subject;
    $mail->Body = $msg;  
    $mail->AltBody = $msg;

    // If the email is not sent 
    // Display an error message
    if(!$mail->send()){

        echo "Message could not be sent \n";
        echo 'Mailer Error:' . $mail->ErrorInfo;

    }
    else {

        echo 'Message has been sent';
    }

    return mail($email, $subject, $msg, $headers); 

}


/*************************** validation function**************************************/

/*  This function is used to ensure the user registration
 *  in terms of number of characters that need to entered 
 *  to make the registration possible
 *
 */
function validate_user_registration(){

    $errors = []; // create an array to store error message 
    $min = 3;  // minimum number of character that can be entered 
    $max = 20; // maximun number of character that can be entered 

    // use a post method to ensure the user 
    // validation  
    if($_SERVER['REQUEST_METHOD']== "POST") {

        $first_name        = clean($_POST['first_name']); 
        $last_name         = clean($_POST['last_name']);
        $username          = clean($_POST['username']);
        $email             = clean($_POST['email']);
        $password          = clean($_POST['password']);
        $confirm_password  = clean($_POST['confirm_password']);


    if(strlen($first_name) < $min){

            $errors[] = "Your first name connot be less than {$min} Characters";

    } 
    elseif(strlen($first_name) > $max) {

	 	$errors[] = "Your last name cannot be more than {$max} Characters";
	}

    if (strlen($last_name) < $min) {
            $errors[] = "Your last name connot be less than {$min} Characters";

        }

    elseif(strlen($last_name) > $max ) {

            $errors[] = "Your last name connot be more than {$max} Characters";

    }

    if (strlen($username) < $min) {

            $errors[] = "Your username connot be less than {$min} Characters";
    }

    elseif (strlen($username) > $max) {

            $errors[] = "Your username connot be more than {$max} Characters";
    }


    if(email_exists($email)){

            $errors[]  = "Sorry this email is already registered ";

    }

    // Verify if a username had been taken 

     if(username_exists($username)){

            $errors[] = "this username is already taken";
        
        }

    //  Verify if the  password and confirm password field match 
    if($password !== $confirm_password) {

            $errors[] = "Your password do not match";

        }

    //  Verify if the errors  message array is not empty 

     if(!empty($errors)){

        // loop trough the array to display the error message
            foreach ($errors as $error) {
                
                echo validation_errors($error);
                
            }

        } 
         else { // if the user entered correct data  register send an activation code via email to the user

            if(register_user($first_name, $last_name, $username,$password,$email)) {

                    set_message("<p class = 'bg-success text-center'> Please check your email or span spam folder for an activation link</p>");
                    
                    redirect("index.php");   // redirect the user to the main page
        }
         else // if the registration fails notify the user
        {

                    set_message("<p class='bg-danger text-center'>Sorry we could not register the user</p>");
                    
                    redirect("index.php");     

        }


    } // POST Request

} 
}// function 


/**************************** User registration ***********************************************/

/* This function is used to register the user when the user 
    information has been validated
    @params: fisrt_name, last_name, username, password , email
    @return: NA 
*/

function register_user($first_name, $last_name, $username, $password, $email){

    // call the escape function to escape the variable 
    // to prevent sql injecction 

    $first_name =  escape($first_name); 
    $last_name  =  escape($last_name);
    $username   =  escape($username);
    $password   =  escape($password);
    $email      =  escape($email);
    

    // if the user enters an email that is already taken 
    //  deny the registration

    if(email_exists($email)){
        
        return false; 
    }

    // if the user enters a username that is already taken
    // deny the registration 
    else if(username_exists($username)){

        return false; 

    }  
    else 
    {   // if the user information is verified proceed to insertion in the user table 


        // encrypt the message using password to encrypt the message 
        $password = password_hash($password, PASSWORD_BCRYPT, array('cost'=>12));

        $validation_code = md5($username . microtime()); // create random validation codes

        //  insert the user information to the user table with the validation code
        //  Also make sure that the user activation state is set to 0 for this moment 
        $sql = "INSERT INTO users(first_name, last_name, username, password ,validation_code, active, email)";
        $sql.= "VALUES('$first_name','$last_name','$username','$password','$validation_code',0, '$email')";

        $result = query($sql); // send the information

        // send a link to the user email  with the validation code
        $subject = "Activate Account";
        $msg = "Please click the link below to activate your account 
        
            <a href =\"".Config::DEVELOPMENT_URL."/activate.php?email=$email&code=$validation_code\">
            
            LINK HERE</a>"; 
            
        
        // setup a email header 
        $headers = "From: noreply@localhost";

        //  send the link
        send_email($email, $subject, $msg, $headers);

        return true;
    
    }
}


/**************************** Activate User  ***********************************************/

/* This functions is used to activate the user 
 * account after the user's registration 
*/

function activate_user(){

    // use the get request since the  user information 
    // has been post 
    if($_SERVER['REQUEST_METHOD'] == "GET"){

        if(isset($_GET['email'])) {
        
           $email = clean($_GET['email']);
           $validation_code = clean($_GET['code']);
        
            // select the user in the database      
            $sql = "SELECT id FROM users WHERE email ='".escape($_GET['email'])."' AND validation_code = '".escape($_GET['code'])."'";
            $result = query($sql); // get the result 

             // if the user exists in the database, activate the account by updating active by 1
             // Then the user is redirected to the login page to login 
            if(row_count($result)==1){

                 $sql2 = "UPDATE users SET active = 1, validation_code = 0 WHERE email = '".escape($email)."' AND validation_code = '".escape($validation_code)."'";
                 $result2 = query($sql2);

                 set_message("<p class='bg-success'> Your account has been activated please login</p>");
            
                 redirect("login.php");
            }
             else{ // if the user is not activated display an error message
                    // and redirect the user to the login page

                set_message("<p class='bg-danger'> Sorry Your account has not been activated</p>");
            
                 redirect("register.php");  

        } 
    }

} // GET 

} // function 


/*********************** Validate user login function **************************/

// This  function is used as double checker 
// for the user authentication

function validate_user_login(){

    $errors = [];
    $min = 3; 
    $max = 20; 
    
    if($_SERVER['REQUEST_METHOD']== "POST") {

          $email    = clean($_POST['email']);
          $password = clean($_POST['password']);
          $remember = isset($_POST['remember']);
         
          if(empty($email)){

            $errors[] = "Email field cannot be empty";

          } 
          else if(empty($password)){

                $errors[] = "Password field cannot be empty";
             
          }


     if(!empty($errors)){

            foreach ($errors as $error) {
                
                echo validation_errors($error);      
            }
            
        } else {
 
            if(login_user($email, $password, $remember)){

                redirect("admin.php");
            } 
            else{
                                
                echo validation_errors("Your cridentials are not correct");
           }

        }

    }

} // function 

 /*********************** User Login ***********************************/
//
 function login_user($email, $password, $remember){


    // select the user password
    $sql = "SELECT password, id FROM users WHERE email = '".escape($email)."' AND active = 1";

    // get data
    $result = query($sql);

    // if the user is found  proceed to the authentication 
    if(row_count($result) == 1) {

         $row = fetch_array($result);

         $db_password = $row['password'];
        

         if(password_verify($password, $db_password)){

            setcookie('email', $email, time() + 86400); // set the cookie for a day 
    
        } 

        $_SESSION['email'] = $email;
    
        return true;

    } else {

        return false; 
    }


 } // end of function 
 

 /*************************Login Function ******************************/

 // This function is used to keep the user logged in 
 function logged_in(){

    // if the user loging session is active  return true 
    // otherwise the user needed to reenter the critentials
    if(isset( $_SESSION['email']) || isset($_COOKIE['email'])){

        return true ;

    }
     else
     {

        return  false; 
    }
 } // end function 


 /***********************Recover password **********************/

 // This function is used for the password recovery 
 //  basically a link is sent to the user email 
 // when the link is clicked. The user will 
 // be redirected to a page to enter the new 
 // validation code, so if the code is right the user
 // directed the the password reset page
 function recover_password(){

    // verify if the request method
    if($_SERVER['REQUEST_METHOD'] == "POST")
    {

        if(isset($_SESSION['token']) && $_POST['token'] === $_SESSION['token'])
        {

            $email = clean($_POST['email']);

            if(email_exists($email)){ // if the email exists proceed the password recovery 

                $validation_code = md5($email . microtime()); // generate random validation code 

                setcookie('temp_access_code', $validation_code, time() + 900);  // set the cookie for 15 minutes 

                // update the validation code for the password recovery 
                $sql ="UPDATE users SET validation_code = '".escape($validation_code)."' WHERE email = '".escape($email)."'";
                $result = query($sql);
            

                
                $subject = "Please reset your password";
                $message = "<h2>Please reset your password with this code</h2> <h1>{$validation_code}</h1>
                Please click her to reset your password 
                                <a href=\"http://localhost/login2/code.php?email={$email}&code={$validation_code}\">http://localhost/login2/code.php?email={$email}&code={$validation_code}</a>";
                
                $headers = "From: noreply@localhost";

                // send the validation code to the user email 
                send_email($email, $subject, $message, $headers);

                set_message("<p class='bg-success text-center'>Please check your email or spam folder for a password reset code</p>");

                redirect("index.php");

            }

            else
             {
            
                echo validation_errors("This email does not exist ");
            
            }

        }
        
       else 
        {

            redirect("index.php");

       }

        //  token checks 
       // if(isset($_POST['cancel_submit'])){
            
         //   redirect("login.php");
//
        //}


    } // post request


 } // function 


/***********************Code validation**********************/

// This function is used to  make sure that if the session 
// expired the user is redirected the index page 
function validation_code(){

    if( isset($_COOKIE['temp_access_code'])){ 


        // if the session  expired redirect the user to the index page 
        // even if the email field is empty  redirect the user to 
        // the main page
        if(!isset($_GET['email']) && !isset($_GET['code'])){

                redirect("index.php");

        } 
        else if(empty($_GET['email'] || empty($_GET['code']) )) {

                  redirect("index.php");
        }

        else{ // match the code entered in to the one in the database

            if(isset($_POST['code'])){ // if the the code matches redirect the user to the reset page 
                                        // otherwise display an error message.

                $email = clean($_GET['email']); 

                $validation_code =  clean($_POST['code']);

                $sql = "SELECT id FROM users WHERE validation_code = '".escape($validation_code)."' AND  email = '".escape($email)."'";
                $result = query($sql);
                

                if(row_count($result) == 1 ){

                    setcookie('temp_access_code', $validation_code, time() + 300);

                    redirect("reset.php?email={$email}&code={$validation_code}");

                } else{

                     echo validation_errors(" Sorry wrong validation code");
                }

            }
        }


    } 
    else
    {
        
        set_message("<p class='bg-danger text-center'>Sorry Your validation code is expired. Please Try again</p>"); 
        redirect("recover.php");

    }
    
}

/***********************Password Reset function**********************/

// This function is finally used to reset the user the user 
// password 
function password_reset(){

    // if the cookie is still active  get the email 
    //  if the email and the validation code are set 
    if(isset($_COOKIE['temp_access_code'])) {

        if(isset($_GET['email']) && isset($_GET['code'])){

            if(isset($_SESSION['token']) && isset($_POST['token'])) {
            
            // If all the conditions are verified  update the user information 
        
            if ( $_POST['token'] === $_SESSION['token'])  {

                if( $_POST['password'] === $_POST['confirm_password'] ){ 

                
		                
                //$updated_password = $_POST['password']
			
		$updated_password = password_hash($_POST['password']  , PASSWORD_BCRYPT, array('cost'=>12));

                 $sql = "UPDATE users SET password = '".escape($updated_password)."', validation_code = 0, active = 1 WHERE email = '".escape($_GET['email'])."' ";
                 query($sql);


                set_message("<p class='bg-success text-center'>Your password has been updated, Please login</p>"); 

                    redirect("login.php");

                } else{ // otherwise display an error message

                    echo validation_errors("Password fields do not match. Please try again");
                }

            }

            
         }

     }

    }
    else 
    { // If the conditions are false redirect the user to the recovery page   

         set_message("<p class='bg-danger text-center'>Sorry your time expired</p>");
         
         redirect("recover.php");
    }


}
?>
