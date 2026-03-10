USE la_comanda;

INSERT INTO productos (categoria_id, nombre, precio, icono, activo)
SELECT c.id, v.nombre, v.precio, v.icono, 1
FROM categorias c
JOIN (
    SELECT 'cafes' AS slug, 'Espresso' AS nombre, 1800 AS precio, 'fa-mug-hot' AS icono
    UNION ALL SELECT 'cafes', 'Capuccino', 2400, 'fa-mug-hot'
    UNION ALL SELECT 'comidas', 'Sandwich de Pollo', 4200, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Panini Toscana', 4800, 'fa-bread-slice'
    UNION ALL SELECT 'especialidades', 'Frappé Caramelo', 3200, 'fa-blender'
    UNION ALL SELECT 'especialidades', 'Mocaccino Especial', 3500, 'fa-star'
    UNION ALL SELECT 'postres', 'Cheesecake', 2800, 'fa-cake-candles'
    UNION ALL SELECT 'postres', 'Brownie con Helado', 3000, 'fa-ice-cream'
    UNION ALL SELECT 'bebidas', 'Iced Latte', 2900, 'fa-glass-water'
    UNION ALL SELECT 'bebidas', 'Té Frío Durazno', 2500, 'fa-glass-water'
) v ON v.slug = c.slug
LEFT JOIN productos p ON p.categoria_id = c.id AND p.nombre = v.nombre
WHERE p.id IS NULL;
