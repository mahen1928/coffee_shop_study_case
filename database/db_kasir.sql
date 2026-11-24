-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: kasir
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `detail_transaksi`
--

DROP TABLE IF EXISTS `detail_transaksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detail_transaksi` (
  `id_detail` int(11) NOT NULL AUTO_INCREMENT,
  `id_transaksi` int(11) NOT NULL,
  `id_menu` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga_satuan` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `fk_detail_transaksi` (`id_transaksi`),
  KEY `fk_detail_menu` (`id_menu`),
  CONSTRAINT `fk_detail_menu` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`) ON UPDATE CASCADE,
  CONSTRAINT `fk_detail_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_transaksi`
--

LOCK TABLES `detail_transaksi` WRITE;
/*!40000 ALTER TABLE `detail_transaksi` DISABLE KEYS */;
INSERT INTO `detail_transaksi` VALUES (1,1,1,1,25000.00,25000.00),(2,1,4,1,18000.00,18000.00),(3,2,2,1,22000.00,22000.00),(4,2,7,1,15000.00,15000.00),(5,3,8,1,5000.00,5000.00),(6,3,4,1,18000.00,18000.00),(7,4,4,1,18000.00,18000.00),(8,5,2,1,22000.00,22000.00),(9,5,8,9,5000.00,45000.00),(10,5,7,1,15000.00,15000.00);
/*!40000 ALTER TABLE `detail_transaksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu`
--

DROP TABLE IF EXISTS `menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu` (
  `id_menu` int(11) NOT NULL AUTO_INCREMENT,
  `kode_barcode` varchar(50) NOT NULL,
  `nama_menu` varchar(100) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_menu`),
  UNIQUE KEY `kode_barcode` (`kode_barcode`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu`
--

LOCK TABLES `menu` WRITE;
/*!40000 ALTER TABLE `menu` DISABLE KEYS */;
INSERT INTO `menu` VALUES (1,'MN001','Nasi Goreng Spesial',25000.00,30,'2026-11-24 12:25:23','2026-11-24 12:25:23'),(2,'MN002','Ayam Geprek Sambal Matah',22000.00,24,'2026-11-24 12:25:23','2026-11-24 13:15:13'),(3,'MN003','Mie Goreng Jawa',20000.00,15,'2026-11-24 12:25:23','2026-11-24 12:25:23'),(4,'MN004','Kopi Susu Gula Aren',18000.00,38,'2026-11-24 12:25:23','2026-11-24 13:14:19'),(5,'MN005','Es Teh Manis',6000.00,50,'2026-11-24 12:25:23','2026-11-24 12:25:23'),(6,'MN006','Matcha Latte',23000.00,0,'2026-11-24 12:25:23','2026-11-24 12:25:23'),(7,'MN007','Kentang Goreng',15000.00,19,'2026-11-24 12:25:23','2026-11-24 13:15:13'),(8,'MN008','Air Mineral 600ml',5000.00,50,'2026-11-24 12:25:23','2026-11-24 13:15:13');
/*!40000 ALTER TABLE `menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaksi`
--

DROP TABLE IF EXISTS `transaksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL AUTO_INCREMENT,
  `no_struk` varchar(30) NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `total_bayar` decimal(10,2) NOT NULL,
  `metode_pembayaran` enum('Tunai','Non-Tunai') NOT NULL,
  `uang_diterima` decimal(10,2) NOT NULL,
  `kembalian` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_transaksi`),
  UNIQUE KEY `no_struk` (`no_struk`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi`
--

LOCK TABLES `transaksi` WRITE;
/*!40000 ALTER TABLE `transaksi` DISABLE KEYS */;
INSERT INTO `transaksi` VALUES (1,'TRX-20261124-001','2026-11-24 10:15:00',43000.00,'Tunai',50000.00,7000.00),(2,'TRX-20261124-002','2026-11-24 11:30:00',37000.00,'Non-Tunai',37000.00,0.00),(3,'TRX-20261124-200808-95','2026-11-24 20:08:08',23000.00,'Non-Tunai',23000.00,0.00),(4,'TRX-20261124-201419-95','2026-11-24 20:14:19',18000.00,'Non-Tunai',18000.00,0.00),(5,'TRX-20261124-201513-70','2026-11-24 20:15:13',82000.00,'Tunai',100000.00,18000.00);
/*!40000 ALTER TABLE `transaksi` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-24 20:35:22
