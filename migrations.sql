-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 24, 2023 at 06:57 AM
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
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2022_09_05_085756_create_invoice_terms_table', 1),
(6, '2022_09_05_091528_create_permission_tables', 1),
(7, '2022_09_05_152802_create_companies_table', 1),
(8, '2022_09_05_152944_create_discounts_table', 1),
(9, '2022_09_05_171942_create_accounts_table', 1),
(10, '2022_09_06_083948_create_sales_people_table', 1),
(11, '2022_09_06_100007_create_products_table', 1),
(12, '2022_09_06_133859_create_account_user', 1),
(13, '2022_09_06_151412_create_branches_table', 1),
(14, '2022_09_07_080138_create_account_logins_table', 1),
(15, '2022_09_08_111751_create_price_codes_table', 1),
(16, '2022_09_09_071111_create_shipping_addresses_table', 1),
(17, '2022_09_09_080014_create_sales_orders_table', 1),
(18, '2022_09_09_082749_create_sales_order_products_table', 1),
(19, '2022_09_13_082419_create_sales_order_product_uoms_table', 1),
(20, '2022_09_13_152931_create_settings_table', 1),
(21, '2022_09_15_130213_create_account_sales_person', 1),
(22, '2022_09_23_103427_create_activity_log_table', 1),
(23, '2022_09_26_170051_create_purchase_order_numbers_table', 1),
(24, '2022_09_28_091332_create_operation_processes_table', 1),
(25, '2022_09_28_091622_create_activities_table', 1),
(26, '2022_09_28_100944_create_branch_logins_table', 1),
(27, '2022_09_28_145545_create_branch_login_activities_table', 1),
(28, '2022_09_28_165439_create_branch_user', 1),
(29, '2022_09_29_160337_create_user_branch_schedules_table', 1),
(30, '2022_09_06_150000_create_regions_table', 1),
(31, '2022_09_06_150001_create_classifications_table', 1),
(32, '2022_09_06_150002_create_areas_table', 1),
(33, '2022_10_05_153807_create_account_product', 1),
(34, '2022_10_11_112347_create_account_product_references_table', 1),
(35, '2022_10_13_130735_create_user_branch_schedule_approvals_table', 1),
(36, '2022_10_18_083019_create_notifications_table', 1),
(37, '2022_11_04_162544_create_job_titles_table', 1),
(38, '2022_11_04_162742_create_organization_structures_table', 1),
(39, '2022_11_08_084404_create_activity_plans_table', 1),
(40, '2022_11_08_084841_create_activity_plan_details_table', 1),
(41, '2022_11_08_085343_create_activity_plan_approvals_table', 1),
(42, '2022_11_16_153459_create_weekly_activity_reports_table', 1),
(43, '2022_11_16_154219_create_weekly_activity_report_objectives_table', 1),
(44, '2022_11_16_154849_create_weekly_activity_report_areas_table', 1),
(45, '2022_11_16_155613_create_weekly_activity_report_collections_table', 1),
(46, '2022_11_16_160438_create_weekly_activity_report_action_plans_table', 1),
(47, '2022_11_16_160928_create_weekly_activity_report_activities_table', 1),
(48, '2022_11_18_113329_create_deviations_table', 1),
(49, '2022_11_18_113859_create_deviation_schedules_table', 1),
(50, '2022_11_18_114852_create_deviation_approvals_table', 1),
(51, '2022_11_23_090826_create_weekly_activity_report_approvals_table', 1),
(52, '2022_12_13_101032_create_sales_order_cut_offs_table', 1),
(53, '2023_02_01_105016_create_cost_centers_table', 1),
(54, '2023_02_02_105059_create_holidays_table', 1),
(55, '2023_02_03_102314_create_activity_plan_detail_activities_table', 1),
(56, '2023_03_21_133400_create_districts_table', 1),
(57, '2023_03_21_133531_create_territories_table', 1),
(58, '2023_03_21_134259_create_district_user', 1),
(59, '2023_03_21_134736_create_territory_branch', 1),
(60, '2023_05_02_164825_create_branch_uploads_table', 1),
(61, '2023_05_05_080558_create_reminders_table', 1),
(62, '2023_05_26_093901_create_salesmen_table', 1),
(63, '2023_05_26_112729_create_salesmen_locations_table', 1),
(64, '2023_05_26_133414_create_productivity_reports_table', 1),
(65, '2023_05_26_133502_create_productivity_report_data_table', 1),
(66, '2023_05_31_154052_create_channel_operations_table', 1),
(67, '2023_05_31_154531_create_channel_operation_merch_updates_table', 1),
(68, '2023_05_31_154951_create_channel_operation_trade_displays_table', 1),
(69, '2023_05_31_162754_create_channel_operation_display_rentals_table', 1),
(70, '2023_05_31_163501_create_channel_operation_extra_displays_table', 1),
(71, '2023_05_31_163501_create_channel_operation_extra_displays_table', 1),
(72, '2023_06_01_080011_create_channel_operation_competetive_reports_table', 1),
(73, '2023_06_01_093002_create_pafs_table', 1),
(74, '2023_06_01_093202_create_paf_details_table', 1),
(75, '2023_06_01_134503_create_channel_operation_trade_marketing_activities_table', 1),
(76, '2023_06_01_134704_create_channel_operation_trade_marketing_activity_skus_table', 1),
(77, '2023_10_24_142721_create_activity_plan_detail_trips_table', 2);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
