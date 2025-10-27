-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: mysql-staging-downstairs.mysql.database.azure.com    Database: downstairs
-- ------------------------------------------------------
-- Server version	8.0.42-azure

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `laundry_orders`
--

DROP TABLE IF EXISTS `laundry_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `laundry_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `causer_id` bigint unsigned NOT NULL,
  `laundry_preference_id` bigint unsigned NOT NULL,
  `subscription_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `pickup_property_id` bigint unsigned DEFAULT NULL,
  `pickup_team_id` bigint unsigned DEFAULT NULL,
  `pickup_time` time DEFAULT NULL,
  `delivery_property_id` bigint unsigned DEFAULT NULL,
  `delivery_team_id` bigint unsigned DEFAULT NULL,
  `delivery_time` time DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordered_at` timestamp NOT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `laundry_orders_store_id_foreign` (`store_id`),
  KEY `laundry_orders_user_id_foreign` (`user_id`),
  KEY `laundry_orders_causer_id_foreign` (`causer_id`),
  KEY `laundry_orders_laundry_preference_id_foreign` (`laundry_preference_id`),
  KEY `laundry_orders_subscription_id_foreign` (`subscription_id`),
  KEY `laundry_orders_customer_id_foreign` (`customer_id`),
  KEY `laundry_orders_pickup_property_id_foreign` (`pickup_property_id`),
  KEY `laundry_orders_pickup_team_id_foreign` (`pickup_team_id`),
  KEY `laundry_orders_delivery_property_id_foreign` (`delivery_property_id`),
  KEY `laundry_orders_delivery_team_id_foreign` (`delivery_team_id`),
  CONSTRAINT `laundry_orders_causer_id_foreign` FOREIGN KEY (`causer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `laundry_orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `laundry_orders_delivery_property_id_foreign` FOREIGN KEY (`delivery_property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `laundry_orders_delivery_team_id_foreign` FOREIGN KEY (`delivery_team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `laundry_orders_laundry_preference_id_foreign` FOREIGN KEY (`laundry_preference_id`) REFERENCES `laundry_preferences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `laundry_orders_pickup_property_id_foreign` FOREIGN KEY (`pickup_property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `laundry_orders_pickup_team_id_foreign` FOREIGN KEY (`pickup_team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `laundry_orders_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  CONSTRAINT `laundry_orders_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `laundry_orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

