-- Primero, vamos a añadir la categoría Componentes
INSERT INTO categorias (nombre, descripcion, categoria_padre_id, slug, activo)
VALUES ('Componentes', 'Componentes para PC y ensamblaje', NULL, 'componentes', 1);

-- Obtener el ID de la categoría Componentes
SET @componentes_id = LAST_INSERT_ID();

-- Ahora, actualizamos las categorías existentes para tener a Componentes como categoría padre
UPDATE categorias SET categoria_padre_id = @componentes_id WHERE id IN (1, 2, 3, 7);

-- Para verificar la estructura actualizada
SELECT c1.id, c1.nombre, c1.slug, c2.nombre AS categoria_padre
FROM categorias c1
LEFT JOIN categorias c2 ON c1.categoria_padre_id = c2.id
ORDER BY c2.nombre, c1.nombre;