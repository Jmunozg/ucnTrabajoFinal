-- phpMyAdmin SQL Dump
-- version 4.9.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 08, 2020 at 03:29 PM
-- Server version: 5.6.49-cll-lve
-- PHP Version: 7.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ucntrabajofinal`
--
CREATE DATABASE IF NOT EXISTS `ucntrabajofinal` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `ucntrabajofinal`;

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

CREATE TABLE `usuario` (
  `username` varchar(200) NOT NULL,
  `Nombre` varchar(500) NOT NULL,
  `Contrasena` blob NOT NULL,
  `Token` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `usuario`
--

INSERT INTO `usuario` (`username`, `Nombre`, `Contrasena`, `Token`) VALUES
('Admin', 'Admin', 0x24327924313024354d376630394b61486c5570497a6338434c36647765564b574e783648306261594276535861354a56503731527a37416746414761, '79467e408b72a785131f4696acc43802'),
('Jose', 'Jose Gomez', 0x243279243130244f6f5a3657336d7a432f66396162336f7030426e6f2e2e557a625a7a6f6b6d697365612f2f387032366a6e6c5534664a6642386b71, '80a3ef01c05688e5d99d573a40890d51'),
('Julian', 'Julian Alvarez', 0x243279243130246f4f436f63506463714f355054653849556264574c65324f6d7763442f474d574146306e725a35745371344d78736c547542336f32, '8fc6b1008722814bb00e1f1bba44c402'),
('Pau', 'Paulina Muñoz', 0x2432792431302437414736572e74367170316e7a6373776e575733342e63724b535033576b366f794c516d4a434f43714a676e66472f514279447136, 'febe1c7e1d046d00d6e28f91ade32942');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`username`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
