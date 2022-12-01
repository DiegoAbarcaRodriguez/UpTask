(function () {


    obtenerTareas(); //Función que obtiene las tareas pertinente a la URL del proyecto solicitado

    let tareas=[]; //Variable global que funge como obejto del virtual DOM en la tabla de tareas de la BD
    let filtradas=[]; //Variable global para filtrar tareas

    //Botón para mostrar el modal de agregar tarea
    const nuevaTareaBtn=document.querySelector('#agregar-tarea');
    nuevaTareaBtn.addEventListener('click',function () {
        mostrarFormulario();
    });


    //Filtro para mostrar tareas
    const filtros=document.querySelectorAll('#filtros input[type="radio"]');
    filtros.forEach(filtro=>{
        filtro.addEventListener('input',filtrarTareas);
    });

    function filtrarTareas(e){
        const filtro=e.target.value;

        if(filtro !== ''){
            filtradas=tareas.filter(tarea=> tarea.estado === filtro);
        }else{
            filtradas=[];
        }

        mostrarTareas();
    }

  async  function obtenerTareas() {
        try {   
           
            const id=obtenerProyecto();
            const  url=`/api/tareas?id=${id}`;

            const respuesta= await fetch(url);
            const resultado= await respuesta.json();

            tareas=resultado.tareas; //Se reescribe contenido de variable global
            mostrarTareas();

        } catch (error) {
            console.log(error);
        }
    }

    function mostrarTareas() {
        
        limpiarTareas(); //Limpia el arreglo global de Tareas, para evita que Hallan duplicados al momento de realizar

        totalPendientes();
        totalCompletadas();

        const arrayTareas=filtradas.length ? filtradas : tareas;

        if(arrayTareas.length===0){

            const contenedorTareas=document.querySelector('#listado-tareas');

            const textoNoTareas=document.createElement('LI');
            textoNoTareas.textContent='No Hay Tareas';
            textoNoTareas.classList.add('no-tareas');

            contenedorTareas.appendChild(textoNoTareas);
            
            return;
        }

        const estados={
            0:'Pendiente',
            1:'Completa'
        }
      
        arrayTareas.forEach(tarea=> {
            
            const contenedorTarea=document.createElement('LI');
            contenedorTarea.dataset.tareaId=tarea.id;
            contenedorTarea.classList.add('tarea');

          
            const nombreTarea=document.createElement('P');
            nombreTarea.textContent=tarea.nombre;
            nombreTarea.ondblclick=function () { //Cambiar nombre de la tarea al dar doble click
                mostrarFormulario(true,{...tarea});
            }
          

            const opcionesDiv=document.createElement('DIV');
            opcionesDiv.classList.add('opciones');


            //Botones
            const btnEstadoTarea=document.createElement('BUTTON');
            btnEstadoTarea.classList.add('estado-tarea');
            btnEstadoTarea.classList.add(`${estados[tarea.estado].toLowerCase()}`);
            btnEstadoTarea.textContent=estados[tarea.estado];
            btnEstadoTarea.dataset.estadoTarea=tarea.estado;
            btnEstadoTarea.ondblclick=function () {
                cambiarEstadoTarea({...tarea}); //Extrae el objeto tarea, lo asigna a una nueva variable con ese nombre y como un objeto , para evitar modificar el objeto global Tareas, antes de que se realize el cambio.
            }

            const btnEliminarTarea=document.createElement('BUTTON');
            btnEliminarTarea.classList.add('eliminar-tarea');
            btnEliminarTarea.dataset.idTarea=tarea.id;
            btnEliminarTarea.textContent='Eliminar Tarea';
            btnEliminarTarea.ondblclick=function () {
                confirmarEliminarTarea({...tarea});
            }

            opcionesDiv.appendChild(btnEstadoTarea);
            opcionesDiv.appendChild(btnEliminarTarea);

            contenedorTarea.appendChild(nombreTarea);
            contenedorTarea.appendChild(opcionesDiv);
            
            const listadoTareas=document.querySelector('#listado-tareas');
            listadoTareas.appendChild(contenedorTarea);

            
        });
    }

    function totalPendientes() {
        const totalPendientes=tareas.filter(tarea=>tarea.estado==='0');
        const pendientesRadio=document.querySelector('#pendientes');

        if(totalPendientes.length===0){
            pendientesRadio.disabled=true;
        }else{
            pendientesRadio.disabled=false;
        }
    }

    function totalCompletadas() {
        const totalCompletadas=tareas.filter(tarea=>tarea.estado==='1');
        const completadasRadio=document.querySelector('#completadas');

        if(totalCompletadas.length===0){
            completadasRadio.disabled=true;
        }else{
            completadasRadio.disabled=false;
        }
    }

    function mostrarFormulario(editar=false,tarea={}) {
        const modal=document.createElement('DIV');
        modal.classList.add('modal');

        modal.innerHTML=`
            <form class="formulario nueva-tarea">

                <legend>${editar ? 'Editar Tarea' : 'Añade una tueva tarea'}</legend>

                <div class="campo"> 
                    <label for="tarea">Tarea</label>
                    <input  type="text" name="tarea" id="tarea" placeholder="${tarea.nombre ? 'Editar la Tarea' : 'Añadir Tarea al Proyecto Actual'}" value="${tarea.nombre ? tarea.nombre : ''}"> </input>
                </div>

                <div class="opciones">
                    <input type="submit" class="submit-nueva-tarea" value="${tarea.nombre ? 'Guardar Cambios' : 'Añadir Tarea'}"></input>
                    <button class="cerrar-modal" type="button">Cancelar </button>
                </div>


            </form>`;

            setTimeout(() => {
                const formulario=document.querySelector('.formulario');
                formulario.classList.add('animar');//Para añadir animación con Transition en SASS(_modal.scss)
            }, 0);

            modal.addEventListener('click',function (e) { //Se puede aplicar un evento en el ya que se empleo SCRIPTING para su creación sin embargo el HTML generado con InnerHtml es necesario emplear el concepto de delegation
                e.preventDefault();//Prevenir que ocurra un dirrecionamiento al hacer click el input submit

                
            //--------------Aplicando delegation para determinar cuando se dió click en cerrar
                if(e.target.classList.contains('cerrar-modal')){ //Identifica que se dió click en el boton de cerrar


                    const formulario=document.querySelector('.formulario');
                    formulario.classList.add('cerrar'); //Para añadir animación con Transition en SASS(_modal.scss)


                    setTimeout(() => {
                        modal.remove();
                    }, 500);
                }


              //--------------Aplicando delegation para determinar cuando se dió click en crear-tarea
              if(e.target.classList.contains('submit-nueva-tarea')){
                   
                   const nombreTarea=document.querySelector('#tarea').value.trim();//El trim elimina espacios en blanco

                   if(nombreTarea===''){
                       //Mostrar alerta de error
                       mostrarAlerta('El Nombre de la Tarea es Obligatorio','error',
                       document.querySelector('.formulario legend'));
                       return;
                   }

                   if(editar){
                        //Editar Tarea
                        tarea.nombre=nombreTarea;
                        actualizaTarea(tarea);
                   }else{

                        //Guardar nueva Tarea
                       
                        agregarTarea(nombreTarea);
                   }
           
              }

        

            })



            document.querySelector('.dashboard').appendChild(modal);


    }

  

    //Muestra un mensaje de validación en la interfaz
    function  mostrarAlerta(mensaje,tipo,referencia) {
        //Eliminar alertas previamente creadas
        $alertaPrevia=document.querySelector('.alerta');
        if($alertaPrevia){
            $alertaPrevia.remove();
        }


        const alerta=document.createElement('DIV');
        alerta.classList.add('alerta',tipo);
        alerta.textContent=mensaje;


        //Inserta elemento antes del legend
       // referencia.parentElement.insertBefore(alerta,referencia);        

       //Inserta elemento despues del legend
         referencia.parentElement.insertBefore(alerta,referencia.nextElementSibling); 

        //Elimina Alerta después de 5segundos
        setTimeout(() => {
            alerta.remove();
        }, 3000);
    }   

    //Consultar el servidor para añadir una nueva Tarea al proyecto actual(API a la tabla de tareas)
   async function agregarTarea(tarea) {
        //Construir la petición

        const datos=new FormData();
        datos.append('nombre',tarea);
        datos.append('proyectoId',obtenerProyecto());

     

        try {
            const url="http://localhost:3000/api/tarea";

            const respuesta= await fetch(url,{
                method:'POST',
                body:datos
            });

            const resultado= await respuesta.json();


             //Mostrar alerta de error
             mostrarAlerta(resultado.mensaje,resultado.tipo,
             document.querySelector('.formulario legend'));

             if(resultado.tipo==='exito'){
                setTimeout(() => {
                    document.querySelector('.modal').remove();
                }, 3000);

                //Agregar el objeto de tarea al global de Tareas(Virtual DOM)
                const tareaObj={
                    id:String(resultado.id),
                    nombre:tarea,
                    estado:'0',
                    proyectoId:resultado.proyectoId
                }
                //Las tareas previamente almacenadas en el global tras mostrarTareas(), se les agrega el nuevo objeto Tarea al momento de agregar una
                tareas=[...tareas,tareaObj];

                //Nuevamente se muestran la tareas generando el HTML meadiente Scripting
                mostrarTareas(); 
             }

        } catch (error) {
            
        }
        
    }

    function cambiarEstadoTarea(tarea) { //El tarea empleado es una copia del objeto correspondiente al botón clickeado
        

        const nuevoEstado= tarea.estado === '1' ? '0' : '1';
        tarea.estado=nuevoEstado;

        actualizaTarea(tarea);
    }

   async function actualizaTarea(tarea) { //Los cambios en nuestro Objeto general solo deben reflejarse cuando acontece una operación en la BD(insert,delete,update)
        const{estado,id,nombre,proyectoId}=tarea;

        const datos=new FormData();
        datos.append('estado',estado);
        datos.append('nombre',nombre);
        datos.append('id',id);
        datos.append('proyectoId',obtenerProyecto());

        try {
            const url="http://localhost:3000/api/tarea/actualizar";

            const respuesta=await fetch(url,{
                method:'POST',
                body:datos
            });

            const resultado=await respuesta.json();

            if(resultado.respuesta.tipo==='exito'){
                Swal.fire(resultado.respuesta.mensaje,'Operación exitosa','success');

                modal=document.querySelector('.modal');
                if(modal){
                    modal.remove();
                }
               

                //Actualizar objeto general (VirtualDOM)

                tareas=tareas.map(tareaMemoria=>{ 
                    if(tareaMemoria.id === id){
                        tareaMemoria.estado=estado;
                        tareaMemoria.nombre=nombre;
                    }

                    return tareaMemoria; 
                    
                });

                mostrarTareas();//Se reconstruye el HTML de las tareas con el cambio de estado pertinente

            }

        } catch (error) {
            console.log(error);
        }
    }

    function confirmarEliminarTarea(tarea) {
         
        Swal.fire({
        title: '¿Eliminar Tarea?',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText:'No'
        }).then((result) => {
            if (result.isConfirmed) {
               eliminarTarea(tarea);
            } 
        })
    }

   async function eliminarTarea(tarea) {
        const{estado,id,nombre}=tarea;

        const datos=new FormData();
        datos.append('estado',estado);
        datos.append('nombre',nombre);
        datos.append('id',id);
        datos.append('proyectoId',obtenerProyecto());

        try {
            const url="http://localhost:3000/api/tarea/eliminar";

            const respuesta=await fetch(url,{
                method:'POST',
                body:datos
            })

            const resultado= await respuesta.json();
           

            if(resultado.resultado){
                //mostrarAlerta(resultado.mensaje,resultado.tipo, document.querySelector('.contenedor-nueva-tarea'));

                swal.fire('Eliminado!',resultado.mensaje,'success');

                //Actualizar objeto general (VirtualDOM)
                tareas=tareas.filter(tareaMemoria=>{
                   return tareaMemoria.id !== id
                });

                //Se reconstruye el HTML con el cambio del objeto general
                mostrarTareas();
            }
        } catch (error) {
            console.log(error);
        }
    }

    function obtenerProyecto() {
           //Leer el contenido de la URL actual
           const proyectoParams= new URLSearchParams(window.location.search);
          
           const proyecto=Object.fromEntries(proyectoParams.entries()); //Permite visualizar el contenido de la instancia de un objeto.
           return proyecto.id;
    }   

    function limpiarTareas() {
        const listadoTareas=document.querySelector('#listado-tareas');

        while(listadoTareas.firstChild){
            listadoTareas.removeChild(listadoTareas.firstChild);
        }
    }

})(); //Función IIFE(Las variables declaradas dentro de ella solo existen este archivo y la función es ejecutada inmediantamente)