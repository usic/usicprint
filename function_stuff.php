<?
/* This function connects to datebase, where users bills are.
   username and pass used here are my own. And I'm not a mad one!
   It's just necessary. 
   Datebase has one table with 2 fields: uid && bill (copecks) */
function connect_to_DB(){
	mysql_connect('db.usic.lan:3306', 'gloria','mia6Eith') or die("can't connect to the datebase");
        mysql_select_db('gloriadb') or die(mysql_error());
}

/* finishs connection to datebase*/
function close_connection(){
	mysql_close();
}

/* it checks if certain user has enough money to pay for a print job.
   the only parameter is uid */
function check_bill($userid){
	connect_to_DB();
	$query = "SELECT bill FROM users WHERE userid='".$userid."'";
	$res = mysql_query($query);
	while($row = mysql_fetch_array($res)) $bill = $row['bill'];
	close_connection();
	return $bill;
}

/* pays money for a print job. It substracts necessary sum of money from the user's bill
   the first parameter is uid
   the second shows the sum to pay 
   the third - how much money is there on user's account (before payment has been done)*/
function pay($userid,$sum_to_pay,$how_much_it_was){
        $query = "UPDATE users SET bill=".(int)($how_much_it_was-$sum_to_pay)." WHERE userid='".$userid."'";
        return mysql_query($query);// or die(mysql_error());
}

/* 1. checks if user with such username and pass exists in LDAP
   2. if exists - gets his uid
   3. checks if user with such uid exists in MySQL datebase
   4. if not - creates corresponding record in table users*/
function check_user($username,$password){	
	exec("echo ".trim($password)." | /opt/usic/bin/usiccheckpasswd ".trim($username), $output, $err);    
	if($err==0){
		exec('echo -e "login='.trim($username).'\nvalues=uid" |/opt/usic/bin/usic_userinfo',$output,$err);
		foreach($output as $string){$userid = substr(trim($string),4);}
		connect_to_DB();
		$query = "SELECT userid FROM users WHERE userid='".$userid."'";
        	$res = mysql_query($query);
		if(mysql_num_rows($res)!=1) {
   			mysql_query("INSERT INTO users(userid,bill) VALUES('".$userid."','0')");
		}
		close_connection();
		return $userid;
	}
	else return -1;
}

function get_uid_by_username($username){
	exec('echo -e "login='.trim($username).'\nvalues=uid" |/opt/usic/bin/usic_userinfo',$output,$err);
	foreach($output as $string){$userid = substr(trim($string),4);}
	return $userid;
}

/* to represent the price of the job properly, it should looks like: hryvnas.copecks */
function echo_price($to_echo){
	if ((int)(($to_echo % 100)/10) == 0) return (int)($to_echo/100).".0".(int)($to_echo % 10);
	else return (int)($to_echo/100).".".(int)($to_echo % 100);
}

/* opens connection to the print server, where some scripts should execute*/
function connect_to_printserver(){
@	$con = ssh2_connect("scribus.usic.lan", 22) or die("unable to establish connection to scribus.usic.lan");
@       ssh2_auth_password($con, "gloria", "23fialki") or die("fail: unable to authenticate");
	return $con;
}

/* counts how many pages are there in the document.
   the first parameter is var with opened connection
   the second isid of the job, whoes number of pages we should count */
function count_pages($con, $jobid){
	/* jobid should be have five signs.
	   if it has less than 5, then some zeros should be added on the front */
        if(!(int)($jobid / 10)) $jobid = "0000".$jobid;
        else if(!(int)($jobid / 100)) $jobid = "000".$jobid;
        else if(!(int)($jobid / 1000)) $jobid = "00".$jobid;
        else if(!(int)($jobid / 10000)) $jobid = "0".$jobid;

	/* special script, which is located on the print server 
	   and can count number of pages in any document  from the print queue */
	if ($stream = ssh2_exec($con, "/etc/scripts/pages ".$jobid)) {
		// collects returning data from command
		stream_set_blocking($stream, true);
		$data = "";
		while ($buf = fread($stream,4096)) {
			$data .= $buf;
		}
	}
        fclose($stream);
	return $data;
}	

/* send document with given id from print queue directly to print
   also checks one more time if user has enough money to pay for the service */
function print_job($ssh_con, $jobid, $price = 0, $userid = 0){
	/* for operators */
	if ($userid == 0 && $price == 0){
		connect_to_DB();
		$stream = ssh2_exec($ssh_con, "/etc/scripts/usicprint allow ".trim($jobid));
        	fclose($stream);
		close_connection();
	} else {
	/* for the rest */
		$how_much_money_we_have = check_bill($userid);
		connect_to_DB();
		if(($how_much_money_we_have>=$price) && pay($userid,$price, $how_much_money_we_have)){
			$stream = ssh2_exec($ssh_con, "/etc/scripts/usicprint allow ".trim($jobid));
                	fclose($stream);
		}        
		close_connection();
	}
}


/* removes job with given id from the print queue */
function cancel_job($ssh_con, $jobid){
	$stream = ssh2_exec($ssh_con, "/etc/scripts/usicprint deny ".trim($jobid));
	fclose($stream);
}
/*
function get_username_by_uid($uid){
		exec("getent passwd ".trim($uid), $output1, $err1);    
		foreach($output1 as $string){
			for($i=0; $i<strlen($string) && $string[$i]!=':'; $i++) $username .= $string[$i];
		}
		return $username;

}*/

/* returns an array, where the first element is a code of error (it's 0 if no errors)
   and the second is list of print jobs of user with given uid */
function show_queue($ssh_con, $userid = 0){
	/* for operators */
	if ($userid == 0){
		exec("/home/gloria/public_html/files/usicprint show", $output[1], $output[0]);
	} else {
	/* for the rest */
		exec("getent passwd ".trim($userid), $output1, $err1);    
		foreach($output1 as $string){
			for($i=0; $i<strlen($string) && $string[$i]!=':'; $i++) $username .= $string[$i];
		}
	//	$username = trim(get_username_by_uid($userid));
		exec("/home/gloria/public_html/files/usicprint show ".$username, $output[1], $output[0]);
	}
	return $output;
}

?>
