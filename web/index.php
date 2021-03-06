<?php

/**
* FacebookUtils test
* Some utilities for use with the Facebook PHP SDK.
* https://github.com/jonasmonnier/FacebookUtils
*
* @author Jonas
* @version 0.1.7
* @date 2013-02-11
* 
*/

error_reporting(E_ALL);
ini_set("display_errors", 1); 

require '../src/facebook/facebook.php'; // PHP SDK

require '../conf/index.conf.php';

require '../src/jonas/Utils.php';
require '../src/jonas/TransSID.php';

require '../src/jonas/facebook/FBLogin.php';
require '../src/jonas/facebook/FBSignedRequest.php';
require '../src/jonas/facebook/FBPerms.php';
require '../src/jonas/facebook/FBSessionUtil.php';

use jonas\Debug;
use jonas\facebook\FBSignedRequest;
use jonas\facebook\FBLogin;
use jonas\facebook\FBAppType;
use jonas\facebook\FBPerms;

// Debug
Debug::$ACTIVE = true;

// Safari fix 
TransSID::$DEBUG = true;
TransSID::$SAFARI_ONLY = false;
TransSID::init();


// Init Facebook PHP SDK
$facebook = new Facebook(array(
  'appId'  => APP_ID,
  'secret' => APP_SECRET
));

// Get page index
if(isset($_REQUEST['page'])){
    $page = urlencode($_REQUEST['page']);
    $use_session = true;
}else{
    $page = 0;
    $use_session = false;
}

// Init SignedRequest
$request = new FBSignedRequest($facebook, $use_session);
/*
if(!$use_session)
    $request->clear(); // Clear session
*/
$request->load();

switch($request->getAppType())
{
    case FBAppType::CANEVAS :
    $appURL = APP_CANVAS_URL;
    break;
    
    case FBAppType::PAGE_TAB :
    $appURL = APP_PAGE_TAB_URL;
    break;
    
    case FBAppType::WEBSITE :
    $appURL = APP_WEBSITE_URL;
    break;
}

// Init FacebookSession
$session = new FBLogin($facebook, $use_session);
$session->setAppURI($appURL);
$session->setScope(array(
    FBPerms::publish_stream,
    FBPerms::email
));

/*
if(!$use_session)
    $session->clear();  // Clear session
*/
$session->load();

?>

<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>FBUtils</title>
    <style>
      body {
        font-family: 'Lucida Grande', Verdana, Arial, sans-serif;
        margin: 0 0 0 30px;
      }
      
      h1 {
        font-size:20px;
      }
      
      h2 {
        font-size:14px;
        background-color: #cccccc;
        padding: 10px 10px 10px 10px;
        margin-top: 40px;
        width: 300px; 
      }
      
      h2 a {
        text-decoration: none;
        color: #3b5998;
        
      }
      h2 a:hover {
        text-decoration: underline;
      }

      #debug-control {
          top:0px;
          right:0px;
          position:fixed;
          float:right;
          width:120px;
          height:20px;
          background-color: #cccccc;
          text-align: left;
      }

      #debug-control span {
          margin-left: 5px;
      }


      #debug-panel {
          top: 0px;
          right: 120px;
          position: fixed;
          background-color: #cccccc;
          display: none;
          width:400px;
          height:400px;
      }

      #debug-panel div {
          margin: 15px 15px 15px 15px;
      }

    </style>


  </head>
  <body>
  <script>
      function toggleDebug(){document.getElementById("debug-panel").style.display = (document.getElementById("debug-panel").style.display == 'none' ? 'block' : 'none');}
  </script>
    <div id="debug-control" onclick="toggleDebug()"><span>Debug</span></div>
    <div id="debug-panel">
        <div><?php echo Debug::$message; ?></div>
    </div>

    <h1>FacebookUtils 
<?php 
    if($page !=0) 
        echo ' > page '.$page;
?> 
    </h1>
    <p><a href="https://github.com/jonasmonnier/FacebookUtils" target="_blank">Source</a></p>
    <?php
        $u = 'index.php?page='.($page+1);
        $u = TransSID::getURL($u); 
    ?>

    <a href="<?php echo $u ?>">Next page</a><br/><br/>
