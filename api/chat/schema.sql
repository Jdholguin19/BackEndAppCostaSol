-- Esquema inicial para chat entre Usuario y Responsable

-- Tabla de hilos (un hilo por usuario con responsable fijo id=2)
CREATE TABLE IF NOT EXISTS chat_thread (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  responsable_id INT NOT NULL,
  estado TINYINT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_responsable (user_id, responsable_id)
);

-- Tabla de mensajes
CREATE TABLE IF NOT EXISTS chat_message (
  id INT AUTO_INCREMENT PRIMARY KEY,
  thread_id INT NOT NULL,
  sender_type ENUM('user','responsable') NOT NULL,
  sender_id INT NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  read_at DATETIME NULL,
  INDEX idx_thread_created (thread_id, created_at),
  FOREIGN KEY (thread_id) REFERENCES chat_thread(id) ON DELETE CASCADE
);