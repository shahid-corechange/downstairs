-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: mysql-production-downstairs.mysql.database.azure.com    Database: downstairs
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
-- Temporary view structure for view `monthly_work_hours`
--

DROP TABLE IF EXISTS `monthly_work_hours`;
/*!50001 DROP VIEW IF EXISTS `monthly_work_hours`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `monthly_work_hours` AS SELECT 
 1 AS `user_id`,
 1 AS `fortnox_id`,
 1 AS `employee_id`,
 1 AS `fullname`,
 1 AS `month`,
 1 AS `year`,
 1 AS `adjustment_hours`,
 1 AS `booking_hours`,
 1 AS `schedule_work_hours`,
 1 AS `store_work_hours`,
 1 AS `total_work_hours`,
 1 AS `schedule_deviation`,
 1 AS `schedule_employee_deviation`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `monthly_work_hours`
--

/*!50001 DROP VIEW IF EXISTS `monthly_work_hours`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`dsproductionroot`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `monthly_work_hours` AS with `adjustment_data` as (select `se`.`work_hour_id` AS `work_hour_id`,(sum(`ta`.`quarters`) / 4) AS `adjustment_hours` from (`schedule_employees` `se` left join `time_adjustments` `ta` on((`ta`.`schedule_employee_id` = `se`.`id`))) group by `se`.`work_hour_id`), `booking_hours_data` as (select `se`.`user_id` AS `user_id`,year(`se`.`start_at`) AS `year`,month(`se`.`start_at`) AS `month`,sum((ceiling((timestampdiff(SECOND,`se`.`start_at`,`se`.`end_at`) / 900)) / 4)) AS `booking_hours` from `schedule_employees` `se` where (`se`.`work_hour_id` is not null) group by `se`.`user_id`,year(`se`.`start_at`),month(`se`.`start_at`)), `filtered_schedule_employees` as (select `schedule_employees`.`schedule_id` AS `schedule_id`,`schedule_employees`.`user_id` AS `user_id`,`schedule_employees`.`work_hour_id` AS `work_hour_id` from `schedule_employees` where (`schedule_employees`.`deleted_at` is null)), `filtered_schedules` as (select `s`.`id` AS `schedule_id`,`fse`.`user_id` AS `user_id`,`fse`.`work_hour_id` AS `work_hour_id`,`s`.`start_at` AS `start_at`,`s`.`end_at` AS `end_at` from (`schedules` `s` join `filtered_schedule_employees` `fse` on((`s`.`id` = `fse`.`schedule_id`)))), `deviation_data` as (select `fs`.`user_id` AS `user_id`,year(`fs`.`start_at`) AS `year`,month(`fs`.`start_at`) AS `month`,count(0) AS `schedule_deviation` from (`schedule_deviations` `sd` join `filtered_schedules` `fs` on((`sd`.`schedule_id` = `fs`.`schedule_id`))) where ((`sd`.`is_handled` = 0) and (`sd`.`deleted_at` is null)) group by `fs`.`user_id`,`year`,`month`), `employee_deviation_data` as (select `fs`.`user_id` AS `user_id`,year(`fs`.`start_at`) AS `year`,month(`fs`.`start_at`) AS `month`,count(0) AS `schedule_employee_deviation` from (`deviations` `d` join `filtered_schedules` `fs` on(((`d`.`schedule_id` = `fs`.`schedule_id`) and (`d`.`user_id` = `fs`.`user_id`)))) where ((`d`.`is_handled` = 0) and (`d`.`deleted_at` is null)) group by `fs`.`user_id`,`year`,`month`), `store_work_hours_data` as (select `ca`.`user_id` AS `user_id`,year(`ca`.`check_in_at`) AS `year`,month(`ca`.`check_in_at`) AS `month`,sum((ceiling((timestampdiff(MINUTE,`ca`.`check_in_at`,`ca`.`check_out_at`) / 15)) / 4)) AS `store_work_hours` from `cashier_attendances` `ca` where ((`ca`.`deleted_at` is null) and (`ca`.`check_in_at` is not null) and (`ca`.`check_out_at` is not null)) group by `ca`.`user_id`,year(`ca`.`check_in_at`),month(`ca`.`check_in_at`)), `schedule_work_hours_data` as (select `wh`.`user_id` AS `user_id`,year(`wh`.`date`) AS `year`,month(`wh`.`date`) AS `month`,sum((ceiling((timestampdiff(MINUTE,concat(`wh`.`date`,' ',`wh`.`start_time`),concat(`wh`.`date`,' ',`wh`.`end_time`)) / 15)) / 4)) AS `schedule_work_hours` from `work_hours` `wh` where (`wh`.`type` = 'schedule') group by `wh`.`user_id`,year(`wh`.`date`),month(`wh`.`date`)) select `wh`.`user_id` AS `user_id`,`e`.`fortnox_id` AS `fortnox_id`,`e`.`id` AS `employee_id`,trim(concat_ws(' ',`u`.`first_name`,`u`.`last_name`)) AS `fullname`,month(`wh`.`date`) AS `month`,year(`wh`.`date`) AS `year`,coalesce(sum(`ad`.`adjustment_hours`),0) AS `adjustment_hours`,coalesce(max(`bh`.`booking_hours`),0) AS `booking_hours`,coalesce(max(`sh`.`schedule_work_hours`),0) AS `schedule_work_hours`,coalesce(max(`swh`.`store_work_hours`),0) AS `store_work_hours`,(coalesce(max(`sh`.`schedule_work_hours`),0) + coalesce(max(`swh`.`store_work_hours`),0)) AS `total_work_hours`,coalesce(max(`dd`.`schedule_deviation`),0) AS `schedule_deviation`,coalesce(max(`ed`.`schedule_employee_deviation`),0) AS `schedule_employee_deviation` from ((((((((`work_hours` `wh` left join `users` `u` on((`wh`.`user_id` = `u`.`id`))) left join `employees` `e` on((`e`.`user_id` = `u`.`id`))) left join `schedule_work_hours_data` `sh` on(((`sh`.`user_id` = `wh`.`user_id`) and (`sh`.`year` = year(`wh`.`date`)) and (`sh`.`month` = month(`wh`.`date`))))) left join `adjustment_data` `ad` on((`ad`.`work_hour_id` = `wh`.`id`))) left join `booking_hours_data` `bh` on(((`bh`.`user_id` = `wh`.`user_id`) and (`bh`.`year` = year(`wh`.`date`)) and (`bh`.`month` = month(`wh`.`date`))))) left join `store_work_hours_data` `swh` on(((`swh`.`user_id` = `wh`.`user_id`) and (`swh`.`year` = year(`wh`.`date`)) and (`swh`.`month` = month(`wh`.`date`))))) left join `deviation_data` `dd` on(((`dd`.`user_id` = `wh`.`user_id`) and (`dd`.`year` = year(`wh`.`date`)) and (`dd`.`month` = month(`wh`.`date`))))) left join `employee_deviation_data` `ed` on(((`ed`.`user_id` = `wh`.`user_id`) and (`ed`.`year` = year(`wh`.`date`)) and (`ed`.`month` = month(`wh`.`date`))))) group by `wh`.`user_id`,`e`.`fortnox_id`,`e`.`id`,`fullname`,year(`wh`.`date`),month(`wh`.`date`) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-27  8:08:08
