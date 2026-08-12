
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `categoria_grupos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categoria_grupos` (
  `categoria_id` int NOT NULL,
  `grupo_id` int NOT NULL,
  PRIMARY KEY (`categoria_id`,`grupo_id`),
  KEY `grupo_id` (`grupo_id`),
  CONSTRAINT `categoria_grupos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `categoria_grupos_ibfk_2` FOREIGN KEY (`grupo_id`) REFERENCES `grupos_opciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `categoria_grupos` WRITE;
/*!40000 ALTER TABLE `categoria_grupos` DISABLE KEYS */;
/*!40000 ALTER TABLE `categoria_grupos` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icono` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fa-tags',
  `orden` int NOT NULL DEFAULT '1',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (1,'Mesas','mesas','fa-table',1,1,'2026-08-10 23:33:18');
INSERT INTO `categorias` VALUES (2,'Cafés','cafes','fa-coffee',2,1,'2026-08-10 23:33:18');
INSERT INTO `categorias` VALUES (3,'Comidas','comidas','fa-hamburger',3,1,'2026-08-10 23:33:18');
INSERT INTO `categorias` VALUES (4,'Especialidades','especialidades','fa-star',4,1,'2026-08-10 23:33:18');
INSERT INTO `categorias` VALUES (5,'Postres','postres','fa-cake',5,1,'2026-08-10 23:33:18');
INSERT INTO `categorias` VALUES (6,'Bebidas Frías','bebidas','fa-glass-water',6,1,'2026-08-10 23:33:18');
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `detalle_orden`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_orden` (
  `id_detalle` int NOT NULL AUTO_INCREMENT,
  `id_orden` int NOT NULL,
  `id_producto` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `opciones_json` json DEFAULT NULL,
  `estado_item` enum('pendiente','en_preparacion','listo','entregado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `fecha_inicio_preparacion` datetime DEFAULT NULL,
  `fecha_lista` datetime DEFAULT NULL,
  `fecha_entrega` datetime DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_detalle`),
  KEY `idx_detalle_orden` (`id_orden`),
  KEY `idx_detalle_producto` (`id_producto`),
  KEY `idx_detalle_estado_item` (`estado_item`),
  CONSTRAINT `detalle_orden_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `ordenes` (`id_orden`) ON DELETE CASCADE,
  CONSTRAINT `detalle_orden_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `detalle_orden` WRITE;
/*!40000 ALTER TABLE `detalle_orden` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_orden` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `grupos_opciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grupos_opciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `requerido` tinyint(1) NOT NULL DEFAULT '0',
  `seleccion_multiple` tinyint(1) NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `grupos_opciones` WRITE;
/*!40000 ALTER TABLE `grupos_opciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `grupos_opciones` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `layout_configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `layout_configs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `layout_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload_json` longtext COLLATE utf8mb4_unicode_ci,
  `actualizado_por` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `layout_key` (`layout_key`),
  KEY `actualizado_por` (`actualizado_por`),
  CONSTRAINT `layout_configs_ibfk_1` FOREIGN KEY (`actualizado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `layout_configs` WRITE;
/*!40000 ALTER TABLE `layout_configs` DISABLE KEYS */;
/*!40000 ALTER TABLE `layout_configs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `mesas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mesas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `numero` int NOT NULL,
  `estado` enum('disponible','ocupada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disponible',
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero` (`numero`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `mesas` WRITE;
/*!40000 ALTER TABLE `mesas` DISABLE KEYS */;
INSERT INTO `mesas` VALUES (1,1,'disponible');
INSERT INTO `mesas` VALUES (2,2,'disponible');
INSERT INTO `mesas` VALUES (3,3,'disponible');
INSERT INTO `mesas` VALUES (4,4,'disponible');
INSERT INTO `mesas` VALUES (5,5,'disponible');
INSERT INTO `mesas` VALUES (6,6,'disponible');
INSERT INTO `mesas` VALUES (7,7,'disponible');
INSERT INTO `mesas` VALUES (8,8,'disponible');
INSERT INTO `mesas` VALUES (9,9,'disponible');
INSERT INTO `mesas` VALUES (10,10,'disponible');
INSERT INTO `mesas` VALUES (11,11,'disponible');
INSERT INTO `mesas` VALUES (12,12,'disponible');
/*!40000 ALTER TABLE `mesas` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `opciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `grupo_id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio_adicional` decimal(10,2) NOT NULL DEFAULT '0.00',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `grupo_id` (`grupo_id`),
  CONSTRAINT `opciones_ibfk_1` FOREIGN KEY (`grupo_id`) REFERENCES `grupos_opciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `opciones` WRITE;
/*!40000 ALTER TABLE `opciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `opciones` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ordenes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordenes` (
  `id_orden` int NOT NULL AUTO_INCREMENT,
  `numero_orden` int NOT NULL,
  `mesa_id` int NOT NULL,
  `id_usuario` int DEFAULT NULL,
  `notas` text COLLATE utf8mb4_unicode_ci,
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_lista` datetime DEFAULT NULL,
  `fecha_entrega` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_orden`),
  UNIQUE KEY `numero_orden` (`numero_orden`),
  KEY `idx_ordenes_mesa` (`mesa_id`),
  KEY `idx_ordenes_usuario` (`id_usuario`),
  KEY `idx_ordenes_fecha_creacion` (`fecha_creacion`),
  CONSTRAINT `ordenes_ibfk_1` FOREIGN KEY (`mesa_id`) REFERENCES `mesas` (`id`),
  CONSTRAINT `ordenes_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `ordenes` WRITE;
/*!40000 ALTER TABLE `ordenes` DISABLE KEYS */;
/*!40000 ALTER TABLE `ordenes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expira_en` datetime NOT NULL,
  `usado` tinyint(1) DEFAULT '0',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES (1,23,'ba72520a9fa0ae45b7e398400a731b468ef5dee1a5ee34cc706163de14578050','2026-08-11 00:12:04',1,'2026-08-10 23:42:04');
INSERT INTO `password_resets` VALUES (2,23,'43058bb8492a2c1125745d77499bd3755db67de66752c41eb1749d154311014b','2026-08-11 00:15:21',1,'2026-08-10 23:45:21');
INSERT INTO `password_resets` VALUES (3,23,'ee926abfd0ce05849f88b79a1df78246a5a3fe6f6624020d3e333d97f7c85937','2026-08-11 01:07:42',1,'2026-08-11 00:07:42');
INSERT INTO `password_resets` VALUES (4,23,'3e05124aeede6b91dba3db13cf401e484afc96c63237ada7ee9bf36ac6ce692a','2026-08-11 01:12:26',0,'2026-08-11 00:12:26');
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `producto_grupos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `producto_grupos` (
  `producto_id` int NOT NULL,
  `grupo_id` int NOT NULL,
  PRIMARY KEY (`producto_id`,`grupo_id`),
  KEY `grupo_id` (`grupo_id`),
  CONSTRAINT `producto_grupos_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `producto_grupos_ibfk_2` FOREIGN KEY (`grupo_id`) REFERENCES `grupos_opciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `producto_grupos` WRITE;
/*!40000 ALTER TABLE `producto_grupos` DISABLE KEYS */;
/*!40000 ALTER TABLE `producto_grupos` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `categoria_id` int NOT NULL,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_producto_categoria_nombre` (`categoria_id`,`nombre`),
  CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (1,2,'Espresso',1800.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (2,2,'Capuccino',2400.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (3,2,'Latte',2600.00,'https://images.unsplash.com/photo-1550881111-7cfde14b8073?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (4,2,'Americano',2000.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (5,2,'Mocha',2800.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (6,2,'Macchiato',2500.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (7,2,'Café Negro',1700.00,'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (8,2,'Café con Leche',2200.00,'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (9,2,'Café Vainilla',2900.00,'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (10,2,'Flat White',2700.00,'https://images.unsplash.com/photo-1550881111-7cfde14b8073?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (11,3,'Sandwich de Pollo',4200.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (12,3,'Panini Toscana',4800.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (13,3,'Croissant de Jamón y Queso',3500.00,'https://images.unsplash.com/photo-1623366302587-bca835848bb5?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (14,3,'Wrap de Pavo',3900.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (15,3,'Bagel de Salmón',5200.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (16,3,'Quiche Lorena',4100.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (17,3,'Empanada de Carne',2200.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (18,3,'Empanada de Queso',2100.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (19,3,'Tostada de Aguacate',4300.00,'https://images.unsplash.com/photo-1550881111-7cfde14b8073?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (20,3,'Bowl de Yogurt y Frutas',3600.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (21,4,'Frappé Caramelo',3200.00,'https://images.unsplash.com/photo-1572442388796-11668a67e53d?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (22,4,'Mocaccino Especial',3500.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (23,4,'Chocolate Caliente Premium',3000.00,'https://images.unsplash.com/photo-1550881111-7cfde14b8073?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (24,4,'Latte Lavanda',3400.00,'https://images.unsplash.com/photo-1550881111-7cfde14b8073?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (25,4,'Café Bombón',3100.00,'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (26,4,'Frappé Mocha',3300.00,'https://images.unsplash.com/photo-1572442388796-11668a67e53d?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (27,4,'Matcha Latte',3600.00,'https://images.unsplash.com/photo-1550881111-7cfde14b8073?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (28,4,'Golden Milk',3400.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (29,4,'Taro Latte',3700.00,'https://images.unsplash.com/photo-1550881111-7cfde14b8073?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (30,4,'Affogato',3900.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (31,5,'Cheesecake',2800.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (32,5,'Brownie con Helado',3000.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (33,5,'Tiramisú',3200.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (34,5,'Croissant de Almendra',2600.00,'https://images.unsplash.com/photo-1623366302587-bca835848bb5?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (35,5,'Galleta de Chispas',1800.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (36,5,'Queque de Zanahoria',2700.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (37,5,'Muffin de Arándanos',2300.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (38,5,'Pie de Limón',2900.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (39,5,'Tres Leches',3100.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (40,5,'Roll de Canela',2500.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (41,6,'Iced Latte',2900.00,'https://images.unsplash.com/photo-1550881111-7cfde14b8073?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (42,6,'Té Frío Durazno',2500.00,'https://images.unsplash.com/photo-1550881111-7cfde14b8073?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (43,6,'Limonada Natural',2200.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (44,6,'Limonada con Hierbabuena',2400.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (45,6,'Smoothie de Fresa',3300.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (46,6,'Smoothie de Mango',3300.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (47,6,'Chocolate Frío',2800.00,'https://images.unsplash.com/photo-1550881111-7cfde14b8073?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (48,6,'Milkshake Vainilla',3500.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (49,6,'Milkshake Chocolate',3500.00,'https://images.unsplash.com/photo-1550881111-7cfde14b8073?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
INSERT INTO `productos` VALUES (50,6,'Agua Mineral',1500.00,'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&q=80&w=150&h=150',1,'2026-08-10 23:33:18');
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin');
INSERT INTO `roles` VALUES (4,'barista');
INSERT INTO `roles` VALUES (3,'cocina');
INSERT INTO `roles` VALUES (2,'mesero');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol_id` int NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `rol_id` (`rol_id`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Admin','admin','admin@proyecto.com',1,'$2y$10$GKsD.e.cZoSYxMDBbMBHFOgo.Y30c/q2EZ2dIsawRMwe/JVp5NrJy',1,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (2,'Mesero','mesero','mesero@proyecto.com',1,'$2y$10$SoHgtdyHv03k8vJGiG8bAe/y58gdq0Jok0F4ODrULH4ZCRdv87UJe',2,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (3,'Cocina','cocina','cocina@proyecto.com',1,'$2y$10$5mBtWhzM51hYAcjUFNPC6.am2VvqLeu4h78Q3iJJIY.NRyiVTmo1G',3,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (4,'Barista','barista','barista@lacomanda.com',1,'$2y$10$esYITJaGc.AvG18WlMi8ROwwiB/64QRH8ieUl9.wlgYNdnYX1OtLC',4,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (5,'Carlos','Ramírez','carlos.ramirez@lacomanda.com',1,'$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a',2,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (6,'Ana','Gómez','ana.gomez@lacomanda.com',1,'$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a',2,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (7,'Luis','Fernández','luis.fernandez@lacomanda.com',1,'$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a',2,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (8,'María','Vargas','maria.vargas@lacomanda.com',1,'$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a',2,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (9,'José','Soto','jose.soto@lacomanda.com',1,'$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a',2,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (10,'Pedro','Mora','pedro.mora@lacomanda.com',1,'$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a',3,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (11,'Laura','Jiménez','laura.jimenez@lacomanda.com',1,'$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a',3,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (12,'Andrés','Rojas','andres.rojas@lacomanda.com',1,'$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a',3,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (13,'Paola','Castro','paola.castro@lacomanda.com',1,'$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a',3,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (14,'Daniel','Alvarado','daniel.alvarado@lacomanda.com',1,'$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a',4,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (15,'Sofía','Navarro','sofia.navarro@lacomanda.com',1,'$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a',4,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (16,'Kevin','Chaves','kevin.chaves@lacomanda.com',1,'$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a',4,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (17,'Valeria','Cordero','valeria.cordero@lacomanda.com',1,'$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a',4,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (18,'Ricardo','Pérez','ricardo.perez@lacomanda.com',1,'$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a',2,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (19,'Natalia','Salas','natalia.salas@lacomanda.com',1,'$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a',3,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (20,'Esteban','León','esteban.leon@lacomanda.com',1,'$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a',4,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (21,'Mónica','Araya','monica.araya@lacomanda.com',1,'$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a',2,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (22,'Fernando','Solís','fernando.solis@lacomanda.com',1,'$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a',3,'2026-08-10 23:33:18');
INSERT INTO `usuarios` VALUES (23,'Javier','Quiros','jaquirs10@gmail.com',1,'$2y$10$cgWVe/yj/4ALg8p7nBSUjesAj9Q2aghNKd6tqTVMuvzLIppB5r8q2',1,'2026-08-10 23:36:57');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `vw_detalle_preparacion`;
/*!50001 DROP VIEW IF EXISTS `vw_detalle_preparacion`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_detalle_preparacion` AS SELECT 
 1 AS `id_detalle`,
 1 AS `id_orden`,
 1 AS `numero_orden`,
 1 AS `mesa_id`,
 1 AS `id_usuario`,
 1 AS `notas_orden`,
 1 AS `fecha_creacion`,
 1 AS `id_producto`,
 1 AS `producto_nombre`,
 1 AS `categoria_id`,
 1 AS `categoria_nombre`,
 1 AS `categoria_slug`,
 1 AS `cantidad`,
 1 AS `precio_unitario`,
 1 AS `estado_item`,
 1 AS `fecha_inicio_preparacion`,
 1 AS `fecha_lista`,
 1 AS `fecha_entrega`,
 1 AS `area_preparacion`*/;
SET character_set_client = @saved_cs_client;
/*!50001 DROP VIEW IF EXISTS `vw_detalle_preparacion`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb3_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_detalle_preparacion` AS select `d`.`id_detalle` AS `id_detalle`,`d`.`id_orden` AS `id_orden`,`o`.`numero_orden` AS `numero_orden`,`o`.`mesa_id` AS `mesa_id`,`o`.`id_usuario` AS `id_usuario`,`o`.`notas` AS `notas_orden`,`o`.`fecha_creacion` AS `fecha_creacion`,`d`.`id_producto` AS `id_producto`,`p`.`nombre` AS `producto_nombre`,`c`.`id` AS `categoria_id`,`c`.`nombre` AS `categoria_nombre`,`c`.`slug` AS `categoria_slug`,`d`.`cantidad` AS `cantidad`,`d`.`precio_unitario` AS `precio_unitario`,`d`.`estado_item` AS `estado_item`,`d`.`fecha_inicio_preparacion` AS `fecha_inicio_preparacion`,`d`.`fecha_lista` AS `fecha_lista`,`d`.`fecha_entrega` AS `fecha_entrega`,(case when (`c`.`slug` in ('cafes','bebidas')) then 'barista' when (`c`.`slug` = 'mesas') then 'ignorar' else 'cocina' end) AS `area_preparacion` from (((`detalle_orden` `d` join `ordenes` `o` on((`o`.`id_orden` = `d`.`id_orden`))) join `productos` `p` on((`p`.`id` = `d`.`id_producto`))) join `categorias` `c` on((`c`.`id` = `p`.`categoria_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

