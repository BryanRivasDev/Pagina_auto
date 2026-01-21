-- 002_add_test_car.sql

-- Ensure Category Exists
INSERT INTO categories (name)
SELECT * FROM (SELECT 'Sedan') AS tmp
WHERE NOT EXISTS (
    SELECT name FROM categories WHERE name = 'Sedan'
) LIMIT 1;

-- Insert Test Car
INSERT INTO cars (make, model, year, price, mileage, description, image_path, show_price, category, status_label, 
        traction, engine_displacement, cylinders, fuel_type, color_exterior, color_interior, passengers, doors, steering,
        feature_electric_windows, feature_ac) 
VALUES ('Auto de Prueba', 'Tal Cual', 2024, 15000.00, 100, 'Este es un auto de prueba agregado por migración automática.', '', 1, 'Sedan', 'Nuevo',
        '4x2', '2.0', 4, 'Gasolina', 'Blanco', 'Negro', 5, 4, 'Hidráulica',
        1, 1);
