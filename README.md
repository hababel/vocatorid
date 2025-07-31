# vocatorid

Este proyecto gestiona eventos y el registro de asistencia.
Inicialmente se usaban códigos QR para validar la participación,
pero actualmente los eventos virtuales se confirman mediante
**retos dinámicos**. El organizador puede programar varios retos
en distintos momentos para cumplir los requisitos de presencia.

Para ver el detalle del nuevo flujo consulta
[docs/verificacion_retos.md](docs/verificacion_retos.md). La guía
sobre problemas con QR solo aplica al kiosco físico y está en
[docs/guia_manejo_error_qr.md](docs/guia_manejo_error_qr.md).

## Gestión de eventos

Para administrar cada evento se utiliza la vista `app/views/eventos/gestionar.php`.
El controlador `EventoController` expone el método `gestionar($id_evento)` y la
ruta correspondiente es `/evento/gestionar/{id}`. Asegúrate de acceder a esta
URL para cargar el archivo `gestionar.php`.
