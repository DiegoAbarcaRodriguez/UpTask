<?php 
namespace Controllers;
use MVC\Router;
use Model\Usuario;
use Classes\Email;
class LoginController{

    public static function login(Router $router){

        $usuario=new Usuario();
        $alertas=[];

        if($_SERVER['REQUEST_METHOD']==='POST'){
            $usuario=new Usuario($_POST);

            //Validar entradas del formulario
            $alertas=$usuario->validarLogin();

            if(empty($alertas)){
                //Validar que el usuario exista y esté confirmado
                $usuario=Usuario::where('email',$usuario->email);

                if(!$usuario || !$usuario->confirmado){

                    Usuario::setAlerta('error','No exite el usuario o no está confirmado');

                }else{

                    //Comprobar contraseña
                    if(password_verify($_POST['password'],$usuario->password)){
                        //Iniciar Sesión
                        session_start();
                        $_SESSION['id']=$usuario->id;
                        $_SESSION['nombre']=$usuario->nombre;
                        $_SESSION['email']=$usuario->email;

                        $_SESSION['login']=true;

                        //redireccionar
                        header('Location: /dashboard');


                    }else{

                        Usuario::setAlerta('error','Password incorrecto');

                    }
                }
            }

         
        }

        $alertas=Usuario::getAlertas();
        $router->render('auth/login',[
            'titulo'=>'Iniciar Sesión',
            'usuario'=>$usuario,
            'alertas'=>$alertas
        ]);
    }

    public static function logout(Router $router){

        session_start();
        $_SESSION=[];
        header('Location: /');

    }



    public static function crear(Router $router){

        $usuario=new Usuario();
        $alertas=[];

        if($_SERVER['REQUEST_METHOD']==='POST'){

            $usuario->sincronizar($_POST);
            
            $alertas=$usuario->validarNuevaCuenta();

            if(empty($alertas)){
                $existeUsuario=Usuario::where('email',$usuario->email);

                if($existeUsuario){
                    Usuario::setAlerta('error','El usuario ya está registrado');
                    $alertas=Usuario::getAlertas();
                }else{
                    $usuario->hashearPassword();

                    unset($usuario->password2);//Elimina el password2 empleando meramente para comparar que el password se haya escrito correctamente

                    $usuario->crearToken();

                    $resultado=$usuario->guardar();

                    //Enviar Email de confirmación de cuenta
                    $email=new Email($usuario->email,$usuario->nombre,$usuario->token);

                    $email->enviarConfirmacion();

                    if($resultado){
                        header('Location: /mensaje');
                    }
                }
            }
        }

        $router->render('auth/crear',[
            'titulo'=>'Crea tu cuenta en UpTask',
            'usuario'=>$usuario,
            'alertas'=>$alertas
        ]);
    }

    public static function olvide(Router $router){
        $alertas=[];
        if($_SERVER['REQUEST_METHOD']==='POST'){

            $usuario=new Usuario($_POST);
            $alertas=$usuario->validarEmail();

            if(empty($alertas)){
                $usuario=Usuario::where('email',$usuario->email);

                if($usuario && $usuario->confirmado){
                    //Generar Token
                    $usuario->crearToken();
                    unset($usuario->password2);

                    //Actualizar Usuario
                    $usuario->guardar();

                    //Enviar Email
                    $email=new Email($usuario->email,$usuario->nombre,$usuario->token);
                    $email->enviarInstrucciones();

                    //Imprimir Alerta
                    Usuario::setAlerta('exito','Hemos enviado las instrucciones a tu email');

                }else{
                    Usuario::setAlerta('error','El usuario no existe o no está confirmado');
                   
                }
            }
        }

        $alertas=Usuario::getAlertas();

        $router->render('auth/olvide',[
            'titulo'=>'Olvide mi Password',
            'alertas'=>$alertas
        ]);
        
    }


    public static function reestablecer(Router $router){

        $token=s($_GET['token']);
        $mostrar=true; //Variable que muestra el formulario de reestablecer solo si el token ingresado es válido

        //Si el token ingresado no es válido retorna al login
        if(!$token) header('Location: /');

        $usuario=Usuario::where('token',$token);

        //Verifica que el token pertenezca a un usuario
        if(empty($usuario)){
            Usuario::setAlerta('error','Token no Válido');
            $mostrar=false;
        }else{

            if($_SERVER['REQUEST_METHOD']==='POST'){
                //Valida Nuevo password
                $usuario->sincronizar($_POST);
                $alertas=$usuario->validarPassword();

                if(empty($alertas)){
                    //Hashear nuevo password
                    $usuario->hashearPassword();

                    //Eliminar Token
                    $usuario->token='';

                    //Actualizar tabla de Usuario
                    $resultado= $usuario->guardar();

                    //Redireccionar
                    if($resultado){
                        header('Location: /' );
                    }
                   
                }
            
            }
    

        }
        
      
       $alertas=Usuario::getAlertas();

        $router->render('auth/reestablecer',[
            'titulo'=>'Reestablecer Password',
            'alertas'=>$alertas,
            'mostrar'=>$mostrar
        ]);
        
    }

    public static function mensaje(Router $router){
        

        $router->render('auth/mensaje',[
            'titulo'=>'Cuenta Creada Exitosamente'
        ]);
    }

    public static function confirmar(Router $router){
        $token=s($_GET['token']);

        if(!$token) header('Location: /');

        $usuario=Usuario::where('token',$token);

        if(empty($usuario)){
            Usuario::setAlerta('error','Token no Válido');
        }else{
            $usuario->confirmado=1;
            $usuario->token='';

            $usuario->guardar();

            Usuario::setAlerta('exito','Cuenta Comprobada Correctamente');

        }

        $alertas=Usuario::getAlertas();

        $router->render('auth/confirmar',[
            'titulo'=>'Confirmar tu Cuenta UpTask',
            'alertas'=>$alertas
        ]);
    }

}