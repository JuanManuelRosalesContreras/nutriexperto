USE nutriexperto;

INSERT INTO receta_alimentos (receta_id, alimento_id, es_principal) VALUES
-- 1. Enfrijoladas
(1,5,1),(1,1,1),(1,39,1),(1,25,1),(1,15,1),(1,48,0),(1,27,0),
-- 2. Sopa lentejas
(2,7,1),(2,13,1),(2,20,1),(2,14,1),(2,25,1),(2,26,0),
-- 3. Nopalitos con huevo
(3,19,1),(3,3,1),(3,14,1),(3,15,1),(3,39,1),
-- 4. Pozole pollo
(4,1,1),(4,44,1),(4,53,1),(4,25,1),(4,14,1),(4,27,0),
-- 5. Bistec con verduras
(5,10,1),(5,21,1),(5,20,1),(5,13,1),(5,14,1),(5,25,1),
-- 6. Tacos frijol nopal
(6,5,1),(6,19,1),(6,39,1),(6,27,1),(6,15,1),
-- 7. Arroz rojo
(7,41,1),(7,13,1),(7,20,1),(7,14,1),(7,25,1),
-- 8. Caldo de pollo
(8,1,1),(8,20,1),(8,17,1),(8,18,1),(8,22,1),(8,27,0),(8,26,0),
-- 9. Chilaquiles verdes
(9,39,1),(9,3,1),(9,15,1),(9,48,0),(9,49,0),
-- 10. Ensalada nopales
(10,19,1),(10,13,1),(10,14,1),(10,27,1),(10,15,0),(10,28,0),
-- 11. Huevos a la mexicana
(11,3,1),(11,13,1),(11,14,1),(11,15,1),(11,39,1),
-- 12. Bowl arroz pollo aguacate
(12,41,1),(12,11,1),(12,28,1),(12,13,1),(12,27,0),
-- 13. Sopa de fideo
(13,13,1),(13,14,1),(13,25,1),(13,20,1),(13,48,0),
-- 14. Quesadillas
(14,39,1),(14,5,1),(14,48,1),(14,49,0),
-- 15. Tostadas nopal
(15,39,1),(15,5,1),(15,19,1),(15,48,0),(15,49,0),
-- 16. Lentejas con chorizo
(16,7,1),(16,9,1),(16,14,1),(16,25,1),(16,13,1),(16,20,1),
-- 17. Calabacitas con elote
(17,17,1),(17,22,1),(17,14,1),(17,25,1),(17,48,0),
-- 18. Atún a la mexicana
(18,4,1),(18,13,1),(18,14,1),(18,15,1),(18,27,1),
-- 19. Chiles rellenos de frijol
(19,16,1),(19,5,1),(19,48,1),(19,13,1),(19,14,1),
-- 20. Sopa de verduras con garbanzo (sin garbanzo, usamos lenteja)
(20,7,1),(20,13,1),(20,20,1),(20,17,1),(20,14,1),(20,27,0),(20,26,0),
-- 21. Machaca con huevo
(21,2,1),(21,3,1),(21,13,1),(21,14,1),(21,15,1),(21,40,1),
-- 22. Tamales de rajas en cazuela
(22,16,1),(22,22,1),(22,14,1),(22,48,1),(22,49,0),(22,41,0),
-- 23. Ceviche de atún
(23,4,1),(23,13,1),(23,14,1),(23,15,1),(23,27,1),(23,28,1),
-- 24. Frijoles de olla
(24,5,1),(24,14,1),(24,25,1),(24,26,1),(24,39,0),
-- 25. Tinga de pollo
(25,11,1),(25,13,1),(25,14,1),(25,15,1),(25,39,1),
-- 26. Guacamole con tostadas
(26,28,1),(26,13,1),(26,14,1),(26,15,1),(26,27,1),(26,39,1),
-- 27. Sopa azteca
(27,39,1),(27,13,1),(27,25,1),(27,14,1),(27,28,0),(27,48,0),(27,49,0),
-- 28. Picadillo de res
(28,2,1),(28,21,1),(28,20,1),(28,13,1),(28,14,1),(28,25,1),
-- 29. Espagueti a la mexicana
(29,41,1),(29,13,1),(29,14,1),(29,25,1),(29,15,0),(29,48,0),
-- 30. Avena con fruta
(30,42,1),(30,29,1),(30,36,0),(30,32,0),(30,46,1),
-- 31. Enchiladas rojas (premium)
(31,39,1),(31,11,1),(31,53,1),(31,48,0),(31,49,0),(31,14,0),
-- 32. Pozole verde (premium)
(32,1,1),(32,44,1),(32,52,1),(32,15,1),(32,26,1),
-- 33. Mole negro (premium)
(33,1,1),(33,53,1),(33,13,1),(33,14,1),(33,25,1),(33,39,0),
-- 34. Sopa de lima (premium)
(34,1,1),(34,13,1),(34,14,1),(34,25,1),(34,39,1),(34,27,1),
-- 35. Camarones a la diabla (premium)
(35,53,1),(35,13,1),(35,25,1),(35,14,1),(35,41,0),
-- 36. Birria de res (premium)
(36,10,1),(36,53,1),(36,25,1),(36,14,1),(36,39,1),(36,27,1),
-- 37. Chiles en nogada (premium)
(37,16,1),(37,2,1),(37,13,1),(37,14,1),(37,48,1),(37,3,0),
-- 38. Tortas ahogadas (premium)
(38,10,1),(38,5,1),(38,53,1),(38,13,1),(38,14,1),(38,39,0),
-- 39. Gorditas chicharrón (premium)
(39,45,1),(39,15,1),(39,13,1),(39,48,1),(39,49,0),(39,39,0),
-- 40. Plan nutricional (premium)
(40,11,1),(40,5,1),(40,41,1),(40,19,1),(40,3,1),(40,7,0),(40,39,0);

