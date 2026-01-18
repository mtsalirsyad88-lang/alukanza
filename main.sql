CREATE TABLE IF NOT EXISTS `data_akademik` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `class_name` varchar(10) NOT NULL,
  `subject` varchar(50) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `status` varchar(20) DEFAULT NULL,
  `topic` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `grade_value` varchar(10) DEFAULT NULL,
  `grade_type` varchar(20) DEFAULT NULL,
  `grade_title` varchar(100) DEFAULT NULL,
  `proof_image` longtext DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
