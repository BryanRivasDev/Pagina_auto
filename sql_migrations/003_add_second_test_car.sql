-- 003_add_second_test_car.sql

-- Insert Second Test Car
INSERT INTO cars (make, model, year, price, mileage, description, image_path, show_price, category, status_label, 
        traction, engine_displacement, cylinders, fuel_type, color_exterior, color_interior, passengers, doors, steering,
        feature_electric_windows, feature_ac) 
VALUES ('Auto de Prueba 2', 'Verificación Final', 2025, 25000.00, 50, 'Este es el segundo auto de prueba para confirmar que el deploy funciona correctamente.', '', 1, 'Sedan', 'Oferta',
        '4x4', '3.0', 6, 'Híbrido', 'Rojo', 'Cuero', 4, 2, 'Electro-asistida',
        1, 1);
