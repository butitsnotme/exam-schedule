-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 192.168.0.6:3306
-- Generation Time: Mar 03, 2016 at 09:28 PM
-- Server version: 10.0.16-MariaDB-1~wheezy-log
-- PHP Version: 5.6.14-0+deb8u1

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `exams`
--
CREATE DATABASE IF NOT EXISTS `exams` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `exams`;

-- --------------------------------------------------------

--
-- Table structure for table `Exams`
--

DROP TABLE IF EXISTS `Exams`;
CREATE TABLE `Exams` (
  `ID` int(11) NOT NULL,
  `Course` varchar(40) NOT NULL,
  `Number` varchar(40) DEFAULT NULL,
  `Section` varchar(40) NOT NULL,
  `Term` int(11) NOT NULL,
  `StartTime` datetime NOT NULL,
  `EndTime` datetime NOT NULL,
  `Location` varchar(255) NOT NULL,
  `Notes` varchar(255) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Terms`
--

DROP TABLE IF EXISTS `Terms`;
CREATE TABLE `Terms` (
  `ID` int(11) NOT NULL,
  `Term_ID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Year` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Exams`
--
ALTER TABLE `Exams`
ADD PRIMARY KEY (`ID`),
ADD UNIQUE KEY `Exam_ID` (`Course`,`Number`,`Section`,`Term`) USING BTREE,
ADD KEY `Course` (`Course`),
ADD KEY `Number` (`Number`),
ADD KEY `Term` (`Term`),
ADD KEY `Section` (`Section`);

--
-- Indexes for table `Terms`
--
ALTER TABLE `Terms`
ADD PRIMARY KEY (`ID`),
ADD UNIQUE KEY `Term_ID` (`Term_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Exams`
--
ALTER TABLE `Exams`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
--
-- AUTO_INCREMENT for table `Terms`
--
ALTER TABLE `Terms`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;
