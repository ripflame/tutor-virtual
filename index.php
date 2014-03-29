<?php

require 'class.mysql.php';
require 'class.rest.php';

require 'helpers.php';

$mysql = new MySQL("tutorvirtual", "root", "JoseCuervo");

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
        // prettyArray($query);
        // Aqui ya tengo todas las materias cursadas

        $failedSubjects = array();
        foreach($query as $key => $value) {
        if ($value['situacion'] == 0) {
          $failedSubjects[$key] = $value;
        }
        }
        // Aqui ya tengo todas las materias reprobadas sin contar las que se aprobaron luego

        // prettyArray($failedSubjects);

        $passedSubjects = array();
        foreach($query as $key => $value) {
        if ($value['situacion'] == 1) {
          $passedSubjects[$key] = $value;
        }
        }
        //Aqui ya tengo las materias aprobadas
        // prettyArray($passedSubjects);

        foreach($failedSubjects as $key => $value) {
          foreach($passedSubjects as $key2 => $value2){
              if ($value2['nombre'] == $value['nombre']){
                  unset($failedSubjects[$key]);
              }
          }
        }
        // Aqui ya tengo las materias reprobadas
        prettyArray($failedSubjects);

        $results = array();

        foreach($failedSubjects as $entry){
            $name = $entry['nombre'];
            $results[$name]['nombre'] = $name;

            if (isset($results[$name][$entry['periodo']])){
                $results[$name]['periodos'][] += $entry['periodo'];
            } else {
                $results[$name]['periodos'][] = $entry['periodo'];
            }
        }

        $results = array_values($results);

        prettyArray($results);

        }

        // prettyArray($result);

    }



?>
