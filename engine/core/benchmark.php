<?php
    //------------------------------------------------------------
    // benchmark.php  - micro timing functions to determine the
    //     time spent in certain functions
    //
    // to use: insert a start_benchmark call before the block you want to test
    //   then put a stop_benchmark call after the block.
    //
    // e.g.,
    // start_benchmark("url detect benchmark");
    //  //.......
    // //php code
    // //.......
    // stop_benchmark();  //the time will be printed out.
    //
    //------------------------------------------------------------
     
    $GLOBALS['start_time'] = 0;
    $GLOBALS['end_time'] = 0;
    $GLOBALS['total_time'] = 0;
    $GLOBALS['benchmark_title'] = "";
     
     
     
    //from the PHP docs, works like PHP5
    function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
     
     
    function start_benchmark($blockname )
    {
        $GLOBALS['benchmark_title'] = $blockname;
        $GLOBALS['start_time'] = microtime_float();
    }
     
    function stop_benchmark()
    {
        $GLOBALS['end_time'] = microtime_float();
        $GLOBALS['total_time'] = $GLOBALS['end_time'] - $GLOBALS['start_time'];
        global $siteSettings;
                
        
        print("[BENCHMARK: " . $GLOBALS['benchmark_title'] . " executed in " . $GLOBALS['total_time'] ."]<br/>");
         
        /*$GLOBALS['start_time'] = 0;
        $GLOBALS['end_time'] = 0;
        $GLOBALS['total_time'] = 0;
        $GLOBALS['benchmark_title'] = "";*/
    }
     
     
?>
