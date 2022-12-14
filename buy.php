<?php session_start();

$err = "";
$notify="Enter Passcode and Click on buy to get the product.";

if(!isset($_SESSION['userid']) or !isset($_SESSION['username'])){
  header('location:index.php');
}

if(isset($_POST['buy'])){
  require_once('connect.php');
  $passkey = $_POST['buykey'];
  $pid = $_POST['buy'];
  // echo $passkey.$pid;
  $stmt2 = $con->prepare("SELECT buyingkey FROM `products` WHERE p_id=?");
  $stmt2 -> bind_param('i', $pid);
  $stmt2 -> execute();
  if($res2 = $stmt2->get_result()){
    if($row2 = $res2->fetch_assoc()){
      if($row2['buyingkey'] == $passkey){

        $findby= "";
        include_once('validation.php');
        if(validate_email($_SESSION['userid'])=='good')
          $findby = "email";
        else if(validate_roll($_SESSION['userid'])=='good')
          $findby = "roll";
        
        $stmt1=$con->prepare('SELECT roll FROM `users` WHERE '.$findby.'=?');
        $stmt1->bind_param('s',$_SESSION['userid']);
        $stmt1->execute();
        $res1 = $stmt1->get_result();
        $stmt1->close();
        if($row1 = $res1->fetch_assoc()){
          $stmt3 = $con->prepare("UPDATE `products` SET `ownerroll` = ?, `status`= 'cancelled' WHERE p_id = ?");
          $stmt3->bind_param('si',$row1['roll'], $pid);
          $stmt3->execute();
          $res3 = $stmt3->get_result();
          // $pname = "You Bought ".$row2['name'];
          echo '<script>alert("You Bought it.")</script>';
          $stmt3->close();
        }
      }else{
        $err = "wrong passcode";
        $notify = "";
      }
    }
  }
  $stmt2->close();
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>buy&sell</title>
	<!-- Meta Tags -->
  <meta charset="utf-8">
  <meta html-equiv = "X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="css/bootstrap/bootstrap.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" type="text/css" href="css/buy.css" media="screen">
  <!-- Symbol -->
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- STYLE -->
  <style type="text/css">
  	@import url('https://fonts.googleapis.com/css2?family=Philosopher&display=swap');
  </style>
</head>
<body>
	<!-- HEADER -->
  <header>
    <div class="text-center"> <a href="./buy.php">BUY</a> and <a href="./sell.php">SELL</a>
      <div class="sym user text-secondary"> <div class="hide"><?php echo $_SESSION['username']; ?></div>
        <a href="logout.php">
          <i class="fa text-secondary">
          &nbsp; &#xf08b;
          </i>
        </a>
      </div>
    </div>
  </header>
<!-- MAIN CONTAINT -->
<br><br><br><br>
<?php
  require_once('connect.php');
  
  $stmt=$con->prepare('SELECT * FROM products');
  $stmt->execute();
  $res = $stmt->get_result();
  $stmt->close();

?>
  <div class="container">
    <p style="color:red"><b><?php echo $err; ?></b></p>
		<p style="color:green"><b><?php echo $notify; ?></b></p>
    <div class="row">
<?php
    while($row = $res->fetch_assoc()){
      if($row['status']=='available'){
?>
        <div class="col-md-5 card">
<?php
          $imgname = $row['img'];
          if(strlen($row['img'])>0){
            echo "<img class='card-img-top' src='up/$imgname' width='100' height='300' alt='NO IMAGE'>";
          }else{
            echo '<img class="card-img-top" src="up/default.png" width="100" height="300">';
          }
?>
          <div class="card-body">
            <hr><b>
            <?php echo $row['name'];?>
            </b><hr>
<?php
            $stmt1 =$con->prepare("SELECT contact, roll FROM users WHERE roll = ? ");
            $stmt1->bind_param("s", $row['ownerroll']);
            $stmt1->execute();
            $res1 = $stmt1->get_result();
            $stmt1->close();
            $row1 = $res1->fetch_assoc();
            if($row1 && $row['ownerroll'] == $row1['roll'])
              echo '<b>CONTACT : </b>'.$row1['contact'];
            else
              echo '<b>CONTACT : </b> 9525177622';
?>
            <hr>
            <?php echo $row['details']; ?>
            <form method="POST" action="#">
              <div class="buythings">
                <input type="text" id="buykey" class="form-control" placeholder="give pass key" name="buykey" style="width: 70%;" required />
                <button name="buy" value="<?php echo $row['p_id']; ?>" value="<?php echo $row['p_id']; ?>" type="submit"> 
                  &#8377; <?php echo $row['price']; ?> buy
                </button>
              </div>
            </form>
          </div>
        </div>
<?php 
      }
    }
?>
    </div>
  </div>
<!-- FOOTER -->
  <footer>2022 &copy; Made with <font color="red">&hearts;</font> by <a href="https://github.com/aniket532015">Aniket Kumar</a>  </footer>
</body>
</html>
