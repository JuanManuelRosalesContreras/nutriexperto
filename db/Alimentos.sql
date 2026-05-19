USE nutriexperto;

INSERT INTO alimentos (nombre, categoria, calorias_100g, proteinas_g, carbohidratos_g, grasas_g, apto_vegano, apto_sin_gluten, emoji) VALUES

-- PROTEÍNAS
('Pollo entero',           'proteina', 165, 31.00,  0.00,  3.60, 0, 1, '🍗'),
('Carne de res molida',    'proteina', 250, 26.00,  0.00, 15.00, 0, 1, '🥩'),
('Huevo',                  'proteina', 155, 13.00,  1.10, 11.00, 0, 1, '🥚'),
('Atún en lata',           'proteina', 116, 25.50,  0.00,  1.00, 0, 1, '🐟'),
('Frijol negro',           'proteina', 132,  8.90, 23.70,  0.50, 1, 1, '🫘'),
('Frijol pinto',           'proteina', 130,  8.70, 23.50,  0.50, 1, 1, '🫘'),
('Lenteja',                'proteina', 116,  9.00, 20.00,  0.40, 1, 1, '🫘'),
('Sardina en lata',        'proteina', 208, 24.60,  0.00, 11.45, 0, 1, '🐟'),
('Chorizo de res',         'proteina', 290, 14.00,  2.00, 25.00, 0, 1, '🌭'),
('Bistec de res',          'proteina', 271, 26.00,  0.00, 18.00, 0, 1, '🥩'),
('Pechuga de pollo',       'proteina', 110, 23.00,  0.00,  2.00, 0, 1, '🍗'),
('Hígado de res',          'proteina', 135, 20.00,  3.90,  3.60, 0, 1, '🥩'),

-- VERDURAS
('Jitomate',               'verdura',   18,  0.90,  3.90,  0.20, 1, 1, '🍅'),
('Cebolla blanca',         'verdura',   40,  1.10,  9.30,  0.10, 1, 1, '🧅'),
('Chile serrano',          'verdura',   32,  1.80,  6.70,  0.40, 1, 1, '🌶️'),
('Chile poblano',          'verdura',   29,  1.30,  6.00,  0.30, 1, 1, '🫑'),
('Calabaza',               'verdura',   26,  1.00,  6.50,  0.10, 1, 1, '🎃'),
('Chayote',                'verdura',   24,  0.80,  5.50,  0.10, 1, 1, '🥬'),
('Nopal',                  'verdura',   16,  1.32,  3.33,  0.09, 1, 1, '🌵'),
('Zanahoria',              'verdura',   41,  0.90,  9.60,  0.20, 1, 1, '🥕'),
('Papa',                   'verdura',   77,  2.00, 17.00,  0.10, 1, 1, '🥔'),
('Elote',                  'verdura',   86,  3.20, 19.00,  1.20, 1, 1, '🌽'),
('Espinaca',               'verdura',   23,  2.90,  3.60,  0.40, 1, 1, '🥬'),
('Brócoli',                'verdura',   34,  2.80,  6.60,  0.40, 1, 1, '🥦'),
('Ajo',                    'verdura',  149,  6.40, 33.00,  0.50, 1, 1, '🧄'),
('Epazote',                'verdura',   32,  3.30,  7.40,  0.50, 1, 1, '🌿'),
('Cilantro',               'verdura',   23,  2.10,  3.70,  0.50, 1, 1, '🌿'),
('Aguacate',               'grasa_saludable', 160, 2.00, 8.50, 14.70, 1, 1, '🥑'),

-- FRUTAS
('Plátano tabasco',        'fruta',     89,  1.10, 22.80,  0.30, 1, 1, '🍌'),
('Manzana',                'fruta',     52,  0.30, 13.80,  0.20, 1, 1, '🍎'),
('Naranja',                'fruta',     47,  0.90, 11.80,  0.10, 1, 1, '🍊'),
('Papaya',                 'fruta',     43,  0.50, 10.80,  0.30, 1, 1, '🍈'),
('Guayaba',                'fruta',     68,  2.60, 14.30,  0.90, 1, 1, '🍏'),
('Sandía',                 'fruta',     30,  0.60,  7.60,  0.20, 1, 1, '🍉'),
('Melón',                  'fruta',     34,  0.80,  8.20,  0.20, 1, 1, '🍈'),
('Mango',                  'fruta',     60,  0.80, 15.00,  0.40, 1, 1, '🥭'),
('Lima',                   'fruta',     30,  0.70,  9.70,  0.20, 1, 1, '🍋'),
('Tuna',                   'fruta',     41,  0.70,  9.60,  0.50, 1, 1, '🌵'),

-- CEREALES
('Tortilla de maíz',       'cereal',   218,  5.70, 46.00,  2.50, 1, 1, '🫓'),
('Tortilla de harina',     'cereal',   306,  7.80, 49.00,  8.50, 1, 0, '🫓'),
('Arroz blanco',           'cereal',   130,  2.70, 28.20,  0.30, 1, 1, '🍚'),
('Avena',                  'cereal',   389, 16.90, 66.30,  6.90, 1, 0, '🌾'),
('Pan de caja integral',   'cereal',   247,  8.50, 41.30,  3.40, 1, 0, '🍞'),
('Maíz pozolero',          'cereal',   365,  9.40, 74.30,  4.70, 1, 1, '🌽'),
('Harina de maíz (masa)', 'cereal',   361,  8.10, 72.00,  4.30, 1, 1, '🌾'),

-- LÁCTEOS
('Leche entera',           'lacteo',    61,  3.20,  4.80,  3.30, 0, 1, '🥛'),
('Yogur natural',          'lacteo',    59, 10.00,  3.60,  0.40, 0, 1, '🥛'),
('Queso fresco',           'lacteo',   264, 18.00,  3.50, 20.00, 0, 1, '🧀'),
('Crema ácida',            'lacteo',   193,  2.40,  4.00, 19.00, 0, 1, '🥛'),

-- GRASAS SALUDABLES
('Aceite vegetal',         'grasa_saludable', 884, 0.00,  0.00, 100.0, 1, 1, '🫒'),
('Cacahuate',              'grasa_saludable', 567, 25.80, 16.10, 49.20, 1, 1, '🥜'),
('Semilla de calabaza',    'grasa_saludable', 559, 30.20, 10.70, 49.10, 1, 1, '🌻'),
('Chile seco (ancho/mulato)','verdura',  281,  9.90, 49.70,  6.80, 1, 1, '🌶️');