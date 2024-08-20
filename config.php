<?php 
//By default
$config = [
    '--host' => '10.178.30.50',
    '--port' => 8080
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