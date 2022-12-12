<?php


 // require_once __DIR__ . '/../Config.php';
// require_once __DIR__ . '/../vendor/autoload.php';

//require_once("Mail.php");

// require __DIR__ . '/../vendor/autoload.php';
// use Twilio\Rest\Client;

  

//$db = new DB("127.0.0.1", "pdo_tb", "root", "");
//$db = new DB("localhost", "smylekmo_demo", "smylekmo_demo1", "china419");

require_once("../mydatetotime.php");
date_default_timezone_set('africa/lagos');

function RemoveSpecialChar($str) {

      // Using str_replace() function
      // to replace the word
      $res = str_replace( array( '\'', '"',
      ',' , ';', '<', '>' ), ' ', $str);
 
      // Returning the result
      return $res;
}

function check_input($input,$type){
     if (preg_match('/[\'"^£$%&*()}{@#~?><>,|=_+¬-]/', $input)){
    // one or more of the 'special characters' found in $string
          echo json_encode(array('status'=>'error','msg'=>'Special characters not allowed '.$type));
          die();
     }else{
        return $input;
      }
      
}

function check_if_empty($input_name){
    if (empty(trim($input_name)) ){
        echo json_encode(array('status'=>'error','msg'=>'Fill in all input'));
          die();
    }

}


              

