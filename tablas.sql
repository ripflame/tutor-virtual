/*
SQLyog Ultimate v11.11 (64 bit)
MySQL - 5.6.14 : Database - tutorvirtual
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`tutorvirtual` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `tutorvirtual`;

/*Table structure for table `alumno` */

DROP TABLE IF EXISTS `alumno`;

CREATE TABLE `alumno` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `matricula` varchar(8) NOT NULL DEFAULT '',
  `nombre` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(50) NOT NULL DEFAULT '',
  `fecha_registro` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `alumno` */

insert  into `alumno`(`id`,`matricula`,`nombre`,`password`,`fecha_registro`) values (1,'12216317','Fulanito Perez','123456','06/08/2012');

/*Table structure for table `asignatura` */

DROP TABLE IF EXISTS `asignatura`;

CREATE TABLE `asignatura` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL DEFAULT '',
  `creditos` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

/*Data for the table `asignatura` */

insert  into `asignatura`(`id`,`nombre`,`creditos`) values (1,'ÁLGEBRA SUPERIOR I',10),(2,'CÁLCULO I',23),(3,'COMPUTACIÓN I',10),(4,'ÁLGEBRA SUPERIOR II',10),(5,'CÁLCULO II',23),(6,'COMPUTACIÓN II',10),(7,'ÁLGEBRA LINEAL 1',10),(8,'ANÁLISIS NUMÉRICO I',10),(9,'ECUACIONES DIFERENCIALES I',10),(10,'ÁLGEBRA LINEAL II',10),(11,'ANÁLISIS NUMÉRICO II',10),(12,'ECUACIONES DIFERENCIALES II',10),(13,'CALCULO III',23);

/*Table structure for table `asignatura_requisito` */

DROP TABLE IF EXISTS `asignatura_requisito`;

CREATE TABLE `asignatura_requisito` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_obligatoria` int(11) unsigned NOT NULL,
  `id_requisito` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;

/*Data for the table `asignatura_requisito` */

insert  into `asignatura_requisito`(`id`,`id_obligatoria`,`id_requisito`) values (1,1,0),(2,2,0),(3,3,0),(4,4,1),(5,5,2),(6,6,1),(7,6,3),(8,6,4),(9,7,1),(10,7,4),(11,8,2),(12,8,3),(13,8,4),(14,8,5),(15,8,6),(16,8,7),(17,9,2),(18,9,4),(19,9,5),(20,9,7),(21,10,1),(22,10,4),(23,10,7),(24,11,2),(25,11,5),(26,11,6),(27,11,8),(28,11,9),(29,12,1),(30,12,2),(31,12,4),(32,12,5),(33,12,7),(34,12,13),(35,12,9);

/*Table structure for table `kardex` */

DROP TABLE IF EXISTS `kardex`;

CREATE TABLE `kardex` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `matricula` int(8) NOT NULL,
  `id_asignatura` int(11) unsigned NOT NULL,
  `situacion` int(1) unsigned NOT NULL DEFAULT '0',
  `tipo` int(1) unsigned NOT NULL DEFAULT '0',
  `periodo` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*Data for the table `kardex` */

insert  into `kardex`(`id`,`matricula`,`id_asignatura`,`situacion`,`tipo`,`periodo`) values (1,12216317,1,0,0,'2012-ago'),(2,12216317,2,0,0,'2012-ago'),(3,12216317,3,1,0,'2012-ago'),(4,12216317,1,0,0,'2013-ene'),(5,12216317,2,1,1,'2013-ene'),(6,12216317,5,1,0,'2013-ene'),(7,12216317,6,1,0,'2013-ene'),(8,12216317,7,0,0,'2013-ago'),(9,12216317,8,1,0,'2013-ago'),(10,12216317,9,1,0,'2013-ago'),(11,12216317,1,0,1,'2013-ago');

/*Table structure for table `oferta` */

DROP TABLE IF EXISTS `oferta`;

CREATE TABLE `oferta` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_asignatura` int(11) unsigned NOT NULL,
  `profesor` varchar(50) NOT NULL DEFAULT '',
  `lunes` varchar(11) NOT NULL DEFAULT '',
  `martes` varchar(11) NOT NULL DEFAULT '',
  `miercoles` varchar(11) NOT NULL DEFAULT '',
  `jueves` varchar(11) NOT NULL DEFAULT '',
  `viernes` varchar(11) NOT NULL DEFAULT '',
  `periodo` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

/*Data for the table `oferta` */

insert  into `oferta`(`id`,`id_asignatura`,`profesor`,`lunes`,`martes`,`miercoles`,`jueves`,`viernes`,`periodo`) values (1,7,'A','12:00-13:20','','12:00-13:20','','12:00-13:20','2014-ene'),(2,7,'B','','12:00-13:20','12:00-13:20','12:00-13:20','','2014-ene'),(3,10,'A','15:00-16:20','','15:00-16:20','','15:00-16:20','2014-ene'),(4,10,'B','16:30-17:50','','16:30-17:50','','16:30-17:50','2014-ene'),(5,11,'A','','12:00-13:20','12:00-13:20','12:00-13:20','','2014-ene'),(6,11,'B','12:00-13:20','','12:00-13:20','','12:00-13:20','2014-ene'),(7,11,'C','','15:00-16:20','15:00-16:20','','15:00-16:20','2014-ene'),(8,12,'A','15:00-16:20','','16:30-17:50','','15:00-16:20','2014-ene'),(9,12,'B','15:00-16:20','','15:00-16:20','','15:00-16:20','2014-ene'),(10,12,'C','','12:00-13:20','12:00-13:20','12:00-13:20','','2014-ene');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
