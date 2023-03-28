<?php 
//database Name
    // $database = "register_user";
//connect to database
	// $con = mysqli_connect("localhost","root","",$database); 
	// 	if(mysqli_connect_errno()){
	// 		echo "Failed to connect Mysql:" . mysqli_connect_errno();
	// 	}
	// Table Names
		// $tb_login = "register_user1";

?>
<?php
$reports_url = "https://analyticsapi.zoho.in";
$accounts_url = "https://accounts.zoho.in";

$scope = "ZohoAnalytics.embed.read";
// when creating App
$ClienID = "1000.HM3SUUQK7M3NKTZB5PYPHL3OH0PGMS";
$ClienSecret = "dd28166ee4ac89b3338f48be649892a8342983daee";

$Redirect_URI = "https://3.110.176.92/index.php";

$code=NULL;
$access_token = NULL;
$refresh_token = NULL;
?>