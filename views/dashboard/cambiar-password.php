<?php include_once __DIR__.'/header-dashboard.php';?>

<div class="contenedor-sm">

    <?php include_once __DIR__.'/../templates/alertas.php'; ?>

    <a href="/perfil" class="enlace">Volver a Perfil</a>


    <form action="/cambiar-password" class="formulario" method="POST">

        <div class="campo">
            <label>Password Actual</label>
            <input type="password" name="passwordActual"  placeholder="Tu Password Actual">
        </div>

        <div class="campo">
            <label>Password Nuevo</label>
            <input type="password" name="passwordNuevo"  placeholder="Tu Password Nuevo">
        </div>

        <input type="submit" value="Guardar Cambios">
    </form>
</div>
<?php include_once __DIR__.'/footer-dashboard.php';?>