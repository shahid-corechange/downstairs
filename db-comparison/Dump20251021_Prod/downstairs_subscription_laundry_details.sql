--
-- ------------------------------------------------------

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
-- Table structure for table `subscription_laundry_details`
--

DROP TABLE IF EXISTS `subscription_laundry_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscription_laundry_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_id` bigint unsigned NOT NULL,
  `laundry_preference_id` bigint unsigned NOT NULL,
  `pickup_property_id` bigint unsigned DEFAULT NULL,
  `pickup_team_id` bigint unsigned DEFAULT NULL,
  `pickup_time` time DEFAULT NULL,
  `delivery_property_id` bigint unsigned DEFAULT NULL,
  `delivery_team_id` bigint unsigned DEFAULT NULL,
  `delivery_time` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `subscription_laundry_details_delivery_property_id_foreign` FOREIGN KEY (`delivery_property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscription_laundry_details_delivery_team_id_foreign` FOREIGN KEY (`delivery_team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscription_laundry_details_laundry_preference_id_foreign` FOREIGN KEY (`laundry_preference_id`) REFERENCES `laundry_preferences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscription_laundry_details_pickup_property_id_foreign` FOREIGN KEY (`pickup_property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscription_laundry_details_pickup_team_id_foreign` FOREIGN KEY (`pickup_team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscription_laundry_details_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

