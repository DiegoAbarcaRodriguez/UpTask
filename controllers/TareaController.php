<?php
namespace Controllers;
use Model\Proyecto;
use Model\Tarea;

class TareaController{

    public static function index(){

        $proyectoId=$_GET['id'];

        if(!$proyectoId) return header('Location: /dashboard');

        $proyecto= Proyecto::where('url',$proyectoId);

        session_start();
        if(!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) return header('Location: /404');

        $tareas=Tarea::belongsTo('proyectoId',$proyecto->id);

        echo json_encode(['tareas'=>$tareas]);
    }

    public static function crear(){
        if($_SERVER['REQUEST_METHOD']==='POST'){

            session_start();

            $proyectoId=$_POST['proyectoId'];

            $proyecto=Proyecto::where('url',$proyectoId);

            //Se corroborá de que la url leida mediante JS y pasa por FormData este asociada a algún proyecto
            if(!$proyecto || $proyecto->propietarioId !== $_SESSION['id']){
                $respuesta=[
                    'tipo'=>'error',
                    'mensaje'=>'Hubo un Error al Agregar la Tarea'
                ];

                echo json_encode($respuesta);
               return;
            }

            // Exito, todo bien,instanciar y crear la tarea
            $tarea= new Tarea($_POST);
            $tarea->proyectoId=$proyecto->id;
            $resultado= $tarea->guardar();
            $respuesta=[
                'tipo'=>'exito',
                'mensaje'=>'Tarea Creada Correctamente ',
                'id'=>$resultado['id'],
                'proyectoId'=>$proyecto->id

            ];

            echo json_encode($respuesta);



        }
        
    }

    public static function actualizar(){
        if($_SERVER['REQUEST_METHOD']==='POST'){

            session_start();

            //Validar que el proyecto exista
            $proyecto=Proyecto::where('url',$_POST['proyectoId']);


            //Se corroborá de que la url leida mediante JS y pasa por FormData este asociada a algún proyecto
            if(!$proyecto || $proyecto->propietarioId !== $_SESSION['id']){
                $respuesta=[
                    'tipo'=>'error',
                    'mensaje'=>'Hubo un Error al Actualizar la Tarea'
                ];

                echo json_encode($respuesta);
               return;
            }

            $tarea=new Tarea($_POST);
            $tarea->proyectoId=$proyecto->id;

            $resultado=$tarea->guardar();

            if($resultado){
                $respuesta=[
                    'tipo'=>'exito',
                    'id'=>$tarea->id,
                    'proyectoId'=>$proyecto->id,
                    'mensaje'=>'Tarea Actualizada correctamente'
    
                ];

                echo json_encode(['respuesta'=>$respuesta]);

            }
        
        }

    }

    public static function eliminar(){
        if($_SERVER['REQUEST_METHOD']==='POST'){
            session_start();

            $proyecto=Proyecto::where('url',$_POST['proyectoId']);

            if(!$proyecto || $proyecto->propietarioId !== $_SESSION['id']){
                $respuesta=[
                    'tipo'=>'error',
                    'mensaje'=>'Hubo un Error al Eliminar la Tarea'
                ];

                echo json_encode($respuesta);
               return;

            }

            $tarea=new Tarea($_POST);
            $resultado= $tarea->eliminar();

            
            $respuesta=[
                'resultado'=>$resultado,
                'tipo'=>'exito',
                'mensaje'=>'Tarea Eliminada Correctamente'  
             ];

            

            echo json_encode($respuesta);
        }

    }
}