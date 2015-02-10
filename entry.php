<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>deltaPay</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-7">
<style type="text/css">
<!--
.style1 {color: #FF0000}
.style2 {color: #006600}
.style3 {color: #FF00FF}
.style4 {font-size: x-small}
body,td,th {
	font-family: Trebuchet MS, Verdana, sans-serif;
	font-size: medium;
	background-color: #CCCCCC;
}
-->
</style>
</head>
<body>
<hr>
<h1>DeltaPay Simulator</h1>
<h3>  By George Litos, <a href="mailto:GL@cyberpunk.gr">GL@cyberpunk.gr</a></h3>
<hr>
Posted data:<br>
<pre>
<?php 
	echo '<br>Called from : '. $_SERVER['HTTP_REFERER'].
		'<br>merchantcode: '. $_POST['merchantcode'].
		'<br>charge: '.$_POST['charge'].
		'<br>cardholdername: '.$_POST['cardholdername'].
		'<br>cardholderemail: '.$_POST['cardholderemail'].
		'<br>currencycode: '.$_POST['currencycode'].
		'<br>param1: '.$_POST['param1'].
		'<br>param2: '.$_POST['param2'];
		// action="http://[url to your checkout page.php]/checkout_process.php?osCsid=<?php echo $_POST['param2'];  ? >
?>
</pre>
<form name="result" method="post" action="http://localhost/wlan/shop/checkout_process.php<?php if($_POST['param2'] != '' ) echo '?osCsid='.$_POST['param2']; ?>">
  <input type="text" name="result">
  <input type="submit" name="Submit" value="Submit">
</form>
<pre><span class="style2">1=OK </span><span class="style1">2=ERROR </span><span class="style3">3=CANCELED</span></pre>
<hr>
<?php 
	//echo "<pre class=\"style4\">debug";
	//var_dump($HTTP_POST_VARS);
	//echo "</pre><hr>";
?>
</body>
</html>
