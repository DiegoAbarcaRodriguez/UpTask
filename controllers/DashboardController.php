<?php 
namespace Controllers;

use Model\Proyecto;
use MVC\Router;
use Model\Usuario;

class DashboardController{
    public static function index(Router $router){

        session_start(); //Trae valor definidos en la super global $_SESSION
       

        isAuth();//¿Está autenticado?

        $id=$_SESSION['id'];

        $proyectos=Proyecto::belongsTo('propietarioId',$id);

        $router->render('dashboard/index',[
            'titulo'=>'Proyectos',
            'proyectos'=>$proyectos
        ]);
    }

    public static function crear_proyecto(Router $router){

        session_start();
        isAuth();
        $alertas=[];

        
        if($_SERVER['REQUEST_METHOD']==='POST'){

            $proyecto=new Proyecto($_POST);

            //Valida el nombre del proyecto
            $alertas= $proyecto->validarProyecto();

            if(empty($alertas)){
                //Generar url única
                $proyecto->url=md5(uniqid());

                //Almacenar creador del proyecto
                $proyecto->propietarioId=$_SESSION['id'];

                //Guarda proyecto
                $proyecto->guardar();

                //Redireccionar 
                header('Location: /proyecto?id='.$proyecto->url);

            }

        }

        $router->render('dashboard/crear-proyecto',[
            'titulo'=>'Crear Proyecto',
            'alertas'=>$alertas
        ]);
    }

    public static function proyecto(Router $router){
        
        session_start();

        isAuth();

        $token=$_GET['id'];

        if(!$token) header('Location: /dashboard');

        //Revisar que la persona que visita el proyecto es quien la creo,
        $proyecto=Proyecto::where('url',$token);

        if($proyecto->propietarioId !== $_SESSION['id']) {
            header('Location: /dashboard');
        }

        $router->render('dashboard/proyecto',[
            'titulo'=>$proyecto->proyecto
        ]);
    }

    public static function perfil(Router $router){

        session_start();

        isAuth();

        $alertas=[];

       $usuario= Usuario::find($_SESSION['id']);

       if($_SERVER['REQUEST_METHOD']==='POST'){
            $usuario->sincronizar($_POST);

            $alertas=$usuario->validarPerfil();

            if(empty($alertas)){

                $existeUsuario=Usuario::where('email',$usuario->email);

                if($existeUsuario && $existeUsuario->id !== $usuario->id){
                    //Mensaje de Error
                    Usuario::setAlerta('error','Email no Válido, ya pertenece a otra cuenta');
                }else{
                     //Guardar Usuario
                    $usuario->guardar();

                    //Asignar el nombre nuevo a la barra
                    $_SESSION['nombre']=$usuario->nombre;

                    Usuario::setAlerta('exito','Guardado Correctamente');
                   

                }

               

            }
       }

       $alertas= Usuario::getAlertas();

        $router->render('dashboard/perfil',[
            'titulo'=>'Perfil',
            'usuario'=>$usuario,
            'alertas'=>$alertas
        ]);
    }

    public static function cambiar_password(Router $router){

        session_start();

        isAuth();

        $alertas=[];

        if($_SERVER['REQUEST_METHOD']==='POST'){
            $usuario=Usuario::find($_SESSION['id']);

            
            
            $usuario->sincronizar($_POST);


            $alertas=$usuario->nuevo_password();

            if(empty($alertas)){
                $resultado=$usuario->comprobarPassword();

                if($resultado){
                    //Asignar nuevo Password

                    $usuario->password=$usuario->passwordNuevo;

                    //Eliminar propiedades no necesarias
                    unset($usuario->passwordActual);
                    unset($usuario->passwordNuevo);

                    //hashear y guardar nuevo password
                    $usuario->hashearPassword();
                    $respuesta= $usuario->guardar();

                    if($respuesta){
                        Usuario::setAlerta('exito','Password Guardado Correctamente');
                    }

                }else{
                    Usuario::setAlerta('error','Password Incorrecto');
                }
            }
        }

        $alertas=Usuario::getAlertas();
        $router->render('dashboard/cambiar-password',[
            'titulo'=>'Cambiar Password',
            'alertas'=>$alertas
        ]);
    }


}