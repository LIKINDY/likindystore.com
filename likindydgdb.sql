-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 18, 2025 at 12:16 AM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.3.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `likindydgdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `sender_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_inquiry` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `sender_name`, `sender_email`, `subject`, `message`, `service_inquiry`, `sent_at`, `is_read`) VALUES
(1, 'juma khamis', 'likindyismail@gmail.com', 'Inquiry about Battery System Repair', 'naihitaji hii bidha', '', '2025-06-17 19:04:01', 1),
(2, 'juma khamis', 'likindyismail@gmail.com', 'Inquiry about Battery System Repair', 'naihitaji hii bidha', '', '2025-06-17 21:10:38', 1),
(3, 'juma khamis', 'likindyismail@gmail.com', 'Inquiry about Battery System Repair', 'naihitaji hii bidha', '', '2025-06-17 22:07:37', 0);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_method` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_time_of_purchase` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_en` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_sw` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description_en`, `description_sw`, `price`, `image_url`, `category`, `stock`, `is_featured`, `created_at`, `updated_at`) VALUES
(1, 'sumsangPhone Clear Case', 'Durable clear case with reinforced corners for Samsung. Protects against drops and scratches while showing off your phone\'s original design.', 'Kinga imara ya uwazi yenye pembe zilizotiwa nguvu kwa samsung. Hulinda dhidi ya kudondoka na mikwaruzo huku ikionyesha muundo asili wa simu yako.', '6000.00', 'assets/images/phone.jpeg', 'Covers', 100, 1, '2025-06-17 16:50:49', '2025-06-17 19:07:45'),
(2, 'USB-C Fast Charger 20W', '20W USB-C Power Delivery charger for rapid charging of modern smartphones and tablets. Compact and efficient.', 'Chaja ya haraka ya 20W USB-C kwa kuchaji simu na tabu za kisasa haraka. Ndogo na yenye ufanisi.', '35000.00', 'assets/images/product_usbc_charger.jpg', 'Chargers', 150, 1, '2025-06-17 16:50:49', '2025-06-17 16:50:49'),
(3, 'Wireless Earbuds Pro', 'High-fidelity wireless earbuds with active noise cancellation and long battery life. Perfect for music and calls on the go.', 'Earbuds zisizo na waya za ubora wa juu zenye uwezo wa kuzuia kelele na betri inayodumu. Ni nzuri kwa muziki na simu popote ulipo.', '80000.00', 'assets/images/product_earbuds_pro.jpg', 'Earphones', 75, 0, '2025-06-17 16:50:49', '2025-06-17 16:50:49'),
(4, 'Universal Screen Protector (7-inch)', 'High-definition universal screen protector for 7-inch tablets or large phones. Anti-glare and scratch-resistant.', 'Kinga ya kioo ya jumla yenye ubora wa hali ya juu kwa tabu za inchi 7 au simu kubwa. Huzuia mng\'ao na mikwaruzo.', '15000.00', 'assets/images/product_screenprotector_7inch.jpg', 'Screen Protectors', 200, 0, '2025-06-17 16:50:49', '2025-06-17 16:50:49');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_sw` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_en` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_sw` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `base_price` decimal(10,2) DEFAULT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name_en`, `name_sw`, `description_en`, `description_sw`, `base_price`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 'iPhone Screen Replacement', 'Kubadili Kioo cha iPhone', 'Professional screen replacement service for various iPhone models. Original quality parts used.', 'Huduma ya kitaalamu ya kubadili vioo vya iPhone. Tunatumia vipuri vya ubora wa asili.', NULL, 'assets/images/service_screen_repair.jpg', '2025-06-17 16:50:49', '2025-06-17 16:50:49'),
(2, 'Battery System Repair', 'Ukarabati wa Mfumo wa Betri', 'Diagnosis and repair of smartphone battery charging issues, including port repair and internal battery system checks.', 'Uchunguzi na ukarabati wa matatizo ya chaji ya betri za simu, ikiwemo ukarabati wa tundu la chaji na ukaguzi wa mfumo wa ndani wa betri.', '5666.00', 'assets/images/bett.png', '2025-06-17 16:50:49', '2025-06-17 17:48:37'),
(3, 'Software Troubleshooting & Reinstallation', 'Kutatua Matatizo ya Software na Kuweka Upya', 'Resolve software glitches, app crashes, and system errors. Includes OS reinstallation and factory reset with data backup (optional).', 'Kutatua matatizo ya software, programu zinazokwama, na makosa ya mfumo. Inajumuisha kuweka upya mfumo wa uendeshaji na kurejesha mipangilio ya kiwandani (kwa hiari na hifadhi ya data).', NULL, 'assets/images/service_software_repair.jpg', '2025-06-17 16:50:49', '2025-06-17 16:50:49'),
(4, 'bettery', 'betri', 'we fix bettery problems', 'tunarekebisha matatizo ya betri za simu', '15000.00', 'assets/images/bettry.jpeg', '2025-06-17 17:47:36', '2025-06-17 17:47:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('customer','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `email`, `full_name`, `phone_number`, `address`, `role`, `registration_date`) VALUES
(1, NULL, '$2y$10$9A/fFp2Kx/lX.P1o.B0Vlu.1J.M6b.R7m.wz.k0J.X6h.V1z.2C.5C.', 'admin@likindydigitalsolution.com', 'Likindy Admin', '+255658415488', NULL, 'admin', '2025-06-17 16:50:49'),
(2, NULL, '$2y$10$hSrFe5g5wvrClnwuWBWl1u3ukOWZNK4sGbF414KkovyUMno0HtBaO', 'likindyismail@gmail.com', 'LIKINDY ISMAIL', NULL, NULL, 'customer', '2025-06-17 19:33:27'),
(6, NULL, '$2y$10$0zWapT9m/gGcqL3A3XKOwOJImZvPhx03IPiSP6Kfwr6IPW7CZSLZ6', 'likindy@gmail.com', 'LIKINDY ISMAIL', '0737473743', NULL, 'customer', '2025-06-17 20:29:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
