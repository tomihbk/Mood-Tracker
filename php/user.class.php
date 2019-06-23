<?php
class User{
    public function  __construct() {
	}

    private function connectDB(){
        try{
            //Pour établir une co avec notre db
            $dbh = new PDO('mysql:dbname=mydb;host=localhost;port=3306','root','',array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
        }catch(Exception $e){
            echo $e->getMessage();
        }
        return $dbh;
    }

    // Cette fontion sert à verifier si l'utilisateur existe dans la db
    function checkLogin($username){
        $dbh = $this->connectDB();
        $sql = "SELECT userName FROM tblusers where userName = :loginName";

        $stmt = $dbh->prepare($sql);
        $stmt->execute(array(':loginName'=>$username));

        if($stmt->fetch() == null){
            return false;
        }else{
            return true;
        }
    }

    // Pour ajouter un utilisateur dans la db
    function addUser($username){
        $dbh = $this->connectDB();
        $sql = "SELECT * FROM tblusers where userName like :username";

        $stmt = $dbh->prepare($sql);
        $stmt->execute(array(':username'=>$username));

        if($stmt->rowCount() <= 0){// Si l'utilisateur n'existe pas, on l'ajoute
            $sql = "INSERT INTO tblusers (`userName`) VALUES (:newUser)";
            $stmt = $dbh->prepare($sql);
            $stmt->execute(array(':newUser'=>$username));
            echo('<label class="success">Utilisateur '.$username.' ajouté</label>');
        }else{
            echo "<label class='error'>Utilisateur ".$username." existe déjà</label>";
        }        
    }

    // Pour ajouter l'état du jour
    function addLog($username,$date,$mood){
        $dbh = $this->connectDB();
        $sql = "SELECT userId FROM tblusers
            where userName like :loginName";

        $stmt = $dbh->prepare($sql);
        $stmt->execute(array(':loginName'=>$username));
        $userId = $stmt->fetch();

        $sql = 'SELECT * FROM tblmoodtracker where tblUsers_userId=:userId and trackerDate=:dat';
        $stmt = $dbh->prepare($sql);
        $stmt->execute(array(':userId'=>$userId['userId'],':dat'=>$date));
        
        if($stmt->rowCount() <= 0){ //Si dans cette date, il n'y pas de donnée, on ajoute dans la liste sinon, mettre à jour les données
            $sql = "INSERT INTO tblmoodtracker VALUES (null,:userId, :mood, :dat)";
            $stmt = $dbh->prepare($sql);
            $stmt->execute(array(':userId'=>$userId['userId'],':mood'=>$mood,':dat'=>$date));
        }else{
            $logId = $stmt->fetch();
            $sql = 'UPDATE tblmoodtracker SET tblMoods_moodId = :mood WHERE trackerId = :logId';
            $stmt = $dbh->prepare($sql);
            $stmt->execute(array(':mood'=>$mood,':logId'=>$logId['trackerId']));
        }
        
    }

    // Pour effacer des données
    function deleteLog($id){
        $dbh = $this->connectDB();
        $sql = 'DELETE FROM tblmoodtracker WHERE trackerId = :trackId';
        $stmt = $dbh->prepare($sql);
        $stmt->execute(array(':trackId' => $id));
    }

    // Retourne une liste des entrées, c'est un sort d'historique 
    function listHistory($username){
        $dbh = $this->connectDB();
        $sql = 'SELECT trackerId,userName, moodName, trackerDate FROM tblmoodtracker
        inner join tblusers on tblUsers_userId = userId
        inner join tblmoods on tblMoods_moodId = moodId
        where userName like :username
        order by trackerDate DESC
        limit 10';

        $stmt = $dbh->prepare($sql);
        $stmt->execute(array(":username"=>$username));

        $res = "<table><tr><td colspan='3' style='text-align:center' class='history'><b>Historique (10 jours)</b></td></tr><tr><th>Date</th><th>Mood</th></tr>";
        foreach($stmt as $row){
            $date = date_create($row['trackerDate']);// On "crée" la date qui sera convertit en d-m-Y (par défaut Y-m-d)
            $res .= "<tr><td>".date_format($date, 'd-m-Y')."</td><td>".$row['moodName']."</td><td><a href='?delete=".$row["trackerId"]."'><span class='delete'>x</span></a></tr>";
        }
        $res .="</table>";

        return $res;
    }

    // Ici, on va retourné l'état du mood, l'utilisateur choisi la date
    function searchDate($username,$date){
        $dbh = $this->connectDB();
        $sql = 'SELECT trackerId,userName, moodName, trackerDate FROM tblmoodtracker
        inner join tblusers on tblUsers_userId = userId
        inner join tblmoods on tblMoods_moodId = moodId
        where userName like :username and trackerDate=:dat';

        $stmt = $dbh->prepare($sql);
        $stmt->execute(array(":username"=>$username, ":dat"=>$date));

        if($stmt->rowCount() <= 0){
            $res = "<p class='searchResult'>Aucun enregistrement existe pour la date cherchée.</p>";
        }else{
            $res = "<table class='searchResult'>";
            foreach($stmt as $row){
                $date = date_create($row['trackerDate']);
                $res .= "<tr><td>".date_format($date, 'd-m-Y')."</td><td>".$row['moodName']."</td><td><a href='?delete=".$row["trackerId"]."'><span class='delete'>x</span></a></tr>";
            }
            $res .="</table>";
        }
        return $res;
    }

}
?>