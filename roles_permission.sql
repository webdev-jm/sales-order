-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 07, 2023 at 12:06 AM
-- Server version: 8.0.33
-- PHP Version: 8.1.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sales_order_entries`
--

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(14, 'App\\Models\\User', 2),
(2, 'App\\Models\\User', 3),
(2, 'App\\Models\\User', 4),
(11, 'App\\Models\\User', 5),
(2, 'App\\Models\\User', 6),
(2, 'App\\Models\\User', 7),
(2, 'App\\Models\\User', 8),
(2, 'App\\Models\\User', 9),
(2, 'App\\Models\\User', 10),
(2, 'App\\Models\\User', 11),
(2, 'App\\Models\\User', 12),
(2, 'App\\Models\\User', 13),
(2, 'App\\Models\\User', 14),
(2, 'App\\Models\\User', 15),
(2, 'App\\Models\\User', 16),
(2, 'App\\Models\\User', 17),
(2, 'App\\Models\\User', 18),
(2, 'App\\Models\\User', 19),
(2, 'App\\Models\\User', 20),
(5, 'App\\Models\\User', 21),
(12, 'App\\Models\\User', 21),
(2, 'App\\Models\\User', 22),
(2, 'App\\Models\\User', 23),
(2, 'App\\Models\\User', 24),
(2, 'App\\Models\\User', 25),
(2, 'App\\Models\\User', 26),
(7, 'App\\Models\\User', 27),
(7, 'App\\Models\\User', 28),
(7, 'App\\Models\\User', 29),
(7, 'App\\Models\\User', 30),
(7, 'App\\Models\\User', 31),
(7, 'App\\Models\\User', 32),
(2, 'App\\Models\\User', 33),
(2, 'App\\Models\\User', 34),
(3, 'App\\Models\\User', 35),
(3, 'App\\Models\\User', 36),
(3, 'App\\Models\\User', 37),
(2, 'App\\Models\\User', 38),
(2, 'App\\Models\\User', 39),
(1, 'App\\Models\\User', 40),
(4, 'App\\Models\\User', 40),
(5, 'App\\Models\\User', 41),
(12, 'App\\Models\\User', 41),
(5, 'App\\Models\\User', 42),
(2, 'App\\Models\\User', 43),
(2, 'App\\Models\\User', 44),
(6, 'App\\Models\\User', 44),
(3, 'App\\Models\\User', 45),
(8, 'App\\Models\\User', 46),
(2, 'App\\Models\\User', 47),
(2, 'App\\Models\\User', 48),
(9, 'App\\Models\\User', 49),
(10, 'App\\Models\\User', 50),
(10, 'App\\Models\\User', 51),
(11, 'App\\Models\\User', 52),
(11, 'App\\Models\\User', 53),
(11, 'App\\Models\\User', 54),
(1, 'App\\Models\\User', 55),
(4, 'App\\Models\\User', 55),
(2, 'App\\Models\\User', 56),
(8, 'App\\Models\\User', 57),
(12, 'App\\Models\\User', 57),
(1, 'App\\Models\\User', 58),
(8, 'App\\Models\\User', 59);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=139 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `module`, `description`, `created_at`, `updated_at`) VALUES
(1, 'sales order list', 'web', 'Sales Order', 'access to sales order lists', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(2, 'sales order change status', 'web', 'Sales Order', 'access to change upload error status sales orders', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(3, 'sales order access', 'web', 'Sales Order', 'access to sales order module', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(4, 'sales order create', 'web', 'Sales Order', 'access to create sales order', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(5, 'sales order edit', 'web', 'Sales Order', 'access to edit sales order', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(6, 'sales order delete', 'web', 'Sales Order', 'access to delete sales order', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(7, 'schedule access', 'web', 'Schedule', 'access to schedule module', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(8, 'schedule create', 'web', 'Schedule', 'access to create schedule', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(9, 'schedule list', 'web', 'Schedule', 'access to view schedule list', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(10, 'schedule reschedule', 'web', 'Schedule', 'access to create reschedule', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(11, 'schedule approve request', 'web', 'Schedule', 'access to approve schedule request', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(12, 'schedule delete request', 'web', 'Schedule', 'access to create delete schedule request', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(13, 'schedule approve reschedule', 'web', 'Schedule', 'access to approve reschedule request', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(14, 'schedule approve delete request', 'web', 'Schedule', 'access to approve delete schedule request', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(15, 'schedule approve deviation', 'web', 'Schedule', 'access to approve deviation request', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(16, 'report access', 'web', 'Report', 'access to reports', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(17, 'report export', 'web', 'Report', 'access to export raw data of reports', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(18, 'sales dashboard', 'web', 'Sales Dashboard', 'access to live sales dashboard', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(19, 'mcp access', 'web', 'Activity Plan/MCP', 'access to activity plan module', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(20, 'mcp create', 'web', 'Activity Plan/MCP', 'access to create activity plan', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(21, 'mcp edit', 'web', 'Activity Plan/MCP', 'access to edit activity plan', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(22, 'mcp delete', 'web', 'Activity Plan/MCP', 'access to delete activity plan', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(23, 'mcp approval', 'web', 'Activity Plan/MCP', 'access to approve activity plan', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(24, 'mcp confirmation', 'web', 'Activity Plan/MCP', 'access to confirm activity plan', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(25, 'war access', 'web', 'Weekly Activity Report', 'access to weekly activity report module', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(26, 'war create', 'web', 'Weekly Activity Report', 'access to create weekly activity report', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(27, 'war edit', 'web', 'Weekly Activity Report', 'access to edit weekly activity report', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(28, 'war delete', 'web', 'Weekly Activity Report', 'access to delete weekly activity report', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(29, 'war approve', 'web', 'Weekly Activity Report', 'access to approve weekly activity report', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(30, 'productivity report access', 'web', 'Productivity Report', 'access to productivity report module', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(31, 'productivity report upload', 'web', 'Productivity Report', 'access to upload productivity report', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(32, 'salesman access', 'web', 'Salesman', 'access to salesman module', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(33, 'salesman create', 'web', 'Salesman', 'access to create salesman', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(34, 'salesman edit', 'web', 'Salesman', 'access to edit salesman', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(35, 'salesman delete', 'web', 'Salesman', 'access to delete salesman', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(36, 'salesmen location access', 'web', 'Salesman Location', 'access to salesman location module', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(37, 'salesmen location create', 'web', 'Salesman Location', 'access to create salesman location', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(38, 'salesmen location edit', 'web', 'Salesman Location', 'access to edit salesman location', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(39, 'salesmen location delete', 'web', 'Salesman Location', 'access to delete salesman location', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(40, 'channel operation report', 'web', 'Channel Operation', 'access to channel operation reports', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(41, 'channel operation print', 'web', 'Channel Operation', 'access to print channel operation report data', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(42, 'channel operation list', 'web', 'Channel Operation', 'access to channel operation submission list', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(43, 'so cut-off access', 'web', 'Sales Order Cut-off', 'access to sales order cut-off module', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(44, 'so cut-off create', 'web', 'Sales Order Cut-off', 'access to create sales order cut-off', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(45, 'so cut-off edit', 'web', 'Sales Order Cut-off', 'access to edit sales order cut-off', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(46, 'so cut-off delete', 'web', 'Sales Order Cut-off', 'access to delete sales order cut-off', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(47, 'company access', 'web', 'Company', 'access to company module', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(48, 'company create', 'web', 'Company', 'access to create company', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(49, 'company edit', 'web', 'Company', 'access to edit company', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(50, 'company delete', 'web', 'Company', 'access tp delete company', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(51, 'discount access', 'web', 'Discount', 'access to discount module', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(52, 'discount create', 'web', 'Discount', 'access to create discount', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(53, 'discount edit', 'web', 'Discount', 'access to edit discount', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(54, 'discount delete', 'web', 'Discount', 'access to delete discount', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(55, 'account access', 'web', 'Account', 'access to account module', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(56, 'account create', 'web', 'Account', 'access to create account', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(57, 'account edit', 'web', 'Account', 'access to edit account', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(58, 'account delete', 'web', 'Account', 'access to delete account', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(59, 'account reference access', 'web', 'Account Reference', 'access to account reference module', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(60, 'account reference create', 'web', 'Account Reference', 'access to create account reference', '2023-11-06 01:44:28', '2023-11-06 01:44:28'),
(61, 'account reference edit', 'web', 'Account Reference', 'access to edit account reference', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(62, 'account reference delete', 'web', 'Account Reference', 'access to delete account reference', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(63, 'shipping address access', 'web', 'Shipping Address', 'access to shipping address module', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(64, 'shipping address create', 'web', 'Shipping Address', 'access to create shipping address', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(65, 'shipping address edit', 'web', 'Shipping Address', 'access to edit shipping address', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(66, 'shipping address delete', 'web', 'Shipping Address', 'access to delete shipping address', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(67, 'branch access', 'web', 'Branches', 'access to branches module', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(68, 'branch create', 'web', 'Branches', 'access to create branches', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(69, 'branch edit', 'web', 'Branches', 'access to edit branches', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(70, 'branch delete', 'web', 'Branches', 'access to delete branches', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(71, 'region access', 'web', 'Regions', 'access to regions module', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(72, 'region create', 'web', 'Regions', 'access to create regions', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(73, 'region edit', 'web', 'Regions', 'access to edit regions', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(74, 'region delete', 'web', 'Regions', 'access to delete regions', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(75, 'classification access', 'web', 'Classification', 'access to classification module', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(76, 'classification create', 'web', 'Classification', 'access to create classification', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(77, 'classification edit', 'web', 'Classification', 'access to edit classification', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(78, 'classification delete', 'web', 'Classification', 'access to delete classification', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(79, 'area access', 'web', 'Area', 'access to area module', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(80, 'area create', 'web', 'Area', 'access to create area', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(81, 'area edit', 'web', 'Area', 'access to edit area', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(82, 'area delete', 'web', 'Area', 'access to delete area', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(83, 'invoice term access', 'web', 'Invoice Terms', 'access to invoice terms module', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(84, 'invoice term create', 'web', 'Invoice Terms', 'access to create invoice terms', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(85, 'invoice term edit', 'web', 'Invoice Terms', 'access to edit invoice terms', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(86, 'invoice term delete', 'web', 'Invoice Terms', 'access to delete invoice terms', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(87, 'product access', 'web', 'Products', 'access to products module', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(88, 'product create', 'web', 'Products', 'access to create products', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(89, 'product edit', 'web', 'Products', 'access to edit products', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(90, 'product delete', 'web', 'Products', 'access to delete products', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(91, 'price code access', 'web', 'Price Code', 'access to price code module', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(92, 'price code create', 'web', 'Price Code', 'access to create price code', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(93, 'price code edit', 'web', 'Price Code', 'access to edit price code', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(94, 'price code delete', 'web', 'Price Code', 'access to delete price code', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(95, 'sales people access', 'web', 'Sales People', 'access to sales people module', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(96, 'sales person create', 'web', 'Sales People', 'access to create sales person', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(97, 'sales person edit', 'web', 'Sales People', 'access to edit sales person', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(98, 'sales person delete', 'web', 'Sales People', 'access to delete sales person', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(99, 'operation process access', 'web', 'Operation Process', 'access to operation process module', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(100, 'operation process create', 'web', 'Operation Process', 'access to create operation process', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(101, 'operation process edit', 'web', 'Operation Process', 'access to edit operation process', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(102, 'operation process delete', 'web', 'Operation Process', 'access to delete operation process', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(103, 'cost center access', 'web', 'Cost Center', 'access to cost center module', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(104, 'cost center create', 'web', 'Cost Center', 'access to create cost center', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(105, 'cost center edit', 'web', 'Cost Center', 'access to edit cost center', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(106, 'cost center delete', 'web', 'Cost Center', 'access to delete cost center', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(107, 'district access', 'web', 'District', 'access to district module', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(108, 'district create', 'web', 'District', 'access to create district', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(109, 'district edit', 'web', 'District', 'access to edit district', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(110, 'district delete', 'web', 'District', 'access to delete district', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(111, 'district assign', 'web', 'District', 'assign districts', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(112, 'territory access', 'web', 'Territory', 'access to territory module', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(113, 'territory create', 'web', 'Territory', 'access to create territory', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(114, 'territory edit', 'web', 'Territory', 'access to edit territory', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(115, 'territory delete', 'web', 'Territory', 'access to delete territory', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(116, 'territory assign', 'web', 'Territory', 'assign territories', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(117, 'holiday access', 'web', 'Holiday', 'access to holiday module', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(118, 'holiday create', 'web', 'Holiday', 'access to create holiday', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(119, 'holiday edit', 'web', 'Holiday', 'access to edit holiday', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(120, 'holiday delete', 'web', 'Holiday', 'access to delete holiday', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(121, 'account login access', 'web', 'Account Logins', 'access to account logins', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(122, 'account login export', 'web', 'Account Logins', 'export account logins', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(123, 'user access', 'web', 'Users', 'access to users', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(124, 'user create', 'web', 'Users', 'create users', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(125, 'user upload', 'web', 'Users', 'upload users', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(126, 'user edit', 'web', 'Users', 'edit users', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(127, 'user change password', 'web', 'Users', 'change user passwords', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(128, 'user delete', 'web', 'Users', 'delete users', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(129, 'organizational structure access', 'web', 'Organizational Structure', 'access to organizational structure', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(130, 'organizational structure create', 'web', 'Organizational Structure', 'create organizational structure', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(131, 'organizational structure edit', 'web', 'Organizational Structure', 'edit organizational structure', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(132, 'organizational structure delete', 'web', 'Organizational Structure', 'delete organizational structure', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(133, 'role access', 'web', 'Roles', 'access to roles', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(134, 'role create', 'web', 'Roles', 'create roles', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(135, 'role edit', 'web', 'Roles', 'edit roles', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(136, 'role delete', 'web', 'Roles', 'delete roles', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(137, 'system logs', 'web', 'System Log', 'system logs', '2023-11-06 01:44:29', '2023-11-06 01:44:29'),
(138, 'settings', 'web', 'Setting', 'settings access', '2023-11-06 01:44:29', '2023-11-06 01:44:29');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'superadmin', 'web', '2022-11-08 01:15:08', '2022-11-08 01:15:08'),
(2, 'user', 'web', '2022-11-08 01:15:08', '2022-11-08 01:15:08'),
(3, 'finance', 'web', '2022-11-08 08:57:04', '2022-11-08 08:57:04'),
(4, 'admin', 'web', '2022-11-08 08:58:01', '2022-11-08 08:58:01'),
(5, 'NSM', 'web', '2022-11-24 10:53:26', '2022-11-24 10:53:26'),
(6, 'sales', 'web', '2023-01-16 14:53:35', '2023-01-16 15:06:32'),
(7, 'MCP', 'web', '2023-02-06 08:22:20', '2023-02-06 08:22:20'),
(8, 'SCM', 'web', '2023-03-21 08:51:25', '2023-03-21 08:51:25'),
(9, 'CMD', 'web', '2023-05-12 08:20:49', '2023-05-12 08:20:49'),
(10, 'marketing', 'web', '2023-06-05 14:39:36', '2023-06-05 14:39:36'),
(11, 'MCP Access', 'web', '2023-06-15 08:53:56', '2023-06-15 08:53:56'),
(12, 'Sales Order', 'web', '2023-06-26 17:05:47', '2023-06-26 17:05:47'),
(13, 'sample', 'web', '2023-08-03 15:30:14', '2023-08-03 15:30:14'),
(14, 'COE', 'web', '2023-08-29 09:27:03', '2023-08-29 09:27:03');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 1),
(55, 1),
(56, 1),
(57, 1),
(58, 1),
(59, 1),
(60, 1),
(61, 1),
(62, 1),
(63, 1),
(64, 1),
(65, 1),
(66, 1),
(67, 1),
(68, 1),
(69, 1),
(70, 1),
(71, 1),
(72, 1),
(73, 1),
(74, 1),
(75, 1),
(76, 1),
(77, 1),
(78, 1),
(79, 1),
(80, 1),
(81, 1),
(82, 1),
(83, 1),
(84, 1),
(85, 1),
(86, 1),
(87, 1),
(88, 1),
(89, 1),
(90, 1),
(91, 1),
(92, 1),
(93, 1),
(94, 1),
(95, 1),
(102, 1),
(103, 1),
(104, 1),
(105, 1),
(106, 1),
(107, 1),
(108, 1),
(109, 1),
(110, 1),
(111, 1),
(112, 1),
(113, 1),
(114, 1),
(115, 1),
(116, 1),
(117, 1),
(118, 1),
(119, 1),
(120, 1),
(121, 1),
(122, 1),
(123, 1),
(124, 1),
(125, 1),
(126, 1),
(127, 1),
(128, 1),
(129, 1),
(130, 1),
(131, 1),
(132, 1),
(133, 1),
(134, 1),
(135, 1),
(136, 1),
(137, 1),
(138, 1),
(3, 2),
(4, 2),
(5, 2),
(7, 2),
(10, 2),
(12, 2),
(15, 2),
(19, 2),
(20, 2),
(21, 2),
(25, 2),
(26, 2),
(27, 2),
(67, 2),
(1, 3),
(3, 3),
(7, 3),
(16, 3),
(17, 3),
(19, 3),
(25, 3),
(67, 3),
(1, 4),
(2, 4),
(3, 4),
(4, 4),
(5, 4),
(6, 4),
(7, 4),
(8, 4),
(9, 4),
(10, 4),
(11, 4),
(12, 4),
(13, 4),
(14, 4),
(15, 4),
(16, 4),
(17, 4),
(19, 4),
(20, 4),
(21, 4),
(23, 4),
(25, 4),
(32, 4),
(36, 4),
(43, 4),
(47, 4),
(51, 4),
(55, 4),
(59, 4),
(63, 4),
(67, 4),
(71, 4),
(75, 4),
(79, 4),
(83, 4),
(87, 4),
(91, 4),
(99, 4),
(121, 4),
(122, 4),
(123, 4),
(124, 4),
(126, 4),
(129, 4),
(130, 4),
(131, 4),
(1, 5),
(3, 5),
(7, 5),
(9, 5),
(11, 5),
(13, 5),
(14, 5),
(15, 5),
(16, 5),
(17, 5),
(19, 5),
(20, 5),
(21, 5),
(23, 5),
(25, 5),
(26, 5),
(27, 5),
(29, 5),
(7, 6),
(9, 6),
(15, 6),
(16, 6),
(19, 6),
(25, 6),
(102, 6),
(7, 7),
(10, 7),
(12, 7),
(19, 7),
(20, 7),
(21, 7),
(25, 7),
(26, 7),
(27, 7),
(1, 8),
(3, 8),
(1, 9),
(3, 9),
(7, 9),
(9, 9),
(11, 9),
(13, 9),
(14, 9),
(15, 9),
(16, 9),
(17, 9),
(19, 9),
(23, 9),
(25, 9),
(29, 9),
(7, 10),
(16, 10),
(19, 10),
(7, 11),
(8, 11),
(19, 11),
(20, 11),
(3, 12),
(4, 12),
(5, 12),
(6, 12),
(1, 13),
(40, 14),
(41, 14),
(42, 14);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
