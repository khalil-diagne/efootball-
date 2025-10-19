CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `total_price` DECIMAL(10, 2) NOT NULL,
  `order_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `status` VARCHAR(50) DEFAULT 'pending',
  PRIMARY KEY (`id`),
  -- Si vous avez une table `visiteur` avec un `id`, vous pouvez ajouter une clé étrangère
  -- FOREIGN KEY (`user_id`) REFERENCES `visiteur`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;