if ($_SERVER['REQUEST_METHOD'] == "GET") {
       if ($_GET['url'] == "auth") {
                $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));

                   Mail::sendMail("<h1>GGG</h1>!", "<h1><a href='http://localhost/social-network-part47/change-password.php?token=$token'>http://localhost/tutorials/sn/change-password.php?token=$token</a> nice</h1>", 'femismyle@gmail.com');
                   echo 'Email sent!';

        } 
        
       elseif ($_GET['url'] == "get_details") {
                  
                   $token = $_COOKIE['Passwordhash'];
                   $loggedinUser = $db->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($token)))[0]['user_id'];
                  $query=$db->query('SELECT user_id,site_name,token,datee  FROM pwd_details WHERE user_id=:id ORDER by id DESC',array(':id'=>$loggedinUser));
                   echo json_encode($query);
            
    }
       


  }    elseif ($_SERVER['REQUEST_METHOD'] == "POST") {
                  if ($_GET['url'] == "signup") {
                          $postBody = file_get_contents("php://input");
                          $postBody = json_decode($postBody);

                            // $email = $postBody->email;
                            $username = $_POST['usn'];
                            $password = $_POST['pwd'];
                            $fn = $_POST['fn'];
                            $email = $_POST['email'];
                            $array=array();

                            

                              check_if_empty($username);
                              check_if_empty($fn); 
                              check_input($fn,' for Name');
                              check_if_empty($password);
                              check_if_empty($email);

                            if (preg_match(" /^[0-9a-zA-Z_]{1,}$/", $username) ===0) {
                                  echo json_encode(array('status'=>'error','msg'=>'Invalid username characters'));
                                 die();         
                            }
                            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                  echo json_encode(array('status'=>'error','msg'=>'Invalid email address'));
                                 die();         
                            }
                            if (strlen($password) <= 7 OR strlen($password) >= 60) {
                                  echo json_encode(array('status'=>'error','msg'=>'Your password must contain atleast 8 characters'));
                                 
                                 die();         
                                      

                            }
                            if ($db->query('SELECT username FROM users WHERE username=:username', array(':username'=>$username))) {
                                  echo json_encode(array('status'=>'error','msg'=>'Username already taken'));
                                 die();   
                            }
                        
                          if ($db->query('SELECT email FROM users WHERE email=:email', array(':email'=>$email))) {
                                 echo json_encode(array('status'=>'error','msg'=>'Email already taken'));
                                 die();   

                          }

                          $db->query('INSERT INTO users VALUES (\'\',:fullname,:username,:email,:password)', array(':fullname'=>$fn,':username'=>$username, ':email'=>$email, ':password'=>password_hash($password, PASSWORD_BCRYPT)) );
                              echo json_encode(array('status'=>'success','msg'=>'Account created'));
                              die();


                  }elseif ($_GET['url'] == "login") {

                            $password = $_POST['pwd'];
                            $username = $_POST['email'];
              
                            check_if_empty($username);
                            check_if_empty($password);
                            


                              $username=preg_replace('/\s+/', '', $username);
                              $password = preg_replace('/\s+/', '', $password);
                               if ($db->query('SELECT username FROM users WHERE  email=:username',array(':username'=>$username))) {
                                   
                                    if (password_verify($password,  $db->query('SELECT password FROM users WHERE email=:username',array(':username'=>$username))[0]['password'])){
                                            $cstrong=True;
                                            $token=bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
                                            $user_id=$db->query('SELECT id FROM users WHERE email=:username',array(':username' =>$username ))[0]['id'];
                                             $db->query('INSERT INTO login_tokens VALUES (\'\',:token,:user_id)', array(':token' =>sha1($token), ':user_id'=>$user_id ));

                                              setcookie("Passwordhash", $token, time() + 60 * 60 *24 *7, '/', NULL,NULL, TRUE );

                                              setcookie("Password_", '1', time() + 60 * 60 *24 *3, '/', NULL,NULL, TRUE);
                                               echo json_encode(array('status'=>'success','msg'=>'Logged In'));
          
                                               die();
                                    }else{
                                        
                                        echo json_encode(array('status'=>'error','msg'=>'incorrect password'));
                                        die();
                                    }
                               }else{
                                 
                                 echo json_encode(array('status'=>'error','msg'=>'User not found'));
                                 die();
                               }
                 
                                     
              


                             
                  }elseif ($_GET['url'] == "logout") {
                                
                    

                   $token = $_COOKIE['Passwordhash'];
                   @$loggedIn = $db->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($token)))[0]['user_id'];
                   if (isset($_POST['check'])) {
                        $db->query('DELETE FROM login_tokens WHERE user_id=:userid',array(':userid'=>$loggedIn));
                    }else{
                       if (isset($_COOKIE['Passwordhash'])) {
               
                      $db->query('DELETE FROM login_tokens WHERE token=:token',array(':token'=>sha1($_COOKIE['Passwordhash'])));
                      setcookie("Passwordhash", '1', time()-3600);
                      setcookie("Passwordhash_", '1', time()-3600);

                         }
                    }
                    echo json_encode(array('status'=>'success','msg'=>'Logged out sucessfully'));
                                 die();



                  }elseif ($_GET['url'] == "insert_details") {
                    $token = $_COOKIE['Passwordhash'];
                    $loggedinUser = $db->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($token)))[0]['user_id'];
                    $name = $_POST['web_name'];
                    $len = $_POST['pwd_len'];

                    if (empty(trim($name)) OR empty(trim($len)) ) {
                          echo json_encode(array('status'=>'error','msg'=>'Please fill all inputs'));
                          die();
                     }
                     if($len > 15 OR $len <50){
                         
                     }else{
                        echo json_encode(array('status'=>'error','msg'=>'Password length must contain 15 to 50 characters'));
                        die();
                     }



                    if (isset($_POST['check'])) {
                                 
                         $check="@";
                     }else{
                         $check="";
                     }
                      $name=check_input($name,'for website Name');
                      $len=check_input($len,'for password length');
                     
                              $chars="1234abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                              $ran_dir_name=substr(str_shuffle($chars), 0,$len);
                              $token=$check.$ran_dir_name;
                              $db->query('INSERT INTO pwd_details VALUES (\'\',:user_id,:name,:pwd,:datee)', array(':user_id' =>$loggedinUser, ':name'=>$name, ':pwd'=>$token , ':datee'=>date('y-m-d') ));
                              echo json_encode(array('status'=>'success','msg'=>'Added'));
                            

                  }

  }
