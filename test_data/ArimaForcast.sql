-- phpMyAdmin SQL Dump
-- version 4.8.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 24, 2018 at 02:42 PM
-- Server version: 10.2.14-MariaDB
-- PHP Version: 7.1.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ArimaForcast`
--

-- --------------------------------------------------------

--
-- Table structure for table `forcastData`
--

CREATE TABLE `forcastData` (
  `id` int(11) NOT NULL,
  `value` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `forcastData`
--

INSERT INTO `forcastData` (`id`, `value`) VALUES
(1, 1156535),
(2, 1291442),
(3, 1359713),
(4, 1374810),
(5, 1416035),
(6, 1528192),
(7, 1521013),
(8, 1455094),
(9, 1371776),
(10, 1369609),
(11, 1392741),
(12, 1455568),
(13, 1534559),
(14, 1517461),
(15, 1384346),
(16, 1405234),
(17, 1413308),
(18, 1425039),
(19, 1470182),
(20, 1516333),
(21, 1542575),
(22, 1416539),
(23, 1377720),
(24, 1358558),
(25, 1400223),
(26, 1458195),
(27, 1543368),
(28, 1501977),
(29, 1357366),
(30, 1369052),
(31, 1447046),
(32, 1676666),
(33, 1628102),
(34, 1605383),
(35, 1530729),
(36, 1471185),
(37, 1466896),
(38, 1487514),
(39, 1501739),
(40, 1562044),
(41, 1689814),
(42, 1725907),
(43, 1503980),
(44, 1506300),
(45, 1494398),
(46, 1493466),
(47, 1540286),
(48, 1653948),
(49, 1654839),
(50, 1527258),
(51, 1566723),
(52, 1517780),
(53, 1518362),
(54, 1559158),
(55, 1659617),
(56, 1657210),
(57, 1490064),
(58, 1484474),
(59, 1462345),
(60, 1480193),
(61, 1518520),
(62, 1620768),
(63, 1760712),
(64, 1803999),
(65, 1510895),
(66, 1507146),
(67, 1557876),
(68, 1552917),
(69, 1686550);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `forcastData`
--
ALTER TABLE `forcastData`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `forcastData`
--
ALTER TABLE `forcastData`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
