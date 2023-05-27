<?php
//Akiko Koyama 
// 2023-04-23 
function display_html_header($title=""){
    /*displays the html header, title parameter is optional */
        echo <<<HEADER
        <!DOCTYPE html>
        <html>
        <head>
        <title>$title</title>
        <link rel="stylesheet" href="style.css"/>
        </head>  
        <body>  
        HEADER; 
    }
    function display_html_footer(){
    /*displays the html footer */
        echo <<<FOOTER
        </body>
        </html>
        FOOTER; 
    }
    
    
    function validate_form(){
    /**Validates form input, returns a list of error(s) if any */
        $errors=[];
        //validates pid
        if(isset($_POST['pid'])){       //!!!! 'pid'   
            if(filter_input(INPUT_POST, 'pid',FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]])){
                $_SESSION['PID']=$_POST['pid'];
            }
            else{
                $errors[0]="PID must be a positive interger";
            }
        }


        //validate name
        if(isset($_POST['name'])){
            if(trim($_POST['name']=="")){
                $errors[1]="Name cannot be empty";
            }
            else{
                $_SESSION['Name']=$_POST['name'];
            }
        }
        
        //valudate team names
        if(isset($_POST['teams'])){
            $validTeams=['U10', 'U11','U12'];
            if(!isset($_POST['teams']) || !in_array($_POST['teams'], $validTeams)){
                $errors[2]="Invalid team name";
            }
            else{
                $_SESSION['Team Name']=$_POST['teams'];
            }        
        }
       
        //validates gender
        if(isset($_POST['gender'])){
            $validGender=['M', 'F', 'X'];
            if(!in_array($_POST['gender'], $validGender)){
                $errors[3]="Invalid gender input";
            }
            else{
                $_SESSION['Gender']=$_POST['gender'];
            }
        }
    
        //vaidates sports by calling another function validate_sports_input
        if(isset($_POST['sports'])){
            if(validate_sports_input($_POST['sports'])===false){
            $errors[4]="Invalid sports input";
        } 
        else{
            $_SESSION['Favorite Sports']=$_POST['sports'];
        }  
        }
        //validates that all fields are inputed
        if(!isset($_POST['pid']) || !isset($_POST['gender']) || !isset($_POST['teams']) || !isset($_POST['sports'])){
            $errors[5]="Please enter all fields";
        }
        return $errors;
        
    }
    
    function validate_sports_input($array){
    /**Called inside validate_form function to validate sports input(s) */
        $validSports=['sc','tc','sw','bb'];
        $counter=0;
        foreach($array as $sport){
            if(!in_array($sport, $validSports)){
                $counter++;
            }
        }
        if($counter>0){
            return false;
        }
    }
    
    function display_form($errors=[] ){
        /**Displays the form and error messge(s) if any */
        //pid
        echo " <form method='POST' action='$_SERVER[PHP_SELF]''>
                <label>PID</label><input type='number' name='pid' value='".($_SESSION['PID'] ?? ''). "' />";
        if(isset($errors[0])){
            echo "<span class='err' > $errors[0] </span><br/>";
        }else echo "<br/>";
        
        //name
        echo "<label>Name</label><input type='text' name='name' value='".($_SESSION['Name']??"")."' />";
        if(isset($errors[1])){
            echo "<span class='err' > $errors[1] </span><br/>";
        }else echo "<br/>";
    
        //team names  
        echo "<label>Team Name</label> <select name='teams'>";
        echo "<option value='U10' ".(($_SESSION['Team Name'] ?? '')=='U10' ? 'Selected':'')." >U10</option>";
        echo "<option value='U11' ".(($_SESSION['Team Name'] ?? '')=='U11' ? 'Selected':'')." >U11</option>";
        echo "<option value='U12' ".(($_SESSION['Team Name'] ?? '')=='U12' ? 'Selected':'')." >U12</option>";
        echo "</select> <br/>";
        if(isset($errors[2])){
            echo "<span class='err' > $errors[2] </span>";
        }
        //gender 
        echo " <label>Gender</label> <br/> ";
        echo " <input type='radio' name='gender' value='M' ".(($_SESSION['Gender'] ?? '' ) =='M' ? 'Checked':''). " /><label>Male</label> <br/>";
        echo " <input type='radio' name='gender' value='F' ".(($_SESSION['Gender'] ?? '' ) =='F' ? 'Checked':''). " /><label>Female</label> <br/>";
        echo " <input type='radio' name='gender' value='X' ".(($_SESSION['Gender'] ?? '' ) =='X' ? 'Checked':''). " /><label>Other</label> <br/>";
        if(isset($errors[3])){
            echo "<span class='err' > $errors[3] </span>";
        }
        //sports
        echo " <label>Favorite Sports</label><br/>";
        echo " <input type='checkbox' name='sports[]' value='sc' ".(in_array('sc',($_SESSION['Favorite Sports'] ?? [] ))  ? 'Checked':''). " /><label>Soccer</label> <br/>";
        echo " <input type='checkbox' name='sports[]' value='tc' ".(in_array('tc',($_SESSION['Favorite Sports'] ?? [] ))  ? 'Checked':''). " /><label>Tennis</label> <br/>";
        echo " <input type='checkbox' name='sports[]' value='sw' ".(in_array('sw',($_SESSION['Favorite Sports'] ?? [] ))  ? 'Checked':''). " /><label>Swimming</label> <br/>";
        echo " <input type='checkbox' name='sports[]' value='bb' ".(in_array('bb',($_SESSION['Favorite Sports'] ?? [] ))  ? 'Checked':''). " /><label>Basket Ball</label> <br/>";
        if(isset($errors[4])){
            echo "<span class='err' > $errors[4] </span>";
        }
        echo " <input type='submit' name='submit' value='Submit'/></form>";
        // error message for any missing input
        if(isset($errors[5])){
            echo "<span class='err' > $errors[5] </span>";
        }
    }
    
    function confirm_form(){
        /** Displays confirmation page. Displays the input values stored in Session variables */
        asort($_SESSION);
        foreach ($_SESSION as $key => $value){
            if(!is_array($value)){
                echo "<p> $key:  $value</p>";
            }else{
                echo "<p> $key:  ". implode(", ",$value)."</p>";
                //echo "<p> $key: {implode(",", $value)} </p>";
            }
        }
        echo "<form method='POST' action='$_SERVER[PHP_SELF]' >
                <input type='submit' name='submit' value='Confirm'/>
                <input type='submit' name='submit' value='Edit'/>";       
    }
    
    function process_form(){
        /** Connects to MySql database and records user inputs */
        //connect to database   
        try{
            $conn=new PDO("mysql:host=localhost", "root", "iE061714");
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e){
            echo "<p class='err'> Error: ". $e->getMessage()."</p>";
        }
        //create tables
        try{
            $sql="CREATE DATABASE IF NOT EXISTS players_db;
                    USE players_db;
                    CREATE TABLE IF NOT EXISTS Player(
                        PID INT PRIMARY KEY,
                        PName VARCHAR(20),
                        TeamName VARCHAR(3),
                        Gender CHAR(1)
                    );
                    CREATE TABLE IF NOT EXISTS Player_FavSports(
                        PID INT,
                        FavSport CHAR(2),
                        PRIMARY KEY(PID, FavSport),
                        FOREIGN KEY(PID) REFERENCES Player(PID)
                    ); ";
            $conn->exec($sql);
        }catch(PDOException $e){
            echo "<p class='err'> Error: ". $e->getMessage()."</p>";
        }
        //insert values to the database
        try{
            $insert = "INSERT INTO players_db.Player VALUES (?,?,?,?);";
            $stmt= $conn->prepare($insert);
            $stmt->execute(array($_SESSION['PID'], $_SESSION['Name'], $_SESSION['Team Name'], $_SESSION['Gender']));
            foreach($_SESSION['Favorite Sports'] as $sport){
                $insert = "INSERT INTO players_db.Player_Favsports VALUES(?,?);";
                $stmt=$conn->prepare($insert);
                $stmt->execute(array($_SESSION['PID'], $sport));
            }
            echo "<p style='color:green'> Data Inserted Successfully.</p>";
    
        }catch(PDOException $e){
            echo "<p class='err'> Error: ". $e->getMessage()."</p>";
        }
    
    } 

?>

<?php
// if (!filter_input(INPUT_GET, "email", FILTER_VALIDATE_EMAIL)) {
//     echo("Email is not valid");
// } else {
//     echo("Email is valid");
// }
// INPUT_GET
// INPUT_POST
// INPUT_COOKIE
// INPUT_SERVER
// INPUT_ENV
?>
<?php
// $int = 122;
// $min = 1;
// $max = 200;

// if (filter_var($int, FILTER_VALIDATE_INT, array("options" => array("min_range"=>$min, "max_range"=>$max))) === false) {
//   echo("Variable value is not within the legal range");
// } else {
//   echo("Variable value is within the legal range");
// }
?>
