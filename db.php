<?php


require __DIR__.'/vendor/autoload.php';
use Kreait\Firebase\Factory;

$factory = (new Factory)->withServiceAccount('goldapp-4827a-firebase-adminsdk-5rj5j-0f875b8af8.json')
                        ->withDatabaseUri('https://goldapp-4827a-default-rtdb.firebaseio.com/');


$database = $factory->createDatabase();

?>