<?php 
namespace Model;

class Usuario extends ActiveRecord {
    protected static $tabla='usuarios'; 
    protected static $columnasDB=['id','nombre','email','password','token','confirmado'];

    public function __construct($args=[]){
        $this->id=$args['id']??null;
        $this->nombre=$args['nombre']??'';
        $this->email=$args['email']??'';
        $this->password=$args['password']??'';
        $this->passwordActual=$args['passwordActual']??'';
        $this->passwordNuevo=$args['passwordNuevo']??'';
        $this->password2=$args['password2']??'';
        $this->token=$args['token']??'';
        $this->confirmado=$args['confirmado']??0;

    }

    public function validarLogin(){

        if(!$this->email){
            self::$alertas['error'][]='El email del Usuario es Obligatorio';
        }

        if(!filter_var($this->email,FILTER_VALIDATE_EMAIL)){
            self::$alertas['error'][]='Email no válido';
        }

        if(!$this->password){
            self::$alertas['error'][]='El password no puede ir vacio';
        }


        return self::$alertas;
    }

    public function validarNuevaCuenta()
    {
        if(!$this->nombre){
            self::$alertas['error'][]='El nombre del Usuario es Obligatorio';
        }

        if(!$this->email){
            self::$alertas['error'][]='El email del Usuario es Obligatorio';
        }

        if(!$this->password){
            self::$alertas['error'][]='El password no puede ir vacio';
        }

        if(strlen($this->password)<6){
            self::$alertas['error'][]='El password debe contener al menos 6 carácteres';
        }
        if($this->password !== $this->password2){
            self::$alertas['error'][]='Los password son diferentes';
        }


        return self::$alertas;
    }

    public function validarPassword(){

        if(!$this->password){
            self::$alertas['error'][]='El password no puede ir vacio';
        }

        if(strlen($this->password)<6){
            self::$alertas['error'][]='El password debe contener al menos 6 carácteres';
        }

        return self::$alertas;
    }

   public function hashearPassword(){
        $this->password=password_hash($this->password,PASSWORD_BCRYPT);
    }

    public function crearToken(){
        $this->token=uniqid();
    }

    public function validarEmail(){
        if(!$this->email){
            self::$alertas['error'][]='El email del Usuario es Obligatorio';
        }

        if(!filter_var($this->email,FILTER_VALIDATE_EMAIL)){
            self::$alertas['error'][]='Email no válido';
        }

        return self::$alertas;

    }

    public function validarPerfil(){
        if(!$this->nombre){
            self::$alertas['error'][]='El Nombre es Obligatorio';
        }

        if(!$this->email){
            self::$alertas['error'][]='El Email es Obligatorio';
        }

        if(!filter_var($this->email,FILTER_VALIDATE_EMAIL)){
            self::$alertas['error'][]='Email no válido';
        }

        return self::$alertas;

    }

    public function nuevo_password(){
        if(!$this->passwordActual){
            self::$alertas['error'][]='El Password Actual no puede ir vacio';
        }

        if(!$this->passwordNuevo){
            self::$alertas['error'][]='El Password Nuevo no puede ir vacio';
        }
        if(strlen($this->passwordNuevo)<6){
            self::$alertas['error'][]='El Password Nuevo debe tener al menos 6 carácteres';
        }

        return self::$alertas;
    }

    public function comprobarPassword(){
      return  password_verify($this->passwordActual,$this->password);
    }
}