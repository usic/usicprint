/**
 * config.php
 * Contains changeable parameters
 */

<?php

    $DB_PARAMS = array(
                            'host' => 'somehost',
                            'name' => 'somedb',
                            'user' => 'someuser',
                            'password' => 'somepassword');
                            
    define("UMS_UTILS_PATH", '/path/to/ums/utils/');
    $UMS_UTILS = array(
                            'check_passwd' => 'usiccheckpasswd',
                            'user_info' => 'usic_userinfo',
                            'group' => 'usicgroup'
                            );
                            
    $PRINT_SERVER_PARAMS = array(
                                     'hostname' => 'someotherhost',
                                     'username' => 'someotheruser',
                                     'password' => 'someotherpassword' // isn't it better to use a key? --oksamyt
                                      );
    
    define("CUPS_UTILS_PATH", '/path/to/cups/utils/'); // ??? 
    $CUPS_UTILS = array(
                            'pages' => 'pages',
                            'print' => 'usicprint'
                            );
                            
    $PRICES = array(
                        'users' => 30,
                        'operators' => 15
                        );
                        
    $MANAGING_GROUPS = array(
                                0 => 'staff'
                            );
?>
