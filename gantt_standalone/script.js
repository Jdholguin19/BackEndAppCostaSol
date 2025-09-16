console.log("script.js loaded");

let selectedProjectId = null;
let allUsers = [];


// --- Funciones de Utilidad para Fechas ---
function parseDateCustom(dateString) {
    if (typeof dateString === 'string' && dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
        const parts = dateString.split('-');
        return new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
    }
    return dateString;
}

function formatDateToYYYYMMDD(dateObj) {
    if (dateObj instanceof Date && !isNaN(dateObj)) {
        const year = dateObj.getFullYear();
        const month = (dateObj.getMonth() + 1).toString().padStart(2, '0');
        const day = dateObj.getDate().toString().padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    const today = new Date();
    const year = today.getFullYear();
    const month = (today.getMonth() + 1).toString().padStart(2, '0');
    const day = today.getDate().toString().padStart(2, '0');
    return `${year}-${month}-${day}`;
}


// --- Configuración de las columnas del Gantt ---
gantt.config.columns = [
    {name: "text", label: "Nombre de Tarea", tree: true, width: ''},
    {name: "start_date", label: "Fecha Inicio", align: "center"},
    {name: "duration", label: "Duración", align: "center"},
    {name: "end_date", label: "Fecha Fin", align: "center", template: function(task) {
        if (task.start_date && task.duration) {
            const endDate = gantt.calculateEndDate(task.start_date, task.duration);
            return formatDateToYYYYMMDD(endDate);
        }
        return "";
    }},
    {name: "owners", label: "Dueños", align: "center", template: function(task) {
        if (!task.owners || allUsers.length === 0) return "";
        let ownerIds = Array.isArray(task.owners) ? task.owners : String(task.owners).split(',').map(id => parseInt(id.trim())).filter(id => !isNaN(id));
        const ownerNames = ownerIds.map(id => {
            const user = allUsers.find(u => u.id === id);
            return user ? user.name : `ID:${id}`;
        }).filter(name => name);
        return ownerNames.join(', ');
    }},
    {
        name: "add_subtask",
        label: "",
        width: 44,
        template: function (task) {
            return '<div class="gantt_add"></div>';
        }
    }
];

gantt.config.editable = true;
gantt.config.date_grid = "%Y-%m-%d";
// Formato para enviar fechas al servidor, compatible con MySQL DATETIME
gantt.config.xml_date = "%Y-%m-%d %H:%i:%s";

gantt.templates.task_class = function (start, end, task) {
    if (task.color) {
        return "custom-task-color";
    }
    return "";
};

gantt.templates.task_style = function(start, end, task) {
    if (task.color) {
        return {
            background: task.color,
            borderColor: task.color
        };
    }
    return {};
};

// --- Deshabilitar Lightbox por Defecto ---
gantt.config.show_lightbox = false;
gantt.attachEvent("onBeforeLightbox", function(id) {
    return false;
});

gantt.init("gantt_here");

// --- Configuración del DataProcessor ---
const dp = new gantt.dataProcessor("api/save.php");
dp.setUpdateMode("row"); // Forzar el envío de todos los datos de la fila en cada actualización
dp.init(gantt);

dp.attachEvent("onAfterUpdate", function(id, action, tid, response){
    if(action == "deleted"){
        gantt.message("Task deleted");
    }
});

// --- Carga de Datos Inicial ---
(async () => {
    try {
        await loadUsers();
        await loadProjects();
    } catch (e) {
        console.error("Error during initial data loading:", e);
        alert("Ocurrió un error al cargar los datos iniciales.");
    }
})();


// --- Lógica de Proyectos ---
const projectSelect = document.getElementById('project-select');
const newProjectBtn = document.getElementById('new-project-btn');

async function loadProjects() {
    try {
        const response = await fetch('api/get_projects.php');
        if (!response.ok) throw new Error('Error al cargar proyectos: ' + response.statusText);
        const projects = await response.json();
        projectSelect.innerHTML = '<option value="">-- Seleccionar Proyecto --</option>';
        if (projects.length > 0) {
            projects.forEach(project => {
                const option = document.createElement('option');
                option.value = project.id;
                option.textContent = project.name;
                projectSelect.appendChild(option);
            });
            if (!selectedProjectId) {
                selectedProjectId = projects[0].id;
                projectSelect.value = selectedProjectId;
            } else {
                projectSelect.value = selectedProjectId;
            }
            loadGanttData(selectedProjectId);
        } else {
            gantt.clearAll();
            alert('No hay proyectos. Por favor, crea uno nuevo.');
        }
    } catch (error) {
        console.error('Error en loadProjects:', error);
        alert('No se pudieron cargar los proyectos.');
    }
}

function loadGanttData(projectId) {
    if (projectId) {
        gantt.clearAll();
        gantt.load(`api/data.php?project_id=${projectId}`);
    } else {
        gantt.clearAll();
    }
}

projectSelect.addEventListener('change', (event) => {
    selectedProjectId = event.target.value;
    loadGanttData(selectedProjectId);
});

newProjectBtn.addEventListener('click', async () => {
    const projectName = prompt('Introduce el nombre del nuevo proyecto:');
    if (projectName) {
        try {
            const response = await fetch('api/create_project.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ name: projectName })
            });
            const result = await response.json();
            if (result.success) {
                alert(result.message);
                selectedProjectId = result.id;
                await loadProjects();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('No se pudo crear el proyecto.');
        }
    }
});

// --- Carga de Usuarios ---
async function loadUsers() {
    try {
        const response = await fetch('api/get_users.php');
        if (!response.ok) throw new Error('Error al cargar usuarios: ' + response.statusText);
        const users = await response.json();
        allUsers = users.map(user => ({ id: parseInt(user.id, 10), name: user.name }));
    } catch (error) {
        console.error('Error al cargar usuarios:', error);
        alert('No se pudieron cargar los usuarios.');
    }
}

// --- Lógica para Dependencias entre Proyectos ---
function showCrossProjectLinkModal(sourceTaskId) {
    const modal = document.getElementById('cross-link-modal');
    document.getElementById('cross-link-source-task-id').value = sourceTaskId;
    const projectSelect = document.getElementById('cross-link-project-select');
    const taskSelect = document.getElementById('cross-link-task-select');
    projectSelect.innerHTML = '<option value="">-- Seleccione un proyecto --</option>';
    taskSelect.innerHTML = '<option value="">-- Primero seleccione un proyecto --</option>';
    const allProjects = Array.from(document.getElementById('project-select').options)
                             .map(opt => ({id: opt.value, name: opt.textContent}))
                             .filter(p => p.id && p.id != selectedProjectId);
    allProjects.forEach(project => {
        if (project.id && project.name.indexOf('--') === -1) {
             const option = document.createElement('option');
             option.value = project.id;
             option.textContent = project.name;
             projectSelect.appendChild(option);
        }
    });
    modal.style.display = 'block';
}

function closeCrossLinkModal() {
    document.getElementById('cross-link-modal').style.display = 'none';
}

document.getElementById('cross-link-project-select').addEventListener('change', async function() {
    const projectId = this.value;
    const taskSelect = document.getElementById('cross-link-task-select');
    taskSelect.innerHTML = '<option value="">Cargando tareas...</option>';
    if (!projectId) {
        taskSelect.innerHTML = '<option value="">-- Primero seleccione un proyecto --</option>';
        return;
    }
    try {
        const response = await fetch(`api/get_tasks_by_project.php?project_id=${projectId}`);
        if (!response.ok) throw new Error('Error al cargar las tareas del proyecto.');
        const tasks = await response.json();
        taskSelect.innerHTML = '<option value="">-- Seleccione una tarea --</option>';
        tasks.forEach(task => {
            const option = document.createElement('option');
            option.value = task.id;
            option.textContent = task.text;
            taskSelect.appendChild(option);
        });
    } catch (error) {
        console.error(error);
        taskSelect.innerHTML = '<option value="">Error al cargar tareas</option>';
        alert(error.message);
    }
});

async function saveCrossProjectLink() {
    const source_task_id = document.getElementById('cross-link-source-task-id').value;
    const target_task_id = document.getElementById('cross-link-task-select').value;
    const target_project_id = document.getElementById('cross-link-project-select').value;
    const type = document.getElementById('cross-link-type-select').value;
    if (!target_task_id || !target_project_id) {
        alert('Por favor, seleccione un proyecto y una tarea de destino.');
        return;
    }
    const link = { source_task_id: parseInt(source_task_id), source_project_id: parseInt(selectedProjectId), target_task_id: parseInt(target_task_id), target_project_id: parseInt(target_project_id), type: type };
    try {
        const response = await fetch('api/save_cross_project_link.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(link)
        });
        const result = await response.json();
        if (response.ok) {
            alert('Dependencia guardada con éxito.');
            closeCrossLinkModal();
            loadCrossProjectLinks(source_task_id);
        } else {
            throw new Error(result.message || `Error ${response.status}: ${response.statusText}`);
        }
    } catch (error) {
        console.error('Error al guardar la dependencia:', error);
        alert(`No se pudo guardar la dependencia: ${error.message}`);
    }
}

