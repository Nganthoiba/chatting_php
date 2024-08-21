<?php 
//By default
$config = [
    '--host' => '192.168.137.1',
    '--port' => 8085
];

//$config = [];
if(isset($argv)){
    //however if argument is supplied at command line
    $sizeOfArgv = sizeof($argv);
    if($sizeOfArgv > 1){
        for($i = 1; $i < $sizeOfArgv; $i++){
            $argument = explode('=', $argv[$i]);
            $key = $argument[0];
            $value = $argument[1];

            /* $config[] = [
                $argument[0] => $argument[1]
            ]; */

            $config[$key] = $value;
        }
    }
}
define('HOST_NAME',$config['--host']); 
define('PORT',$config['--port']);