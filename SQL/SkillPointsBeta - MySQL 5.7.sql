-- ========================================================
-- BASIC POINTS STATS - Esquema SQL Oficial (MySQL 5.7+ / 8.0+)
-- Compatible con Multi-Server, SteamID y Nombre (nick máx. 12 letras)
-- Sincronizado exactamente con BasicPointsStats.sma y Web
-- ========================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Crear y seleccionar la base de datos automáticamente
--
CREATE DATABASE IF NOT EXISTS `basicpointsstats` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `basicpointsstats`;

-- --------------------------------------------------------
-- 0. Tabla de Servidores Registrados: `bps_servers` (Multi-Server)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bps_servers` (
  `server_id` varchar(16) NOT NULL,
  `server_name` varchar(64) NOT NULL DEFAULT '',
  `save_mode` tinyint NOT NULL DEFAULT 1,
  `multi_mode` tinyint NOT NULL DEFAULT 0,
  `last_heartbeat` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`server_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 1. Estructura de Tabla: `basicpointsstats` (Puntos, Kills, Objetivos, Rondas)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `basicpointsstats` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `server_id` varchar(16) NOT NULL DEFAULT 'global',
  `save_key` varchar(40) NOT NULL,
  `authid` varchar(35) NOT NULL DEFAULT '',
  `nick` varchar(12) NOT NULL DEFAULT '',
  `skillpoints` int DEFAULT 0,
  `level` int DEFAULT 0,
  `pug_k` int DEFAULT 0,
  `pug_d` int DEFAULT 0,
  `pug_a` int DEFAULT 0,
  `pug_bp` int DEFAULT 0,
  `pug_bd` int DEFAULT 0,
  `pug_adr` int DEFAULT 0,
  `pug_hsp` int DEFAULT 0,
  `pug_rws` int DEFAULT 0,
  `pug_win` int DEFAULT 0,
  `pug_los` int DEFAULT 0,
  `pug_tied` int DEFAULT 0,
  `pug_r_los` int DEFAULT 0,
  `pug_rp` int DEFAULT 0,
  `last_server` varchar(16) NOT NULL DEFAULT '',
  `last_seen` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_server_save_key` (`server_id`, `save_key`),
  KEY `idx_server_points` (`server_id`, `skillpoints`),
  KEY `idx_save_key` (`save_key`),
  KEY `idx_authid` (`authid`),
  KEY `idx_nick` (`nick`),
  KEY `idx_skillpoints` (`skillpoints`),
  KEY `idx_pug_k` (`pug_k`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Estructura de Tabla: `basicweapomstats1` (Pistolas, Cuchillo, Granada, C4)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `basicweapomstats1` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `server_id` varchar(16) NOT NULL DEFAULT 'global',
  `save_key` varchar(40) NOT NULL,
  `authid` varchar(35) NOT NULL DEFAULT '',
  `nick` varchar(12) NOT NULL DEFAULT '',
  `pug_CSW_GLOCK18` int DEFAULT 0,
  `pug_CSW_USP` int DEFAULT 0,
  `pug_CSW_P228` int DEFAULT 0,
  `pug_CSW_DEAGLE` int DEFAULT 0,
  `pug_CSW_ELITE` int DEFAULT 0,
  `pug_CSW_FIVESEVEN` int DEFAULT 0,
  `pug_CSW_KNIFE` int DEFAULT 0,
  `pug_CSW_HEGRENADE` int DEFAULT 0,
  `pug_CSW_C4` int DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_server_save_key` (`server_id`, `save_key`),
  KEY `idx_save_key` (`save_key`),
  KEY `idx_authid` (`authid`),
  KEY `idx_nick` (`nick`),
  KEY `idx_deagle` (`pug_CSW_DEAGLE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Estructura de Tabla: `basicweapomstats2` (Rifles, Escopetas, Subfusiles, Snipers)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `basicweapomstats2` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `server_id` varchar(16) NOT NULL DEFAULT 'global',
  `save_key` varchar(40) NOT NULL,
  `authid` varchar(35) NOT NULL DEFAULT '',
  `nick` varchar(12) NOT NULL DEFAULT '',
  `pug_CSW_M3` int DEFAULT 0,
  `pug_CSW_XM1014` int DEFAULT 0,
  `pug_CSW_TMP` int DEFAULT 0,
  `pug_CSW_UMP45` int DEFAULT 0,
  `pug_CSW_MAC10` int DEFAULT 0,
  `pug_CSW_MP5NAVY` int DEFAULT 0,
  `pug_CSW_P90` int DEFAULT 0,
  `pug_CSW_GALIL` int DEFAULT 0,
  `pug_CSW_FAMAS` int DEFAULT 0,
  `pug_CSW_AK47` int DEFAULT 0,
  `pug_CSW_M4A1` int DEFAULT 0,
  `pug_CSW_SCOUT` int DEFAULT 0,
  `pug_CSW_SG552` int DEFAULT 0,
  `pug_CSW_AWP` int DEFAULT 0,
  `pug_CSW_G3SG1` int DEFAULT 0,
  `pug_CSW_AUG` int DEFAULT 0,
  `pug_CSW_SG550` int DEFAULT 0,
  `pug_CSW_M249` int DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_server_save_key` (`server_id`, `save_key`),
  KEY `idx_save_key` (`save_key`),
  KEY `idx_authid` (`authid`),
  KEY `idx_nick` (`nick`),
  KEY `idx_ak47` (`pug_CSW_AK47`),
  KEY `idx_m4a1` (`pug_CSW_M4A1`),
  KEY `idx_awp` (`pug_CSW_AWP`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Estructura de Tabla: `basicversusstats` (Multikills y Clutches)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `basicversusstats` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `server_id` varchar(16) NOT NULL DEFAULT 'global',
  `save_key` varchar(40) NOT NULL,
  `authid` varchar(35) NOT NULL DEFAULT '',
  `nick` varchar(12) NOT NULL DEFAULT '',
  `pug_2k` int DEFAULT 0,
  `pug_3k` int DEFAULT 0,
  `pug_4k` int DEFAULT 0,
  `pug_5k` int DEFAULT 0,
  `pug_v1` int DEFAULT 0,
  `pug_v2` int DEFAULT 0,
  `pug_v3` int DEFAULT 0,
  `pug_v4` int DEFAULT 0,
  `pug_v5` int DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_server_save_key` (`server_id`, `save_key`),
  KEY `idx_save_key` (`save_key`),
  KEY `idx_authid` (`authid`),
  KEY `idx_nick` (`nick`),
  KEY `idx_pug_5k` (`pug_5k`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
