<center>
<table width="690px" cellspacing="0px" cellpadding="0px">
<tr bgcolor="#000000"><td width="250" align="center" valign="center">
<?
//protection!!
$uname = htmlspecialchars($_REQUEST['uname']);

//include ("function_stuff.php");
if($uname!='' && strcmp($uname,'Username')){
	/* check via DB if such a user exists */
	exec('echo -e "login='.trim($uname).'\nvalues=uid" | ' . UMS_UTILS_PATH . UMS_UTILS['user_info'],$output,$err);
	
	/* if user exists and money are in the right format */
	if($err==0 && ereg("^([0-9])+$",trim($_REQUEST['hrn'])) && ereg("^([0-9])+$",trim($_REQUEST['kop']))){

		/* get uid by username */
		foreach($output as $string){$userid_current = substr(trim($string),4);}

		/* convert money to copeks */
		$money = $_REQUEST['hrn']*100 + $_REQUEST['kop'];		
		connect_to_DB();
		
		/* check if such a user exists in mySQL DB */
		$query = "SELECT userid FROM users WHERE userid='".$userid_current."'";
        	$res = mysql_query($query);

		/* if not - add user && add money to his account*/
		if(mysql_num_rows($res)!=1) {
   			mysql_query("INSERT INTO users(userid,bill) VALUES('".$userid_current."','".$money."')");
		}else{
			/* if exists - update his account*/
			$res= mysql_query("SELECT bill FROM users WHERE userid='".$userid_current."'");
			while($row = mysql_fetch_array($res)) $bill = $row['bill'];
			mysql_query("UPDATE users SET bill=".($bill+$money)." WHERE userid='".$userid_current."'");
		}
		
		close_connection();
		/* show user's acount state */?>
		<table>
		<tr align="left">
			<td>
				<font face="helvetica" color="#ffffff" size="4"><? echo $uname ?></font>
				<br><font face="helvetica" color="#33CC00" size="4"><? echo echo_price($bill+$money)?></font>
			</td>
		</tr>
		</table>
	<?} else { ?><font color="#ffffff" face="helvetica"><?echo "Invalid data";?></font><?}
}?>

</td>
<td align="left">
	<form method="POST" action=<?echo $_SERVER['PHP_SELF'];?> >
	<input type="hidden" name="userid" value=<?echo $_REQUEST['userid'];?>>
	<table cellspacing=0 cellpadding=0>
	<tr bgcolor="#000000" height="30px">
		<td width="30px"></td>
		<td align="left">
			<input type="text" size=17 maxlength=17 value="Username" name="uname" style="width:130px; font-family:helvetica; font-size:14"
			onclick="if(this.value=='Username') this.value=''" onblur="if(this.value=='') this.value='Username'">
		</td>
	</tr>
	<tr bgcolor="#000000" height="30px">
		<td width="30px"><font face="helvetica" color="#ff8000">+</font></td>
		<td align="left"><font face="helvetica" color="#ff8000">
			<input type="text" size=2 maxlength=2 value="00" name="hrn" style="font-family:helvetica; font-size:14; text-align:right" 
			onclick="if(this.value=='0' || this.value=='00') this.value=''" onblur="if(this.value=='') this.value='00'"> грн.
			<input type="text" size=2 maxlength=2 value="00" name="kop" style="font-family:helvetica; font-size:14; text-align:right"
			onclick="if(this.value=='0' || this.value=='00') this.value=''" onblur="if(this.value=='') this.value='00'"> коп.
		</font></td>
	</tr>
	<tr bgcolor="#000000" height="30px">
		<td width="30px"></td>
		<td align="left"><input type="submit" name="manage_bills_x" style="background-color:#ff8000; width:130px; font-family:helvetica; font-size:14" value='submit'></td>
	</tr>
	</table>
	</form>
</td></tr>
