<?php
include("config.php");
$uid = $_GET['id'];

// view code//
$sql = "SELECT * FROM user where uid='$uid'";
$result = mysqli_query($con, $sql);
while($row = mysqli_fetch_array($result))
	{
	  $img=$row["uimage"];
	}
@unlink('user/'.$img);

//end view code
$msg="";
//$sql = "DELETE FROM user WHERE uid = {$uid}";

$sql="UPDATE user SET utype = '{$ustate}'  WHERE uid = {$uid}";
$result = mysqli_query($con, $sql);
if($result)
{
	$msg="<p class='alert alert-success'>User Updated</p>";
	header("Location:userlist.php?msg=$msg");
}
else
{
	$msg="<p class='alert alert-warning'>User not Deleted</p>";
		header("Location:userlist.php?msg=$msg");
}


?>
