-- phpMyAdmin SQL Dump
-- version 4.9.5deb2ubuntu0.1~esm1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 27-06-2026 a las 04:49:59
-- Versión del servidor: 8.0.46-0ubuntu0.20.04.1+esm3
-- Versión de PHP: 7.4.3-4ubuntu2.29+esm3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `SkillPointsBeta`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `basicpointsstats`
--

CREATE TABLE `basicpointsstats` (
  `authid` varchar(32) NOT NULL,
  `nick` varchar(32) DEFAULT NULL,
  `skillpoints` int DEFAULT NULL,
  `level` int DEFAULT NULL,
  `pug_k` int DEFAULT NULL,
  `pug_d` int DEFAULT NULL,
  `pug_a` int DEFAULT NULL,
  `pug_bp` int DEFAULT NULL,
  `pug_bd` int DEFAULT NULL,
  `pug_adr` int DEFAULT NULL,
  `pug_hsp` int DEFAULT NULL,
  `pug_rws` int DEFAULT NULL,
  `pug_win` int DEFAULT NULL,
  `pug_los` int DEFAULT NULL,
  `pug_tied` int DEFAULT NULL,
  `pug_r_los` int DEFAULT NULL,
  `pug_rp` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `basicpointsstats`
--

INSERT INTO `basicpointsstats` (`authid`, `nick`, `skillpoints`, `level`, `pug_k`, `pug_d`, `pug_a`, `pug_bp`, `pug_bd`, `pug_adr`, `pug_hsp`, `pug_rws`, `pug_win`, `pug_los`, `pug_tied`, `pug_r_los`, `pug_rp`) VALUES
('STEAM_0:0:5996564', 'kwpd (>* ; *)>', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `basicversusstats`
--

CREATE TABLE `basicversusstats` (
  `authid` varchar(32) NOT NULL,
  `nick` varchar(32) DEFAULT NULL,
  `pug_2k` int DEFAULT NULL,
  `pug_3k` int DEFAULT NULL,
  `pug_4k` int DEFAULT NULL,
  `pug_5k` int DEFAULT NULL,
  `pug_v1` int DEFAULT NULL,
  `pug_v2` int DEFAULT NULL,
  `pug_v3` int DEFAULT NULL,
  `pug_v4` int DEFAULT NULL,
  `pug_v5` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `basicversusstats`
--

INSERT INTO `basicversusstats` (`authid`, `nick`, `pug_2k`, `pug_3k`, `pug_4k`, `pug_5k`, `pug_v1`, `pug_v2`, `pug_v3`, `pug_v4`, `pug_v5`) VALUES
('STEAM_0:0:5996564', 'kwpd (>* ; *)>', 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `basicweapomstats1`
--

CREATE TABLE `basicweapomstats1` (
  `authid` varchar(32) NOT NULL,
  `nick` varchar(32) DEFAULT NULL,
  `pug_CSW_GLOCK18` int DEFAULT NULL,
  `pug_CSW_USP` int DEFAULT NULL,
  `pug_CSW_P228` int DEFAULT NULL,
  `pug_CSW_DEAGLE` int DEFAULT NULL,
  `pug_CSW_ELITE` int DEFAULT NULL,
  `pug_CSW_FIVESEVEN` int DEFAULT NULL,
  `pug_CSW_KNIFE` int DEFAULT NULL,
  `pug_CSW_HEGRENADE` int DEFAULT NULL,
  `pug_CSW_C4` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `basicweapomstats1`
--

INSERT INTO `basicweapomstats1` (`authid`, `nick`, `pug_CSW_GLOCK18`, `pug_CSW_USP`, `pug_CSW_P228`, `pug_CSW_DEAGLE`, `pug_CSW_ELITE`, `pug_CSW_FIVESEVEN`, `pug_CSW_KNIFE`, `pug_CSW_HEGRENADE`, `pug_CSW_C4`) VALUES
('STEAM_0:0:5996564', 'kwpd (>* ; *)>', 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `basicweapomstats2`
--

CREATE TABLE `basicweapomstats2` (
  `authid` varchar(32) NOT NULL,
  `nick` varchar(32) DEFAULT NULL,
  `pug_CSW_M3` int DEFAULT NULL,
  `pug_CSW_XM1014` int DEFAULT NULL,
  `pug_CSW_TMP` int DEFAULT NULL,
  `pug_CSW_UMP45` int DEFAULT NULL,
  `pug_CSW_MAC10` int DEFAULT NULL,
  `pug_CSW_MP5NAVY` int DEFAULT NULL,
  `pug_CSW_P90` int DEFAULT NULL,
  `pug_CSW_GALIL` int DEFAULT NULL,
  `pug_CSW_FAMAS` int DEFAULT NULL,
  `pug_CSW_AK47` int DEFAULT NULL,
  `pug_CSW_M4A1` int DEFAULT NULL,
  `pug_CSW_SCOUT` int DEFAULT NULL,
  `pug_CSW_SG552` int DEFAULT NULL,
  `pug_CSW_AWP` int DEFAULT NULL,
  `pug_CSW_G3SG1` int DEFAULT NULL,
  `pug_CSW_AUG` int DEFAULT NULL,
  `pug_CSW_SG550` int DEFAULT NULL,
  `pug_CSW_M249` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `basicweapomstats2`
--

INSERT INTO `basicweapomstats2` (`authid`, `nick`, `pug_CSW_M3`, `pug_CSW_XM1014`, `pug_CSW_TMP`, `pug_CSW_UMP45`, `pug_CSW_MAC10`, `pug_CSW_MP5NAVY`, `pug_CSW_P90`, `pug_CSW_GALIL`, `pug_CSW_FAMAS`, `pug_CSW_AK47`, `pug_CSW_M4A1`, `pug_CSW_SCOUT`, `pug_CSW_SG552`, `pug_CSW_AWP`, `pug_CSW_G3SG1`, `pug_CSW_AUG`, `pug_CSW_SG550`, `pug_CSW_M249`) VALUES
('STEAM_0:0:5996564', 'kwpd (>* ; *)>', 0, 0, 0, 0, 0, 0, 0, 0, 0, 8, 7, 0, 0, 0, 0, 0, 0, 0);

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
