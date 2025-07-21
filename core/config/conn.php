<?php
      
    class Database{

        /* Funcion que conecta la base de datos usando el controlador MYSQL */
        public static function Conectar(){
            try {

                $pdo = new PDO('mysql:host='. DB_HOST.';dbname='. DB_NAME.';charset=utf8', ''. DB_USER.'', ''. DB_PASS.'');
                //Filtrando posibles errores de conexión.
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
								$pdo->exec("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'");
                return $pdo; //Retorna conexion a la base de datos

            } catch (PDOException $pe) {
                die("Could not connect to the database '". DB_NAME ."': " . $pe->getMessage());

            }
            
        }

    }

    

?>
