-- Query para obtener materias reprobadas

-- Funciona igual para saber que materia se ha reprobado dos veces

SELECT a.`nombre`, k.`tipo`, k.`periodo` FROM kardex AS k 
LEFT JOIN asignatura AS a ON a.`id` = k.`id_asignatura`
WHERE k.`situacion` = 0 AND k.`matricula` = 12216317;

-- Suponiendo en extra algebra 


-- materias que nunca haz llevado

SELECT a.`nombre`
    , o.`profesor`
    , o.`lunes`
    , o.`martes`
    , o.`miercoles`
    , o.`jueves`
    , o.`viernes` 
    -- ,prioridad = 4
FROM kardex k 
RIGHT JOIN oferta AS o ON o.`id_asignatura` = k.`id_asignatura`
LEFT JOIN asignatura AS a ON o.`id_asignatura` = a.`id`
WHERE k.`id` IS NULL
and a.'id' = 2, 7;

