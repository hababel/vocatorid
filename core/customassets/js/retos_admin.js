// Gestion dinámica de retos para el dashboard del organizador

async function cargarRetos(){
    const res = await fetch(`${URL_BASE}evento/estadoRetos/${ID_EVENTO}`);
    const data = await res.json();
    if(!data.exito) return;
    const tbody = document.getElementById('lista-retos');
    tbody.innerHTML = '';
    data.retos.forEach(r => {
        const tr = document.createElement('tr');
        let acciones = `<button class="btn btn-sm btn-secondary" onclick="verDetalles(${r.id})">Ver Detalles</button>`;
        if(r.estado === 'Pendiente'){
            acciones = `<button class="btn btn-sm btn-primary me-2" onclick="activarReto(${r.id})">Activar Ahora</button>` + acciones;
        }
        tr.innerHTML = `<td>${r.descripcion}</td><td>${r.hora_inicio}</td><td>${r.hora_fin}</td><td>${r.estado}</td><td>${r.completados}</td><td>${acciones}</td>`;
        tbody.appendChild(tr);
    });
}

async function crearReto(ev){
    ev.preventDefault();
    const form = ev.target;
    const formData = new FormData(form);
    const res = await fetch(`${URL_BASE}evento/crearReto/${ID_EVENTO}`, {method:'POST', body: formData});
    const data = await res.json();
    if(data.exito){
        form.reset();
        alert('Reto creado correctamente');
        cargarRetos();
    }else{
        alert('Error al crear reto');
    }
}

async function activarReto(id){
    const res = await fetch(`${URL_BASE}evento/activarReto/${id}`, {method:'POST'});
    const data = await res.json();
    if(data.exito){
        cargarRetos();
    }
}

async function verDetalles(id){
    const res = await fetch(`${URL_BASE}evento/detalleReto/${id}`);
    const data = await res.json();
    if(!data.exito) return;
    const tbody = document.getElementById('detalle-body');
    tbody.innerHTML = '';
    data.registros.forEach(r => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${r.nombre}</td><td>${r.codigo_ingresado}</td><td>${r.fecha_registro}</td>`;
        tbody.appendChild(tr);
    });
    const modal = new bootstrap.Modal(document.getElementById('detalleModal'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-reto');
    if(form){
        form.addEventListener('submit', crearReto);
    }
    cargarRetos();
    setInterval(cargarRetos, 10000);
});