async function loadCrossProjectLinks(taskId) {
    const listElement = document.getElementById('cross-links-list-modal');
    listElement.innerHTML = '<li>Cargando dependencias...</li>';
    try {
        const response = await fetch(`api/get_cross_project_links.php?task_id=${taskId}&project_id=${selectedProjectId}`);
        if (!response.ok) throw new Error('No se pudieron cargar las dependencias.');
        const links = await response.json();
        listElement.innerHTML = '';
        if (links.length === 0) {
            listElement.innerHTML = '<li>No hay dependencias para esta tarea.</li>';
            return;
        }
        links.forEach(link => {
            const listItem = document.createElement('li');
            let text = '';
            if (link.source_task_id == taskId) {
                text = `Precede a: "${link.target_task_name}" (Proyecto: ${link.target_project_name})`;
            } else {
                text = `Viene después de: "${link.source_task_name}" (Proyecto: ${link.source_project_name})`;
            }
            listItem.textContent = text;
            listElement.appendChild(listItem);
        });
    } catch (error) {
        console.error(error);
        listElement.innerHTML = `<li>Error al cargar dependencias: ${error.message}</li>`;
    }
}

// --- Creación de Tareas y Lógica de Lightbox ---

function addNewTask(parentId = null) {
    openCustomLightbox(null, true, parentId);
}

