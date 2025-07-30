# Verificación con Retos Dinámicos

El organizador puede emitir retos temporales para que los asistentes validen su presencia en eventos virtuales. Cada reto tiene un código que cambia de forma periódica.

Cuando un evento cuenta con varios retos programados, la pantalla del asistente indica en cuánto tiempo se activará el siguiente. Al llegar la hora, el reto aparece automáticamente sin necesidad de recargar la página.

El asistente accede mediante su enlace personal `/asistencia/[token]` y ve el código activo. Debe ingresar dicho código para registrar su asistencia.

Los registros se guardan en la tabla `registros_retos` con la hora, IP y resultado. Si el código es correcto se genera además un registro en `registros_asistencia` asociado al reto correspondiente.
