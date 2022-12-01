<?php
namespace Classes;
use PHPMailer\PHPMailer\PHPMailer;


class Email{
    protected $email;
    protected $nombre;
    protected $token;

    public function  __construct($email,$nombre,$token)
    {
        $this->email=$email;
        $this->nombre=$nombre;
        $this->token=$token;
        
    }

    public function enviarConfirmacion(){
     
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Port = 2525;
        $mail->SMTPSecure = 'tls';
        $mail->Username = '5eddd4ca20d39e';
        $mail->Password = '3fd4f80c786ab2';

        $mail->setFrom('cuentas@Uptask.com');
        $mail->addAddress('cuentas@Uptask.com','uptask.com');
        $mail->Subject='Confirma tu Cuenta';

        $mail->isHTML(TRUE);
        $mail->CharSet='UTF-8';

        $contenido='<html>';
        $contenido.='<p>Hola <strong>'. $this->nombre .'</strong> Has creado tu cuenta en UpTask,solo debes confirmarla
        en el siguiente enlace</p>';
        $contenido.='<p>Presiona aquí: <a href="http://localhost:3000/confirmar?token='.$this->token.'">Confirmar Cuenta</a> </p>';
        $contenido.='Si tu no creaste esta cuenta, puedes ignorar este mensaje';
        $contenido.='</html>';

        $mail->Body=$contenido;
        

        $mail->send();
    }

    public function enviarInstrucciones(){
        
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Port = 2525;
        $mail->SMTPSecure = 'tls';
        $mail->Username = '5eddd4ca20d39e';
        $mail->Password = '3fd4f80c786ab2';

        $mail->setFrom('cuentas@Uptask.com');
        $mail->addAddress('cuentas@Uptask.com','uptask.com');
        $mail->Subject='Reestablece tu Password';

        $mail->isHTML(TRUE);
        $mail->CharSet='UTF-8';

        $contenido='<html>';
        $contenido.='<p>Hola <strong>'. $this->nombre .'</strong> Parece que has olvidado tu password sigue
        el siguiente enlace para recuperarlo</p>';
        $contenido.='<p>Presiona aquí: <a href="http://localhost:3000/reestablecer?token='.$this->token.'">Reestablecer Password</a> </p>';
        $contenido.='Si tu no creaste esta cuenta, puedes ignorar este mensaje';
        $contenido.='</html>';

        $mail->Body=$contenido;
        

        $mail->send();
    }
}