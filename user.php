<?
	//we want to print certain job and pay for it
	if($_REQUEST['job']!='' && !strcmp(trim($_REQUEST['do']),'allow') && $_REQUEST['price']!='') {
		print_job($ssh_con, $_REQUEST['job'],$_REQUEST['price'], $userid);
	}	

	//we want to deny some job
	else if($_REQUEST['job']!='' && !strcmp(trim($_REQUEST['do']),'deny')){	
		cancel_job($ssh_con, $_REQUEST['job']);
	}	
	
	//just show the queue of jobs
	$output = show_queue($ssh_con,$userid);
?>


<center><table width="690px" border="0px" cellspacing="0px" cellpadding="0px">
<tr height="30px" align="center" bgcolor="#000000">
        <td><font color="#ff8000">[</font><font face="helvetica"color="#ffffff"><b>document title</b></font><font color="#ff8000">]</font></td>
        <td width="70px"><font color="#ff8000">[</font><font face="helvetica"color="#ffffff"><b>pages</b></font><font color="#ff8000">]</font></td>
        <td width="70px"><font color="#ff8000">[</font><font face="helvetica"color="#ffffff"><b>price</b></font><font color="#ff8000">]</font></td>
        <td width="70px"><font color="#ff8000">[</font><font face="helvetica"color="#ffffff"><b>print</b></font><font color="#ff8000">]</font></td>
        <td width="70px"><font color="#ff8000">[</font><font face="helvetica"color="#ffffff"><b>cancel</b></font><font color="#ff8000">]</font></td>
</tr>

<?
	$err = $output[0];
	$output = $output[1];
	if($err!=0){?>
		<tr bgcolor="#000000"><td colspan=5><font face="helvetica" color="#ffffff">
        	<?if($err == 1){ echo "Invalid task specification";}
        	else if($err == 2){ echo "No jobs";}
        	else if($err == 15){ echo "Connection to cups failed";} ?>
		</font></td></tr>
	<?}else{
	
		foreach($output as $string){?>
                        <tr bgcolor="000000">
                        <?list($id, $author, $hostname, $title, $pages) = split("\t", $string, 5);?>

			<!-- title of the document -->
	                <td><font face="helvetica"color="#ffffff"><?echo $title;?></font></td>
			<!-- number of pages in document -->
	                <td align="center"><font face="helvetica"color="#ffffff"><? $pages = count_pages($ssh_con, $id); echo $pages;?></font></td>
                        <!--price for job -->
                        <td align="center"><?  $price_for_job = $pages* $price;
                                $user_bill = check_bill($userid);?>
                                <font color=<?if($user_bill < $price_for_job)echo "#ff0000"; else echo "#33CC00"; ?>>
                                <?echo echo_price($price_for_job);?></font>
                        </td>
			<!-- button which allows print -->
			<td align="center"><form method="POST" action=<?echo $_SERVER['PHP_SELF'];?> >
				   <input type="hidden" name="userid" value=<?echo $userid?>>
				   <input type="hidden" name="job" value=<?echo $id?>>
				   <input type="hidden" name="do" value="allow">
				   <? if($user_bill >= $price_for_job){?> <input type="hidden" name="price" value=<?echo $price_for_job?>> <?}?>
				   <input type="submit" name="<?if($_REQUEST['operator_as_user_x']!='') echo "operator_as_user_x"?>" value="ok" <?if($user_bill < $price_for_job) echo "disabled";?> > 
			    </form>
			</td>
			<!-- button which denies print -->
			<td align="center"><form method="POST" action=<?echo $_SERVER['PHP_SELF'];?> >
                                   <input type="hidden" name="userid" value=<?echo $userid?>>
                                   <input type="hidden" name="job" value=<?echo $id?>>
                                   <input type="hidden" name="do" value="deny">
                                   <input type="submit" name="<?if($_REQUEST['operator_as_user_x']!='') echo "operator_as_user_x"?>" value="cancel">
				   <!-- <input type="image" src="images/delete.gif" name="allow"> -->
                            </form>
                        </td>
			</tr>
		<?} //foreach?>
	<?} //else if ?>

