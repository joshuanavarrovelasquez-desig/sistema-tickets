-- Ejecutar UNA vez en phpMyAdmin (base gestion_tickets).
-- Guarda la fecha límite real del SLA de cada ticket (NULL = aún sin clasificar).
ALTER TABLE tickets ADD COLUMN fecha_limite DATETIME NULL AFTER tiempo_resolucion;
