<?php
$starter_cash=100;
$starter_coins=1000;
$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/php/session.class.php";
include_once($path);
$sess = new Session();
$sess->Init();
if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email']) && isset($_POST['confirm_password']) && isset($_POST['birthday']) && $_POST['password']==$_POST['confirm_password'] && $_POST['birthday']!='')
{
    $username = $_POST['username'];
    $password = base64_encode($_POST['password']);
    $confirm_password = $_POST['confirm_password'];
    $email = $_POST['email'];
    $birthday = $_POST['birthday'];
    $conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_TABLE);
    if ($conn->connect_error)
    {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
        echo 'unable to connect to database';
    }
    
    $query = "SELECT username FROM accounts WHERE username = ?";
    $stmt = $conn->prepare($query);
    if ($stmt)
    {
        $stmt->bind_param("s", $username); /* Bind parameters. Types: s = string, i = integer, d = double,  b = blob */
        $stmt->execute();
        $stmt->bind_result($username_result);
        $stmt->fetch();
        $stmt->close();
        if($username!=$username_result)
        {
            $id = "SELECT MAX(ID) FROM sqlserver.accounts";
            $id = $conn->query($id);
            $id = $id->fetch_assoc()["MAX(ID)"];
            $id+=1;
            $ip=$_SERVER['REMOTE_ADDR'];
            $cookie=generateRandomString(100);
            $query = "INSERT INTO accounts (`id`, `username`, `password`, `birthday`, `cookie`, `ip`, `email`,`cash`,`coins`) VALUES (?,?,?,?,?,?,?,?,?)";
            $stmt = $conn->prepare($query);
            if ($stmt)
            {
                $stmt->bind_param("issssssii",$id,$username,$password,$birthday,$cookie,$ip,$email,$starter_cash,$starter_coins); /* Bind parameters. Types: s = string, i = integer, d = double,  b = blob */
                $stmt->execute();
                $stmt->close();
                setcookie("session",$cookie,time()+3600*24,"/");
                $social = "INSERT INTO social (`id`, `username`, `cookie`) VALUES (?,?,?)";
                $stmt = $conn->prepare($social);
                if ($stmt)
                {
                    $stmt->bind_param("iss",$id,$username,$cookie); /* Bind parameters. Types: s = string, i = integer, d = double,  b = blob */
                    $stmt->execute();
                    $stmt->close();
                    $social = "INSERT INTO inventory (`id`, `username`, `cookie`) VALUES (?,?,?)";
                    $stmt = $conn->prepare($social);
                    if ($stmt)
                    {
                        $stmt->bind_param("iss",$id,$username,$cookie); /* Bind parameters. Types: s = string, i = integer, d = double,  b = blob */
                        $stmt->execute();
                        $stmt->close();
                        echo 'success';
                    }
                }
            }
        }
        else
        {
            echo 'username taken';
        }
    }
    else
    {
        trigger_error('Statement failed : ' . $stmt->error, E_USER_ERROR);
    }
        $conn->close();
}
else
{
    header('Location: /');
}

function generateRandomString($length)
{
		$characters='1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
}
?>