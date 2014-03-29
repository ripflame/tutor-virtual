<?php
require_once("class.rest.php");
require_once("class.mysql.php");
require_once("../index.php");



class API extends REST {
    public $data = "";
    const DB_SERVER = "localhost";
    const DB_USER = "root";
    const DB_PASSWORD = "JoseCuervo";
    const DB = "tutorvirtual";

    private $db = NULL;

    public function __construct()
    {
        parent::__construct();// Init parent contructor
        $this->dbConnect();// Initiate Database connection
    }

    //Database connection
    private function dbConnect()
    {
        $this->db = new MySQL(self::DB, self::DB_USER, self::DB_PASSWORD,self::DB_SERVER);
    }

    //Public method for access api.
    //This method dynmically call the method based on the query string
    public function processApi()
    {

        // print_r($_REQUEST);
        $func = strtolower(trim(str_replace("/","",$_REQUEST['rquest'])));
        if((int)method_exists($this,$func) > 0)
            $this->$func();
        else
            $this->response('',404);
        // If the method not exist with in this class, response would be "Page not found".
    }

    //Se verifica que la token pertenesca a un usuario real
    private function verifyToken($token)
    {

        if (is_null($token)) {
            return false;
        }

        $hash = substr($token, 8);
        $matricula = substr($token, 0, 8);

        $loginQuery = $this->db->ExecuteSQL(
                "SELECT matricula FROM alumno where matricula = '$matricula' AND hash = '$hash' LIMIT 1");

        if ($loginQuery && !is_bool($loginQuery)) {
            return true;
        }else{
            return false;
        }
    }

    //Recibe via post matricula y password y si es correcto
    private function login()
    {
        // Cross validation if the request method is POST else it will return "Not Acceptable" status
        if($this->get_request_method() != "POST")
        {
            $this->response('',406);
        }

        if (!isset($this->_request['matricula'])) {
            $matricula = NULL;
        }else{
            $password = $this->_request['pwd'];
        }


        if (!isset($this->_request['pwd'])) {
            $password = NULL;
        }else{
            $matricula = $this->_request['matricula'];
        }

        // Input validations
        if(!empty($matricula) and !empty($password)){

            $matricula  = str_replace("a", "", $matricula);

            $password = sha1($password);

            $loginQuery = $this->db->ExecuteSQL(
                sprintf("SELECT matricula, nombre, hash FROM alumno where matricula = '%s' AND password = '%s' LIMIT 1",
                    mysql_real_escape_string($matricula),
                    mysql_real_escape_string($password)));

            if ($loginQuery) {
                if (is_bool($loginQuery)) {
                    $error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
                    $this->response($this->json($error), 400);
                }else{
                    //regresa el hash
                    $result = array("nombre" => $loginQuery['nombre'],
                        "hash" => $loginQuery['matricula'].$loginQuery['hash'], 'status' => "Logged");

                    $this->response($this->json($result), 200);

                }
            }

        }

        // If invalid inputs "Bad Request" status message and reason
        $error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
        $this->response($this->json($error), 400);
    }


