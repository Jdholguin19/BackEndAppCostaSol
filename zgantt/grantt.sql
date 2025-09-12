  CREATE TABLE IF NOT EXISTS gantt_projects (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

  -- Tabla para las tareas de Gantt
  CREATE TABLE IF NOT EXISTS gantt_tasks (
    id INT(11) NOT NULL AUTO_INCREMENT,
    project_id INT(11) NOT NULL DEFAULT 0, -- ID del proyecto al que pertenece la tarea
    text VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    duration INT(11) NOT NULL,
    progress FLOAT NOT NULL,
    parent INT(11) NOT NULL,
    sortorder INT(11) NOT NULL,
    open TINYINT(1) NOT NULL DEFAULT '1',
    owners TEXT NULL, -- Almacena los IDs de los dueños separados por comas
    PRIMARY KEY (id),
    INDEX idx_project_id (project_id),
    CONSTRAINT fk_gantt_tasks_project_id FOREIGN KEY (project_id) REFERENCES gantt_projects(id) ON DELETE CASCADE ON UPDATE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

  -- Tabla para los enlaces (dependencias) dentro del mismo proyecto de Gantt
  CREATE TABLE IF NOT EXISTS gantt_links (
    id INT(11) NOT NULL AUTO_INCREMENT,
    source INT(11) NOT NULL,
    target INT(11) NOT NULL,
    type VARCHAR(1) NOT NULL, -- Ej: '0' (FS), '1' (SS), '2' (FF), '3' (SF)
    PRIMARY KEY (id),
    CONSTRAINT fk_gantt_links_source FOREIGN KEY (source) REFERENCES gantt_tasks(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_gantt_links_target FOREIGN KEY (target) REFERENCES gantt_tasks(id) ON DELETE CASCADE ON UPDATE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

  -- Tabla para los enlaces (dependencias) entre diferentes proyectos de Gantt
  CREATE TABLE IF NOT EXISTS gantt_cross_project_links (
    id INT(11) NOT NULL AUTO_INCREMENT,
    source_task_id INT(11) NOT NULL,
    source_project_id INT(11) NOT NULL,
    target_task_id INT(11) NOT NULL,
    target_project_id INT(11) NOT NULL,
    type VARCHAR(10) NOT NULL, -- Ej: 'FS', 'SS', 'FF', 'SF'
    PRIMARY KEY (id),
    CONSTRAINT fk_cross_link_source_task FOREIGN KEY (source_task_id) REFERENCES gantt_tasks(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_cross_link_target_task FOREIGN KEY (target_task_id) REFERENCES gantt_tasks(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_cross_link_source_project FOREIGN KEY (source_project_id) REFERENCES gantt_projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_cross_link_target_project FOREIGN KEY (target_project_id) REFERENCES gantt_projects(id) ON DELETE CASCADE ON UPDATE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

  -- Opcional: Insertar un proyecto de ejemplo
  INSERT IGNORE INTO gantt_projects (id, name) VALUES (1, 'Proyecto de Ejemplo');

  -- Opcional: Insertar algunas tareas de ejemplo para el proyecto 1
  INSERT IGNORE INTO gantt_tasks (id, project_id, text, start_date, duration, progress, parent, sortorder, open, owners) VALUES
  (1, 1, 'Fase 1: Planificación', '2025-09-15', 5, 0.5, 0, 10, 1, NULL),
  (2, 1, 'Definir Requisitos', '2025-09-15', 3, 0.8, 1, 20, 1, NULL),
  (3, 1, 'Diseño de Arquitectura', '2025-09-18', 2, 0.3, 1, 30, 1, NULL),
  (4, 1, 'Fase 2: Desarrollo', '2025-09-22', 10, 0.1, 0, 40, 1, NULL),
  (5, 1, 'Implementar Módulo A', '2025-09-22', 6, 0.0, 4, 50, 1, NULL),
  (6, 1, 'Implementar Módulo B', '2025-09-25', 7, 0.0, 4, 60, 1, NULL);

  -- Opcional: Insertar algunos enlaces de ejemplo para el proyecto 1
  INSERT IGNORE INTO gantt_links (id, source, target, type) VALUES
  (1, 2, 3, '0'), -- Fin-a-Inicio
  (2, 3, 5, '0'); -- Fin-a-Inicio