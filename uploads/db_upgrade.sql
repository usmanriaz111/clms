-- ALTER TABLE `users` ADD `class_id` INT(11) NULL DEFAULT NULL AFTER `status`;
-- ALTER TABLE `live_sessions` ADD `course_id` INT(11) NOT NULL AFTER `class_id`;
ALTER TABLE `plans` ADD `type` TEXT NULL AFTER `private`;