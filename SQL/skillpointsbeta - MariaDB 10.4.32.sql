-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-06-2026 a las 22:17:22
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
-- Base de datos: `skillpointsbeta`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `basicpointsstats`
--

CREATE TABLE `basicpointsstats` (
  `authid` varchar(32) NOT NULL,
  `nick` varchar(32) DEFAULT NULL,
  `skillpoints` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `pug_k` int(11) DEFAULT NULL,
  `pug_d` int(11) DEFAULT NULL,
  `pug_a` int(11) DEFAULT NULL,
  `pug_bp` int(11) DEFAULT NULL,
  `pug_bd` int(11) DEFAULT NULL,
  `pug_adr` int(11) DEFAULT NULL,
  `pug_hsp` int(11) DEFAULT NULL,
  `pug_rws` int(11) DEFAULT NULL,
  `pug_win` int(11) DEFAULT NULL,
  `pug_los` int(11) DEFAULT NULL,
  `pug_tied` int(11) DEFAULT NULL,
  `pug_r_los` int(11) DEFAULT NULL,
  `pug_rp` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `basicpointsstats`
--

INSERT INTO `basicpointsstats` (`authid`, `nick`, `skillpoints`, `level`, `pug_k`, `pug_d`, `pug_a`, `pug_bp`, `pug_bd`, `pug_adr`, `pug_hsp`, `pug_rws`, `pug_win`, `pug_los`, `pug_tied`, `pug_r_los`, `pug_rp`) VALUES
('STEAM_0:0:5996564', 'kwpd', 0, 0, 0, 0, 0, 0, 0, 0, 0, 9, 0, 0, 0, 0, 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `basicversusstats`
--

CREATE TABLE `basicversusstats` (
  `authid` varchar(32) NOT NULL,
  `nick` varchar(32) DEFAULT NULL,
  `pug_2k` int(11) DEFAULT NULL,
  `pug_3k` int(11) DEFAULT NULL,
  `pug_4k` int(11) DEFAULT NULL,
  `pug_5k` int(11) DEFAULT NULL,
  `pug_v1` int(11) DEFAULT NULL,
  `pug_v2` int(11) DEFAULT NULL,
  `pug_v3` int(11) DEFAULT NULL,
  `pug_v4` int(11) DEFAULT NULL,
  `pug_v5` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `basicversusstats`
--

INSERT INTO `basicversusstats` (`authid`, `nick`, `pug_2k`, `pug_3k`, `pug_4k`, `pug_5k`, `pug_v1`, `pug_v2`, `pug_v3`, `pug_v4`, `pug_v5`) VALUES
('STEAM_0:0:5996564', 'kwpd', 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `basicweapomstats1`
--

CREATE TABLE `basicweapomstats1` (
  `authid` varchar(32) NOT NULL,
  `nick` varchar(32) DEFAULT NULL,
  `pug_CSW_GLOCK18` int(11) DEFAULT NULL,
  `pug_CSW_USP` int(11) DEFAULT NULL,
  `pug_CSW_P228` int(11) DEFAULT NULL,
  `pug_CSW_DEAGLE` int(11) DEFAULT NULL,
  `pug_CSW_ELITE` int(11) DEFAULT NULL,
  `pug_CSW_FIVESEVEN` int(11) DEFAULT NULL,
  `pug_CSW_KNIFE` int(11) DEFAULT NULL,
  `pug_CSW_HEGRENADE` int(11) DEFAULT NULL,
  `pug_CSW_C4` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `basicweapomstats1`
--

INSERT INTO `basicweapomstats1` (`authid`, `nick`, `pug_CSW_GLOCK18`, `pug_CSW_USP`, `pug_CSW_P228`, `pug_CSW_DEAGLE`, `pug_CSW_ELITE`, `pug_CSW_FIVESEVEN`, `pug_CSW_KNIFE`, `pug_CSW_HEGRENADE`, `pug_CSW_C4`) VALUES
('STEAM_0:0:5996564', 'kwpd', 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `basicweapomstats2`
--

CREATE TABLE `basicweapomstats2` (
  `authid` varchar(32) NOT NULL,
  `nick` varchar(32) DEFAULT NULL,
  `pug_CSW_M3` int(11) DEFAULT NULL,
  `pug_CSW_XM1014` int(11) DEFAULT NULL,
  `pug_CSW_TMP` int(11) DEFAULT NULL,
  `pug_CSW_UMP45` int(11) DEFAULT NULL,
  `pug_CSW_MAC10` int(11) DEFAULT NULL,
  `pug_CSW_MP5NAVY` int(11) DEFAULT NULL,
  `pug_CSW_P90` int(11) DEFAULT NULL,
  `pug_CSW_GALIL` int(11) DEFAULT NULL,
  `pug_CSW_FAMAS` int(11) DEFAULT NULL,
  `pug_CSW_AK47` int(11) DEFAULT NULL,
  `pug_CSW_M4A1` int(11) DEFAULT NULL,
  `pug_CSW_SCOUT` int(11) DEFAULT NULL,
  `pug_CSW_SG552` int(11) DEFAULT NULL,
  `pug_CSW_AWP` int(11) DEFAULT NULL,
  `pug_CSW_G3SG1` int(11) DEFAULT NULL,
  `pug_CSW_AUG` int(11) DEFAULT NULL,
  `pug_CSW_SG550` int(11) DEFAULT NULL,
  `pug_CSW_M249` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `basicweapomstats2`
--

INSERT INTO `basicweapomstats2` (`authid`, `nick`, `pug_CSW_M3`, `pug_CSW_XM1014`, `pug_CSW_TMP`, `pug_CSW_UMP45`, `pug_CSW_MAC10`, `pug_CSW_MP5NAVY`, `pug_CSW_P90`, `pug_CSW_GALIL`, `pug_CSW_FAMAS`, `pug_CSW_AK47`, `pug_CSW_M4A1`, `pug_CSW_SCOUT`, `pug_CSW_SG552`, `pug_CSW_AWP`, `pug_CSW_G3SG1`, `pug_CSW_AUG`, `pug_CSW_SG550`, `pug_CSW_M249`) VALUES
('STEAM_0:0:5996564', 'kwpd', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `basicpointsstats`
--
ALTER TABLE `basicpointsstats`
  ADD PRIMARY KEY (`authid`);

--
-- Indices de la tabla `basicversusstats`
--
ALTER TABLE `basicversusstats`
  ADD PRIMARY KEY (`authid`);

--
-- Indices de la tabla `basicweapomstats1`
--
ALTER TABLE `basicweapomstats1`
  ADD PRIMARY KEY (`authid`);

--
-- Indices de la tabla `basicweapomstats2`
--
ALTER TABLE `basicweapomstats2`
  ADD PRIMARY KEY (`authid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