INSERT INTO receta_alimentos (receta_id, alimento_id, es_principal) VALUES
-- 41. Frijoles pintos a la charra: frijol pinto(6), chorizo(9), jitomate(13), cebolla(14), ajo(25), cilantro(27), tortilla maíz(39)
(41,6,1),(41,9,1),(41,13,1),(41,14,1),(41,25,1),(41,27,0),(41,39,0),
-- 42. Sardinas guisadas: sardina(8), jitomate(13), cebolla(14), ajo(25), chile serrano(15), arroz(41)
(42,8,1),(42,13,1),(42,14,1),(42,25,1),(42,15,0),(42,41,0),(42,50,0),
-- 43. Hígado encebollado: hígado(12), cebolla(14), jitomate(13), ajo(25), chile serrano(15), aceite(50)
(43,12,1),(43,14,1),(43,13,1),(43,25,1),(43,15,0),(43,50,1),(43,39,0),
-- 44. Ensalada espinaca aguacate: espinaca(23), aguacate(28), jitomate(13), cebolla(14), chile serrano(15), aceite(50)
(44,23,1),(44,28,1),(44,13,1),(44,14,0),(44,15,0),(44,50,0),
-- 45. Caldo espinacas con huevo: espinaca(23), huevo(3), cebolla(14), ajo(25), chile serrano(15)
(45,23,1),(45,3,1),(45,14,1),(45,25,1),(45,15,0),
-- 46. Brócoli con crema y queso: brócoli(24), crema(49), queso fresco(48), aceite(50)
(46,24,1),(46,49,1),(46,48,1),(46,50,0),
-- 47. Sopa de brócoli: brócoli(24), cebolla(14), ajo(25), crema(49), queso fresco(48), aceite(50)
(47,24,1),(47,14,1),(47,25,1),(47,49,1),(47,48,0),(47,50,0),
-- 48. Agua fresca de manzana: manzana(30), lima(37)
(48,30,1),(48,37,0),
-- 49. Avena con manzana: avena(42), manzana(30), leche(46)
(49,42,1),(49,30,1),(49,46,1),
-- 50. Agua de naranja con chía: naranja(31)
(50,31,1),
-- 51. Agua de guayaba: guayaba(33), lima(37)
(51,33,1),(51,37,0),
-- 52. Agua de sandía: sandía(34), lima(37)
(52,34,1),(52,37,0),
-- 53. Agua de melón: melón(35), lima(37)
(53,35,1),(53,37,0),
-- 54. Agua de lima: lima(37)
(54,37,1),
-- 55. Agua de tuna: tuna(38), lima(37)
(55,38,1),(55,37,0),
-- 56. Sándwich integral con atún: pan integral(43), atún(4), aguacate(28), jitomate(13), cebolla(14), cilantro(27), espinaca(23)
(56,43,1),(56,4,1),(56,28,1),(56,13,1),(56,14,0),(56,27,0),(56,23,0),
-- 57. Yogur con fruta y cacahuate: yogur(47), plátano(29), cacahuate(51), avena(42)
(57,47,1),(57,29,1),(57,51,1),(57,42,0),
-- 58. Pollo salteado con brócoli: pechuga(11), brócoli(24), cebolla(14), ajo(25), aceite(50)
(58,11,1),(58,24,1),(58,14,1),(58,25,1),(58,50,0),
-- 59. Cacahuates enchilados: cacahuate(51), chile serrano(15), lima(37), aceite(50)
(59,51,1),(59,15,1),(59,37,0),(59,50,1),
-- 60. Ensalada espinaca con sardina: espinaca(23), sardina(8), jitomate(13), cebolla(14), aceite(50)
(60,23,1),(60,8,1),(60,13,1),(60,14,0),(60,50,0);