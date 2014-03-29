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


        usort($query, "usortSituacion");

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

                    if (isset($hashmap[$value['nombre']]['periodosPasados'])) {

                       $value['periodosPasados'] = $hashmap[$value['nombre']]['periodosPasados'];

                    }

                    $value['periodosPasados'][] = implode("-", $periodoAnterior);

                    $hashmap[$value['nombre']] = $value;

                }else if($periodoActual[0] == $periodoAnterior[0]){

                    if ($periodoActual[1] == "ene" && $periodoAnterior[1] == "ago") {

                        $hashmap[$value['nombre']]['periodosPasados'][] = implode("-", $periodoActual);

                    }elseif ($periodoActual[1] == "ago" && $periodoAnterior[1] == "ago") {

                        $hashmap[$value['nombre']]['periodosPasados'][] = implode("-", $periodoActual);

                    }elseif ($periodoActual[1] == "ene" && $periodoAnterior[1] == "ene") {

                        $hashmap[$value['nombre']]['periodosPasados'][] = implode("-", $periodoActual);

                    }elseif ($periodoActual[1] == "ago" && $periodoAnterior[1] == "ene") {

                        if (isset($hashmap[$value['nombre']]['periodosPasados'])) {

                            $value['periodosPasados'] = $hashmap[$value['nombre']]['periodosPasados'];

                        }

                        $value['periodosPasados'][] = implode("-", $periodoAnterior);

                        $hashmap[$value['nombre']] = $value;
                        

                    }

                }else if($periodoActual[0] < $periodoAnterior[0]){

                    $hashmap[$value['nombre']]['periodosPasados'][] = implode("-", $periodoActual);

                }

            }

        }

        foreach ($hashmap as $key => $value) {

            if ($value['situacion'] == 1) {
                unset($hashmap[$key]);
            }

        }

        prettyArray($hashmap);

    }
}


?>
