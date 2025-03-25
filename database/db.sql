-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 22, 2025 at 07:36 PM
-- Server version: 8.0.41-0ubuntu0.22.04.1
-- PHP Version: 8.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `NewEcsaRewportingV3`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clusters`
--

CREATE TABLE `clusters` (
  `id` bigint UNSIGNED NOT NULL,
  `Description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Cluster_Name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ClusterID` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cluster_indicator_targets`
--

CREATE TABLE `cluster_indicator_targets` (
  `id` bigint UNSIGNED NOT NULL,
  `ClusterTargetID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `ClusterID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `IndicatorID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Target_Year` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Target_Value` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `ResponseType` enum('Text','Number','Boolean','Yes/No') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `Baseline2024` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cluster_performance_mappings`
--

CREATE TABLE `cluster_performance_mappings` (
  `id` bigint UNSIGNED NOT NULL,
  `ClusterID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `ReportingID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `SO_ID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `UserID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `IndicatorID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Response` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `ReportingComment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `ResponseType` enum('Text','Number','Boolean','Yes/No') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Baseline_2023_2024` int DEFAULT NULL,
  `Target_Year1` int DEFAULT NULL,
  `Target_Year2` int DEFAULT NULL,
  `Target_Year3` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `dashboard_cluster_completeness_summary`
-- (See below for the actual view)
--
CREATE TABLE `dashboard_cluster_completeness_summary` (
`cluster_pk` bigint unsigned
,`cluster_text_identifier` varchar(200)
,`cluster_name` varchar(255)
,`cluster_description` varchar(255)
,`timeline_pk` bigint unsigned
,`timeline_name` enum('First Quarter (Q1): July 1 - September 30','Second Quarter (Q2): October 1 - December 31','Third Quarter (Q3): January 1 - March 31','Fourth Quarter (Q4): April 1 - June 30')
,`timeline_type` enum('Quarterly Reports')
,`timeline_description` text
,`timeline_reporting_id` varchar(255)
,`timeline_year` int
,`timeline_quarter` tinyint
,`timeline_closing_date` date
,`timeline_status` enum('Pending','In Progress','Completed')
,`total_indicators` bigint
,`reported_indicators` decimal(23,0)
,`not_reported_indicators` decimal(23,0)
,`completeness_percentage` decimal(5,2)
,`considered_reports` text
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `dashboard_cluster_quarterly_rank`
-- (See below for the actual view)
--
CREATE TABLE `dashboard_cluster_quarterly_rank` (
`cluster_pk` bigint unsigned
,`cluster_code` varchar(200)
,`cluster_name` varchar(255)
,`timeline_year` int
,`timeline_quarter` tinyint
,`final_score_percent` decimal(6,2)
,`cluster_rank` bigint unsigned
,`status_label` varchar(15)
,`comment` varchar(13)
,`considered_reports` text
);

-- --------------------------------------------------------

--
-- Table structure for table `ecsahc_timelines`
--

CREATE TABLE `ecsahc_timelines` (
  `id` bigint UNSIGNED NOT NULL,
  `ReportName` enum('First Quarter (Q1): July 1 - September 30','Second Quarter (Q2): October 1 - December 31','Third Quarter (Q3): January 1 - March 31','Fourth Quarter (Q4): April 1 - June 30') NOT NULL,
  `Type` enum('Quarterly Reports') NOT NULL,
  `Description` text,
  `ReportingID` varchar(255) NOT NULL,
  `Year` int NOT NULL,
  `ClosingDate` date DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `Quarter` tinyint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `performance_indicators`
--

CREATE TABLE `performance_indicators` (
  `id` bigint UNSIGNED NOT NULL,
  `SO_ID` varchar(255) NOT NULL,
  `Indicator_Number` varchar(10) NOT NULL,
  `Indicator_Name` varchar(255) NOT NULL,
  `ResponseType` enum('Text','Number','Boolean','Yes/No') NOT NULL,
  `Responsible_Cluster` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `strategic_objectives`
--

CREATE TABLE `strategic_objectives` (
  `id` bigint UNSIGNED NOT NULL,
  `SO_Name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `SO_Number` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `StrategicObjectiveID` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `EntityID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `ClusterID` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `UserType` enum('MPA','ECSA-HC') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'ECSA-HC',
  `UserCode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `Phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `Nationality` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `PhoneNumber` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `Address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `ParentOrganization` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `Sex` enum('Male','Female') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `JobTitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `AccountRole` enum('Admin','User','Cluster Head') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'User',
  `UserID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_annual_indicator_performance`
-- (See below for the actual view)
--
CREATE TABLE `vw_annual_indicator_performance` (
`cluster_pk` bigint unsigned
,`cluster_code` varchar(200)
,`cluster_name` varchar(255)
,`so_pk` bigint unsigned
,`so_number` varchar(200)
,`so_name` varchar(255)
,`indicator_pk` bigint unsigned
,`indicator_number` varchar(10)
,`indicator_name` varchar(255)
,`indicator_response_type` enum('Text','Number','Boolean','Yes/No')
,`timeline_year` int
,`reporting_periods` text
,`num_periods` bigint
,`annual_actual` decimal(42,4)
,`annual_target` decimal(42,4)
,`achievement_percent` decimal(48,2)
,`status_label` varchar(15)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_annual_performance_dashboard`
-- (See below for the actual view)
--
CREATE TABLE `vw_annual_performance_dashboard` (
`year_val` int
,`cluster_pk` bigint unsigned
,`cluster_code` varchar(200)
,`cluster_name` varchar(255)
,`so_pk` bigint unsigned
,`so_number` varchar(200)
,`so_name` varchar(255)
,`indicator_pk` bigint unsigned
,`indicator_number` varchar(10)
,`indicator_name` varchar(255)
,`annual_actual` decimal(42,4)
,`annual_target` decimal(42,4)
,`achievement_percent` decimal(48,2)
,`contributing_timelines` text
,`contributing_report_names` text
,`reporting_periods_count` bigint
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_cluster_completeness_summary`
-- (See below for the actual view)
--
CREATE TABLE `vw_cluster_completeness_summary` (
`cluster_pk` bigint unsigned
,`cluster_text_identifier` varchar(200)
,`cluster_name` varchar(255)
,`cluster_description` varchar(255)
,`timeline_pk` bigint unsigned
,`timeline_name` enum('First Quarter (Q1): July 1 - September 30','Second Quarter (Q2): October 1 - December 31','Third Quarter (Q3): January 1 - March 31','Fourth Quarter (Q4): April 1 - June 30')
,`timeline_type` enum('Quarterly Reports')
,`timeline_description` text
,`timeline_reporting_id` varchar(255)
,`timeline_year` int
,`timeline_quarter` tinyint
,`timeline_closing_date` date
,`timeline_status` enum('Pending','In Progress','Completed')
,`total_indicators` bigint
,`reported_indicators` decimal(23,0)
,`not_reported_indicators` decimal(23,0)
,`completeness_percentage` decimal(5,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_cluster_indicator_targets_full`
-- (See below for the actual view)
--
CREATE TABLE `vw_cluster_indicator_targets_full` (
`target_pk` bigint unsigned
,`ClusterTargetID` varchar(255)
,`cluster_id_db` varchar(255)
,`target_indicator_id` varchar(255)
,`Target_Year` varchar(200)
,`Target_Value` varchar(200)
,`target_response_type` enum('Text','Number','Boolean','Yes/No')
,`Baseline2024` varchar(200)
,`target_created_at` timestamp
,`target_updated_at` timestamp
,`cluster_pk` bigint unsigned
,`cluster_description` varchar(255)
,`cluster_name` varchar(255)
,`cluster_created_at` timestamp
,`cluster_updated_at` timestamp
,`cluster_text_identifier` varchar(200)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_cluster_performance_mappings_full`
-- (See below for the actual view)
--
CREATE TABLE `vw_cluster_performance_mappings_full` (
`performance_pk` bigint unsigned
,`cluster_id_db` varchar(255)
,`ReportingID` varchar(255)
,`SO_ID` varchar(255)
,`UserID` varchar(255)
,`IndicatorID` varchar(255)
,`Response` varchar(255)
,`ReportingComment` text
,`performance_response_type` enum('Text','Number','Boolean','Yes/No')
,`Baseline_2023_2024` int
,`Target_Year1` int
,`Target_Year2` int
,`Target_Year3` int
,`performance_created_at` timestamp
,`performance_updated_at` timestamp
,`cluster_pk` bigint unsigned
,`cluster_description` varchar(255)
,`cluster_name` varchar(255)
,`cluster_created_at` timestamp
,`cluster_updated_at` timestamp
,`cluster_text_identifier` varchar(200)
,`so_pk` bigint unsigned
,`SO_Name` varchar(255)
,`so_description` text
,`SO_Number` varchar(200)
,`StrategicObjectiveID` varchar(200)
,`so_created_at` timestamp
,`so_updated_at` timestamp
,`timeline_pk` bigint unsigned
,`ReportName` enum('First Quarter (Q1): July 1 - September 30','Second Quarter (Q2): October 1 - December 31','Third Quarter (Q3): January 1 - March 31','Fourth Quarter (Q4): April 1 - June 30')
,`timeline_type` enum('Quarterly Reports')
,`timeline_description` text
,`timeline_reporting_id` varchar(255)
,`timeline_year` int
,`timeline_closing_date` date
,`timeline_status` enum('Pending','In Progress','Completed')
,`timeline_created_at` timestamp
,`timeline_updated_at` timestamp
,`timeline_quarter` tinyint
,`user_pk` bigint unsigned
,`user_name` varchar(255)
,`user_email` varchar(255)
,`user_cluster_id` varchar(200)
,`user_type` enum('MPA','ECSA-HC')
,`user_code` varchar(255)
,`user_phone` varchar(20)
,`user_nationality` varchar(100)
,`user_address` text
,`user_parent_org` varchar(255)
,`user_sex` enum('Male','Female')
,`user_job_title` varchar(255)
,`user_account_role` enum('Admin','User','Cluster Head')
,`user_created_at` timestamp
,`user_updated_at` timestamp
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_cluster_rank_annual`
-- (See below for the actual view)
--
CREATE TABLE `vw_cluster_rank_annual` (
`cluster_pk` bigint unsigned
,`cluster_code` varchar(200)
,`cluster_name` varchar(255)
,`timeline_year` int
,`final_score_percent` decimal(6,2)
,`cluster_rank` bigint unsigned
,`status_label` varchar(15)
,`comment` varchar(13)
,`reporting_periods` text
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_cluster_rank_by_quarter`
-- (See below for the actual view)
--
CREATE TABLE `vw_cluster_rank_by_quarter` (
`cluster_pk` bigint unsigned
,`cluster_code` varchar(200)
,`cluster_name` varchar(255)
,`timeline_year` int
,`quarter_label` varchar(14)
,`final_score_percent` decimal(6,2)
,`cluster_rank` bigint unsigned
,`status_label` varchar(15)
,`comment` varchar(13)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_cluster_rank_semiannual`
-- (See below for the actual view)
--
CREATE TABLE `vw_cluster_rank_semiannual` (
`cluster_pk` bigint unsigned
,`cluster_code` varchar(200)
,`cluster_name` varchar(255)
,`timeline_year` int
,`semi_annual_label` varchar(18)
,`final_score_percent` decimal(6,2)
,`cluster_rank` bigint unsigned
,`status_label` varchar(15)
,`comment` varchar(13)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_cluster_so_performance_summary`
-- (See below for the actual view)
--
CREATE TABLE `vw_cluster_so_performance_summary` (
`cluster_pk` bigint unsigned
,`cluster_code` varchar(200)
,`cluster_name` varchar(255)
,`so_pk` bigint unsigned
,`so_number` varchar(200)
,`so_name` varchar(255)
,`timeline_pk` bigint unsigned
,`timeline_name` enum('First Quarter (Q1): July 1 - September 30','Second Quarter (Q2): October 1 - December 31','Third Quarter (Q3): January 1 - March 31','Fourth Quarter (Q4): April 1 - June 30')
,`timeline_year` int
,`timeline_quarter` tinyint
,`timeline_closing_date` date
,`timeline_status` enum('Pending','In Progress','Completed')
,`total_indicators_in_group` bigint
,`final_score_percent` decimal(6,2)
,`status_label` varchar(15)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_cluster_vs_target_achievements`
-- (See below for the actual view)
--
CREATE TABLE `vw_cluster_vs_target_achievements` (
`cluster_pk` bigint unsigned
,`cluster_code` varchar(200)
,`cluster_name` varchar(255)
,`indicator_pk` bigint unsigned
,`indicator_number` varchar(10)
,`indicator_name` varchar(255)
,`indicator_response_type` enum('Text','Number','Boolean','Yes/No')
,`timeline_pk` bigint unsigned
,`timeline_name` enum('First Quarter (Q1): July 1 - September 30','Second Quarter (Q2): October 1 - December 31','Third Quarter (Q3): January 1 - March 31','Fourth Quarter (Q4): April 1 - June 30')
,`timeline_year` int
,`timeline_quarter` tinyint
,`timeline_closing_date` date
,`timeline_status` enum('Pending','In Progress','Completed')
,`cluster_target_pk` bigint unsigned
,`target_year_string` varchar(200)
,`target_value_raw` varchar(200)
,`sum_of_raw_actual_before_clamp` decimal(20,4)
,`target_value_for_calc` decimal(20,4)
,`total_actual_value` decimal(20,4)
,`achievement_percent` decimal(31,8)
,`status_label` varchar(15)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_indicator_target_and_performance_full`
-- (See below for the actual view)
--
CREATE TABLE `vw_indicator_target_and_performance_full` (
`performance_pk` bigint unsigned
,`performance_cluster_id_db` varchar(255)
,`ReportingID` varchar(255)
,`SO_ID` varchar(255)
,`UserID` varchar(255)
,`performance_indicator_id` varchar(255)
,`Response` varchar(255)
,`ReportingComment` text
,`performance_response_type` enum('Text','Number','Boolean','Yes/No')
,`Baseline_2023_2024` int
,`Target_Year1` int
,`Target_Year2` int
,`Target_Year3` int
,`performance_created_at` timestamp
,`performance_updated_at` timestamp
,`target_pk` bigint unsigned
,`ClusterTargetID` varchar(255)
,`target_indicator_id` varchar(255)
,`Target_Year` varchar(200)
,`Target_Value` varchar(200)
,`target_response_type` enum('Text','Number','Boolean','Yes/No')
,`Baseline2024` varchar(200)
,`target_created_at` timestamp
,`target_updated_at` timestamp
,`cluster_pk` bigint unsigned
,`cluster_description` varchar(255)
,`cluster_name` varchar(255)
,`cluster_created_at` timestamp
,`cluster_updated_at` timestamp
,`cluster_text_identifier` varchar(200)
,`so_pk` bigint unsigned
,`SO_Name` varchar(255)
,`so_description` text
,`SO_Number` varchar(200)
,`StrategicObjectiveID` varchar(200)
,`so_created_at` timestamp
,`so_updated_at` timestamp
,`timeline_pk` bigint unsigned
,`ReportName` enum('First Quarter (Q1): July 1 - September 30','Second Quarter (Q2): October 1 - December 31','Third Quarter (Q3): January 1 - March 31','Fourth Quarter (Q4): April 1 - June 30')
,`timeline_type` enum('Quarterly Reports')
,`timeline_description` text
,`timeline_reporting_id` varchar(255)
,`timeline_year` int
,`timeline_closing_date` date
,`timeline_status` enum('Pending','In Progress','Completed')
,`timeline_created_at` timestamp
,`timeline_updated_at` timestamp
,`timeline_quarter` tinyint
,`user_pk` bigint unsigned
,`user_name` varchar(255)
,`user_email` varchar(255)
,`user_cluster_id` varchar(200)
,`user_type` enum('MPA','ECSA-HC')
,`user_code` varchar(255)
,`user_phone` varchar(20)
,`user_nationality` varchar(100)
,`user_address` text
,`user_parent_org` varchar(255)
,`user_sex` enum('Male','Female')
,`user_job_title` varchar(255)
,`user_account_role` enum('Admin','User','Cluster Head')
,`user_created_at` timestamp
,`user_updated_at` timestamp
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_performance_over_time_by_quarter`
-- (See below for the actual view)
--
CREATE TABLE `vw_performance_over_time_by_quarter` (
`cluster_pk` bigint unsigned
,`cluster_code` varchar(200)
,`cluster_name` varchar(255)
,`indicator_pk` bigint unsigned
,`indicator_number` varchar(10)
,`indicator_name` varchar(255)
,`indicator_response_type` enum('Text','Number','Boolean','Yes/No')
,`timeline_pk` bigint unsigned
,`timeline_name` enum('First Quarter (Q1): July 1 - September 30','Second Quarter (Q2): October 1 - December 31','Third Quarter (Q3): January 1 - March 31','Fourth Quarter (Q4): April 1 - June 30')
,`timeline_year` int
,`timeline_quarter` tinyint
,`timeline_closing_date` date
,`timeline_status` enum('Pending','In Progress','Completed')
,`cluster_target_pk` bigint unsigned
,`target_year_string` varchar(200)
,`target_value_raw` varchar(200)
,`total_actual_value` decimal(20,4)
,`total_target_value` decimal(20,4)
,`achievement_percent` decimal(31,8)
,`status_label` varchar(15)
,`comment` varchar(13)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_semi_annual_cluster_summary`
-- (See below for the actual view)
--
CREATE TABLE `vw_semi_annual_cluster_summary` (
`cluster_pk` bigint unsigned
,`cluster_code` varchar(200)
,`cluster_name` varchar(255)
,`timeline_year` int
,`semi_annual_label` varchar(18)
,`total_indicators` bigint
,`average_achievement` decimal(26,2)
,`needs_attention` decimal(23,0)
,`progressing` decimal(23,0)
,`on_track` decimal(23,0)
,`met` decimal(23,0)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_semi_annual_performance`
-- (See below for the actual view)
--
CREATE TABLE `vw_semi_annual_performance` (
`cluster_pk` bigint unsigned
,`cluster_code` varchar(200)
,`cluster_name` varchar(255)
,`so_pk` bigint unsigned
,`so_number` varchar(200)
,`so_name` varchar(255)
,`indicator_pk` bigint unsigned
,`indicator_number` varchar(10)
,`indicator_name` varchar(255)
,`indicator_response_type` enum('Text','Number','Boolean','Yes/No')
,`timeline_year` int
,`semi_annual_label` varchar(18)
,`raw_actual_value` decimal(20,4)
,`raw_target_value` decimal(20,4)
,`achievement_percent` decimal(31,8)
,`status_label` varchar(15)
,`comment` varchar(13)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_semi_annual_trend_analysis`
-- (See below for the actual view)
--
CREATE TABLE `vw_semi_annual_trend_analysis` (
`cluster_pk` bigint unsigned
,`cluster_code` varchar(200)
,`cluster_name` varchar(255)
,`timeline_year` int
,`first_half_score` decimal(26,2)
,`second_half_score` decimal(26,2)
,`score_change` decimal(27,2)
,`percent_change` decimal(33,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_so_indicators_report`
-- (See below for the actual view)
--
CREATE TABLE `vw_so_indicators_report` (
`so_pk` bigint unsigned
,`so_number` varchar(200)
,`so_name` varchar(255)
,`indicator_id` bigint unsigned
,`Indicator_Number` varchar(10)
,`Indicator_Name` varchar(255)
,`indicator_type` enum('Text','Number','Boolean','Yes/No')
,`cluster_pk` bigint unsigned
,`cluster_code` varchar(200)
,`cluster_name` varchar(255)
,`timeline_pk` bigint unsigned
,`timeline_name` enum('First Quarter (Q1): July 1 - September 30','Second Quarter (Q2): October 1 - December 31','Third Quarter (Q3): January 1 - March 31','Fourth Quarter (Q4): April 1 - June 30')
,`timeline_year` int
,`timeline_type` enum('Quarterly Reports')
,`timeline_status` enum('Pending','In Progress','Completed')
,`user_pk` bigint unsigned
,`user_name` varchar(255)
,`user_email` varchar(255)
,`cluster_target_pk` bigint unsigned
,`target_year_string` varchar(200)
,`target_value_raw` varchar(200)
,`user_entered_value` varchar(255)
,`aggregated_actual_value` decimal(20,4)
,`score_percent` decimal(53,8)
,`status_label` varchar(22)
,`formula_explanation` varchar(94)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_threshold_alerts_and_flags`
-- (See below for the actual view)
--
CREATE TABLE `vw_threshold_alerts_and_flags` (
`cluster_pk` bigint unsigned
,`cluster_code` varchar(200)
,`cluster_name` varchar(255)
,`indicator_pk` bigint unsigned
,`indicator_number` varchar(10)
,`indicator_name` varchar(255)
,`indicator_response_type` enum('Text','Number','Boolean','Yes/No')
,`timeline_pk` bigint unsigned
,`timeline_name` enum('First Quarter (Q1): July 1 - September 30','Second Quarter (Q2): October 1 - December 31','Third Quarter (Q3): January 1 - March 31','Fourth Quarter (Q4): April 1 - June 30')
,`timeline_year` int
,`timeline_quarter` tinyint
,`timeline_closing_date` date
,`timeline_status` enum('Pending','In Progress','Completed')
,`cluster_target_pk` bigint unsigned
,`target_year_string` varchar(200)
,`target_value_raw` varchar(200)
,`total_actual_value` decimal(20,4)
,`achievement_percent` decimal(31,8)
,`status_label` varchar(15)
,`alert_flag` varchar(15)
);

-- --------------------------------------------------------

--
-- Structure for view `dashboard_cluster_completeness_summary`
--
DROP TABLE IF EXISTS `dashboard_cluster_completeness_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `dashboard_cluster_completeness_summary`  AS SELECT `c`.`id` AS `cluster_pk`, `c`.`ClusterID` AS `cluster_text_identifier`, `c`.`Cluster_Name` AS `cluster_name`, `c`.`Description` AS `cluster_description`, `t`.`id` AS `timeline_pk`, `t`.`ReportName` AS `timeline_name`, `t`.`Type` AS `timeline_type`, `t`.`Description` AS `timeline_description`, `t`.`ReportingID` AS `timeline_reporting_id`, `t`.`Year` AS `timeline_year`, `t`.`Quarter` AS `timeline_quarter`, `t`.`ClosingDate` AS `timeline_closing_date`, `t`.`status` AS `timeline_status`, count(0) AS `total_indicators`, sum((case when (`cpm`.`id` is not null) then 1 else 0 end)) AS `reported_indicators`, sum((case when (`cpm`.`id` is null) then 1 else 0 end)) AS `not_reported_indicators`, (case when (count(0) = 0) then 0 else cast(((100.0 * sum((case when (`cpm`.`id` is not null) then 1 else 0 end))) / count(0)) as decimal(5,2)) end) AS `completeness_percentage`, group_concat(distinct `t`.`ReportingID` order by `t`.`ReportingID` ASC separator ', ') AS `considered_reports` FROM ((((`clusters` `c` join `cluster_indicator_targets` `cit` on((`c`.`ClusterID` = `cit`.`ClusterID`))) join `performance_indicators` `pi` on(((`pi`.`id` = cast(`cit`.`IndicatorID` as unsigned)) and (`pi`.`id` <> 0) and (json_contains(`pi`.`Responsible_Cluster`,json_quote(`c`.`ClusterID`)) = 1)))) join `ecsahc_timelines` `t` on(((`t`.`status` in ('In Progress','Completed')) and (((`cit`.`Target_Year` like '%-%') and (`t`.`Year` between cast(substring_index(`cit`.`Target_Year`,'-',1) as unsigned) and cast(substring_index(`cit`.`Target_Year`,'-',-(1)) as unsigned))) or ((not((`cit`.`Target_Year` like '%-%'))) and (`t`.`Year` = cast(`cit`.`Target_Year` as unsigned))))))) left join `cluster_performance_mappings` `cpm` on(((`cpm`.`ClusterID` = `cit`.`ClusterID`) and (`cpm`.`IndicatorID` = `cit`.`IndicatorID`) and (`cpm`.`ReportingID` = `t`.`ReportingID`)))) GROUP BY `c`.`id`, `c`.`ClusterID`, `c`.`Cluster_Name`, `c`.`Description`, `t`.`id`, `t`.`ReportName`, `t`.`Type`, `t`.`Description`, `t`.`ReportingID`, `t`.`Year`, `t`.`Quarter`, `t`.`ClosingDate`, `t`.`status` ;

-- --------------------------------------------------------

--
-- Structure for view `dashboard_cluster_quarterly_rank`
--
DROP TABLE IF EXISTS `dashboard_cluster_quarterly_rank`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `dashboard_cluster_quarterly_rank`  AS SELECT `agg`.`cluster_pk` AS `cluster_pk`, `agg`.`cluster_code` AS `cluster_code`, `agg`.`cluster_name` AS `cluster_name`, `agg`.`year_val` AS `timeline_year`, `agg`.`quarter_val` AS `timeline_quarter`, `agg`.`final_score_percent` AS `final_score_percent`, dense_rank() OVER (PARTITION BY `agg`.`year_val`,`agg`.`quarter_val` ORDER BY `agg`.`final_score_percent` desc ) AS `cluster_rank`, (case when (`agg`.`final_score_percent` < 10) then 'Needs Attention' when (`agg`.`final_score_percent` < 50) then 'Progressing' when (`agg`.`final_score_percent` < 90) then 'On Track' else 'Met' end) AS `status_label`, (case when ((`agg`.`sum_target_val` > 0) and (`agg`.`sum_actual_val` > `agg`.`sum_target_val`)) then 'Over Achieved' else '' end) AS `comment`, group_concat(distinct `t`.`ReportingID` order by `t`.`ReportingID` ASC separator ', ') AS `considered_reports` FROM ((select `innerAgg`.`cluster_pk` AS `cluster_pk`,`innerAgg`.`cluster_code` AS `cluster_code`,`innerAgg`.`cluster_name` AS `cluster_name`,`innerAgg`.`year_val` AS `year_val`,`innerAgg`.`quarter_val` AS `quarter_val`,sum(`innerAgg`.`sum_actual_val`) AS `sum_actual_val`,sum(`innerAgg`.`sum_target_val`) AS `sum_target_val`,cast((avg(`innerAgg`.`indicator_fraction`) * 100) as decimal(6,2)) AS `final_score_percent` from (select `c`.`id` AS `cluster_pk`,`c`.`ClusterID` AS `cluster_code`,`c`.`Cluster_Name` AS `cluster_name`,`t`.`Year` AS `year_val`,`t`.`Quarter` AS `quarter_val`,`t`.`ReportingID` AS `ReportingID`,(case when (`pi`.`ResponseType` = 'Number') then cast(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)) as decimal(20,4)) when (`pi`.`ResponseType` in ('Yes/No','Boolean')) then (case when (sum((case when (((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) or ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False')))) then 1 else 0 end)) > 0) then 1 else 0 end) else 0 end) AS `sum_actual_val`,(case when ((`pi`.`ResponseType` = 'Number') and regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cit`.`Target_Value` as decimal(20,4)) when ((`pi`.`ResponseType` in ('Yes/No','Boolean')) and (`cit`.`Target_Value` in ('Yes','True','No','False'))) then 1 else 0 end) AS `sum_target_val`,(case when (`pi`.`ResponseType` = 'Number') then (case when (regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$') and (cast(`cit`.`Target_Value` as decimal(20,4)) > 0)) then least(1,greatest(0,(cast(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)) as decimal(20,4)) / cast(`cit`.`Target_Value` as decimal(20,4))))) else 0 end) when (`pi`.`ResponseType` in ('Yes/No','Boolean')) then (case when (`cit`.`Target_Value` in ('Yes','True','No','False')) then (case when (sum((case when (((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) or ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False')))) then 1 else 0 end)) > 0) then 1 else 0 end) else 0 end) else NULL end) AS `indicator_fraction` from ((((`cluster_indicator_targets` `cit` join `performance_indicators` `pi` on(((`pi`.`id` = cast(`cit`.`IndicatorID` as unsigned)) and (`pi`.`id` <> 0) and (json_contains(`pi`.`Responsible_Cluster`,json_quote(`cit`.`ClusterID`)) = 1)))) join `clusters` `c` on((`c`.`ClusterID` = `cit`.`ClusterID`))) join `ecsahc_timelines` `t` on(((`t`.`status` in ('In Progress','Completed')) and (((`cit`.`Target_Year` like '%-%') and (`t`.`Year` between cast(substring_index(`cit`.`Target_Year`,'-',1) as unsigned) and cast(substring_index(`cit`.`Target_Year`,'-',-(1)) as unsigned))) or ((not((`cit`.`Target_Year` like '%-%'))) and (`t`.`Year` = cast(`cit`.`Target_Year` as unsigned))))))) left join `cluster_performance_mappings` `cpm` on(((`cpm`.`ClusterID` = `cit`.`ClusterID`) and (`cpm`.`IndicatorID` = `cit`.`IndicatorID`) and (`cpm`.`ReportingID` = `t`.`ReportingID`)))) group by `c`.`id`,`c`.`ClusterID`,`c`.`Cluster_Name`,`t`.`Year`,`t`.`Quarter`,`t`.`ReportingID`,`pi`.`id`,`pi`.`ResponseType`,`cit`.`Target_Value`) `innerAgg` group by `innerAgg`.`cluster_pk`,`innerAgg`.`cluster_code`,`innerAgg`.`cluster_name`,`innerAgg`.`year_val`,`innerAgg`.`quarter_val`) `agg` join `ecsahc_timelines` `t` on(((`t`.`Year` = `agg`.`year_val`) and (`t`.`Quarter` = `agg`.`quarter_val`) and (`t`.`status` in ('In Progress','Completed'))))) GROUP BY `agg`.`cluster_pk`, `agg`.`cluster_code`, `agg`.`cluster_name`, `agg`.`year_val`, `agg`.`quarter_val`, `agg`.`final_score_percent`, `agg`.`sum_actual_val`, `agg`.`sum_target_val` ORDER BY `agg`.`year_val` ASC, `agg`.`quarter_val` ASC, `agg`.`final_score_percent` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_annual_indicator_performance`
--
DROP TABLE IF EXISTS `vw_annual_indicator_performance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_annual_indicator_performance`  AS SELECT `derived`.`cluster_pk` AS `cluster_pk`, `derived`.`cluster_code` AS `cluster_code`, `derived`.`cluster_name` AS `cluster_name`, `derived`.`so_pk` AS `so_pk`, `derived`.`so_number` AS `so_number`, `derived`.`so_name` AS `so_name`, `derived`.`indicator_pk` AS `indicator_pk`, `derived`.`indicator_number` AS `indicator_number`, `derived`.`indicator_name` AS `indicator_name`, `derived`.`indicator_response_type` AS `indicator_response_type`, `derived`.`timeline_year` AS `timeline_year`, group_concat(distinct `derived`.`timeline_name` order by `derived`.`timeline_quarter` ASC separator ', ') AS `reporting_periods`, count(distinct `derived`.`timeline_id`) AS `num_periods`, sum(`derived`.`total_actual_value`) AS `annual_actual`, sum(`derived`.`total_target_value`) AS `annual_target`, (case when (sum(`derived`.`total_target_value`) = 0) then 0 else round(((sum(`derived`.`total_actual_value`) / sum(`derived`.`total_target_value`)) * 100),2) end) AS `achievement_percent`, (case when (sum(`derived`.`total_target_value`) = 0) then 'No Valid Target' when (round(((sum(`derived`.`total_actual_value`) / sum(`derived`.`total_target_value`)) * 100),2) < 10) then 'Needs Attention' when (round(((sum(`derived`.`total_actual_value`) / sum(`derived`.`total_target_value`)) * 100),2) < 50) then 'Progressing' when (round(((sum(`derived`.`total_actual_value`) / sum(`derived`.`total_target_value`)) * 100),2) < 90) then 'On Track' else 'Met' end) AS `status_label` FROM (select `c`.`id` AS `cluster_pk`,`c`.`ClusterID` AS `cluster_code`,`c`.`Cluster_Name` AS `cluster_name`,`so`.`id` AS `so_pk`,`so`.`SO_Number` AS `so_number`,`so`.`SO_Name` AS `so_name`,`pi`.`id` AS `indicator_pk`,`pi`.`Indicator_Number` AS `indicator_number`,`pi`.`Indicator_Name` AS `indicator_name`,`pi`.`ResponseType` AS `indicator_response_type`,`t`.`Year` AS `timeline_year`,`t`.`id` AS `timeline_id`,`t`.`ReportName` AS `timeline_name`,`t`.`Quarter` AS `timeline_quarter`,(case when (`pi`.`ResponseType` = 'Number') then cast(sum((case when regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$') then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)) as decimal(20,4)) when (`pi`.`ResponseType` in ('Yes/No','Boolean')) then (case when (sum((case when (`cpm`.`Response` in ('Yes','True')) then 1 else 0 end)) > 0) then 1 else 0 end) else 0 end) AS `total_actual_value`,(case when (`pi`.`ResponseType` = 'Number') then cast((case when regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$') then `cit`.`Target_Value` else '0' end) as decimal(20,4)) when (`pi`.`ResponseType` in ('Yes/No','Boolean')) then (case when (`cit`.`Target_Value` in ('Yes','True','No','False')) then 1 else 0 end) else 0 end) AS `total_target_value` from (((((`cluster_indicator_targets` `cit` join `clusters` `c` on((`cit`.`ClusterID` = `c`.`ClusterID`))) join `performance_indicators` `pi` on(((`pi`.`id` = cast(`cit`.`IndicatorID` as unsigned)) and (`pi`.`id` <> 0) and (json_contains(`pi`.`Responsible_Cluster`,json_quote(`c`.`ClusterID`)) = 1)))) join `ecsahc_timelines` `t` on(((`t`.`status` in ('In Progress','Completed')) and (((`cit`.`Target_Year` like '%-%') and (`t`.`Year` between cast(substring_index(`cit`.`Target_Year`,'-',1) as unsigned) and cast(substring_index(`cit`.`Target_Year`,'-',-(1)) as unsigned))) or ((not((`cit`.`Target_Year` like '%-%'))) and (`t`.`Year` = cast(`cit`.`Target_Year` as unsigned))))))) left join `cluster_performance_mappings` `cpm` on(((`cpm`.`ClusterID` = `cit`.`ClusterID`) and (`cpm`.`IndicatorID` = `cit`.`IndicatorID`) and (`cpm`.`ReportingID` = `t`.`ReportingID`)))) left join `strategic_objectives` `so` on((`so`.`SO_Number` = `pi`.`SO_ID`))) group by `c`.`id`,`so`.`id`,`pi`.`id`,`t`.`id`,`cit`.`Target_Value`,`pi`.`ResponseType`) AS `derived` GROUP BY `derived`.`cluster_pk`, `derived`.`so_pk`, `derived`.`indicator_pk`, `derived`.`timeline_year` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_annual_performance_dashboard`
--
DROP TABLE IF EXISTS `vw_annual_performance_dashboard`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_annual_performance_dashboard`  AS SELECT `sub`.`year_val` AS `year_val`, `sub`.`cluster_pk` AS `cluster_pk`, `sub`.`cluster_code` AS `cluster_code`, `sub`.`cluster_name` AS `cluster_name`, `sub`.`so_pk` AS `so_pk`, `sub`.`so_number` AS `so_number`, `sub`.`so_name` AS `so_name`, `sub`.`indicator_pk` AS `indicator_pk`, `sub`.`indicator_number` AS `indicator_number`, `sub`.`indicator_name` AS `indicator_name`, sum(`sub`.`total_actual_value`) AS `annual_actual`, sum(`sub`.`total_target_value`) AS `annual_target`, (case when (sum(`sub`.`total_target_value`) = 0) then 0 else round(((sum(`sub`.`total_actual_value`) / sum(`sub`.`total_target_value`)) * 100),2) end) AS `achievement_percent`, group_concat(distinct `sub`.`timeline_pk` order by `sub`.`timeline_pk` ASC separator ', ') AS `contributing_timelines`, group_concat(distinct `sub`.`ReportName` order by `sub`.`ReportName` ASC separator ', ') AS `contributing_report_names`, count(distinct `sub`.`timeline_pk`) AS `reporting_periods_count` FROM (select `t`.`Year` AS `year_val`,`c`.`id` AS `cluster_pk`,`c`.`ClusterID` AS `cluster_code`,`c`.`Cluster_Name` AS `cluster_name`,`so`.`id` AS `so_pk`,`so`.`SO_Number` AS `so_number`,`so`.`SO_Name` AS `so_name`,`pi`.`id` AS `indicator_pk`,`pi`.`Indicator_Number` AS `indicator_number`,`pi`.`Indicator_Name` AS `indicator_name`,(case when (`pi`.`ResponseType` = 'Number') then cast(sum((case when regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$') then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)) as decimal(20,4)) when (`pi`.`ResponseType` in ('Yes/No','Boolean')) then (case when (sum((case when (`cpm`.`Response` in ('Yes','True')) then 1 else 0 end)) > 0) then 1 else 0 end) else 0 end) AS `total_actual_value`,(case when ((`pi`.`ResponseType` = 'Number') and regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cit`.`Target_Value` as decimal(20,4)) when ((`pi`.`ResponseType` in ('Yes/No','Boolean')) and (`cit`.`Target_Value` in ('Yes','True','No','False'))) then 1 else 0 end) AS `total_target_value`,`t`.`id` AS `timeline_pk`,`t`.`ReportName` AS `ReportName` from (((((`cluster_indicator_targets` `cit` join `performance_indicators` `pi` on((`pi`.`id` = cast(`cit`.`IndicatorID` as unsigned)))) join `clusters` `c` on((`c`.`ClusterID` = `cit`.`ClusterID`))) join `ecsahc_timelines` `t` on(((`t`.`status` in ('In Progress','Completed')) and (((`cit`.`Target_Year` like '%-%') and (`t`.`Year` between cast(substring_index(`cit`.`Target_Year`,'-',1) as unsigned) and cast(substring_index(`cit`.`Target_Year`,'-',-(1)) as unsigned))) or ((not((`cit`.`Target_Year` like '%-%'))) and (`t`.`Year` = cast(`cit`.`Target_Year` as unsigned))))))) left join `cluster_performance_mappings` `cpm` on(((`cpm`.`ClusterID` = `cit`.`ClusterID`) and (`cpm`.`IndicatorID` = `cit`.`IndicatorID`) and (`cpm`.`ReportingID` = `t`.`ReportingID`)))) left join `strategic_objectives` `so` on((`so`.`SO_Number` = `pi`.`SO_ID`))) group by `c`.`id`,`c`.`ClusterID`,`c`.`Cluster_Name`,`so`.`id`,`so`.`SO_Number`,`so`.`SO_Name`,`pi`.`id`,`pi`.`Indicator_Number`,`pi`.`Indicator_Name`,`t`.`Year`,`t`.`id`,`t`.`ReportName`,`pi`.`ResponseType`,`cit`.`Target_Value`) AS `sub` GROUP BY `sub`.`year_val`, `sub`.`cluster_pk`, `sub`.`so_pk`, `sub`.`indicator_pk` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_cluster_completeness_summary`
--
DROP TABLE IF EXISTS `vw_cluster_completeness_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`hacker`@`localhost` SQL SECURITY DEFINER VIEW `vw_cluster_completeness_summary`  AS SELECT `c`.`id` AS `cluster_pk`, `c`.`ClusterID` AS `cluster_text_identifier`, `c`.`Cluster_Name` AS `cluster_name`, `c`.`Description` AS `cluster_description`, `t`.`id` AS `timeline_pk`, `t`.`ReportName` AS `timeline_name`, `t`.`Type` AS `timeline_type`, `t`.`Description` AS `timeline_description`, `t`.`ReportingID` AS `timeline_reporting_id`, `t`.`Year` AS `timeline_year`, `t`.`Quarter` AS `timeline_quarter`, `t`.`ClosingDate` AS `timeline_closing_date`, `t`.`status` AS `timeline_status`, count(0) AS `total_indicators`, sum((case when (`cpm`.`id` is not null) then 1 else 0 end)) AS `reported_indicators`, sum((case when (`cpm`.`id` is null) then 1 else 0 end)) AS `not_reported_indicators`, (case when (count(0) = 0) then 0 else cast(((100.0 * sum((case when (`cpm`.`id` is not null) then 1 else 0 end))) / count(0)) as decimal(5,2)) end) AS `completeness_percentage` FROM ((((`clusters` `c` join `cluster_indicator_targets` `cit` on((`c`.`ClusterID` = `cit`.`ClusterID`))) join `performance_indicators` `pi` on(((`pi`.`id` = cast(`cit`.`IndicatorID` as unsigned)) and (`pi`.`id` <> 0) and (json_contains(`pi`.`Responsible_Cluster`,json_quote(`c`.`ClusterID`)) = 1)))) join `ecsahc_timelines` `t` on(((`t`.`status` in ('In Progress','Completed')) and (((`cit`.`Target_Year` like '%-%') and (`t`.`Year` between cast(substring_index(`cit`.`Target_Year`,'-',1) as unsigned) and cast(substring_index(`cit`.`Target_Year`,'-',-(1)) as unsigned))) or ((not((`cit`.`Target_Year` like '%-%'))) and (`t`.`Year` = cast(`cit`.`Target_Year` as unsigned))))))) left join `cluster_performance_mappings` `cpm` on(((`cpm`.`ClusterID` = `c`.`ClusterID`) and (`cpm`.`IndicatorID` = `cit`.`IndicatorID`) and (`cpm`.`ReportingID` = `t`.`ReportingID`)))) GROUP BY `c`.`id`, `c`.`ClusterID`, `c`.`Cluster_Name`, `c`.`Description`, `t`.`id`, `t`.`ReportName`, `t`.`Type`, `t`.`Description`, `t`.`ReportingID`, `t`.`Year`, `t`.`Quarter`, `t`.`ClosingDate`, `t`.`status` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_cluster_indicator_targets_full`
--
DROP TABLE IF EXISTS `vw_cluster_indicator_targets_full`;

CREATE ALGORITHM=UNDEFINED DEFINER=`hacker`@`localhost` SQL SECURITY DEFINER VIEW `vw_cluster_indicator_targets_full`  AS SELECT `cit`.`id` AS `target_pk`, `cit`.`ClusterTargetID` AS `ClusterTargetID`, `cit`.`ClusterID` AS `cluster_id_db`, `cit`.`IndicatorID` AS `target_indicator_id`, `cit`.`Target_Year` AS `Target_Year`, `cit`.`Target_Value` AS `Target_Value`, `cit`.`ResponseType` AS `target_response_type`, `cit`.`Baseline2024` AS `Baseline2024`, `cit`.`created_at` AS `target_created_at`, `cit`.`updated_at` AS `target_updated_at`, `c`.`id` AS `cluster_pk`, `c`.`Description` AS `cluster_description`, `c`.`Cluster_Name` AS `cluster_name`, `c`.`created_at` AS `cluster_created_at`, `c`.`updated_at` AS `cluster_updated_at`, `c`.`ClusterID` AS `cluster_text_identifier` FROM (`cluster_indicator_targets` `cit` join `clusters` `c` on((`cit`.`ClusterID` = `c`.`ClusterID`))) ;

-- --------------------------------------------------------

--
-- Structure for view `vw_cluster_performance_mappings_full`
--
DROP TABLE IF EXISTS `vw_cluster_performance_mappings_full`;

CREATE ALGORITHM=UNDEFINED DEFINER=`hacker`@`localhost` SQL SECURITY DEFINER VIEW `vw_cluster_performance_mappings_full`  AS SELECT `cpm`.`id` AS `performance_pk`, `cpm`.`ClusterID` AS `cluster_id_db`, `cpm`.`ReportingID` AS `ReportingID`, `cpm`.`SO_ID` AS `SO_ID`, `cpm`.`UserID` AS `UserID`, `cpm`.`IndicatorID` AS `IndicatorID`, `cpm`.`Response` AS `Response`, `cpm`.`ReportingComment` AS `ReportingComment`, `cpm`.`ResponseType` AS `performance_response_type`, `cpm`.`Baseline_2023_2024` AS `Baseline_2023_2024`, `cpm`.`Target_Year1` AS `Target_Year1`, `cpm`.`Target_Year2` AS `Target_Year2`, `cpm`.`Target_Year3` AS `Target_Year3`, `cpm`.`created_at` AS `performance_created_at`, `cpm`.`updated_at` AS `performance_updated_at`, `c`.`id` AS `cluster_pk`, `c`.`Description` AS `cluster_description`, `c`.`Cluster_Name` AS `cluster_name`, `c`.`created_at` AS `cluster_created_at`, `c`.`updated_at` AS `cluster_updated_at`, `c`.`ClusterID` AS `cluster_text_identifier`, `so`.`id` AS `so_pk`, `so`.`SO_Name` AS `SO_Name`, `so`.`Description` AS `so_description`, `so`.`SO_Number` AS `SO_Number`, `so`.`StrategicObjectiveID` AS `StrategicObjectiveID`, `so`.`created_at` AS `so_created_at`, `so`.`updated_at` AS `so_updated_at`, `t`.`id` AS `timeline_pk`, `t`.`ReportName` AS `ReportName`, `t`.`Type` AS `timeline_type`, `t`.`Description` AS `timeline_description`, `t`.`ReportingID` AS `timeline_reporting_id`, `t`.`Year` AS `timeline_year`, `t`.`ClosingDate` AS `timeline_closing_date`, `t`.`status` AS `timeline_status`, `t`.`created_at` AS `timeline_created_at`, `t`.`updated_at` AS `timeline_updated_at`, `t`.`Quarter` AS `timeline_quarter`, `u`.`id` AS `user_pk`, `u`.`name` AS `user_name`, `u`.`email` AS `user_email`, `u`.`ClusterID` AS `user_cluster_id`, `u`.`UserType` AS `user_type`, `u`.`UserCode` AS `user_code`, `u`.`Phone` AS `user_phone`, `u`.`Nationality` AS `user_nationality`, `u`.`Address` AS `user_address`, `u`.`ParentOrganization` AS `user_parent_org`, `u`.`Sex` AS `user_sex`, `u`.`JobTitle` AS `user_job_title`, `u`.`AccountRole` AS `user_account_role`, `u`.`created_at` AS `user_created_at`, `u`.`updated_at` AS `user_updated_at` FROM ((((`cluster_performance_mappings` `cpm` join `clusters` `c` on((`cpm`.`ClusterID` = `c`.`ClusterID`))) left join `strategic_objectives` `so` on((`cpm`.`SO_ID` = `so`.`SO_Number`))) left join `ecsahc_timelines` `t` on((`cpm`.`ReportingID` = `t`.`ReportingID`))) left join `users` `u` on((`cpm`.`UserID` = `u`.`UserID`))) ;

-- --------------------------------------------------------

--
-- Structure for view `vw_cluster_rank_annual`
--
DROP TABLE IF EXISTS `vw_cluster_rank_annual`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_cluster_rank_annual`  AS SELECT `agg`.`cluster_pk` AS `cluster_pk`, `agg`.`cluster_code` AS `cluster_code`, `agg`.`cluster_name` AS `cluster_name`, `agg`.`year_val` AS `timeline_year`, `agg`.`final_score_percent` AS `final_score_percent`, dense_rank() OVER (PARTITION BY `agg`.`year_val` ORDER BY `agg`.`final_score_percent` desc ) AS `cluster_rank`, (case when (`agg`.`final_score_percent` < 10) then 'Needs Attention' when (`agg`.`final_score_percent` < 50) then 'Progressing' when (`agg`.`final_score_percent` < 90) then 'On Track' else 'Met' end) AS `status_label`, (case when ((`agg`.`sum_target_val` > 0) and (`agg`.`sum_actual_val` > `agg`.`sum_target_val`)) then 'Over Achieved' else '' end) AS `comment`, `agg`.`reporting_periods` AS `reporting_periods` FROM (select `frac`.`cluster_pk` AS `cluster_pk`,`frac`.`cluster_code` AS `cluster_code`,`frac`.`cluster_name` AS `cluster_name`,`frac`.`year_val` AS `year_val`,group_concat(distinct `frac`.`timeline_name` order by `frac`.`timeline_quarter` ASC separator ', ') AS `reporting_periods`,sum(`frac`.`sum_actual_val`) AS `sum_actual_val`,sum(`frac`.`sum_target_val`) AS `sum_target_val`,cast((avg(`frac`.`indicator_fraction`) * 100) as decimal(6,2)) AS `final_score_percent` from (select `c`.`id` AS `cluster_pk`,`c`.`ClusterID` AS `cluster_code`,`c`.`Cluster_Name` AS `cluster_name`,`t`.`Year` AS `year_val`,`t`.`Quarter` AS `timeline_quarter`,`t`.`ReportName` AS `timeline_name`,(case when (`pi`.`ResponseType` = 'Number') then cast(sum((case when regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$') then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)) as decimal(20,4)) when (`pi`.`ResponseType` in ('Yes/No','Boolean')) then (case when (sum((case when (((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) or ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False')))) then 1 else 0 end)) > 0) then 1 else 0 end) else 0 end) AS `sum_actual_val`,(case when ((`pi`.`ResponseType` = 'Number') and regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cit`.`Target_Value` as decimal(20,4)) when ((`pi`.`ResponseType` in ('Yes/No','Boolean')) and (`cit`.`Target_Value` in ('Yes','True','No','False'))) then 1 else 0 end) AS `sum_target_val`,(case when (`pi`.`ResponseType` = 'Number') then (case when (regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$') and (cast(`cit`.`Target_Value` as decimal(20,4)) > 0)) then least(1,greatest(0,(cast(sum((case when regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$') then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)) as decimal(20,4)) / cast(`cit`.`Target_Value` as decimal(20,4))))) else 0 end) when (`pi`.`ResponseType` in ('Yes/No','Boolean')) then (case when (`cit`.`Target_Value` in ('Yes','True','No','False')) then (case when (sum((case when (((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) or ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False')))) then 1 else 0 end)) > 0) then 1 else 0 end) else 0 end) else NULL end) AS `indicator_fraction` from ((((`cluster_indicator_targets` `cit` join `performance_indicators` `pi` on(((`pi`.`id` = cast(`cit`.`IndicatorID` as unsigned)) and (`pi`.`id` <> 0) and (json_contains(`pi`.`Responsible_Cluster`,json_quote(`cit`.`ClusterID`)) = 1)))) join `clusters` `c` on((`c`.`ClusterID` = `cit`.`ClusterID`))) join `ecsahc_timelines` `t` on(((`t`.`status` in ('In Progress','Completed')) and (((`cit`.`Target_Year` like '%-%') and (`t`.`Year` between cast(substring_index(`cit`.`Target_Year`,'-',1) as unsigned) and cast(substring_index(`cit`.`Target_Year`,'-',-(1)) as unsigned))) or ((not((`cit`.`Target_Year` like '%-%'))) and (`t`.`Year` = cast(`cit`.`Target_Year` as unsigned))))))) left join `cluster_performance_mappings` `cpm` on(((`cpm`.`ClusterID` = `cit`.`ClusterID`) and (`cpm`.`IndicatorID` = `cit`.`IndicatorID`) and (`cpm`.`ReportingID` = `t`.`ReportingID`)))) group by `c`.`id`,`c`.`ClusterID`,`c`.`Cluster_Name`,`t`.`Year`,`t`.`Quarter`,`pi`.`id`,`pi`.`ResponseType`,`cit`.`Target_Value`) `frac` group by `frac`.`cluster_pk`,`frac`.`cluster_code`,`frac`.`cluster_name`,`frac`.`year_val`) AS `agg` GROUP BY `agg`.`cluster_pk`, `agg`.`year_val` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_cluster_rank_by_quarter`
--
DROP TABLE IF EXISTS `vw_cluster_rank_by_quarter`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_cluster_rank_by_quarter`  AS SELECT `sums`.`cluster_pk` AS `cluster_pk`, `sums`.`cluster_code` AS `cluster_code`, `sums`.`cluster_name` AS `cluster_name`, `sums`.`year_val` AS `timeline_year`, (case when (`sums`.`quarter_val` = 1) then 'First Quarter' when (`sums`.`quarter_val` = 2) then 'Second Quarter' when (`sums`.`quarter_val` = 3) then 'Third Quarter' when (`sums`.`quarter_val` = 4) then 'Fourth Quarter' else concat('Q',`sums`.`quarter_val`) end) AS `quarter_label`, `sums`.`final_score_percent` AS `final_score_percent`, dense_rank() OVER (PARTITION BY `sums`.`year_val`,`sums`.`quarter_val` ORDER BY `sums`.`final_score_percent` desc ) AS `cluster_rank`, (case when (`sums`.`final_score_percent` < 10) then 'Needs Attention' when (`sums`.`final_score_percent` < 50) then 'Progressing' when (`sums`.`final_score_percent` < 90) then 'On Track' else 'Met' end) AS `status_label`, (case when ((`sums`.`sum_target_val` > 0) and (`sums`.`sum_actual_val` > `sums`.`sum_target_val`)) then 'Over Achieved' else '' end) AS `comment` FROM (select `frac`.`cluster_pk` AS `cluster_pk`,`frac`.`cluster_code` AS `cluster_code`,`frac`.`cluster_name` AS `cluster_name`,`frac`.`year_val` AS `year_val`,`frac`.`quarter_val` AS `quarter_val`,sum(`frac`.`sum_actual_val`) AS `sum_actual_val`,sum(`frac`.`sum_target_val`) AS `sum_target_val`,cast((avg(`frac`.`indicator_fraction`) * 100) as decimal(6,2)) AS `final_score_percent` from (select `c`.`id` AS `cluster_pk`,`c`.`ClusterID` AS `cluster_code`,`c`.`Cluster_Name` AS `cluster_name`,`t`.`Year` AS `year_val`,`t`.`Quarter` AS `quarter_val`,(case when (`pi`.`ResponseType` = 'Number') then cast(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)) as decimal(20,4)) when (`pi`.`ResponseType` in ('Yes/No','Boolean')) then (case when (sum((case when (((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) or ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False')))) then 1 else 0 end)) > 0) then 1 else 0 end) else 0 end) AS `sum_actual_val`,(case when ((`pi`.`ResponseType` = 'Number') and regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cit`.`Target_Value` as decimal(20,4)) when ((`pi`.`ResponseType` in ('Yes/No','Boolean')) and (`cit`.`Target_Value` in ('Yes','True','No','False'))) then 1 else 0 end) AS `sum_target_val`,(case when (`pi`.`ResponseType` = 'Number') then (case when (regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$') and (cast(`cit`.`Target_Value` as decimal(20,4)) > 0)) then least(1,greatest(0,(cast(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)) as decimal(20,4)) / cast(`cit`.`Target_Value` as decimal(20,4))))) else 0 end) when (`pi`.`ResponseType` in ('Yes/No','Boolean')) then (case when (`cit`.`Target_Value` in ('Yes','True','No','False')) then (case when (sum((case when (((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) or ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False')))) then 1 else 0 end)) > 0) then 1 else 0 end) else 0 end) else NULL end) AS `indicator_fraction` from ((((`cluster_indicator_targets` `cit` join `performance_indicators` `pi` on(((`pi`.`id` = cast(`cit`.`IndicatorID` as unsigned)) and (`pi`.`id` <> 0) and (json_contains(`pi`.`Responsible_Cluster`,json_quote(`cit`.`ClusterID`)) = 1)))) join `clusters` `c` on((`c`.`ClusterID` = `cit`.`ClusterID`))) join `ecsahc_timelines` `t` on(((`t`.`status` in ('In Progress','Completed')) and (((`cit`.`Target_Year` like '%-%') and (`t`.`Year` between cast(substring_index(`cit`.`Target_Year`,'-',1) as unsigned) and cast(substring_index(`cit`.`Target_Year`,'-',-(1)) as unsigned))) or ((not((`cit`.`Target_Year` like '%-%'))) and (`t`.`Year` = cast(`cit`.`Target_Year` as unsigned))))))) left join `cluster_performance_mappings` `cpm` on(((`cpm`.`ClusterID` = `cit`.`ClusterID`) and (`cpm`.`IndicatorID` = `cit`.`IndicatorID`) and (`cpm`.`ReportingID` = `t`.`ReportingID`)))) group by `c`.`id`,`c`.`ClusterID`,`c`.`Cluster_Name`,`t`.`Year`,`t`.`Quarter`,`pi`.`id`,`pi`.`ResponseType`,`cit`.`Target_Value`) `frac` group by `frac`.`cluster_pk`,`frac`.`cluster_code`,`frac`.`cluster_name`,`frac`.`year_val`,`frac`.`quarter_val`) AS `sums` ORDER BY `sums`.`year_val` ASC, `sums`.`quarter_val` ASC, `sums`.`final_score_percent` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_cluster_rank_semiannual`
--
DROP TABLE IF EXISTS `vw_cluster_rank_semiannual`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_cluster_rank_semiannual`  AS SELECT `sums`.`cluster_pk` AS `cluster_pk`, `sums`.`cluster_code` AS `cluster_code`, `sums`.`cluster_name` AS `cluster_name`, `sums`.`year_val` AS `timeline_year`, (case when (`sums`.`half` = 1) then 'First Semi Annual' else 'Second Semi Annual' end) AS `semi_annual_label`, `sums`.`final_score_percent` AS `final_score_percent`, dense_rank() OVER (PARTITION BY `sums`.`year_val`,`sums`.`half` ORDER BY `sums`.`final_score_percent` desc ) AS `cluster_rank`, (case when (`sums`.`final_score_percent` < 10) then 'Needs Attention' when (`sums`.`final_score_percent` < 50) then 'Progressing' when (`sums`.`final_score_percent` < 90) then 'On Track' else 'Met' end) AS `status_label`, (case when ((`sums`.`sum_target_val` > 0) and (`sums`.`sum_actual_val` > `sums`.`sum_target_val`)) then 'Over Achieved' else '' end) AS `comment` FROM (select `frac`.`cluster_pk` AS `cluster_pk`,`frac`.`cluster_code` AS `cluster_code`,`frac`.`cluster_name` AS `cluster_name`,`frac`.`year_val` AS `year_val`,(case when (`frac`.`quarter_val` in (1,2)) then 1 else 2 end) AS `half`,sum(`frac`.`sum_actual_val`) AS `sum_actual_val`,sum(`frac`.`sum_target_val`) AS `sum_target_val`,cast((avg(`frac`.`indicator_fraction`) * 100) as decimal(6,2)) AS `final_score_percent` from (select `c`.`id` AS `cluster_pk`,`c`.`ClusterID` AS `cluster_code`,`c`.`Cluster_Name` AS `cluster_name`,`t`.`Year` AS `year_val`,`t`.`Quarter` AS `quarter_val`,(case when (`pi`.`ResponseType` = 'Number') then cast(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)) as decimal(20,4)) when (`pi`.`ResponseType` in ('Yes/No','Boolean')) then (case when (sum((case when (((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) or ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False')))) then 1 else 0 end)) > 0) then 1 else 0 end) else 0 end) AS `sum_actual_val`,(case when ((`pi`.`ResponseType` = 'Number') and regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cit`.`Target_Value` as decimal(20,4)) when ((`pi`.`ResponseType` in ('Yes/No','Boolean')) and (`cit`.`Target_Value` in ('Yes','True','No','False'))) then 1 else 0 end) AS `sum_target_val`,(case when (`pi`.`ResponseType` = 'Number') then (case when (regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$') and (cast(`cit`.`Target_Value` as decimal(20,4)) > 0)) then least(1,greatest(0,(cast(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)) as decimal(20,4)) / cast(`cit`.`Target_Value` as decimal(20,4))))) else 0 end) when (`pi`.`ResponseType` in ('Yes/No','Boolean')) then (case when (`cit`.`Target_Value` in ('Yes','True','No','False')) then (case when (sum((case when (((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) or ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False')))) then 1 else 0 end)) > 0) then 1 else 0 end) else 0 end) else NULL end) AS `indicator_fraction` from ((((`cluster_indicator_targets` `cit` join `performance_indicators` `pi` on(((`pi`.`id` = cast(`cit`.`IndicatorID` as unsigned)) and (`pi`.`id` <> 0) and (json_contains(`pi`.`Responsible_Cluster`,json_quote(`cit`.`ClusterID`)) = 1)))) join `clusters` `c` on((`c`.`ClusterID` = `cit`.`ClusterID`))) join `ecsahc_timelines` `t` on(((`t`.`status` in ('In Progress','Completed')) and (((`cit`.`Target_Year` like '%-%') and (`t`.`Year` between cast(substring_index(`cit`.`Target_Year`,'-',1) as unsigned) and cast(substring_index(`cit`.`Target_Year`,'-',-(1)) as unsigned))) or ((not((`cit`.`Target_Year` like '%-%'))) and (`t`.`Year` = cast(`cit`.`Target_Year` as unsigned))))))) left join `cluster_performance_mappings` `cpm` on(((`cpm`.`ClusterID` = `cit`.`ClusterID`) and (`cpm`.`IndicatorID` = `cit`.`IndicatorID`) and (`cpm`.`ReportingID` = `t`.`ReportingID`)))) group by `c`.`id`,`c`.`ClusterID`,`c`.`Cluster_Name`,`t`.`Year`,`t`.`Quarter`,`pi`.`id`,`pi`.`ResponseType`,`cit`.`Target_Value`) `frac` group by `frac`.`cluster_pk`,`frac`.`cluster_code`,`frac`.`cluster_name`,`frac`.`year_val`,(case when (`frac`.`quarter_val` in (1,2)) then 1 else 2 end)) AS `sums` ORDER BY `sums`.`year_val` ASC, `sums`.`half` ASC, `sums`.`final_score_percent` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_cluster_so_performance_summary`
--
DROP TABLE IF EXISTS `vw_cluster_so_performance_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_cluster_so_performance_summary`  AS SELECT `calc`.`cluster_pk` AS `cluster_pk`, `calc`.`cluster_code` AS `cluster_code`, `calc`.`cluster_name` AS `cluster_name`, `calc`.`so_pk` AS `so_pk`, `calc`.`so_number` AS `so_number`, `calc`.`so_name` AS `so_name`, `calc`.`timeline_pk` AS `timeline_pk`, `calc`.`timeline_name` AS `timeline_name`, `calc`.`timeline_year` AS `timeline_year`, `calc`.`timeline_quarter` AS `timeline_quarter`, `calc`.`timeline_closing_date` AS `timeline_closing_date`, `calc`.`timeline_status` AS `timeline_status`, count(`calc`.`indicator_pk`) AS `total_indicators_in_group`, cast((avg(`calc`.`indicator_fraction`) * 100) as decimal(6,2)) AS `final_score_percent`, (case when (cast((avg(`calc`.`indicator_fraction`) * 100) as decimal(6,2)) < 10) then 'Needs Attention' when (cast((avg(`calc`.`indicator_fraction`) * 100) as decimal(6,2)) < 50) then 'In Progress' when (cast((avg(`calc`.`indicator_fraction`) * 100) as decimal(6,2)) < 90) then 'On Track' else 'Met' end) AS `status_label` FROM (select `sums`.`cluster_pk` AS `cluster_pk`,`sums`.`cluster_code` AS `cluster_code`,`sums`.`cluster_name` AS `cluster_name`,`sums`.`timeline_pk` AS `timeline_pk`,`sums`.`timeline_name` AS `timeline_name`,`sums`.`timeline_year` AS `timeline_year`,`sums`.`timeline_quarter` AS `timeline_quarter`,`sums`.`timeline_closing_date` AS `timeline_closing_date`,`sums`.`timeline_status` AS `timeline_status`,`sums`.`indicator_pk` AS `indicator_pk`,`sums`.`so_pk` AS `so_pk`,`sums`.`so_number` AS `so_number`,`sums`.`so_name` AS `so_name`,(case when (`sums`.`indicator_response_type` = 'Number') then (case when (`sums`.`sum_target_val` = 0) then NULL else least(1,greatest(0,(`sums`.`sum_actual_val` / `sums`.`sum_target_val`))) end) when (`sums`.`indicator_response_type` in ('Yes/No','Boolean')) then (case when (`sums`.`target_is_yes` = 1) then `sums`.`any_yes` when (`sums`.`target_is_no` = 1) then `sums`.`any_no` else 0 end) else NULL end) AS `indicator_fraction` from (select `c`.`id` AS `cluster_pk`,`c`.`ClusterID` AS `cluster_code`,`c`.`Cluster_Name` AS `cluster_name`,`t`.`id` AS `timeline_pk`,`t`.`ReportName` AS `timeline_name`,`t`.`Year` AS `timeline_year`,`t`.`Quarter` AS `timeline_quarter`,`t`.`ClosingDate` AS `timeline_closing_date`,`t`.`status` AS `timeline_status`,`t`.`ReportingID` AS `timeline_reporting_id`,`pi`.`id` AS `indicator_pk`,`pi`.`ResponseType` AS `indicator_response_type`,`so`.`id` AS `so_pk`,`so`.`SO_Number` AS `so_number`,`so`.`SO_Name` AS `so_name`,cast(sum((case when regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$') then cast(`cit`.`Target_Value` as decimal(20,4)) else 0 end)) as decimal(20,4)) AS `sum_target_val`,cast(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)) as decimal(20,4)) AS `sum_actual_val`,max((case when (`cpm`.`Response` in ('Yes','True')) then 1 else 0 end)) AS `any_yes`,max((case when (`cpm`.`Response` in ('No','False')) then 1 else 0 end)) AS `any_no`,max((case when (`cit`.`Target_Value` in ('Yes','True')) then 1 else 0 end)) AS `target_is_yes`,max((case when (`cit`.`Target_Value` in ('No','False')) then 1 else 0 end)) AS `target_is_no` from (((((`cluster_indicator_targets` `cit` join `performance_indicators` `pi` on(((`pi`.`id` = cast(`cit`.`IndicatorID` as unsigned)) and (`pi`.`id` <> 0) and (json_contains(`pi`.`Responsible_Cluster`,json_quote(`cit`.`ClusterID`)) = 1)))) join `clusters` `c` on((`c`.`ClusterID` = `cit`.`ClusterID`))) left join `strategic_objectives` `so` on((`so`.`SO_Number` = `pi`.`SO_ID`))) join `ecsahc_timelines` `t` on(((`t`.`status` in ('In Progress','Completed')) and (((`cit`.`Target_Year` like '%-%') and (`t`.`Year` between cast(substring_index(`cit`.`Target_Year`,'-',1) as unsigned) and cast(substring_index(`cit`.`Target_Year`,'-',-(1)) as unsigned))) or ((not((`cit`.`Target_Year` like '%-%'))) and (`t`.`Year` = cast(`cit`.`Target_Year` as unsigned))))))) left join `cluster_performance_mappings` `cpm` on(((`cpm`.`ClusterID` = `cit`.`ClusterID`) and (`cpm`.`IndicatorID` = `cit`.`IndicatorID`) and (`cpm`.`ReportingID` = `t`.`ReportingID`)))) group by `c`.`id`,`c`.`ClusterID`,`c`.`Cluster_Name`,`t`.`id`,`t`.`ReportName`,`t`.`Year`,`t`.`Quarter`,`t`.`ClosingDate`,`t`.`status`,`t`.`ReportingID`,`pi`.`id`,`pi`.`ResponseType`,`so`.`id`,`so`.`SO_Number`,`so`.`SO_Name`) `sums`) AS `calc` GROUP BY `calc`.`cluster_pk`, `calc`.`cluster_code`, `calc`.`cluster_name`, `calc`.`so_pk`, `calc`.`so_number`, `calc`.`so_name`, `calc`.`timeline_pk`, `calc`.`timeline_name`, `calc`.`timeline_year`, `calc`.`timeline_quarter`, `calc`.`timeline_closing_date`, `calc`.`timeline_status` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_cluster_vs_target_achievements`
--
DROP TABLE IF EXISTS `vw_cluster_vs_target_achievements`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_cluster_vs_target_achievements`  AS SELECT `c`.`id` AS `cluster_pk`, `c`.`ClusterID` AS `cluster_code`, `c`.`Cluster_Name` AS `cluster_name`, `pi`.`id` AS `indicator_pk`, `pi`.`Indicator_Number` AS `indicator_number`, `pi`.`Indicator_Name` AS `indicator_name`, `pi`.`ResponseType` AS `indicator_response_type`, `t`.`id` AS `timeline_pk`, `t`.`ReportName` AS `timeline_name`, `t`.`Year` AS `timeline_year`, `t`.`Quarter` AS `timeline_quarter`, `t`.`ClosingDate` AS `timeline_closing_date`, `t`.`status` AS `timeline_status`, `cit`.`id` AS `cluster_target_pk`, `cit`.`Target_Year` AS `target_year_string`, `cit`.`Target_Value` AS `target_value_raw`, cast(sum((case when (`pi`.`ResponseType` = 'Number') then (case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cpm`.`Response` as decimal(20,4)) else 0 end) when (`pi`.`ResponseType` in ('Yes/No','Boolean')) then (case when ((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) then 1 when ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False'))) then 1 else 0 end) else 0 end)) as decimal(20,4)) AS `sum_of_raw_actual_before_clamp`, (case when (`pi`.`ResponseType` = 'Number') then (case when regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$') then cast(`cit`.`Target_Value` as decimal(20,4)) else 0 end) when (`pi`.`ResponseType` in ('Yes/No','Boolean')) then (case when (`cit`.`Target_Value` in ('Yes','True','No','False')) then 1 else 0 end) else 0 end) AS `target_value_for_calc`, (case when (`pi`.`ResponseType` = 'Number') then cast(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)) as decimal(20,4)) when (`pi`.`ResponseType` in ('Yes/No','Boolean')) then (case when (sum((case when (((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) or ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False')))) then 1 else 0 end)) > 0) then 1 else 0 end) else 0 end) AS `total_actual_value`, (case when ((case when ((`pi`.`ResponseType` = 'Number') and regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cit`.`Target_Value` as decimal(20,4)) when ((`pi`.`ResponseType` in ('Yes/No','Boolean')) and (`cit`.`Target_Value` in ('Yes','True','No','False'))) then 1 else 0 end) = 0) then 0 else least(100,greatest(0,(((case when (`pi`.`ResponseType` = 'Number') then cast(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)) as decimal(20,4)) when (`pi`.`ResponseType` in ('Yes/No','Boolean')) then (case when (sum((case when (((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) or ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False')))) then 1 else 0 end)) > 0) then 1 else 0 end) else 0 end) / (case when ((`pi`.`ResponseType` = 'Number') and regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cit`.`Target_Value` as decimal(20,4)) when ((`pi`.`ResponseType` in ('Yes/No','Boolean')) and (`cit`.`Target_Value` in ('Yes','True','No','False'))) then 1 else 0 end)) * 100))) end) AS `achievement_percent`, (case when ((case when ((`pi`.`ResponseType` = 'Number') and regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$')) then least(100,greatest(0,((cast(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)) as decimal(20,4)) / cast(`cit`.`Target_Value` as decimal(20,4))) * 100))) when ((`pi`.`ResponseType` in ('Yes/No','Boolean')) and (`cit`.`Target_Value` in ('Yes','True','No','False'))) then (case when (sum((case when (((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) or ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False')))) then 1 else 0 end)) > 0) then 100 else 0 end) else 0 end) < 10) then 'Needs Attention' when ((case when ((`pi`.`ResponseType` = 'Number') and regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$')) then least(100,greatest(0,((cast(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)) as decimal(20,4)) / cast(`cit`.`Target_Value` as decimal(20,4))) * 100))) when ((`pi`.`ResponseType` in ('Yes/No','Boolean')) and (`cit`.`Target_Value` in ('Yes','True','No','False'))) then (case when (sum((case when (((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) or ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False')))) then 1 else 0 end)) > 0) then 100 else 0 end) else 0 end) < 50) then 'In Progress' when ((case when ((`pi`.`ResponseType` = 'Number') and regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$')) then least(100,greatest(0,((cast(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)) as decimal(20,4)) / cast(`cit`.`Target_Value` as decimal(20,4))) * 100))) when ((`pi`.`ResponseType` in ('Yes/No','Boolean')) and (`cit`.`Target_Value` in ('Yes','True','No','False'))) then (case when (sum((case when (((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) or ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False')))) then 1 else 0 end)) > 0) then 100 else 0 end) else 0 end) < 90) then 'On Track' else 'Met' end) AS `status_label` FROM ((((`cluster_indicator_targets` `cit` join `performance_indicators` `pi` on(((`pi`.`id` = cast(`cit`.`IndicatorID` as unsigned)) and (`pi`.`id` <> 0) and (json_contains(`pi`.`Responsible_Cluster`,json_quote(`cit`.`ClusterID`)) = 1)))) join `clusters` `c` on((`c`.`ClusterID` = `cit`.`ClusterID`))) join `ecsahc_timelines` `t` on(((`t`.`status` in ('In Progress','Completed')) and (((`cit`.`Target_Year` like '%-%') and (`t`.`Year` between cast(substring_index(`cit`.`Target_Year`,'-',1) as unsigned) and cast(substring_index(`cit`.`Target_Year`,'-',-(1)) as unsigned))) or ((not((`cit`.`Target_Year` like '%-%'))) and (`t`.`Year` = cast(`cit`.`Target_Year` as unsigned))))))) left join `cluster_performance_mappings` `cpm` on(((`cpm`.`ClusterID` = `cit`.`ClusterID`) and (`cpm`.`IndicatorID` = `cit`.`IndicatorID`) and (`cpm`.`ReportingID` = `t`.`ReportingID`)))) GROUP BY `c`.`id`, `c`.`ClusterID`, `c`.`Cluster_Name`, `pi`.`id`, `pi`.`Indicator_Number`, `pi`.`Indicator_Name`, `pi`.`ResponseType`, `t`.`id`, `t`.`ReportName`, `t`.`Year`, `t`.`Quarter`, `t`.`ClosingDate`, `t`.`status`, `cit`.`id`, `cit`.`Target_Year`, `cit`.`Target_Value` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_indicator_target_and_performance_full`
--
DROP TABLE IF EXISTS `vw_indicator_target_and_performance_full`;

CREATE ALGORITHM=UNDEFINED DEFINER=`hacker`@`localhost` SQL SECURITY DEFINER VIEW `vw_indicator_target_and_performance_full`  AS SELECT `cpm`.`id` AS `performance_pk`, `cpm`.`ClusterID` AS `performance_cluster_id_db`, `cpm`.`ReportingID` AS `ReportingID`, `cpm`.`SO_ID` AS `SO_ID`, `cpm`.`UserID` AS `UserID`, `cpm`.`IndicatorID` AS `performance_indicator_id`, `cpm`.`Response` AS `Response`, `cpm`.`ReportingComment` AS `ReportingComment`, `cpm`.`ResponseType` AS `performance_response_type`, `cpm`.`Baseline_2023_2024` AS `Baseline_2023_2024`, `cpm`.`Target_Year1` AS `Target_Year1`, `cpm`.`Target_Year2` AS `Target_Year2`, `cpm`.`Target_Year3` AS `Target_Year3`, `cpm`.`created_at` AS `performance_created_at`, `cpm`.`updated_at` AS `performance_updated_at`, `cit`.`id` AS `target_pk`, `cit`.`ClusterTargetID` AS `ClusterTargetID`, `cit`.`IndicatorID` AS `target_indicator_id`, `cit`.`Target_Year` AS `Target_Year`, `cit`.`Target_Value` AS `Target_Value`, `cit`.`ResponseType` AS `target_response_type`, `cit`.`Baseline2024` AS `Baseline2024`, `cit`.`created_at` AS `target_created_at`, `cit`.`updated_at` AS `target_updated_at`, `c`.`id` AS `cluster_pk`, `c`.`Description` AS `cluster_description`, `c`.`Cluster_Name` AS `cluster_name`, `c`.`created_at` AS `cluster_created_at`, `c`.`updated_at` AS `cluster_updated_at`, `c`.`ClusterID` AS `cluster_text_identifier`, `so`.`id` AS `so_pk`, `so`.`SO_Name` AS `SO_Name`, `so`.`Description` AS `so_description`, `so`.`SO_Number` AS `SO_Number`, `so`.`StrategicObjectiveID` AS `StrategicObjectiveID`, `so`.`created_at` AS `so_created_at`, `so`.`updated_at` AS `so_updated_at`, `t`.`id` AS `timeline_pk`, `t`.`ReportName` AS `ReportName`, `t`.`Type` AS `timeline_type`, `t`.`Description` AS `timeline_description`, `t`.`ReportingID` AS `timeline_reporting_id`, `t`.`Year` AS `timeline_year`, `t`.`ClosingDate` AS `timeline_closing_date`, `t`.`status` AS `timeline_status`, `t`.`created_at` AS `timeline_created_at`, `t`.`updated_at` AS `timeline_updated_at`, `t`.`Quarter` AS `timeline_quarter`, `u`.`id` AS `user_pk`, `u`.`name` AS `user_name`, `u`.`email` AS `user_email`, `u`.`ClusterID` AS `user_cluster_id`, `u`.`UserType` AS `user_type`, `u`.`UserCode` AS `user_code`, `u`.`Phone` AS `user_phone`, `u`.`Nationality` AS `user_nationality`, `u`.`Address` AS `user_address`, `u`.`ParentOrganization` AS `user_parent_org`, `u`.`Sex` AS `user_sex`, `u`.`JobTitle` AS `user_job_title`, `u`.`AccountRole` AS `user_account_role`, `u`.`created_at` AS `user_created_at`, `u`.`updated_at` AS `user_updated_at` FROM (((((`cluster_performance_mappings` `cpm` join `cluster_indicator_targets` `cit` on(((`cpm`.`ClusterID` = `cit`.`ClusterID`) and (`cpm`.`IndicatorID` = `cit`.`IndicatorID`)))) join `clusters` `c` on((`cpm`.`ClusterID` = `c`.`ClusterID`))) left join `strategic_objectives` `so` on((`cpm`.`SO_ID` = `so`.`SO_Number`))) left join `ecsahc_timelines` `t` on((`cpm`.`ReportingID` = `t`.`ReportingID`))) left join `users` `u` on((`cpm`.`UserID` = `u`.`UserID`))) ;

-- --------------------------------------------------------

--
-- Structure for view `vw_performance_over_time_by_quarter`
--
DROP TABLE IF EXISTS `vw_performance_over_time_by_quarter`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_performance_over_time_by_quarter`  AS SELECT `c`.`id` AS `cluster_pk`, `c`.`ClusterID` AS `cluster_code`, `c`.`Cluster_Name` AS `cluster_name`, `pi`.`id` AS `indicator_pk`, `pi`.`Indicator_Number` AS `indicator_number`, `pi`.`Indicator_Name` AS `indicator_name`, `pi`.`ResponseType` AS `indicator_response_type`, `t`.`id` AS `timeline_pk`, `t`.`ReportName` AS `timeline_name`, `t`.`Year` AS `timeline_year`, `t`.`Quarter` AS `timeline_quarter`, `t`.`ClosingDate` AS `timeline_closing_date`, `t`.`status` AS `timeline_status`, `cit`.`id` AS `cluster_target_pk`, `cit`.`Target_Year` AS `target_year_string`, `cit`.`Target_Value` AS `target_value_raw`, (case when (`pi`.`ResponseType` = 'Number') then cast(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)) as decimal(20,4)) when (`pi`.`ResponseType` in ('Yes/No','Boolean')) then (case when (sum((case when (((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) or ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False')))) then 1 else 0 end)) > 0) then 1 else 0 end) else 0 end) AS `total_actual_value`, (case when ((`pi`.`ResponseType` = 'Number') and regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cit`.`Target_Value` as decimal(20,4)) when ((`pi`.`ResponseType` in ('Yes/No','Boolean')) and (`cit`.`Target_Value` in ('Yes','True','No','False'))) then 1 else 0 end) AS `total_target_value`, (case when ((case when ((`pi`.`ResponseType` = 'Number') and regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cit`.`Target_Value` as decimal(20,4)) when ((`pi`.`ResponseType` in ('Yes/No','Boolean')) and (`cit`.`Target_Value` in ('Yes','True','No','False'))) then 1 else 0 end) <= 0) then 0 else least(100,greatest(0,(((case when (`pi`.`ResponseType` = 'Number') then cast(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then `cpm`.`Response` else '0' end)) as decimal(20,4)) when (`pi`.`ResponseType` in ('Yes/No','Boolean')) then (case when (sum((case when (((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) or ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False')))) then 1 else 0 end)) > 0) then 1 else 0 end) else 0 end) / (case when ((`pi`.`ResponseType` = 'Number') and regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cit`.`Target_Value` as decimal(20,4)) when ((`pi`.`ResponseType` in ('Yes/No','Boolean')) and (`cit`.`Target_Value` in ('Yes','True','No','False'))) then 1 else 0 end)) * 100))) end) AS `achievement_percent`, (case when ((case when ((`pi`.`ResponseType` = 'Number') and regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$')) then least(100,greatest(0,((cast(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then `cpm`.`Response` else '0' end)) as decimal(20,4)) / cast(`cit`.`Target_Value` as decimal(20,4))) * 100))) when ((`pi`.`ResponseType` in ('Yes/No','Boolean')) and (`cit`.`Target_Value` in ('Yes','True','No','False'))) then (case when (sum((case when (((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) or ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False')))) then 1 else 0 end)) > 0) then 100 else 0 end) else 0 end) < 10) then 'Needs Attention' when ((case when ((`pi`.`ResponseType` = 'Number') and regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$')) then least(100,greatest(0,((cast(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then `cpm`.`Response` else '0' end)) as decimal(20,4)) / cast(`cit`.`Target_Value` as decimal(20,4))) * 100))) when ((`pi`.`ResponseType` in ('Yes/No','Boolean')) and (`cit`.`Target_Value` in ('Yes','True','No','False'))) then (case when (sum((case when (((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) or ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False')))) then 1 else 0 end)) > 0) then 100 else 0 end) else 0 end) < 50) then 'In Progress' when ((case when ((`pi`.`ResponseType` = 'Number') and regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$')) then least(100,greatest(0,((cast(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then `cpm`.`Response` else '0' end)) as decimal(20,4)) / cast(`cit`.`Target_Value` as decimal(20,4))) * 100))) when ((`pi`.`ResponseType` in ('Yes/No','Boolean')) and (`cit`.`Target_Value` in ('Yes','True','No','False'))) then (case when (sum((case when (((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) or ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False')))) then 1 else 0 end)) > 0) then 100 else 0 end) else 0 end) < 90) then 'On Track' else 'Met' end) AS `status_label`, (case when ((case when (`pi`.`ResponseType` = 'Number') then cast(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then `cpm`.`Response` else '0' end)) as decimal(20,4)) when (`pi`.`ResponseType` in ('Yes/No','Boolean')) then (case when (sum((case when (((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) or ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False')))) then 1 else 0 end)) > 0) then 1 else 0 end) else 0 end) > (case when ((`pi`.`ResponseType` = 'Number') and regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cit`.`Target_Value` as decimal(20,4)) when ((`pi`.`ResponseType` in ('Yes/No','Boolean')) and (`cit`.`Target_Value` in ('Yes','True','No','False'))) then 1 else 0 end)) then 'Over Achieved' else '' end) AS `comment` FROM ((((`cluster_indicator_targets` `cit` join `performance_indicators` `pi` on(((`pi`.`id` = cast(`cit`.`IndicatorID` as unsigned)) and (`pi`.`id` <> 0) and (json_contains(`pi`.`Responsible_Cluster`,json_quote(`cit`.`ClusterID`)) = 1)))) join `clusters` `c` on((`c`.`ClusterID` = `cit`.`ClusterID`))) join `ecsahc_timelines` `t` on(((`t`.`status` in ('In Progress','Completed')) and (((`cit`.`Target_Year` like '%-%') and (`t`.`Year` between cast(substring_index(`cit`.`Target_Year`,'-',1) as unsigned) and cast(substring_index(`cit`.`Target_Year`,'-',-(1)) as unsigned))) or ((not((`cit`.`Target_Year` like '%-%'))) and (`t`.`Year` = cast(`cit`.`Target_Year` as unsigned))))))) left join `cluster_performance_mappings` `cpm` on(((`cpm`.`ClusterID` = `cit`.`ClusterID`) and (`cpm`.`IndicatorID` = `cit`.`IndicatorID`) and (`cpm`.`ReportingID` = `t`.`ReportingID`)))) GROUP BY `c`.`id`, `c`.`ClusterID`, `c`.`Cluster_Name`, `pi`.`id`, `pi`.`Indicator_Number`, `pi`.`Indicator_Name`, `pi`.`ResponseType`, `t`.`id`, `t`.`ReportName`, `t`.`Year`, `t`.`Quarter`, `t`.`ClosingDate`, `t`.`status`, `cit`.`id`, `cit`.`Target_Year`, `cit`.`Target_Value` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_semi_annual_cluster_summary`
--
DROP TABLE IF EXISTS `vw_semi_annual_cluster_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_semi_annual_cluster_summary`  AS SELECT `sp`.`cluster_pk` AS `cluster_pk`, `sp`.`cluster_code` AS `cluster_code`, `sp`.`cluster_name` AS `cluster_name`, `sp`.`timeline_year` AS `timeline_year`, `sp`.`semi_annual_label` AS `semi_annual_label`, count(0) AS `total_indicators`, round(avg(`sp`.`achievement_percent`),2) AS `average_achievement`, sum((case when (`sp`.`achievement_percent` < 10) then 1 else 0 end)) AS `needs_attention`, sum((case when ((`sp`.`achievement_percent` >= 10) and (`sp`.`achievement_percent` < 50)) then 1 else 0 end)) AS `progressing`, sum((case when ((`sp`.`achievement_percent` >= 50) and (`sp`.`achievement_percent` < 90)) then 1 else 0 end)) AS `on_track`, sum((case when (`sp`.`achievement_percent` >= 90) then 1 else 0 end)) AS `met` FROM `vw_semi_annual_performance` AS `sp` GROUP BY `sp`.`cluster_pk`, `sp`.`cluster_code`, `sp`.`cluster_name`, `sp`.`timeline_year`, `sp`.`semi_annual_label` ORDER BY `sp`.`timeline_year` ASC, `sp`.`semi_annual_label` ASC, `sp`.`cluster_name` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_semi_annual_performance`
--
DROP TABLE IF EXISTS `vw_semi_annual_performance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_semi_annual_performance`  AS SELECT `base`.`cluster_pk` AS `cluster_pk`, `base`.`cluster_code` AS `cluster_code`, `base`.`cluster_name` AS `cluster_name`, `base`.`so_pk` AS `so_pk`, `base`.`so_number` AS `so_number`, `base`.`so_name` AS `so_name`, `base`.`indicator_pk` AS `indicator_pk`, `base`.`indicator_number` AS `indicator_number`, `base`.`indicator_name` AS `indicator_name`, `base`.`indicator_response_type` AS `indicator_response_type`, `base`.`year_val` AS `timeline_year`, (case when (`base`.`semi_annual_half` = 1) then 'First Semi Annual' else 'Second Semi Annual' end) AS `semi_annual_label`, `base`.`total_actual_value` AS `raw_actual_value`, `base`.`total_target_value` AS `raw_target_value`, (case when (`base`.`total_target_value` <= 0) then 0 else least(100,greatest(0,((`base`.`total_actual_value` / `base`.`total_target_value`) * 100))) end) AS `achievement_percent`, (case when (`base`.`total_target_value` <= 0) then 'No Valid Target' else (case when (((`base`.`total_actual_value` / `base`.`total_target_value`) * 100) < 10) then 'Needs Attention' when (((`base`.`total_actual_value` / `base`.`total_target_value`) * 100) < 50) then 'In Progress' when (((`base`.`total_actual_value` / `base`.`total_target_value`) * 100) < 90) then 'On Track' else 'Met' end) end) AS `status_label`, (case when (`base`.`total_actual_value` > `base`.`total_target_value`) then 'Over Achieved' else '' end) AS `comment` FROM (select `c`.`id` AS `cluster_pk`,`c`.`ClusterID` AS `cluster_code`,`c`.`Cluster_Name` AS `cluster_name`,`pi`.`id` AS `indicator_pk`,`pi`.`Indicator_Number` AS `indicator_number`,`pi`.`Indicator_Name` AS `indicator_name`,`pi`.`ResponseType` AS `indicator_response_type`,`so`.`id` AS `so_pk`,`so`.`SO_Number` AS `so_number`,`so`.`SO_Name` AS `so_name`,`t`.`Year` AS `year_val`,(case when (`t`.`Quarter` in (1,2)) then 1 else 2 end) AS `semi_annual_half`,(case when (`pi`.`ResponseType` = 'Number') then cast(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)) as decimal(20,4)) when (`pi`.`ResponseType` in ('Yes/No','Boolean')) then (case when (sum((case when (((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) or ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False')))) then 1 else 0 end)) > 0) then 1 else 0 end) else 0 end) AS `total_actual_value`,(case when ((`pi`.`ResponseType` = 'Number') and regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(sum(cast(`cit`.`Target_Value` as decimal(20,4))) as decimal(20,4)) when ((`pi`.`ResponseType` in ('Yes/No','Boolean')) and (`cit`.`Target_Value` in ('Yes','True','No','False'))) then 1 else 0 end) AS `total_target_value` from (((((`cluster_indicator_targets` `cit` join `performance_indicators` `pi` on(((`pi`.`id` = cast(`cit`.`IndicatorID` as unsigned)) and (`pi`.`id` <> 0) and (json_contains(`pi`.`Responsible_Cluster`,json_quote(`cit`.`ClusterID`)) = 1)))) left join `strategic_objectives` `so` on((`so`.`SO_Number` = `pi`.`SO_ID`))) join `clusters` `c` on((`c`.`ClusterID` = `cit`.`ClusterID`))) join `ecsahc_timelines` `t` on(((`t`.`status` in ('In Progress','Completed')) and (((`cit`.`Target_Year` like '%-%') and (`t`.`Year` between cast(substring_index(`cit`.`Target_Year`,'-',1) as unsigned) and cast(substring_index(`cit`.`Target_Year`,'-',-(1)) as unsigned))) or ((not((`cit`.`Target_Year` like '%-%'))) and (`t`.`Year` = cast(`cit`.`Target_Year` as unsigned))))))) left join `cluster_performance_mappings` `cpm` on(((`cpm`.`ClusterID` = `cit`.`ClusterID`) and (`cpm`.`IndicatorID` = `cit`.`IndicatorID`) and (`cpm`.`ReportingID` = `t`.`ReportingID`)))) group by `c`.`id`,`c`.`ClusterID`,`c`.`Cluster_Name`,`pi`.`id`,`pi`.`Indicator_Number`,`pi`.`Indicator_Name`,`pi`.`ResponseType`,`so`.`id`,`so`.`SO_Number`,`so`.`SO_Name`,`t`.`Year`,(case when (`t`.`Quarter` in (1,2)) then 1 else 2 end)) AS `base` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_semi_annual_trend_analysis`
--
DROP TABLE IF EXISTS `vw_semi_annual_trend_analysis`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_semi_annual_trend_analysis`  AS SELECT `a`.`cluster_pk` AS `cluster_pk`, `a`.`cluster_code` AS `cluster_code`, `a`.`cluster_name` AS `cluster_name`, `a`.`timeline_year` AS `timeline_year`, `a`.`average_achievement` AS `first_half_score`, `b`.`average_achievement` AS `second_half_score`, round((`b`.`average_achievement` - `a`.`average_achievement`),2) AS `score_change`, round(((100.0 * (`b`.`average_achievement` - `a`.`average_achievement`)) / nullif(`a`.`average_achievement`,0)),2) AS `percent_change` FROM (`vw_semi_annual_cluster_summary` `a` join `vw_semi_annual_cluster_summary` `b` on(((`a`.`cluster_pk` = `b`.`cluster_pk`) and (`a`.`timeline_year` = `b`.`timeline_year`) and (`a`.`semi_annual_label` = 'First Semi Annual') and (`b`.`semi_annual_label` = 'Second Semi Annual')))) ORDER BY `a`.`cluster_name` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_so_indicators_report`
--
DROP TABLE IF EXISTS `vw_so_indicators_report`;

CREATE ALGORITHM=UNDEFINED DEFINER=`hacker`@`localhost` SQL SECURITY DEFINER VIEW `vw_so_indicators_report`  AS SELECT `so`.`id` AS `so_pk`, `so`.`SO_Number` AS `so_number`, `so`.`SO_Name` AS `so_name`, `pi`.`id` AS `indicator_id`, `pi`.`Indicator_Number` AS `Indicator_Number`, `pi`.`Indicator_Name` AS `Indicator_Name`, `pi`.`ResponseType` AS `indicator_type`, `c`.`id` AS `cluster_pk`, `c`.`ClusterID` AS `cluster_code`, `c`.`Cluster_Name` AS `cluster_name`, `t`.`id` AS `timeline_pk`, `t`.`ReportName` AS `timeline_name`, `t`.`Year` AS `timeline_year`, `t`.`Type` AS `timeline_type`, `t`.`status` AS `timeline_status`, `u`.`id` AS `user_pk`, `u`.`name` AS `user_name`, `u`.`email` AS `user_email`, `cit`.`id` AS `cluster_target_pk`, `cit`.`Target_Year` AS `target_year_string`, `cit`.`Target_Value` AS `target_value_raw`, any_value(`cpm`.`Response`) AS `user_entered_value`, (case when (`pi`.`ResponseType` = 'Number') then cast(coalesce(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)),0) as decimal(20,4)) when (`pi`.`ResponseType` = 'Yes/No') then cast(coalesce(max((case when (`cpm`.`Response` in ('Yes','True')) then 1 else 0 end)),0) as decimal(20,4)) else 0 end) AS `aggregated_actual_value`, (case when ((`pi`.`id` = 0) or (`cit`.`IndicatorID` = '0') or (`cit`.`Target_Value` is null) or (`cit`.`Target_Value` = '') or (`cit`.`Target_Value` = '0')) then 0.0 when (`pi`.`ResponseType` = 'Number') then least(100,greatest(0,((coalesce(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)),0) / cast(`cit`.`Target_Value` as decimal(20,4))) * 100))) when (`pi`.`ResponseType` = 'Yes/No') then (case when (`cit`.`Target_Value` in ('Yes','True')) then (case when (max((case when (`cpm`.`Response` in ('Yes','True')) then 1 else 0 end)) >= 1) then 100 else 0 end) when (`cit`.`Target_Value` in ('No','False')) then (case when (max((case when (`cpm`.`Response` in ('No','False')) then 1 else 0 end)) >= 1) then 100 else 0 end) else 0 end) else 0 end) AS `score_percent`, (case when ((`pi`.`id` = 0) or (`cit`.`IndicatorID` = '0') or (`cit`.`Target_Value` is null) or (`cit`.`Target_Value` = '') or (`cit`.`Target_Value` = '0')) then 'No Valid Target (Flag)' else (case when ((case when (`pi`.`ResponseType` = 'Number') then least(100,greatest(0,((coalesce(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)),0) / cast(`cit`.`Target_Value` as decimal(20,4))) * 100))) when (`pi`.`ResponseType` = 'Yes/No') then (case when (`cit`.`Target_Value` in ('Yes','True')) then (case when (max((case when (`cpm`.`Response` in ('Yes','True')) then 1 else 0 end)) >= 1) then 100 else 0 end) when (`cit`.`Target_Value` in ('No','False')) then (case when (max((case when (`cpm`.`Response` in ('No','False')) then 1 else 0 end)) >= 1) then 100 else 0 end) else 0 end) else 0 end) < 10) then 'Needs Attention' when ((case when (`pi`.`ResponseType` = 'Number') then least(100,greatest(0,((coalesce(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)),0) / cast(`cit`.`Target_Value` as decimal(20,4))) * 100))) when (`pi`.`ResponseType` = 'Yes/No') then (case when (`cit`.`Target_Value` in ('Yes','True')) then (case when (max((case when (`cpm`.`Response` in ('Yes','True')) then 1 else 0 end)) >= 1) then 100 else 0 end) when (`cit`.`Target_Value` in ('No','False')) then (case when (max((case when (`cpm`.`Response` in ('No','False')) then 1 else 0 end)) >= 1) then 100 else 0 end) else 0 end) else 0 end) < 50) then 'In Progress' when ((case when (`pi`.`ResponseType` = 'Number') then least(100,greatest(0,((coalesce(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)),0) / cast(`cit`.`Target_Value` as decimal(20,4))) * 100))) when (`pi`.`ResponseType` = 'Yes/No') then (case when (`cit`.`Target_Value` in ('Yes','True')) then (case when (max((case when (`cpm`.`Response` in ('Yes','True')) then 1 else 0 end)) >= 1) then 100 else 0 end) when (`cit`.`Target_Value` in ('No','False')) then (case when (max((case when (`cpm`.`Response` in ('No','False')) then 1 else 0 end)) >= 1) then 100 else 0 end) else 0 end) else 0 end) < 90) then 'On Track' else 'Met' end) end) AS `status_label`, (case when (`pi`.`ResponseType` = 'Number') then 'score = sum_of_numeric_responses / target_value * 100, threshold (<10, <50, <90, >=90)' when (`pi`.`ResponseType` = 'Yes/No') then 'score = 100 if any response matches the yes/no target, else 0. threshold (<10, <50, <90, >=90)' else 'Not applicable here.' end) AS `formula_explanation` FROM ((((((`cluster_indicator_targets` `cit` join `performance_indicators` `pi` on(((`pi`.`id` = cast(`cit`.`IndicatorID` as unsigned)) and (`pi`.`id` <> 0) and (json_contains(`pi`.`Responsible_Cluster`,json_quote(`cit`.`ClusterID`)) = 1)))) join `clusters` `c` on((`c`.`ClusterID` = `cit`.`ClusterID`))) join `ecsahc_timelines` `t` on(((`t`.`status` in ('In Progress','Completed')) and (((`cit`.`Target_Year` like '%-%') and (`t`.`Year` between cast(substring_index(`cit`.`Target_Year`,'-',1) as unsigned) and cast(substring_index(`cit`.`Target_Year`,'-',-(1)) as unsigned))) or ((not((`cit`.`Target_Year` like '%-%'))) and (`t`.`Year` = cast(`cit`.`Target_Year` as unsigned))))))) left join `cluster_performance_mappings` `cpm` on(((`cpm`.`ClusterID` = `cit`.`ClusterID`) and (`cpm`.`IndicatorID` = `cit`.`IndicatorID`) and (`cpm`.`ReportingID` = `t`.`ReportingID`)))) left join `strategic_objectives` `so` on((`so`.`SO_Number` = `pi`.`SO_ID`))) left join `users` `u` on((`cpm`.`UserID` = `u`.`UserID`))) WHERE ((`cit`.`IndicatorID` <> '0') AND (`pi`.`ResponseType` in ('Number','Yes/No'))) GROUP BY `so`.`id`, `so`.`SO_Number`, `so`.`SO_Name`, `pi`.`id`, `pi`.`Indicator_Number`, `pi`.`Indicator_Name`, `pi`.`ResponseType`, `c`.`id`, `c`.`ClusterID`, `c`.`Cluster_Name`, `t`.`id`, `t`.`ReportName`, `t`.`Year`, `t`.`Type`, `t`.`status`, `cit`.`id`, `cit`.`Target_Year`, `cit`.`Target_Value`, `cit`.`IndicatorID`, `u`.`id`, `u`.`name`, `u`.`email` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_threshold_alerts_and_flags`
--
DROP TABLE IF EXISTS `vw_threshold_alerts_and_flags`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_threshold_alerts_and_flags`  AS SELECT `vw_cluster_vs_target_achievements`.`cluster_pk` AS `cluster_pk`, `vw_cluster_vs_target_achievements`.`cluster_code` AS `cluster_code`, `vw_cluster_vs_target_achievements`.`cluster_name` AS `cluster_name`, `vw_cluster_vs_target_achievements`.`indicator_pk` AS `indicator_pk`, `vw_cluster_vs_target_achievements`.`indicator_number` AS `indicator_number`, `vw_cluster_vs_target_achievements`.`indicator_name` AS `indicator_name`, `vw_cluster_vs_target_achievements`.`indicator_response_type` AS `indicator_response_type`, `vw_cluster_vs_target_achievements`.`timeline_pk` AS `timeline_pk`, `vw_cluster_vs_target_achievements`.`timeline_name` AS `timeline_name`, `vw_cluster_vs_target_achievements`.`timeline_year` AS `timeline_year`, `vw_cluster_vs_target_achievements`.`timeline_quarter` AS `timeline_quarter`, `vw_cluster_vs_target_achievements`.`timeline_closing_date` AS `timeline_closing_date`, `vw_cluster_vs_target_achievements`.`timeline_status` AS `timeline_status`, `vw_cluster_vs_target_achievements`.`cluster_target_pk` AS `cluster_target_pk`, `vw_cluster_vs_target_achievements`.`target_year_string` AS `target_year_string`, `vw_cluster_vs_target_achievements`.`target_value_raw` AS `target_value_raw`, `vw_cluster_vs_target_achievements`.`total_actual_value` AS `total_actual_value`, `vw_cluster_vs_target_achievements`.`achievement_percent` AS `achievement_percent`, `vw_cluster_vs_target_achievements`.`status_label` AS `status_label`, (case when (`vw_cluster_vs_target_achievements`.`achievement_percent` < 10) then 'Needs Attention' else '' end) AS `alert_flag` FROM `vw_cluster_vs_target_achievements` WHERE ((cast(ifnull(`vw_cluster_vs_target_achievements`.`target_value_raw`,'0') as decimal(20,4)) > 0) AND (`vw_cluster_vs_target_achievements`.`achievement_percent` < 10)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `clusters`
--
ALTER TABLE `clusters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cluster_indicator_targets`
--
ALTER TABLE `cluster_indicator_targets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ClusterID` (`ClusterID`),
  ADD KEY `idx_IndicatorID` (`IndicatorID`),
  ADD KEY `idx_TargetYear` (`Target_Year`);

--
-- Indexes for table `cluster_performance_mappings`
--
ALTER TABLE `cluster_performance_mappings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ClusterID` (`ClusterID`),
  ADD KEY `idx_ReportingID` (`ReportingID`),
  ADD KEY `idx_IndicatorID` (`IndicatorID`),
  ADD KEY `idx_SO_ID` (`SO_ID`),
  ADD KEY `idx_UserID` (`UserID`);

--
-- Indexes for table `ecsahc_timelines`
--
ALTER TABLE `ecsahc_timelines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_Year` (`Year`),
  ADD KEY `idx_Quarter` (`Quarter`),
  ADD KEY `idx_Status` (`status`),
  ADD KEY `idx_ReportingID` (`ReportingID`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `strategic_objectives`
--
ALTER TABLE `strategic_objectives`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `UserCode` (`UserCode`),
  ADD UNIQUE KEY `UserID` (`UserID`),
  ADD KEY `idx_UserID` (`UserID`),
  ADD KEY `idx_Email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clusters`
--
ALTER TABLE `clusters`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cluster_indicator_targets`
--
ALTER TABLE `cluster_indicator_targets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cluster_performance_mappings`
--
ALTER TABLE `cluster_performance_mappings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ecsahc_timelines`
--
ALTER TABLE `ecsahc_timelines`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `strategic_objectives`
--
ALTER TABLE `strategic_objectives`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