gantt.attachEvent("onTaskClick", function(id, e){
    if (e.target.classList.contains('gantt_add')) {
        addNewTask(id);
        return false;
    }
    return true;
});

gantt.attachEvent("onTaskDblClick", function(id, e) {
    if (gantt.isTaskExists(id)) {
        openCustomLightbox(id, false, null);
    }
    return false;
});

function openCustomLightbox(taskId, isNew, parentId) {
    const modal = document.getElementById('custom-lightbox-modal');
    const form = document.getElementById('custom-lightbox-form');
    const textInput = document.getElementById('modal-task-text');
    const startDateInput = document.getElementById('modal-task-start-date');
    const durationInput = document.getElementById('modal-task-duration');
    const progressInput = document.getElementById('modal-task-progress');
    const progressValue = document.getElementById('modal-task-progress-value');
    const ownersSelect = document.getElementById('modal-task-owners');
    const colorInput = document.getElementById('modal-task-color');
    const taskIdInput = document.getElementById('modal-task-id');
    const isNewInput = document.getElementById('modal-task-is-new');
    const parentIdInput = document.getElementById('modal-task-parent-id');

    isNewInput.value = isNew ? '1' : '0';
    form.reset();
    ownersSelect.innerHTML = '';
    allUsers.forEach(user => {
        const option = document.createElement('option');
        option.value = user.id;
        option.textContent = user.name;
        ownersSelect.appendChild(option);
    });

    if (isNew) {
        taskIdInput.value = '';
        parentIdInput.value = parentId || '';
        textInput.value = 'Nueva Tarea';
        startDateInput.value = formatDateToYYYYMMDD(new Date());
        durationInput.value = 1;
        progressInput.value = 0;
        progressValue.textContent = '0%';
        colorInput.value = '#3498db'; // Default color
        document.getElementById('cross-links-list-modal').innerHTML = '<li>Las dependencias se pueden añadir una vez guardada la tarea.</li>';
    } else {
        const task = gantt.getTask(taskId);
        if (!task) { alert("Error: Tarea no encontrada: " + taskId); return; }
        taskIdInput.value = task.id;
        parentIdInput.value = task.parent;
        textInput.value = task.text;
        startDateInput.value = formatDateToYYYYMMDD(task.start_date);
        durationInput.value = task.duration || 1;
        const progress = task.progress || 0;
        progressInput.value = progress;
        progressValue.textContent = `${Math.round(progress * 100)}%`;
        const ownerIds = task.owners || [];
        Array.from(ownersSelect.options).forEach(opt => {
            opt.selected = ownerIds.includes(parseInt(opt.value));
        });
        colorInput.value = task.color || '#3498db';
        loadCrossProjectLinks(taskId);
    }
    modal.style.display = 'block';
}

