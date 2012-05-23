<?php
    //------------------------------------------------------------
    // db.php - database connectivity routines
    //
    //------------------------------------------------------------
    function connectMysql($siteSettings) {
        mysql_connect($siteSettings['server'], $siteSettings['user'], $siteSettings['password']);
        mysql_select_db($siteSettings['db']);
		mysql_set_charset('utf8'); 
    }
    
    
    function mf_query($SQLString) {
		$status1 = explode('  ', mysql_stat());
		$time1 = microtime(true);
		$result = mysql_query($SQLString) or die(mysql_error_override($SQLString));
		$time2 = microtime(true);
		$status2 = explode('  ', mysql_stat());
		
		if (false) {
			$timeout = 0;
			while(!@mkdir("./engine/core/lockdir") && $timeout++ <60){
				 if(time() - @filectime("./engine/core/lockdir") > 90)
					  @rmdir("./engine/core/lockdir");
				 else
					  sleep(1);
			}
			if($timeout < 60) {
				//you have the lock and can proceed safely in here
				//open file, append, ect
				$fh = fopen("engine/core/dblog.txt", 'a');
				fwrite($fh, "SQL: $SQLString\n");
				if (stristr($SQLString, "select")) {
					fwrite($fh, sprintf("%-2s|%-20s|%-20s|%-15s|%-20s|%-20s|%-4s|%-20s|%-4s|%-30s\n","ID","SELECT TYPE","TABLE","TYPE","POSSIBLE KEYS","KEY","KEYL","REF","ROWS","EXTRA"));
					fwrite($fh, "--+--------------------+--------------------+---------------+--------------------+--------------------+----+--------------------+----+--------------------\n");
					$SQLExplain = mysql_query("EXPLAIN $SQLString");
					while(($row = mysql_fetch_row($SQLExplain))) {
						fwrite($fh, sprintf("%-2s|%-20s|%-20s|%-15s|%-20s|%-20s|%-4s|%-20s|%-4s|%-30s ",$row[0],$row[1],$row[2],$row[3],$row[4],$row[5],$row[6],$row[7],$row[8],$row[9]));
						if($row[3] == "ALL"  || $row[4] == "" || $row[4] == "NULL" || $row[5] == "" || $row[5] == "NULL")
							fwrite($fh, "*****");
						fwrite($fh, "\n");
					}
				}
				
				if($status2[3] > $status1[3]) //SLOW QUERY!
					fwrite($fh, "\n*****");
				fwrite($fh, "Query Time: ".($time2 - $time1));
				//print("QueryTime:".($time2 - $time1)." ($time2)<br/>");
				fwrite($fh, "\n\n\n");
				fclose($fh);		 
				@rmdir("./engine/core/lockdir");
				return $result;
			}
			
		}

		return $result;
	}
?>