<?php
        
        
        // Error
        /*
        if($utils->hasError()){
            echo '<h2>Error</h2><pre>';
            $error = $utils->getError();
            echo $error->getError().'<br/>';
            echo 'Reason : '.$error->getErrorReason().'<br/>';
            echo 'Description : '.$error->getErrorDescription().'<br/>';
            echo '</pre>';
        }
        */
        
        // Check auth
        echo '<h2>Is Auth ?</h2><pre>';
        if($session->isAuth())
            echo 'Yes<br/><a href="https://www.facebook.com/settings/?tab=applications" target="_blank">Change</a><br/>';
        else
            echo 'No<br/><a href="'.$session->getLoginURL().'" target="_parent">Change</a><br/>';
        echo '</pre>';
        
        // Check permissions
        echo '<h2>Has publish Stream Permission ?</h2><pre>';
        if($session->hasPermission(FBPerms::publish_stream))
            echo 'Yes<br/><a href="https://www.facebook.com/settings/?tab=applications" target="_blank">Change</a><br/>';
        else
            echo 'No<br/><a href="'.$session->getLoginURL().'" target="_parent">Change</a><br/>';
        echo '</pre>';
        
        // Check permissions
        echo '<h2>Has email Permission ?</h2><pre>';
        if($session->hasPermission(FBPerms::email))
            echo 'Yes<br/><a href="https://www.facebook.com/settings/?tab=applications" target="_blank">Change</a><br/>';
        else
            echo 'No<br/><a href="'.$session->getLoginURL().'" target="_parent">Change</a><br/>';
        echo '</pre>';
        
        // Show user id
        echo '<h2>User ID</h2><pre>';
        echo $session->getUserID();
        echo '</pre>';

        // Show app type
        echo '<h2>App type</h2><pre>';
        echo $request->getAppType();
        if($request->isPageTab()){
            echo ' (liked = '.($request->isPageLiked() ? 'true' : 'false').')';
        }
        echo '</pre>';
        
        // Show request
        echo '<h2>Request</h2>
        GET
        <pre>';
        print_r($_GET);
        echo '</pre>';
        
        echo 'POST
        <pre>';
        print_r($_POST);
        echo '</pre>';
        
        echo 'COOKIE
        <pre>';
        print_r($_COOKIE);
        echo '</pre>';
        
        // Show signed request
        echo '<h2>Signed request (source = '.$request->getSource().')</h2><pre>';
        if($request->hasData())
            print_r($request->getData());
        else
            echo 'Not defined';
        echo '</pre>';
        
        
        
        // Show user data
        echo '<h2>User data (source = '.$session->getSource().')</h2><pre>';
        if($session->isAuth())
            print_r($session->getUserData());
        else
            echo 'Needs auth';
        echo '</pre>';
        
        
        // Show user permissions
        echo '<h2>User permissions</h2><pre>';
        if($session->isAuth())
            print_r($session->getUserPermissions());
        else
            echo 'Needs auth';
        echo '</pre>';
        
        
        // Show session
        echo '<h2>Session</h2><pre>';
        print_r($_SESSION);
        echo '</pre>';
        
        
        echo '<h2>Browser</h2><pre>';
        echo Browser::getUserAgent() . "\n\n";
        echo 'Safari : '.(Browser::isSafari() ? 'true' : 'false'); 
        /**
        try {
            $browser = get_browser(null, true);
            print_r($browser);
        }catch(Exception $e){
            //echo $e->getMessage();
        }
        */
        echo '</pre>'; 
        
        
        // Demos
        /*
        echo '<h2>Demos</h2><pre>';
        $apps = $utils->getAppURI();
        echo '<a href="'.$apps[FacebookAppType::WEBSITE].'" target="_blank">Website demo</a><br/>';
        echo '<a href="'.$apps[FacebookAppType::CANEVAS].'" target="_blank">Canevas demo</a><br/>';
        echo '<a href="'.$apps[FacebookAppType::PAGE_TAB].'" target="_blank">PageTab demo</a><br/>';
        echo '</pre>';
        */
        
        ?>
        
  </body>
</html>
