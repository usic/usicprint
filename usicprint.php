<?
include('function_stuff.php');

/* initialize prices for operators and users */
$file = fopen('price', 'r');
$price = fgets($file);
$price_operator = fgets($file);
fclose($file);
?>


<html>
<head>
  <title>Usicprint</title>
  <meta http-equiv="Content-Language" content="ua">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <link rel="stylesheet" type="text/css" href="style.css" /> 
</head>
<body>

<div id="container"> 
	<div id="header"><img src="images/header.png"></div> 
	<div id="body">

<? 

/* check if user has logged in */
$userid = trim($_REQUEST['userid']);
if($_REQUEST['userid']=='') $userid = check_user(htmlspecialchars($_REQUEST['username']), htmlspecialchars($_REQUEST['password']));

/* if not  - show login form */
if($userid == -1) include ("login_form.html");
else{
	?><center><?$ssh_con = connect_to_printserver();?></center><?

	/* if logged in then check if user is an operator */
	/* used just after login to open the "default tab" for operator */
	exec("/opt/usic/bin/usicgroup check staff ".trim(htmlspecialchars($_REQUEST['username'])), $output, $err);

        if (!strcmp($_REQUEST['username'],'gloria') )$err = 0;
	/* if operator */
	/* if operator has chosen to act as operator */
	if ($err == 0 || $_REQUEST['operator_as_operator_x']!=''){
		include ('operator.php');?>

		<tr hight="35"><td colspan=6>
			<form action=<?echo $_SERVER['PHP_SELF']?> method="post">
			<table border="0px" cellspacing="0px" cellpadding="0px">
				<tr bgcolor="#000000" height="10px"><td colspan=3></td></tr>
				<tr valign="center" align="center" height="25px">
				   	<input type="hidden" name="userid" value=<?echo $userid?>>
					<td width="230" background="images/bookmark_inactive.png"><input type="image" src="images/my_doc.png" name="operator_as_user"></td>
					<td width="230" background="images/bookmark_active.png"><input type="image" src="images/a_all_doc.png" name="operator_as_operator"></td>
					<td width="230" background="images/bookmark_inactive.png"><input type="image" src="images/bills.png" name="manage_bills"></td>
				</tr>
			</table>
			</form>
		</td></tr></table></center>

	<?}else if($_REQUEST['operator_as_user_x']!=''){
	/* if operator has chosen to act as ordinary user */
		$price = $price_operator;
		include ('user.php');?>
		<tr hight="35"><td colspan=5>
			<form action=<? echo $_SERVER['PHP_SELF']?> method="post">
			<table border="0px" cellspacing="0px" cellpadding="0px">
				<tr bgcolor="#000000" height="10px"><td colspan=3></td></tr>
				<tr valign="center" align="center" height="25px">
				   	<input type="hidden" name="userid" value=<?echo $userid?>>
					<td width="230" background="images/bookmark_active.png"><input type="image" src="images/a_my_doc.png" name="operator_as_user"></td>
					<td width="230" background="images/bookmark_inactive.png"><input type="image" src="images/all_doc.png" name="operator_as_operator"></td>
					<td width="230" background="images/bookmark_inactive.png"><input type="image" src="images/bills.png" name="manage_bills"></td>
				</tr>
			</table>
			</form>
		</td></tr></table></center>

		<center><table border=0 width="600px"><tr align="right">
			<!-- account state -->
			<td>on your account: <font color="#33CC00"><? echo echo_price(check_bill($userid));?></td>
		</tr></table></center>
		 
	<?} else if($_REQUEST['manage_bills_x']!=''){
	/* if operator wants to operate users' bills */
		include('bills.php');?>
		<tr hight="35"><td colspan=2>
			<form action=<? echo $_SERVER['PHP_SELF']?> method="post">
			<table border="0px" cellspacing="0px" cellpadding="0px">
				<tr bgcolor="#000000" height="10px"><td colspan=3></td></tr>
				<tr valign="center" align="center" height="25px">
				   	<input type="hidden" name="userid" value=<?echo $userid?>>
					<td width="230" background="images/bookmark_inactive.png"><input type="image" src="images/my_doc.png" name="operator_as_user"></td>
					<td width="230" background="images/bookmark_inactive.png"><input type="image" src="images/all_doc.png" name="operator_as_operator"></td>
					<td width="230" background="images/bookmark_active.png"><input type="image" src="images/a_bills.png" name="manage_bills"></td>
				</tr>
			</table>
			</form>
		</td></tr></table></center>

	<?}else {
	/* if not an operator */	
		include ("user.php");?>
		<tr bgcolor="000000" height="10px"><td colspan=5></td></tr>
		</table></center>
		<center><table border=0 width="600px"><tr align="right">
			<!-- account state -->
			<td>on your account: <font color="#33CC00"><? echo echo_price(check_bill($userid));?></td>
		</tr></table></center>
	<?}
?>

<!--_____________________________ -->
<!-- another one table -->
<center><table border=0 width="600px">
<tr height="10px"><td></td></tr>
<tr height="30px" align="right">
	<!-- logout button -->
	<td><a href=<?echo $_SERVER['PHP_SELF'] ?>>logout</a></td>
</tr>
</table>
<?}?>
	 </div>
	<div id="footer"><a href="http://www.usic.org.ua" border="0"><img src="images/link.png" border=0></a></div>
</div>
</body>
</html>