function closeCustomLightbox() {
    document.getElementById('custom-lightbox-modal').style.display = 'none';
}

function saveCustomTask() {
    const isNew = document.getElementById('modal-task-is-new').value === '1';

    if (isNew) {
        const parentId = document.getElementById('modal-task-parent-id').value || 0;
        const newTask = {
            text: document.getElementById('modal-task-text').value,
            start_date: document.getElementById('modal-task-start-date').value,
            duration: parseInt(document.getElementById('modal-task-duration').value, 10) || 1,
            progress: parseFloat(document.getElementById('modal-task-progress').value) || 0,
            owners: Array.from(document.getElementById('modal-task-owners').selectedOptions).map(opt => opt.value),
            color: document.getElementById('modal-task-color').value
        };
        // addTask should trigger the DataProcessor automatically with 'inserted' status
        gantt.addTask(newTask, parentId);
        dp.sendData(); // Forzar el envío de datos pendientes para asegurar el guardado inmediato.
    } else {
        const taskId = document.getElementById('modal-task-id').value;
        if (!gantt.isTaskExists(taskId)) {
            alert(`Error: La tarea con ID ${taskId} no fue encontrada.`);
            return;
        }
        const task = gantt.getTask(taskId);

        // Update task object properties
        task.text = document.getElementById('modal-task-text').value;
        task.start_date = parseDateCustom(document.getElementById('modal-task-start-date').value);
        task.duration = parseInt(document.getElementById('modal-task-duration').value, 10) || 1;

        // Recalcular la fecha de fin para asegurar la consistencia y evitar que el auto-scheduling la revierta
        task.end_date = gantt.calculateEndDate(task.start_date, task.duration);

        task.progress = parseFloat(document.getElementById('modal-task-progress').value) || 0;
        task.owners = Array.from(document.getElementById('modal-task-owners').selectedOptions).map(opt => parseInt(opt.value));
        task.color = document.getElementById('modal-task-color').value;
        
        // Refresh the UI for the task
        gantt.updateTask(taskId);
        
        // Explicitly trigger the DataProcessor to send the update to the server
        dp.sendData(taskId);
    }
    closeCustomLightbox();
}

function deleteCustomTask() {
    const taskId = document.getElementById('modal-task-id').value;
    const customLightbox = document.getElementById('custom-lightbox-modal');
    customLightbox.style.display = 'none';
    gantt.confirm({
        title: "Confirmar Eliminación",
        text: `¿Estás seguro de que quieres eliminar la tarea "<b>${gantt.getTask(taskId).text}</b>"?`,
        ok: "Sí",
        cancel: "No",
        callback: function(result) {
            if (result) {
                if (gantt.isTaskExists(taskId)) {
                    gantt.deleteTask(taskId);
                    dp.setUpdated(taskId, true, 'deleted');
                    dp.sendData();
                }
            } else {
                customLightbox.style.display = 'block';
            }
        }
    });
}

document.getElementById('modal-task-progress').addEventListener('input', function() {
    document.getElementById('modal-task-progress-value').textContent = `${Math.round(this.value * 100)}%`;
});

// --- Eventos del DataProcessor y Carga de Tareas ---
dp.attachEvent("onBeforeUpdate", function(id, status, data){
    if (status === "inserted" && selectedProjectId) {
        data.project_id = selectedProjectId;
    }
    if (data.owners && Array.isArray(data.owners)) {
        data.owners = data.owners.join(',');
    } else if (!data.owners) {
        data.owners = "";
    }
    if (data.color) {
        // Already a string, just ensure it's sent
    } else {
        data.color = "";
    }
    return true;
});

gantt.attachEvent("onTaskLoading", function(task) {
    if (task.owners && typeof task.owners === 'string') {
        task.owners = task.owners.split(',').map(id => parseInt(id.trim())).filter(id => !isNaN(id));
    } else {
        task.owners = [];
    }
    if (typeof task.start_date === 'string') {
        task.start_date = parseDateCustom(task.start_date);
    }
    return true;
});

gantt.attachEvent("onAfterTaskDrag", function(id, mode, e){
    const task = gantt.getTask(id);
    dp.sendData(id);
});