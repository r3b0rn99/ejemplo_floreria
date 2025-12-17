-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-12-2025 a las 03:58:34
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `floreria_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `boletas`
--

CREATE TABLE `boletas` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) DEFAULT NULL,
  `numero_boleta` varchar(50) DEFAULT NULL,
  `fecha_emision` timestamp NOT NULL DEFAULT current_timestamp(),
  `pdf_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `boletas`
--

INSERT INTO `boletas` (`id`, `pedido_id`, `numero_boleta`, `fecha_emision`, `pdf_path`) VALUES
(1, 2, 'PED-693884FFA7347', '2025-12-09 20:31:09', 'boleta_PED-693884FFA7347.pdf'),
(2, 1, 'PED-69388265109A8', '2025-12-09 20:31:24', 'boleta_PED-69388265109A8.pdf'),
(3, 3, 'PED-6938874AD6468', '2025-12-09 20:32:14', 'boleta_PED-6938874AD6468.pdf'),
(4, 4, 'PED-6938896A5B5A1', '2025-12-09 20:41:15', 'boleta_PED-6938896A5B5A1.pdf'),
(5, 5, 'PED-6938897E776E7', '2025-12-09 20:41:36', 'boleta_PED-6938897E776E7.pdf'),
(6, 6, 'PED-69388FE2E7155', '2025-12-09 21:09:03', 'boleta_PED-69388FE2E7155.pdf'),
(12, 7, 'PED-6938906E35894', '2025-12-09 21:11:11', 'boleta_PED-6938906E35894.pdf'),
(15, 8, 'PED-693898AB54F84', '2025-12-09 21:46:20', 'boleta_PED-693898AB54F84.pdf'),
(18, 9, 'PED-6938B7B776197', '2025-12-09 23:58:49', 'boleta_PED-6938B7B776197.pdf'),
(19, 10, 'PED-6940F246313A5', '2025-12-16 05:46:48', 'boleta_PED-6940F246313A5.pdf'),
(20, 11, 'PED-6940F635278CF', '2025-12-16 06:03:35', 'boleta_PED-6940F635278CF.pdf'),
(21, 12, 'PED-6941000F7237E', '2025-12-16 13:51:55', 'boleta_PED-6941000F7237E.pdf'),
(22, 14, 'PED-694169628EA9A', '2025-12-16 14:15:00', 'boleta_PED-694169628EA9A.pdf'),
(23, 15, 'PED-69416A2E819E6', '2025-12-16 14:18:23', 'boleta_PED-69416A2E819E6.pdf'),
(24, 17, 'PED-6941A4481B339', '2025-12-16 18:26:18', 'boleta_PED-6941A4481B339.pdf'),
(25, 18, 'PED-6941CD8504AA7', '2025-12-16 21:22:14', 'boleta_PED-6941CD8504AA7.pdf'),
(26, 19, 'PED-6941D821AB1B8', '2025-12-16 22:07:31', 'boleta_PED-6941D821AB1B8.pdf'),
(27, 20, 'PED-6941DB3109BFC', '2025-12-16 22:20:34', 'boleta_PED-6941DB3109BFC.pdf'),
(28, 21, 'PED-694210CA1CC7A', '2025-12-17 02:09:15', 'boleta_PED-694210CA1CC7A.pdf');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `sesion_id` varchar(100) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`id`, `pedido_id`, `producto_id`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 3, 1, 38.90, 38.90),
(2, 2, 7, 1, 79.50, 79.50),
(3, 3, 3, 5, 38.90, 194.50),
(4, 3, 4, 2, 89.90, 179.80),
(5, 4, 7, 1, 79.50, 79.50),
(6, 5, 7, 1, 79.50, 79.50),
(7, 6, 3, 4, 38.90, 155.60),
(8, 6, 6, 4, 65.90, 263.60),
(9, 6, 5, 3, 72.00, 216.00),
(10, 6, 7, 2, 79.50, 159.00),
(11, 6, 4, 4, 89.90, 359.60),
(12, 6, 2, 3, 55.50, 166.50),
(13, 6, 8, 1, 32.00, 32.00),
(14, 7, 7, 2, 79.50, 159.00),
(15, 8, 9, 1, 95.00, 95.00),
(16, 9, 3, 1, 38.90, 38.90),
(17, 10, 7, 1, 79.50, 79.50),
(18, 11, 6, 3, 65.90, 197.70),
(19, 12, 3, 1, 38.90, 38.90),
(20, 13, 3, 1, 38.90, 38.90),
(21, 14, 3, 1, 38.90, 38.90),
(22, 15, 4, 1, 89.90, 89.90),
(23, 16, 13, 1, 1.00, 1.00),
(24, 17, 13, 1, 1.00, 1.00),
(25, 18, 13, 1, 1.00, 1.00),
(26, 19, 10, 1, 49.90, 49.90),
(27, 20, 9, 1, 95.00, 95.00),
(28, 21, 10, 1, 49.90, 49.90);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direcciones`
--

CREATE TABLE `direcciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `referencia` text DEFAULT NULL,
  `principal` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `codigo_pedido` varchar(20) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `direccion_envio` text DEFAULT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_entrega` datetime DEFAULT NULL,
  `estado` enum('pendiente','confirmado','preparando','enviado','entregado','cancelado') DEFAULT 'pendiente',
  `subtotal` decimal(10,2) DEFAULT NULL,
  `envio` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `metodo_pago` varchar(50) DEFAULT NULL,
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `codigo_pedido`, `usuario_id`, `direccion_envio`, `fecha_pedido`, `fecha_entrega`, `estado`, `subtotal`, `envio`, `total`, `metodo_pago`, `notas`) VALUES
(1, 'PED-69388265109A8', 1, NULL, '2025-12-09 20:11:17', NULL, 'pendiente', NULL, NULL, 53.90, 'efectivo', NULL),
(2, 'PED-693884FFA7347', 1, NULL, '2025-12-09 20:22:23', NULL, 'pendiente', NULL, NULL, 94.50, 'efectivo', NULL),
(3, 'PED-6938874AD6468', 1, NULL, '2025-12-09 20:32:10', NULL, 'pendiente', NULL, NULL, 389.30, 'efectivo', NULL),
(4, 'PED-6938896A5B5A1', 1, NULL, '2025-12-09 20:41:14', NULL, 'pendiente', NULL, NULL, 94.50, 'efectivo', NULL),
(5, 'PED-6938897E776E7', 1, NULL, '2025-12-09 20:41:34', NULL, 'pendiente', NULL, NULL, 94.50, 'efectivo', NULL),
(6, 'PED-69388FE2E7155', 1, NULL, '2025-12-09 21:08:50', NULL, 'pendiente', NULL, NULL, 1367.30, 'efectivo', NULL),
(7, 'PED-6938906E35894', 1, NULL, '2025-12-09 21:11:10', NULL, 'pendiente', NULL, NULL, 174.00, 'yape', NULL),
(8, 'PED-693898AB54F84', 1, NULL, '2025-12-09 21:46:19', NULL, 'entregado', NULL, NULL, 110.00, 'yape', NULL),
(9, 'PED-6938B7B776197', 1, NULL, '2025-12-09 23:58:47', NULL, 'preparando', NULL, NULL, 53.90, 'tarjeta', NULL),
(10, 'PED-6940F246313A5', 1, NULL, '2025-12-16 05:46:46', NULL, 'pendiente', NULL, NULL, 94.50, 'yape', NULL),
(11, 'PED-6940F635278CF', 1, NULL, '2025-12-16 06:03:33', NULL, 'pendiente', NULL, NULL, 212.70, 'efectivo', NULL),
(12, 'PED-6941000F7237E', 1, NULL, '2025-12-16 06:45:35', NULL, 'pendiente', NULL, NULL, 53.90, 'yape', NULL),
(13, 'PED-694163F919DB2', 1, NULL, '2025-12-16 13:51:53', NULL, 'confirmado', NULL, NULL, 53.90, 'efectivo', NULL),
(14, 'PED-694169628EA9A', 2, NULL, '2025-12-16 14:14:58', NULL, 'pendiente', NULL, NULL, 53.90, 'efectivo', NULL),
(15, 'PED-69416A2E819E6', 2, NULL, '2025-12-16 14:18:22', NULL, 'pendiente', NULL, NULL, 104.90, 'efectivo', NULL),
(16, 'PED-69419E702CB9C', 1, NULL, '2025-12-16 18:01:20', NULL, 'pendiente', NULL, NULL, 16.00, 'efectivo', NULL),
(17, 'PED-6941A4481B339', 1, NULL, '2025-12-16 18:26:16', NULL, 'pendiente', NULL, NULL, 16.00, 'efectivo', NULL),
(18, 'PED-6941CD8504AA7', 1, NULL, '2025-12-16 21:22:13', NULL, 'pendiente', NULL, NULL, 16.00, 'efectivo', NULL),
(19, 'PED-6941D821AB1B8', 2, NULL, '2025-12-16 22:07:29', NULL, 'confirmado', NULL, NULL, 64.90, 'efectivo', NULL),
(20, 'PED-6941DB3109BFC', 3, NULL, '2025-12-16 22:20:33', NULL, 'confirmado', NULL, NULL, 110.00, 'efectivo', NULL),
(21, 'PED-694210CA1CC7A', 3, NULL, '2025-12-17 02:09:14', NULL, 'pendiente', NULL, NULL, 64.90, 'efectivo', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(200) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `imagen_principal` varchar(255) DEFAULT NULL,
  `imagenes` text DEFAULT NULL,
  `destacado` tinyint(1) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `categoria_id`, `stock`, `imagen_principal`, `imagenes`, `destacado`, `activo`, `fecha_creacion`) VALUES
(1, 'Ramo de Rosas Rojas', 'Hermoso ramo de 12 rosas rojas frescas ideal para ocasiones románticas.', 45.90, NULL, 0, 'rosas_rojas.jpg', NULL, 1, 1, '2025-12-09 16:50:36'),
(2, 'Tulipanes Rosados', 'Elegante bouquet de tulipanes rosados perfectos para un regalo especial.', 55.50, NULL, 0, 'tulipanes_rosados.jpg', NULL, 1, 1, '2025-12-09 16:50:36'),
(3, 'Ramo de Girasoles', 'Arreglo vibrante de girasoles que transmite alegría y energía.', 38.90, NULL, 0, 'girasoles.jpg', NULL, 1, 1, '2025-12-09 16:50:36'),
(4, 'Caja de Rosas Premium', 'Caja de lujo con 18 rosas rojas seleccionadas de alta calidad.', 89.90, NULL, 0, 'caja_rosas.jpg', NULL, 1, 1, '2025-12-09 16:50:36'),
(5, 'Arreglo Floral Mixto', 'Arreglo mixto con flores de temporada para celebraciones especiales.', 72.00, NULL, 0, 'ramo_mixto.jpg', NULL, 1, 1, '2025-12-09 16:50:36'),
(6, 'Orquídea Blanca', 'Orquídea blanca elegante en maceta, símbolo de pureza y armonía.', 65.90, NULL, 0, 'orquidea_blanca.jpg', NULL, 1, 1, '2025-12-09 16:50:36'),
(7, 'Bouquet Azul Premium', 'Bouquet decorativo con rosas azules tratadas y flores blancas.', 79.50, NULL, 0, 'bouquet_azul.jpg', NULL, 1, 1, '2025-12-09 16:50:36'),
(8, 'Ramo de Margaritas', 'Ramo fresco y sencillo de margaritas blancas y amarillas.', 32.00, NULL, 0, 'margaritas.jpg', NULL, 1, 1, '2025-12-09 16:50:36'),
(9, 'Caja Luxury Dorada', 'Caja dorada con rosas rosadas y follaje premium, ideal para aniversarios.', 95.00, NULL, 0, 'caja_luxury.jpg', NULL, 1, 1, '2025-12-09 16:50:36'),
(10, 'Florero Primavera', 'Florero decorativo con flores surtidas de temporada.', 49.90, NULL, 0, 'florero_primaveral.jpg', NULL, 1, 1, '2025-12-09 16:50:36'),
(13, 'chocolate bon o bon', 'bon o bon', 1.00, NULL, 0, 'assets/uploads/productos/prod_20251216_182046_f447db5b5e9d.png', NULL, 1, 0, '2025-12-16 17:20:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `tipo` enum('cliente','admin') DEFAULT 'cliente',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `telefono`, `direccion`, `tipo`, `fecha_registro`, `activo`) VALUES
(1, 'Hans Ajuñiga', 'hansedsonvalverde@gmail.com', '$2y$10$.1/abKXSC29.vlq1XayNIeSiYUgjXHvEIbVqqyCLDfialZGfhM/RG', '925576823', 'mi casa mz f lt 3', 'admin', '2025-12-09 16:17:19', 1),
(2, 'mathias sanchez', 'mathiassanchez@gmail.com', '$2y$10$4qwoke26afyPzRr5RWfFLOs6hGNojnep1dZRLbIucaFMFQNIMCwwS', NULL, NULL, 'cliente', '2025-12-16 14:14:45', 1),
(3, 'santiago jauregui', 'santiagojauregui@gmail.com', '$2y$10$N/ziHqycYVVLVUDAVmxhPO6Irp6m2mclLhUmBvtZ.yqxfIqA2Kr1G', NULL, NULL, 'cliente', '2025-12-16 22:20:11', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `boletas`
--
ALTER TABLE `boletas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pedido_id` (`pedido_id`),
  ADD UNIQUE KEY `numero_boleta` (`numero_boleta`);

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_pedido` (`codigo_pedido`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `boletas`
--
ALTER TABLE `boletas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `boletas`
--
ALTER TABLE `boletas`
  ADD CONSTRAINT `boletas_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`);

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  ADD CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD CONSTRAINT `direcciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
