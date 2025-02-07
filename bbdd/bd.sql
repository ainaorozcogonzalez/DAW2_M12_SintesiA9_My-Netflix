-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 07-02-2025 a las 16:14:14
-- Versión del servidor: 8.3.0
-- Versión de PHP: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `mynetflix`
--
CREATE DATABASE IF NOT EXISTS `mynetflix` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `mynetflix`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contenidos`
--

DROP TABLE IF EXISTS `contenidos`;
CREATE TABLE IF NOT EXISTS `contenidos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text,
  `fecha_lanzamiento` date DEFAULT NULL,
  `likes` int DEFAULT '0',
  `tipo` enum('pelicula','serie') NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `video_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `contenidos`
--

INSERT INTO `contenidos` (`id`, `titulo`, `descripcion`, `fecha_lanzamiento`, `likes`, `tipo`, `imagen`, `activo`, `fecha_creacion`, `video_url`) VALUES
(6, 'Prison Break', 'Prison Break es una serie de televisión dramática estadounidense creada por Paul Scheuring y estrenada el 29 de agosto de 2005.\r\n\r\nLa trama de la serie gira en torno a Michael Scofield, que en un elaborado plan para rescatar a su hermano Lincoln Burrows, entra a \"Fox River\" una cárcel de máxima seguridad cerca de Chicago, para sacar a su hermano acusado por un falso asesinato del hermano de la vicepresidenta. Fue creada por Paul Scheuring y producida por Adelstein-Parouse Productions en asociación con Original Television y 20th Century Fox Television.', '2005-08-29', 0, 'serie', 'prison_break.jpg', 1, '2025-02-03 14:37:43', 'prison_break.mp4'),
(7, 'Torrente', 'Torrente, el brazo tonto de la ley es una película española dirigida por Santiago Segura, escrita y protagonizada por él mismo, y producida por Lolafilms. Las bromas de mal gusto, el lenguaje soez y las escenas escatológicas parecen ser las claves de su éxito en España.', '1998-03-26', 0, 'pelicula', 'torrente_el_brazo_tonto_de_la_ley.jpg', 1, '2025-02-03 14:47:04', NULL),
(10, 'Tokyo Ghoul', 'Tokyo Ghoul (東京喰種:トーキョーグール Tōkyō Gūru?) es una serie de manga escrita e ilustrada por Sui Ishida, serializada en la revista seinen Young Jump, con entrega semanal desde septiembre de 2011. Compilado en 14 volúmenes (tankōbon) a partir de junio de 2014. Una adaptación al anime del Studio Pierrot comenzó a emitirse en Tokyo MX el 5 de julio de 2014. Funimation ha licenciado la serie de anime para el streaming de vídeo y el hogar con distribución en América, mientras en España es Selecta Visión la encargada de dicha tarea.', '2014-10-16', 0, 'serie', 'tokyo_ghoul.jpg', 1, '2025-02-03 15:46:30', NULL),
(9, 'One Piece', 'One Piece (ワンピース Wan Pīsu?)2​ (estilizado en mayúsculas) es un manga escrito e ilustrado por Eiichirō Oda. Comenzó a publicarse en la revista Japonesa Weekly Shōnen Jump el 22 de julio de 1997 y a la fecha se han publicado 110 volúmenes.3​ La obra narra las aventuras de Monkey D. Luffy y su tripulación, los Piratas de Sombrero de Paja, recorriendo el mar para encontrar el legendario tesoro \"One Piece\" y así convertirse en el Rey de los Piratas.', '1997-07-22', 0, 'serie', 'one_piece.jpg', 1, '2025-02-03 15:00:13', NULL),
(11, 'Narnia', 'Las Crónicas de Narnia (título original en inglés: The Chronicles of Narnia) es una heptalogía de libros juveniles escrita por el escritor y profesor anglo-irlandés C. S. Lewis entre 1950 y 1956, e ilustrado, en su versión original, por Pauline Baynes. Relata las aventuras en Narnia, una tierra de fantasía y magia creada por el autor y poblada por animales parlantes y otras criaturas mitológicas que se ven envueltas en la eterna lucha entre el bien y el mal. Aslan, un legendario león creador del país de Narnia, se constituye como el auténtico protagonista de todos los relatos (si bien los cuatro hermanos Pevensie: Peter, Susan, Lucy y Edmund, aunque ausentes directamente en dos títulos, sirven de hilo conductor).', '2015-02-10', 0, 'pelicula', 'narnia.jpg', 1, '2025-02-03 16:25:03', NULL),
(12, 'Breaking Bad', 'Breaking Bad es una serie de televisión estadounidense que se emitió entre 2008 y 2013, creada y producida por Vince Gilligan. Narra la historia de Walter White (Bryan Cranston), un profesor de química con problemas económicos a quien le diagnostican un cáncer de pulmón inoperable. Para pagar su tratamiento y asegurar el futuro económico de su familia, comienza a cocinar y vender metanfetamina4​ junto con Jesse Pinkman (Aaron Paul), un antiguo alumno suyo. La serie, ambientada y producida en Albuquerque (Nuevo México), se caracteriza por sus escenarios desérticos y por la tendencia en la historia de poner a sus personajes en situaciones que aparentemente no tienen salida, lo que llevó a que su creador la describa como un wéstern contemporáneo.', '2013-07-12', 0, 'serie', 'breaking_bad.jpg', 1, '2025-02-03 16:31:12', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `likes`
--

DROP TABLE IF EXISTS `likes`;
CREATE TABLE IF NOT EXISTS `likes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `contenido_id` int DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`,`contenido_id`),
  KEY `contenido_id` (`contenido_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `rol_id` int DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `rol_id` (`rol_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `contraseña`, `rol_id`, `activo`, `fecha_registro`) VALUES
(1, 'Aina', 'ainaorozcogonzalez@gmail.com', '$2y$10$reBE2q69Vl7mVMB6rTfW/eRBx4IloOsuAvsYUjK0PnLwWjmMTInh2', 2, 1, '2025-02-03 19:37:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `validaciones`
--

DROP TABLE IF EXISTS `validaciones`;
CREATE TABLE IF NOT EXISTS `validaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `fecha_validacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;