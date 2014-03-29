<?php 

require 'class.mysql.php';
require 'class.rest.php';

require 'helpers.php';

$mysql = new MySQL("tutorvirtual", "root", "");

$query = $mysql->ExecuteSQL("SELECT 
    a.`id`
    , a.`nombre`
    , k.`tipo`
    , k.`situacion`
    , k.`periodo` 
    FROM kardex AS k 
    LEFT JOIN asignatura AS a ON a.`id` = k.`id_asignatura`
    WHERE k.`matricula` = 12216317
    Order by rand()");

if ($query) {
    if (is_bool($query)) {
        echo "El alumno no ha cursado materias.";
    }else{


        usort($query, "usortTest");

        // prettyArray($query);

        // echo "____________";

        $hashmap = array();


        foreach ($query as $key => $value) {


            if (!isset($hashmap[$value['nombre']])) {

                $hashmap[$value['nombre']] = $value;

            }elseif ($value['situacion'] == 1 && isset($hashmap[$value['nombre']])) {

                $hashmap[$value['nombre']] = $value;

            }else{


                $periodoAnterior = explode("-", $hashmap[$value['nombre']]['periodo']);

                $periodoActual = explode("-", $value['periodo']);


                if ($periodoActual[0] > $periodoAnterior[0]) {

                    $hashmap[$value['nombre']]['periodoPasado'][] = implode("-", $periodoAnterior);

                }else if($periodoActual[0] == $periodoAnterior[0]){

                    if ($periodoActual[1] == "ago") {

                        $hashmap[$value['nombre']] = $value;

                        $hashmap[$value['nombre']]['periodoPasado'][] = implode("-", $periodoAnterior);


                    } elseif ($periodoAnterior[1] == "ago") {

                        $hashmap[$value['nombre']]['periodoPasado'][] = implode("-", $periodoAnterior);

                    }

                }else if($periodoActual[0] < $periodoAnterior[0]){

                    $hashmap[$value['nombre']]['periodoPasado'][] = implode("-", $periodoAnterior);
                    


                }



            }

        }

        // prettyArray($hashmap);

        //Se eliminan las repobadas

        foreach ($hashmap as $key => $value) {

            if ($value['situacion'] == 1) {
                unset($hashmap[$key]);
            }

        }

        prettyArray($hashmap);

    }
}


?>
