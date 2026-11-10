-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.3 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para sgr_vrin
CREATE DATABASE IF NOT EXISTS `sgr_vrin` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `sgr_vrin`;

-- Volcando estructura para tabla sgr_vrin.archivos
CREATE TABLE IF NOT EXISTS `archivos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `expediente_id` bigint unsigned NOT NULL,
  `etapa` tinyint unsigned NOT NULL,
  `tipo` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre_original` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `storage_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tamano_bytes` bigint unsigned NOT NULL,
  `sha256` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ocr_json` json DEFAULT NULL,
  `ocr_confianza` decimal(4,1) DEFAULT NULL,
  `subido_por` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_archivos_exp` (`expediente_id`,`tipo`),
  KEY `fk_arch_user` (`subido_por`),
  CONSTRAINT `fk_arch_exp` FOREIGN KEY (`expediente_id`) REFERENCES `expedientes` (`id`),
  CONSTRAINT `fk_arch_user` FOREIGN KEY (`subido_por`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `ck_arch_etapa` CHECK ((`etapa` between 1 and 4)),
  CONSTRAINT `ck_arch_ocr` CHECK (((`ocr_confianza` is null) or (`ocr_confianza` between 0 and 100))),
  CONSTRAINT `ck_arch_tamano` CHECK ((`tamano_bytes` <= 26214400))
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Escaneos y comprobantes; el PDF/DOCX vive en el almacén de objetos';

-- Volcando datos para la tabla sgr_vrin.archivos: ~65 rows (aproximadamente)
INSERT INTO `archivos` (`id`, `expediente_id`, `etapa`, `tipo`, `descripcion`, `nombre_original`, `storage_path`, `mime`, `tamano_bytes`, `sha256`, `ocr_json`, `ocr_confianza`, `subido_por`, `created_at`, `deleted_at`) VALUES
	(1, 2, 1, 'CARTA_DOCENTE', NULL, 'CARTA_DOCENTE_068.pdf', 'expedientes/2/carta_docente_20261002172724_FCn1fQXy.pdf', 'application/pdf', 1688501, '86e2826adb51e98a379c81ba5b7282907a01825c650506e2fd0ad190ae0f35ec', NULL, NULL, 1, '2026-10-02 22:27:24', NULL),
	(2, 3, 1, 'CARTA_DOCENTE', NULL, 'CARTA_DOCENTE_N°047.pdf', 'expedientes/3/carta_docente_20261002174640_kiiRfsfZ.pdf', 'application/pdf', 1245725, '0b4301b00926489c244da54ff0abbcf31ec2c8cd8a9ce77148a6ba337fd4daa7', NULL, NULL, 1, '2026-10-02 22:46:40', NULL),
	(3, 4, 1, 'CARTA_DOCENTE', NULL, 'CARTA_DOCENTE_N°047.pdf', 'expedientes/4/carta_docente_20261007145759_ANZYof4N.pdf', 'application/pdf', 1245725, '0b4301b00926489c244da54ff0abbcf31ec2c8cd8a9ce77148a6ba337fd4daa7', NULL, NULL, 1, '2026-10-07 19:57:59', NULL),
	(4, 5, 1, 'CARTA_DOCENTE', NULL, 'Carta-29.pdf', 'expedientes/5/carta_docente_20261007173900_k5ZoWkQF.pdf', 'application/pdf', 1693064, '106634a440331da6172a8b0ced05b063585eb812870b82647a3a603860276c4a', NULL, NULL, 1, '2026-10-07 22:39:00', NULL),
	(5, 5, 3, 'CARTA_OPP', NULL, 'carta-opp-guadalupe.pdf', 'expedientes/5/carta_opp_20261007175017_QvwpYcX8.pdf', 'application/pdf', 1356013, '95553428ddc59c3c0bd81c892064db51df46be550fd4969c19225b261394b386', NULL, NULL, 1, '2026-10-07 22:50:17', NULL),
	(6, 4, 3, 'CARTA_OPP', NULL, 'Resolucion_68_2026_v1 (1).pdf', 'expedientes/4/carta_opp_20261007191517_JelSyS6Z.pdf', 'application/pdf', 168100, '42e1e108d7896aa014909bef3d7df11b768c4beb2582b197779885ac74346320', NULL, NULL, 1, '2026-10-08 00:15:17', NULL),
	(7, 4, 3, 'CARTA_OPP', NULL, 'carta-opp-guadalupe.pdf', 'expedientes/4/carta_opp_20261007191534_jY1SKo14.pdf', 'application/pdf', 1356013, '95553428ddc59c3c0bd81c892064db51df46be550fd4969c19225b261394b386', NULL, NULL, 1, '2026-10-08 00:15:34', NULL),
	(8, 5, 4, 'COMPROBANTE_RENDICION', NULL, 'Carta-29.pdf', 'expedientes/5/comprobante_rendicion_20261007205523_RCNHmeq1.pdf', 'application/pdf', 1693064, '106634a440331da6172a8b0ced05b063585eb812870b82647a3a603860276c4a', NULL, NULL, 1, '2026-10-08 01:55:23', '2026-10-08 01:55:40'),
	(9, 5, 4, 'COMPROBANTE_RENDICION', NULL, 'carta-opp-guadalupe.pdf', 'expedientes/5/comprobante_rendicion_20261007205528_8TueRo22.pdf', 'application/pdf', 1356013, '95553428ddc59c3c0bd81c892064db51df46be550fd4969c19225b261394b386', NULL, NULL, 1, '2026-10-08 01:55:28', '2026-10-08 01:55:38'),
	(10, 5, 4, 'COMPROBANTE_RENDICION', NULL, 'carta-vrin-guadalupe.pdf', 'expedientes/5/comprobante_rendicion_20261007205600_guiqgi7j.pdf', 'application/pdf', 1072690, '033a1a4d081c573d0ef03bf1a42a583fd30245bc9bff7a248922ac4fe9cb1899', NULL, NULL, 1, '2026-10-08 01:56:00', NULL),
	(11, 6, 1, 'CARTA_DOCENTE', NULL, 'CARTA_DOCENTE_067.pdf', 'expedientes/6/carta_docente_20261009162651_4vkLJmRV.pdf', 'application/pdf', 1898253, '93bd6d56759a7b05ae3d98d045f891d9adaa1d3eed6218a349a6cd7d9011e3eb', NULL, NULL, 1, '2026-10-09 21:26:51', NULL),
	(12, 7, 1, 'CARTA_DOCENTE', NULL, 'Carta-29.pdf', 'expedientes/7/carta_docente_20261009162917_lQYSh6Be.pdf', 'application/pdf', 1693064, '106634a440331da6172a8b0ced05b063585eb812870b82647a3a603860276c4a', NULL, NULL, 1, '2026-10-09 21:29:17', NULL),
	(13, 6, 3, 'CARTA_OPP', NULL, 'Carta OPP.pdf', 'expedientes/6/carta_opp_20261009194922_VpX3wyey.pdf', 'application/pdf', 104743, 'f1ebc8ece64b94b22c7a8ce823552f57041d6541f9d079a720b7230359cd3a3c', NULL, NULL, 1, '2026-10-10 00:49:22', NULL),
	(14, 6, 4, 'COMPROBANTE_RENDICION', NULL, 'boleta.png', 'expedientes/6/comprobante_rendicion_20261009204901_4ug6ZOhc.png', 'image/png', 159939, '0408f2c2b092ecbd1b69791a267c449849ac55890222f8fc7ee768c6fe7db941', NULL, NULL, 1, '2026-10-10 01:49:01', NULL),
	(15, 6, 4, 'COMPROBANTE_RENDICION', NULL, 'boletasss.png', 'expedientes/6/comprobante_rendicion_20261009204910_HBINYEJU.png', 'image/png', 103908, '513ae40b42fe6a312c72f0a35a5012bab31cd4fedfbe3e8e3711bcdfa7ebba7d', NULL, NULL, 1, '2026-10-10 01:49:10', '2026-10-10 01:49:17'),
	(16, 6, 4, 'COMPROBANTE_RENDICION', NULL, 'factura.png', 'expedientes/6/comprobante_rendicion_20261009204922_I29FoNh1.png', 'image/png', 52283, '468f1a514e7692b90bf6d278f77c39940178f8a7632cf5777d6687880b256945', NULL, NULL, 1, '2026-10-10 01:49:22', NULL),
	(17, 8, 1, 'CARTA_DOCENTE', NULL, 'CARTA_DOCENTE_068.pdf', 'expedientes/8/carta_docente_20261012152612_4q9Xf4aL.pdf', 'application/pdf', 1688501, '86e2826adb51e98a379c81ba5b7282907a01825c650506e2fd0ad190ae0f35ec', NULL, NULL, 1, '2026-10-12 20:26:12', NULL),
	(18, 8, 3, 'CARTA_OPP', NULL, 'CARTA-OPP.pdf', 'expedientes/8/carta_opp_20261012153405_4R7hQ2LG.pdf', 'application/pdf', 1356013, '95553428ddc59c3c0bd81c892064db51df46be550fd4969c19225b261394b386', NULL, NULL, 1, '2026-10-12 20:34:05', NULL),
	(19, 8, 4, 'COMPROBANTE_RENDICION', NULL, 'factura.png', 'expedientes/8/comprobante_rendicion_20261012153908_l78Qw7XH.png', 'image/png', 52283, '468f1a514e7692b90bf6d278f77c39940178f8a7632cf5777d6687880b256945', NULL, NULL, 1, '2026-10-12 20:39:08', NULL),
	(20, 9, 1, 'CARTA_DOCENTE', NULL, 'CARTA_DOCENTE_N°047.pdf', 'expedientes/9/carta_docente_20261012174959_S4WkMVcP.pdf', 'application/pdf', 1245725, '0b4301b00926489c244da54ff0abbcf31ec2c8cd8a9ce77148a6ba337fd4daa7', NULL, NULL, 1, '2026-10-12 22:49:59', NULL),
	(21, 9, 3, 'CARTA_OPP', NULL, 'CARTA_DOCENTE_N°047.pdf', 'expedientes/9/carta_opp_20261012204946_cTqw9frF.pdf', 'application/pdf', 1245725, '0b4301b00926489c244da54ff0abbcf31ec2c8cd8a9ce77148a6ba337fd4daa7', NULL, NULL, 1, '2026-10-13 01:49:46', NULL),
	(22, 9, 4, 'COMPROBANTE_RENDICION', NULL, 'boleta.png', 'expedientes/9/comprobante_rendicion_20261012205756_zCjPeHoZ.png', 'image/png', 159939, '0408f2c2b092ecbd1b69791a267c449849ac55890222f8fc7ee768c6fe7db941', NULL, NULL, 1, '2026-10-13 01:57:56', NULL),
	(23, 10, 1, 'CARTA_DOCENTE', NULL, 'CARTA_DOCENTE_068.pdf', 'expedientes/10/carta_docente_20261012210118_ScwcyoKI.pdf', 'application/pdf', 1688501, '86e2826adb51e98a379c81ba5b7282907a01825c650506e2fd0ad190ae0f35ec', NULL, NULL, 1, '2026-10-13 02:01:18', NULL),
	(24, 13, 1, 'CARTA_DOCENTE', NULL, 'carta_guadalupe.pdf', 'expedientes/13/carta_docente_20261014131852_2pAyJmSf.pdf', 'application/pdf', 79334, 'a13624f868b5004425c2b00b701cd642fd9e1a55ce38fd02d1fab8aa0e68bab3', NULL, NULL, 1, '2026-10-14 18:18:52', NULL),
	(25, 14, 1, 'CARTA_DOCENTE', NULL, 'carta_walquer.pdf', 'expedientes/14/carta_docente_20261014132104_kFtajlZr.pdf', 'application/pdf', 59657, 'b44c6d11b62299f6cc7a4b7f9efd7208717deb303fad37f14dcb40c75640beac', NULL, NULL, 1, '2026-10-14 18:21:04', NULL),
	(26, 14, 3, 'CARTA_OPP', NULL, 'walquer_opp.pdf', 'expedientes/14/carta_opp_20261014132353_QajZiuxf.pdf', 'application/pdf', 109857, 'addbad0c81f4c1e4f57992ce82ba79c140fee77d7c59d84d26d4fe8a3fc90fe8', NULL, NULL, 1, '2026-10-14 18:23:53', NULL),
	(27, 14, 4, 'COMPROBANTE_RENDICION', NULL, 'walquer_opp.pdf', 'expedientes/14/comprobante_rendicion_20261014132625_Dk5qpH5h.pdf', 'application/pdf', 109857, 'addbad0c81f4c1e4f57992ce82ba79c140fee77d7c59d84d26d4fe8a3fc90fe8', NULL, NULL, 1, '2026-10-14 18:26:25', NULL),
	(28, 15, 1, 'CARTA_DOCENTE', NULL, 'carta_walquer.pdf', 'expedientes/15/carta_docente_20261014134931_Y8n8dO94.pdf', 'application/pdf', 59657, 'b44c6d11b62299f6cc7a4b7f9efd7208717deb303fad37f14dcb40c75640beac', NULL, NULL, 1, '2026-10-14 18:49:31', NULL),
	(29, 15, 3, 'CARTA_OPP', NULL, 'walquer_opp.pdf', 'expedientes/15/carta_opp_20261014135804_jDupc2ch.pdf', 'application/pdf', 109857, 'addbad0c81f4c1e4f57992ce82ba79c140fee77d7c59d84d26d4fe8a3fc90fe8', NULL, NULL, 1, '2026-10-14 18:58:04', NULL),
	(30, 15, 4, 'COMPROBANTE_RENDICION', NULL, 'walquer_opp.pdf', 'expedientes/15/comprobante_rendicion_20261014140401_fIumHS7A.pdf', 'application/pdf', 109857, 'addbad0c81f4c1e4f57992ce82ba79c140fee77d7c59d84d26d4fe8a3fc90fe8', NULL, NULL, 1, '2026-10-14 19:04:01', NULL),
	(31, 16, 1, 'CARTA_DOCENTE', NULL, 'carta_guadalupe.pdf', 'expedientes/16/carta_docente_20261016134906_0V2BA2a0.pdf', 'application/pdf', 79334, 'a13624f868b5004425c2b00b701cd642fd9e1a55ce38fd02d1fab8aa0e68bab3', NULL, NULL, 3, '2026-10-16 18:49:06', NULL),
	(32, 17, 1, 'CARTA_DOCENTE', NULL, 'carta_guadalupe.pdf', 'expedientes/17/carta_docente_20261020144244_QWlz9UuL.pdf', 'application/pdf', 79334, 'a13624f868b5004425c2b00b701cd642fd9e1a55ce38fd02d1fab8aa0e68bab3', NULL, NULL, 1, '2026-10-20 19:42:44', NULL),
	(33, 18, 1, 'CARTA_DOCENTE', NULL, 'carta_guadalupe.pdf', 'expedientes/18/carta_docente_20261020144409_nWEMKZux.pdf', 'application/pdf', 79334, 'a13624f868b5004425c2b00b701cd642fd9e1a55ce38fd02d1fab8aa0e68bab3', NULL, NULL, 1, '2026-10-20 19:44:09', NULL),
	(34, 18, 3, 'CARTA_OPP', NULL, 'opp_guadalupe.pdf', 'expedientes/18/carta_opp_20261020144721_c3C30AYq.pdf', 'application/pdf', 102634, '2473555da61f23159c60787688ba9d967853ab636e98d2a26b3c97bc25dfd2da', NULL, NULL, 1, '2026-10-20 19:47:21', NULL),
	(35, 18, 4, 'COMPROBANTE_RENDICION', NULL, 'carta_guadalupe.pdf', 'expedientes/18/comprobante_rendicion_20261020145013_FBlihMDb.pdf', 'application/pdf', 79334, 'a13624f868b5004425c2b00b701cd642fd9e1a55ce38fd02d1fab8aa0e68bab3', NULL, NULL, 1, '2026-10-20 19:50:13', NULL),
	(36, 19, 1, 'CARTA_DOCENTE', NULL, 'carta_guadalupe.pdf', 'expedientes/19/carta_docente_20261026210933_FnmLUqjH.pdf', 'application/pdf', 79334, 'a13624f868b5004425c2b00b701cd642fd9e1a55ce38fd02d1fab8aa0e68bab3', NULL, NULL, 1, '2026-10-27 02:09:33', NULL),
	(37, 19, 3, 'CARTA_OPP', NULL, 'opp_guadalupe.pdf', 'expedientes/19/carta_opp_20261026211248_KvzxrvCQ.pdf', 'application/pdf', 102634, '2473555da61f23159c60787688ba9d967853ab636e98d2a26b3c97bc25dfd2da', NULL, NULL, 1, '2026-10-27 02:12:48', NULL),
	(38, 20, 1, 'CARTA_DOCENTE', NULL, 'CARTA_DOCENTE_GUADALUPE.pdf', 'expedientes/20/carta_docente_20261029172725_vtjizwfj.pdf', 'application/pdf', 1688501, '86e2826adb51e98a379c81ba5b7282907a01825c650506e2fd0ad190ae0f35ec', NULL, NULL, 1, '2026-10-29 22:27:25', NULL),
	(39, 21, 1, 'CARTA_DOCENTE', NULL, 'carta_walquer.pdf', 'expedientes/21/carta_docente_20261029174146_phSWDdmA.pdf', 'application/pdf', 59657, 'b44c6d11b62299f6cc7a4b7f9efd7208717deb303fad37f14dcb40c75640beac', NULL, NULL, 1, '2026-10-29 22:41:46', NULL),
	(40, 22, 1, 'CARTA_DOCENTE', NULL, 'carta_walquer.pdf', 'expedientes/22/carta_docente_20261029174431_J73FUdj7.pdf', 'application/pdf', 59657, 'b44c6d11b62299f6cc7a4b7f9efd7208717deb303fad37f14dcb40c75640beac', NULL, NULL, 1, '2026-10-29 22:44:31', NULL),
	(41, 23, 1, 'CARTA_DOCENTE', NULL, 'carta_guadalupe.pdf', 'expedientes/23/carta_docente_20261029174628_5VTXqKa3.pdf', 'application/pdf', 79334, 'a13624f868b5004425c2b00b701cd642fd9e1a55ce38fd02d1fab8aa0e68bab3', NULL, NULL, 1, '2026-10-29 22:46:28', NULL),
	(42, 24, 1, 'CARTA_DOCENTE', NULL, 'carta_guadalupe.pdf', 'expedientes/24/carta_docente_20261029174747_Bh0FLcAT.pdf', 'application/pdf', 79334, 'a13624f868b5004425c2b00b701cd642fd9e1a55ce38fd02d1fab8aa0e68bab3', NULL, NULL, 1, '2026-10-29 22:47:47', NULL),
	(43, 24, 3, 'CARTA_OPP', NULL, 'opp_guadalupe.pdf', 'expedientes/24/carta_opp_20261029195203_a0dDuaV8.pdf', 'application/pdf', 102634, '2473555da61f23159c60787688ba9d967853ab636e98d2a26b3c97bc25dfd2da', NULL, NULL, 1, '2026-10-30 00:52:03', NULL),
	(44, 22, 3, 'CARTA_OPP', NULL, 'walquer_opp.pdf', 'expedientes/22/carta_opp_20261029203636_ffsC1IRK.pdf', 'application/pdf', 109857, 'addbad0c81f4c1e4f57992ce82ba79c140fee77d7c59d84d26d4fe8a3fc90fe8', NULL, NULL, 1, '2026-10-30 01:36:36', NULL),
	(45, 20, 3, 'CARTA_OPP', NULL, 'CARTA-OPP.pdf', 'expedientes/20/carta_opp_20261029203838_RooYQP4o.pdf', 'application/pdf', 1356013, '95553428ddc59c3c0bd81c892064db51df46be550fd4969c19225b261394b386', NULL, NULL, 1, '2026-10-30 01:38:38', NULL),
	(46, 21, 3, 'CARTA_OPP', NULL, 'CARTA-OPP.pdf', 'expedientes/21/carta_opp_20261030132243_nQIzKARk.pdf', 'application/pdf', 1356013, '95553428ddc59c3c0bd81c892064db51df46be550fd4969c19225b261394b386', NULL, NULL, 1, '2026-10-30 18:22:43', NULL),
	(47, 21, 3, 'CARTA_OPP', NULL, 'CARTA-OPP.pdf', 'expedientes/21/carta_opp_20261030132944_N1xRvPO7.pdf', 'application/pdf', 1356013, '95553428ddc59c3c0bd81c892064db51df46be550fd4969c19225b261394b386', NULL, NULL, 1, '2026-10-30 18:29:44', NULL),
	(48, 21, 3, 'CARTA_OPP', NULL, 'walquer_opp.pdf', 'expedientes/21/carta_opp_20261030133002_n6nwPPAi.pdf', 'application/pdf', 109857, 'addbad0c81f4c1e4f57992ce82ba79c140fee77d7c59d84d26d4fe8a3fc90fe8', NULL, NULL, 1, '2026-10-30 18:30:02', NULL),
	(49, 21, 3, 'CARTA_OPP', NULL, 'carta_walquer.pdf', 'expedientes/21/carta_opp_20261030133020_pkcAmEU1.pdf', 'application/pdf', 59657, 'b44c6d11b62299f6cc7a4b7f9efd7208717deb303fad37f14dcb40c75640beac', NULL, NULL, 1, '2026-10-30 18:30:20', NULL),
	(50, 21, 3, 'CARTA_OPP', NULL, 'walquer_opp.pdf', 'expedientes/21/carta_opp_20261030140630_y0rXdSEu.pdf', 'application/pdf', 109857, 'addbad0c81f4c1e4f57992ce82ba79c140fee77d7c59d84d26d4fe8a3fc90fe8', NULL, NULL, 1, '2026-10-30 19:06:30', NULL),
	(51, 24, 4, 'COMPROBANTE_RENDICION', NULL, 'carta_guadalupe.pdf', 'expedientes/24/comprobante_rendicion_20261030162021_Y4nW50yJ.pdf', 'application/pdf', 79334, 'a13624f868b5004425c2b00b701cd642fd9e1a55ce38fd02d1fab8aa0e68bab3', NULL, NULL, 1, '2026-10-30 21:20:21', '2026-10-30 21:20:33'),
	(52, 24, 4, 'COMPROBANTE_RENDICION', NULL, 'boleta.png', 'expedientes/24/comprobante_rendicion_20261030162042_p6KMg2II.png', 'image/png', 159939, '0408f2c2b092ecbd1b69791a267c449849ac55890222f8fc7ee768c6fe7db941', NULL, NULL, 1, '2026-10-30 21:20:42', NULL),
	(53, 24, 4, 'COMPROBANTE_RENDICION', NULL, 'factura.png', 'expedientes/24/comprobante_rendicion_20261030162056_NXs45QbU.png', 'image/png', 52283, '468f1a514e7692b90bf6d278f77c39940178f8a7632cf5777d6687880b256945', NULL, NULL, 1, '2026-10-30 21:20:56', NULL),
	(54, 22, 4, 'COMPROBANTE_RENDICION', NULL, 'boleta.png', 'expedientes/22/comprobante_rendicion_20261030162204_nnyI7M4g.png', 'image/png', 159939, '0408f2c2b092ecbd1b69791a267c449849ac55890222f8fc7ee768c6fe7db941', NULL, NULL, 1, '2026-10-30 21:22:04', NULL),
	(55, 21, 4, 'COMPROBANTE_RENDICION', NULL, 'boleta.png', 'expedientes/21/comprobante_rendicion_20261030162239_Wl0zRJR5.png', 'image/png', 159939, '0408f2c2b092ecbd1b69791a267c449849ac55890222f8fc7ee768c6fe7db941', NULL, NULL, 1, '2026-10-30 21:22:39', NULL),
	(56, 20, 4, 'COMPROBANTE_RENDICION', NULL, 'boleta.png', 'expedientes/20/comprobante_rendicion_20261030162317_So9OKzd5.png', 'image/png', 159939, '0408f2c2b092ecbd1b69791a267c449849ac55890222f8fc7ee768c6fe7db941', NULL, NULL, 1, '2026-10-30 21:23:17', NULL),
	(57, 20, 4, 'COMPROBANTE_RENDICION', NULL, 'boleta_faneo.png', 'expedientes/20/comprobante_rendicion_20261030162336_T4VOyBMI.png', 'image/png', 103908, '513ae40b42fe6a312c72f0a35a5012bab31cd4fedfbe3e8e3711bcdfa7ebba7d', NULL, NULL, 1, '2026-10-30 21:23:36', NULL),
	(58, 22, 4, 'COMPROBANTE_RENDICION', NULL, 'factura.png', 'expedientes/22/comprobante_rendicion_20261030163631_8idfTsdU.png', 'image/png', 52283, '468f1a514e7692b90bf6d278f77c39940178f8a7632cf5777d6687880b256945', NULL, NULL, 1, '2026-10-30 21:36:31', NULL),
	(59, 25, 1, 'CARTA_DOCENTE', NULL, 'CARTA_DOCENTE_067.pdf', 'expedientes/25/carta_docente_20261030171900_ELWZUO0e.pdf', 'application/pdf', 1898253, '93bd6d56759a7b05ae3d98d045f891d9adaa1d3eed6218a349a6cd7d9011e3eb', NULL, NULL, 1, '2026-10-30 22:19:00', NULL),
	(60, 25, 3, 'CARTA_OPP', NULL, 'Carta OPP.pdf', 'expedientes/25/carta_opp_20261030172002_TUZyf7Pi.pdf', 'application/pdf', 104743, 'f1ebc8ece64b94b22c7a8ce823552f57041d6541f9d079a720b7230359cd3a3c', NULL, NULL, 1, '2026-10-30 22:20:02', NULL),
	(61, 26, 1, 'CARTA_DOCENTE', NULL, 'CARTA_DOCENTE_N°047.pdf', 'expedientes/26/carta_docente_20261030173349_Jl7fEfHu.pdf', 'application/pdf', 1245725, '0b4301b00926489c244da54ff0abbcf31ec2c8cd8a9ce77148a6ba337fd4daa7', NULL, NULL, 1, '2026-10-30 22:33:49', NULL),
	(62, 26, 3, 'CARTA_OPP', NULL, 'walquer_opp.pdf', 'expedientes/26/carta_opp_20261030174100_ZgxOjXdZ.pdf', 'application/pdf', 109857, 'addbad0c81f4c1e4f57992ce82ba79c140fee77d7c59d84d26d4fe8a3fc90fe8', NULL, NULL, 1, '2026-10-30 22:41:00', NULL),
	(63, 27, 1, 'CARTA_DOCENTE', NULL, 'carta_walquer.pdf', 'expedientes/27/carta_docente_20261030174448_EQG3VDS4.pdf', 'application/pdf', 59657, 'b44c6d11b62299f6cc7a4b7f9efd7208717deb303fad37f14dcb40c75640beac', NULL, NULL, 1, '2026-10-30 22:44:48', NULL),
	(64, 27, 3, 'CARTA_OPP', NULL, 'walquer_opp.pdf', 'expedientes/27/carta_opp_20261030174556_eHEfYVd7.pdf', 'application/pdf', 109857, 'addbad0c81f4c1e4f57992ce82ba79c140fee77d7c59d84d26d4fe8a3fc90fe8', NULL, NULL, 1, '2026-10-30 22:45:56', NULL),
	(65, 27, 4, 'COMPROBANTE_RENDICION', NULL, 'carta_walquer.pdf', 'expedientes/27/comprobante_rendicion_20261030205013_U9QdMblC.pdf', 'application/pdf', 59657, 'b44c6d11b62299f6cc7a4b7f9efd7208717deb303fad37f14dcb40c75640beac', NULL, NULL, 1, '2026-10-31 01:50:13', NULL);

-- Volcando estructura para tabla sgr_vrin.auditoria
CREATE TABLE IF NOT EXISTS `auditoria` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `expediente_id` bigint unsigned DEFAULT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `accion` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidad` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entidad_id` bigint unsigned DEFAULT NULL,
  `antes` json DEFAULT NULL,
  `despues` json DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_aud_exp` (`expediente_id`,`created_at`),
  KEY `ix_aud_user` (`usuario_id`,`created_at`),
  KEY `ix_aud_entidad` (`entidad`,`entidad_id`)
) ENGINE=InnoDB AUTO_INCREMENT=241 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Append-only: ningún evento es editable ni eliminable (CA-12)';

-- Volcando datos para la tabla sgr_vrin.auditoria: ~234 rows (aproximadamente)
INSERT INTO `auditoria` (`id`, `expediente_id`, `usuario_id`, `accion`, `entidad`, `entidad_id`, `antes`, `despues`, `ip`, `created_at`) VALUES
	(1, NULL, 1, 'plantilla.seleccionada', 'plantilla_seleccionada', 1, NULL, '{"modulo": "ARTICULOS", "plantilla_id": 1, "tipo_documento_id": 1}', '127.0.0.1', '2026-10-02 14:35:34.007'),
	(2, NULL, 1, 'plantilla.seleccionada', 'plantilla_seleccionada', 2, NULL, '{"modulo": "ARTICULOS", "plantilla_id": 2, "tipo_documento_id": 2}', '127.0.0.1', '2026-10-02 14:35:35.941'),
	(3, 1, 1, 'expediente.creado', 'expedientes', 1, NULL, '{"codigo": "ART-2026-000001", "estado": "EN_REVISION_CALIDAD", "docente_id": 1}', '127.0.0.1', '2026-10-02 14:42:36.084'),
	(4, 1, 4, 'estado.cambiado', 'expedientes', 1, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-02 15:06:17.681'),
	(5, 1, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 1, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE"}', '127.0.0.1', '2026-10-02 15:06:17.682'),
	(6, 2, 1, 'expediente.creado', 'expedientes', 2, NULL, '{"codigo": "ART-2026-000002", "estado": "EN_REVISION_CALIDAD", "docente_id": 3}', '127.0.0.1', '2026-10-02 17:27:23.843'),
	(7, 3, 1, 'expediente.creado', 'expedientes', 3, NULL, '{"codigo": "ART-2026-000003", "estado": "EN_REVISION_CALIDAD", "docente_id": 4}', '127.0.0.1', '2026-10-02 17:46:40.610'),
	(8, 2, 4, 'estado.cambiado', 'expedientes', 2, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-02 17:48:15.426'),
	(9, 2, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 2, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE"}', '127.0.0.1', '2026-10-02 17:48:15.427'),
	(10, 1, 1, 'documento.generado', 'documentos_generados', 1, NULL, '{"tipo": "CARTA_VRIN", "sha256": "d23f2608f368c34ed8409a57373442ff079c7b7ec1a0cf6af1fb2320b6c2c98c", "version": 1, "plantilla_id": 1}', '127.0.0.1', '2026-10-05 21:06:21.492'),
	(11, 1, 1, 'estado.cambiado', 'expedientes', 1, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-05 21:06:21.499'),
	(12, 1, 1, 'estado.cambiado', 'expedientes', 1, '{"estado": "EN_ESPERA_OPP"}', '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '127.0.0.1', '2026-10-05 21:27:04.451'),
	(13, 1, 1, 'documento.generado', 'documentos_generados', 2, NULL, '{"tipo": "RESOLUCION", "sha256": "2cb08b2cb50741d5c2afae52bfaa3dad947c5d2c1a2ddaeea25a32c26bbf82df", "version": 1, "plantilla_id": 2}', '127.0.0.1', '2026-10-05 21:27:15.631'),
	(14, 1, 1, 'estado.cambiado', 'expedientes', 1, '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '{"estado": "RESOLUCION_EMITIDA"}', '127.0.0.1', '2026-10-05 21:27:15.633'),
	(15, 2, 1, 'documento.generado', 'documentos_generados', 3, NULL, '{"tipo": "CARTA_VRIN", "sha256": "71f22a4b006f808d3da1a360056e4bd6217e43a2057a95ea3fb587ade5118bb4", "version": 1, "plantilla_id": 1}', '127.0.0.1', '2026-10-05 21:36:50.057'),
	(16, 2, 1, 'estado.cambiado', 'expedientes', 2, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-05 21:36:50.060'),
	(17, 2, 1, 'estado.cambiado', 'expedientes', 2, '{"estado": "EN_ESPERA_OPP"}', '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '127.0.0.1', '2026-10-05 21:38:15.646'),
	(18, 2, 1, 'documento.generado', 'documentos_generados', 4, NULL, '{"tipo": "RESOLUCION", "sha256": "cf831701aded937f05f25b1b634914537428676d089093783d77cfd76df020d7", "version": 1, "plantilla_id": 2}', '127.0.0.1', '2026-10-05 21:38:25.327'),
	(19, 2, 1, 'estado.cambiado', 'expedientes', 2, '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '{"estado": "RESOLUCION_EMITIDA"}', '127.0.0.1', '2026-10-05 21:38:25.329'),
	(20, 3, 4, 'estado.cambiado', 'expedientes', 3, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-07 14:36:19.703'),
	(21, 3, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 3, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE"}', '127.0.0.1', '2026-10-07 14:36:19.710'),
	(22, 3, 1, 'documento.generado', 'documentos_generados', 5, NULL, '{"tipo": "CARTA_VRIN", "sha256": "d9dfa512179053cea84419fdeed8e42990d783e2ea3d456fd8289c8784466f8c", "version": 1, "plantilla_id": 1}', '127.0.0.1', '2026-10-07 14:47:03.041'),
	(23, 3, 1, 'estado.cambiado', 'expedientes', 3, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-07 14:47:03.044'),
	(24, 3, 1, 'documento.generado', 'documentos_generados', 6, NULL, '{"tipo": "CARTA_VRIN", "sha256": "e5a09a7df36356c0402ec27523d1c431b966819d8a68c89210ed0670f2c31caf", "version": 2, "plantilla_id": 1}', '127.0.0.1', '2026-10-07 14:47:24.926'),
	(25, 4, 1, 'expediente.creado', 'expedientes', 4, NULL, '{"codigo": "ART-2026-000004", "estado": "EN_REVISION_CALIDAD", "docente_id": 4}', '127.0.0.1', '2026-10-07 14:57:58.968'),
	(26, 4, 4, 'estado.cambiado', 'expedientes', 4, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-07 14:58:44.110'),
	(27, 4, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 4, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE"}', '127.0.0.1', '2026-10-07 14:58:44.111'),
	(28, 4, 1, 'documento.generado', 'documentos_generados', 7, NULL, '{"tipo": "CARTA_VRIN", "sha256": "1601f06d86d73dca25c982b1e7ca1a57774a5ef921c69fc0f12bedc3e126bb05", "version": 1, "plantilla_id": 1}', '127.0.0.1', '2026-10-07 14:59:43.328'),
	(29, 4, 1, 'estado.cambiado', 'expedientes', 4, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-07 14:59:43.329'),
	(30, 4, 1, 'documento.generado', 'documentos_generados', 8, NULL, '{"tipo": "CARTA_VRIN", "sha256": "3180dd7f139655f2be8d4e2d19d08a84fc6101bd822ab5988635c2b5369514ec", "version": 2, "plantilla_id": 1}', '127.0.0.1', '2026-10-07 15:26:01.873'),
	(31, 4, 1, 'documento.generado', 'documentos_generados', 9, NULL, '{"tipo": "CARTA_VRIN", "sha256": "d00faa757d4c8fec0a4e361830a37c17117aae409e14ef573de9453e1bd23dd1", "version": 3, "plantilla_id": 1}', '127.0.0.1', '2026-10-07 15:42:10.264'),
	(32, 4, 1, 'documento.generado', 'documentos_generados', 10, NULL, '{"tipo": "CARTA_VRIN", "sha256": "8efa46e3c10545f12086bd0236df6e8318e656712564a338d2d17fc124e9d61a", "version": 4, "plantilla_id": 1}', '127.0.0.1', '2026-10-07 15:52:37.592'),
	(33, 4, 1, 'documento.generado', 'documentos_generados', 11, NULL, '{"tipo": "CARTA_VRIN", "sha256": "e727c038a5dddd1f9b39f57377f7c68fc666d6dc963c31d69493483fb39bc19a", "version": 5, "plantilla_id": 1}', '127.0.0.1', '2026-10-07 16:33:50.147'),
	(34, 5, 1, 'expediente.creado', 'expedientes', 5, NULL, '{"codigo": "ART-2026-000005", "estado": "EN_REVISION_CALIDAD", "docente_id": 3}', '127.0.0.1', '2026-10-07 17:39:00.064'),
	(35, 5, 4, 'estado.cambiado', 'expedientes', 5, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-07 17:39:15.229'),
	(36, 5, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 5, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE"}', '127.0.0.1', '2026-10-07 17:39:15.230'),
	(37, 5, 1, 'documento.generado', 'documentos_generados', 12, NULL, '{"tipo": "CARTA_VRIN", "sha256": "ba218d5e16ca761d3d5c38588b6597d9096999629ae2ac85ee2d365d4e0d398e", "version": 1, "plantilla_id": 1}', '127.0.0.1', '2026-10-07 17:40:54.697'),
	(38, 5, 1, 'estado.cambiado', 'expedientes', 5, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-07 17:40:54.699'),
	(39, 5, 1, 'documento.generado', 'documentos_generados', 13, NULL, '{"tipo": "CARTA_VRIN", "sha256": "a49395c46d4d378da1c42ce3533760376155558293864c56ab2c6aef71f64cb2", "version": 2, "plantilla_id": 1}', '127.0.0.1', '2026-10-07 17:47:26.946'),
	(40, 5, 1, 'estado.cambiado', 'expedientes', 5, '{"estado": "EN_ESPERA_OPP"}', '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '127.0.0.1', '2026-10-07 17:52:18.880'),
	(41, 5, 1, 'documento.generado', 'documentos_generados', 14, NULL, '{"tipo": "RESOLUCION", "sha256": "faf03ba22ee763161eabbc0d502c48651211f4bb4840c6791d7424b5de930b27", "version": 1, "plantilla_id": 2}', '127.0.0.1', '2026-10-07 17:52:25.983'),
	(42, 5, 1, 'estado.cambiado', 'expedientes', 5, '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '{"estado": "RESOLUCION_EMITIDA"}', '127.0.0.1', '2026-10-07 17:52:25.985'),
	(43, 5, 1, 'documento.generado', 'documentos_generados', 15, NULL, '{"tipo": "RESOLUCION", "sha256": "9f1f9d697899f44f42793410c6abd0ecd8adcb2ab945ab49f67291d599702d5a", "version": 2, "plantilla_id": 2}', '127.0.0.1', '2026-10-07 19:37:41.539'),
	(44, 5, 1, 'documento.generado', 'documentos_generados', 16, NULL, '{"tipo": "RESOLUCION", "sha256": "46e8180a008fdb7594edbfbdbad78d877bbe54268253490a7a0efeba8b387c7c", "version": 3, "plantilla_id": 2}', '127.0.0.1', '2026-10-07 20:06:50.995'),
	(45, 5, 1, 'estado.cambiado', 'expedientes', 5, '{"estado": "RESOLUCION_EMITIDA"}', '{"estado": "POR_RENDIR"}', '127.0.0.1', '2026-10-07 20:40:51.889'),
	(46, 5, 1, 'estado.cambiado', 'expedientes', 5, '{"estado": "POR_RENDIR"}', '{"estado": "RENDIDO"}', '127.0.0.1', '2026-10-07 20:58:58.876'),
	(47, 6, 1, 'expediente.creado', 'expedientes', 6, NULL, '{"codigo": "ART-2026-000006", "estado": "EN_REVISION_CALIDAD", "docente_id": 5}', '127.0.0.1', '2026-10-09 16:26:50.865'),
	(48, 7, 1, 'expediente.creado', 'expedientes', 7, NULL, '{"codigo": "ART-2026-000007", "estado": "EN_REVISION_CALIDAD", "docente_id": 3}', '127.0.0.1', '2026-10-09 16:29:17.296'),
	(49, 7, 4, 'estado.cambiado', 'expedientes', 7, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "NO_CUMPLE"}', '127.0.0.1', '2026-10-09 16:48:15.008'),
	(50, 7, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 7, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": false, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE"}', '127.0.0.1', '2026-10-09 16:48:15.009'),
	(51, 7, 4, 'estado.cambiado', 'expedientes', 7, '{"estado": "NO_CUMPLE"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-09 17:57:27.637'),
	(52, 7, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 7, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": false, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": null, "validado_at": "2026-10-09 16:48:15", "validado_por": 4}', '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-09 17:57:27", "validado_por": 4, "motivo_correccion": "Me confundí en la evaluación"}', '127.0.0.1', '2026-10-09 17:57:27.638'),
	(53, 7, 4, 'estado.cambiado', 'expedientes', 7, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "NO_CUMPLE"}', '127.0.0.1', '2026-10-09 18:47:08.921'),
	(54, 7, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 7, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-09 17:57:27", "validado_por": 4}', '{"checklist": {"carta_aceptacion": false, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": null, "validado_at": "2026-10-09 18:47:08", "validado_por": 4, "motivo_correccion": "Falto revisar la carta de aceptacion"}', '127.0.0.1', '2026-10-09 18:47:08.922'),
	(55, 7, 4, 'estado.cambiado', 'expedientes', 7, '{"estado": "NO_CUMPLE"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-09 18:47:45.171'),
	(56, 7, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 7, '{"checklist": {"carta_aceptacion": false, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": null, "validado_at": "2026-10-09 18:47:08", "validado_por": 4}', '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-09 18:47:45", "validado_por": 4, "motivo_correccion": "Si presento carta de aceptacion"}', '127.0.0.1', '2026-10-09 18:47:45.191'),
	(57, 6, 4, 'estado.cambiado', 'expedientes', 6, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "NO_CUMPLE"}', '127.0.0.1', '2026-10-09 19:29:34.175'),
	(58, 6, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 6, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": false, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": null, "validado_at": "2026-10-09 19:29:34", "validado_por": 4}', '127.0.0.1', '2026-10-09 19:29:34.177'),
	(59, 6, 4, 'estado.cambiado', 'expedientes', 6, '{"estado": "NO_CUMPLE"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-09 19:30:20.395'),
	(60, 6, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 6, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": false, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": null, "validado_at": "2026-10-09 19:29:34", "validado_por": 4}', '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-09 19:30:20", "validado_por": 4, "motivo_correccion": "No se verifico bien la afilicacion a la Unoversidad"}', '127.0.0.1', '2026-10-09 19:30:20.397'),
	(61, 6, 1, 'documento.generado', 'documentos_generados', 17, NULL, '{"tipo": "CARTA_VRIN", "sha256": "df1e178d62fcd51bdf35b49d03a85724aff85b759f188ffda9135bca02f29e9e", "version": 1, "plantilla_id": 1}', '127.0.0.1', '2026-10-09 19:40:58.573'),
	(62, 6, 1, 'estado.cambiado', 'expedientes', 6, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-09 19:40:58.574'),
	(63, 6, 1, 'estado.cambiado', 'expedientes', 6, '{"estado": "EN_ESPERA_OPP"}', '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '127.0.0.1', '2026-10-09 19:52:03.338'),
	(64, 6, 1, 'documento.generado', 'documentos_generados', 18, NULL, '{"tipo": "RESOLUCION", "sha256": "4ee0e554e4f9ae34ed1094ee908a312034011abdd8f86dc26630696078db9d4d", "version": 1, "plantilla_id": 2}', '127.0.0.1', '2026-10-09 19:52:31.785'),
	(65, 6, 1, 'estado.cambiado', 'expedientes', 6, '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '{"estado": "RESOLUCION_EMITIDA"}', '127.0.0.1', '2026-10-09 19:52:31.787'),
	(66, 6, 1, 'estado.cambiado', 'expedientes', 6, '{"estado": "RESOLUCION_EMITIDA"}', '{"estado": "POR_RENDIR"}', '127.0.0.1', '2026-10-09 20:03:20.004'),
	(67, 6, 1, 'estado.cambiado', 'expedientes', 6, '{"estado": "POR_RENDIR"}', '{"estado": "RENDIDO"}', '127.0.0.1', '2026-10-09 20:50:05.690'),
	(68, 8, 1, 'expediente.creado', 'expedientes', 8, NULL, '{"codigo": "ART-2026-000008", "estado": "EN_REVISION_CALIDAD", "docente_id": 3}', '127.0.0.1', '2026-10-12 15:26:11.833'),
	(69, 8, 4, 'estado.cambiado', 'expedientes', 8, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "NO_CUMPLE"}', '127.0.0.1', '2026-10-12 15:27:33.917'),
	(70, 8, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 8, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": false, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": null, "validado_at": "2026-10-12 15:27:33", "validado_por": 4}', '127.0.0.1', '2026-10-12 15:27:33.918'),
	(71, 8, 4, 'estado.cambiado', 'expedientes', 8, '{"estado": "NO_CUMPLE"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-12 15:28:39.369'),
	(72, 8, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 8, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": false, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": null, "validado_at": "2026-10-12 15:27:33", "validado_por": 4}', '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-12 15:28:39", "validado_por": 4, "motivo_correccion": "Cumple con los requisitos"}', '127.0.0.1', '2026-10-12 15:28:39.370'),
	(73, 8, 1, 'documento.generado', 'documentos_generados', 19, NULL, '{"tipo": "CARTA_VRIN", "sha256": "f608223f6828b457f01404f7cb98a0e2cff07d8713c8b91d33d8230afff0d19d", "version": 1, "plantilla_id": 1}', '127.0.0.1', '2026-10-12 15:29:53.619'),
	(74, 8, 1, 'estado.cambiado', 'expedientes', 8, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-12 15:29:53.621'),
	(75, 8, 1, 'documento.generado', 'documentos_generados', 20, NULL, '{"tipo": "CARTA_VRIN", "sha256": "19e9a2cd844b3ab7a570a18a3620b49fb6ddb393d4d31171b408050755f36d0b", "version": 2, "plantilla_id": 1}', '127.0.0.1', '2026-10-12 15:31:54.851'),
	(76, 8, 1, 'estado.cambiado', 'expedientes', 8, '{"estado": "EN_ESPERA_OPP"}', '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '127.0.0.1', '2026-10-12 15:35:37.771'),
	(77, 8, 1, 'documento.generado', 'documentos_generados', 21, NULL, '{"tipo": "RESOLUCION", "sha256": "a2335aff95d9450f748259d4b9d86781b1333e7425ef7785ada06a724fc0c0b3", "version": 1, "plantilla_id": 2}', '127.0.0.1', '2026-10-12 15:36:48.077'),
	(78, 8, 1, 'estado.cambiado', 'expedientes', 8, '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '{"estado": "RESOLUCION_EMITIDA"}', '127.0.0.1', '2026-10-12 15:36:48.078'),
	(79, 8, 1, 'estado.cambiado', 'expedientes', 8, '{"estado": "RESOLUCION_EMITIDA"}', '{"estado": "POR_RENDIR"}', '127.0.0.1', '2026-10-12 15:38:06.381'),
	(80, 8, 1, 'estado.cambiado', 'expedientes', 8, '{"estado": "POR_RENDIR"}', '{"estado": "RENDIDO"}', '127.0.0.1', '2026-10-12 15:39:21.328'),
	(81, 9, 1, 'expediente.creado', 'expedientes', 9, NULL, '{"codigo": "ART-2026-000009", "estado": "EN_REVISION_CALIDAD", "docente_id": 4}', '127.0.0.1', '2026-10-12 17:49:59.623'),
	(82, 9, 4, 'estado.cambiado', 'expedientes', 9, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-12 19:04:49.888'),
	(83, 9, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 9, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-12 19:04:49", "validado_por": 4}', '127.0.0.1', '2026-10-12 19:04:49.889'),
	(84, 9, 1, 'documento.generado', 'documentos_generados', 22, NULL, '{"tipo": "CARTA_VRIN", "sha256": "b0a928c15d8d418bfc34c855d90d5e01dcd6387812b72693d6ad70ea01236247", "version": 1, "plantilla_id": 1}', '127.0.0.1', '2026-10-12 19:06:58.299'),
	(85, 9, 1, 'estado.cambiado', 'expedientes', 9, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-12 19:06:58.301'),
	(86, 9, 1, 'documento.generado', 'documentos_generados', 23, NULL, '{"tipo": "CARTA_VRIN", "sha256": "7a4e6b07ef5fa6bc1fdae9cfad3ea573bd5e6bccaf98d20060cf888337bc7c5d", "version": 2, "plantilla_id": 1}', '127.0.0.1', '2026-10-12 19:19:00.857'),
	(87, 9, 1, 'documento.generado', 'documentos_generados', 24, NULL, '{"tipo": "CARTA_VRIN", "sha256": "b31ebffb53e6ad9d23cff265d0f121671ec6f80476b7349ff4586a357da3f1f9", "version": 3, "plantilla_id": 1}', '127.0.0.1', '2026-10-12 20:47:19.093'),
	(88, 9, 1, 'estado.cambiado', 'expedientes', 9, '{"estado": "EN_ESPERA_OPP"}', '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '127.0.0.1', '2026-10-12 20:52:40.057'),
	(89, 9, 1, 'documento.generado', 'documentos_generados', 25, NULL, '{"tipo": "RESOLUCION", "sha256": "e3f007bcf09fdb08bee9bf4849c3816ee3510561bc6a3e0b893b22cab5a0c8ca", "version": 1, "plantilla_id": 2}', '127.0.0.1', '2026-10-12 20:52:46.668'),
	(90, 9, 1, 'estado.cambiado', 'expedientes', 9, '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '{"estado": "RESOLUCION_EMITIDA"}', '127.0.0.1', '2026-10-12 20:52:46.670'),
	(91, 9, 1, 'estado.cambiado', 'expedientes', 9, '{"estado": "RESOLUCION_EMITIDA"}', '{"estado": "POR_RENDIR"}', '127.0.0.1', '2026-10-12 20:57:44.430'),
	(92, 10, 1, 'expediente.creado', 'expedientes', 10, NULL, '{"codigo": "ART-2026-000010", "estado": "EN_REVISION_CALIDAD", "docente_id": 3}', '127.0.0.1', '2026-10-12 21:01:17.851'),
	(93, 11, 1, 'expediente.creado', 'expedientes', 11, NULL, '{"codigo": "ART-2026-000011", "estado": "EN_REVISION_CALIDAD", "docente_id": 4}', '127.0.0.1', '2026-10-13 15:27:46.210'),
	(94, 11, 4, 'estado.cambiado', 'expedientes', 11, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-13 15:30:51.200'),
	(95, 11, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 11, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-13 15:30:51", "validado_por": 4}', '127.0.0.1', '2026-10-13 15:30:51.201'),
	(96, 11, 1, 'documento.generado', 'documentos_generados', 26, NULL, '{"tipo": "CARTA_VRIN", "sha256": "78ed28738583a28f62bb4a3d2648312af16a6aa526446ea8df9117661efe1795", "version": 1, "plantilla_id": 1}', '127.0.0.1', '2026-10-13 15:44:47.939'),
	(97, 11, 1, 'estado.cambiado', 'expedientes', 11, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-13 15:44:47.941'),
	(98, 11, 1, 'documento.generado', 'documentos_generados', 27, NULL, '{"tipo": "CARTA_VRIN", "sha256": "33ff47d86547162a712b652d205f7de97e84fd899719916ef047d3959e0ad4ea", "version": 2, "plantilla_id": 1}', '127.0.0.1', '2026-10-13 15:46:36.283'),
	(99, 12, 1, 'expediente.creado', 'expedientes', 12, NULL, '{"codigo": "ART-2026-000012", "estado": "EN_REVISION_CALIDAD", "docente_id": 2}', '127.0.0.1', '2026-10-13 16:26:52.050'),
	(100, 12, 4, 'estado.cambiado', 'expedientes', 12, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-13 16:28:09.004'),
	(101, 12, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 12, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-13 16:28:09", "validado_por": 4}', '127.0.0.1', '2026-10-13 16:28:09.005'),
	(102, 12, 1, 'documento.generado', 'documentos_generados', 28, NULL, '{"tipo": "CARTA_VRIN", "sha256": "d6dcbd2cf16ddb95dc40338044560287b114f240c12ddef655d47d248130cd74", "version": 1, "plantilla_id": 1}', '127.0.0.1', '2026-10-13 16:28:29.866'),
	(103, 12, 1, 'estado.cambiado', 'expedientes', 12, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-13 16:28:29.867'),
	(104, 13, 1, 'expediente.creado', 'expedientes', 13, NULL, '{"codigo": "ART-2026-000013", "estado": "EN_REVISION_CALIDAD", "docente_id": 3}', '127.0.0.1', '2026-10-14 13:18:51.341'),
	(105, 14, 1, 'expediente.creado', 'expedientes', 14, NULL, '{"codigo": "ART-2026-000014", "estado": "EN_REVISION_CALIDAD", "docente_id": 6}', '127.0.0.1', '2026-10-14 13:21:02.862'),
	(106, 14, 4, 'estado.cambiado', 'expedientes', 14, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-14 13:21:30.584'),
	(107, 14, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 14, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-14 13:21:30", "validado_por": 4}', '127.0.0.1', '2026-10-14 13:21:30.585'),
	(108, 14, 1, 'documento.generado', 'documentos_generados', 29, NULL, '{"tipo": "CARTA_VRIN", "sha256": "8752bcd9adb3e298e0ff4862db5e781c44dfd30c346d3e22d6255e2cf9669c2b", "version": 1, "plantilla_id": 1}', '127.0.0.1', '2026-10-14 13:21:52.914'),
	(109, 14, 1, 'estado.cambiado', 'expedientes', 14, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-14 13:21:52.915'),
	(110, 13, 4, 'estado.cambiado', 'expedientes', 13, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-14 13:22:53.176'),
	(111, 13, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 13, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-14 13:22:53", "validado_por": 4}', '127.0.0.1', '2026-10-14 13:22:53.177'),
	(112, 14, 1, 'estado.cambiado', 'expedientes', 14, '{"estado": "EN_ESPERA_OPP"}', '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '127.0.0.1', '2026-10-14 13:24:56.414'),
	(113, 14, 1, 'documento.generado', 'documentos_generados', 30, NULL, '{"tipo": "RESOLUCION", "sha256": "ddaf53b67abcffae516c7997a26c288bcfb5b790c2c993691d130f6ad4e43821", "version": 1, "plantilla_id": 2}', '127.0.0.1', '2026-10-14 13:25:04.070'),
	(114, 14, 1, 'estado.cambiado', 'expedientes', 14, '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '{"estado": "RESOLUCION_EMITIDA"}', '127.0.0.1', '2026-10-14 13:25:04.072'),
	(115, 14, 1, 'estado.cambiado', 'expedientes', 14, '{"estado": "RESOLUCION_EMITIDA"}', '{"estado": "POR_RENDIR"}', '127.0.0.1', '2026-10-14 13:25:33.187'),
	(116, 13, 1, 'documento.generado', 'documentos_generados', 31, NULL, '{"tipo": "CARTA_VRIN", "sha256": "28d37c03d2758effb746464702e5cf67b36e285f4dea21b9ef2691bc1a889e00", "version": 1, "plantilla_id": 1}', '127.0.0.1', '2026-10-14 13:27:22.028'),
	(117, 13, 1, 'estado.cambiado', 'expedientes', 13, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-14 13:27:22.029'),
	(118, 9, 1, 'estado.cambiado', 'expedientes', 9, '{"estado": "POR_RENDIR"}', '{"estado": "RENDIDO"}', '127.0.0.1', '2026-10-14 13:45:11.972'),
	(119, 15, 1, 'expediente.creado', 'expedientes', 15, NULL, '{"codigo": "ART-2026-000015", "estado": "EN_REVISION_CALIDAD", "docente_id": 6}', '127.0.0.1', '2026-10-14 13:49:30.657'),
	(120, 15, 4, 'estado.cambiado', 'expedientes', 15, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-14 13:52:54.933'),
	(121, 15, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 15, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-14 13:52:54", "validado_por": 4}', '127.0.0.1', '2026-10-14 13:52:54.933'),
	(122, 15, 1, 'documento.generado', 'documentos_generados', 32, NULL, '{"tipo": "CARTA_VRIN", "sha256": "c6077ecba2aee3f286736421de4f9ab8d7d825e13598228e858b63343bd00f1a", "version": 1, "plantilla_id": 1}', '127.0.0.1', '2026-10-14 13:54:49.296'),
	(123, 15, 1, 'estado.cambiado', 'expedientes', 15, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-14 13:54:49.298'),
	(124, 15, 1, 'estado.cambiado', 'expedientes', 15, '{"estado": "EN_ESPERA_OPP"}', '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '127.0.0.1', '2026-10-14 13:59:13.859'),
	(125, 15, 1, 'documento.generado', 'documentos_generados', 33, NULL, '{"tipo": "RESOLUCION", "sha256": "7c6549ff637d09e80f50aefb6e5c1195149d621ed57ea4da82ba376ded6caddb", "version": 1, "plantilla_id": 2}', '127.0.0.1', '2026-10-14 13:59:24.358'),
	(126, 15, 1, 'estado.cambiado', 'expedientes', 15, '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '{"estado": "RESOLUCION_EMITIDA"}', '127.0.0.1', '2026-10-14 13:59:24.359'),
	(127, 15, 1, 'estado.cambiado', 'expedientes', 15, '{"estado": "RESOLUCION_EMITIDA"}', '{"estado": "POR_RENDIR"}', '127.0.0.1', '2026-10-14 14:03:40.298'),
	(128, 15, 1, 'estado.cambiado', 'expedientes', 15, '{"estado": "POR_RENDIR"}', '{"estado": "RENDIDO"}', '127.0.0.1', '2026-10-14 14:05:08.553'),
	(129, 16, 3, 'expediente.creado', 'expedientes', 16, NULL, '{"codigo": "ART-2026-000016", "estado": "EN_REVISION_CALIDAD", "docente_id": 3}', '127.0.0.1', '2026-10-16 13:49:06.333'),
	(130, 16, 4, 'estado.cambiado', 'expedientes', 16, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-16 13:49:21.403'),
	(131, 16, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 16, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-16 13:49:21", "validado_por": 4}', '127.0.0.1', '2026-10-16 13:49:21.404'),
	(132, 17, 1, 'expediente.creado', 'expedientes', 17, NULL, '{"codigo": "ART-2026-000017", "estado": "EN_REVISION_CALIDAD", "docente_id": 3}', '127.0.0.1', '2026-10-20 14:42:43.896'),
	(133, 18, 1, 'expediente.creado', 'expedientes', 18, NULL, '{"codigo": "ART-2026-000018", "estado": "EN_REVISION_CALIDAD", "docente_id": 3}', '127.0.0.1', '2026-10-20 14:44:09.604'),
	(134, 18, 4, 'estado.cambiado', 'expedientes', 18, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-20 14:45:05.611'),
	(135, 18, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 18, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-20 14:45:05", "validado_por": 4}', '127.0.0.1', '2026-10-20 14:45:05.612'),
	(136, 18, 1, 'documento.generado', 'documentos_generados', 34, NULL, '{"tipo": "CARTA_VRIN", "sha256": "273d77d9284f9e8b2d4ec0568e9c1632aa3e20ec5eace0ba8dcf352aff1bfdcf", "version": 1, "plantilla_id": 1}', '127.0.0.1', '2026-10-20 14:46:11.181'),
	(137, 18, 1, 'estado.cambiado', 'expedientes', 18, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-20 14:46:11.183'),
	(138, 18, 1, 'estado.cambiado', 'expedientes', 18, '{"estado": "EN_ESPERA_OPP"}', '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '127.0.0.1', '2026-10-20 14:48:29.449'),
	(139, 18, 1, 'documento.generado', 'documentos_generados', 35, NULL, '{"tipo": "RESOLUCION", "sha256": "083c6d69615e5ce560dd679df5f702a7c3f136c670758e1ec6f092b4a7e69ed0", "version": 1, "plantilla_id": 2}', '127.0.0.1', '2026-10-20 14:48:37.926'),
	(140, 18, 1, 'estado.cambiado', 'expedientes', 18, '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '{"estado": "RESOLUCION_EMITIDA"}', '127.0.0.1', '2026-10-20 14:48:37.928'),
	(141, 18, 1, 'estado.cambiado', 'expedientes', 18, '{"estado": "RESOLUCION_EMITIDA"}', '{"estado": "POR_RENDIR"}', '127.0.0.1', '2026-10-20 14:49:56.876'),
	(142, 18, 1, 'estado.cambiado', 'expedientes', 18, '{"estado": "POR_RENDIR"}', '{"estado": "RENDIDO"}', '127.0.0.1', '2026-10-20 14:50:48.123'),
	(143, 19, 1, 'expediente.creado', 'expedientes', 19, NULL, '{"codigo": "ART-2026-000019", "estado": "EN_REVISION_CALIDAD", "docente_id": 3}', '127.0.0.1', '2026-10-26 21:09:32.957'),
	(144, 19, 4, 'estado.cambiado', 'expedientes', 19, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-26 21:11:14.715'),
	(145, 19, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 19, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-26 21:11:14", "validado_por": 4}', '127.0.0.1', '2026-10-26 21:11:14.716'),
	(146, 19, 1, 'documento.generado', 'documentos_generados', 36, NULL, '{"tipo": "CARTA_VRIN", "sha256": "ac1dea39b5bc6cccdc116241fdec060b9a2b4fee8dd775a83a45b0dbb34bcb5b", "version": 1, "plantilla_id": 1}', '127.0.0.1', '2026-10-26 21:12:16.586'),
	(147, 19, 1, 'estado.cambiado', 'expedientes', 19, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-26 21:12:16.588'),
	(148, 19, 1, 'estado.cambiado', 'expedientes', 19, '{"estado": "EN_ESPERA_OPP"}', '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '127.0.0.1', '2026-10-26 21:13:30.775'),
	(149, NULL, 1, 'plantilla.seleccionada', 'plantilla_seleccionada', 4, NULL, '{"modulo": "ARTICULOS", "plantilla_id": 4, "tipo_documento_id": 2}', '127.0.0.1', '2026-10-26 21:38:08.931'),
	(150, 19, 1, 'documento.generado', 'documentos_generados', 37, NULL, '{"tipo": "RESOLUCION", "sha256": "684a0c235764750b10ff58e717115a27ff395f936552dfb897ab11f13f9fe534", "version": 1, "plantilla_id": 4}', '127.0.0.1', '2026-10-26 21:47:36.357'),
	(151, 19, 1, 'estado.cambiado', 'expedientes', 19, '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '{"estado": "RESOLUCION_EMITIDA"}', '127.0.0.1', '2026-10-26 21:47:36.361'),
	(152, 20, 1, 'expediente.creado', 'expedientes', 20, NULL, '{"codigo": "ART-2026-000020", "estado": "EN_REVISION_CALIDAD", "docente_id": 3}', '127.0.0.1', '2026-10-29 17:27:24.862'),
	(153, 20, 4, 'estado.cambiado', 'expedientes', 20, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "NO_CUMPLE"}', '127.0.0.1', '2026-10-29 17:35:07.537'),
	(154, 20, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 20, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": false, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": null, "validado_at": "2026-10-29 17:35:07", "validado_por": 4}', '127.0.0.1', '2026-10-29 17:35:07.538'),
	(155, 20, 4, 'estado.cambiado', 'expedientes', 20, '{"estado": "NO_CUMPLE"}', '{"estado": "NO_CUMPLE"}', '127.0.0.1', '2026-10-29 17:36:03.258'),
	(156, 20, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 20, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": false, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": null, "validado_at": "2026-10-29 17:35:07", "validado_por": 4}', '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": false, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": null, "validado_at": "2026-10-29 17:36:03", "validado_por": 4, "motivo_correccion": "No está de acuerdo a la afiliación de la Universidad"}', '127.0.0.1', '2026-10-29 17:36:03.259'),
	(157, 21, 1, 'expediente.creado', 'expedientes', 21, NULL, '{"codigo": "ART-2026-000021", "estado": "EN_REVISION_CALIDAD", "docente_id": 9}', '127.0.0.1', '2026-10-29 17:41:46.085'),
	(158, 22, 1, 'expediente.creado', 'expedientes', 22, NULL, '{"codigo": "ART-2026-000022", "estado": "EN_REVISION_CALIDAD", "docente_id": 9}', '127.0.0.1', '2026-10-29 17:44:31.340'),
	(159, 23, 1, 'expediente.creado', 'expedientes', 23, NULL, '{"codigo": "ART-2026-000023", "estado": "EN_REVISION_CALIDAD", "docente_id": 3}', '127.0.0.1', '2026-10-29 17:46:28.414'),
	(160, 24, 1, 'expediente.creado', 'expedientes', 24, NULL, '{"codigo": "ART-2026-000024", "estado": "EN_REVISION_CALIDAD", "docente_id": 3}', '127.0.0.1', '2026-10-29 17:47:46.984'),
	(161, 21, 4, 'estado.cambiado', 'expedientes', 21, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "NO_CUMPLE"}', '127.0.0.1', '2026-10-29 17:54:18.608'),
	(162, 21, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 21, NULL, '{"checklist": {"carta_aceptacion": false, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": "No cuenta con carta de Afiliación", "validado_at": "2026-10-29 17:54:18", "validado_por": 4}', '127.0.0.1', '2026-10-29 17:54:18.609'),
	(163, 22, 4, 'estado.cambiado', 'expedientes', 22, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-29 17:54:41.439'),
	(164, 22, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 22, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": "Falta firma del docente", "validado_at": "2026-10-29 17:54:41", "validado_por": 4}', '127.0.0.1', '2026-10-29 17:54:41.440'),
	(165, 20, 4, 'estado.cambiado', 'expedientes', 20, '{"estado": "NO_CUMPLE"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-29 18:02:28.962'),
	(166, 20, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 20, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": false, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": null, "validado_at": "2026-10-29 17:36:03", "validado_por": 4}', '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-29 18:02:28", "validado_por": 4, "motivo_correccion": "Si cumple con la Afiliación hubo un error"}', '127.0.0.1', '2026-10-29 18:02:28.963'),
	(167, 22, 4, 'estado.cambiado', 'expedientes', 22, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "NO_CUMPLE"}', '127.0.0.1', '2026-10-29 18:03:18.036'),
	(168, 22, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 22, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": "Falta firma del docente", "validado_at": "2026-10-29 17:54:41", "validado_por": 4}', '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": false, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": "No cumple con afiliación", "validado_at": "2026-10-29 18:03:18", "validado_por": 4, "motivo_correccion": "No cumple con afiliación"}', '127.0.0.1', '2026-10-29 18:03:18.037'),
	(169, 23, 4, 'estado.cambiado', 'expedientes', 23, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "NO_CUMPLE"}', '127.0.0.1', '2026-10-29 18:04:15.895'),
	(170, 23, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 23, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": "asdadasd", "validado_at": "2026-10-29 18:04:15", "validado_por": 4}', '127.0.0.1', '2026-10-29 18:04:15.897'),
	(171, 24, 4, 'estado.cambiado', 'expedientes', 24, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "NO_CUMPLE"}', '127.0.0.1', '2026-10-29 18:04:27.695'),
	(172, 24, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 24, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": false, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": "sdadadad", "validado_at": "2026-10-29 18:04:27", "validado_por": 4}', '127.0.0.1', '2026-10-29 18:04:27.695'),
	(173, 24, 4, 'estado.cambiado', 'expedientes', 24, '{"estado": "NO_CUMPLE"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-29 19:13:28.691'),
	(174, 24, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 24, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": false, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": "sdadadad", "validado_at": "2026-10-29 18:04:27", "validado_por": 4}', '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-29 19:13:28", "validado_por": 4, "motivo_correccion": "hubo una equivocación en la revisión de requisitos"}', '127.0.0.1', '2026-10-29 19:13:28.693'),
	(175, 23, 4, 'estado.cambiado', 'expedientes', 23, '{"estado": "NO_CUMPLE"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-29 19:18:32.143'),
	(176, 23, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 23, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": "asdadasd", "validado_at": "2026-10-29 18:04:15", "validado_por": 4}', '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-29 19:18:32", "validado_por": 4, "motivo_correccion": "Hubo un fallo en la revison"}', '127.0.0.1', '2026-10-29 19:18:32.163'),
	(177, 23, 4, 'estado.cambiado', 'expedientes', 23, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "NO_CUMPLE"}', '127.0.0.1', '2026-10-29 19:22:29.596'),
	(178, 23, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 23, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-29 19:18:32", "validado_por": 4}', '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": "adasdasd", "validado_at": "2026-10-29 19:22:29", "validado_por": 4, "motivo_correccion": "Hubo un fallo en la revison"}', '127.0.0.1', '2026-10-29 19:22:29.597'),
	(179, 24, 4, 'estado.cambiado', 'expedientes', 24, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "NO_CUMPLE"}', '127.0.0.1', '2026-10-29 19:22:39.882'),
	(180, 24, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 24, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-29 19:13:28", "validado_por": 4}', '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": "asdasdasd", "validado_at": "2026-10-29 19:22:39", "validado_por": 4, "motivo_correccion": "hubo una equivocación en la revisión de requisitos"}', '127.0.0.1', '2026-10-29 19:22:39.883'),
	(181, 24, 4, 'estado.cambiado', 'expedientes', 24, '{"estado": "NO_CUMPLE"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-29 19:23:23.230'),
	(182, 24, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 24, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": "asdasdasd", "validado_at": "2026-10-29 19:22:39", "validado_por": 4}', '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-29 19:23:23", "validado_por": 4, "motivo_correccion": "Si cuenta con todos los requisitos"}', '127.0.0.1', '2026-10-29 19:23:23.231'),
	(183, 23, 4, 'estado.cambiado', 'expedientes', 23, '{"estado": "NO_CUMPLE"}', '{"estado": "NO_CUMPLE"}', '127.0.0.1', '2026-10-29 19:23:59.161'),
	(184, 23, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 23, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": "adasdasd", "validado_at": "2026-10-29 19:22:29", "validado_por": 4}', '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": "Falta Documentos", "validado_at": "2026-10-29 19:23:59", "validado_por": 4, "motivo_correccion": "No Hay DOI"}', '127.0.0.1', '2026-10-29 19:23:59.162'),
	(185, 22, 4, 'estado.cambiado', 'expedientes', 22, '{"estado": "NO_CUMPLE"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-29 19:24:41.188'),
	(186, 22, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 22, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": false, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": "No cumple con afiliación", "validado_at": "2026-10-29 18:03:18", "validado_por": 4}', '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-29 19:24:41", "validado_por": 4, "motivo_correccion": "Si cumple con la afiliacion"}', '127.0.0.1', '2026-10-29 19:24:41.189'),
	(187, 21, 4, 'estado.cambiado', 'expedientes', 21, '{"estado": "NO_CUMPLE"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-29 19:25:08.614'),
	(188, 21, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 21, '{"checklist": {"carta_aceptacion": false, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "NO_CUMPLE", "observacion": "No cuenta con carta de Afiliación", "validado_at": "2026-10-29 17:54:18", "validado_por": 4}', '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-29 19:25:08", "validado_por": 4, "motivo_correccion": "Si cumple con los requisitos"}', '127.0.0.1', '2026-10-29 19:25:08.615'),
	(189, 20, 1, 'documento.generado', 'documentos_generados', 38, NULL, '{"tipo": "CARTA_VRIN", "sha256": "b9a3d2da714cfe38dd5267408ea3e8bae1ec56ff8d99b52132338c00909149d5", "version": 1, "plantilla_id": 1}', '127.0.0.1', '2026-10-29 19:42:32.518'),
	(190, 20, 1, 'estado.cambiado', 'expedientes', 20, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-29 19:42:32.520'),
	(191, 24, 1, 'documento.generado', 'documentos_generados', 39, NULL, '{"tipo": "CARTA_VRIN", "sha256": "b58139b1a3a7b75731a212734138d0ec018e5365eaa1454341599dead7ec4589", "version": 1, "plantilla_id": 1}', '127.0.0.1', '2026-10-29 19:47:05.605'),
	(192, 24, 1, 'estado.cambiado', 'expedientes', 24, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-29 19:47:05.607'),
	(193, 24, 1, 'documento.generado', 'documentos_generados', 40, NULL, '{"tipo": "CARTA_VRIN", "sha256": "608de1e4d70cf9194a45bda0ad8d8f8f70b6575094860aafac5170a39217bb53", "version": 2, "plantilla_id": 1}', '127.0.0.1', '2026-10-29 19:48:21.763'),
	(194, 22, 1, 'documento.generado', 'documentos_generados', 41, NULL, '{"tipo": "CARTA_VRIN", "sha256": "f0488124abe58748de570a50a4908a5e44a6dfabf5d7426914c9951797bcf8fd", "version": 1, "plantilla_id": 1}', '127.0.0.1', '2026-10-29 19:50:13.391'),
	(195, 22, 1, 'estado.cambiado', 'expedientes', 22, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-29 19:50:13.393'),
	(196, 24, 1, 'estado.cambiado', 'expedientes', 24, '{"estado": "EN_ESPERA_OPP"}', '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '127.0.0.1', '2026-10-29 19:58:47.088'),
	(197, 24, 1, 'documento.generado', 'documentos_generados', 42, NULL, '{"tipo": "RESOLUCION", "sha256": "c401e91f7dbe5d7a87ef24f4967ec61a818c16f1204d38fe7c2040d0e14e14e4", "version": 1, "plantilla_id": 4}', '127.0.0.1', '2026-10-29 19:59:43.017'),
	(198, 24, 1, 'estado.cambiado', 'expedientes', 24, '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '{"estado": "RESOLUCION_EMITIDA"}', '127.0.0.1', '2026-10-29 19:59:43.019'),
	(199, 22, 1, 'estado.cambiado', 'expedientes', 22, '{"estado": "EN_ESPERA_OPP"}', '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '127.0.0.1', '2026-10-29 20:37:10.674'),
	(200, 22, 1, 'documento.generado', 'documentos_generados', 43, NULL, '{"tipo": "RESOLUCION", "sha256": "c3b3f5ac1c96bc3064e48e5d250db919328d0497162ee6c6a42432a297c99bbb", "version": 1, "plantilla_id": 4}', '127.0.0.1', '2026-10-29 20:37:45.469'),
	(201, 22, 1, 'estado.cambiado', 'expedientes', 22, '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '{"estado": "RESOLUCION_EMITIDA"}', '127.0.0.1', '2026-10-29 20:37:45.470'),
	(202, 20, 1, 'estado.cambiado', 'expedientes', 20, '{"estado": "EN_ESPERA_OPP"}', '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '127.0.0.1', '2026-10-29 20:40:12.065'),
	(203, 20, 1, 'documento.generado', 'documentos_generados', 44, NULL, '{"tipo": "RESOLUCION", "sha256": "5fd7a4fad6ce1b6c3122959bb537af91cf235283f2ad53edc18aaee0924ea675", "version": 1, "plantilla_id": 4}', '127.0.0.1', '2026-10-29 20:40:36.352'),
	(204, 20, 1, 'estado.cambiado', 'expedientes', 20, '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '{"estado": "RESOLUCION_EMITIDA"}', '127.0.0.1', '2026-10-29 20:40:36.354'),
	(205, 24, 1, 'estado.cambiado', 'expedientes', 24, '{"estado": "RESOLUCION_EMITIDA"}', '{"estado": "POR_RENDIR"}', '127.0.0.1', '2026-10-29 20:40:53.145'),
	(206, 19, 1, 'estado.cambiado', 'expedientes', 19, '{"estado": "RESOLUCION_EMITIDA"}', '{"estado": "POR_RENDIR"}', '127.0.0.1', '2026-10-29 20:41:07.213'),
	(207, 22, 1, 'estado.cambiado', 'expedientes', 22, '{"estado": "RESOLUCION_EMITIDA"}', '{"estado": "POR_RENDIR"}', '127.0.0.1', '2026-10-29 20:41:17.442'),
	(208, 20, 1, 'estado.cambiado', 'expedientes', 20, '{"estado": "RESOLUCION_EMITIDA"}', '{"estado": "POR_RENDIR"}', '127.0.0.1', '2026-10-29 20:41:27.275'),
	(209, 21, 1, 'documento.generado', 'documentos_generados', 45, NULL, '{"tipo": "CARTA_VRIN", "sha256": "08ac48d2832d4e6393d63a9cb5dab5f60820bd2bca42a8655846c475c882c4d3", "version": 1, "plantilla_id": 1}', '127.0.0.1', '2026-10-29 20:42:49.075'),
	(210, 21, 1, 'estado.cambiado', 'expedientes', 21, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-29 20:42:49.077'),
	(211, 21, 1, 'estado.cambiado', 'expedientes', 21, '{"estado": "EN_ESPERA_OPP"}', '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '127.0.0.1', '2026-10-30 14:48:10.727'),
	(212, 21, 1, 'documento.generado', 'documentos_generados', 46, NULL, '{"tipo": "RESOLUCION", "sha256": "0883b9af9c45c2c6ce52a61b8e1f296a3ab8d29c49a04b6b5b4f04a903d49f20", "version": 1, "plantilla_id": 4}', '127.0.0.1', '2026-10-30 14:48:22.544'),
	(213, 21, 1, 'estado.cambiado', 'expedientes', 21, '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '{"estado": "RESOLUCION_EMITIDA"}', '127.0.0.1', '2026-10-30 14:48:22.546'),
	(214, 21, 1, 'documento.generado', 'documentos_generados', 47, NULL, '{"tipo": "RESOLUCION", "sha256": "01576370f24c7edacba4ceb6e12a06eacbb48961645aad5b20ce11b4a2d078ab", "version": 2, "plantilla_id": 4}', '127.0.0.1', '2026-10-30 14:49:13.518'),
	(215, 21, 1, 'estado.cambiado', 'expedientes', 21, '{"estado": "RESOLUCION_EMITIDA"}', '{"estado": "POR_RENDIR"}', '127.0.0.1', '2026-10-30 16:22:32.651'),
	(216, 24, 1, 'estado.cambiado', 'expedientes', 24, '{"estado": "POR_RENDIR"}', '{"estado": "RENDIDO"}', '127.0.0.1', '2026-10-30 16:25:16.550'),
	(217, 22, 1, 'estado.cambiado', 'expedientes', 22, '{"estado": "POR_RENDIR"}', '{"estado": "RENDIDO"}', '127.0.0.1', '2026-10-30 16:36:43.743'),
	(218, 21, 1, 'estado.cambiado', 'expedientes', 21, '{"estado": "POR_RENDIR"}', '{"estado": "RENDIDO"}', '127.0.0.1', '2026-10-30 16:37:46.082'),
	(219, NULL, 1, 'plantilla.seleccionada', 'plantilla_seleccionada', 7, NULL, '{"modulo": "ARTICULOS", "plantilla_id": 7, "tipo_documento_id": 1}', '127.0.0.1', '2026-10-30 17:12:47.697'),
	(220, 25, 1, 'expediente.creado', 'expedientes', 25, NULL, '{"codigo": "ART-2026-000025", "estado": "EN_REVISION_CALIDAD", "docente_id": 5}', '127.0.0.1', '2026-10-30 17:19:00.056'),
	(221, 25, 4, 'estado.cambiado', 'expedientes', 25, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-30 17:19:17.286'),
	(222, 25, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 25, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-30 17:19:17", "validado_por": 4}', '127.0.0.1', '2026-10-30 17:19:17.287'),
	(223, 25, 1, 'documento.generado', 'documentos_generados', 48, NULL, '{"tipo": "CARTA_VRIN", "sha256": "2e96a69847fd11820da777287853dafb925ede79d6e481aaf1374cc7f0362f16", "version": 1, "plantilla_id": 7}', '127.0.0.1', '2026-10-30 17:19:51.443'),
	(224, 25, 1, 'estado.cambiado', 'expedientes', 25, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-30 17:19:51.445'),
	(225, 26, 1, 'expediente.creado', 'expedientes', 26, NULL, '{"codigo": "ART-2026-000026", "estado": "EN_REVISION_CALIDAD", "docente_id": 4}', '127.0.0.1', '2026-10-30 17:33:49.662'),
	(226, 26, 4, 'estado.cambiado', 'expedientes', 26, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-30 17:34:03.892'),
	(227, 26, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 26, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-30 17:34:03", "validado_por": 4}', '127.0.0.1', '2026-10-30 17:34:03.893'),
	(228, 26, 1, 'documento.generado', 'documentos_generados', 49, NULL, '{"tipo": "CARTA_VRIN", "sha256": "32a1c188e5a23127df28582cb61e177e7753e7561a0deb01d8abea562da53148", "version": 1, "plantilla_id": 7}', '127.0.0.1', '2026-10-30 17:34:40.766'),
	(229, 26, 1, 'estado.cambiado', 'expedientes', 26, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-30 17:34:40.767'),
	(230, 25, 1, 'estado.cambiado', 'expedientes', 25, '{"estado": "EN_ESPERA_OPP"}', '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '127.0.0.1', '2026-10-30 17:36:25.530'),
	(231, 26, 1, 'estado.cambiado', 'expedientes', 26, '{"estado": "EN_ESPERA_OPP"}', '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '127.0.0.1', '2026-10-30 17:41:59.674'),
	(232, 27, 1, 'expediente.creado', 'expedientes', 27, NULL, '{"codigo": "ART-2026-000027", "estado": "EN_REVISION_CALIDAD", "docente_id": 9}', '127.0.0.1', '2026-10-30 17:44:47.962'),
	(233, 27, 4, 'estado.cambiado', 'expedientes', 27, '{"estado": "EN_REVISION_CALIDAD"}', '{"estado": "VALIDADO_CALIDAD"}', '127.0.0.1', '2026-10-30 17:45:01.842'),
	(234, 27, 4, 'expediente.validacion_calidad', 'validaciones_calidad', 27, NULL, '{"checklist": {"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}, "resultado": "CUMPLE", "observacion": null, "validado_at": "2026-10-30 17:45:01", "validado_por": 4}', '127.0.0.1', '2026-10-30 17:45:01.844'),
	(235, 27, 1, 'documento.generado', 'documentos_generados', 50, NULL, '{"tipo": "CARTA_VRIN", "sha256": "faf73280ea3532de337952f9ad7d6c3d2be4f5b86667ae850eee0f6ffc86448f", "version": 1, "plantilla_id": 7}', '127.0.0.1', '2026-10-30 17:45:42.428'),
	(236, 27, 1, 'estado.cambiado', 'expedientes', 27, '{"estado": "VALIDADO_CALIDAD"}', '{"estado": "EN_ESPERA_OPP"}', '127.0.0.1', '2026-10-30 17:45:42.430'),
	(237, 27, 1, 'estado.cambiado', 'expedientes', 27, '{"estado": "EN_ESPERA_OPP"}', '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '127.0.0.1', '2026-10-30 17:56:58.638'),
	(238, 27, 1, 'documento.generado', 'documentos_generados', 51, NULL, '{"tipo": "RESOLUCION", "sha256": "cba4ee75c1cf3e9eb8ab01735577df407f450c9987dcc040a45e57d64202e889", "version": 1, "plantilla_id": 4}', '127.0.0.1', '2026-10-30 20:12:19.045'),
	(239, 27, 1, 'estado.cambiado', 'expedientes', 27, '{"estado": "DISPONIBILIDAD_CONFIRMADA"}', '{"estado": "RESOLUCION_EMITIDA"}', '127.0.0.1', '2026-10-30 20:12:19.046'),
	(240, 27, 1, 'estado.cambiado', 'expedientes', 27, '{"estado": "RESOLUCION_EMITIDA"}', '{"estado": "POR_RENDIR"}', '127.0.0.1', '2026-10-30 20:50:06.377');

-- Volcando estructura para tabla sgr_vrin.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla sgr_vrin.cache: ~3 rows (aproximadamente)
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
	('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba', 'i:2;', 1794321738),
	('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1794321738;', 1794321738),
	('laravel-cache-dashboard:2026', 'a:9:{s:4:"year";s:4:"2026";s:11:"expedientes";i:16;s:10:"plantillas";i:2;s:23:"plantillas_actualizadas";i:2;s:8:"docentes";i:8;s:16:"docentes_activos";i:8;s:16:"monto_financiado";d:93287.33;s:7:"estados";O:29:"Illuminate\\Support\\Collection":2:{s:8:"\0*\0items";a:5:{s:7:"RENDIDO";i:8;s:13:"EN_ESPERA_OPP";i:2;s:10:"POR_RENDIR";i:3;s:9:"NO_CUMPLE";i:1;s:25:"DISPONIBILIDAD_CONFIRMADA";i:2;}s:28:"\0*\0escapeWhenCastingToString";b:0;}s:9:"actividad";O:29:"Illuminate\\Support\\Collection":2:{s:8:"\0*\0items";a:3:{i:0;a:5:{s:2:"id";i:27;s:6:"codigo";s:15:"ART-2026-000027";s:6:"estado";s:10:"POR_RENDIR";s:7:"docente";s:22:"Walquer Huacani Calsin";s:10:"updated_at";s:25:"2026-10-30T20:50:06+00:00";}i:1;a:5:{s:2:"id";i:26;s:6:"codigo";s:15:"ART-2026-000026";s:6:"estado";s:25:"DISPONIBILIDAD_CONFIRMADA";s:7:"docente";s:25:"Hesmeralda Rojas Enriquez";s:10:"updated_at";s:25:"2026-10-30T17:41:59+00:00";}i:2;a:5:{s:2:"id";i:25;s:6:"codigo";s:15:"ART-2026-000025";s:6:"estado";s:25:"DISPONIBILIDAD_CONFIRMADA";s:7:"docente";s:19:"Delmer Zea Gonzales";s:10:"updated_at";s:25:"2026-10-30T17:36:25+00:00";}}s:28:"\0*\0escapeWhenCastingToString";b:0;}}', 1794321798);

-- Volcando estructura para tabla sgr_vrin.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla sgr_vrin.cache_locks: ~0 rows (aproximadamente)

-- Volcando estructura para tabla sgr_vrin.cartas_vrin
CREATE TABLE IF NOT EXISTS `cartas_vrin` (
  `expediente_id` bigint unsigned NOT NULL,
  `numero` int unsigned NOT NULL,
  `anio` smallint unsigned NOT NULL,
  `fecha` date NOT NULL,
  `ciudad` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Abancay',
  `asunto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('EMITIDA','ANULADA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EMITIDA',
  `emitida_por` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`expediente_id`),
  UNIQUE KEY `uq_cartas_vrin_numero` (`anio`,`numero`),
  KEY `fk_cv_user` (`emitida_por`),
  CONSTRAINT `fk_cv_exp` FOREIGN KEY (`expediente_id`) REFERENCES `expedientes` (`id`),
  CONSTRAINT `fk_cv_user` FOREIGN KEY (`emitida_por`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Etapa 2 (1:1); corrección = anular y generar nueva versión (RN-14)';

-- Volcando datos para la tabla sgr_vrin.cartas_vrin: ~17 rows (aproximadamente)
INSERT INTO `cartas_vrin` (`expediente_id`, `numero`, `anio`, `fecha`, `ciudad`, `asunto`, `estado`, `emitida_por`, `created_at`, `updated_at`) VALUES
	(1, 1, 2026, '2026-10-06', 'Abancay', NULL, 'EMITIDA', 1, '2026-10-06 02:06:21', '2026-10-06 02:06:21'),
	(2, 2, 2026, '2026-10-05', 'Abancay', 'Solicito financiamiento para publicación en revista indexada para el docente Dra. Guadalupe Chaquilla Quilca', 'EMITIDA', 1, '2026-10-06 02:36:49', '2026-10-06 02:36:49'),
	(3, 3, 2026, '2026-10-07', 'Abancay', 'Solicito financiamiento para publicación en revista indexada para el docente Dra. Hesmeralda Rojas Enriquez', 'EMITIDA', 1, '2026-10-07 19:47:02', '2026-10-07 19:47:02'),
	(4, 4, 2026, '2026-10-07', 'Abancay', 'Solicito financiamiento para publicación en revista indexada para el docente Dra. Hesmeralda Rojas Enriquez', 'EMITIDA', 1, '2026-10-07 19:59:43', '2026-10-07 19:59:43'),
	(5, 718, 2026, '2026-07-27', 'Abancay', 'Solicito financiamiento para publicación de artículo científico de la Dra. Guadalupe Chaquilla Quilca', 'EMITIDA', 1, '2026-10-07 22:40:54', '2026-10-07 22:47:26'),
	(6, 719, 2026, '2026-10-09', 'Abancay', 'Solicito financiamiento para publicación en revista indexada para el docente M.Sc. Delmer Zea Gonzales', 'EMITIDA', 1, '2026-10-10 00:40:58', '2026-10-10 00:40:58'),
	(8, 106, 2026, '2026-10-12', 'Abancay', 'Solicito financiamiento para publicación en revista indexada para el docente Dra. Guadalupe Chaquilla Quilca', 'EMITIDA', 1, '2026-10-12 20:29:53', '2026-10-12 20:31:54'),
	(9, 792, 2026, '2026-10-12', 'Abancay', 'Solicito financiamiento para publicación en revista indexada para el docente Dra. Hesmeralda Rojas Enriquez', 'EMITIDA', 1, '2026-10-13 00:06:58', '2026-10-13 01:47:19'),
	(11, 800, 2026, '2026-10-13', 'Abancay', 'Solicito financiamiento para publicación en revista indexada para el docente Dra. Hesmeralda Rojas Enriquez', 'EMITIDA', 1, '2026-10-13 20:44:47', '2026-10-13 20:46:36'),
	(12, 801, 2026, '2026-10-13', 'Abancay', 'Solicito financiamiento para publicación en revista indexada para el docente Mg. Maria Cárdenas Hurtado', 'EMITIDA', 1, '2026-10-13 21:28:29', '2026-10-13 21:28:29'),
	(13, 803, 2026, '2026-10-14', 'Abancay', 'Solicito financiamiento para publicación en revista indexada para el docente Dra. Guadalupe Chaquilla Quilca', 'EMITIDA', 1, '2026-10-14 18:27:21', '2026-10-14 18:27:21'),
	(14, 802, 2026, '2026-10-14', 'Abancay', 'Solicito financiamiento para publicación en revista indexada para el docente Ing. Walquer Huacani Calsin', 'EMITIDA', 1, '2026-10-14 18:21:52', '2026-10-14 18:21:52'),
	(15, 804, 2026, '2026-10-14', 'Abancay', 'Solicito financiamiento para publicación en revista indexada para el docente Ing. Walquer Huacani Calsin', 'EMITIDA', 1, '2026-10-14 18:54:49', '2026-10-14 18:54:49'),
	(18, 92, 2026, '2026-10-20', 'Abancay', 'Solicito financiamiento para publicación en revista indexada para el docente Dra. Guadalupe Chaquilla Quilca', 'EMITIDA', 1, '2026-10-20 19:46:11', '2026-10-20 19:46:11'),
	(19, 805, 2026, '2026-10-26', 'Abancay', 'Solicito financiamiento para publicación en revista indexada para el docente Dra. Guadalupe Chaquilla Quilca', 'EMITIDA', 1, '2026-10-27 02:12:16', '2026-10-27 02:12:16'),
	(20, 806, 2026, '2026-10-29', 'Abancay', 'Solicito financiamiento para publicación en revista indexada para el docente Dra. Guadalupe Chaquilla Quilca', 'EMITIDA', 1, '2026-10-30 00:42:32', '2026-10-30 00:42:32'),
	(21, 807, 2026, '2026-10-29', 'Abancay', 'Solicito financiamiento para publicación en revista indexada para el docente Ing. Walquer Huacani Calsin', 'EMITIDA', 1, '2026-10-30 01:42:49', '2026-10-30 01:42:49'),
	(22, 90, 2026, '2026-10-29', 'Abancay', 'Solicito financiamiento para publicación en revista indexada para el docente Ing. Walquer Huacani Calsin', 'EMITIDA', 1, '2026-10-30 00:50:13', '2026-10-30 00:50:13'),
	(24, 30, 2026, '2026-10-30', 'Abancay', 'Solicito financiamiento para publicación en revista indexada para el docente Dra. Guadalupe Chaquilla Quilca', 'EMITIDA', 1, '2026-10-30 00:47:05', '2026-10-30 00:48:21'),
	(25, 808, 2026, '2026-10-30', 'Abancay', 'Solicito financiamiento para publicación en revista indexada para el docente M.Sc. Delmer Zea Gonzales', 'EMITIDA', 1, '2026-10-30 22:19:51', '2026-10-30 22:19:51'),
	(26, 809, 2026, '2026-10-30', 'Abancay', 'Solicito financiamiento para publicación en revista indexada para la docente Dra. Hesmeralda Rojas Enriquez', 'EMITIDA', 1, '2026-10-30 22:34:40', '2026-10-30 22:34:40'),
	(27, 812, 2026, '2026-10-30', 'Abancay', 'Solicito financiamiento para publicación en revista indexada para el docente Ing. Walquer Huacani Calsin', 'EMITIDA', 1, '2026-10-30 22:45:42', '2026-10-30 22:45:42');

-- Volcando estructura para tabla sgr_vrin.docentes
CREATE TABLE IF NOT EXISTS `docentes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dni` char(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombres` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido_paterno` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido_materno` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `genero` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grado` enum('Dr.','Dra.','Mg.','M.Sc.','Ph.D.','CPC','Ing.','Lic.','Abog.','Otro') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Otro',
  `tipo_contrato` enum('NOMBRADO','CONTRATADO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria` enum('ORDINARIO_PRINCIPAL','ORDINARIO_ASOCIADO','ORDINARIO_AUXILIAR','CONTRATADO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `celular` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `escuela_id` bigint unsigned NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `origen` enum('LOCAL','API') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'LOCAL',
  `external_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `synced_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_docentes_dni` (`dni`),
  UNIQUE KEY `uq_docentes_external` (`origen`,`external_id`),
  KEY `ix_docentes_nombre` (`apellido_paterno`,`apellido_materno`,`nombres`),
  KEY `fk_docentes_escuela` (`escuela_id`),
  CONSTRAINT `fk_docentes_escuela` FOREIGN KEY (`escuela_id`) REFERENCES `escuelas` (`id`),
  CONSTRAINT `ck_docentes_dni` CHECK (regexp_like(`dni`,_utf8mb4'^[0-9]{8}$'))
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Docentes solicitantes (RF-17/47); espejo local listo para la API universitaria';

-- Volcando datos para la tabla sgr_vrin.docentes: ~8 rows (aproximadamente)
INSERT INTO `docentes` (`id`, `dni`, `nombres`, `apellido_paterno`, `apellido_materno`, `genero`, `grado`, `tipo_contrato`, `categoria`, `celular`, `escuela_id`, `email`, `activo`, `origen`, `external_id`, `synced_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, '77100134', 'Luz', 'Carbajal', 'Hurtado', 'FEMENINO', 'Dr.', 'NOMBRADO', 'ORDINARIO_PRINCIPAL', NULL, 2, 'Luz@unamba.edu.pe', 1, 'LOCAL', NULL, NULL, '2026-10-02 19:25:15', '2026-10-27 19:54:27', NULL),
	(2, '77200123', 'Maria', 'Cárdenas', 'Hurtado', 'FEMENINO', 'Mg.', 'NOMBRADO', 'ORDINARIO_PRINCIPAL', NULL, 7, 'Maria@unamba.edu.pe', 1, 'LOCAL', NULL, NULL, '2026-10-02 19:27:53', '2026-10-27 02:05:16', NULL),
	(3, '77100205', 'Guadalupe', 'Chaquilla', 'Quilca', 'FEMENINO', 'Dra.', 'NOMBRADO', 'ORDINARIO_PRINCIPAL', NULL, 4, 'guadalupe@unamba.edu.pe', 1, 'LOCAL', NULL, NULL, '2026-10-02 22:23:12', '2026-10-27 02:05:23', NULL),
	(4, '77199299', 'Hesmeralda', 'Rojas', 'Enriquez', 'FEMENINO', 'Dra.', 'NOMBRADO', 'ORDINARIO_PRINCIPAL', NULL, 6, 'hrojas@unamba.edu.pe', 1, 'LOCAL', NULL, NULL, '2026-10-02 22:43:08', '2026-10-27 02:05:54', NULL),
	(5, '77100101', 'Delmer', 'Zea', 'Gonzales', 'MASCULINO', 'M.Sc.', 'NOMBRADO', 'ORDINARIO_PRINCIPAL', NULL, 7, 'Delmer@unamba.edu.pe', 1, 'LOCAL', NULL, NULL, '2026-10-09 20:02:16', '2026-10-27 02:06:13', NULL),
	(6, '77200456', 'Walquer', 'Calsin', NULL, 'MASCULINO', 'Ing.', 'NOMBRADO', 'ORDINARIO_PRINCIPAL', NULL, 9, 'walquer@unamba.edu.pe', 0, 'LOCAL', NULL, NULL, '2026-10-14 18:17:03', '2026-10-27 19:57:03', '2026-10-27 19:57:03'),
	(7, '70811009', 'Nuria', 'Segundo', 'Villa', 'FEMENINO', 'Dra.', 'NOMBRADO', 'ORDINARIO_PRINCIPAL', '986609906', 6, '191238@unamba.edu.pe', 1, 'LOCAL', NULL, NULL, '2026-10-27 02:07:07', '2026-10-27 02:07:07', NULL),
	(8, '89584512', 'Lucero', 'Villegas', NULL, 'FEMENINO', 'Dra.', 'CONTRATADO', 'CONTRATADO', '986609906', 8, 'lucero@unamba.edu.pe', 1, 'LOCAL', NULL, NULL, '2026-10-27 19:55:57', '2026-10-27 19:55:57', NULL),
	(9, '77200156', 'Walquer', 'Huacani', 'Calsin', 'MASCULINO', 'Ing.', 'NOMBRADO', 'ORDINARIO_ASOCIADO', '986609906', 5, 'walquer02@unamba.edu.pe', 1, 'LOCAL', NULL, NULL, '2026-10-29 22:40:02', '2026-10-29 22:40:02', NULL);

-- Volcando estructura para tabla sgr_vrin.documentos_generados
CREATE TABLE IF NOT EXISTS `documentos_generados` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `expediente_id` bigint unsigned NOT NULL,
  `tipo` enum('CARTA_VRIN','RESOLUCION') COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` int unsigned NOT NULL,
  `plantilla_id` bigint unsigned NOT NULL,
  `datos` json NOT NULL,
  `docx_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pdf_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sha256` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_verificacion` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `es_vigente` tinyint(1) NOT NULL DEFAULT '1',
  `generado_por` bigint unsigned NOT NULL,
  `generado_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `drive_file_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `drive_folder_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `drive_synced_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_docgen` (`expediente_id`,`tipo`,`version`),
  UNIQUE KEY `uq_docgen_codigo` (`codigo_verificacion`),
  KEY `fk_dg_pl` (`plantilla_id`),
  KEY `fk_dg_user` (`generado_por`),
  CONSTRAINT `fk_dg_exp` FOREIGN KEY (`expediente_id`) REFERENCES `expedientes` (`id`),
  CONSTRAINT `fk_dg_pl` FOREIGN KEY (`plantilla_id`) REFERENCES `plantillas` (`id`),
  CONSTRAINT `fk_dg_user` FOREIGN KEY (`generado_por`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Emisiones del motor documental; jamás se sobreescriben (RN-02/RNF-06)';

-- Volcando datos para la tabla sgr_vrin.documentos_generados: ~51 rows (aproximadamente)
INSERT INTO `documentos_generados` (`id`, `expediente_id`, `tipo`, `version`, `plantilla_id`, `datos`, `docx_path`, `pdf_path`, `sha256`, `codigo_verificacion`, `es_vigente`, `generado_por`, `generado_at`, `created_at`, `drive_file_id`, `drive_folder_id`, `drive_synced_at`) VALUES
	(1, 1, 'CARTA_VRIN', 1, 1, '{"GRADO": "Dr.", "CIUDAD": "Abancay", "CUARTIL": "Q1", "ESCUELA": "Ingeniería Civil", "NOMBRES": "Luz", "REVISTA": "Frontiers", "BASE_DATOS": "Scopus", "CARTA_DOCENTE": "CARTA N° 017-2026-CP-MVZ-UNAMBA-ABANCAY-LH", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Hydrochemical characterizacion of hot springs on Ollachea, Puno acording to peruvian", "APELLIDO_MATERNO": "Hurtado", "APELLIDO_PATERNO": "Carbajal", "FECHA_CARTA_VRIN": "6 de octubre del 2026", "NUMERO_CARTA_VRIN": "001-2026", "FECHA_CARTA_DOCENTE": "16/10/2026", "REGISTRO_MESA_PARTES": "1546", "MONTO_TOTAL_SOLICITADO": "6,597.00"}', 'documentos/1/CARTA_VRIN_1_a9fpxeqt.docx', 'documentos/1/CARTA_VRIN_1_a9fpxeqt.pdf', 'd23f2608f368c34ed8409a57373442ff079c7b7ec1a0cf6af1fb2320b6c2c98c', '3YEDHRV7WWGJ', 1, 1, '2026-10-05 21:06:21', '2026-10-06 02:06:21', NULL, NULL, NULL),
	(2, 1, 'RESOLUCION', 1, 2, '{"META": "017", "anio": 2026, "GRADO": "Dr.", "CIUDAD": "Abancay", "FUENTE": "R.O", "CUARTIL": "Q1", "ESCUELA": "Ingeniería Civil", "NOMBRES": "Luz", "REVISTA": "Frontiers", "CARTA_OPP": "Carta-OPP-2026", "BASE_DATOS": "Scopus", "ESPECIFICA": "2.3.11.07", "CARTA_DOCENTE": "CARTA N° 017-2026-CP-MVZ-UNAMBA-ABANCAY-LH", "fecha_emision": "5 de octubre del 2026", "MONTO_APROBADO": "6,597.00", "FECHA_CARTA_OPP": "15 de octubre del 2026", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Hydrochemical characterizacion of hot springs on Ollachea, Puno acording to peruvian", "APELLIDO_MATERNO": "Hurtado", "APELLIDO_PATERNO": "Carbajal", "numero_resolucion": "045", "FECHA_CARTA_DOCENTE": "16 de octubre del 2026", "FECHA_REGISTRO_VRIN": "15 de octubre del 2026", "NUMERO_REGISTRO_VRIN": "1234", "MONTO_TOTAL_SOLICITADO": "6,597.00"}', 'documentos/1/RESOLUCION_1_htlqc22r.docx', 'documentos/1/RESOLUCION_1_htlqc22r.pdf', '2cb08b2cb50741d5c2afae52bfaa3dad947c5d2c1a2ddaeea25a32c26bbf82df', 'MUWHIV329NR7', 1, 1, '2026-10-05 21:27:15', '2026-10-06 02:27:15', NULL, NULL, NULL),
	(3, 2, 'CARTA_VRIN', 1, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Agroindustrial", "NOMBRES": "Guadalupe", "REVISTA": "CyTA Journal of Food", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Guadalupe Chaquilla Quilca", "CARTA_DOCENTE": "CARTA N° 29-2026-GCHQ-EAPIA-FI-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Effect of processing and germination on the nutritional and bioactive properties of black amaranth (Amaranthus hybridus) cultivated at 2700 m above sea level in Apurimac", "APELLIDO_MATERNO": "Quilca", "APELLIDO_PATERNO": "Chaquilla", "FECHA_ACEPTACION": "15 de octubre del 2026", "FECHA_CARTA_VRIN": "5 de octubre del 2026", "NUMERO_CARTA_VRIN": "002-2026", "FECHA_CARTA_DOCENTE": "22/06/2026", "REGISTRO_MESA_PARTES": "1546", "MONTO_TOTAL_SOLICITADO": "9,883.80"}', 'documentos/2/CARTA_VRIN_1_rrdpqzca.docx', 'documentos/2/CARTA_VRIN_1_rrdpqzca.pdf', '71f22a4b006f808d3da1a360056e4bd6217e43a2057a95ea3fb587ade5118bb4', 'QAG6B9311GS2', 1, 1, '2026-10-05 21:36:50', '2026-10-06 02:36:50', NULL, NULL, NULL),
	(4, 2, 'RESOLUCION', 1, 2, '{"META": "017", "anio": 2026, "GRADO": "Dra.", "CIUDAD": "Abancay", "FUENTE": "R.O", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Agroindustrial", "NOMBRES": "Guadalupe", "REVISTA": "CyTA Journal of Food", "CARTA_OPP": "Carta-N° 10-OPP-2026", "BASE_DATOS": "Scopus", "ESPECIFICA": "2.3.11.07", "CARTA_DOCENTE": "CARTA N° 29-2026-GCHQ-EAPIA-FI-UNAMBA", "fecha_emision": "5 de octubre del 2026", "MONTO_APROBADO": "9,883.80", "FECHA_CARTA_OPP": "7 de octubre del 2026", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Effect of processing and germination on the nutritional and bioactive properties of black amaranth (Amaranthus hybridus) cultivated at 2700 m above sea level in Apurimac", "APELLIDO_MATERNO": "Quilca", "APELLIDO_PATERNO": "Chaquilla", "numero_resolucion": "050", "FECHA_CARTA_DOCENTE": "22 de junio del 2026", "FECHA_REGISTRO_VRIN": "9 de octubre del 2026", "NUMERO_REGISTRO_VRIN": "1234", "MONTO_TOTAL_SOLICITADO": "9,883.80"}', 'documentos/2/RESOLUCION_1_cajomz0e.docx', 'documentos/2/RESOLUCION_1_cajomz0e.pdf', 'cf831701aded937f05f25b1b634914537428676d089093783d77cfd76df020d7', 'OFBEVCMFC3LJ', 1, 1, '2026-10-05 21:38:25', '2026-10-06 02:38:25', NULL, NULL, NULL),
	(5, 3, 'CARTA_VRIN', 1, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q1", "ESCUELA": "Ingeniería Informática y Sistemas", "NOMBRES": "Hesmeralda", "REVISTA": "Frontiers", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Hesmeralda Rojas Enriquez", "CARTA_DOCENTE": "CARTA N° 35-2026-FI-HRE-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Hydrochemical characterization of hot springs in Ollachea, Puno, according to Peruvian environmental standards", "APELLIDO_MATERNO": "Enriquez", "APELLIDO_PATERNO": "Rojas", "FECHA_ACEPTACION": "6 de octubre del 2026", "FECHA_CARTA_VRIN": "7 de octubre del 2026", "NUMERO_CARTA_VRIN": "003-2026", "FECHA_CARTA_DOCENTE": "02/06/2026", "REGISTRO_MESA_PARTES": "1546", "MONTO_TOTAL_SOLICITADO": "6,591.15"}', 'documentos/3/CARTA_VRIN_1_rzkv6hp0.docx', 'documentos/3/CARTA_VRIN_1_rzkv6hp0.pdf', 'd9dfa512179053cea84419fdeed8e42990d783e2ea3d456fd8289c8784466f8c', 'YBH2LJVFGZ8T', 0, 1, '2026-10-07 14:47:03', '2026-10-07 19:47:03', NULL, NULL, NULL),
	(6, 3, 'CARTA_VRIN', 2, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q1", "ESCUELA": "Ingeniería Informática y Sistemas", "NOMBRES": "Hesmeralda", "REVISTA": "Frontiers", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Hesmeralda Rojas Enriquez", "CARTA_DOCENTE": "CARTA N° 35-2026-FI-HRE-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Hydrochemical characterization of hot springs in Ollachea, Puno, according to Peruvian environmental standards", "APELLIDO_MATERNO": "Enriquez", "APELLIDO_PATERNO": "Rojas", "FECHA_ACEPTACION": "24 de octubre del 2026", "FECHA_CARTA_VRIN": "7 de octubre del 2026", "NUMERO_CARTA_VRIN": "003-2026", "FECHA_CARTA_DOCENTE": "02/06/2026", "REGISTRO_MESA_PARTES": "1546", "MONTO_TOTAL_SOLICITADO": "6,591.15"}', 'documentos/3/CARTA_VRIN_2_dazxue4h.docx', 'documentos/3/CARTA_VRIN_2_dazxue4h.pdf', 'e5a09a7df36356c0402ec27523d1c431b966819d8a68c89210ed0670f2c31caf', 'XZFOCPYN7QQB', 1, 1, '2026-10-07 14:47:24', '2026-10-07 19:47:24', NULL, NULL, NULL),
	(7, 4, 'CARTA_VRIN', 1, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q1", "ESCUELA": "Ingeniería Informática y Sistemas", "NOMBRES": "Hesmeralda", "REVISTA": "Frontiers", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Hesmeralda Rojas Enriquez", "CARTA_DOCENTE": "CARTA N° 35-2026-FI-HRE-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Hydrochemical characterization of hot springs in Ollachea, Puno, according to Peruvian environmental standards", "APELLIDO_MATERNO": "Enriquez", "APELLIDO_PATERNO": "Rojas", "FECHA_ACEPTACION": "9 de octubre del 2026", "FECHA_CARTA_VRIN": "7 de octubre del 2026", "NUMERO_CARTA_VRIN": "004-2026", "FECHA_CARTA_DOCENTE": "02/06/2026", "REGISTRO_MESA_PARTES": "1546", "MONTO_TOTAL_SOLICITADO": "6,591.15"}', 'documentos/4/CARTA_VRIN_1_yeqgorea.docx', 'documentos/4/CARTA_VRIN_1_yeqgorea.pdf', '1601f06d86d73dca25c982b1e7ca1a57774a5ef921c69fc0f12bedc3e126bb05', 'DVKFXP6U8C6O', 0, 1, '2026-10-07 14:59:43', '2026-10-07 19:59:43', NULL, NULL, NULL),
	(8, 4, 'CARTA_VRIN', 2, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q1", "ESCUELA": "Ingeniería Informática y Sistemas", "NOMBRES": "Hesmeralda", "REVISTA": "Frontiers", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Hesmeralda Rojas Enriquez", "CARTA_DOCENTE": "CARTA N° 35-2026-FI-HRE-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Hydrochemical characterization of hot springs in Ollachea, Puno, according to Peruvian environmental standards", "APELLIDO_MATERNO": "Enriquez", "APELLIDO_PATERNO": "Rojas", "FECHA_ACEPTACION": "17 de octubre del 2026", "FECHA_CARTA_VRIN": "7 de octubre del 2026", "NUMERO_CARTA_VRIN": "004-2026", "FECHA_CARTA_DOCENTE": "02/06/2026", "REGISTRO_MESA_PARTES": "1356", "MONTO_TOTAL_SOLICITADO": "6,591.15"}', 'documentos/4/CARTA_VRIN_2_owix6dnv.docx', 'documentos/4/CARTA_VRIN_2_owix6dnv.pdf', '3180dd7f139655f2be8d4e2d19d08a84fc6101bd822ab5988635c2b5369514ec', 'NWMBEANHBFJI', 0, 1, '2026-10-07 15:26:01', '2026-10-07 20:26:01', NULL, NULL, NULL),
	(9, 4, 'CARTA_VRIN', 3, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q1", "ESCUELA": "Ingeniería Informática y Sistemas", "NOMBRES": "Hesmeralda", "REVISTA": "Frontiers", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Hesmeralda Rojas Enriquez", "CARTA_DOCENTE": "CARTA N° 35-2026-FI-HRE-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Hydrochemical characterization of hot springs in Ollachea, Puno, according to Peruvian environmental standards", "APELLIDO_MATERNO": "Enriquez", "APELLIDO_PATERNO": "Rojas", "FECHA_ACEPTACION": "21 de octubre del 2026", "FECHA_CARTA_VRIN": "7 de octubre del 2026", "NUMERO_CARTA_VRIN": "004-2026", "FECHA_CARTA_DOCENTE": "02/06/2026", "REGISTRO_MESA_PARTES": "12099", "MONTO_TOTAL_SOLICITADO": "6,591.15"}', 'documentos/4/CARTA_VRIN_3_gvsqqyon.docx', 'documentos/4/CARTA_VRIN_3_gvsqqyon.pdf', 'd00faa757d4c8fec0a4e361830a37c17117aae409e14ef573de9453e1bd23dd1', 'X2UTPV9DJSN8', 0, 1, '2026-10-07 15:42:10', '2026-10-07 20:42:10', NULL, NULL, NULL),
	(10, 4, 'CARTA_VRIN', 4, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q1", "ESCUELA": "Ingeniería Informática y Sistemas", "NOMBRES": "Hesmeralda", "REVISTA": "Frontiers", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Hesmeralda Rojas Enriquez", "CARTA_DOCENTE": "CARTA N° 35-2026-FI-HRE-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Hydrochemical characterization of hot springs in Ollachea, Puno, according to Peruvian environmental standards", "APELLIDO_MATERNO": "Enriquez", "APELLIDO_PATERNO": "Rojas", "FECHA_ACEPTACION": "8 de octubre del 2026", "FECHA_CARTA_VRIN": "7 de octubre del 2026", "NUMERO_CARTA_VRIN": "004-2026", "FECHA_CARTA_DOCENTE": "02/06/2026", "REGISTRO_MESA_PARTES": "50597", "MONTO_TOTAL_SOLICITADO": "6,591.15"}', 'documentos/4/CARTA_VRIN_4_ohmnfbdk.docx', 'documentos/4/CARTA_VRIN_4_ohmnfbdk.pdf', '8efa46e3c10545f12086bd0236df6e8318e656712564a338d2d17fc124e9d61a', 'FS7K5KLPYDHL', 0, 1, '2026-10-07 15:52:37', '2026-10-07 20:52:37', NULL, NULL, NULL),
	(11, 4, 'CARTA_VRIN', 5, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q1", "ESCUELA": "Ingeniería Informática y Sistemas", "NOMBRES": "Hesmeralda", "REVISTA": "Frontiers", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Hesmeralda Rojas Enriquez", "CARTA_DOCENTE": "CARTA N° 35-2026-FI-HRE-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Hydrochemical characterization of hot springs in Ollachea, Puno, according to Peruvian environmental standards", "APELLIDO_MATERNO": "Enriquez", "APELLIDO_PATERNO": "Rojas", "FECHA_ACEPTACION": "8 de octubre del 2026", "FECHA_CARTA_VRIN": "7 de octubre del 2026", "NUMERO_CARTA_VRIN": "004-2026", "FECHA_CARTA_DOCENTE": "02/06/2026", "REGISTRO_MESA_PARTES": "50597", "MONTO_TOTAL_SOLICITADO": "6,591.15"}', 'documentos/4/CARTA_VRIN_5_fb8fqtn3.docx', 'documentos/4/CARTA_VRIN_5_fb8fqtn3.pdf', 'e727c038a5dddd1f9b39f57377f7c68fc666d6dc963c31d69493483fb39bc19a', 'VIDS9HGXBI3O', 1, 1, '2026-10-07 16:33:50', '2026-10-07 21:33:50', NULL, NULL, NULL),
	(12, 5, 'CARTA_VRIN', 1, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Agroindustrial", "NOMBRES": "Guadalupe", "REVISTA": "CyTA Journal of Food", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Guadalupe Chaquilla Quilca", "CARTA_DOCENTE": "CARTA N° N°29-2026-GCHQ-EAPIA-FI-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Effect of processing and germination on the nutritional and bioactive properties of black amaranth (Amaranthus hybridus) cultivated at 2700 m above sea level in Apurimac", "APELLIDO_MATERNO": "Quilca", "APELLIDO_PATERNO": "Chaquilla", "FECHA_ACEPTACION": "2 de octubre del 2026", "FECHA_CARTA_VRIN": "7 de octubre del 2026", "NUMERO_CARTA_VRIN": "005-2026", "FECHA_CARTA_DOCENTE": "25/06/2026", "REGISTRO_MESA_PARTES": "77100", "MONTO_TOTAL_SOLICITADO": "9,883.80"}', 'documentos/5/CARTA_VRIN_1_kjevqnne.docx', 'documentos/5/CARTA_VRIN_1_kjevqnne.pdf', 'ba218d5e16ca761d3d5c38588b6597d9096999629ae2ac85ee2d365d4e0d398e', 'L4IALCA7JQQH', 0, 1, '2026-10-07 17:40:54', '2026-10-07 22:40:54', NULL, NULL, NULL),
	(13, 5, 'CARTA_VRIN', 2, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Agroindustrial", "NOMBRES": "Guadalupe", "REVISTA": "CyTA Journal of Food", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación de artículo científico de la Dra. Guadalupe Chaquilla Quilca", "CARTA_DOCENTE": "CARTA N° N°29-2026-GCHQ-EAPIA-FI-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Effect of processing and germination on the nutritional and bioactive properties of black amaranth (Amaranthus hybridus) cultivated at 2700 m above sea level in Apurimac", "APELLIDO_MATERNO": "Quilca", "APELLIDO_PATERNO": "Chaquilla", "FECHA_ACEPTACION": "2 de octubre del 2026", "FECHA_CARTA_VRIN": "27 de julio del 2026", "NUMERO_CARTA_VRIN": "718-2026", "FECHA_CARTA_DOCENTE": "25/06/2026", "REGISTRO_MESA_PARTES": "1555-2025", "MONTO_TOTAL_SOLICITADO": "9,883.80"}', 'documentos/5/CARTA_VRIN_2_yygveyrz.docx', 'documentos/5/CARTA_VRIN_2_yygveyrz.pdf', 'a49395c46d4d378da1c42ce3533760376155558293864c56ab2c6aef71f64cb2', 'VQHDS3FKXJQN', 1, 1, '2026-10-07 17:47:26', '2026-10-07 22:47:26', NULL, NULL, NULL),
	(14, 5, 'RESOLUCION', 1, 2, '{"META": "017", "anio": 2026, "GRADO": "Dra.", "CIUDAD": "Abancay", "FUENTE": "Recursos Determinados", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Agroindustrial", "NOMBRES": "Guadalupe", "REVISTA": "CyTA Journal of Food", "CARTA_OPP": "1688-2026-OPP-UNAMBA", "BASE_DATOS": "Scopus", "ESPECIFICA": "2.3.27.1199", "CARTA_DOCENTE": "CARTA N° N°29-2026-GCHQ-EAPIA-FI-UNAMBA", "fecha_emision": "3 de agosto del 2026", "MONTO_APROBADO": "9,883.00", "FECHA_CARTA_OPP": "30 de julio del 2026", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Effect of processing and germination on the nutritional and bioactive properties of black amaranth (Amaranthus hybridus) cultivated at 2700 m above sea level in Apurimac", "APELLIDO_MATERNO": "Quilca", "APELLIDO_PATERNO": "Chaquilla", "numero_resolucion": "068", "FECHA_CARTA_DOCENTE": "25 de junio del 2026", "FECHA_REGISTRO_VRIN": "31 de julio del 2026", "NUMERO_REGISTRO_VRIN": "1581", "MONTO_TOTAL_SOLICITADO": "9,883.80"}', 'documentos/5/RESOLUCION_1_ynhmiuja.docx', 'documentos/5/RESOLUCION_1_ynhmiuja.pdf', 'faf03ba22ee763161eabbc0d502c48651211f4bb4840c6791d7424b5de930b27', 'LVZL7NBK7LY4', 0, 1, '2026-10-07 17:52:25', '2026-10-07 22:52:25', NULL, NULL, NULL),
	(15, 5, 'RESOLUCION', 2, 2, '{"META": "017", "anio": 2026, "GRADO": "Dra.", "CIUDAD": "Abancay", "FUENTE": "Recursos Determinados", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Agroindustrial", "NOMBRES": "Guadalupe", "REVISTA": "CyTA Journal of Food", "CARTA_OPP": "1688-2026-OPP-UNAMBA", "BASE_DATOS": "Scopus", "ESPECIFICA": "2.3.27.1199", "CARTA_DOCENTE": "CARTA N° N°29-2026-GCHQ-EAPIA-FI-UNAMBA", "fecha_emision": "3 de agosto del 2026", "MONTO_APROBADO": "9,883.00", "FECHA_CARTA_OPP": "30 de julio del 2026", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Effect of processing and germination on the nutritional and bioactive properties of black amaranth (Amaranthus hybridus) cultivated at 2700 m above sea level in Apurimac", "APELLIDO_MATERNO": "Quilca", "APELLIDO_PATERNO": "Chaquilla", "numero_resolucion": "090", "FECHA_CARTA_DOCENTE": "25 de junio del 2026", "FECHA_REGISTRO_VRIN": "31 de julio del 2026", "NUMERO_REGISTRO_VRIN": "1581", "MONTO_TOTAL_SOLICITADO": "9,883.80"}', 'documentos/5/RESOLUCION_2_xa7axdrr.docx', 'documentos/5/RESOLUCION_2_xa7axdrr.pdf', '9f1f9d697899f44f42793410c6abd0ecd8adcb2ab945ab49f67291d599702d5a', 'NGKLYW9OK5KF', 0, 1, '2026-10-07 19:37:41', '2026-10-08 00:37:41', NULL, NULL, NULL),
	(16, 5, 'RESOLUCION', 3, 2, '{"META": "017", "anio": 2026, "GRADO": "Dra.", "CIUDAD": "Abancay", "FUENTE": "Recursos Determinados", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Agroindustrial", "NOMBRES": "Guadalupe", "REVISTA": "CyTA Journal of Food", "CARTA_OPP": "1688-2026-OPP-UNAMBA", "BASE_DATOS": "Scopus", "ESPECIFICA": "2.3.27.1199", "CARTA_DOCENTE": "CARTA N° N°29-2026-GCHQ-EAPIA-FI-UNAMBA", "fecha_emision": "3 de agosto del 2026", "MONTO_APROBADO": "9,883.00", "FECHA_CARTA_OPP": "30 de julio del 2026", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Effect of processing and germination on the nutritional and bioactive properties of black amaranth (Amaranthus hybridus) cultivated at 2700 m above sea level in Apurimac", "APELLIDO_MATERNO": "Quilca", "APELLIDO_PATERNO": "Chaquilla", "numero_resolucion": "093", "FECHA_CARTA_DOCENTE": "25 de junio del 2026", "FECHA_REGISTRO_VRIN": "31 de julio del 2026", "NUMERO_REGISTRO_VRIN": "1581", "MONTO_TOTAL_SOLICITADO": "9,883.80"}', 'documentos/5/RESOLUCION_3_x3i4d2ev.docx', 'documentos/5/RESOLUCION_3_x3i4d2ev.pdf', '46e8180a008fdb7594edbfbdbad78d877bbe54268253490a7a0efeba8b387c7c', 'LX637EN5XJBS', 1, 1, '2026-10-07 20:06:50', '2026-10-08 01:06:50', NULL, NULL, NULL),
	(17, 6, 'CARTA_VRIN', 1, 1, '{"GRADO": "M.Sc.", "CIUDAD": "Abancay", "CUARTIL": "Q1", "ESCUELA": "Medicina Veterinaria y Zootécnia", "NOMBRES": "Delmer", "REVISTA": "Frontiers in Insect Science", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente M.Sc. Delmer Zea Gonzales", "CARTA_DOCENTE": "CARTA N° 017 - 2026- CP.MVZ - UNAMBA - ABANCAY/D.DZG", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Characterization and Typology of Beekeeping Systems: A Case Study in the District of Tamburco, Apurímac, Peru", "APELLIDO_MATERNO": "Gonzales", "APELLIDO_PATERNO": "Zea", "FECHA_ACEPTACION": "14 de julio del 2026", "FECHA_CARTA_VRIN": "9 de octubre del 2026", "NUMERO_CARTA_VRIN": "719-2026", "FECHA_CARTA_DOCENTE": "14/07/2026", "REGISTRO_MESA_PARTES": "1475", "MONTO_TOTAL_SOLICITADO": "11,420.00"}', 'documentos/6/CARTA_VRIN_1_xf9snwew.docx', 'documentos/6/CARTA_VRIN_1_xf9snwew.pdf', 'df1e178d62fcd51bdf35b49d03a85724aff85b759f188ffda9135bca02f29e9e', 'HXSDDFFEKIZ7', 1, 1, '2026-10-09 19:40:58', '2026-10-10 00:40:58', NULL, NULL, NULL),
	(18, 6, 'RESOLUCION', 1, 2, '{"META": "017", "anio": 2026, "GRADO": "M.Sc.", "CIUDAD": "Abancay", "FUENTE": "Recursos Determinados", "CUARTIL": "Q1", "ESCUELA": "Medicina Veterinaria y Zootécnia", "NOMBRES": "Delmer", "REVISTA": "Frontiers in Insect Science", "CARTA_OPP": "CARTA N° 1574-2026-OPP-UNAMBA", "BASE_DATOS": "Scopus", "ESPECIFICA": "2.3.2 7.11 99", "CARTA_DOCENTE": "CARTA N° 017 - 2026- CP.MVZ - UNAMBA - ABANCAY/D.DZG", "fecha_emision": "9 de octubre del 2026", "MONTO_APROBADO": "11,420.00", "FECHA_CARTA_OPP": "20 de julio del 2026", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Characterization and Typology of Beekeeping Systems: A Case Study in the District of Tamburco, Apurímac, Peru", "APELLIDO_MATERNO": "Gonzales", "APELLIDO_PATERNO": "Zea", "numero_resolucion": "097", "FECHA_CARTA_DOCENTE": "14 de julio del 2026", "FECHA_REGISTRO_VRIN": "20 de julio del 2026", "NUMERO_REGISTRO_VRIN": "1537", "MONTO_TOTAL_SOLICITADO": "11,420.00"}', 'documentos/6/RESOLUCION_1_v3jnvhmf.docx', 'documentos/6/RESOLUCION_1_v3jnvhmf.pdf', '4ee0e554e4f9ae34ed1094ee908a312034011abdd8f86dc26630696078db9d4d', 'FA0ORUER4TJA', 1, 1, '2026-10-09 19:52:31', '2026-10-10 00:52:31', NULL, NULL, NULL),
	(19, 8, 'CARTA_VRIN', 1, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Agroindustrial", "NOMBRES": "Guadalupe", "REVISTA": "CyTA Journal of Food", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Guadalupe Chaquilla Quilca", "CARTA_DOCENTE": "CARTA N° N°29-2026-GCHQ-EAPIA-FI-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Effect of processing and germination on the nutritional and bioactive properties of black amaranth (Amaranthus hybridus) cultivated at 2700 m above sea level in Apurimac", "APELLIDO_MATERNO": "Quilca", "APELLIDO_PATERNO": "Chaquilla", "FECHA_ACEPTACION": "22 de julio del 2026", "FECHA_CARTA_VRIN": "12 de octubre del 2026", "NUMERO_CARTA_VRIN": "100-2026", "FECHA_CARTA_DOCENTE": "22/07/2026", "REGISTRO_MESA_PARTES": "1555", "MONTO_TOTAL_SOLICITADO": "9,883.80"}', 'documentos/8/CARTA_VRIN_1_vlfm11k3.docx', 'documentos/8/CARTA_VRIN_1_vlfm11k3.pdf', 'f608223f6828b457f01404f7cb98a0e2cff07d8713c8b91d33d8230afff0d19d', 'Z3YWFQT0UMCL', 0, 1, '2026-10-12 15:29:53', '2026-10-12 20:29:53', NULL, NULL, NULL),
	(20, 8, 'CARTA_VRIN', 2, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Agroindustrial", "NOMBRES": "Guadalupe", "REVISTA": "CyTA Journal of Food", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Guadalupe Chaquilla Quilca", "CARTA_DOCENTE": "CARTA N° N°29-2026-GCHQ-EAPIA-FI-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Effect of processing and germination on the nutritional and bioactive properties of black amaranth (Amaranthus hybridus) cultivated at 2700 m above sea level in Apurimac", "APELLIDO_MATERNO": "Quilca", "APELLIDO_PATERNO": "Chaquilla", "FECHA_ACEPTACION": "22 de julio del 2026", "FECHA_CARTA_VRIN": "12 de octubre del 2026", "NUMERO_CARTA_VRIN": "106-2026", "FECHA_CARTA_DOCENTE": "22/07/2026", "REGISTRO_MESA_PARTES": "1555", "MONTO_TOTAL_SOLICITADO": "9,883.80"}', 'documentos/8/CARTA_VRIN_2_iuuaborv.docx', 'documentos/8/CARTA_VRIN_2_iuuaborv.pdf', '19e9a2cd844b3ab7a570a18a3620b49fb6ddb393d4d31171b408050755f36d0b', 'D2PSQFRIALZB', 1, 1, '2026-10-12 15:31:54', '2026-10-12 20:31:54', NULL, NULL, NULL),
	(21, 8, 'RESOLUCION', 1, 2, '{"META": "017", "anio": 2026, "GRADO": "Dra.", "CIUDAD": "Abancay", "FUENTE": "Recursos Determinados", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Agroindustrial", "NOMBRES": "Guadalupe", "REVISTA": "CyTA Journal of Food", "CARTA_OPP": "CARTA N° 1688-2026-OPP-UNAMBA", "BASE_DATOS": "Scopus", "ESPECIFICA": "2.3.27.1199", "CARTA_DOCENTE": "CARTA N° N°29-2026-GCHQ-EAPIA-FI-UNAMBA", "fecha_emision": "12 de octubre del 2026", "MONTO_APROBADO": "9,883.00", "FECHA_CARTA_OPP": "30 de julio del 2026", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Effect of processing and germination on the nutritional and bioactive properties of black amaranth (Amaranthus hybridus) cultivated at 2700 m above sea level in Apurimac", "APELLIDO_MATERNO": "Quilca", "APELLIDO_PATERNO": "Chaquilla", "numero_resolucion": "060", "FECHA_CARTA_DOCENTE": "22 de julio del 2026", "FECHA_REGISTRO_VRIN": "31 de julio del 2026", "NUMERO_REGISTRO_VRIN": "1381", "MONTO_TOTAL_SOLICITADO": "9,883.80"}', 'documentos/8/RESOLUCION_1_sbbxsiu6.docx', 'documentos/8/RESOLUCION_1_sbbxsiu6.pdf', 'a2335aff95d9450f748259d4b9d86781b1333e7425ef7785ada06a724fc0c0b3', 'XULE4ISEGV2Q', 1, 1, '2026-10-12 15:36:48', '2026-10-12 20:36:48', NULL, NULL, NULL),
	(22, 9, 'CARTA_VRIN', 1, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q1", "ESCUELA": "Ingeniería Informática y Sistemas", "NOMBRES": "Hesmeralda", "REVISTA": "Frontiers", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Hesmeralda Rojas Enriquez", "CARTA_DOCENTE": "CARTA N° 35-2026-FI-HRE-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Hydrochemical characterization of hot springs in Ollachea, Puno, according to Peruvian environmental standards", "APELLIDO_MATERNO": "Enriquez", "APELLIDO_PATERNO": "Rojas", "FECHA_ACEPTACION": "2 de junio del 2026", "FECHA_CARTA_VRIN": "12 de octubre del 2026", "NUMERO_CARTA_VRIN": "790-2026", "FECHA_CARTA_DOCENTE": "02/06/2026", "REGISTRO_MESA_PARTES": "1114", "MONTO_TOTAL_SOLICITADO": "6,591.15"}', 'documentos/9/CARTA_VRIN_1_iglm0yad.docx', 'documentos/9/CARTA_VRIN_1_iglm0yad.pdf', 'b0a928c15d8d418bfc34c855d90d5e01dcd6387812b72693d6ad70ea01236247', 'Z6LGULE2YXRK', 0, 1, '2026-10-12 19:06:58', '2026-10-13 00:06:58', NULL, NULL, NULL),
	(23, 9, 'CARTA_VRIN', 2, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q1", "ESCUELA": "Ingeniería Informática y Sistemas", "NOMBRES": "Hesmeralda", "REVISTA": "Frontiers", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Hesmeralda Rojas Enriquez", "CARTA_DOCENTE": "CARTA N° 35-2026-FI-HRE-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Hydrochemical characterization of hot springs in Ollachea, Puno, according to Peruvian environmental standards", "APELLIDO_MATERNO": "Enriquez", "APELLIDO_PATERNO": "Rojas", "FECHA_ACEPTACION": "2 de junio del 2026", "FECHA_CARTA_VRIN": "12 de octubre del 2026", "NUMERO_CARTA_VRIN": "791-2026", "FECHA_CARTA_DOCENTE": "02/06/2026", "REGISTRO_MESA_PARTES": "1114", "MONTO_TOTAL_SOLICITADO": "6,591.15"}', 'documentos/9/CARTA_VRIN_2_cttgonlf.docx', 'documentos/9/CARTA_VRIN_2_cttgonlf.pdf', '7a4e6b07ef5fa6bc1fdae9cfad3ea573bd5e6bccaf98d20060cf888337bc7c5d', 'FPDVJDRH8KSX', 0, 1, '2026-10-12 19:19:00', '2026-10-13 00:19:00', '1osJFqT_bT8M4_K0u3Jt0FOgqqGUdQdc0', '1Ps3pNZnwYAqUUlNGcWrVjuvnPxvXoRO8', '2026-10-13 01:46:13'),
	(24, 9, 'CARTA_VRIN', 3, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q1", "ESCUELA": "Ingeniería Informática y Sistemas", "NOMBRES": "Hesmeralda", "REVISTA": "Frontiers", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Hesmeralda Rojas Enriquez", "CARTA_DOCENTE": "CARTA N° 35-2026-FI-HRE-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Hydrochemical characterization of hot springs in Ollachea, Puno, according to Peruvian environmental standards", "APELLIDO_MATERNO": "Enriquez", "APELLIDO_PATERNO": "Rojas", "FECHA_ACEPTACION": "2 de junio del 2026", "FECHA_CARTA_VRIN": "12 de octubre del 2026", "NUMERO_CARTA_VRIN": "792-2026", "FECHA_CARTA_DOCENTE": "02/06/2026", "REGISTRO_MESA_PARTES": "1114", "MONTO_TOTAL_SOLICITADO": "6,591.15"}', 'documentos/9/CARTA_VRIN_3_kuz8ak3w.docx', NULL, 'b31ebffb53e6ad9d23cff265d0f121671ec6f80476b7349ff4586a357da3f1f9', 'QMK9VNTSG6HJ', 1, 1, '2026-10-12 20:47:19', '2026-10-13 01:47:19', '1BPhZLORIyggwlQ-CfeQY8_U-QHU0CaaH', '1Ps3pNZnwYAqUUlNGcWrVjuvnPxvXoRO8', '2026-10-13 01:47:28'),
	(25, 9, 'RESOLUCION', 1, 2, '{"META": "017", "anio": 2026, "GRADO": "Dra.", "CIUDAD": "Abancay", "FUENTE": "Recursos Determinados", "CUARTIL": "Q1", "ESCUELA": "Ingeniería Informática y Sistemas", "NOMBRES": "Hesmeralda", "REVISTA": "Frontiers", "CARTA_OPP": "1176-2026-OPP-UNAMBA", "BASE_DATOS": "Scopus", "ESPECIFICA": "2.3.27.11", "CARTA_DOCENTE": "CARTA N° 35-2026-FI-HRE-UNAMBA", "fecha_emision": "12 de octubre del 2026", "MONTO_APROBADO": "6,591.15", "FECHA_CARTA_OPP": "8 de junio del 2026", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Hydrochemical characterization of hot springs in Ollachea, Puno, according to Peruvian environmental standards", "APELLIDO_MATERNO": "Enriquez", "APELLIDO_PATERNO": "Rojas", "numero_resolucion": "076", "FECHA_CARTA_DOCENTE": "2 de junio del 2026", "FECHA_REGISTRO_VRIN": "10 de junio del 2026", "NUMERO_REGISTRO_VRIN": "1155", "MONTO_TOTAL_SOLICITADO": "6,591.15"}', 'documentos/9/RESOLUCION_1_hhdkuizc.docx', NULL, 'e3f007bcf09fdb08bee9bf4849c3816ee3510561bc6a3e0b893b22cab5a0c8ca', 'W6ERSV5NMGAZ', 1, 1, '2026-10-12 20:52:46', '2026-10-13 01:52:46', '1izVnljjy-kAudurl7DRMDXyZVZXOu9di', '1hw-xlYrDmCvKFTL6tDC9aYCeikzBeA--', '2026-10-13 01:56:39'),
	(26, 11, 'CARTA_VRIN', 1, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Informática y Sistemas", "NOMBRES": "Hesmeralda", "REVISTA": "Riqchary", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Hesmeralda Rojas Enriquez", "CARTA_DOCENTE": "CARTA N° 29-2026-GCHQ-EAPIA-FI-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Asistente multimodal con inteligencia artificial para optimizar la comunicación de admisión en la UNAMBA", "APELLIDO_MATERNO": "Enriquez", "APELLIDO_PATERNO": "Rojas", "FECHA_ACEPTACION": "20 de octubre del 2026", "FECHA_CARTA_VRIN": "13 de octubre del 2026", "NUMERO_CARTA_VRIN": "793-2026", "FECHA_CARTA_DOCENTE": "14/10/2026", "REGISTRO_MESA_PARTES": "1555", "MONTO_TOTAL_SOLICITADO": "3,200.00"}', 'documentos/11/CARTA_VRIN_1_vqfidusl.docx', 'documentos/11/CARTA_VRIN_1_vqfidusl.pdf', '78ed28738583a28f62bb4a3d2648312af16a6aa526446ea8df9117661efe1795', 'V9U27XRGMKYW', 0, 1, '2026-10-13 15:44:47', '2026-10-13 20:44:47', '1XUPyyhCtJaqwS7X-8osZaYfQSxrJe2L_', '1Ps3pNZnwYAqUUlNGcWrVjuvnPxvXoRO8', '2026-10-13 20:45:53'),
	(27, 11, 'CARTA_VRIN', 2, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Informática y Sistemas", "NOMBRES": "Hesmeralda", "REVISTA": "Riqchary", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Hesmeralda Rojas Enriquez", "CARTA_DOCENTE": "CARTA N° 29-2026-GCHQ-EAPIA-FI-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Asistente multimodal con inteligencia artificial para optimizar la comunicación de admisión en la UNAMBA", "APELLIDO_MATERNO": "Enriquez", "APELLIDO_PATERNO": "Rojas", "FECHA_ACEPTACION": "20 de octubre del 2026", "FECHA_CARTA_VRIN": "13 de octubre del 2026", "NUMERO_CARTA_VRIN": "800-2026", "FECHA_CARTA_DOCENTE": "14/10/2026", "REGISTRO_MESA_PARTES": "1555", "MONTO_TOTAL_SOLICITADO": "3,200.00"}', 'documentos/11/CARTA_VRIN_2_obs9cqli.docx', NULL, '33ff47d86547162a712b652d205f7de97e84fd899719916ef047d3959e0ad4ea', '8MS5KDLR6QXD', 1, 1, '2026-10-13 15:46:36', '2026-10-13 20:46:36', NULL, NULL, NULL),
	(28, 12, 'CARTA_VRIN', 1, 1, '{"GRADO": "Mg.", "CIUDAD": "Abancay", "CUARTIL": "Q2", "ESCUELA": "Medicina Veterinaria y Zootécnia", "NOMBRES": "Maria", "REVISTA": "Micaela", "BASE_DATOS": "SciELO", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Mg. Maria Cárdenas Hurtado", "CARTA_DOCENTE": "CARTA N° 017 - 2026- CP.MVZ - UNAMBA - ABANCAY/D.DZG", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "FIDO2 y WebAuthn para autenticación organizacional resistente al phishing: revisión sistemática", "APELLIDO_MATERNO": "Hurtado", "APELLIDO_PATERNO": "Cárdenas", "FECHA_ACEPTACION": "14 de octubre del 2026", "FECHA_CARTA_VRIN": "13 de octubre del 2026", "NUMERO_CARTA_VRIN": "801-2026", "FECHA_CARTA_DOCENTE": "14/10/2026", "REGISTRO_MESA_PARTES": "1475", "MONTO_TOTAL_SOLICITADO": "3,200.00"}', 'documentos/12/CARTA_VRIN_1_xjodlsc1.docx', 'documentos/12/CARTA_VRIN_1_xjodlsc1.pdf', 'd6dcbd2cf16ddb95dc40338044560287b114f240c12ddef655d47d248130cd74', 'BPGCV5E0HWJ3', 1, 1, '2026-10-13 16:28:29', '2026-10-13 21:28:29', '19kSdwnNnp2BWRjaE4_DCu1KOFJJU1fE-', '1Ps3pNZnwYAqUUlNGcWrVjuvnPxvXoRO8', '2026-10-30 21:18:58'),
	(29, 14, 'CARTA_VRIN', 1, 1, '{"GRADO": "Ing.", "CIUDAD": "Abancay", "CUARTIL": "Q1", "ESCUELA": "Escuela Profesional de Ingenieria de Minas", "NOMBRES": "Walquer", "REVISTA": "Frontiers", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Ing. Walquer Huacani Calsin", "CARTA_DOCENTE": "CARTA N° 09-2026-FI-WHC-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Analysis of atmospheric air pollutants (CO, NO2, and SO2), through Sentinel-5P images in Google Earth Engine, Apurimac region, period 2020-2023", "APELLIDO_MATERNO": "Calsin", "APELLIDO_PATERNO": "Huacani", "FECHA_ACEPTACION": "30 de julio del 2026", "FECHA_CARTA_VRIN": "14 de octubre del 2026", "NUMERO_CARTA_VRIN": "802-2026", "FECHA_CARTA_DOCENTE": "30/07/2026", "REGISTRO_MESA_PARTES": "158", "MONTO_TOTAL_SOLICITADO": "11,360.00"}', 'documentos/14/CARTA_VRIN_1_wzvx09ci.docx', 'documentos/14/CARTA_VRIN_1_wzvx09ci.pdf', '8752bcd9adb3e298e0ff4862db5e781c44dfd30c346d3e22d6255e2cf9669c2b', '7B9FTJZBAOJB', 1, 1, '2026-10-14 13:21:52', '2026-10-14 18:21:52', '1gV0jLVoIwitBB9MiLVHlpLxdt7tTJ8Zc', '1Ps3pNZnwYAqUUlNGcWrVjuvnPxvXoRO8', '2026-10-14 18:22:00'),
	(30, 14, 'RESOLUCION', 1, 2, '{"META": "017", "anio": 2026, "GRADO": "Ing.", "CIUDAD": "Abancay", "FUENTE": "Recursos Determinados", "CUARTIL": "Q1", "ESCUELA": "Escuela Profesional de Ingenieria de Minas", "NOMBRES": "Walquer", "REVISTA": "Frontiers", "CARTA_OPP": "CARTA N° 1710-2026-OPP-UNAMBA", "BASE_DATOS": "Scopus", "ESPECIFICA": "2.3.27.1199", "CARTA_DOCENTE": "CARTA N° 09-2026-FI-WHC-UNAMBA", "fecha_emision": "14 de octubre del 2026", "MONTO_APROBADO": "11,360.00", "FECHA_CARTA_OPP": "3 de agosto del 2026", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Analysis of atmospheric air pollutants (CO, NO2, and SO2), through Sentinel-5P images in Google Earth Engine, Apurimac region, period 2020-2023", "APELLIDO_MATERNO": "Calsin", "APELLIDO_PATERNO": "Huacani", "numero_resolucion": "200", "FECHA_CARTA_DOCENTE": "30 de julio del 2026", "FECHA_REGISTRO_VRIN": "3 de agosto del 2026", "NUMERO_REGISTRO_VRIN": "1602", "MONTO_TOTAL_SOLICITADO": "11,360.00"}', 'documentos/14/RESOLUCION_1_7rrgkab1.docx', NULL, 'ddaf53b67abcffae516c7997a26c288bcfb5b790c2c993691d130f6ad4e43821', '3AHV4TPNBELC', 1, 1, '2026-10-14 13:25:04', '2026-10-14 18:25:04', '130Hmrp_4K7bi76tLTPu-hlOolShNAPj6', '1hw-xlYrDmCvKFTL6tDC9aYCeikzBeA--', '2026-10-14 18:25:11'),
	(31, 13, 'CARTA_VRIN', 1, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Agroindustrial", "NOMBRES": "Guadalupe", "REVISTA": "International Journal of Agriculture and Biosciences", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Guadalupe Chaquilla Quilca", "CARTA_DOCENTE": "CARTA N° N°10-2026-GCHQ-EAPIA-FI-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Morphology and organic compounds in \'Cacho de Toro\' potato: a comparative study between aeroponics and soil", "APELLIDO_MATERNO": "Quilca", "APELLIDO_PATERNO": "Chaquilla", "FECHA_ACEPTACION": "4 de febrero del 2026", "FECHA_CARTA_VRIN": "14 de octubre del 2026", "NUMERO_CARTA_VRIN": "803-2026", "FECHA_CARTA_DOCENTE": "04/02/2026", "REGISTRO_MESA_PARTES": "223", "MONTO_TOTAL_SOLICITADO": "3,356.46"}', 'documentos/13/CARTA_VRIN_1_eh3delkk.docx', 'documentos/13/CARTA_VRIN_1_eh3delkk.pdf', '28d37c03d2758effb746464702e5cf67b36e285f4dea21b9ef2691bc1a889e00', 'IRU9MK2PVFYF', 1, 1, '2026-10-14 13:27:22', '2026-10-14 18:27:22', '1J9WrIU3gKqcFVpqFqmoJhm68gDGEIlOc', '1Ps3pNZnwYAqUUlNGcWrVjuvnPxvXoRO8', '2026-10-14 18:28:19'),
	(32, 15, 'CARTA_VRIN', 1, 1, '{"GRADO": "Ing.", "CIUDAD": "Abancay", "CUARTIL": "Q1", "ESCUELA": "Escuela Profesional de Ingenieria de Minas", "NOMBRES": "Walquer", "REVISTA": "Frontiers", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Ing. Walquer Huacani Calsin", "CARTA_DOCENTE": "CARTA N° 09-2026-FI-WHC-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Analysis of atmospheric air pollutants (CO, NO2, and SO2), through Sentinel-5P images in Google Earth Engine, Apurimac region, period 2020-2023", "APELLIDO_MATERNO": "Calsin", "APELLIDO_PATERNO": "Huacani", "FECHA_ACEPTACION": "30 de julio del 2026", "FECHA_CARTA_VRIN": "14 de octubre del 2026", "NUMERO_CARTA_VRIN": "804-2026", "FECHA_CARTA_DOCENTE": "30/07/2026", "REGISTRO_MESA_PARTES": "158", "MONTO_TOTAL_SOLICITADO": "11,360.00"}', 'documentos/15/CARTA_VRIN_1_qj1pawiw.docx', 'documentos/15/CARTA_VRIN_1_qj1pawiw.pdf', 'c6077ecba2aee3f286736421de4f9ab8d7d825e13598228e858b63343bd00f1a', '5C1G8TFZ6GQO', 1, 1, '2026-10-14 13:54:49', '2026-10-14 18:54:49', '1_N4Anhqw3V5Z6eOpL__owj84ENb9uZOu', '1Ps3pNZnwYAqUUlNGcWrVjuvnPxvXoRO8', '2026-10-14 18:55:07'),
	(33, 15, 'RESOLUCION', 1, 2, '{"META": "017", "anio": 2026, "GRADO": "Ing.", "CIUDAD": "Abancay", "FUENTE": "Recursos Determinados", "CUARTIL": "Q1", "ESCUELA": "Escuela Profesional de Ingenieria de Minas", "NOMBRES": "Walquer", "REVISTA": "Frontiers", "CARTA_OPP": "CARTA N° 1710-2026-OPP-UNAMBA", "BASE_DATOS": "Scopus", "ESPECIFICA": "2.3.27.1199", "CARTA_DOCENTE": "CARTA N° 09-2026-FI-WHC-UNAMBA", "fecha_emision": "14 de octubre del 2026", "MONTO_APROBADO": "11,360.00", "FECHA_CARTA_OPP": "3 de agosto del 2026", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Analysis of atmospheric air pollutants (CO, NO2, and SO2), through Sentinel-5P images in Google Earth Engine, Apurimac region, period 2020-2023", "APELLIDO_MATERNO": "Calsin", "APELLIDO_PATERNO": "Huacani", "numero_resolucion": "201", "FECHA_CARTA_DOCENTE": "30 de julio del 2026", "FECHA_REGISTRO_VRIN": "3 de agosto del 2026", "NUMERO_REGISTRO_VRIN": "1602", "MONTO_TOTAL_SOLICITADO": "11,360.00"}', 'documentos/15/RESOLUCION_1_7lc5xgsn.docx', 'documentos/15/RESOLUCION_1_7lc5xgsn.pdf', '7c6549ff637d09e80f50aefb6e5c1195149d621ed57ea4da82ba376ded6caddb', 'FI3YKOUCNQPR', 1, 1, '2026-10-14 13:59:24', '2026-10-14 18:59:24', '1qKUy89BRv5G27lDfe1vVmqG0QTvbxTS1', '1hw-xlYrDmCvKFTL6tDC9aYCeikzBeA--', '2026-10-14 18:59:45'),
	(34, 18, 'CARTA_VRIN', 1, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Agroindustrial", "NOMBRES": "Guadalupe", "REVISTA": "International Journal of Agriculture and Biosciences", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Guadalupe Chaquilla Quilca", "CARTA_DOCENTE": "CARTA N° N°10-2026-GCHQ-EAPIA-FI-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Morphology and organic compounds in \'Cacho de Toro\' potato: a comparative study between aeroponics and soil", "APELLIDO_MATERNO": "Quilca", "APELLIDO_PATERNO": "Chaquilla", "FECHA_ACEPTACION": "4 de febrero del 2026", "FECHA_CARTA_VRIN": "20 de octubre del 2026", "NUMERO_CARTA_VRIN": "092-2026", "FECHA_CARTA_DOCENTE": "04/02/2026", "REGISTRO_MESA_PARTES": "223", "MONTO_TOTAL_SOLICITADO": "3,356.46"}', 'documentos/18/CARTA_VRIN_1_vpkvcn49.docx', 'documentos/18/CARTA_VRIN_1_vpkvcn49.pdf', '273d77d9284f9e8b2d4ec0568e9c1632aa3e20ec5eace0ba8dcf352aff1bfdcf', 'TCLRHLRYQ29G', 1, 1, '2026-10-20 14:46:11', '2026-10-20 19:46:11', '1pOjgOxE4v4zibXOkiZ-aRn4ln9UV8bfu', '1Ps3pNZnwYAqUUlNGcWrVjuvnPxvXoRO8', '2026-10-20 19:46:20'),
	(35, 18, 'RESOLUCION', 1, 2, '{"META": "017", "anio": 2026, "GRADO": "Dra.", "CIUDAD": "Abancay", "FUENTE": "Recursos Determinados", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Agroindustrial", "NOMBRES": "Guadalupe", "REVISTA": "International Journal of Agriculture and Biosciences", "CARTA_OPP": "CARTA N° 0212-2026-OPP-UNAMBA", "BASE_DATOS": "Scopus", "ESPECIFICA": "2.3.27.1199", "CARTA_DOCENTE": "CARTA N° N°10-2026-GCHQ-EAPIA-FI-UNAMBA", "fecha_emision": "20 de octubre del 2026", "MONTO_APROBADO": "3,356.46", "FECHA_CARTA_OPP": "5 de febrero del 2026", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Morphology and organic compounds in \'Cacho de Toro\' potato: a comparative study between aeroponics and soil", "APELLIDO_MATERNO": "Quilca", "APELLIDO_PATERNO": "Chaquilla", "numero_resolucion": "100", "FECHA_CARTA_DOCENTE": "4 de febrero del 2026", "FECHA_REGISTRO_VRIN": "6 de febrero del 2026", "NUMERO_REGISTRO_VRIN": "247", "MONTO_TOTAL_SOLICITADO": "3,356.46"}', 'documentos/18/RESOLUCION_1_nwf8bkif.docx', 'documentos/18/RESOLUCION_1_nwf8bkif.pdf', '083c6d69615e5ce560dd679df5f702a7c3f136c670758e1ec6f092b4a7e69ed0', 'VBUXDLNNJC4Q', 1, 1, '2026-10-20 14:48:37', '2026-10-20 19:48:37', '11bEWj6xdWjs-zgIVnDkfr1YGCNLkxwtI', '1hw-xlYrDmCvKFTL6tDC9aYCeikzBeA--', '2026-10-20 19:48:58'),
	(36, 19, 'CARTA_VRIN', 1, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Agroindustrial", "NOMBRES": "Guadalupe", "REVISTA": "International Journal of Agriculture and Biosciences", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Guadalupe Chaquilla Quilca", "CARTA_DOCENTE": "CARTA N° N°15-2026-GCHQ-EAPIA-FI-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Morphology and organic compounds in \'Cacho de Toro\' potato: a comparative study between aeroponics and soil", "APELLIDO_MATERNO": "Quilca", "APELLIDO_PATERNO": "Chaquilla", "FECHA_ACEPTACION": "4 de febrero del 2026", "FECHA_CARTA_VRIN": "26 de octubre del 2026", "NUMERO_CARTA_VRIN": "805-2026", "FECHA_CARTA_DOCENTE": "04/02/2026", "REGISTRO_MESA_PARTES": "223", "MONTO_TOTAL_SOLICITADO": "3,356.46"}', 'documentos/19/CARTA_VRIN_1_v1sdvxi3.docx', NULL, 'ac1dea39b5bc6cccdc116241fdec060b9a2b4fee8dd775a83a45b0dbb34bcb5b', 'P8XAGO7ZGMAX', 1, 1, '2026-10-26 21:12:16', '2026-10-27 02:12:16', '1esLdliLHX_idL09SkHEReUbRtW_shSUM', '1Ps3pNZnwYAqUUlNGcWrVjuvnPxvXoRO8', '2026-10-27 02:12:22'),
	(37, 19, 'RESOLUCION', 1, 4, '{"META": "017", "anio": 2026, "GRADO": "Dra.", "CIUDAD": "Abancay", "FUENTE": "Recursos Determinados", "GENERO": "de la", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Agroindustrial", "NOMBRES": "Guadalupe", "REVISTA": "International Journal of Agriculture and Biosciences", "CARTA_OPP": "CARTA N° 0212-2026-OPP-UNAMBA", "BASE_DATOS": "Scopus", "ESPECIFICA": "2.3.27.1199", "CARTA_DOCENTE": "CARTA N° N°15-2026-GCHQ-EAPIA-FI-UNAMBA", "fecha_emision": "26 de octubre del 2026", "MONTO_APROBADO": "3,356.46", "FECHA_CARTA_OPP": "5 de febrero del 2026", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Morphology and organic compounds in \'Cacho de Toro\' potato: a comparative study between aeroponics and soil", "APELLIDO_MATERNO": "Quilca", "APELLIDO_PATERNO": "Chaquilla", "numero_resolucion": "300", "FECHA_CARTA_DOCENTE": "4 de febrero del 2026", "FECHA_REGISTRO_VRIN": "6 de febrero del 2026", "NUMERO_REGISTRO_VRIN": "247", "MONTO_TOTAL_SOLICITADO": "3,356.46", "CARTA_ESPECIALISTA_INVESTIGACION": "CARTA N° 0245-2026-FLC-UNAMBA", "FECHA_CARTA_ESPECIALISTA_INVESTIGACION": "21 de octubre del 2026"}', 'documentos/19/RESOLUCION_1_dddeaac3.docx', NULL, '684a0c235764750b10ff58e717115a27ff395f936552dfb897ab11f13f9fe534', 'ONJBCXUU7VDT', 1, 1, '2026-10-26 21:47:36', '2026-10-27 02:47:36', '1CkTp4VKT46zcvWlA3_8LvIF3j9QEKgs6', '1hw-xlYrDmCvKFTL6tDC9aYCeikzBeA--', '2026-10-27 02:47:50'),
	(38, 20, 'CARTA_VRIN', 1, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Agroindustrial", "NOMBRES": "Guadalupe", "REVISTA": "CyTA Journal of Food", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Guadalupe Chaquilla Quilca", "CARTA_DOCENTE": "CARTA N° 29-2026-GCHQ-EAPIA-FI-UNAMBA", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Effect of processing and germination on the nutritional and bioactive properties of black amaranth (Amaranthus hybridus) cultivated at 2700 m above sea level in Apurimac", "APELLIDO_MATERNO": "Quilca", "APELLIDO_PATERNO": "Chaquilla", "FECHA_ACEPTACION": "22 de julio del 2026", "FECHA_CARTA_VRIN": "29 de octubre del 2026", "NUMERO_CARTA_VRIN": "806-2026", "FECHA_CARTA_DOCENTE": "22/07/2026", "REGISTRO_MESA_PARTES": "1555", "MONTO_TOTAL_SOLICITADO": "9,883.80"}', 'documentos/20/CARTA_VRIN_1_f9ybdx4b.docx', 'documentos/20/CARTA_VRIN_1_f9ybdx4b.pdf', 'b9a3d2da714cfe38dd5267408ea3e8bae1ec56ff8d99b52132338c00909149d5', 'NYAQ0HHAYMNE', 1, 1, '2026-10-29 19:42:32', '2026-10-30 00:42:32', '15__Ecb_LyA4PYltOj66H1a_pUFIWgem4', '1Ps3pNZnwYAqUUlNGcWrVjuvnPxvXoRO8', '2026-10-30 00:43:23'),
	(39, 24, 'CARTA_VRIN', 1, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Agroindustrial", "NOMBRES": "Guadalupe", "REVISTA": "International Journal of Agriculture and Biosciences", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Guadalupe Chaquilla Quilca", "CARTA_DOCENTE": "CARTA N° 12-2026-GCHQ-EAPIA-FI-UNAMBА", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Morphology and organic compounds in \'Cacho de Toro\' potato: a comparative study between aeroponics and soil", "APELLIDO_MATERNO": "Quilca", "APELLIDO_PATERNO": "Chaquilla", "FECHA_ACEPTACION": "4 de febrero del 2026", "FECHA_CARTA_VRIN": "29 de octubre del 2026", "NUMERO_CARTA_VRIN": "807-2026", "FECHA_CARTA_DOCENTE": "04/02/2026", "REGISTRO_MESA_PARTES": "229", "MONTO_TOTAL_SOLICITADO": "3,356.46"}', 'documentos/24/CARTA_VRIN_1_5qyisyve.docx', 'documentos/24/CARTA_VRIN_1_5qyisyve.pdf', 'b58139b1a3a7b75731a212734138d0ec018e5365eaa1454341599dead7ec4589', 'FKANASAUROOQ', 0, 1, '2026-10-29 19:47:05', '2026-10-30 00:47:05', NULL, NULL, NULL),
	(40, 24, 'CARTA_VRIN', 2, 1, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Agroindustrial", "NOMBRES": "Guadalupe", "REVISTA": "International Journal of Agriculture and Biosciences", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Dra. Guadalupe Chaquilla Quilca", "CARTA_DOCENTE": "CARTA N° 12-2026-GCHQ-EAPIA-FI-UNAMBА", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Morphology and organic compounds in \'Cacho de Toro\' potato: a comparative study between aeroponics and soil", "APELLIDO_MATERNO": "Quilca", "APELLIDO_PATERNO": "Chaquilla", "FECHA_ACEPTACION": "4 de febrero del 2026", "FECHA_CARTA_VRIN": "30 de octubre del 2026", "NUMERO_CARTA_VRIN": "030-2026", "FECHA_CARTA_DOCENTE": "04/02/2026", "REGISTRO_MESA_PARTES": "229", "MONTO_TOTAL_SOLICITADO": "3,356.46"}', 'documentos/24/CARTA_VRIN_2_yfyqn0sy.docx', 'documentos/24/CARTA_VRIN_2_yfyqn0sy.pdf', '608de1e4d70cf9194a45bda0ad8d8f8f70b6575094860aafac5170a39217bb53', 'PEAEPYW9IJTQ', 1, 1, '2026-10-29 19:48:21', '2026-10-30 00:48:21', '1tEgTTjRx5TxG3CfrNokF531mCE__N_Jc', '1Ps3pNZnwYAqUUlNGcWrVjuvnPxvXoRO8', '2026-10-30 00:48:53'),
	(41, 22, 'CARTA_VRIN', 1, 1, '{"GRADO": "Ing.", "CIUDAD": "Abancay", "CUARTIL": "Q1", "ESCUELA": "Ingeniería de Minas", "NOMBRES": "Walquer", "REVISTA": "Frontiers", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Ing. Walquer Huacani Calsin", "CARTA_DOCENTE": "CARTA N° 010-2026-FI-WHC-UNAMBА", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Analysis of atmospheric air pollutants (CO, NO2, and SO2), through Sentinel-5P images in Google Earth Engine, Apurimac region, period 2020- 2023", "APELLIDO_MATERNO": "Calsin", "APELLIDO_PATERNO": "Huacani", "FECHA_ACEPTACION": "30 de julio del 2026", "FECHA_CARTA_VRIN": "29 de octubre del 2026", "NUMERO_CARTA_VRIN": "090-2026", "FECHA_CARTA_DOCENTE": "30/07/2026", "REGISTRO_MESA_PARTES": "1580", "MONTO_TOTAL_SOLICITADO": "11,360.00"}', 'documentos/22/CARTA_VRIN_1_jas65jii.docx', 'documentos/22/CARTA_VRIN_1_jas65jii.pdf', 'f0488124abe58748de570a50a4908a5e44a6dfabf5d7426914c9951797bcf8fd', '7L72AVPDA7WW', 1, 1, '2026-10-29 19:50:13', '2026-10-30 00:50:13', NULL, NULL, NULL),
	(42, 24, 'RESOLUCION', 1, 4, '{"META": "017", "anio": 2026, "GRADO": "Dra.", "CIUDAD": "Abancay", "FUENTE": "Recursos Determinados", "GENERO": "de la", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Agroindustrial", "NOMBRES": "Guadalupe", "REVISTA": "International Journal of Agriculture and Biosciences", "CARTA_OPP": "0212-2026-OPP-UNAMBA", "BASE_DATOS": "Scopus", "ESPECIFICA": "2.3.27.1199", "CARTA_DOCENTE": "CARTA N° 12-2026-GCHQ-EAPIA-FI-UNAMBА", "fecha_emision": "29 de octubre del 2026", "MONTO_APROBADO": "3,356.46", "FECHA_CARTA_OPP": "5 de febrero del 2026", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Morphology and organic compounds in \'Cacho de Toro\' potato: a comparative study between aeroponics and soil", "APELLIDO_MATERNO": "Quilca", "APELLIDO_PATERNO": "Chaquilla", "numero_resolucion": "400", "FECHA_CARTA_DOCENTE": "4 de febrero del 2026", "FECHA_REGISTRO_VRIN": "6 de febrero del 2026", "NUMERO_REGISTRO_VRIN": "247", "MONTO_TOTAL_SOLICITADO": "3,356.46", "CARTA_ESPECIALISTA_INVESTIGACION": "0314-2026-FCL-UNAMBA", "FECHA_CARTA_ESPECIALISTA_INVESTIGACION": "27 de febrero del 2026"}', 'documentos/24/RESOLUCION_1_bkg4eqfy.docx', 'documentos/24/RESOLUCION_1_bkg4eqfy.pdf', 'c401e91f7dbe5d7a87ef24f4967ec61a818c16f1204d38fe7c2040d0e14e14e4', '6MJ8XRW4YUCE', 1, 1, '2026-10-29 19:59:43', '2026-10-30 00:59:43', '1_SCv8dDZAKXCQ2KXF3oEsUMYy9LRlZvU', '1hw-xlYrDmCvKFTL6tDC9aYCeikzBeA--', '2026-10-30 00:59:49'),
	(43, 22, 'RESOLUCION', 1, 4, '{"META": "017", "anio": 2026, "GRADO": "Ing.", "CIUDAD": "Abancay", "FUENTE": "Recursos Determinados", "GENERO": "del", "CUARTIL": "Q1", "ESCUELA": "Ingeniería de Minas", "NOMBRES": "Walquer", "REVISTA": "Frontiers", "CARTA_OPP": "CARTA N° 1710-2026-OPP-UNAMBA", "BASE_DATOS": "Scopus", "ESPECIFICA": "2.3.27.1199", "CARTA_DOCENTE": "CARTA N° 010-2026-FI-WHC-UNAMBА", "fecha_emision": "29 de octubre del 2026", "MONTO_APROBADO": "11,360.00", "FECHA_CARTA_OPP": "3 de agosto del 2026", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Analysis of atmospheric air pollutants (CO, NO2, and SO2), through Sentinel-5P images in Google Earth Engine, Apurimac region, period 2020- 2023", "APELLIDO_MATERNO": "Calsin", "APELLIDO_PATERNO": "Huacani", "numero_resolucion": "401", "FECHA_CARTA_DOCENTE": "30 de julio del 2026", "FECHA_REGISTRO_VRIN": "3 de agosto del 2026", "NUMERO_REGISTRO_VRIN": "1602", "MONTO_TOTAL_SOLICITADO": "11,360.00", "CARTA_ESPECIALISTA_INVESTIGACION": "0315-2026-FCL-UNAMBA", "FECHA_CARTA_ESPECIALISTA_INVESTIGACION": "16 de julio del 2026"}', 'documentos/22/RESOLUCION_1_wn0sblio.docx', NULL, 'c3b3f5ac1c96bc3064e48e5d250db919328d0497162ee6c6a42432a297c99bbb', 'RL6MR6BGEV8O', 1, 1, '2026-10-29 20:37:45', '2026-10-30 01:37:45', '1HphZRq1gkXzNEmktRoUffWYA1zrFrH_C', '1hw-xlYrDmCvKFTL6tDC9aYCeikzBeA--', '2026-10-30 01:37:50'),
	(44, 20, 'RESOLUCION', 1, 4, '{"META": "017", "anio": 2026, "GRADO": "Dra.", "CIUDAD": "Abancay", "FUENTE": "Recursos Determinados", "GENERO": "de la", "CUARTIL": "Q2", "ESCUELA": "Ingeniería Agroindustrial", "NOMBRES": "Guadalupe", "REVISTA": "CyTA Journal of Food", "CARTA_OPP": "1688-2026-OPP-UNAMBA", "BASE_DATOS": "Scopus", "ESPECIFICA": "2.3.27.1199", "CARTA_DOCENTE": "CARTA N° 29-2026-GCHQ-EAPIA-FI-UNAMBA", "fecha_emision": "29 de octubre del 2026", "MONTO_APROBADO": "9,883.80", "FECHA_CARTA_OPP": "30 de julio del 2026", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Effect of processing and germination on the nutritional and bioactive properties of black amaranth (Amaranthus hybridus) cultivated at 2700 m above sea level in Apurimac", "APELLIDO_MATERNO": "Quilca", "APELLIDO_PATERNO": "Chaquilla", "numero_resolucion": "500", "FECHA_CARTA_DOCENTE": "22 de julio del 2026", "FECHA_REGISTRO_VRIN": "31 de julio del 2026", "NUMERO_REGISTRO_VRIN": "1581", "MONTO_TOTAL_SOLICITADO": "9,883.80", "CARTA_ESPECIALISTA_INVESTIGACION": "0314-2026-FCL-UNAMBA", "FECHA_CARTA_ESPECIALISTA_INVESTIGACION": "30 de junio del 2026"}', 'documentos/20/RESOLUCION_1_2pd1we49.docx', NULL, '5fd7a4fad6ce1b6c3122959bb537af91cf235283f2ad53edc18aaee0924ea675', 'CH8NYCLFGE04', 1, 1, '2026-10-29 20:40:36', '2026-10-30 01:40:36', NULL, NULL, NULL),
	(45, 21, 'CARTA_VRIN', 1, 1, '{"GRADO": "Ing.", "CIUDAD": "Abancay", "CUARTIL": "Q1", "ESCUELA": "Ingeniería de Minas", "NOMBRES": "Walquer", "REVISTA": "Frontiers", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Ing. Walquer Huacani Calsin", "CARTA_DOCENTE": "CARTA N° 09-2026-FI-WHC-UNAMBА", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Analysis of atmospheric air pollutants (CO, NO2, and SO2), through Sentinel-5P images in Google Earth Engine, Apurimac region, period 2020- 2023", "APELLIDO_MATERNO": "Calsin", "APELLIDO_PATERNO": "Huacani", "FECHA_ACEPTACION": "30 de julio del 2026", "FECHA_CARTA_VRIN": "29 de octubre del 2026", "NUMERO_CARTA_VRIN": "807-2026", "FECHA_CARTA_DOCENTE": "30/07/2026", "REGISTRO_MESA_PARTES": "1580", "MONTO_TOTAL_SOLICITADO": "1,360.00"}', 'documentos/21/CARTA_VRIN_1_77zna1zr.docx', 'documentos/21/CARTA_VRIN_1_77zna1zr.pdf', '08ac48d2832d4e6393d63a9cb5dab5f60820bd2bca42a8655846c475c882c4d3', 'CFSGKLQUVAAA', 1, 1, '2026-10-29 20:42:49', '2026-10-30 01:42:49', NULL, NULL, NULL),
	(46, 21, 'RESOLUCION', 1, 4, '{"META": "017", "anio": 2026, "GRADO": "Ing.", "CIUDAD": "Abancay", "FUENTE": "Recursos Determinados", "GENERO": "del", "CUARTIL": "Q1", "ESCUELA": "Ingeniería de Minas", "NOMBRES": "Walquer", "REVISTA": "Frontiers", "CARTA_OPP": "1710-2026-OPP-UNAMBA", "BASE_DATOS": "Scopus", "ESPECIFICA": "2.3.27.1199", "CARTA_DOCENTE": "CARTA N° 09-2026-FI-WHC-UNAMBА", "fecha_emision": "30 de octubre del 2026", "MONTO_APROBADO": "11,360.00", "FECHA_CARTA_OPP": "3 de agosto del 2026", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Analysis of atmospheric air pollutants (CO, NO2, and SO2), through Sentinel-5P images in Google Earth Engine, Apurimac region, period 2020- 2023", "APELLIDO_MATERNO": "Calsin", "APELLIDO_PATERNO": "Huacani", "numero_resolucion": "020", "FECHA_CARTA_DOCENTE": "30 de julio del 2026", "FECHA_REGISTRO_VRIN": "3 de agosto del 2026", "NUMERO_REGISTRO_VRIN": "1602", "MONTO_TOTAL_SOLICITADO": "1,360.00", "CARTA_ESPECIALISTA_INVESTIGACION": "0314-2026-FCL-UNAMBA", "FECHA_CARTA_ESPECIALISTA_INVESTIGACION": "1 de julio del 2026"}', 'documentos/21/RESOLUCION_1_1nmzdiqz.docx', 'documentos/21/RESOLUCION_1_1nmzdiqz.pdf', '0883b9af9c45c2c6ce52a61b8e1f296a3ab8d29c49a04b6b5b4f04a903d49f20', 'Z4KE8LR1JBUR', 0, 1, '2026-10-30 14:48:22', '2026-10-30 19:48:22', NULL, NULL, NULL),
	(47, 21, 'RESOLUCION', 2, 4, '{"META": "017", "anio": 2026, "GRADO": "Ing.", "CIUDAD": "Abancay", "FUENTE": "Recursos Determinados", "GENERO": "del", "CUARTIL": "Q1", "ESCUELA": "Ingeniería de Minas", "NOMBRES": "Walquer", "REVISTA": "Frontiers", "CARTA_OPP": "1710-2026-OPP-UNAMBA", "BASE_DATOS": "Scopus", "ESPECIFICA": "2.3.27.1199", "CARTA_DOCENTE": "CARTA N° 09-2026-FI-WHC-UNAMBА", "fecha_emision": "30 de octubre del 2026", "MONTO_APROBADO": "11,360.00", "FECHA_CARTA_OPP": "3 de agosto del 2026", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Analysis of atmospheric air pollutants (CO, NO2, and SO2), through Sentinel-5P images in Google Earth Engine, Apurimac region, period 2020- 2023", "APELLIDO_MATERNO": "Calsin", "APELLIDO_PATERNO": "Huacani", "numero_resolucion": "020", "FECHA_CARTA_DOCENTE": "30 de julio del 2026", "FECHA_REGISTRO_VRIN": "3 de agosto del 2026", "NUMERO_REGISTRO_VRIN": "1602", "MONTO_TOTAL_SOLICITADO": "1,360.00", "CARTA_ESPECIALISTA_INVESTIGACION": "0314-2026-FCL-UNAMBA", "FECHA_CARTA_ESPECIALISTA_INVESTIGACION": "2 de julio del 2026"}', 'documentos/21/RESOLUCION_2_05mzeo5y.docx', NULL, '01576370f24c7edacba4ceb6e12a06eacbb48961645aad5b20ce11b4a2d078ab', 'T9BUX5NM1GCL', 1, 1, '2026-10-30 14:49:13', '2026-10-30 19:49:13', '16KLvBAXQxhfgcS2UDGSkh-M5VN_ZSMFE', '1hw-xlYrDmCvKFTL6tDC9aYCeikzBeA--', '2026-10-30 19:49:21'),
	(48, 25, 'CARTA_VRIN', 1, 7, '{"GRADO": "M.Sc.", "CIUDAD": "Abancay", "GENERO": "del", "CUARTIL": "Q1", "ESCUELA": "Medicina Veterinaria y Zootécnia", "NOMBRES": "Delmer", "REVISTA": "Frontiers in Insect Science", "BASE_DATOS": "Web of Science", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente M.Sc. Delmer Zea Gonzales", "CARTA_DOCENTE": "CARTA N° 017 - 2026- CP.MVZ – UNAМВА – АBANCAY/D.DZG", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Characterization and Typology of Beekeeping Systems: A Case Study in the District of Tamhurco Anurímac, Peru", "APELLIDO_MATERNO": "Gonzales", "APELLIDO_PATERNO": "Zea", "FECHA_ACEPTACION": "14 de julio del 2026", "FECHA_CARTA_VRIN": "30 de octubre del 2026", "NUMERO_CARTA_VRIN": "808-2026", "FECHA_CARTA_DOCENTE": "14/07/2026", "REGISTRO_MESA_PARTES": "1475", "MONTO_TOTAL_SOLICITADO": "11,420.00"}', 'documentos/25/CARTA_VRIN_1_0hs6nkhu.docx', 'documentos/25/CARTA_VRIN_1_0hs6nkhu.pdf', '2e96a69847fd11820da777287853dafb925ede79d6e481aaf1374cc7f0362f16', '1XA9JILHOR76', 1, 1, '2026-10-30 17:19:51', '2026-10-30 22:19:51', NULL, NULL, NULL),
	(49, 26, 'CARTA_VRIN', 1, 7, '{"GRADO": "Dra.", "CIUDAD": "Abancay", "GENERO": "de la", "CUARTIL": "Q1", "ESCUELA": "Ingeniería Informática y Sistemas", "NOMBRES": "Hesmeralda", "REVISTA": "Frontiers", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para la docente Dra. Hesmeralda Rojas Enriquez", "CARTA_DOCENTE": "CARTA N° 35-2026-FI-HRE-UNAMВА", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Hydrochemical characterization of hot springs in Ollachea, Puno, according to Peruvian enyironmental standards", "APELLIDO_MATERNO": "Enriquez", "APELLIDO_PATERNO": "Rojas", "FECHA_ACEPTACION": "2 de junio del 2026", "FECHA_CARTA_VRIN": "30 de octubre del 2026", "NUMERO_CARTA_VRIN": "809-2026", "FECHA_CARTA_DOCENTE": "02/06/2026", "REGISTRO_MESA_PARTES": "1114", "MONTO_TOTAL_SOLICITADO": "1,175.00"}', 'documentos/26/CARTA_VRIN_1_eywycxfs.docx', NULL, '32a1c188e5a23127df28582cb61e177e7753e7561a0deb01d8abea562da53148', '8P35HZJMAXAO', 1, 1, '2026-10-30 17:34:40', '2026-10-30 22:34:40', NULL, NULL, NULL),
	(50, 27, 'CARTA_VRIN', 1, 7, '{"GRADO": "Ing.", "CIUDAD": "Abancay", "GENERO": "del", "CUARTIL": "Q1", "ESCUELA": "Ingeniería de Minas", "NOMBRES": "Walquer", "REVISTA": "Frontiers", "BASE_DATOS": "Scopus", "ASUNTO_CARTA": "Solicito financiamiento para publicación en revista indexada para el docente Ing. Walquer Huacani Calsin", "CARTA_DOCENTE": "CARTA N° 03-2026-FI-WHC-UNAMBА", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Analysis of atmospheric air pollutants (CO, NO2, and SO2), through Sentinel-5P images in Google Earth Engine, Apurimac region, period 2020- 2023", "APELLIDO_MATERNO": "Calsin", "APELLIDO_PATERNO": "Huacani", "FECHA_ACEPTACION": "30 de julio del 2026", "FECHA_CARTA_VRIN": "30 de octubre del 2026", "NUMERO_CARTA_VRIN": "812-2026", "FECHA_CARTA_DOCENTE": "30/07/2026", "REGISTRO_MESA_PARTES": "1580", "MONTO_TOTAL_SOLICITADO": "11,360.00"}', 'documentos/27/CARTA_VRIN_1_ah2tk5rm.docx', 'documentos/27/CARTA_VRIN_1_ah2tk5rm.pdf', 'faf73280ea3532de337952f9ad7d6c3d2be4f5b86667ae850eee0f6ffc86448f', 'WIHO69V5CKAX', 1, 1, '2026-10-30 17:45:42', '2026-10-30 22:45:42', NULL, NULL, NULL),
	(51, 27, 'RESOLUCION', 1, 4, '{"META": "017", "anio": 2026, "GRADO": "Ing.", "CIUDAD": "Abancay", "FUENTE": "Recursos Determinados", "GENERO": "del", "CUARTIL": "Q1", "ESCUELA": "Ingeniería de Minas", "NOMBRES": "Walquer", "REVISTA": "Frontiers", "CARTA_OPP": "1710-2026-OPP-UNAMBA", "BASE_DATOS": "Scopus", "ESPECIFICA": "2.3.11.07", "CARTA_DOCENTE": "CARTA N° 03-2026-FI-WHC-UNAMBА", "fecha_emision": "30 de octubre del 2026", "MONTO_APROBADO": "11,360.00", "FECHA_CARTA_OPP": "1 de octubre del 2026", "REGLAMENTO_BASE": "Resolución N° 411-2026-R-UNAMBA", "TITULO_ARTICULO": "Analysis of atmospheric air pollutants (CO, NO2, and SO2), through Sentinel-5P images in Google Earth Engine, Apurimac region, period 2020- 2023", "APELLIDO_MATERNO": "Calsin", "APELLIDO_PATERNO": "Huacani", "numero_resolucion": "501", "FECHA_CARTA_DOCENTE": "30 de julio del 2026", "FECHA_REGISTRO_VRIN": "7 de octubre del 2026", "NUMERO_REGISTRO_VRIN": "1234", "MONTO_TOTAL_SOLICITADO": "11,360.00", "CARTA_ESPECIALISTA_INVESTIGACION": "0314-2026-FCL-UNAMBA", "FECHA_CARTA_ESPECIALISTA_INVESTIGACION": "7 de octubre del 2026"}', 'documentos/27/RESOLUCION_1_dutmqaps.docx', 'documentos/27/RESOLUCION_1_dutmqaps.pdf', 'cba4ee75c1cf3e9eb8ab01735577df407f450c9987dcc040a45e57d64202e889', 'FTRTHG0R78RB', 1, 1, '2026-10-30 20:12:19', '2026-10-31 01:12:19', '1FExUJg-4O5k2DqnS-AkdF649VapOO1jF', '1hw-xlYrDmCvKFTL6tDC9aYCeikzBeA--', '2026-10-31 01:12:24');

-- Volcando estructura para tabla sgr_vrin.escuelas
CREATE TABLE IF NOT EXISTS `escuelas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `facultad_id` bigint unsigned NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_escuelas` (`facultad_id`,`nombre`),
  CONSTRAINT `fk_escuelas_facultad` FOREIGN KEY (`facultad_id`) REFERENCES `facultades` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla sgr_vrin.escuelas: ~8 rows (aproximadamente)
INSERT INTO `escuelas` (`id`, `facultad_id`, `nombre`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 1, 'Medicina Veterinaria y Zootecnia', 1, NULL, NULL),
	(2, 3, 'Ingeniería Civil', 1, '2026-10-02 14:09:10', '2026-10-02 14:09:10'),
	(3, 3, 'Ingeniería Agroecológica y Desarrollo Rural', 1, '2026-10-02 14:09:10', '2026-10-02 14:09:10'),
	(4, 3, 'Ingeniería Agroindustrial', 1, '2026-10-02 14:09:10', '2026-10-02 14:09:10'),
	(5, 3, 'Ingeniería de Minas', 1, '2026-10-02 14:09:10', '2026-10-02 14:09:10'),
	(6, 3, 'Ingeniería Informática y Sistemas', 1, '2026-10-02 14:09:10', '2026-10-02 14:09:10'),
	(7, 2, 'Medicina Veterinaria y Zootécnia', 1, '2026-10-02 14:13:14', '2026-10-02 14:13:14'),
	(8, 4, 'Administración', 1, '2026-10-02 14:13:14', '2026-10-02 14:13:14'),
	(9, 3, 'Escuela Profesional de Ingenieria de Minas', 1, '2026-10-14 18:17:00', '2026-10-14 18:17:00');

-- Volcando estructura para tabla sgr_vrin.expedientes
CREATE TABLE IF NOT EXISTS `expedientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modulo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ARTICULOS',
  `docente_id` bigint unsigned NOT NULL,
  `grado` enum('Dr.','Dra.','Mg.','M.Sc.','Ph.D.','CPC','Ing.','Lic.','Abog.','Otro') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_contrato` enum('NOMBRADO','CONTRATADO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `escuela_id` bigint unsigned NOT NULL,
  `carta_docente_numero` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `carta_docente_registro_numero` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `carta_docente_registro_fecha` date DEFAULT NULL,
  `carta_docente_fecha` date NOT NULL,
  `registro_mp_numero` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expediente_vrin_numero` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `documentos_completos` tinyint(1) NOT NULL DEFAULT '0',
  `estado` enum('OBSERVADO','EN_REVISION_CALIDAD','VALIDADO_CALIDAD','NO_CUMPLE','EN_ESPERA_OPP','SIN_DISPONIBILIDAD','DISPONIBILIDAD_CONFIRMADA','RESOLUCION_EMITIDA','POR_RENDIR','RENDICION_VENCIDA','RENDIDO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OBSERVADO',
  `etapa_actual` tinyint unsigned NOT NULL DEFAULT '1',
  `cerrado_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `resolucion_borrador` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_expedientes_codigo` (`codigo`),
  KEY `ix_expedientes_estado` (`modulo`,`estado`),
  KEY `ix_expedientes_docente` (`docente_id`,`estado`),
  KEY `ix_expedientes_fecha` (`created_at`),
  KEY `fk_exp_escuela` (`escuela_id`),
  KEY `fk_exp_creador` (`created_by`),
  KEY `fk_exp_editor` (`updated_by`),
  CONSTRAINT `fk_exp_creador` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_exp_docente` FOREIGN KEY (`docente_id`) REFERENCES `docentes` (`id`),
  CONSTRAINT `fk_exp_editor` FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_exp_escuela` FOREIGN KEY (`escuela_id`) REFERENCES `escuelas` (`id`),
  CONSTRAINT `ck_exp_etapa` CHECK ((`etapa_actual` between 1 and 4))
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cabecera del trámite; solo el workflow del backend cambia `estado`';

-- Volcando datos para la tabla sgr_vrin.expedientes: ~27 rows (aproximadamente)
INSERT INTO `expedientes` (`id`, `codigo`, `modulo`, `docente_id`, `grado`, `tipo_contrato`, `escuela_id`, `carta_docente_numero`, `carta_docente_registro_numero`, `carta_docente_registro_fecha`, `carta_docente_fecha`, `registro_mp_numero`, `expediente_vrin_numero`, `documentos_completos`, `estado`, `etapa_actual`, `cerrado_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`, `resolucion_borrador`) VALUES
	(1, 'ART-2026-000001', 'ARTICULOS', 1, 'Dr.', 'NOMBRADO', 2, '017-2026-CP-MVZ-UNAMBA-ABANCAY-LH', NULL, NULL, '2026-10-16', '1546', NULL, 1, 'RESOLUCION_EMITIDA', 3, NULL, 1, NULL, '2026-10-02 19:42:36', '2026-10-06 18:21:55', '2026-10-06 18:21:55', NULL),
	(2, 'ART-2026-000002', 'ARTICULOS', 3, 'Dra.', 'NOMBRADO', 4, '29-2026-GCHQ-EAPIA-FI-UNAMBA', NULL, NULL, '2026-06-22', '1546', NULL, 1, 'RESOLUCION_EMITIDA', 3, NULL, 1, NULL, '2026-10-02 22:27:23', '2026-10-07 22:30:25', '2026-10-07 22:30:25', NULL),
	(3, 'ART-2026-000003', 'ARTICULOS', 4, 'Dra.', 'NOMBRADO', 6, '35-2026-FI-HRE-UNAMBA', NULL, NULL, '2026-06-02', '1546', NULL, 1, 'EN_ESPERA_OPP', 2, NULL, 1, NULL, '2026-10-02 22:46:40', '2026-10-07 19:54:36', '2026-10-07 19:54:36', NULL),
	(4, 'ART-2026-000004', 'ARTICULOS', 4, 'Dra.', 'NOMBRADO', 6, '35-2026-FI-HRE-UNAMBA', NULL, NULL, '2026-06-02', '50597', NULL, 1, 'EN_ESPERA_OPP', 2, NULL, 1, NULL, '2026-10-07 19:57:58', '2026-10-09 21:26:58', '2026-10-09 21:26:58', NULL),
	(5, 'ART-2026-000005', 'ARTICULOS', 3, 'Dra.', 'NOMBRADO', 4, 'N°29-2026-GCHQ-EAPIA-FI-UNAMBA', NULL, NULL, '2026-06-25', '1555-2025', NULL, 1, 'RENDIDO', 4, '2026-10-07 20:58:58', 1, NULL, '2026-10-07 22:39:00', '2026-10-09 21:27:53', '2026-10-09 21:27:53', '{"anio": 2026, "numero": 93, "fecha_emision": "2026-08-03"}'),
	(6, 'ART-2026-000006', 'ARTICULOS', 5, 'M.Sc.', 'NOMBRADO', 7, '017 - 2026- CP.MVZ - UNAMBA - ABANCAY/D.DZG', '1475', '2026-07-14', '2026-07-14', NULL, NULL, 1, 'RENDIDO', 4, '2026-10-09 20:50:05', 1, NULL, '2026-10-09 21:26:50', '2026-10-10 01:50:05', NULL, '{"anio": 2026, "numero": 97, "fecha_emision": "2026-10-09"}'),
	(7, 'ART-2026-000007', 'ARTICULOS', 3, 'Dra.', 'NOMBRADO', 4, '29-2026-GCHQ-EAPIA-FI-UNAMBA', NULL, NULL, '2026-07-22', NULL, NULL, 1, 'VALIDADO_CALIDAD', 1, NULL, 1, NULL, '2026-10-09 21:29:17', '2026-10-10 00:08:51', '2026-10-10 00:08:51', NULL),
	(8, 'ART-2026-000008', 'ARTICULOS', 3, 'Dra.', 'NOMBRADO', 4, 'N°29-2026-GCHQ-EAPIA-FI-UNAMBA', '1555', '2026-07-22', '2026-07-22', NULL, NULL, 1, 'RENDIDO', 4, '2026-10-12 15:39:21', 1, NULL, '2026-10-12 20:26:11', '2026-10-12 20:39:21', NULL, '{"anio": 2026, "numero": 60, "fecha_emision": "2026-10-12"}'),
	(9, 'ART-2026-000009', 'ARTICULOS', 4, 'Dra.', 'NOMBRADO', 6, '35-2026-FI-HRE-UNAMBA', '1114', '2026-06-02', '2026-06-02', NULL, NULL, 1, 'RENDIDO', 4, '2026-10-14 13:45:11', 1, NULL, '2026-10-12 22:49:59', '2026-10-14 18:45:11', NULL, '{"anio": 2026, "numero": 76, "fecha_emision": "2026-10-12"}'),
	(10, 'ART-2026-000010', 'ARTICULOS', 3, 'Dra.', 'NOMBRADO', 4, '29-2026-GCHQ-EAPIA-FI-UNAMBA', '1555', '2026-10-14', '2026-10-13', NULL, NULL, 1, 'EN_REVISION_CALIDAD', 1, NULL, 1, NULL, '2026-10-13 02:01:17', '2026-10-27 02:08:01', '2026-10-27 02:08:01', NULL),
	(11, 'ART-2026-000011', 'ARTICULOS', 4, 'Dra.', 'NOMBRADO', 6, '29-2026-GCHQ-EAPIA-FI-UNAMBA', '1555', '2026-10-20', '2026-10-14', NULL, NULL, 1, 'EN_ESPERA_OPP', 2, NULL, 1, NULL, '2026-10-13 20:27:46', '2026-10-13 20:44:47', NULL, NULL),
	(12, 'ART-2026-000012', 'ARTICULOS', 2, 'Mg.', 'CONTRATADO', 7, '017 - 2026- CP.MVZ - UNAMBA - ABANCAY/D.DZG', '1475', '2026-10-14', '2026-10-14', NULL, NULL, 1, 'EN_ESPERA_OPP', 2, NULL, 1, NULL, '2026-10-13 21:26:52', '2026-10-13 21:28:29', NULL, NULL),
	(13, 'ART-2026-000013', 'ARTICULOS', 3, 'Dra.', 'NOMBRADO', 4, 'N°10-2026-GCHQ-EAPIA-FI-UNAMBA', '223', '2026-02-04', '2026-02-04', NULL, NULL, 1, 'EN_ESPERA_OPP', 2, NULL, 1, NULL, '2026-10-14 18:18:51', '2026-10-20 19:43:13', '2026-10-20 19:43:13', NULL),
	(14, 'ART-2026-000014', 'ARTICULOS', 6, 'Ing.', 'NOMBRADO', 9, '09-2026-FI-WHC-UNAMBA', '158', '2026-07-30', '2026-07-30', NULL, NULL, 1, 'POR_RENDIR', 4, NULL, 1, NULL, '2026-10-14 18:21:02', '2026-10-14 18:26:35', '2026-10-14 18:26:35', '{"anio": 2026, "numero": 200, "fecha_emision": "2026-10-14"}'),
	(15, 'ART-2026-000015', 'ARTICULOS', 6, 'Ing.', 'NOMBRADO', 9, '09-2026-FI-WHC-UNAMBA', '158', '2026-07-30', '2026-07-30', NULL, NULL, 1, 'RENDIDO', 4, '2026-10-14 14:05:08', 1, NULL, '2026-10-14 18:49:30', '2026-10-14 19:05:08', NULL, '{"anio": 2026, "numero": 201, "fecha_emision": "2026-10-14"}'),
	(16, 'ART-2026-000016', 'ARTICULOS', 3, 'Dra.', 'NOMBRADO', 4, '10-2026-GCHQ-EAPIA-FI-UNAMBA', '229', '2026-02-04', '2026-02-04', NULL, NULL, 1, 'VALIDADO_CALIDAD', 1, NULL, 3, NULL, '2026-10-16 18:49:06', '2026-10-20 19:43:01', '2026-10-20 19:43:01', NULL),
	(17, 'ART-2026-000017', 'ARTICULOS', 3, 'Dra.', 'NOMBRADO', 4, 'N°10-2026-GCHQ-EAPIA-FI-UNAMBA', '223', '2026-02-04', '2026-02-04', NULL, NULL, 1, 'EN_REVISION_CALIDAD', 1, NULL, 1, NULL, '2026-10-20 19:42:43', '2026-10-20 19:43:08', '2026-10-20 19:43:08', NULL),
	(18, 'ART-2026-000018', 'ARTICULOS', 3, 'Dra.', 'NOMBRADO', 4, 'N°10-2026-GCHQ-EAPIA-FI-UNAMBA', '223', '2026-02-04', '2026-02-04', NULL, NULL, 1, 'RENDIDO', 4, '2026-10-20 14:50:48', 1, NULL, '2026-10-20 19:44:09', '2026-10-20 19:50:48', NULL, '{"anio": 2026, "numero": 100, "fecha_emision": "2026-10-20"}'),
	(19, 'ART-2026-000019', 'ARTICULOS', 3, 'Dra.', 'NOMBRADO', 4, 'N°15-2026-GCHQ-EAPIA-FI-UNAMBA', '223', '2026-02-04', '2026-02-04', NULL, NULL, 1, 'POR_RENDIR', 4, NULL, 1, NULL, '2026-10-27 02:09:32', '2026-10-30 01:41:07', NULL, '{"anio": 2026, "numero": 300, "fecha_emision": "2026-10-26"}'),
	(20, 'ART-2026-000020', 'ARTICULOS', 3, 'Dra.', 'NOMBRADO', 4, '29-2026-GCHQ-EAPIA-FI-UNAMBA', '1555', '2026-07-22', '2026-07-22', NULL, NULL, 1, 'POR_RENDIR', 4, NULL, 1, NULL, '2026-10-29 22:27:24', '2026-10-30 01:41:27', NULL, '{"anio": 2026, "numero": 500, "fecha_emision": "2026-10-29", "carta_especialista_investigacion": "0314-2026-FCL-UNAMBA", "fecha_carta_especialista_investigacion": "2026-06-30"}'),
	(21, 'ART-2026-000021', 'ARTICULOS', 9, 'Ing.', 'NOMBRADO', 5, '09-2026-FI-WHC-UNAMBА', '1580', '2026-07-30', '2026-07-30', NULL, NULL, 1, 'RENDIDO', 4, '2026-10-30 16:37:46', 1, NULL, '2026-10-29 22:41:46', '2026-10-30 21:37:46', NULL, '{"anio": 2026, "numero": 20, "fecha_emision": "2026-10-30", "carta_especialista_investigacion": "0314-2026-FCL-UNAMBA", "fecha_carta_especialista_investigacion": "2026-07-02"}'),
	(22, 'ART-2026-000022', 'ARTICULOS', 9, 'Ing.', 'NOMBRADO', 5, '010-2026-FI-WHC-UNAMBА', '1580', '2026-07-30', '2026-07-30', NULL, NULL, 1, 'RENDIDO', 4, '2026-10-30 16:36:43', 1, NULL, '2026-10-29 22:44:31', '2026-10-30 21:36:43', NULL, '{"anio": 2026, "numero": 401, "fecha_emision": "2026-10-29", "carta_especialista_investigacion": "0315-2026-FCL-UNAMBA", "fecha_carta_especialista_investigacion": "2026-07-16"}'),
	(23, 'ART-2026-000023', 'ARTICULOS', 3, 'Dra.', 'NOMBRADO', 4, '10-2026-GCHQ-EAPIA-FI-UNAMBА', '229', '2026-02-04', '2026-02-04', NULL, NULL, 1, 'NO_CUMPLE', 1, NULL, 1, NULL, '2026-10-29 22:46:28', '2026-10-30 00:22:29', NULL, NULL),
	(24, 'ART-2026-000024', 'ARTICULOS', 3, 'Dra.', 'NOMBRADO', 4, '12-2026-GCHQ-EAPIA-FI-UNAMBА', '229', '2026-02-04', '2026-02-04', NULL, NULL, 1, 'RENDIDO', 4, '2026-10-30 16:25:16', 1, NULL, '2026-10-29 22:47:46', '2026-10-30 21:25:16', NULL, '{"anio": 2026, "numero": 400, "fecha_emision": "2026-10-29", "carta_especialista_investigacion": "0314-2026-FCL-UNAMBA", "fecha_carta_especialista_investigacion": "2026-02-27"}'),
	(25, 'ART-2026-000025', 'ARTICULOS', 5, 'M.Sc.', 'NOMBRADO', 7, '017 - 2026- CP.MVZ – UNAМВА – АBANCAY/D.DZG', '1475', '2026-07-14', '2026-07-14', NULL, NULL, 1, 'DISPONIBILIDAD_CONFIRMADA', 2, NULL, 1, NULL, '2026-10-30 22:19:00', '2026-10-30 22:36:25', NULL, '{"anio": 2026, "numero": 501, "fecha_emision": "2026-10-30", "carta_especialista_investigacion": null, "fecha_carta_especialista_investigacion": null}'),
	(26, 'ART-2026-000026', 'ARTICULOS', 4, 'Dra.', 'NOMBRADO', 6, '35-2026-FI-HRE-UNAMВА', '1114', '2026-06-02', '2026-06-02', NULL, NULL, 1, 'DISPONIBILIDAD_CONFIRMADA', 2, NULL, 1, NULL, '2026-10-30 22:33:49', '2026-10-30 22:41:59', NULL, '{"anio": 2026, "numero": 501, "fecha_emision": "2026-10-30", "carta_especialista_investigacion": null, "fecha_carta_especialista_investigacion": null}'),
	(27, 'ART-2026-000027', 'ARTICULOS', 9, 'Ing.', 'NOMBRADO', 5, '03-2026-FI-WHC-UNAMBА', '1580', '2026-07-30', '2026-07-30', NULL, NULL, 1, 'POR_RENDIR', 4, NULL, 1, NULL, '2026-10-30 22:44:47', '2026-10-31 01:50:06', NULL, '{"anio": 2026, "numero": 501, "fecha_emision": "2026-10-30", "carta_especialista_investigacion": "0314-2026-FCL-UNAMBA", "fecha_carta_especialista_investigacion": "2026-10-07"}');

-- Volcando estructura para tabla sgr_vrin.expediente_articulos
CREATE TABLE IF NOT EXISTS `expediente_articulos` (
  `expediente_id` bigint unsigned NOT NULL,
  `titulo` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `revista` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `base_indexadora` enum('Scopus','Web of Science','SciELO','Otra') COLLATE utf8mb4_unicode_ci NOT NULL,
  `cuartil` enum('Q1','Q2','Q3','Q4') COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto_solicitado` decimal(12,2) NOT NULL,
  `doi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_aceptacion` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`expediente_id`),
  KEY `ix_art_revista` (`revista`),
  CONSTRAINT `fk_art_exp` FOREIGN KEY (`expediente_id`) REFERENCES `expedientes` (`id`),
  CONSTRAINT `ck_art_monto` CHECK ((`monto_solicitado` > 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalle del artículo (1:1 con el expediente)';

-- Volcando datos para la tabla sgr_vrin.expediente_articulos: ~27 rows (aproximadamente)
INSERT INTO `expediente_articulos` (`expediente_id`, `titulo`, `revista`, `base_indexadora`, `cuartil`, `monto_solicitado`, `doi`, `fecha_aceptacion`, `created_at`, `updated_at`) VALUES
	(1, 'Hydrochemical characterizacion of hot springs on Ollachea, Puno acording to peruvian', 'Frontiers', 'Scopus', 'Q1', 6597.00, NULL, NULL, '2026-10-02 19:42:36', '2026-10-02 19:42:36'),
	(2, 'Effect of processing and germination on the nutritional and bioactive properties of black amaranth (Amaranthus hybridus) cultivated at 2700 m above sea level in Apurimac', 'CyTA Journal of Food', 'Scopus', 'Q2', 9883.80, NULL, '2026-10-15', '2026-10-02 22:27:23', '2026-10-06 02:36:49'),
	(3, 'Hydrochemical characterization of hot springs in Ollachea, Puno, according to Peruvian environmental standards', 'Frontiers', 'Scopus', 'Q1', 6591.15, NULL, '2026-10-24', '2026-10-02 22:46:40', '2026-10-07 19:47:24'),
	(4, 'Hydrochemical characterization of hot springs in Ollachea, Puno, according to Peruvian environmental standards', 'Frontiers', 'Scopus', 'Q1', 6591.15, NULL, '2026-10-08', '2026-10-07 19:57:58', '2026-10-07 20:52:37'),
	(5, 'Effect of processing and germination on the nutritional and bioactive properties of black amaranth (Amaranthus hybridus) cultivated at 2700 m above sea level in Apurimac', 'CyTA Journal of Food', 'Scopus', 'Q2', 9883.80, NULL, '2026-10-02', '2026-10-07 22:39:00', '2026-10-07 22:40:54'),
	(6, 'Characterization and Typology of Beekeeping Systems: A Case Study in the District of Tamburco, Apurímac, Peru', 'Frontiers in Insect Science', 'Scopus', 'Q1', 11420.00, 'https://doi.org/10.57166/micaela.v7.n1.2026.1099', '2026-07-14', '2026-10-09 21:26:50', '2026-10-10 01:50:05'),
	(7, 'Effect of processing and germination on the nutritional and bioactive properties of black amaranth (Amaranthus hybridus) cultivated at 2700 m above sea level in Apurimac', 'CyTA Journal of Food', 'Scopus', 'Q2', 9883.80, NULL, NULL, '2026-10-09 21:29:17', '2026-10-09 21:29:17'),
	(8, 'Effect of processing and germination on the nutritional and bioactive properties of black amaranth (Amaranthus hybridus) cultivated at 2700 m above sea level in Apurimac', 'CyTA Journal of Food', 'Scopus', 'Q2', 9883.80, 'https://doi.org/10.57166/micaela.v7.n1.2026.109', '2026-07-22', '2026-10-12 20:26:11', '2026-10-12 20:39:21'),
	(9, 'Hydrochemical characterization of hot springs in Ollachea, Puno, according to Peruvian environmental standards', 'Frontiers', 'Scopus', 'Q1', 6591.15, '2332222', '2026-06-02', '2026-10-12 22:49:59', '2026-10-14 18:45:11'),
	(10, 'REVISTA JOURNAL', 'TAYLEOR', 'Scopus', 'Q1', 3200.00, NULL, NULL, '2026-10-13 02:01:17', '2026-10-13 02:01:17'),
	(11, 'Asistente multimodal con inteligencia artificial para optimizar la comunicación de admisión en la UNAMBA', 'Riqchary', 'Scopus', 'Q2', 3200.00, NULL, '2026-10-20', '2026-10-13 20:27:46', '2026-10-13 20:44:47'),
	(12, 'FIDO2 y WebAuthn para autenticación organizacional resistente al phishing: revisión sistemática', 'Micaela', 'SciELO', 'Q2', 3200.00, NULL, '2026-10-14', '2026-10-13 21:26:52', '2026-10-13 21:28:29'),
	(13, 'Morphology and organic compounds in \'Cacho de Toro\' potato: a comparative study between aeroponics and soil', 'International Journal of Agriculture and Biosciences', 'Scopus', 'Q2', 3356.46, NULL, '2026-02-04', '2026-10-14 18:18:51', '2026-10-14 18:27:21'),
	(14, 'Analysis of atmospheric air pollutants (CO, NO2, and SO2), through Sentinel-5P images in Google Earth Engine, Apurimac region, period 2020-2023', 'Frontiers', 'Scopus', 'Q1', 11360.00, NULL, '2026-07-30', '2026-10-14 18:21:02', '2026-10-14 18:21:52'),
	(15, 'Analysis of atmospheric air pollutants (CO, NO2, and SO2), through Sentinel-5P images in Google Earth Engine, Apurimac region, period 2020-2023', 'Frontiers', 'Scopus', 'Q1', 11360.00, '251515', '2026-07-30', '2026-10-14 18:49:30', '2026-10-14 19:05:08'),
	(16, 'Mhorpholy and organic compounds in \'Cacho de Toro\' potato: a comparative study', 'International Journal of Agriculture and Biosciencies', 'Scopus', 'Q2', 3356.00, NULL, NULL, '2026-10-16 18:49:06', '2026-10-16 18:49:06'),
	(17, 'Morphology and organic compounds in \'Cacho de Toro\' potato: a comparative study between aeroponics and soil', 'International Journal of Agriculture and Biosciences', 'Scopus', 'Q2', 3356.46, NULL, NULL, '2026-10-20 19:42:43', '2026-10-20 19:42:43'),
	(18, 'Morphology and organic compounds in \'Cacho de Toro\' potato: a comparative study between aeroponics and soil', 'International Journal of Agriculture and Biosciences', 'Scopus', 'Q2', 3356.46, NULL, '2026-02-04', '2026-10-20 19:44:09', '2026-10-20 19:46:11'),
	(19, 'Morphology and organic compounds in \'Cacho de Toro\' potato: a comparative study between aeroponics and soil', 'International Journal of Agriculture and Biosciences', 'Scopus', 'Q2', 3356.46, NULL, '2026-02-04', '2026-10-27 02:09:32', '2026-10-27 02:12:16'),
	(20, 'Effect of processing and germination on the nutritional and bioactive properties of black amaranth (Amaranthus hybridus) cultivated at 2700 m above sea level in Apurimac', 'CyTA Journal of Food', 'Scopus', 'Q2', 9883.80, NULL, '2026-07-22', '2026-10-29 22:27:24', '2026-10-30 00:42:32'),
	(21, 'Analysis of atmospheric air pollutants (CO, NO2, and SO2), through Sentinel-5P images in Google Earth Engine, Apurimac region, period 2020- 2023', 'Frontiers', 'Scopus', 'Q1', 1360.00, 'https://doi.org/10.57166/micaela.v7.n1.2026.109', '2026-07-30', '2026-10-29 22:41:46', '2026-10-30 21:37:46'),
	(22, 'Analysis of atmospheric air pollutants (CO, NO2, and SO2), through Sentinel-5P images in Google Earth Engine, Apurimac region, period 2020- 2023', 'Frontiers', 'Scopus', 'Q1', 11360.00, 'https://doi.org/10.57166/micaela.v7.n1.2026.1099', '2026-07-30', '2026-10-29 22:44:31', '2026-10-30 21:36:43'),
	(23, 'Morphology and organic compounds in \'Cacho de Toro\' potato: a comparative study between aeroponics and soil', 'International Journal of Agriculture and Biosciences', 'Scopus', 'Q2', 3356.46, NULL, NULL, '2026-10-29 22:46:28', '2026-10-29 22:46:28'),
	(24, 'Morphology and organic compounds in \'Cacho de Toro\' potato: a comparative study between aeroponics and soil', 'International Journal of Agriculture and Biosciences', 'Scopus', 'Q2', 3356.46, NULL, '2026-02-04', '2026-10-29 22:47:46', '2026-10-30 00:47:05'),
	(25, 'Characterization and Typology of Beekeeping Systems: A Case Study in the District of Tamhurco Anurímac, Peru', 'Frontiers in Insect Science', 'Web of Science', 'Q1', 11420.00, NULL, '2026-07-14', '2026-10-30 22:19:00', '2026-10-30 22:19:51'),
	(26, 'Hydrochemical characterization of hot springs in Ollachea, Puno, according to Peruvian enyironmental standards', 'Frontiers', 'Scopus', 'Q1', 1175.00, NULL, '2026-06-02', '2026-10-30 22:33:49', '2026-10-30 22:34:40'),
	(27, 'Analysis of atmospheric air pollutants (CO, NO2, and SO2), through Sentinel-5P images in Google Earth Engine, Apurimac region, period 2020- 2023', 'Frontiers', 'Scopus', 'Q1', 11360.00, NULL, '2026-07-30', '2026-10-30 22:44:47', '2026-10-30 22:45:42');

-- Volcando estructura para tabla sgr_vrin.facultades
CREATE TABLE IF NOT EXISTS `facultades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `acronimo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_facultades_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla sgr_vrin.facultades: ~5 rows (aproximadamente)
INSERT INTO `facultades` (`id`, `nombre`, `acronimo`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 'Facultad de Ciencias Agropecuarias', 'FCA', 1, NULL, NULL),
	(2, 'Medicina Veterinaria', 'MV', 1, '2026-10-02 14:06:18', '2026-10-02 14:06:18'),
	(3, 'Ingenierias', 'ING', 1, '2026-10-02 14:06:18', '2026-10-02 14:06:18'),
	(4, 'Administración', 'ADM', 1, '2026-10-02 14:06:18', '2026-10-02 14:06:18'),
	(5, 'Educación y Ciencias Sociales', 'ECS', 1, '2026-10-02 14:06:18', '2026-10-02 14:06:18');

-- Volcando estructura para tabla sgr_vrin.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla sgr_vrin.failed_jobs: ~0 rows (aproximadamente)

-- Volcando estructura para tabla sgr_vrin.google_drive_connections
CREATE TABLE IF NOT EXISTS `google_drive_connections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `refresh_token` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `connected_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla sgr_vrin.google_drive_connections: ~0 rows (aproximadamente)
INSERT INTO `google_drive_connections` (`id`, `refresh_token`, `connected_at`, `created_at`, `updated_at`) VALUES
	(1, 'eyJpdiI6ImVlZ1NGQU1RYy8xMExsNUJaTmVvV0E9PSIsInZhbHVlIjoiN0NoSk9ZcHRESXlHNEtRb2dGVjVaTEloczlZSTdvd1BNbWphdWpUK2phcStEMUlZbFlNSUlNQnN4VDhEM2IyZFR4U1p3bWZGSjhOWGpiVDVJNjBOUWRaS2ZUbmRFbnpyeHVDUmExVStDNnRCd1Evc3czdFdKLzc1OUZiNjhxeHNSei9zY2xWbm5VazlmZlpvN3pSeHNnPT0iLCJtYWMiOiJmODQxMWRhZDg1NzVmZTRmMTE2NWRjMjZkZTkxNDIyYmUzZmU2NjJiZjZjNTM5NDQyMDVhZGU4MDg4NDc5NmY0IiwidGFnIjoiIn0=', '2026-10-13 01:45:26', '2026-10-13 01:45:26', '2026-10-13 01:45:26');

-- Volcando estructura para tabla sgr_vrin.jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla sgr_vrin.jobs: ~18 rows (aproximadamente)
INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
	(24, 'default', '{"uuid":"34b629ae-53f4-4623-883e-64d6e457015a","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":1:{s:19:\\"documentoGeneradoId\\";i:23;}","batchId":null},"createdAt":1791832740,"delay":null}', 0, NULL, 1791832740, 1791832740),
	(25, 'default', '{"uuid":"89ed87cb-8080-4eaa-897a-4484a1aef1dd","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":1:{s:19:\\"documentoGeneradoId\\";i:24;}","batchId":null},"createdAt":1791838039,"delay":null}', 0, NULL, 1791838039, 1791838039),
	(26, 'default', '{"uuid":"7caedc18-1c05-44e2-a0b0-8825aee57d77","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":1:{s:19:\\"documentoGeneradoId\\";i:25;}","batchId":null},"createdAt":1791838366,"delay":null}', 0, NULL, 1791838366, 1791838366),
	(27, 'default', '{"uuid":"41f0d86e-3529-4af5-9877-77a15e62a3cb","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":1:{s:19:\\"documentoGeneradoId\\";i:26;}","batchId":null},"createdAt":1791906287,"delay":null}', 0, NULL, 1791906287, 1791906287),
	(28, 'default', '{"uuid":"95f890ee-0baa-4d8e-8e30-1b034a94ae8f","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":1:{s:19:\\"documentoGeneradoId\\";i:27;}","batchId":null},"createdAt":1791906396,"delay":null}', 0, NULL, 1791906396, 1791906396),
	(29, 'default', '{"uuid":"657ee7f6-69ed-470d-9824-4601795d63e9","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":1:{s:19:\\"documentoGeneradoId\\";i:28;}","batchId":null},"createdAt":1791908909,"delay":null}', 0, NULL, 1791908909, 1791908909),
	(30, 'default', '{"uuid":"063f9867-943c-418a-a622-9f4d39162775","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:29;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1791984112,"delay":null}', 0, NULL, 1791984112, 1791984112),
	(31, 'default', '{"uuid":"58ec3265-d895-426e-8d55-04b2642f80fe","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:30;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1791984304,"delay":null}', 0, NULL, 1791984304, 1791984304),
	(32, 'default', '{"uuid":"c1e94c78-4bb9-4f52-9e9c-e74f618b9d1f","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:31;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1791984442,"delay":null}', 0, NULL, 1791984442, 1791984442),
	(33, 'default', '{"uuid":"ac681f75-9af5-4daf-920c-3a3f415c39a2","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:32;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1791986089,"delay":null}', 0, NULL, 1791986089, 1791986089),
	(34, 'default', '{"uuid":"2b307c5e-0321-46b4-9d22-c9b811be4219","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:33;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1791986364,"delay":null}', 0, NULL, 1791986364, 1791986364),
	(35, 'default', '{"uuid":"69592cf3-d9c2-4f5c-abce-396212b1b4ff","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:34;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1792507571,"delay":null}', 0, NULL, 1792507571, 1792507571),
	(36, 'default', '{"uuid":"113ce6d2-299b-4b4f-bd55-6c570d534f1a","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:35;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1792507717,"delay":null}', 0, NULL, 1792507717, 1792507717),
	(37, 'default', '{"uuid":"df9badbd-c6a1-4e07-a8d9-cf437e49fc3a","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:36;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1793049136,"delay":null}', 0, NULL, 1793049136, 1793049136),
	(38, 'default', '{"uuid":"d8d11c08-e568-4173-8acc-e1a426191bfe","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:37;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1793051256,"delay":null}', 0, NULL, 1793051256, 1793051256),
	(39, 'default', '{"uuid":"d247d5cf-9424-43fa-884f-f035b37ec4fe","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:38;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1793302952,"delay":null}', 0, NULL, 1793302952, 1793302952),
	(40, 'default', '{"uuid":"ffbc48ff-4f1f-4027-b90a-9037b1f65b95","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:39;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1793303225,"delay":null}', 0, NULL, 1793303225, 1793303225),
	(41, 'default', '{"uuid":"e2d24e95-c258-48e1-b74d-cc7af43cede3","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:40;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1793303301,"delay":null}', 0, NULL, 1793303301, 1793303301),
	(42, 'default', '{"uuid":"46706df9-b30a-4010-82bc-14987deea224","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:41;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1793303413,"delay":null}', 0, NULL, 1793303413, 1793303413),
	(43, 'default', '{"uuid":"2a8982f6-fa58-4f7f-bd08-e7b8955f47a2","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:42;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1793303983,"delay":null}', 0, NULL, 1793303983, 1793303983),
	(44, 'default', '{"uuid":"faec7357-8f82-4f4c-aa37-8816f48737ca","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:43;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1793306265,"delay":null}', 0, NULL, 1793306265, 1793306265),
	(45, 'default', '{"uuid":"6f7b9ef0-4519-4027-b258-ca09920a56a8","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:44;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1793306436,"delay":null}', 0, NULL, 1793306436, 1793306436),
	(46, 'default', '{"uuid":"23fdfbf9-85e3-4d80-b430-11523c284268","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:45;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1793306569,"delay":null}', 0, NULL, 1793306569, 1793306569),
	(47, 'default', '{"uuid":"d02f6494-a3d5-4aad-b79c-f2ec78d05903","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:46;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1793371702,"delay":null}', 0, NULL, 1793371702, 1793371702),
	(48, 'default', '{"uuid":"d8353c4e-9c6d-4e99-bf12-aed0451fc59d","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:47;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1793371753,"delay":null}', 0, NULL, 1793371753, 1793371753),
	(49, 'default', '{"uuid":"955c1288-4615-4b9f-86d5-f8680c43c586","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:48;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1793380791,"delay":null}', 0, NULL, 1793380791, 1793380791),
	(50, 'default', '{"uuid":"eac1fb57-b897-4138-91a6-58a8c6080453","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:49;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1793381680,"delay":null}', 0, NULL, 1793381680, 1793381680),
	(51, 'default', '{"uuid":"aa361431-5b29-4ad3-94f7-ec7da259da29","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:50;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1793382342,"delay":null}', 0, NULL, 1793382342, 1793382342),
	(52, 'default', '{"uuid":"33d06916-b5e4-4536-a0c3-35e3b66afde9","displayName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"countCrashesAsExceptions":false,"backoff":null,"timeout":120,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ConvertDocxToPdfJob","command":"O:28:\\"App\\\\Jobs\\\\ConvertDocxToPdfJob\\":2:{s:19:\\"documentoGeneradoId\\";i:51;s:11:\\"afterCommit\\";b:1;}","batchId":null},"createdAt":1793391139,"delay":null}', 0, NULL, 1793391139, 1793391139);

-- Volcando estructura para tabla sgr_vrin.job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla sgr_vrin.job_batches: ~0 rows (aproximadamente)

-- Volcando estructura para tabla sgr_vrin.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla sgr_vrin.migrations: ~8 rows (aproximadamente)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000001_create_cache_table', 1),
	(2, '0001_01_01_000002_create_jobs_table', 1),
	(3, '2026_10_01_200509_create_sessions_table', 1),
	(4, '2026_10_01_200510_create_personal_access_tokens_table', 1),
	(5, '2026_10_05_213339_add_resolucion_borrador_to_expedientes_table', 2),
	(6, '2026_10_07_190000_add_monto_desembolsado_to_rendiciones_table', 3),
	(7, '2026_10_09_190210_add_carta_docente_registro_numero_to_expedientes_table', 4),
	(8, '2026_10_09_191651_add_carta_docente_registro_fecha_to_expedientes_table', 5),
	(9, '2026_10_12_195905_create_google_drive_connections_table', 6),
	(10, '2026_10_12_195908_add_drive_file_id_to_documentos_generados_table', 6),
	(11, '2026_10_13_205927_add_fecha_desembolso_index_to_rendiciones_table', 7),
	(12, '2026_10_26_204951_add_genero_to_docentes_table', 8),
	(13, '2026_10_26_212041_add_carta_especialista_investigacion_to_resoluciones_table', 9),
	(14, '2026_10_27_154226_add_deleted_at_to_plantillas_table', 10),
	(15, '2026_10_27_185424_add_familia_id_to_plantillas_table', 11);

-- Volcando estructura para tabla sgr_vrin.observaciones
CREATE TABLE IF NOT EXISTS `observaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `expediente_id` bigint unsigned NOT NULL,
  `etapa` tinyint unsigned NOT NULL,
  `origen` enum('SECRETARIA','CALIDAD','SISTEMA') COLLATE utf8mb4_unicode_ci NOT NULL,
  `texto` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `resuelta_at` datetime DEFAULT NULL,
  `creada_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_obs_exp` (`expediente_id`,`resuelta_at`),
  KEY `fk_obs_user` (`creada_por`),
  CONSTRAINT `fk_obs_exp` FOREIGN KEY (`expediente_id`) REFERENCES `expedientes` (`id`),
  CONSTRAINT `fk_obs_user` FOREIGN KEY (`creada_por`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `ck_obs_etapa` CHECK ((`etapa` between 1 and 4))
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Observaciones manuales y automáticas (vencimiento, RN-04)';

-- Volcando datos para la tabla sgr_vrin.observaciones: ~7 rows (aproximadamente)
INSERT INTO `observaciones` (`id`, `expediente_id`, `etapa`, `origen`, `texto`, `resuelta_at`, `creada_por`, `created_at`) VALUES
	(1, 21, 1, 'CALIDAD', 'No cuenta con carta de Afiliación', '2026-10-29 19:25:08', 4, '2026-10-29 22:54:18'),
	(2, 22, 1, 'CALIDAD', 'No cumple con afiliación', '2026-10-29 19:24:41', 4, '2026-10-29 23:03:18'),
	(3, 23, 1, 'CALIDAD', 'asdadasd', '2026-10-29 19:18:32', 4, '2026-10-29 23:04:15'),
	(4, 24, 1, 'CALIDAD', 'sdadadad', '2026-10-29 19:13:28', 4, '2026-10-29 23:04:27'),
	(5, 23, 1, 'CALIDAD', 'adasdasd', '2026-10-29 19:23:59', 4, '2026-10-30 00:22:29'),
	(6, 24, 1, 'CALIDAD', 'asdasdasd', '2026-10-29 19:23:23', 4, '2026-10-30 00:22:39'),
	(7, 23, 1, 'CALIDAD', 'Falta Documentos', NULL, 4, '2026-10-30 00:23:59');

-- Volcando estructura para tabla sgr_vrin.personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla sgr_vrin.personal_access_tokens: ~0 rows (aproximadamente)

-- Volcando estructura para tabla sgr_vrin.plantillas
CREATE TABLE IF NOT EXISTS `plantillas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modulo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ARTICULOS',
  `tipo_documento_id` bigint unsigned NOT NULL,
  `version` int unsigned NOT NULL DEFAULT '1',
  `archivo_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sha256` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokens` json DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `familia_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_plantillas_codigo` (`codigo`),
  KEY `ix_plantillas_tipo` (`modulo`,`tipo_documento_id`,`estado`),
  KEY `fk_pl_tipo` (`tipo_documento_id`),
  KEY `fk_pl_user` (`created_by`),
  KEY `plantillas_familia_id_index` (`familia_id`),
  CONSTRAINT `fk_pl_tipo` FOREIGN KEY (`tipo_documento_id`) REFERENCES `tipos_documento_plantilla` (`id`),
  CONSTRAINT `fk_pl_user` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Gestor de plantillas (RF-41/42); el DOCX es inmutable';

-- Volcando datos para la tabla sgr_vrin.plantillas: ~7 rows (aproximadamente)
INSERT INTO `plantillas` (`id`, `codigo`, `nombre`, `modulo`, `tipo_documento_id`, `version`, `archivo_path`, `sha256`, `tokens`, `estado`, `created_by`, `created_at`, `updated_at`, `deleted_at`, `familia_id`) VALUES
	(1, 'PLA-0001', 'Plantilla_Carta_OPP', 'ARTICULOS', 1, 1, 'plantillas/wqls9yf5s9h5ywi0zyn0mroqvfjmvym8tlrxlhdv.docx', 'df47db03d27bf8db7e72676f9a3fd3d91df4be3c9c04fdc6df04f2b31108abaa', '["APELLIDO_MATERNO", "APELLIDO_PATERNO", "BASE_DATOS", "CARTA_DOCENTE", "CIUDAD", "CUARTIL", "FECHA_CARTA_VRIN", "GRADO", "MONTO_TOTAL_SOLICITADO", "NOMBRES", "NUMERO_CARTA_VRIN", "REGISTRO_MESA_PARTES", "REVISTA", "TITULO_ARTICULO"]', 'INACTIVO', 1, '2026-10-02 19:30:25', '2026-10-30 22:12:57', '2026-10-30 22:12:57', 1),
	(2, 'PLA-0002', 'Plantilla_Resolucion_Articulo', 'ARTICULOS', 2, 1, 'plantillas/4nzptaza7pavff0dgpkz0de0bjspqxalvzavuxsp.docx', '4861f41ab278c950d85db4253fffa50869a32e1d9bba18f831a6cedfefa026b9', '["APELLIDO_MATERNO", "APELLIDO_PATERNO", "BASE_DATOS", "CARTA_DOCENTE", "CARTA_OPP", "CUARTIL", "ESCUELA", "ESPECIFICA", "FECHA_CARTA_DOCENTE", "FECHA_CARTA_OPP", "FECHA_REGISTRO_VRIN", "FUENTE", "GRADO", "META", "MONTO_APROBADO", "MONTO_TOTAL_SOLICITADO", "NOMBRES", "NUMERO_REGISTRO_VRIN", "REGLAMENTO_BASE", "REVISTA", "TITULO_ARTICULO", "anio", "fecha_emision", "numero_resolucion"]', 'INACTIVO', 1, '2026-10-02 19:31:19', '2026-10-28 01:04:28', '2026-10-28 01:04:28', 2),
	(3, 'PLA-0003', 'Plantilla_Resolucion_Articulo_v2', 'ARTICULOS', 2, 1, 'plantillas/ffqmx6hcenol8786dc4qocosvhd5wt7twvzpcnqf.docx', '9b08a2a5d1c360d59d54821516bed851dcd21f8d41f9ac2623c98f918f75c2f7', '["APELLIDO_MATERNO", "APELLIDO_PATERNO", "BASE_DATOS", "CARTA_DOCENTE", "CARTA_ESPECIALISTA_INVESTIGACION", "CARTA_OPP", "CUARTIL", "ESCUELA", "ESPECIFICA", "FECHA_CARTA_DOCENTE", "FECHA_CARTA_ESPECIALISTA_INVESTIGACION", "FECHA_CARTA_OPP", "FECHA_REGISTRO_VRIN", "FUENTE", "GENERO", "GRADO", "META", "MONTO_APROBADO", "MONTO_TOTAL_SOLICITADO", "NOMBRES", "NUMERO_REGISTRO_VRIN", "REGLAMENTO_BASE", "REVISTA", "TITULO_ARTICULO", "anio", "fecha_emision", "numero_resolucion"]', 'INACTIVO', 1, '2026-10-27 02:15:50', '2026-10-27 20:48:54', '2026-10-27 20:48:54', 3),
	(4, 'PLA-0004', 'Plantilla_Resolucion_Articulo', 'ARTICULOS', 2, 1, 'plantillas/jxbo5go4jsexdiu07bqwe7dk46ak3ferbkxalb13.docx', '9b08a2a5d1c360d59d54821516bed851dcd21f8d41f9ac2623c98f918f75c2f7', '["APELLIDO_MATERNO", "APELLIDO_PATERNO", "BASE_DATOS", "CARTA_DOCENTE", "CARTA_ESPECIALISTA_INVESTIGACION", "CARTA_OPP", "CUARTIL", "ESCUELA", "ESPECIFICA", "FECHA_CARTA_DOCENTE", "FECHA_CARTA_ESPECIALISTA_INVESTIGACION", "FECHA_CARTA_OPP", "FECHA_REGISTRO_VRIN", "FUENTE", "GENERO", "GRADO", "META", "MONTO_APROBADO", "MONTO_TOTAL_SOLICITADO", "NOMBRES", "NUMERO_REGISTRO_VRIN", "REGLAMENTO_BASE", "REVISTA", "TITULO_ARTICULO", "anio", "fecha_emision", "numero_resolucion"]', 'ACTIVO', 1, '2026-10-27 02:37:22', '2026-10-28 01:11:28', NULL, 4),
	(5, 'PLA-0005', 'Plantilla_Resolucion_Articulo_v2', 'ARTICULOS', 2, 2, 'plantillas/upfofy38yq9ajfzstx9bfthghq1yqsrrryrly8xn.docx', 'e0342e1d01df6c92a98d1fc1abeb3a14458b33d4205c01c01277d0c740db5a04', '[]', 'INACTIVO', 1, '2026-10-27 20:48:28', '2026-10-27 20:48:40', '2026-10-27 20:48:40', 3),
	(6, 'PLA-0006', 'Plantilla_Resolucion_Articulo', 'ARTICULOS', 2, 2, 'plantillas/dgnhlg5floi46kkju9s8lawgcfpvyuzazbzsafnv.docx', 'e0342e1d01df6c92a98d1fc1abeb3a14458b33d4205c01c01277d0c740db5a04', '[]', 'INACTIVO', 1, '2026-10-27 22:53:02', '2026-10-28 01:04:19', '2026-10-28 01:04:19', 2),
	(7, 'PLA-0007', 'Plantilla_Carta_08', 'ARTICULOS', 1, 1, 'plantillas/eeudu6v1zsfhrvhvsd6qzgqbbi3nvc8isune8fc9.docx', 'c6b4233c22778c9ae0e0df39d3ba8532f3106292b92abcb03e0f030c57c18433', '["APELLIDO_MATERNO", "APELLIDO_PATERNO", "BASE_DATOS", "CARTA_DOCENTE", "CIUDAD", "CUARTIL", "FECHA_CARTA_VRIN", "GENERO", "GRADO", "MONTO_TOTAL_SOLICITADO", "NOMBRES", "NUMERO_CARTA_VRIN", "REGISTRO_MESA_PARTES", "REVISTA", "TITULO_ARTICULO"]', 'ACTIVO', 1, '2026-10-30 21:57:03', '2026-10-30 21:57:03', NULL, 7);

-- Volcando estructura para tabla sgr_vrin.plantilla_seleccionada
CREATE TABLE IF NOT EXISTS `plantilla_seleccionada` (
  `modulo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_documento_id` bigint unsigned NOT NULL,
  `plantilla_id` bigint unsigned NOT NULL,
  `seleccionado_por` bigint unsigned NOT NULL,
  `seleccionado_at` datetime NOT NULL,
  PRIMARY KEY (`modulo`,`tipo_documento_id`),
  KEY `fk_sel_plantilla` (`plantilla_id`),
  KEY `fk_sel_tipo` (`tipo_documento_id`),
  KEY `fk_sel_user` (`seleccionado_por`),
  CONSTRAINT `fk_sel_plantilla` FOREIGN KEY (`plantilla_id`) REFERENCES `plantillas` (`id`),
  CONSTRAINT `fk_sel_tipo` FOREIGN KEY (`tipo_documento_id`) REFERENCES `tipos_documento_plantilla` (`id`),
  CONSTRAINT `fk_sel_user` FOREIGN KEY (`seleccionado_por`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Plantilla vigente por módulo y tipo (RN-13); el historial de cambios va a auditoria';

-- Volcando datos para la tabla sgr_vrin.plantilla_seleccionada: ~2 rows (aproximadamente)
INSERT INTO `plantilla_seleccionada` (`modulo`, `tipo_documento_id`, `plantilla_id`, `seleccionado_por`, `seleccionado_at`) VALUES
	('ARTICULOS', 1, 7, 1, '2026-10-30 17:12:47'),
	('ARTICULOS', 2, 4, 1, '2026-10-26 21:38:08');

-- Volcando estructura para tabla sgr_vrin.rendiciones
CREATE TABLE IF NOT EXISTS `rendiciones` (
  `expediente_id` bigint unsigned NOT NULL,
  `fecha_desembolso` date NOT NULL,
  `fecha_limite` date NOT NULL,
  `fecha_informe` date DEFAULT NULL,
  `estado` enum('BORRADOR','CERRADA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BORRADOR',
  `con_retraso` tinyint(1) DEFAULT NULL,
  `cerrada_por` bigint unsigned DEFAULT NULL,
  `cerrada_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `monto_desembolsado` decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (`expediente_id`),
  KEY `ix_rendiciones_limite` (`estado`,`fecha_limite`),
  KEY `fk_rend_user` (`cerrada_por`),
  KEY `ix_rendiciones_fecha_desembolso` (`fecha_desembolso`),
  CONSTRAINT `fk_rend_exp` FOREIGN KEY (`expediente_id`) REFERENCES `expedientes` (`id`),
  CONSTRAINT `fk_rend_user` FOREIGN KEY (`cerrada_por`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Etapa 4 (1:1); comprobantes e informe van en `archivos`';

-- Volcando datos para la tabla sgr_vrin.rendiciones: ~12 rows (aproximadamente)
INSERT INTO `rendiciones` (`expediente_id`, `fecha_desembolso`, `fecha_limite`, `fecha_informe`, `estado`, `con_retraso`, `cerrada_por`, `cerrada_at`, `created_at`, `updated_at`, `monto_desembolsado`) VALUES
	(5, '2026-10-07', '2026-12-30', '2026-10-07', 'CERRADA', 0, 1, '2026-10-07 20:58:58', '2026-10-08 01:40:51', '2026-10-08 01:58:58', 9883.00),
	(6, '2026-10-09', '2027-01-01', '2026-10-09', 'CERRADA', 0, 1, '2026-10-09 20:50:05', '2026-10-10 01:03:20', '2026-10-10 01:50:05', 11420.00),
	(8, '2026-10-13', '2027-01-05', '2027-01-01', 'CERRADA', 0, 1, '2026-10-12 15:39:21', '2026-10-12 20:38:06', '2026-10-12 20:39:21', 9883.00),
	(9, '2026-10-12', '2027-01-04', '2026-10-14', 'CERRADA', 0, 1, '2026-10-14 13:45:11', '2026-10-13 01:57:44', '2026-10-14 18:45:11', 6591.15),
	(14, '2026-10-14', '2027-01-06', NULL, 'BORRADOR', NULL, NULL, NULL, '2026-10-14 18:25:33', '2026-10-14 18:25:33', 11360.00),
	(15, '2026-10-14', '2027-01-06', '2026-10-14', 'CERRADA', 0, 1, '2026-10-14 14:05:08', '2026-10-14 19:03:40', '2026-10-14 19:05:08', 11360.00),
	(18, '2026-10-20', '2027-01-12', '2026-10-20', 'CERRADA', 0, 1, '2026-10-20 14:50:48', '2026-10-20 19:49:56', '2026-10-20 19:50:48', 3356.46),
	(19, '2026-10-29', '2027-01-21', NULL, 'BORRADOR', NULL, NULL, NULL, '2026-10-30 01:41:07', '2026-10-30 01:41:07', 3356.46),
	(20, '2026-10-29', '2027-01-21', NULL, 'BORRADOR', NULL, NULL, NULL, '2026-10-30 01:41:27', '2026-10-30 01:41:27', 9883.80),
	(21, '2026-10-19', '2027-01-11', '2026-10-30', 'CERRADA', 0, 1, '2026-10-30 16:37:46', '2026-10-30 21:22:32', '2026-10-30 21:37:46', 11360.00),
	(22, '2026-10-29', '2027-01-21', '2026-10-30', 'CERRADA', 0, 1, '2026-10-30 16:36:43', '2026-10-30 01:41:17', '2026-10-30 21:36:43', 11360.00),
	(24, '2026-10-29', '2027-01-21', '2026-10-30', 'CERRADA', 0, 1, '2026-10-30 16:25:16', '2026-10-30 01:40:53', '2026-10-30 21:25:16', 3356.46),
	(27, '2026-10-30', '2027-01-22', NULL, 'BORRADOR', NULL, NULL, NULL, '2026-10-31 01:50:06', '2026-10-31 01:50:06', 11360.00);

-- Volcando estructura para tabla sgr_vrin.resoluciones
CREATE TABLE IF NOT EXISTS `resoluciones` (
  `expediente_id` bigint unsigned NOT NULL,
  `numero` int unsigned NOT NULL,
  `anio` smallint unsigned NOT NULL,
  `fecha_emision` date NOT NULL,
  `carta_especialista_investigacion` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_carta_especialista_investigacion` date DEFAULT NULL,
  `estado` enum('EMITIDA','ANULADA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EMITIDA',
  `emitida_por` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`expediente_id`),
  UNIQUE KEY `uq_resoluciones_numero` (`anio`,`numero`),
  KEY `fk_res_user` (`emitida_por`),
  CONSTRAINT `fk_res_exp` FOREIGN KEY (`expediente_id`) REFERENCES `expedientes` (`id`),
  CONSTRAINT `fk_res_user` FOREIGN KEY (`emitida_por`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Etapa 3 (1:1); estructura del documento vive en la plantilla (RN-08)';

-- Volcando datos para la tabla sgr_vrin.resoluciones: ~15 rows (aproximadamente)
INSERT INTO `resoluciones` (`expediente_id`, `numero`, `anio`, `fecha_emision`, `carta_especialista_investigacion`, `fecha_carta_especialista_investigacion`, `estado`, `emitida_por`, `created_at`, `updated_at`) VALUES
	(1, 45, 2026, '2026-10-05', NULL, NULL, 'EMITIDA', 1, '2026-10-06 02:27:15', '2026-10-06 02:27:15'),
	(2, 50, 2026, '2026-10-05', NULL, NULL, 'EMITIDA', 1, '2026-10-06 02:38:25', '2026-10-06 02:38:25'),
	(5, 93, 2026, '2026-08-03', NULL, NULL, 'EMITIDA', 1, '2026-10-07 22:52:25', '2026-10-08 01:06:50'),
	(6, 97, 2026, '2026-10-09', NULL, NULL, 'EMITIDA', 1, '2026-10-10 00:52:31', '2026-10-10 00:52:31'),
	(8, 60, 2026, '2026-10-12', NULL, NULL, 'EMITIDA', 1, '2026-10-12 20:36:48', '2026-10-12 20:36:48'),
	(9, 76, 2026, '2026-10-12', NULL, NULL, 'EMITIDA', 1, '2026-10-13 01:52:46', '2026-10-13 01:52:46'),
	(14, 200, 2026, '2026-10-14', NULL, NULL, 'EMITIDA', 1, '2026-10-14 18:25:04', '2026-10-14 18:25:04'),
	(15, 201, 2026, '2026-10-14', NULL, NULL, 'EMITIDA', 1, '2026-10-14 18:59:24', '2026-10-14 18:59:24'),
	(18, 100, 2026, '2026-10-20', NULL, NULL, 'EMITIDA', 1, '2026-10-20 19:48:37', '2026-10-20 19:48:37'),
	(19, 300, 2026, '2026-10-26', 'CARTA N° 0245-2026-FLC-UNAMBA', '2026-10-21', 'EMITIDA', 1, '2026-10-27 02:47:36', '2026-10-27 02:47:36'),
	(20, 500, 2026, '2026-10-29', '0314-2026-FCL-UNAMBA', '2026-06-30', 'EMITIDA', 1, '2026-10-30 01:40:36', '2026-10-30 01:40:36'),
	(21, 20, 2026, '2026-10-30', '0314-2026-FCL-UNAMBA', '2026-07-02', 'EMITIDA', 1, '2026-10-30 19:48:22', '2026-10-30 19:49:13'),
	(22, 401, 2026, '2026-10-29', '0315-2026-FCL-UNAMBA', '2026-07-16', 'EMITIDA', 1, '2026-10-30 01:37:45', '2026-10-30 01:37:45'),
	(24, 400, 2026, '2026-10-29', '0314-2026-FCL-UNAMBA', '2026-02-27', 'EMITIDA', 1, '2026-10-30 00:59:42', '2026-10-30 00:59:42'),
	(27, 501, 2026, '2026-10-30', '0314-2026-FCL-UNAMBA', '2026-10-07', 'EMITIDA', 1, '2026-10-31 01:12:19', '2026-10-31 01:12:19');

-- Volcando estructura para tabla sgr_vrin.respuestas_opp
CREATE TABLE IF NOT EXISTS `respuestas_opp` (
  `expediente_id` bigint unsigned NOT NULL,
  `disponibilidad` enum('SI','NO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `carta_numero` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `carta_fecha` date NOT NULL,
  `monto_aprobado` decimal(12,2) DEFAULT NULL,
  `meta_presupuestal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `especifica_gasto` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fuente_financiamiento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registro_vrin_numero` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `registro_vrin_fecha` date NOT NULL,
  `registrado_por` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`expediente_id`),
  KEY `fk_opp_user` (`registrado_por`),
  CONSTRAINT `fk_opp_exp` FOREIGN KEY (`expediente_id`) REFERENCES `expedientes` (`id`),
  CONSTRAINT `fk_opp_user` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `ck_opp_completa` CHECK (((`disponibilidad` = _utf8mb4'NO') or ((`monto_aprobado` is not null) and (`monto_aprobado` > 0) and (`meta_presupuestal` is not null) and (`especifica_gasto` is not null) and (`fuente_financiamiento` is not null)))),
  CONSTRAINT `ck_opp_meta` CHECK (((`meta_presupuestal` is null) or regexp_like(`meta_presupuestal`,_utf8mb4'^[0-9]{3}$')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Etapa 2/3 (1:1); la Resolución exige disponibilidad SI completa (RN-06)';

-- Volcando datos para la tabla sgr_vrin.respuestas_opp: ~17 rows (aproximadamente)
INSERT INTO `respuestas_opp` (`expediente_id`, `disponibilidad`, `carta_numero`, `carta_fecha`, `monto_aprobado`, `meta_presupuestal`, `especifica_gasto`, `fuente_financiamiento`, `registro_vrin_numero`, `registro_vrin_fecha`, `registrado_por`, `created_at`, `updated_at`) VALUES
	(1, 'SI', 'Carta-OPP-2026', '2026-10-15', 6597.00, '017', '2.3.11.07', 'R.O', '1234', '2026-10-15', 1, '2026-10-06 02:27:04', '2026-10-06 02:27:04'),
	(2, 'SI', 'Carta-N° 10-OPP-2026', '2026-10-07', 9883.80, '017', '2.3.11.07', 'R.O', '1234', '2026-10-09', 1, '2026-10-06 02:38:15', '2026-10-06 02:38:15'),
	(5, 'SI', '1688-2026-OPP-UNAMBA', '2026-07-30', 9883.00, '017', '2.3.27.1199', 'Recursos Determinados', '1581', '2026-07-31', 1, '2026-10-07 22:52:18', '2026-10-08 01:06:44'),
	(6, 'SI', 'CARTA N° 1574-2026-OPP-UNAMBA', '2026-07-20', 11420.00, '017', '2.3.2 7.11 99', 'Recursos Determinados', '1537', '2026-07-20', 1, '2026-10-10 00:52:03', '2026-10-10 00:52:03'),
	(8, 'SI', 'CARTA N° 1688-2026-OPP-UNAMBA', '2026-07-30', 9883.00, '017', '2.3.27.1199', 'Recursos Determinados', '1381', '2026-07-31', 1, '2026-10-12 20:35:37', '2026-10-12 20:36:41'),
	(9, 'SI', '1176-2026-OPP-UNAMBA', '2026-06-08', 6591.15, '017', '2.3.27.11', 'Recursos Determinados', '1155', '2026-06-10', 1, '2026-10-13 01:52:40', '2026-10-13 01:52:40'),
	(14, 'SI', 'CARTA N° 1710-2026-OPP-UNAMBA', '2026-08-03', 11360.00, '017', '2.3.27.1199', 'Recursos Determinados', '1602', '2026-08-03', 1, '2026-10-14 18:24:56', '2026-10-14 18:24:56'),
	(15, 'SI', 'CARTA N° 1710-2026-OPP-UNAMBA', '2026-08-03', 11360.00, '017', '2.3.27.1199', 'Recursos Determinados', '1602', '2026-08-03', 1, '2026-10-14 18:59:13', '2026-10-14 18:59:13'),
	(18, 'SI', 'CARTA N° 0212-2026-OPP-UNAMBA', '2026-02-05', 3356.46, '017', '2.3.27.1199', 'Recursos Determinados', '247', '2026-02-06', 1, '2026-10-20 19:48:29', '2026-10-20 19:48:29'),
	(19, 'SI', 'CARTA N° 0212-2026-OPP-UNAMBA', '2026-02-05', 3356.46, '017', '2.3.27.1199', 'Recursos Determinados', '247', '2026-02-06', 1, '2026-10-27 02:13:30', '2026-10-27 02:13:30'),
	(20, 'SI', '1688-2026-OPP-UNAMBA', '2026-07-30', 9883.80, '017', '2.3.27.1199', 'Recursos Determinados', '1581', '2026-07-31', 1, '2026-10-30 01:40:12', '2026-10-30 01:40:12'),
	(21, 'SI', '1710-2026-OPP-UNAMBA', '2026-08-03', 11360.00, '017', '2.3.27.1199', 'Recursos Determinados', '1602', '2026-08-03', 1, '2026-10-30 19:48:10', '2026-10-30 19:49:01'),
	(22, 'SI', 'CARTA N° 1710-2026-OPP-UNAMBA', '2026-08-03', 11360.00, '017', '2.3.27.1199', 'Recursos Determinados', '1602', '2026-08-03', 1, '2026-10-30 01:37:10', '2026-10-30 01:37:10'),
	(24, 'SI', '0212-2026-OPP-UNAMBA', '2026-02-05', 3356.46, '017', '2.3.27.1199', 'Recursos Determinados', '247', '2026-02-06', 1, '2026-10-30 00:58:47', '2026-10-30 00:58:47'),
	(25, 'SI', '1574-2026-OPP-UNAMBA', '2026-07-20', 11420.00, '017', '2.3.27.11.99', 'Recursos Determinados', '1537', '2026-07-20', 1, '2026-10-30 22:36:25', '2026-10-30 22:36:25'),
	(26, 'SI', '1710-2026-OPP-UNAMBA', '2026-08-03', 1175.00, '017', '2.3.27.1199', 'Recursos Determinados', '1602', '2026-08-03', 1, '2026-10-30 22:41:59', '2026-10-30 22:41:59'),
	(27, 'SI', '1710-2026-OPP-UNAMBA', '2026-10-01', 11360.00, '017', '2.3.11.07', 'Recursos Determinados', '1234', '2026-10-07', 1, '2026-10-30 22:56:58', '2026-10-31 01:09:29');

-- Volcando estructura para tabla sgr_vrin.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Roles gestionables por el administrador (CRUD en la UI)';

-- Volcando datos para la tabla sgr_vrin.roles: ~4 rows (aproximadamente)
INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 'Administrador General', 'Acceso total: catálogos, usuarios, roles, plantillas y expedientes', 1, NULL, '2026-10-02 18:36:51'),
	(2, 'Secretaría', 'Registro y gestión del expediente en las 4 etapas', 1, NULL, NULL),
	(3, 'Calidad', 'Validación de requisitos del artículo (Etapa 1, RN-01)', 1, NULL, NULL),
	(4, 'Asistente', NULL, 0, '2026-10-02 18:28:41', '2026-10-02 18:36:28');

-- Volcando estructura para tabla sgr_vrin.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla sgr_vrin.sessions: ~1 rows (aproximadamente)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('gYrxgO9RZ4JTWCVSTyLMvAhn8FXW8iRYq1baVnyL', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJnMFNRRzJEQkI1cU5tU2dqZllsSk40OFJ3MWtMZzIzT0VYVmxvYklqIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9hcGlcL2V4cGVkaWVudGVzP3BhZ2U9MSIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwicGFzc3dvcmRfaGFzaF93ZWIiOiI3MjA1ZGRhM2UzMzM3ODZhZjI5YjI4M2FhYzA0ZTQzYTAzODQ5YmFhYmE0ZWMyOTZlN2NjMDc0MTRiMzU1NjNjIn0=', 1794321738),
	('HQCOrXwsv4Obv6gucmFzZSOaeKfsUtzQ7TbTyDGm', 4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'eyJfdG9rZW4iOiI3Z0RkZEZBNHFjWFNscDJQTUsySnQwTHRRU3VMSWM5dDNVSEhjcThRIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9hcGlcL2V4cGVkaWVudGVzP3BhZ2U9MSIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6NCwicGFzc3dvcmRfaGFzaF93ZWIiOiIyYmI4ZTFiYzRiZjMxMmY3YjhlNjg2NjQ5NzI5OTFkMWY5MTYwMjY3YWZkNjIzN2YzOTEzNjMxNTRjNTUwMmJlIn0=', 1794321680);

-- Volcando estructura para tabla sgr_vrin.tipos_documento_plantilla
CREATE TABLE IF NOT EXISTS `tipos_documento_plantilla` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tipodocpl_codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tipos de plantilla gestionables (escalable a nuevos tipos)';

-- Volcando datos para la tabla sgr_vrin.tipos_documento_plantilla: ~2 rows (aproximadamente)
INSERT INTO `tipos_documento_plantilla` (`id`, `codigo`, `nombre`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 'CARTA', 'Carta VRIN → OPP', 1, NULL, NULL),
	(2, 'RESOLUCION', 'Resolución VRIN', 1, NULL, NULL);

-- Volcando estructura para tabla sgr_vrin.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dni` char(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombres` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rol_id` bigint unsigned NOT NULL,
  `google_sub` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `ultimo_login_at` datetime DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usuarios_email` (`email`),
  UNIQUE KEY `uq_usuarios_dni` (`dni`),
  UNIQUE KEY `uq_usuarios_google_sub` (`google_sub`),
  KEY `ix_usuarios_rol` (`rol_id`,`activo`),
  CONSTRAINT `fk_usuarios_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `ck_usuarios_dni` CHECK (regexp_like(`dni`,_utf8mb4'^[0-9]{8}$')),
  CONSTRAINT `ck_usuarios_dominio` CHECK ((`email` like _utf8mb4'%@unamba.edu.pe'))
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Usuarios internos; login dual: email+contraseña o Google institucional';

-- Volcando datos para la tabla sgr_vrin.usuarios: ~4 rows (aproximadamente)
INSERT INTO `usuarios` (`id`, `dni`, `nombres`, `apellidos`, `email`, `password_hash`, `rol_id`, `google_sub`, `activo`, `ultimo_login_at`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, '00000000', 'Administrador General', 'VRIN', 'admin@unamba.edu.pe', '$2y$12$vkdBor1Ezej6ywJFle9QL.SY5V4/8X1g0tA5HXgHYmh16yYv8Tesu', 1, NULL, 1, '2026-11-10 14:41:41', NULL, NULL, '2026-11-10 19:41:41', NULL),
	(2, '77100148', 'Kely', 'Segundo', 'ksegundo@unamba.edu.pe', '$2y$12$H3d51CAtJLQWq3YK.E/EWuYQBJfv2Y8xpDalV8ln/xRqvGQRsN9cC', 1, NULL, 1, '2026-10-02 14:00:57', NULL, '2026-10-02 18:50:27', '2026-10-02 19:00:57', NULL),
	(3, '77100123', 'Dina', 'Taipe', 'fondoeditorial@unamba.edu.pe', '$2y$12$Ee8tn.HC7TOYqMi80IN.wOz52VNyJYrfrrJwOtPonaSk59wiYKjxK', 2, NULL, 1, '2026-10-16 13:42:57', NULL, '2026-10-02 18:56:29', '2026-10-16 18:42:57', NULL),
	(4, '77100122', 'Frank', 'Hurtado', 'revistamicaela@unamba.edu.pe', '$2y$12$zZRTQ8NEkBlA5ky/DqOohODynnBfygcdhqV/H22pxJL9Sp5.JhuNa', 3, NULL, 1, '2026-11-10 14:41:18', NULL, '2026-10-02 19:00:41', '2026-11-10 19:41:18', NULL);

-- Volcando estructura para tabla sgr_vrin.validaciones_calidad
CREATE TABLE IF NOT EXISTS `validaciones_calidad` (
  `expediente_id` bigint unsigned NOT NULL,
  `resultado` enum('CUMPLE','NO_CUMPLE') COLLATE utf8mb4_unicode_ci NOT NULL,
  `checklist` json NOT NULL,
  `observacion` text COLLATE utf8mb4_unicode_ci,
  `validado_por` bigint unsigned NOT NULL,
  `validado_at` datetime NOT NULL,
  PRIMARY KEY (`expediente_id`),
  KEY `fk_val_user` (`validado_por`),
  CONSTRAINT `fk_val_exp` FOREIGN KEY (`expediente_id`) REFERENCES `expedientes` (`id`),
  CONSTRAINT `fk_val_user` FOREIGN KEY (`validado_por`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Una validación vigente por expediente; re-validar = UPDATE de la fila';

-- Volcando datos para la tabla sgr_vrin.validaciones_calidad: ~22 rows (aproximadamente)
INSERT INTO `validaciones_calidad` (`expediente_id`, `resultado`, `checklist`, `observacion`, `validado_por`, `validado_at`) VALUES
	(1, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-02 15:06:17'),
	(2, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-02 17:48:15'),
	(3, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-07 14:36:19'),
	(4, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-07 14:58:44'),
	(5, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-07 17:39:15'),
	(6, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-09 19:30:20'),
	(7, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-09 18:47:45'),
	(8, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-12 15:28:39'),
	(9, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-12 19:04:49'),
	(11, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-13 15:30:51'),
	(12, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-13 16:28:08'),
	(13, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-14 13:22:53'),
	(14, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-14 13:21:30'),
	(15, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-14 13:52:54'),
	(16, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-16 13:49:21'),
	(18, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-20 14:45:05'),
	(19, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-26 21:11:14'),
	(20, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-29 18:02:28'),
	(21, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-29 19:25:08'),
	(22, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-29 19:24:41'),
	(23, 'NO_CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', 'Falta Documentos', 4, '2026-10-29 19:23:59'),
	(24, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-29 19:23:23'),
	(25, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-30 17:19:17'),
	(26, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-30 17:34:03'),
	(27, 'CUMPLE', '{"carta_aceptacion": true, "afiliacion_universidad": true, "docente_ordinario_contratado": true}', NULL, 4, '2026-10-30 17:45:01');

-- Volcando estructura para vista sgr_vrin.vw_bandeja_calidad
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vw_bandeja_calidad` (
	`id` BIGINT UNSIGNED NOT NULL,
	`codigo` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`fecha_registro` TIMESTAMP NULL,
	`titulo_articulo` TEXT NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`estado` ENUM('OBSERVADO','EN_REVISION_CALIDAD','VALIDADO_CALIDAD','NO_CUMPLE','EN_ESPERA_OPP','SIN_DISPONIBILIDAD','DISPONIBILIDAD_CONFIRMADA','RESOLUCION_EMITIDA','POR_RENDIR','RENDICION_VENCIDA','RENDIDO') NOT NULL COLLATE 'utf8mb4_unicode_ci'
) ENGINE=MyISAM;

-- Volcando estructura para vista sgr_vrin.vw_catalogo_publico
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vw_catalogo_publico` (
	`numero_resolucion` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`anio` SMALLINT UNSIGNED NOT NULL,
	`resolucion_completa` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_bin',
	`titulo_articulo` TEXT NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`expediente_codigo` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`estado` ENUM('OBSERVADO','EN_REVISION_CALIDAD','VALIDADO_CALIDAD','NO_CUMPLE','EN_ESPERA_OPP','SIN_DISPONIBILIDAD','DISPONIBILIDAD_CONFIRMADA','RESOLUCION_EMITIDA','POR_RENDIR','RENDICION_VENCIDA','RENDIDO') NOT NULL COLLATE 'utf8mb4_unicode_ci'
) ENGINE=MyISAM;

-- Volcando estructura para vista sgr_vrin.vw_dashboard_estados
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vw_dashboard_estados` (
	`modulo` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`estado` ENUM('OBSERVADO','EN_REVISION_CALIDAD','VALIDADO_CALIDAD','NO_CUMPLE','EN_ESPERA_OPP','SIN_DISPONIBILIDAD','DISPONIBILIDAD_CONFIRMADA','RESOLUCION_EMITIDA','POR_RENDIR','RENDICION_VENCIDA','RENDIDO') NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`total` BIGINT NOT NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista sgr_vrin.vw_rendiciones_por_vencer
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vw_rendiciones_por_vencer` (
	`expediente_id` BIGINT UNSIGNED NOT NULL,
	`codigo` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`fecha_desembolso` DATE NOT NULL,
	`fecha_limite` DATE NOT NULL,
	`estado` ENUM('OBSERVADO','EN_REVISION_CALIDAD','VALIDADO_CALIDAD','NO_CUMPLE','EN_ESPERA_OPP','SIN_DISPONIBILIDAD','DISPONIBILIDAD_CONFIRMADA','RESOLUCION_EMITIDA','POR_RENDIR','RENDICION_VENCIDA','RENDIDO') NOT NULL COLLATE 'utf8mb4_unicode_ci'
) ENGINE=MyISAM;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vw_bandeja_calidad`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_bandeja_calidad` AS select `e`.`id` AS `id`,`e`.`codigo` AS `codigo`,`e`.`created_at` AS `fecha_registro`,`a`.`titulo` AS `titulo_articulo`,`e`.`estado` AS `estado` from (`expedientes` `e` join `expediente_articulos` `a` on((`a`.`expediente_id` = `e`.`id`))) where ((`e`.`estado` = 'EN_REVISION_CALIDAD') and (`e`.`documentos_completos` = 1) and (`e`.`deleted_at` is null));

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vw_catalogo_publico`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_catalogo_publico` AS select lpad(`r`.`numero`,3,'0') AS `numero_resolucion`,`r`.`anio` AS `anio`,concat(lpad(`r`.`numero`,3,'0'),'-',`r`.`anio`,'-VRIN-UNAMBA') AS `resolucion_completa`,`a`.`titulo` AS `titulo_articulo`,`e`.`codigo` AS `expediente_codigo`,`e`.`estado` AS `estado` from ((`resoluciones` `r` join `expedientes` `e` on((`e`.`id` = `r`.`expediente_id`))) join `expediente_articulos` `a` on((`a`.`expediente_id` = `e`.`id`))) where ((`r`.`estado` = 'EMITIDA') and (`e`.`deleted_at` is null));

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vw_dashboard_estados`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_dashboard_estados` AS select `expedientes`.`modulo` AS `modulo`,`expedientes`.`estado` AS `estado`,count(0) AS `total` from `expedientes` where (`expedientes`.`deleted_at` is null) group by `expedientes`.`modulo`,`expedientes`.`estado`;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vw_rendiciones_por_vencer`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_rendiciones_por_vencer` AS select `r`.`expediente_id` AS `expediente_id`,`e`.`codigo` AS `codigo`,`r`.`fecha_desembolso` AS `fecha_desembolso`,`r`.`fecha_limite` AS `fecha_limite`,`e`.`estado` AS `estado` from (`rendiciones` `r` join `expedientes` `e` on((`e`.`id` = `r`.`expediente_id`))) where ((`r`.`estado` = 'BORRADOR') and (`e`.`deleted_at` is null));

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
