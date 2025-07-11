-- 注意：已移除创建数据库的语句，由安装程序动态创建

-- 创建品类表
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 创建工人表
CREATE TABLE IF NOT EXISTS `workers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 创建用户表
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 创建记录表
CREATE TABLE IF NOT EXISTS `records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `record_date` date NOT NULL,
  `worker_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `recorded_by` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL COMMENT '商品名称',
  PRIMARY KEY (`id`),
  KEY `worker_id` (`worker_id`),
  KEY `category_id` (`category_id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `records_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`),
  CONSTRAINT `records_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `records_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
