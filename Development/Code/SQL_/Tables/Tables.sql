drop table if exists clues_billing_fee_details_int;
CREATE TABLE `clues_billing_fee_details_int` (
  `company_id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `item_id` bigint(20) NOT NULL,
  `billing_category` int,
  `fee_code` varchar(64) NOT NULL,
  `fee_unit` char(1) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `tax_rate` decimal(5,2) DEFAULT NULL,
  `fee_before_tax` decimal(14,4) DEFAULT NULL,
  `tax_amount` decimal(14,4) DEFAULT NULL,
  `fee_after_tax` decimal(14,4) DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `billing` char(1) DEFAULT '0',
  `billing_cycle` varchar(255),
  PRIMARY KEY (`company_id`,`order_id`,`product_id`, `item_id`,`fee_code`)
) ENGINE=InnoDB;


drop table if exists clues_billing_fee_details;
CREATE TABLE `clues_billing_fee_details` (
  `company_id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `item_id` bigint(20) NOT NULL,
  `billing_category` int,
  `fee_code` varchar(64) NOT NULL,
  `fee_unit` char(1) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `tax_rate` decimal(5,2) DEFAULT NULL,
   `fee_before_tax` decimal(14,4) DEFAULT NULL,
  `tax_amount` decimal(14,4) DEFAULT NULL,
  `fee_after_tax` decimal(14,4) DEFAULT NULL,
 `order_date` date DEFAULT NULL,
  `billing` char(1) DEFAULT '0',
  `billing_cycle` varchar(255),
  PRIMARY KEY (`company_id`,`order_id`,`product_id`, `item_id`,`fee_code`)
) ENGINE=InnoDB;


drop table if exists clues_billing_total_fee;
CREATE TABLE `clues_billing_total_fee` (
  `company_id` bigint(20) DEFAULT NULL,
  `fee_type` varchar(64) DEFAULT NULL,
  `fee_before_tax` decimal(14,4) DEFAULT NULL,
  `tax_amount` decimal(14,4) DEFAULT NULL,
  `fee_after_tax` decimal(14,4) DEFAULT NULL,
  `billing` char(1) DEFAULT '0',
  `run_date` date default null ,	
  `billing_cycle` varchar(255) DEFAULT '0'
) ENGINE=InnoDB;


drop table if exists `clues_billing_fee_config`;
CREATE TABLE IF NOT EXISTS `clues_billing_fee_config` (
   `fee_config_id` bigint(20) NOT NULL AUTO_INCREMENT,
   `name` varchar(64) DEFAULT NULL,
   `code` varchar(64) DEFAULT NULL,
   `type` varchar(64) DEFAULT NULL,
   `amount` decimal(14,4) DEFAULT NULL,
   `status_applicable` varchar(256) DEFAULT NULL,
   `tax_rate` decimal(5,2) DEFAULT NULL,
   `unit` char(1) DEFAULT NULL,
   `from_date` date DEFAULT NULL,
   `to_date` date DEFAULT NULL,
   `created_by` varchar(64) DEFAULT NULL,
   `created_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
   `updated_by` varchar(64) DEFAULT NULL,
   `updated_date` timestamp NULL DEFAULT NULL,
   `fee_status` varchar(10) DEFAULT NULL,
   `fulfillment_ids` varchar(256) DEFAULT NULL,
   PRIMARY KEY (`fee_config_id`),
   UNIQUE KEY `code` (`code`),
   UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `clues_order_exclude`;
CREATE TABLE `clues_order_exclude` (
   `id` bigint(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,
   `order_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `clues_order_exclude_int`;
CREATE TABLE `clues_order_exclude_int` (
   `id` bigint(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,
   `order_id` bigint(20) DEFAULT NULL,
   `exclude_type` varchar(1) DEFAULT 0
) ENGINE=InnoDB;

drop table if exists clues_logs;
CREATE TABLE `clues_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(64) DEFAULT NULL,
  `message` text,
  `created_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;


INSERT INTO `shopclue_cart_30122012`.`clues_billing_fee_config` (`name`,`code`,`type`,`amount`,`status_applicable`,`tax_rate`,`unit`,`from_date`,`to_date`,`created_by`,`fee_status`,`fulfillment_ids`)
VALUES('Handling Fee ','FEE1','F',9,'G',12.36,'I','2012-01-16','2020-12-31','SYSTEM','A','1');

alter table clues_billing_payout_summary add column Packing_Fee decimal(14,4) default 0;

delete  from clues_billing_payout_summary where summary_type='PackingFee';