    private function getCarga()
    {
        // Cross validation if the request method is POST else it will return "Not Acceptable" status
        if($this->get_request_method() != "POST")
        {
            $this->response('',406);
        }

        if (!isset($this->_request['token'])) {
            $token = NULL;
        }else{
            $token = $this->_request['token'];
        }

        if (!$this->verifyToken($token)) {
            // If invalid inputs "Bad Request" status message and reason
            $error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
            $this->response($this->json($error), 400);
        }

        $carga = array();

        //id de materias que tiene reprobadas
        $materiasReporbadas = getFailedIds(substr($token, 0, 8), $this->db);
        // print_r($materiasReporbadas);

        //id de materias que debe cargar obligatoriamente y el tipo de curso que debara llevar
        $materiasPorCargar = getFailed(substr($token, 0, 8), $this->db);
        // print_r($materiasPorCargar);


        // $materiasPorCargar =array();
        //regresa todas las opciones de materias que no haya curzado verificando que tenga al menos uno de los requisitos
        $asignaturasACursar = $this->db->ExecuteSQL("SELECT 
            o.`id_asignatura` FROM oferta AS o 
            LEFT JOIN `asignatura_requisito` ar ON ar.`id_obligatoria` = o.`id_asignatura` 
            LEFT JOIN `kardex` k ON ar.`id_requisito` = k.`id_asignatura` 
            LEFT JOIN `asignatura` AS a ON a.`id` = o.`id_asignatura` 
            WHERE k.`situacion` = 1 
            GROUP BY o.`id_asignatura`");

        if ($asignaturasACursar) { 

            if (is_bool($asignaturasACursar)) {

                echo "El alumno no puede cursar materias."; 

            }else{

                $disponibles = array();

                //Genera un where con las amaterias que no a llevado que podria llevar
                $asignaturaDependenciasWhere = "WHERE ";
                
                for ($i=0; $i < count($asignaturasACursar); $i++) { 
                    if ($i==0) {
                        $asignaturaDependenciasWhere .= "ar.`id_obligatoria` = ". $asignaturasACursar[$i]['id_asignatura'];
                    }else{
                        $asignaturaDependenciasWhere .= "OR ar.`id_obligatoria` = ". $asignaturasACursar[$i]['id_asignatura'];
                    }
                    $asignaturaDependenciasWhere .= " ";
                }

                //obitiene todas las materias necesarias para poder cursar otra materia
                $asignaturaDependencias = $this->db->ExecuteSQL("SELECT 
                    ar.`id_obligatoria`
                    , ar.`id_requisito` 
                    FROM `asignatura_requisito` AS ar 
                    " . $asignaturaDependenciasWhere);

                if ($asignaturaDependencias) {
                    if (is_bool($asignaturaDependencias)) {
                        echo "El alumno no puede cursar materias.";
                    }else{

                        $materias = array();

                        // acomoda los ids en arreglos
                        foreach ($asignaturaDependencias as $value) {

                            $idMateriaPrincipal = $value['id_obligatoria'];
                            $materias[$idMateriaPrincipal][] = $value['id_requisito'];
                        }

                        $materiasACurso = array();
                         //verifica que si en las obligatorias de requisito existe alguna en deuda, no permita cargar
                        foreach ($materias as $key => $value) {
                            $result = array_intersect($materias[$key], $materiasReporbadas);

                            if (count($result)>0) {

                            }else{

                                $materiasACurso[] = $key;
                            }
                        }

                        // print_r($materiasACurso);

                        $extraordinarios = array();

                        // acomoda las materias a l grupo que pertenecen
                        foreach ($materiasPorCargar as $key => $value) {
                            if ($value['tipo'] == 0) {
                                $materiasACurso[] = $value['id'];
                            }elseif ($value['tipo'] == 1) {
                                $extraordinarios[] = $value['id'];
                            }
                        }

                        //genera un where para las materias que llevara como ordinario

                        $materiasACursoWhere = "WHERE ";
                
                        for ($i=0; $i < count($materiasACurso); $i++) { 
                            if ($i==0) {
                                $materiasACursoWhere .= "a.`id` = ". $materiasACurso[$i];
                            }else{
                                $materiasACursoWhere .= "OR a.`id` = ". $materiasACurso[$i];
                            }
                            $materiasACursoWhere .= " ";
                        }


                        //genera where para las materias que pueden ser un presentadas en extraoridnario
                        $extraordinariosWhere = "WHERE ";
                        
                        for ($i=0; $i < count($extraordinarios); $i++) { 
                            if ($i==0) {
                                $extraordinariosWhere .= "a.`id` = ". $extraordinarios[$i];
                            }else{
                                $extraordinariosWhere .= "OR a.`id` = ". $extraordinarios[$i];
                            }
                            $extraordinariosWhere .= " ";
                        }

                        //Se obtienen las materias en ordinario y extraoridnario
                        $extraordinariosAPresentar = $this->db->ExecuteSQL("SELECT a.`nombre` FROM asignatura as a " .  $extraordinariosWhere);

                        $asignaturasACursar = $this->db->ExecuteSQL("SELECT a.`id`,a.`nombre`, o.`profesor`, o.`lunes`, o.`martes`, o.`miercoles`, o.`jueves`, o.`viernes` FROM oferta AS o LEFT JOIN `asignatura` AS a ON a.`id` = o.`id_asignatura`" . $materiasACursoWhere. " Order by a.`nombre` asc");
                        
                        // $horario = compararHorarios($asignaturasACursar);

                        // $arrayName = array('Extraoridnarios' => $extraordinariosAPresentar, "Ordinarios" =>$horario);
                        $arrayName = array('Extraoridnarios' => $extraordinariosAPresentar, 
                            "Ordinarios" =>$asignaturasACursar);

                        // print_r($arrayName);
                        


                        $this->response($this->json($arrayName), 200);
                    }

                    
                }
            }

            
        }




    }


    //Encode array into JSON
    private function json($data)
    {
        if(is_array($data)){
            return json_encode($data);
        }
    }
}

// Initiiate Library
$api = new API;
$api->processApi();
?>
