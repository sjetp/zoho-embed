<?php
require('config.php');
session_start();
// View Dashboard --> Go to Code URl of Zoho
// Redirect in Embed Report(index) Page --> fetch the CODE
// Request View url.

//We have to save the refresh token we get to further use as it's permanernt
//and on logout have to revoked the Refresh token
?>
<?php
// Generate Code
if(isset($_SESSION['code'])){
    $code_url="report.php";
    header('Location: '.$code_url);
}else{
    $code_url = $accounts_url . "/oauth/v2/auth?";
$post_data = "scope=" . $scope . "&client_id=" . $ClienID . "&state=testing&response_type=code&redirect_uri=" . $Redirect_URI . "&access_type=offline&prompt=consent";
$code_url=$code_url.$post_data;
header('Location: '.$code_url);

}
?>