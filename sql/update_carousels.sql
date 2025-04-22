-- Actualizar carruseles 8 y 9 para mantener las referencias correctas

-- Actualizar el carrusel 8 de Tarjetas Gráficas (mantener referencia a categoría 1)
-- No es necesario cambiarlo ya que la categoría Tarjetas Gráficas mantiene su ID

-- Actualizar el carrusel 9 de Procesadores (mantener referencia a categoría 2)
-- No es necesario cambiarlo ya que la categoría Procesadores mantiene su ID

-- Crear nuevos carruseles para las demás categorías si es necesario
-- Por ejemplo, si queremos carruseles para todas las subcategorías de Componentes:

-- Para Placas Madre (ID: 7)
INSERT INTO carruseles (nombre, tipo, tipo_contenido, categoria_id, activo)
VALUES ('Placas Madre', 'producto', 'categoria', 7, 1);

-- Para Cases (ID: 3)
INSERT INTO carruseles (nombre, tipo, tipo_contenido, categoria_id, activo)
VALUES ('Cases', 'producto', 'categoria', 3, 1);

-- Para las categorías que no están en Componentes:

-- Para Laptops (ID: 4)
INSERT INTO carruseles (nombre, tipo, tipo_contenido, categoria_id, activo)
VALUES ('Laptops', 'producto', 'categoria', 4, 1);

-- Para PC Gamers (ID: 5)
INSERT INTO carruseles (nombre, tipo, tipo_contenido, categoria_id, activo)
VALUES ('PC Gamers', 'producto', 'categoria', 5, 1);

-- Para Monitores (ID: 8)
INSERT INTO carruseles (nombre, tipo, tipo_contenido, categoria_id, activo)
VALUES ('Monitores', 'producto', 'categoria', 8, 1);

-- Para Impresoras (ID: 6)
INSERT INTO carruseles (nombre, tipo, tipo_contenido, categoria_id, activo)
VALUES ('Impresoras', 'producto', 'categoria', 6, 1);