<?php

// require 'class.mysql.php';
// require 'class.rest.php';

require 'helpers.php';

function getFailed($matricula, $db){
    $query = $db->ExecuteSQL("SELECT
        a.`id`
        , a.`nombre`
        , k.`tipo`
        , k.`situacion`
        , k.`periodo`
        FROM kardex AS k
        LEFT JOIN asignatura AS a ON a.`id` = k.`id_asignatura`
        WHERE k.`matricula` = " . $matricula . "
        Order by rand()");

    if ($query) {
        if (is_bool($query)) {
            echo "El alumno no ha cursado materias.";
        }else{
            $results = getFailedSubjects($query);
            // prettyArray($results);
            // prettyArray(getFailedSubjectsIds($results));
            // prettyArray($results);
            // return getFailedSubjectsIds($results);
            return getSubjectsToCharge($results);
        }
    }
}

function getFailedIds($matricula, $db){
    $query = $db->ExecuteSQL("SELECT
        a.`id`
        , a.`nombre`
        , k.`tipo`
        , k.`situacion`
        , k.`periodo`
        FROM kardex AS k
        LEFT JOIN asignatura AS a ON a.`id` = k.`id_asignatura`
        WHERE k.`matricula` = " . $matricula . "
        Order by rand()");

    if ($query) {
        if (is_bool($query)) {
            echo "El alumno no ha cursado materias.";
        }else{
            $results = getFailedSubjects($query);
            // prettyArray($results);
            // prettyArray(getFailedSubjectsIds($results));
            // prettyArray($results);
            return getFailedSubjectsIds($results);
            // return getSubjectsToCharge($results);
        }
    }
}

function getSubjectsToCharge($failedSubjects){
    $results = array();

    foreach($failedSubjects as $key => $value){
        $numOrdinarios = 0;
        $numExtras = 0;
        foreach($value['periodos'] as $periodo){
            if ($periodo['tipo'] == 0){
                $numOrdinarios += 1;
            } elseif ($periodo['tipo'] == 1){
                $numExtras += 1;
            }
        }

        if ($numOrdinarios == 2 && $numExtras == 1){
            $results[] = array('id' => $value['id'], 'tipo' => 1);
        } elseif ($numOrdinarios == 1 && $numExtras == 0){
            $results[] = array('id' => $value['id'], 'tipo' => 1);
        } elseif ($numOrdinarios == 2 && $numExtras ==0){
            $results[] = array('id' => $value['id'], 'tipo' => 1);
        } elseif ($numOrdinarios == 1 && $numExtras == 1){
            $results[] = array('id' => $value['id'], 'tipo' => 0);
        } elseif ($numOrdinarios == 1 && $numExtras == 2) {
            $results[] = array('id' => $value['id'], 'tipo' => 0);
        }
    }
    

    return $results;
}

function getFailedSubjectsIds($failedSubjects){
    $results = array();

    foreach($failedSubjects as $subject){
        $results[] = $subject['id'];
    }
    return $results;
}

function getFailedSubjects($queryResult) {
    // prettyArray($query);
    // Aqui ya tengo todas las materias cursadas

    $failedSubjects = array();
    foreach($queryResult as $key => $value) {
        if ($value['situacion'] == 0) {
          $failedSubjects[$key] = $value;
        }
    }
    // Aqui ya tengo todas las materias reprobadas sin contar las que se aprobaron luego

    // prettyArray($failedSubjects);

    $passedSubjects = array();
    foreach($queryResult as $key => $value) {
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
    // prettyArray($failedSubjects);

    $results = array();

    foreach($failedSubjects as $entry){
        $name = $entry['nombre'];
        $results[$name]['nombre'] = $name;

        if (isset($results[$name][$entry['periodo']])){
            $results[$name]['periodos'][] += $entry['periodo'] . "-" . $entry['tipo'];
            $results[$name]['periodos'][] += array('periodo' => $entry['periodo'], 'tipo' => $entry['tipo']);
        } else {
            $results[$name]['periodos'][] = array('periodo' => $entry['periodo'], 'tipo' => $entry['tipo']);
        }
        $results[$name]['situacion'] = $entry['situacion'];
        $results[$name]['id'] = $entry['id'];
    }

    $results = array_values($results);

    // Aqui ya tengo la relacion completa de materias reprobadas
    // prettyArray($results);
    return $results;
}

function compararHorarios($horarios, $restriccionHoras = array('inicio' => "7:30",'fin' => "15:00");){

    $horarioFinal = array();

    // shuffle($horarios); // Se randomiza para seleccionar una materia al azar.
    // falta Query para ordenar por nombre.

    $i = 0;
    foreach ($horarios as $key => $value) {
        // Se selecciona la primera materia.
        if ($horarioFinal[$i] == null) {
            $horarioFinal[$i] = $value;

        } elseif ($horarioFinal[$i] == $value['id']) { // Verificamos si la asignatura ya está cargada.
            // De aparecer nuevamente, es ignorada.

        } elseif($horarioFinal[$i]['id'] != $value['id']) { // De no estar cargada, se verifica que no choquen los horarios
            // Verificamos si coinciden en horario
            if ( ($horarioFinal[$i]['lunes'] != null && $horarioFinal[$i]['lunes'] == $value['lunes']) 
                || ($horarioFinal[$i]['martes'] != null && $horarioFinal[$i]['lunes'] == $value['martes'])
                || ($horarioFinal[$i]['miercoles'] != null && $horarioFinal[$i]['lunes'] == $value['miercoles'])
                || ($horarioFinal[$i]['jueves'] != null && $horarioFinal[$i]['lunes'] == $value['jueves'])
                || ($horarioFinal[$i]['viernes'] != null && $horarioFinal[$i]['lunes'] == $value['viernes']) ) {
            } else {
                $i++;
                $horarioFinal[$i] = $value;
            }
        } 
    }

    // Restricciones de inicio y fin por horario ingresado.
    
    $horaIngresadaInicio = $restriccionHoras['inicio'];
    $horaIngresadaFin = $restriccionHoras['fin'];

    $format = 'H:i';
    $date = DateTime::createFromFormat($format, $horaIngresadaInicio);
    $horaConvertidaIngresadaInicio = $date->format('H:i');
    
    $date = DateTime::createFromFormat($format, $horaIngresadaFin);
    $horaConvertidaIngresadaFin = $date->format('H:i');

    $j = 0;
    // Definimos las asignaturas que puede llevar con respecto a su horario ingresado.
    foreach ($horarioFinal as $key => $value) {

        // Extraemos el horario del lunes.
        if($horarioFinal[$j]['lunes'] == null) {
            // Si el horario de lunes está vacío es ignorado.
            if($horarioFinal[$j]['martes'] == null) {
                if($horarioFinal[$j]['miercoles'] == null) {
                    if($horarioFinal[$j]['jueves'] == null) {
                        if($horarioFinal[$j]['viernes'] == null) {
                            
                        } else {
                            $horaAsignatura = explode('-', $horarioFinal[$j]['viernes']);
                            // Separamos la hora de inicio y la hora de fin.
                            $format = 'H:i';
                            $date = DateTime::createFromFormat($format, $horaAsignatura[0]);
                            $horaConvertidaAsignagnaturaInicio = $date->format('H:i');

                            $date = DateTime::createFromFormat($format, $horaAsignatura[1]);
                            $horaConvertidaAsignagnaturaFin = $date->format('H:i');

                            if ( (($horaConvertidaIngresadaInicio < $horaConvertidaAsignagnaturaInicio) && ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio)) || ($horaConvertidaIngresadaInicio >= $horaConvertidaAsignagnaturaFin) || ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio) ) { 
                                // Si las condiciones establecidas se cumplen, preservamos la asignatura.

                            } else {
                                // En caso de incumplimiento, descartamos la materia de la oferta.
                                unset($horarioFinal[$j]);
                            }
                        } 
                    } else {
                        $horaAsignatura = explode('-', $horarioFinal[$j]['jueves']);
                        // Separamos la hora de inicio y la hora de fin.
                        $format = 'H:i';
                        $date = DateTime::createFromFormat($format, $horaAsignatura[0]);
                        $horaConvertidaAsignagnaturaInicio = $date->format('H:i');

                        $date = DateTime::createFromFormat($format, $horaAsignatura[1]);
                        $horaConvertidaAsignagnaturaFin = $date->format('H:i');

                        if ( (($horaConvertidaIngresadaInicio < $horaConvertidaAsignagnaturaInicio) && ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio)) || ($horaConvertidaIngresadaInicio >= $horaConvertidaAsignagnaturaFin) || ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio) ) { 
                            // Si las condiciones establecidas se cumplen, preservamos la asignatura y revisamos el siguiente dia.
                            // Extraemos el horario del viernes.
                            if($horarioFinal[$j]['viernes'] == null) {
                                // Si el horario de viernes está vacío es ignorado.
                            } else {
                                $horaAsignatura = explode('-', $horarioFinal[$j]['viernes']);
                                // Separamos la hora de inicio y la hora de fin.
                                $format = 'H:i';
                                $date = DateTime::createFromFormat($format, $horaAsignatura[0]);
                                $horaConvertidaAsignagnaturaInicio = $date->format('H:i');

                                $date = DateTime::createFromFormat($format, $horaAsignatura[1]);
                                $horaConvertidaAsignagnaturaFin = $date->format('H:i');

                                if ( (($horaConvertidaIngresadaInicio < $horaConvertidaAsignagnaturaInicio) && ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio)) || ($horaConvertidaIngresadaInicio >= $horaConvertidaAsignagnaturaFin) || ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio) ) { 
                                    // Si las condiciones establecidas se cumplen, preservamos la asignatura.

                                } else {
                                    // En caso de incumplimiento, descartamos la materia de la oferta.
                                    unset($horarioFinal[$j]);
                                }
                            }
                        } else {
                            // En caso de incumplimiento, descartamos la materia de la oferta.
                            unset($horarioFinal[$j]);
                        }
                    } 
                } else {
                    $horaAsignatura = explode('-', $horarioFinal[$j]['miercoles']);
                            // Separamos la hora de inicio y la hora de fin.
                            $format = 'H:i';
                            $date = DateTime::createFromFormat($format, $horaAsignatura[0]);
                            $horaConvertidaAsignagnaturaInicio = $date->format('H:i');

                            $date = DateTime::createFromFormat($format, $horaAsignatura[1]);
                            $horaConvertidaAsignagnaturaFin = $date->format('H:i');

                            if ( (($horaConvertidaIngresadaInicio < $horaConvertidaAsignagnaturaInicio) && ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio)) || ($horaConvertidaIngresadaInicio >= $horaConvertidaAsignagnaturaFin) || ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio) ) { 
                                // Si las condiciones establecidas se cumplen, preservamos la asignatura y revisamos el siguiente dia.
                                // Extraemos el horario del jueves.
                                if($horarioFinal[$j]['jueves'] == null) {
                                    // Si el horario de jueves está vacío es ignorado.
                                } else {
                                    $horaAsignatura = explode('-', $horarioFinal[$j]['jueves']);
                                    // Separamos la hora de inicio y la hora de fin.
                                    $format = 'H:i';
                                    $date = DateTime::createFromFormat($format, $horaAsignatura[0]);
                                    $horaConvertidaAsignagnaturaInicio = $date->format('H:i');

                                    $date = DateTime::createFromFormat($format, $horaAsignatura[1]);
                                    $horaConvertidaAsignagnaturaFin = $date->format('H:i');

                                    if ( (($horaConvertidaIngresadaInicio < $horaConvertidaAsignagnaturaInicio) && ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio)) || ($horaConvertidaIngresadaInicio >= $horaConvertidaAsignagnaturaFin) || ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio) ) { 
                                        // Si las condiciones establecidas se cumplen, preservamos la asignatura y revisamos el siguiente dia.
                                        // Extraemos el horario del viernes.
                                        if($horarioFinal[$j]['viernes'] == null) {
                                            // Si el horario de viernes está vacío es ignorado.
                                        } else {
                                            $horaAsignatura = explode('-', $horarioFinal[$j]['viernes']);
                                            // Separamos la hora de inicio y la hora de fin.
                                            $format = 'H:i';
                                            $date = DateTime::createFromFormat($format, $horaAsignatura[0]);
                                            $horaConvertidaAsignagnaturaInicio = $date->format('H:i');

                                            $date = DateTime::createFromFormat($format, $horaAsignatura[1]);
                                            $horaConvertidaAsignagnaturaFin = $date->format('H:i');

                                            if ( (($horaConvertidaIngresadaInicio < $horaConvertidaAsignagnaturaInicio) && ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio)) || ($horaConvertidaIngresadaInicio >= $horaConvertidaAsignagnaturaFin) || ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio) ) { 
                                                // Si las condiciones establecidas se cumplen, preservamos la asignatura.

                                            } else {
                                                // En caso de incumplimiento, descartamos la materia de la oferta.
                                                unset($horarioFinal[$j]);
                                            }
                                        }
                                    } else {
                                        // En caso de incumplimiento, descartamos la materia de la oferta.
                                        unset($horarioFinal[$j]);
                                    }
                                }                                
                            } else {
                                // En caso de incumplimiento, descartamos la materia de la oferta.
                                unset($horarioFinal[$j]);
                            }
                } 
            } else {
                $horaAsignatura = explode('-', $horarioFinal[$j]['martes']);
                    // Separamos la hora de inicio y la hora de fin.
                    $format = 'H:i';
                    $date = DateTime::createFromFormat($format, $horaAsignatura[0]);
                    $horaConvertidaAsignagnaturaInicio = $date->format('H:i');

                    $date = DateTime::createFromFormat($format, $horaAsignatura[1]);
                    $horaConvertidaAsignagnaturaFin = $date->format('H:i');

                    if ( (($horaConvertidaIngresadaInicio < $horaConvertidaAsignagnaturaInicio) && ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio)) || ($horaConvertidaIngresadaInicio >= $horaConvertidaAsignagnaturaFin) || ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio) ) { 
                        // Si las condiciones establecidas se cumplen, preservamos la asignatura y revisamos el siguiente dia.
                        // Extraemos el horario del miercoles.
                        if($horarioFinal[$j]['miercoles'] == null) {
                            // Si el horario de miercoles está vacío es ignorado.
                        } else {
                            $horaAsignatura = explode('-', $horarioFinal[$j]['miercoles']);
                            // Separamos la hora de inicio y la hora de fin.
                            $format = 'H:i';
                            $date = DateTime::createFromFormat($format, $horaAsignatura[0]);
                            $horaConvertidaAsignagnaturaInicio = $date->format('H:i');

                            $date = DateTime::createFromFormat($format, $horaAsignatura[1]);
                            $horaConvertidaAsignagnaturaFin = $date->format('H:i');

                            if ( (($horaConvertidaIngresadaInicio < $horaConvertidaAsignagnaturaInicio) && ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio)) || ($horaConvertidaIngresadaInicio >= $horaConvertidaAsignagnaturaFin) || ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio) ) { 
                                // Si las condiciones establecidas se cumplen, preservamos la asignatura y revisamos el siguiente dia.
                                // Extraemos el horario del jueves.
                                if($horarioFinal[$j]['jueves'] == null) {
                                    // Si el horario de jueves está vacío es ignorado.
                                } else {
                                    $horaAsignatura = explode('-', $horarioFinal[$j]['jueves']);
                                    // Separamos la hora de inicio y la hora de fin.
                                    $format = 'H:i';
                                    $date = DateTime::createFromFormat($format, $horaAsignatura[0]);
                                    $horaConvertidaAsignagnaturaInicio = $date->format('H:i');

                                    $date = DateTime::createFromFormat($format, $horaAsignatura[1]);
                                    $horaConvertidaAsignagnaturaFin = $date->format('H:i');

                                    if ( (($horaConvertidaIngresadaInicio < $horaConvertidaAsignagnaturaInicio) && ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio)) || ($horaConvertidaIngresadaInicio >= $horaConvertidaAsignagnaturaFin) || ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio) ) { 
                                        // Si las condiciones establecidas se cumplen, preservamos la asignatura y revisamos el siguiente dia.
                                        // Extraemos el horario del viernes.
                                        if($horarioFinal[$j]['viernes'] == null) {
                                            // Si el horario de viernes está vacío es ignorado.
                                        } else {
                                            $horaAsignatura = explode('-', $horarioFinal[$j]['viernes']);
                                            // Separamos la hora de inicio y la hora de fin.
                                            $format = 'H:i';
                                            $date = DateTime::createFromFormat($format, $horaAsignatura[0]);
                                            $horaConvertidaAsignagnaturaInicio = $date->format('H:i');

                                            $date = DateTime::createFromFormat($format, $horaAsignatura[1]);
                                            $horaConvertidaAsignagnaturaFin = $date->format('H:i');

                                            if ( (($horaConvertidaIngresadaInicio < $horaConvertidaAsignagnaturaInicio) && ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio)) || ($horaConvertidaIngresadaInicio >= $horaConvertidaAsignagnaturaFin) || ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio) ) { 
                                                // Si las condiciones establecidas se cumplen, preservamos la asignatura.

                                            } else {
                                                // En caso de incumplimiento, descartamos la materia de la oferta.
                                                unset($horarioFinal[$j]);
                                            }
                                        }
                                    } else {
                                        // En caso de incumplimiento, descartamos la materia de la oferta.
                                        unset($horarioFinal[$j]);
                                    }
                                }                                
                            } else {
                                // En caso de incumplimiento, descartamos la materia de la oferta.
                                unset($horarioFinal[$j]);
                            }
                        }
                    } else {
                        // En caso de incumplimiento, descartamos la materia de la oferta.
                        unset($horarioFinal[$j]);
                    }
            }
        } else {
            echo $j;
            $horaAsignatura = explode('-', $horarioFinal[$j]['lunes']);
            // Separamos la hora de inicio y la hora de fin.
            $format = 'H:i';
            $date = DateTime::createFromFormat($format, $horaAsignatura[0]);
            $horaConvertidaAsignagnaturaInicio = $date->format('H:i');

            $date = DateTime::createFromFormat($format, $horaAsignatura[1]);
            $horaConvertidaAsignagnaturaFin = $date->format('H:i');

            if ( (($horaConvertidaIngresadaInicio < $horaConvertidaAsignagnaturaInicio) && ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio)) || ($horaConvertidaIngresadaInicio >= $horaConvertidaAsignagnaturaFin) || ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio) ) { 
                // Si las condiciones establecidas se cumplen, preservamos la asignatura y revisamos el siguiente dia.
                // Extraemos el horario del martes.
                if($horarioFinal[$j]['martes'] == null) {
                    // Si el horario de martes está vacío es ignorado.
                } else {
                    $horaAsignatura = explode('-', $horarioFinal[$j]['martes']);
                    // Separamos la hora de inicio y la hora de fin.
                    $format = 'H:i';
                    $date = DateTime::createFromFormat($format, $horaAsignatura[0]);
                    $horaConvertidaAsignagnaturaInicio = $date->format('H:i');

                    $date = DateTime::createFromFormat($format, $horaAsignatura[1]);
                    $horaConvertidaAsignagnaturaFin = $date->format('H:i');

                    if ( (($horaConvertidaIngresadaInicio < $horaConvertidaAsignagnaturaInicio) && ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio)) || ($horaConvertidaIngresadaInicio >= $horaConvertidaAsignagnaturaFin) || ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio) ) { 
                        // Si las condiciones establecidas se cumplen, preservamos la asignatura y revisamos el siguiente dia.
                        // Extraemos el horario del miercoles.
                        if($horarioFinal[$j]['miercoles'] == null) {
                            // Si el horario de miercoles está vacío es ignorado.
                        } else {
                            $horaAsignatura = explode('-', $horarioFinal[$j]['miercoles']);
                            // Separamos la hora de inicio y la hora de fin.
                            $format = 'H:i';
                            $date = DateTime::createFromFormat($format, $horaAsignatura[0]);
                            $horaConvertidaAsignagnaturaInicio = $date->format('H:i');

                            $date = DateTime::createFromFormat($format, $horaAsignatura[1]);
                            $horaConvertidaAsignagnaturaFin = $date->format('H:i');

                            if ( (($horaConvertidaIngresadaInicio < $horaConvertidaAsignagnaturaInicio) && ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio)) || ($horaConvertidaIngresadaInicio >= $horaConvertidaAsignagnaturaFin) || ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio) ) { 
                                // Si las condiciones establecidas se cumplen, preservamos la asignatura y revisamos el siguiente dia.
                                // Extraemos el horario del jueves.
                                if($horarioFinal[$j]['jueves'] == null) {
                                    // Si el horario de jueves está vacío es ignorado.
                                } else {
                                    $horaAsignatura = explode('-', $horarioFinal[$j]['jueves']);
                                    // Separamos la hora de inicio y la hora de fin.
                                    $format = 'H:i';
                                    $date = DateTime::createFromFormat($format, $horaAsignatura[0]);
                                    $horaConvertidaAsignagnaturaInicio = $date->format('H:i');

                                    $date = DateTime::createFromFormat($format, $horaAsignatura[1]);
                                    $horaConvertidaAsignagnaturaFin = $date->format('H:i');

                                    if ( (($horaConvertidaIngresadaInicio < $horaConvertidaAsignagnaturaInicio) && ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio)) || ($horaConvertidaIngresadaInicio >= $horaConvertidaAsignagnaturaFin) || ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio) ) { 
                                        // Si las condiciones establecidas se cumplen, preservamos la asignatura y revisamos el siguiente dia.
                                        // Extraemos el horario del viernes.
                                        if($horarioFinal[$j]['viernes'] == null) {
                                            // Si el horario de viernes está vacío es ignorado.
                                        } else {
                                            $horaAsignatura = explode('-', $horarioFinal[$j]['viernes']);
                                            // Separamos la hora de inicio y la hora de fin.
                                            $format = 'H:i';
                                            $date = DateTime::createFromFormat($format, $horaAsignatura[0]);
                                            $horaConvertidaAsignagnaturaInicio = $date->format('H:i');

                                            $date = DateTime::createFromFormat($format, $horaAsignatura[1]);
                                            $horaConvertidaAsignagnaturaFin = $date->format('H:i');

                                            if ( (($horaConvertidaIngresadaInicio < $horaConvertidaAsignagnaturaInicio) && ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio)) || ($horaConvertidaIngresadaInicio >= $horaConvertidaAsignagnaturaFin) || ($horaConvertidaIngresadaFin <= $horaConvertidaAsignagnaturaInicio) ) { 
                                                // Si las condiciones establecidas se cumplen, preservamos la asignatura.

                                            } else {
                                                // En caso de incumplimiento, descartamos la materia de la oferta.
                                                unset($horarioFinal[$j]);
                                            }
                                        }
                                    } else {
                                        // En caso de incumplimiento, descartamos la materia de la oferta.
                                        unset($horarioFinal[$j]);
                                    }
                                }                                
                            } else {
                                // En caso de incumplimiento, descartamos la materia de la oferta.
                                unset($horarioFinal[$j]);
                            }
                        }
                    } else {
                        // En caso de incumplimiento, descartamos la materia de la oferta.
                        unset($horarioFinal[$j]);
                    }
                }                
            } else {
                // En caso de incumplimiento, descartamos la materia de la oferta.
                unset($horarioFinal[$j]);
                $j++;
            }
        }
    }
    // prettyArray($horarioFinal);
    // die();
    

    return $horarioFinal;
  
}
?>
