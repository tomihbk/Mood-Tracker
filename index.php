<?php 
session_start();
require('php/user.class.php');

$user = new User();
$loginName = "";
$_SESSION["real_login"] = '';

if(isset($_POST["login"])){
  $loginName = $_POST['username'];
  $_SESSION["real_login"] = $loginName;
  
  $nameExist = $user->checkLogin($loginName);
  if($nameExist){
      $_SESSION['user_name'] = $_SESSION["real_login"];
  }else{
      echo('<label class="error">Utilisateur inconnu</label>');
  }

}
// Lorsqu'on click sur le button enregistrer
if(isset($_POST["save"])){
  $user->addLog($_SESSION['user_name'], $_POST["date"],$_POST["moods"]);
}

// Lorsqu'on click sur le button X pour effacer
if(isset($_REQUEST["delete"])){
  $user->deleteLog($_REQUEST["delete"]);
}

// Lorsqu'on click sur le button signup, un utilisateur est ajouté
if(isset($_POST['signup'])){
  $user->addUser($_POST['username']);
}

if(isset($_REQUEST["action"])){
    switch ($_REQUEST["action"]) {
        case 'logout':
            unset($_SESSION['user_name']);
            header('Location:index.php');
            break;
        default:
            break;
    }
}    

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Mood Tracker</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/uikit.almost-flat.css" />
    <link rel="stylesheet" href="css/style.css"/>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
  </head>
  <body>
    <div class="container">
    <div class="main-content uk-align-center">
    <?php 
      if(isset($_SESSION["user_name"])){
        echo('<div class="header"><div><h2 class="logo">Mood Tracker™</h2></div><div class="disconnect"><label>Bienvenue <b>'.$_SESSION["user_name"].'</b>, <a class="uk-button uk-button-danger" href="?action=logout">Se déconnecter</a></label></div></div>');
      }?>
      <div class="parent">
<?php 
      if(!isset($_SESSION["user_name"]))
        {
          echo ('
        <h1 class="welcome-title">Welcome to MoodTracker
        <img src="https://1button.co/uploads/1370097681.png" width="50%">
        </h1>
        <form method="POST" class="login-form">
          <div class="uk-margin">
              <div class="uk-inline">
                  <span class="uk-form-icon" uk-icon="icon: user"></span>
                  <input class="uk-input" type="text" name="username" placeholder="user name">
              </div>
              <button class="uk-button uk-button-primary" name="login">Login</button><p>
              <button class="uk-button uk-button-primary" name="signup">Sign Up</button>
          </div>     
      </form>
      </div>
      ');}else{
      echo('
      
      <div>
      <h2>How was your day?</h2>
          <form method="POST">
            <label>Date</label> <br/><input type="date" name="date" data-uk-datepicker="{format:"DD.MM.YYYY"}" required><p></p>
            <label>Mood</label> <div class="uk-form-controls">
                <select name="moods" class="uk-select" id="form-stacked-select">
                    <option value="1">Joie</option>
                    <option value="2">Dégoût</option>
                    <option value="3">Colère</option>
                    <option value="4">Peur</option>
                    <option value="5">Tristesse</option>
                    
                </select>
            </div>
            <button class="uk-button uk-button-primary" style="margin-top:1em;" name="save">Enregistrer</button>
        </div>
        </form>
      ');
      echo ("<div class='history-block'>");
      echo $user->listHistory($_SESSION["user_name"]);
      echo '</div>';

      
      echo ("<div class='search-block'>");
      echo ('<h3>Search my mood on :</h3>
      <form method="POST">
        <label>Date : </label><input type="date" name="searchDate" data-uk-datepicker="{format:"DD.MM.YYYY"}" required><br/>
        <button class="uk-button uk-button-primary" style="margin-top:1em;" name="search">Chercher</button>
    </form>');
    if(isset($_POST["search"])){
      echo $user->searchDate($_SESSION["user_name"],$_POST['searchDate']);
    }
      echo '</div>';
  }
?>
    </div>
    </div>
</div>
  </body>
</html>