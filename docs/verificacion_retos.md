# Verificación con Retos Dinámicos

El organizador puede emitir retos temporales para que los asistentes validen su presencia en eventos virtuales. Cada reto tiene un código que cambia cada 15 segundos.

El asistente accede mediante su enlace personal `/asistencia/[token]` y ve el código activo. Debe ingresar dicho código para registrar su asistencia.

Los registros se guardan en la tabla `registros_retos` con la hora, IP y resultado.
