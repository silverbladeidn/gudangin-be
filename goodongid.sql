-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 21 Jun 2026 pada 04.59
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `goodongid`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#6366f1',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `color`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Elektronik', 'electronics', 'Segala benda elektronik termasuk perangkat aksesorisnya', '#3b82f6', 1, '2026-05-15 07:00:10', '2026-05-15 07:00:10'),
(2, 'Alat Tulis Kerja', 'atk', 'Alat tulis dan perlengkapan kerja lainnya', '#10b981', 1, '2026-05-15 07:00:10', '2026-05-15 07:00:10'),
(3, 'Furnitur', 'furnitur', 'Furnitur untuk sekolah', '#f59e0b', 1, '2026-05-15 07:00:10', '2026-05-15 07:00:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `email_settings`
--

CREATE TABLE `email_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_email` varchar(255) NOT NULL,
  `cc_emails` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`cc_emails`)),
  `request_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `low_stock_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `low_stock_threshold` int(11) NOT NULL DEFAULT 10,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `email_settings`
--

INSERT INTO `email_settings` (`id`, `admin_email`, `cc_emails`, `request_notifications`, `low_stock_notifications`, `low_stock_threshold`, `created_at`, `updated_at`) VALUES
(1, 'aribiya@gmail.com', '[]', 1, 1, 10, '2026-05-15 07:18:20', '2026-05-15 07:18:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_request`
--

CREATE TABLE `item_request` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_number` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `note` text DEFAULT NULL,
  `status` enum('draft','pending','approved','rejected','cancelled','partially_approved','completed') NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `item_request`
--

INSERT INTO `item_request` (`id`, `request_number`, `user_id`, `note`, `status`, `approved_by`, `approved_at`, `admin_note`, `created_at`, `updated_at`) VALUES
(1, 'REQ-20260515-001', 7, NULL, 'draft', NULL, NULL, NULL, '2026-05-15 07:19:19', '2026-05-15 07:19:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_request_details`
--

CREATE TABLE `item_request_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_request_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `requested_quantity` int(11) NOT NULL,
  `approved_quantity` int(11) NOT NULL DEFAULT 0,
  `status` enum('draft','pending','approved','rejected','cancelled','partially_approved','completed') NOT NULL DEFAULT 'pending',
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `item_request_details`
--

INSERT INTO `item_request_details` (`id`, `item_request_id`, `product_id`, `requested_quantity`, `approved_quantity`, `status`, `note`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 12, 0, 'draft', NULL, '2026-05-15 07:19:19', '2026-05-15 07:19:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_request_logs`
--

CREATE TABLE `item_request_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_request_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(255) NOT NULL,
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_data`)),
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_data`)),
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `item_request_logs`
--

INSERT INTO `item_request_logs` (`id`, `item_request_id`, `user_id`, `action`, `old_data`, `new_data`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 7, 'created_draft', NULL, '{\"user_id\":7,\"note\":null,\"request_number\":\"REQ-20260515-001\",\"status\":\"draft\",\"approved_by\":null,\"approved_at\":null,\"updated_at\":\"2026-05-15T14:19:19.000000Z\",\"created_at\":\"2026-05-15T14:19:19.000000Z\",\"id\":1}', 'Request saved as draft', '2026-05-15 07:19:19', '2026-05-15 07:19:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000001_create_cache_table', 1),
(2, '0001_01_01_000002_create_jobs_table', 1),
(3, '2025_08_25_092821_create_categories_table', 1),
(4, '2025_08_25_092858_create_products_table', 1),
(5, '2025_08_25_092916_create_stock_movements_table', 1),
(6, '2025_08_30_164308_create_personal_access_tokens_table', 1),
(7, '2025_09_27_081440_create_email_settings_table', 1),
(8, '2025_10_02_205543_create_permission_tables', 1),
(9, '2025_10_02_206543_create_users_table', 1),
(10, '2025_10_02_210541_create_user_permission_table', 1),
(11, '2025_10_02_210635_add_foreign_key_to_users_table', 1),
(12, '2025_10_27_000000_create_item_request_table', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'view_dashboard', 'web', '2026-05-15 07:00:15', '2026-05-15 07:00:15'),
(2, 'view_homeuser', 'web', '2026-05-15 07:00:15', '2026-05-15 07:00:15'),
(3, 'view_inventaris', 'web', '2026-05-15 07:00:15', '2026-05-15 07:00:15'),
(4, 'create_inventaris', 'web', '2026-05-15 07:00:15', '2026-05-15 07:00:15'),
(5, 'edit_inventaris', 'web', '2026-05-15 07:00:15', '2026-05-15 07:00:15'),
(6, 'delete_inventaris', 'web', '2026-05-15 07:00:15', '2026-05-15 07:00:15'),
(7, 'view_inventory', 'web', '2026-05-15 07:00:15', '2026-05-15 07:00:15'),
(8, 'create_inventory', 'web', '2026-05-15 07:00:15', '2026-05-15 07:00:15'),
(9, 'edit_inventory', 'web', '2026-05-15 07:00:15', '2026-05-15 07:00:15'),
(10, 'delete_inventory', 'web', '2026-05-15 07:00:15', '2026-05-15 07:00:15'),
(11, 'view_stocknote', 'web', '2026-05-15 07:00:15', '2026-05-15 07:00:15'),
(12, 'view_requestnote', 'web', '2026-05-15 07:00:15', '2026-05-15 07:00:15'),
(13, 'view_approvalnote', 'web', '2026-05-15 07:00:15', '2026-05-15 07:00:15'),
(14, 'delete_approvalnote', 'web', '2026-05-15 07:00:15', '2026-05-15 07:00:15'),
(15, 'view_users', 'web', '2026-05-15 07:00:15', '2026-05-15 07:00:15'),
(16, 'create_users', 'web', '2026-05-15 07:00:15', '2026-05-15 07:00:15'),
(17, 'edit_users', 'web', '2026-05-15 07:00:15', '2026-05-15 07:00:15'),
(18, 'delete_users', 'web', '2026-05-15 07:00:15', '2026-05-15 07:00:15'),
(19, 'view_email_settings', 'web', '2026-05-15 07:00:15', '2026-05-15 07:00:15'),
(20, 'edit_email_settings', 'web', '2026-05-15 07:00:15', '2026-05-15 07:00:15');

-- --------------------------------------------------------

--
-- Struktur dari tabel `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(3, 'App\\Models\\User', 7, 'api-token', '1af2fbcd7e0f5f1955f7f73f600209c25a67171103af92b95e20a1f9f0aef8a6', '[\"*\"]', '2026-06-20 09:52:34', NULL, '2026-06-20 09:50:03', '2026-06-20 09:52:34'),
(4, 'App\\Models\\User', 1, 'api-token', '21054ea19ffa4b6a218208d3dbf60fe062150d5a75ec002dfa83f0ab7f1817ed', '[\"*\"]', '2026-06-20 09:53:24', NULL, '2026-06-20 09:52:54', '2026-06-20 09:53:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sku` varchar(255) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `cost_price` decimal(15,2) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `min_stock_level` int(11) NOT NULL DEFAULT 0,
  `max_stock_level` int(11) DEFAULT NULL,
  `status` enum('in_stock','low_stock','out_of_stock') NOT NULL DEFAULT 'in_stock',
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `images` text DEFAULT NULL,
  `attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attributes`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `description`, `sku`, `price`, `cost_price`, `stock_quantity`, `min_stock_level`, `max_stock_level`, `status`, `category_id`, `images`, `attributes`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Monitor LCD LG', 'monitor-lcd-lg', 'Monitor LCD LG 24 inch dengan resolusi Full HD', 'MLC-001', 1000000.00, 500000.00, 45, 10, 100, 'in_stock', 1, 'products/1759113313_GUOkqpvNlx.png', NULL, 1, '2026-05-15 07:00:10', '2026-05-15 07:00:10'),
(2, 'Meja Kayu', 'meja-kayu', 'Meja untuk kantor dari kayu jati berkualitas', 'MK-002', 250000.00, 200000.00, 0, 5, 20, 'out_of_stock', 3, 'products/1758909361_Bwc8hJFEwr.jpg', NULL, 1, '2026-05-15 07:00:10', '2026-05-15 07:00:10'),
(3, 'Pensil 2B Faber-Castell', 'pensil-2b-fc', 'Pensil 2B dari Faber-Castell, cocok untuk menggambar dan menulis', 'CM-004', 1000.00, 500.00, 23, 8, 30, 'in_stock', 2, 'products/1758909219_V8YNkXPx8r.jpg', NULL, 1, '2026-05-15 07:00:10', '2026-05-15 07:00:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Superadmin', 'web', '2026-05-15 07:00:10', '2026-05-15 07:00:10'),
(2, 'Admin', 'web', '2026-05-15 07:00:10', '2026-05-15 07:00:10'),
(3, 'User', 'web', '2026-05-15 07:00:10', '2026-05-15 07:00:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 3),
(3, 1),
(3, 3),
(4, 1),
(4, 3),
(5, 1),
(5, 3),
(6, 1),
(6, 3),
(7, 1),
(7, 2),
(8, 1),
(8, 2),
(9, 1),
(9, 2),
(10, 1),
(10, 2),
(11, 1),
(11, 2),
(12, 1),
(12, 3),
(13, 1),
(13, 2),
(14, 1),
(14, 2),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('in','out','adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `previous_stock` int(11) NOT NULL,
  `current_stock` int(11) NOT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `product_id`, `type`, `quantity`, `previous_stock`, `current_stock`, `reference`, `notes`, `metadata`, `created_at`, `updated_at`) VALUES
(1, 1, 'in', 45, 0, 45, 'initial_stock', 'Initial stock entry', NULL, '2026-05-15 07:00:10', '2026-05-15 07:00:10'),
(2, 3, 'in', 23, 0, 23, 'initial_stock', 'Initial stock entry', NULL, '2026-05-15 07:00:10', '2026-05-15 07:00:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_blocked` tinyint(1) NOT NULL DEFAULT 0,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `is_blocked`, `role_id`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Super Administrator', 'superadmin@example.com', NULL, '$2y$12$BFTL1kT0alEXRlvNrkv0duVssCjdQRseBbDP/CiMfakaYZrx9teme', 0, 1, NULL, '2026-05-15 07:00:11', '2026-05-15 07:00:11'),
(2, 'Administrator', 'admin@example.com', NULL, '$2y$12$bc7Q94U2XGb9Z7itS.QFeuPMl9u3WnffE16vS/.NvKDvWphlYPraC', 0, 2, NULL, '2026-05-15 07:00:11', '2026-05-15 07:00:11'),
(3, 'John Admin', 'johnadmin@example.com', NULL, '$2y$12$w7PibMqqEM7QTWjNVnrL.eKE1Dnx1qcyZe.Kva9pvqXm9CHV4VVyy', 0, 2, NULL, '2026-05-15 07:00:12', '2026-05-15 07:00:12'),
(4, 'Admin Photo', 'adminphoto@example.com', NULL, '$2y$12$Wc1dnOOw2jmCoZTKlTPzb.m5i4mqKU/QvTxmthHWQn.2AW1wj9z.m', 0, 2, NULL, '2026-05-15 07:00:13', '2026-05-15 07:00:13'),
(5, 'Test Admin', 'test@example.com', NULL, '$2y$12$hCStsdcK4gVK2WvfJzshl.4/GE/2/E3I/nli7M7GuCAbj1/ZSD6Im', 0, 2, NULL, '2026-05-15 07:00:13', '2026-05-15 07:00:13'),
(6, 'Blocked Admin', 'blocked@example.com', NULL, '$2y$12$1QwHpBgGyDk5Q4uiQAr51uIo1C8raqDPP98gyxE6Dj8ZLQMTNiDe2', 1, 2, NULL, '2026-05-15 07:00:14', '2026-05-15 07:00:14'),
(7, 'Jane Admin', 'janeadmin@example.com', NULL, '$2y$12$AECdj8H9E2Qd.gYgTVQnYOhsYE.4jCX3WtjmCadZfRRyGfKXOHukm', 0, 3, NULL, '2026-05-15 07:00:14', '2026-05-15 07:00:14'),
(8, 'Silverblade', 'silverbladeid@gmail.com', NULL, '$2y$12$GzL6MqoxeDSclIwNIkMR8.vAqIzs/RVle/xo88BMi2PxAo0rqjdjG', 0, 3, NULL, '2026-05-15 07:00:14', '2026-05-15 07:00:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_permission`
--

CREATE TABLE `user_permission` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indeks untuk tabel `email_settings`
--
ALTER TABLE `email_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `item_request`
--
ALTER TABLE `item_request`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_request_request_number_unique` (`request_number`),
  ADD KEY `item_request_approved_by_foreign` (`approved_by`),
  ADD KEY `item_request_user_id_status_index` (`user_id`,`status`),
  ADD KEY `item_request_request_number_index` (`request_number`),
  ADD KEY `item_request_status_index` (`status`),
  ADD KEY `item_request_created_at_index` (`created_at`);

--
-- Indeks untuk tabel `item_request_details`
--
ALTER TABLE `item_request_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_request_details_item_request_id_product_id_unique` (`item_request_id`,`product_id`),
  ADD KEY `item_request_details_item_request_id_product_id_index` (`item_request_id`,`product_id`),
  ADD KEY `item_request_details_product_id_index` (`product_id`),
  ADD KEY `item_request_details_status_index` (`status`);

--
-- Indeks untuk tabel `item_request_logs`
--
ALTER TABLE `item_request_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_request_logs_item_request_id_created_at_index` (`item_request_id`,`created_at`),
  ADD KEY `item_request_logs_user_id_index` (`user_id`),
  ADD KEY `item_request_logs_action_index` (`action`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indeks untuk tabel `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indeks untuk tabel `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indeks untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD UNIQUE KEY `products_sku_unique` (`sku`),
  ADD KEY `products_status_is_active_index` (`status`,`is_active`),
  ADD KEY `products_category_id_index` (`category_id`);

--
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indeks untuk tabel `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_movements_product_id_type_index` (`product_id`,`type`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_id_foreign` (`role_id`);

--
-- Indeks untuk tabel `user_permission`
--
ALTER TABLE `user_permission`
  ADD PRIMARY KEY (`user_id`,`permission_id`),
  ADD KEY `user_permission_permission_id_foreign` (`permission_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `email_settings`
--
ALTER TABLE `email_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `item_request`
--
ALTER TABLE `item_request`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `item_request_details`
--
ALTER TABLE `item_request_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `item_request_logs`
--
ALTER TABLE `item_request_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `item_request`
--
ALTER TABLE `item_request`
  ADD CONSTRAINT `item_request_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `item_request_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `item_request_details`
--
ALTER TABLE `item_request_details`
  ADD CONSTRAINT `item_request_details_item_request_id_foreign` FOREIGN KEY (`item_request_id`) REFERENCES `item_request` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `item_request_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `item_request_logs`
--
ALTER TABLE `item_request_logs`
  ADD CONSTRAINT `item_request_logs_item_request_id_foreign` FOREIGN KEY (`item_request_id`) REFERENCES `item_request` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `item_request_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `user_permission`
--
ALTER TABLE `user_permission`
  ADD CONSTRAINT `user_permission_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_permission_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
