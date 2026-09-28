/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.7.2-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: sistema_chamados_hospital
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
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `categoria_chamado`
--

DROP TABLE IF EXISTS `categoria_chamado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categoria_chamado` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nome_categoria` varchar(100) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categoria_chamado`
--

LOCK TABLES `categoria_chamado` WRITE;
/*!40000 ALTER TABLE `categoria_chamado` DISABLE KEYS */;
/*!40000 ALTER TABLE `categoria_chamado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chamado`
--

DROP TABLE IF EXISTS `chamado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `chamado` (
  `id_chamado` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) NOT NULL,
  `descricao` text NOT NULL,
  `data_abertura` datetime NOT NULL DEFAULT current_timestamp(),
  `data_fechamento` datetime DEFAULT NULL,
  `id_paciente` int(11) DEFAULT NULL,
  `id_funcionario_abertura` int(11) NOT NULL,
  `id_funcionario_responsavel` int(11) DEFAULT NULL,
  `id_setor` int(11) NOT NULL,
  `id_categoria_chamado` int(11) NOT NULL,
  `id_prioridade` int(11) NOT NULL,
  `id_status_chamado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_chamado`),
  KEY `fk_chamado_paciente` (`id_paciente`),
  KEY `fk_chamado_func_abertura` (`id_funcionario_abertura`),
  KEY `fk_chamado_func_responsavel` (`id_funcionario_responsavel`),
  KEY `fk_chamado_categoria` (`id_categoria_chamado`),
  KEY `fk_chamado_prioridade` (`id_prioridade`),
  KEY `idx_chamado_status` (`id_status_chamado`),
  KEY `idx_chamado_setor` (`id_setor`),
  KEY `idx_chamado_data_abertura` (`data_abertura`),
  CONSTRAINT `fk_chamado_categoria` FOREIGN KEY (`id_categoria_chamado`) REFERENCES `categoria_chamado` (`id_categoria`) ON UPDATE CASCADE,
  CONSTRAINT `fk_chamado_func_abertura` FOREIGN KEY (`id_funcionario_abertura`) REFERENCES `funcionario` (`id_funcionario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_chamado_func_responsavel` FOREIGN KEY (`id_funcionario_responsavel`) REFERENCES `funcionario` (`id_funcionario`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_chamado_paciente` FOREIGN KEY (`id_paciente`) REFERENCES `paciente` (`id_paciente`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_chamado_prioridade` FOREIGN KEY (`id_prioridade`) REFERENCES `prioridade` (`id_prioridade`) ON UPDATE CASCADE,
  CONSTRAINT `fk_chamado_setor` FOREIGN KEY (`id_setor`) REFERENCES `setor` (`id_setor`) ON UPDATE CASCADE,
  CONSTRAINT `fk_chamado_status` FOREIGN KEY (`id_status_chamado`) REFERENCES `status_chamado` (`id_status`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chamado`
--

LOCK TABLES `chamado` WRITE;
/*!40000 ALTER TABLE `chamado` DISABLE KEYS */;
/*!40000 ALTER TABLE `chamado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `funcionario`
--

DROP TABLE IF EXISTS `funcionario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `funcionario` (
  `id_funcionario` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `matricula` varchar(20) NOT NULL,
  `cargo` varchar(80) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `id_setor` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo_usuario` enum('atendente','tecnico','admin') NOT NULL,
  `data_cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_funcionario`),
  UNIQUE KEY `cpf` (`cpf`),
  UNIQUE KEY `matricula` (`matricula`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_funcionario_setor` (`id_setor`),
  CONSTRAINT `fk_funcionario_setor` FOREIGN KEY (`id_setor`) REFERENCES `setor` (`id_setor`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `funcionario`
--

LOCK TABLES `funcionario` WRITE;
/*!40000 ALTER TABLE `funcionario` DISABLE KEYS */;
/*!40000 ALTER TABLE `funcionario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historico_chamado`
--

DROP TABLE IF EXISTS `historico_chamado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `historico_chamado` (
  `id_historico` int(11) NOT NULL AUTO_INCREMENT,
  `id_chamado` int(11) NOT NULL,
  `id_funcionario` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `data_hora` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_historico`),
  KEY `fk_historico_funcionario` (`id_funcionario`),
  KEY `idx_historico_chamado` (`id_chamado`),
  CONSTRAINT `fk_historico_chamado` FOREIGN KEY (`id_chamado`) REFERENCES `chamado` (`id_chamado`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_historico_funcionario` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionario` (`id_funcionario`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historico_chamado`
--

LOCK TABLES `historico_chamado` WRITE;
/*!40000 ALTER TABLE `historico_chamado` DISABLE KEYS */;
/*!40000 ALTER TABLE `historico_chamado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paciente`
--

DROP TABLE IF EXISTS `paciente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `paciente` (
  `id_paciente` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_paciente`),
  UNIQUE KEY `cpf` (`cpf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paciente`
--

LOCK TABLES `paciente` WRITE;
/*!40000 ALTER TABLE `paciente` DISABLE KEYS */;
/*!40000 ALTER TABLE `paciente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prioridade`
--

DROP TABLE IF EXISTS `prioridade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `prioridade` (
  `id_prioridade` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(20) NOT NULL,
  PRIMARY KEY (`id_prioridade`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prioridade`
--

LOCK TABLES `prioridade` WRITE;
/*!40000 ALTER TABLE `prioridade` DISABLE KEYS */;
INSERT INTO `prioridade` VALUES
(1,'Baixa'),
(2,'Media'),
(3,'Alta'),
(4,'Urgente');
/*!40000 ALTER TABLE `prioridade` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `setor`
--

DROP TABLE IF EXISTS `setor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `setor` (
  `id_setor` int(11) NOT NULL AUTO_INCREMENT,
  `nome_setor` varchar(100) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_setor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setor`
--

LOCK TABLES `setor` WRITE;
/*!40000 ALTER TABLE `setor` DISABLE KEYS */;
/*!40000 ALTER TABLE `setor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `status_chamado`
--

DROP TABLE IF EXISTS `status_chamado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `status_chamado` (
  `id_status` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(30) NOT NULL,
  PRIMARY KEY (`id_status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `status_chamado`
--

LOCK TABLES `status_chamado` WRITE;
/*!40000 ALTER TABLE `status_chamado` DISABLE KEYS */;
INSERT INTO `status_chamado` VALUES
(1,'Aberto'),
(2,'Em andamento'),
(3,'Resolvido'),
(4,'Cancelado');
/*!40000 ALTER TABLE `status_chamado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'sistema_chamados_hospital'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-09-28 19:19:18
