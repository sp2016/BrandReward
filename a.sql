/*
SQLyog Ultimate v11.11 (64 bit)
MySQL - 5.7.18-log : Database - bdg_go_base
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`bdg_go_base` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `bdg_go_base`;

/*Table structure for table `advertiser_network_keywords` */

DROP TABLE IF EXISTS `advertiser_network_keywords`;

CREATE TABLE `advertiser_network_keywords` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Keywords` varchar(255) NOT NULL,
  `AddTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Origin` varchar(255) NOT NULL DEFAULT 'System',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `keywords` (`Keywords`)
) ENGINE=MyISAM AUTO_INCREMENT=222 DEFAULT CHARSET=utf8;

/*Table structure for table `aff_account_change_log` */

DROP TABLE IF EXISTS `aff_account_change_log`;

CREATE TABLE `aff_account_change_log` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `AffId` int(11) NOT NULL,
  `Name` varchar(255) DEFAULT NULL,
  `Operator` varchar(255) NOT NULL,
  `OldAccount` varchar(255) NOT NULL,
  `OldPassword` varchar(255) NOT NULL,
  `NewAccount` varchar(255) NOT NULL,
  `NewPassword` varchar(255) NOT NULL,
  `Time` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `aff_crawl_config` */

DROP TABLE IF EXISTS `aff_crawl_config`;

CREATE TABLE `aff_crawl_config` (
  `AffId` int(11) NOT NULL AUTO_INCREMENT,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `ProgramCrawlStatus` enum('Yes','No') NOT NULL DEFAULT 'No',
  `LinkCrawlStatus` enum('Yes','No') NOT NULL DEFAULT 'No',
  `MessageCrawlStatus` enum('Yes','No') NOT NULL DEFAULT 'No',
  `StatsCrawlStatus` enum('Yes','No') NOT NULL DEFAULT 'No',
  `FeedCrawlStatus` enum('Yes','No') NOT NULL DEFAULT 'No',
  `InvaildLinkCrawlStatus` enum('Yes','No') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`AffId`)
) ENGINE=MyISAM AUTO_INCREMENT=471 DEFAULT CHARSET=latin1;

/*Table structure for table `aff_info_select_show` */

DROP TABLE IF EXISTS `aff_info_select_show`;

CREATE TABLE `aff_info_select_show` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `AffId` int(11) NOT NULL,
  `Type` enum('Program','Link','Couponfeed','Product','Transaction') NOT NULL,
  `AffField` varchar(255) NOT NULL,
  `BrField` varchar(255) NOT NULL DEFAULT '',
  `DataSourceType` enum('API','Page') NOT NULL DEFAULT 'Page',
  `AddTime` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `aff_field` (`AffId`,`Type`,`AffField`)
) ENGINE=MyISAM AUTO_INCREMENT=1412 DEFAULT CHARSET=utf8;

/*Table structure for table `aff_siteid` */

DROP TABLE IF EXISTS `aff_siteid`;

CREATE TABLE `aff_siteid` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `AffId` int(11) NOT NULL,
  `AccountId` int(11) NOT NULL,
  `SiteIdInAff` varchar(255) NOT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  `LastUpdateTime` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=latin1;

/*Table structure for table `aff_url_pattern` */

DROP TABLE IF EXISTS `aff_url_pattern`;

CREATE TABLE `aff_url_pattern` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `AffId` int(11) DEFAULT NULL,
  `TplDeepUrlTpl` varchar(255) DEFAULT NULL,
  `TplAffDefaultUrl` varchar(255) DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO') NOT NULL DEFAULT 'YES',
  `NeedAffDefaultUrl` enum('YES','NO') NOT NULL DEFAULT 'YES',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `affid` (`AffId`)
) ENGINE=MyISAM AUTO_INCREMENT=114 DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate` */

DROP TABLE IF EXISTS `affiliate`;

CREATE TABLE `affiliate` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL DEFAULT '',
  `ShortName` varchar(50) NOT NULL,
  `Domain` varchar(50) NOT NULL,
  `BlogUrl` text,
  `FacebookUrl` text,
  `TwitterUrl` text,
  `GetProgramIDInNetworkUrl` text,
  `AffiliateUrlKeywords` text,
  `AffiliateUrlKeywords2` text,
  `SubTracking` varchar(255) DEFAULT NULL,
  `SubTracking2` varchar(255) DEFAULT NULL,
  `IsInHouse` enum('YES','NO') DEFAULT NULL,
  `IsActive` enum('YES','NO') DEFAULT NULL,
  `DeepUrlParaName` varchar(255) DEFAULT NULL,
  `RevenueAccount` int(11) DEFAULT '0',
  `RevenueCycle` text,
  `RevenueRemark` text,
  `ProgramCrawled` enum('YES','NO','No Need to Crawl','Request to Crawl','Can Not Crawl') DEFAULT 'NO',
  `ProgramCrawlRemark` text,
  `StatsReportCrawled` enum('YES','NO','No Need to Crawl','Request to Crawl','Can Not Crawl') DEFAULT 'NO',
  `StatsReportCrawlRemark` text,
  `StatsAffiliateName` varchar(32) DEFAULT NULL,
  `ImportanceRank` int(11) DEFAULT '99999999',
  `ProgramUrlTemplate` varchar(1024) DEFAULT NULL,
  `Country` varchar(255) DEFAULT NULL,
  `LoginUrl` varchar(500) DEFAULT NULL,
  `SupportDeepUrl` enum('YES','NO') DEFAULT 'NO',
  `SupportSubTracking` enum('YES','NO') DEFAULT 'NO',
  `JoinDate` datetime DEFAULT NULL,
  `Comment` text,
  `MarketingCountry` varchar(10) DEFAULT NULL,
  `MarketingContinent` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `Name` (`Name`),
  KEY `IsInHouse` (`IsInHouse`),
  KEY `IsActive` (`IsActive`)
) ENGINE=MyISAM AUTO_INCREMENT=558 DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links` */

DROP TABLE IF EXISTS `affiliate_links`;

CREATE TABLE `affiliate_links` (
  `AffId` int(11) NOT NULL,
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `HttpCode` int(3) NOT NULL DEFAULT '0',
  UNIQUE KEY `aff_id` (`AffId`,`AffMerchantId`,`AffLinkId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_all` */

DROP TABLE IF EXISTS `affiliate_links_all`;

CREATE TABLE `affiliate_links_all` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `affid` int(11) unsigned NOT NULL,
  `ProgramId` int(11) unsigned NOT NULL,
  `PidInaff` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL,
  `CouponCode` varchar(255) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `LinkHtmlCode` text,
  `Desc` text,
  `AffUrl` varchar(255) NOT NULL,
  `StartDate` datetime DEFAULT NULL,
  `EndDate` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `LastChangeTime` datetime DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `Status` enum('Active','InActive') DEFAULT 'Active',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'N/A',
  `IsPromotion` enum('YES','NO','unknown') NOT NULL DEFAULT 'NO',
  `CountryCode` varchar(200) DEFAULT '',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `affid` (`affid`,`PidInaff`,`AffLinkId`),
  KEY `ProgramId` (`ProgramId`)
) ENGINE=MyISAM AUTO_INCREMENT=243935 DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_all_simple` */

DROP TABLE IF EXISTS `affiliate_links_all_simple`;

CREATE TABLE `affiliate_links_all_simple` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `affid` int(11) unsigned NOT NULL,
  `ProgramId` int(11) unsigned NOT NULL,
  `PidInaff` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL,
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `LastUpdateTime` datetime DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime DEFAULT '0000-00-00 00:00:00',
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  `IsPromotion` enum('YES','NO','unknown') NOT NULL DEFAULT 'NO',
  `ScriptTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `language` varchar(200) DEFAULT 'en',
  `KeyWords` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `affid` (`affid`,`PidInaff`,`AffLinkId`),
  KEY `ProgramId` (`ProgramId`),
  KEY `ScriptTime` (`ScriptTime`)
) ENGINE=MyISAM AUTO_INCREMENT=1209187 DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_url_keywords` */

DROP TABLE IF EXISTS `affiliate_url_keywords`;

CREATE TABLE `affiliate_url_keywords` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Affid` int(11) NOT NULL,
  `Keyword` varchar(255) NOT NULL,
  `AddTime` datetime NOT NULL,
  `Origin` enum('System','Manual') NOT NULL DEFAULT 'System',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Unique` (`Keyword`,`Affid`)
) ENGINE=MyISAM AUTO_INCREMENT=95 DEFAULT CHARSET=utf8;

/*Table structure for table `analyze_status_change_log` */

DROP TABLE IF EXISTS `analyze_status_change_log`;

CREATE TABLE `analyze_status_change_log` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `LogId` int(11) NOT NULL DEFAULT '0',
  `ProgramId` int(11) unsigned DEFAULT NULL,
  `AffId` int(11) NOT NULL DEFAULT '0',
  `StatusInAff` varchar(100) DEFAULT NULL,
  `Partnership` varchar(100) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL,
  `Time` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `LogId` (`LogId`,`ProgramId`),
  KEY `ProgramId` (`ProgramId`)
) ENGINE=MyISAM AUTO_INCREMENT=167636 DEFAULT CHARSET=latin1;

/*Table structure for table `base_m_domain` */

DROP TABLE IF EXISTS `base_m_domain`;

CREATE TABLE `base_m_domain` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `MerchantId` int(11) DEFAULT NULL,
  `Site` varchar(10) DEFAULT NULL,
  `MerchantDomain` varchar(255) DEFAULT NULL,
  `CouponDomain` varchar(255) DEFAULT NULL,
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx` (`CouponDomain`,`Site`)
) ENGINE=MyISAM AUTO_INCREMENT=6383 DEFAULT CHARSET=utf8;

/*Table structure for table `base_program_store_relationship` */

DROP TABLE IF EXISTS `base_program_store_relationship`;

CREATE TABLE `base_program_store_relationship` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ProgramId` int(11) NOT NULL,
  `StoreId` int(11) NOT NULL,
  `AffiliateDefaultUrl` varchar(255) NOT NULL,
  `DeepUrlTemplate` varchar(255) NOT NULL,
  `Order` int(11) NOT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `IsFake` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `DomainName` varchar(255) DEFAULT NULL,
  `StoreUrl` varchar(255) DEFAULT NULL,
  `AffId` int(11) DEFAULT NULL,
  `ProgramDomains` varchar(255) DEFAULT NULL,
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Site` varchar(255) DEFAULT NULL,
  `MerchantDomain` varchar(255) DEFAULT NULL,
  `ShippingCountry` varchar(255) DEFAULT NULL,
  `DomainSpecial` varchar(255) DEFAULT NULL,
  `ps_edit_time` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `dd` (`DomainName`,`Order`,`Status`,`ProgramId`),
  KEY `cc` (`ps_edit_time`),
  KEY `un` (`ProgramId`),
  KEY `idx_status` (`Status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `base_program_store_relationship_au` */

DROP TABLE IF EXISTS `base_program_store_relationship_au`;

CREATE TABLE `base_program_store_relationship_au` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ProgramId` int(11) NOT NULL,
  `StoreId` int(11) NOT NULL,
  `AffiliateDefaultUrl` varchar(255) NOT NULL,
  `DeepUrlTemplate` varchar(255) NOT NULL,
  `Order` int(11) NOT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `IsFake` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `DomainName` varchar(255) DEFAULT NULL,
  `StoreUrl` varchar(255) DEFAULT NULL,
  `AffId` int(11) DEFAULT NULL,
  `ProgramDomains` varchar(255) DEFAULT NULL,
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Site` varchar(255) DEFAULT NULL,
  `MerchantDomain` varchar(255) DEFAULT NULL,
  `ShippingCountry` varchar(255) DEFAULT NULL,
  `DomainSpecial` varchar(255) DEFAULT NULL,
  `ps_edit_time` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `dd` (`DomainName`,`Order`,`Status`,`ProgramId`),
  KEY `un` (`ProgramId`),
  KEY `idx_status` (`Status`)
) ENGINE=MyISAM AUTO_INCREMENT=90954 DEFAULT CHARSET=latin1;

/*Table structure for table `base_program_store_relationship_ca` */

DROP TABLE IF EXISTS `base_program_store_relationship_ca`;

CREATE TABLE `base_program_store_relationship_ca` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ProgramId` int(11) NOT NULL,
  `StoreId` int(11) NOT NULL,
  `AffiliateDefaultUrl` varchar(255) NOT NULL,
  `DeepUrlTemplate` varchar(255) NOT NULL,
  `Order` int(11) NOT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `IsFake` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `DomainName` varchar(255) DEFAULT NULL,
  `StoreUrl` varchar(255) DEFAULT NULL,
  `AffId` int(11) DEFAULT NULL,
  `ProgramDomains` varchar(255) DEFAULT NULL,
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Site` varchar(255) DEFAULT NULL,
  `MerchantDomain` varchar(255) DEFAULT NULL,
  `ShippingCountry` varchar(255) DEFAULT NULL,
  `DomainSpecial` varchar(255) DEFAULT NULL,
  `ps_edit_time` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `dd` (`DomainName`,`Order`,`Status`,`ProgramId`),
  KEY `un` (`ProgramId`),
  KEY `idx_status` (`Status`)
) ENGINE=MyISAM AUTO_INCREMENT=91076 DEFAULT CHARSET=latin1;

/*Table structure for table `base_program_store_relationship_de` */

DROP TABLE IF EXISTS `base_program_store_relationship_de`;

CREATE TABLE `base_program_store_relationship_de` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ProgramId` int(11) NOT NULL,
  `StoreId` int(11) NOT NULL,
  `AffiliateDefaultUrl` varchar(255) NOT NULL,
  `DeepUrlTemplate` varchar(255) NOT NULL,
  `Order` int(11) NOT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `IsFake` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `DomainName` varchar(255) DEFAULT NULL,
  `StoreUrl` varchar(255) DEFAULT NULL,
  `AffId` int(11) DEFAULT NULL,
  `ProgramDomains` varchar(255) DEFAULT NULL,
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Site` varchar(255) DEFAULT NULL,
  `MerchantDomain` varchar(255) DEFAULT NULL,
  `ShippingCountry` varchar(255) DEFAULT NULL,
  `DomainSpecial` varchar(255) DEFAULT NULL,
  `ps_edit_time` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `dd` (`DomainName`,`Order`,`Status`,`ProgramId`),
  KEY `un` (`ProgramId`),
  KEY `idx_status` (`Status`)
) ENGINE=MyISAM AUTO_INCREMENT=91104 DEFAULT CHARSET=latin1;

/*Table structure for table `base_program_store_relationship_fr` */

DROP TABLE IF EXISTS `base_program_store_relationship_fr`;

CREATE TABLE `base_program_store_relationship_fr` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ProgramId` int(11) NOT NULL,
  `StoreId` int(11) NOT NULL,
  `AffiliateDefaultUrl` varchar(255) NOT NULL,
  `DeepUrlTemplate` varchar(255) NOT NULL,
  `Order` int(11) NOT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `IsFake` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `DomainName` varchar(255) DEFAULT NULL,
  `StoreUrl` varchar(255) DEFAULT NULL,
  `AffId` int(11) DEFAULT NULL,
  `ProgramDomains` varchar(255) DEFAULT NULL,
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Site` varchar(255) DEFAULT NULL,
  `MerchantDomain` varchar(255) DEFAULT NULL,
  `ShippingCountry` varchar(255) DEFAULT NULL,
  `DomainSpecial` varchar(255) DEFAULT NULL,
  `ps_edit_time` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `dd` (`DomainName`,`Order`,`Status`,`ProgramId`),
  KEY `un` (`ProgramId`),
  KEY `idx_status` (`Status`)
) ENGINE=MyISAM AUTO_INCREMENT=91221 DEFAULT CHARSET=latin1;

/*Table structure for table `base_program_store_relationship_uk` */

DROP TABLE IF EXISTS `base_program_store_relationship_uk`;

CREATE TABLE `base_program_store_relationship_uk` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ProgramId` int(11) NOT NULL,
  `StoreId` int(11) NOT NULL,
  `AffiliateDefaultUrl` varchar(255) NOT NULL,
  `DeepUrlTemplate` varchar(255) NOT NULL,
  `Order` int(11) NOT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `IsFake` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `DomainName` varchar(255) DEFAULT NULL,
  `StoreUrl` varchar(255) DEFAULT NULL,
  `AffId` int(11) DEFAULT NULL,
  `ProgramDomains` varchar(255) DEFAULT NULL,
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Site` varchar(255) DEFAULT NULL,
  `MerchantDomain` varchar(255) DEFAULT NULL,
  `ShippingCountry` varchar(255) DEFAULT NULL,
  `DomainSpecial` varchar(255) DEFAULT NULL,
  `ps_edit_time` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `dd` (`DomainName`,`Order`,`Status`,`ProgramId`),
  KEY `un` (`ProgramId`),
  KEY `idx_status` (`Status`)
) ENGINE=MyISAM AUTO_INCREMENT=91172 DEFAULT CHARSET=latin1;

/*Table structure for table `base_program_store_relationship_us` */

DROP TABLE IF EXISTS `base_program_store_relationship_us`;

CREATE TABLE `base_program_store_relationship_us` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ProgramId` int(11) NOT NULL,
  `StoreId` int(11) NOT NULL,
  `AffiliateDefaultUrl` varchar(255) NOT NULL,
  `DeepUrlTemplate` varchar(255) NOT NULL,
  `Order` int(11) NOT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `IsFake` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `DomainName` varchar(255) DEFAULT NULL,
  `StoreUrl` varchar(255) DEFAULT NULL,
  `AffId` int(11) DEFAULT NULL,
  `ProgramDomains` varchar(255) DEFAULT NULL,
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Site` varchar(255) DEFAULT NULL,
  `MerchantDomain` varchar(255) DEFAULT NULL,
  `ShippingCountry` varchar(255) DEFAULT NULL,
  `DomainSpecial` varchar(255) DEFAULT NULL,
  `ps_edit_time` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `dd` (`DomainName`,`Order`,`Status`,`ProgramId`),
  KEY `un` (`ProgramId`),
  KEY `idx_status` (`Status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `base_ps_domain` */

DROP TABLE IF EXISTS `base_ps_domain`;

CREATE TABLE `base_ps_domain` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `StoreDomain` varchar(255) DEFAULT NULL,
  `Domain` varchar(255) DEFAULT NULL,
  `Site` varchar(255) DEFAULT NULL,
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_u` (`StoreDomain`,`Domain`,`Site`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `base_store` */

DROP TABLE IF EXISTS `base_store`;

CREATE TABLE `base_store` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) DEFAULT NULL,
  `Domain` varchar(100) DEFAULT NULL,
  `Url` varchar(200) DEFAULT NULL,
  `CountryCode` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `idx_domain` (`Domain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `bd_out_tracking` */

DROP TABLE IF EXISTS `bd_out_tracking`;

CREATE TABLE `bd_out_tracking` (
  `id` bigint(60) unsigned NOT NULL AUTO_INCREMENT,
  `alias` varchar(20) NOT NULL DEFAULT '',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `domainId` int(9) unsigned NOT NULL DEFAULT '0',
  `domainUsed` varchar(100) NOT NULL DEFAULT '',
  `programId` int(9) unsigned NOT NULL DEFAULT '0',
  `pageUrl` text NOT NULL COMMENT '目标页面的原始url',
  `outUrl` text NOT NULL COMMENT '通过联盟加工过最终出站的url',
  `sessionId` char(32) NOT NULL COMMENT 'md5([id]_[created])作为出站的唯一标识可在联盟中的transaction中匹配',
  `publishTracking` char(64) NOT NULL DEFAULT '',
  `affId` int(11) NOT NULL DEFAULT '0',
  `createddate` date NOT NULL DEFAULT '0000-00-00',
  `site` char(32) NOT NULL DEFAULT '',
  `cookie` char(32) NOT NULL DEFAULT '',
  `ip` char(64) NOT NULL DEFAULT '',
  `country` char(2) NOT NULL DEFAULT '',
  `referer` varchar(255) NOT NULL DEFAULT '',
  `linkid` int(9) NOT NULL DEFAULT '0' COMMENT 'content_feed_encodeid',
  PRIMARY KEY (`id`,`createddate`),
  KEY `idx_sessionId` (`sessionId`),
  KEY `idx_created_site` (`createddate`,`site`)
) ENGINE=MyISAM AUTO_INCREMENT=103394355 DEFAULT CHARSET=latin1
/*!50100 PARTITION BY RANGE (to_days(createddate))
(PARTITION p1401 VALUES LESS THAN (735630) ENGINE = MyISAM,
 PARTITION p1402 VALUES LESS THAN (735658) ENGINE = MyISAM,
 PARTITION p1403 VALUES LESS THAN (735689) ENGINE = MyISAM,
 PARTITION p1404 VALUES LESS THAN (735719) ENGINE = MyISAM,
 PARTITION p1405 VALUES LESS THAN (735750) ENGINE = MyISAM,
 PARTITION p1406 VALUES LESS THAN (735780) ENGINE = MyISAM,
 PARTITION p1407 VALUES LESS THAN (735811) ENGINE = MyISAM,
 PARTITION p1408 VALUES LESS THAN (735842) ENGINE = MyISAM,
 PARTITION p1409 VALUES LESS THAN (735872) ENGINE = MyISAM,
 PARTITION p1410 VALUES LESS THAN (735903) ENGINE = MyISAM,
 PARTITION p1411 VALUES LESS THAN (735933) ENGINE = MyISAM,
 PARTITION p1412 VALUES LESS THAN (735964) ENGINE = MyISAM,
 PARTITION p1501 VALUES LESS THAN (735995) ENGINE = MyISAM,
 PARTITION p1502 VALUES LESS THAN (736023) ENGINE = MyISAM,
 PARTITION p1503 VALUES LESS THAN (736054) ENGINE = MyISAM,
 PARTITION p1504 VALUES LESS THAN (736084) ENGINE = MyISAM,
 PARTITION p1505 VALUES LESS THAN (736115) ENGINE = MyISAM,
 PARTITION p1506 VALUES LESS THAN (736145) ENGINE = MyISAM,
 PARTITION p1507 VALUES LESS THAN (736176) ENGINE = MyISAM,
 PARTITION p1508 VALUES LESS THAN (736207) ENGINE = MyISAM,
 PARTITION p1509 VALUES LESS THAN (736237) ENGINE = MyISAM,
 PARTITION p1510 VALUES LESS THAN (736268) ENGINE = MyISAM,
 PARTITION p1511 VALUES LESS THAN (736298) ENGINE = MyISAM,
 PARTITION p1512 VALUES LESS THAN (736329) ENGINE = MyISAM,
 PARTITION p1601 VALUES LESS THAN (736360) ENGINE = MyISAM,
 PARTITION p1602 VALUES LESS THAN (736389) ENGINE = MyISAM,
 PARTITION p1603 VALUES LESS THAN (736420) ENGINE = MyISAM,
 PARTITION p1604 VALUES LESS THAN (736450) ENGINE = MyISAM,
 PARTITION p1605 VALUES LESS THAN (736481) ENGINE = MyISAM,
 PARTITION p1606 VALUES LESS THAN (736511) ENGINE = MyISAM,
 PARTITION p1607 VALUES LESS THAN (736542) ENGINE = MyISAM,
 PARTITION p1608 VALUES LESS THAN (736573) ENGINE = MyISAM,
 PARTITION p1609 VALUES LESS THAN (736603) ENGINE = MyISAM,
 PARTITION p1610 VALUES LESS THAN (736634) ENGINE = MyISAM,
 PARTITION p1611 VALUES LESS THAN (736664) ENGINE = MyISAM,
 PARTITION p1612 VALUES LESS THAN (736695) ENGINE = MyISAM,
 PARTITION p1701 VALUES LESS THAN (736726) ENGINE = MyISAM,
 PARTITION p1702 VALUES LESS THAN (736754) ENGINE = MyISAM,
 PARTITION p1703 VALUES LESS THAN (736785) ENGINE = MyISAM,
 PARTITION p1704 VALUES LESS THAN (736815) ENGINE = MyISAM,
 PARTITION p1705 VALUES LESS THAN (736846) ENGINE = MyISAM,
 PARTITION p1706 VALUES LESS THAN (736876) ENGINE = MyISAM,
 PARTITION p1707 VALUES LESS THAN (736907) ENGINE = MyISAM,
 PARTITION p1708 VALUES LESS THAN (736938) ENGINE = MyISAM,
 PARTITION p1709 VALUES LESS THAN (736968) ENGINE = MyISAM,
 PARTITION p1710 VALUES LESS THAN (736999) ENGINE = MyISAM,
 PARTITION p1711 VALUES LESS THAN (737029) ENGINE = MyISAM,
 PARTITION p1712 VALUES LESS THAN (737060) ENGINE = MyISAM,
 PARTITION p1801 VALUES LESS THAN (737091) ENGINE = MyISAM,
 PARTITION p1802 VALUES LESS THAN (737119) ENGINE = MyISAM,
 PARTITION p1803 VALUES LESS THAN (737150) ENGINE = MyISAM,
 PARTITION p1804 VALUES LESS THAN (737180) ENGINE = MyISAM,
 PARTITION p1805 VALUES LESS THAN (737211) ENGINE = MyISAM,
 PARTITION p1806 VALUES LESS THAN (737241) ENGINE = MyISAM,
 PARTITION p1807 VALUES LESS THAN (737272) ENGINE = MyISAM,
 PARTITION p1808 VALUES LESS THAN (737303) ENGINE = MyISAM,
 PARTITION p1809 VALUES LESS THAN (737333) ENGINE = MyISAM,
 PARTITION p1810 VALUES LESS THAN (737364) ENGINE = MyISAM,
 PARTITION p1811 VALUES LESS THAN (737394) ENGINE = MyISAM,
 PARTITION p1812 VALUES LESS THAN (737425) ENGINE = MyISAM,
 PARTITION p1901 VALUES LESS THAN (737456) ENGINE = MyISAM,
 PARTITION p1902 VALUES LESS THAN (737484) ENGINE = MyISAM,
 PARTITION p1903 VALUES LESS THAN (737515) ENGINE = MyISAM,
 PARTITION p1904 VALUES LESS THAN (737545) ENGINE = MyISAM,
 PARTITION p1905 VALUES LESS THAN (737576) ENGINE = MyISAM,
 PARTITION p1906 VALUES LESS THAN (737606) ENGINE = MyISAM,
 PARTITION p1907 VALUES LESS THAN (737637) ENGINE = MyISAM,
 PARTITION p1908 VALUES LESS THAN (737668) ENGINE = MyISAM,
 PARTITION p1909 VALUES LESS THAN (737698) ENGINE = MyISAM,
 PARTITION p1910 VALUES LESS THAN (737729) ENGINE = MyISAM,
 PARTITION p1911 VALUES LESS THAN (737759) ENGINE = MyISAM,
 PARTITION p1912 VALUES LESS THAN (737790) ENGINE = MyISAM,
 PARTITION p2000 VALUES LESS THAN MAXVALUE ENGINE = MyISAM) */;

/*Table structure for table `bd_out_tracking_for_spider` */

DROP TABLE IF EXISTS `bd_out_tracking_for_spider`;

CREATE TABLE `bd_out_tracking_for_spider` (
  `SessionId` char(32) NOT NULL,
  `CreatedDate` date NOT NULL,
  `Created` datetime DEFAULT NULL,
  `Referer` varchar(255) DEFAULT NULL,
  `UserAgent` varchar(255) DEFAULT NULL,
  `IP` varchar(50) DEFAULT NULL,
  `IsRobet` enum('YES','NO','UNKNOWN','POTENTIAL') NOT NULL DEFAULT 'UNKNOWN',
  `Mark` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`CreatedDate`,`SessionId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
/*!50100 PARTITION BY RANGE (to_days(createddate))
(PARTITION p1701 VALUES LESS THAN (736695) ENGINE = MyISAM,
 PARTITION p1702 VALUES LESS THAN (736726) ENGINE = MyISAM,
 PARTITION p1703 VALUES LESS THAN (736754) ENGINE = MyISAM,
 PARTITION p1704 VALUES LESS THAN (736785) ENGINE = MyISAM,
 PARTITION p1705 VALUES LESS THAN (736815) ENGINE = MyISAM,
 PARTITION p1706 VALUES LESS THAN (736846) ENGINE = MyISAM,
 PARTITION p1707 VALUES LESS THAN (736876) ENGINE = MyISAM,
 PARTITION p1708 VALUES LESS THAN (736907) ENGINE = MyISAM,
 PARTITION p1709 VALUES LESS THAN (736938) ENGINE = MyISAM,
 PARTITION p1710 VALUES LESS THAN (736968) ENGINE = MyISAM,
 PARTITION p1711 VALUES LESS THAN (736999) ENGINE = MyISAM,
 PARTITION p1712 VALUES LESS THAN (737029) ENGINE = MyISAM,
 PARTITION p1801 VALUES LESS THAN (737060) ENGINE = MyISAM,
 PARTITION p1802 VALUES LESS THAN (737091) ENGINE = MyISAM,
 PARTITION p1803 VALUES LESS THAN (737119) ENGINE = MyISAM,
 PARTITION p1804 VALUES LESS THAN (737150) ENGINE = MyISAM,
 PARTITION p2000 VALUES LESS THAN MAXVALUE ENGINE = MyISAM) */;

/*Table structure for table `bd_out_tracking_inner` */

DROP TABLE IF EXISTS `bd_out_tracking_inner`;

CREATE TABLE `bd_out_tracking_inner` (
  `alias` varchar(20) NOT NULL DEFAULT '',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `domainId` int(9) unsigned NOT NULL DEFAULT '0',
  `programId` int(9) unsigned NOT NULL DEFAULT '0',
  `programIdInAff` varchar(255) NOT NULL DEFAULT '',
  `programName` varchar(255) NOT NULL DEFAULT '',
  `sessionId` char(32) NOT NULL COMMENT 'md5([id]_[created])作为出站的唯一标识可在联盟中的transaction中匹配',
  `publishTracking` char(64) NOT NULL DEFAULT '',
  `affId` int(11) NOT NULL DEFAULT '0',
  `createddate` date NOT NULL DEFAULT '0000-00-00',
  `site` char(32) NOT NULL DEFAULT '',
  `source` enum('bdg','mk') NOT NULL DEFAULT 'bdg',
  `sourceid` int(9) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`source`,`sourceid`),
  KEY `idx_created_site` (`createddate`,`site`),
  KEY `idx_publishtracking` (`publishTracking`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `bd_out_tracking_min` */

DROP TABLE IF EXISTS `bd_out_tracking_min`;

CREATE TABLE `bd_out_tracking_min` (
  `id` bigint(60) unsigned NOT NULL AUTO_INCREMENT,
  `createddate` date NOT NULL DEFAULT '0000-00-00',
  `sessionId` char(32) NOT NULL COMMENT 'md5([id]_[created])作为出站的唯一标识可在联盟中的transaction中匹配',
  `publishTracking` char(64) NOT NULL DEFAULT '',
  `domainId` int(9) unsigned NOT NULL DEFAULT '0',
  `programId` int(9) unsigned NOT NULL DEFAULT '0',
  `affId` int(11) NOT NULL DEFAULT '0',
  `site` char(32) NOT NULL DEFAULT '',
  `hour` tinyint(2) unsigned zerofill DEFAULT '00',
  `country` char(2) NOT NULL DEFAULT '',
  `IsRobet` enum('YES','NO','UNKNOWN','POTENTIAL') NOT NULL DEFAULT 'UNKNOWN',
  `linkId` int(9) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`createddate`),
  KEY `idx_publishTracking` (`publishTracking`),
  KEY `idx_sessionId` (`sessionId`),
  KEY `idx_domainId` (`domainId`),
  KEY `idx_programId` (`programId`),
  KEY `idx_affId` (`affId`),
  KEY `idx_created_site` (`createddate`,`site`),
  KEY `idx_country` (`country`),
  KEY `idx_linkId` (`linkId`)
) ENGINE=MyISAM AUTO_INCREMENT=103393358 DEFAULT CHARSET=latin1
/*!50100 PARTITION BY RANGE (to_days(createddate))
(PARTITION p1401 VALUES LESS THAN (735630) ENGINE = MyISAM,
 PARTITION p1402 VALUES LESS THAN (735658) ENGINE = MyISAM,
 PARTITION p1403 VALUES LESS THAN (735689) ENGINE = MyISAM,
 PARTITION p1404 VALUES LESS THAN (735719) ENGINE = MyISAM,
 PARTITION p1405 VALUES LESS THAN (735750) ENGINE = MyISAM,
 PARTITION p1406 VALUES LESS THAN (735780) ENGINE = MyISAM,
 PARTITION p1407 VALUES LESS THAN (735811) ENGINE = MyISAM,
 PARTITION p1408 VALUES LESS THAN (735842) ENGINE = MyISAM,
 PARTITION p1409 VALUES LESS THAN (735872) ENGINE = MyISAM,
 PARTITION p1410 VALUES LESS THAN (735903) ENGINE = MyISAM,
 PARTITION p1411 VALUES LESS THAN (735933) ENGINE = MyISAM,
 PARTITION p1412 VALUES LESS THAN (735964) ENGINE = MyISAM,
 PARTITION p1501 VALUES LESS THAN (735995) ENGINE = MyISAM,
 PARTITION p1502 VALUES LESS THAN (736023) ENGINE = MyISAM,
 PARTITION p1503 VALUES LESS THAN (736054) ENGINE = MyISAM,
 PARTITION p1504 VALUES LESS THAN (736084) ENGINE = MyISAM,
 PARTITION p1505 VALUES LESS THAN (736115) ENGINE = MyISAM,
 PARTITION p1506 VALUES LESS THAN (736145) ENGINE = MyISAM,
 PARTITION p1507 VALUES LESS THAN (736176) ENGINE = MyISAM,
 PARTITION p1508 VALUES LESS THAN (736207) ENGINE = MyISAM,
 PARTITION p1509 VALUES LESS THAN (736237) ENGINE = MyISAM,
 PARTITION p1510 VALUES LESS THAN (736268) ENGINE = MyISAM,
 PARTITION p1511 VALUES LESS THAN (736298) ENGINE = MyISAM,
 PARTITION p1512 VALUES LESS THAN (736329) ENGINE = MyISAM,
 PARTITION p1601 VALUES LESS THAN (736360) ENGINE = MyISAM,
 PARTITION p1602 VALUES LESS THAN (736389) ENGINE = MyISAM,
 PARTITION p1603 VALUES LESS THAN (736420) ENGINE = MyISAM,
 PARTITION p1604 VALUES LESS THAN (736450) ENGINE = MyISAM,
 PARTITION p1605 VALUES LESS THAN (736481) ENGINE = MyISAM,
 PARTITION p1606 VALUES LESS THAN (736511) ENGINE = MyISAM,
 PARTITION p1607 VALUES LESS THAN (736542) ENGINE = MyISAM,
 PARTITION p1608 VALUES LESS THAN (736573) ENGINE = MyISAM,
 PARTITION p1609 VALUES LESS THAN (736603) ENGINE = MyISAM,
 PARTITION p1610 VALUES LESS THAN (736634) ENGINE = MyISAM,
 PARTITION p1611 VALUES LESS THAN (736664) ENGINE = MyISAM,
 PARTITION p1612 VALUES LESS THAN (736695) ENGINE = MyISAM,
 PARTITION p1701 VALUES LESS THAN (736726) ENGINE = MyISAM,
 PARTITION p1702 VALUES LESS THAN (736754) ENGINE = MyISAM,
 PARTITION p1703 VALUES LESS THAN (736785) ENGINE = MyISAM,
 PARTITION p1704 VALUES LESS THAN (736815) ENGINE = MyISAM,
 PARTITION p1705 VALUES LESS THAN (736846) ENGINE = MyISAM,
 PARTITION p1706 VALUES LESS THAN (736876) ENGINE = MyISAM,
 PARTITION p1707 VALUES LESS THAN (736907) ENGINE = MyISAM,
 PARTITION p1708 VALUES LESS THAN (736938) ENGINE = MyISAM,
 PARTITION p1709 VALUES LESS THAN (736968) ENGINE = MyISAM,
 PARTITION p1710 VALUES LESS THAN (736999) ENGINE = MyISAM,
 PARTITION p1711 VALUES LESS THAN (737029) ENGINE = MyISAM,
 PARTITION p1712 VALUES LESS THAN (737060) ENGINE = MyISAM,
 PARTITION p1801 VALUES LESS THAN (737091) ENGINE = MyISAM,
 PARTITION p1802 VALUES LESS THAN (737119) ENGINE = MyISAM,
 PARTITION p1803 VALUES LESS THAN (737150) ENGINE = MyISAM,
 PARTITION p1804 VALUES LESS THAN (737180) ENGINE = MyISAM,
 PARTITION p1805 VALUES LESS THAN (737211) ENGINE = MyISAM,
 PARTITION p1806 VALUES LESS THAN (737241) ENGINE = MyISAM,
 PARTITION p1807 VALUES LESS THAN (737272) ENGINE = MyISAM,
 PARTITION p1808 VALUES LESS THAN (737303) ENGINE = MyISAM,
 PARTITION p1809 VALUES LESS THAN (737333) ENGINE = MyISAM,
 PARTITION p1810 VALUES LESS THAN (737364) ENGINE = MyISAM,
 PARTITION p1811 VALUES LESS THAN (737394) ENGINE = MyISAM,
 PARTITION p1812 VALUES LESS THAN (737425) ENGINE = MyISAM,
 PARTITION p1901 VALUES LESS THAN (737456) ENGINE = MyISAM,
 PARTITION p1902 VALUES LESS THAN (737484) ENGINE = MyISAM,
 PARTITION p1903 VALUES LESS THAN (737515) ENGINE = MyISAM,
 PARTITION p1904 VALUES LESS THAN (737545) ENGINE = MyISAM,
 PARTITION p1905 VALUES LESS THAN (737576) ENGINE = MyISAM,
 PARTITION p1906 VALUES LESS THAN (737606) ENGINE = MyISAM,
 PARTITION p1907 VALUES LESS THAN (737637) ENGINE = MyISAM,
 PARTITION p1908 VALUES LESS THAN (737668) ENGINE = MyISAM,
 PARTITION p1909 VALUES LESS THAN (737698) ENGINE = MyISAM,
 PARTITION p1910 VALUES LESS THAN (737729) ENGINE = MyISAM,
 PARTITION p1911 VALUES LESS THAN (737759) ENGINE = MyISAM,
 PARTITION p1912 VALUES LESS THAN (737790) ENGINE = MyISAM,
 PARTITION p2000 VALUES LESS THAN MAXVALUE ENGINE = MyISAM) */;

/*Table structure for table `bd_out_tracking_publisher` */

DROP TABLE IF EXISTS `bd_out_tracking_publisher`;

CREATE TABLE `bd_out_tracking_publisher` (
  `id` bigint(60) unsigned NOT NULL,
  `alias` varchar(20) NOT NULL DEFAULT '',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `domainId` int(9) unsigned NOT NULL DEFAULT '0',
  `domainUsed` varchar(100) NOT NULL DEFAULT '',
  `programId` int(9) unsigned NOT NULL DEFAULT '0',
  `pageUrl` text NOT NULL COMMENT '目标页面的原始url',
  `outUrl` text NOT NULL COMMENT '通过联盟加工过最终出站的url',
  `sessionId` char(32) NOT NULL COMMENT 'md5([id]_[created])作为出站的唯一标识可在联盟中的transaction中匹配',
  `publishTracking` char(64) NOT NULL DEFAULT '',
  `affId` int(11) NOT NULL DEFAULT '0',
  `createddate` date NOT NULL DEFAULT '0000-00-00',
  `site` char(32) NOT NULL DEFAULT '',
  `cookie` char(32) NOT NULL DEFAULT '',
  `ip` char(64) NOT NULL DEFAULT '',
  `country` char(2) NOT NULL DEFAULT '',
  `referer` varchar(255) NOT NULL DEFAULT '',
  `pageUrlMD5` char(32) NOT NULL DEFAULT '',
  `linkid` int(9) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sessionId` (`sessionId`),
  KEY `idx_createddate` (`createddate`),
  KEY `idx_created_site` (`site`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `bd_out_tracking_publisher_statistics` */

DROP TABLE IF EXISTS `bd_out_tracking_publisher_statistics`;

CREATE TABLE `bd_out_tracking_publisher_statistics` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `hour` tinyint(3) NOT NULL,
  `clicks` int(11) NOT NULL,
  `clicks_Aff` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `block_relationship` */

DROP TABLE IF EXISTS `block_relationship`;

CREATE TABLE `block_relationship` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `BlockBy` enum('Affiliate','Merchant','Internal','Store') NOT NULL DEFAULT 'Internal',
  `AccountId` int(11) DEFAULT NULL,
  `AccountType` enum('AccountId','PublisherId') NOT NULL DEFAULT 'AccountId',
  `PublisherId` int(11) NOT NULL DEFAULT '0',
  `ObjId` int(11) DEFAULT NULL,
  `ObjType` enum('Affiliate','Program','Store') DEFAULT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  `AddTime` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `AddUser` varchar(50) DEFAULT NULL,
  `Remark` text,
  `Add_Violation_Warning` int(11) NOT NULL DEFAULT '0' COMMENT '是否添加违规警告（1是 0否）',
  `Is_Email` int(11) NOT NULL DEFAULT '0' COMMENT '是否已发送邮件（1是 0否）',
  `Source` enum('NORMAL','SYSTEM') NOT NULL DEFAULT 'NORMAL' COMMENT '标识添加来源(程序or手动)',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=9480 DEFAULT CHARSET=latin1;

/*Table structure for table `c_content_feed` */

DROP TABLE IF EXISTS `c_content_feed`;

CREATE TABLE `c_content_feed` (
  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) NOT NULL DEFAULT '',
  `Code` varchar(255) NOT NULL DEFAULT '',
  `Type` enum('coupon','promotion','product') NOT NULL,
  `Desc` text NOT NULL,
  `Url` text NOT NULL,
  `Advertiser_Name` varchar(128) NOT NULL DEFAULT '',
  `Source` varchar(128) NOT NULL DEFAULT '',
  `SourceKey` varchar(128) NOT NULL DEFAULT '',
  `StartTime` datetime NOT NULL,
  `ExpireTime` datetime NOT NULL,
  `TimeZone` varchar(64) NOT NULL DEFAULT 'America/Los_Angeles',
  `CreateTime` datetime NOT NULL,
  `Created` date NOT NULL,
  `UpdateTime` datetime NOT NULL,
  `Updated` date NOT NULL,
  `ImgUrl` text NOT NULL,
  `ImgFile` text NOT NULL,
  `ImgIsDownload` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `IsActive` enum('NO','YES') NOT NULL DEFAULT 'NO',
  `Country` char(2) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unq_source_sourcekey` (`Source`,`SourceKey`),
  KEY `idx_created` (`Created`),
  KEY `idx_updated` (`Updated`),
  KEY `IsActive` (`IsActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `category` */

DROP TABLE IF EXISTS `category`;

CREATE TABLE `category` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `AffId` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `PID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `only category` (`AffId`,`Name`,`PID`),
  FULLTEXT KEY `Name` (`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `category_ext` */

DROP TABLE IF EXISTS `category_ext`;

CREATE TABLE `category_ext` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `IdRelated` varchar(255) DEFAULT NULL,
  `AffId` varchar(255) DEFAULT NULL,
  `UpdateTime` datetime NOT NULL,
  `ManualCtrl` enum('NO','YES') NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique name` (`Name`)
) ENGINE=InnoDB AUTO_INCREMENT=184 DEFAULT CHARSET=utf8;

/*Table structure for table `category_relation` */

DROP TABLE IF EXISTS `category_relation`;

CREATE TABLE `category_relation` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Standard` int(11) DEFAULT NULL,
  `Self` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `A pair` (`Self`,`Standard`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `category_std` */

DROP TABLE IF EXISTS `category_std`;

CREATE TABLE `category_std` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique name` (`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `cf_affurl_contrast` */

DROP TABLE IF EXISTS `cf_affurl_contrast`;

CREATE TABLE `cf_affurl_contrast` (
  `Affurl` varchar(255) NOT NULL,
  `Programid` int(11) NOT NULL,
  `Domain` varchar(200) DEFAULT NULL,
  `Domainid` int(11) DEFAULT NULL,
  `Storeid` int(11) DEFAULT NULL,
  `Affid` int(11) DEFAULT NULL,
  `EncodeId` int(11) DEFAULT NULL,
  UNIQUE KEY `affurl` (`Affurl`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `check_aff_url` */

DROP TABLE IF EXISTS `check_aff_url`;

CREATE TABLE `check_aff_url` (
  `ContentFeedId` int(11) unsigned NOT NULL,
  `AffId` int(11) unsigned NOT NULL,
  `AddTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `AffUrl` varchar(255) NOT NULL DEFAULT '',
  `Status` enum('Active','InActive') NOT NULL DEFAULT 'InActive',
  `StatusDesc` varchar(255) DEFAULT NULL,
  `Correct` enum('Unknown','YES','NO') NOT NULL DEFAULT 'Unknown' COMMENT 'Unknown为未处理状态，YES为能访问，NO为不能访问',
  PRIMARY KEY (`ContentFeedId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `check_homepage_log` */

DROP TABLE IF EXISTS `check_homepage_log`;

CREATE TABLE `check_homepage_log` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PID` int(11) NOT NULL,
  `Old` varchar(255) NOT NULL,
  `New` varchar(255) NOT NULL,
  `Checked` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `UpdateTime` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1168 DEFAULT CHARSET=utf8;

/*Table structure for table `check_outbound_log` */

DROP TABLE IF EXISTS `check_outbound_log`;

CREATE TABLE `check_outbound_log` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PID` int(11) NOT NULL,
  `DID` int(11) NOT NULL,
  `Affid` int(11) NOT NULL,
  `ErrorType` enum('DomainNull','DefaultAffUrlTplNoUrl','DefaultAffUrlTplError','DefaultAffUrlTplOK','DefaultAffUrlError','DefaultAffUrlWarning','DefaultAffUrlOK','TplNoUrl','TplNoTpl','TplError','TplOK') NOT NULL,
  `HttpCode` int(11) DEFAULT NULL,
  `UrlOrTpl` varchar(255) DEFAULT NULL,
  `Origin` varchar(255) DEFAULT NULL,
  `Dealt` varchar(255) DEFAULT NULL,
  `UpdateTime` datetime NOT NULL,
  `OverDate` enum('YES','NO') NOT NULL DEFAULT 'NO' COMMENT '超过一个月前的数据作为过期数据，不在处理以及显示',
  `Correct` enum('Unknown','YES','NO','Auto') NOT NULL DEFAULT 'Unknown',
  `Alternative` varchar(255) DEFAULT NULL,
  `Confirmed` enum('YES','NO') NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`ID`),
  KEY `Key` (`DID`,`PID`)
) ENGINE=MyISAM AUTO_INCREMENT=58283 DEFAULT CHARSET=utf8;

/*Table structure for table `chk_homepage_jump_change` */

DROP TABLE IF EXISTS `chk_homepage_jump_change`;

CREATE TABLE `chk_homepage_jump_change` (
  `PID` int(11) NOT NULL,
  `CheckTime` datetime DEFAULT NULL,
  `HttpNormal` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `content_feed` */

DROP TABLE IF EXISTS `content_feed`;

CREATE TABLE `content_feed` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ProgramId` int(11) unsigned NOT NULL,
  `AffLinkId` varchar(255) NOT NULL,
  `EncodeId` int(8) NOT NULL DEFAULT '0',
  `language` varchar(200) NOT NULL DEFAULT 'en',
  `CouponCode` varchar(255) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Desc` text,
  `AffUrl` varchar(255) NOT NULL,
  `StartDate` datetime DEFAULT NULL,
  `EndDate` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `LastChangeTime` datetime DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `Status` enum('Active','InActive') DEFAULT 'Active',
  `Type` enum('Promotion','Coupon','Product') DEFAULT 'Promotion',
  `AddUser` varchar(255) DEFAULT NULL,
  `ImgAdr` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ProgramId` (`ProgramId`,`AffLinkId`),
  KEY `idx_encodeid` (`EncodeId`),
  KEY `idx_status` (`Status`)
) ENGINE=MyISAM AUTO_INCREMENT=186116 DEFAULT CHARSET=latin1;

/*Table structure for table `content_feed_delete_report` */

DROP TABLE IF EXISTS `content_feed_delete_report`;

CREATE TABLE `content_feed_delete_report` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content_feed_id` int(11) unsigned NOT NULL,
  `delete_time` datetime NOT NULL,
  `delete_user` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `content_feed_new` */

DROP TABLE IF EXISTS `content_feed_new`;

CREATE TABLE `content_feed_new` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `SimpleId` int(11) unsigned NOT NULL,
  `ProgramId` int(11) unsigned NOT NULL,
  `StoreId` int(11) unsigned NOT NULL,
  `CouponCode` varchar(255) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Desc` text,
  `AffUrl` text NOT NULL,
  `OriginalUrl` text NOT NULL,
  `StartDate` datetime DEFAULT NULL,
  `EndDate` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `LastChangeTime` datetime DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `Status` enum('Active','InActive') DEFAULT 'Active',
  `Type` enum('Coupon','Promotion','FreeShipping') DEFAULT 'Promotion',
  `EncodeId` int(8) NOT NULL DEFAULT '0',
  `language` varchar(200) NOT NULL DEFAULT 'en',
  `AddUser` varchar(255) DEFAULT NULL,
  `source` varchar(100) NOT NULL DEFAULT 'site',
  `country` varchar(1000) NOT NULL,
  `key_money` decimal(10,2) NOT NULL,
  `key_from` decimal(10,2) NOT NULL,
  `key_percent` decimal(10,2) NOT NULL,
  `key_free_trial` tinyint(1) NOT NULL,
  `key_free_download` tinyint(1) NOT NULL,
  `key_free_gift` tinyint(1) NOT NULL,
  `key_free_sample` tinyint(1) NOT NULL,
  `key_free_shipping` tinyint(1) NOT NULL,
  `key_bngn` tinyint(1) NOT NULL,
  `key_sale_clearance` tinyint(1) NOT NULL,
  `key_reward` tinyint(1) NOT NULL,
  `key_rebate` tinyint(1) NOT NULL,
  `key_other` tinyint(1) NOT NULL,
  `key_from_currency` varchar(200) NOT NULL,
  `key_money_currency` varchar(255) NOT NULL,
  `ImgAdr` varchar(255) DEFAULT NULL,
  `IsParaOptimized` enum('ORIGIN','HANDLE','NO') NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `SimpleId` (`SimpleId`,`source`),
  KEY `Status` (`Status`),
  KEY `EncodeId` (`EncodeId`),
  KEY `ProgramId` (`ProgramId`),
  KEY `CouponCode` (`CouponCode`)
) ENGINE=MyISAM AUTO_INCREMENT=879274 DEFAULT CHARSET=latin1;

/*Table structure for table `content_feed_new2` */

DROP TABLE IF EXISTS `content_feed_new2`;

CREATE TABLE `content_feed_new2` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `SimpleId` int(11) unsigned NOT NULL,
  `ProgramId` int(11) unsigned NOT NULL,
  `StoreId` int(11) unsigned NOT NULL,
  `CouponCode` varchar(255) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Desc` text,
  `AffUrl` varchar(255) NOT NULL,
  `StartDate` datetime DEFAULT NULL,
  `EndDate` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `LastChangeTime` datetime DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `Status` enum('Active','InActive') DEFAULT 'Active',
  `Type` enum('Coupon','Promotion') DEFAULT 'Promotion',
  `EncodeId` int(8) NOT NULL DEFAULT '0',
  `language` varchar(200) NOT NULL DEFAULT 'en',
  `AddUser` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `SimpleId` (`SimpleId`),
  KEY `Status` (`Status`)
) ENGINE=MyISAM AUTO_INCREMENT=223050 DEFAULT CHARSET=latin1;

/*Table structure for table `content_feed_new_delete_report` */

DROP TABLE IF EXISTS `content_feed_new_delete_report`;

CREATE TABLE `content_feed_new_delete_report` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content_feed_id` int(11) unsigned NOT NULL,
  `delete_time` datetime NOT NULL,
  `delete_user` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `country_codes` */

DROP TABLE IF EXISTS `country_codes`;

CREATE TABLE `country_codes` (
  `CountryCode` varchar(10) NOT NULL,
  `id` int(9) unsigned DEFAULT NULL,
  `CountryName` varchar(100) CHARACTER SET latin1 NOT NULL,
  `CountryKeywords` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `language` varchar(100) NOT NULL DEFAULT '' COMMENT '官方语言',
  `CountryStatus` enum('On','Off') CHARACTER SET latin1 DEFAULT NULL,
  `CountryDomain` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`CountryCode`),
  UNIQUE KEY `NewIndex1` (`CountryName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `country_codes_old` */

DROP TABLE IF EXISTS `country_codes_old`;

CREATE TABLE `country_codes_old` (
  `CountryCode` varchar(10) NOT NULL,
  `CountryName` varchar(100) NOT NULL,
  `CountryKeywords` varchar(255) DEFAULT NULL,
  `CountryStatus` enum('On','Off') DEFAULT NULL,
  `CountryDomain` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`CountryCode`),
  UNIQUE KEY `NewIndex1` (`CountryName`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `crawl_links_logs` */

DROP TABLE IF EXISTS `crawl_links_logs`;

CREATE TABLE `crawl_links_logs` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Affid` int(10) unsigned NOT NULL,
  `Type` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `CheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Amount` int(10) NOT NULL DEFAULT '0',
  `ToInactive` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=4261 DEFAULT CHARSET=latin1;

/*Table structure for table `crawl_publish_domain_follow_new` */

DROP TABLE IF EXISTS `crawl_publish_domain_follow_new`;

CREATE TABLE `crawl_publish_domain_follow_new` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `publisherId` int(10) NOT NULL,
  `publisherAccountId` int(10) unsigned DEFAULT '0',
  `domainName` varchar(255) NOT NULL,
  `semKeywords` text,
  `semRTextAds` text,
  `seoKeywords` text NOT NULL,
  `whois` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=256 DEFAULT CHARSET=latin1;

/*Table structure for table `crawl_script_run_log` */

DROP TABLE IF EXISTS `crawl_script_run_log`;

CREATE TABLE `crawl_script_run_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sessionId` varchar(255) CHARACTER SET latin1 NOT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `startTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `endTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `platform` varchar(200) CHARACTER SET latin1 NOT NULL COMMENT '1:MK，2:BR',
  `affid` int(11) unsigned NOT NULL,
  `method` varchar(255) CHARACTER SET latin1 NOT NULL,
  `status` varchar(200) CHARACTER SET latin1 NOT NULL DEFAULT 'doing' COMMENT 'doing:进行中，finish ：已结束, error:错误',
  `logfile` varchar(255) CHARACTER SET latin1 NOT NULL,
  `error_descp` text CHARACTER SET latin1 NOT NULL,
  `analyze_flag` tinyint(1) NOT NULL DEFAULT '0',
  `total` int(10) NOT NULL DEFAULT '0',
  `new` int(10) NOT NULL DEFAULT '0',
  `update` int(10) unsigned NOT NULL,
  `notfound` int(10) unsigned NOT NULL,
  `toInactive` int(10) NOT NULL DEFAULT '0',
  `storeOffcount` int(10) NOT NULL,
  `ext1` text CHARACTER SET latin1 NOT NULL,
  `ext2` text CHARACTER SET latin1 NOT NULL,
  `ext3` text CHARACTER SET latin1 NOT NULL,
  `ext4` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `crawl_transaction_lost_file_logs` */

DROP TABLE IF EXISTS `crawl_transaction_lost_file_logs`;

CREATE TABLE `crawl_transaction_lost_file_logs` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `AffId` int(11) unsigned NOT NULL,
  `AffName` varchar(200) NOT NULL,
  `LostFile` text NOT NULL,
  `AddTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=548 DEFAULT CHARSET=latin1;

/*Table structure for table `currency_contrast` */

DROP TABLE IF EXISTS `currency_contrast`;

CREATE TABLE `currency_contrast` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) NOT NULL,
  `Code` varchar(255) NOT NULL,
  `Symbol` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Code` (`Code`,`Symbol`)
) ENGINE=MyISAM AUTO_INCREMENT=136 DEFAULT CHARSET=latin1;

/*Table structure for table `dictionary` */

DROP TABLE IF EXISTS `dictionary`;

CREATE TABLE `dictionary` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('country','sitetype') NOT NULL,
  `name` varchar(128) NOT NULL DEFAULT '',
  `language` varchar(100) NOT NULL DEFAULT '' COMMENT '官方语言',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=268 DEFAULT CHARSET=latin1;

/*Table structure for table `domain` */

DROP TABLE IF EXISTS `domain`;

CREATE TABLE `domain` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Domain` varchar(255) NOT NULL,
  `Existed` enum('YES','NO') NOT NULL DEFAULT 'YES',
  `CountryCode` varchar(10) DEFAULT NULL,
  `SubDomain` varchar(255) DEFAULT NULL,
  `DomainName` varchar(255) DEFAULT NULL,
  `AddDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastCheckTime` datetime DEFAULT NULL,
  `SupportOutP` enum('YES','NO') NOT NULL DEFAULT 'YES',
  `SupportAff` enum('YES','NO') NOT NULL DEFAULT 'YES',
  `Rank` int(11) NOT NULL DEFAULT '0',
  `SupportFake` enum('YES','NO') NOT NULL DEFAULT 'YES',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_domain` (`Domain`) USING BTREE,
  KEY `idx_dname` (`DomainName`) USING BTREE,
  KEY `idx_rank` (`Rank`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=337880 DEFAULT CHARSET=latin1;

/*Table structure for table `domain_change_log` */

DROP TABLE IF EXISTS `domain_change_log`;

CREATE TABLE `domain_change_log` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DomainId` int(11) NOT NULL,
  `DomainFrom` varchar(50) DEFAULT NULL,
  `DomainTo` varchar(50) DEFAULT NULL,
  `ToId` int(11) DEFAULT NULL,
  `ChangeDate` datetime DEFAULT NULL,
  `Status` enum('New','Active','Inactive') NOT NULL DEFAULT 'New',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_domain_u` (`DomainId`),
  KEY `idx_status` (`Status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `domain_country_stats` */

DROP TABLE IF EXISTS `domain_country_stats`;

CREATE TABLE `domain_country_stats` (
  `DomainId` int(11) NOT NULL,
  `Country` char(2) NOT NULL,
  `Date` date NOT NULL,
  `Traffic` int(11) DEFAULT NULL,
  `Commission` decimal(10,4) DEFAULT NULL,
  `Sales` decimal(10,4) DEFAULT NULL,
  PRIMARY KEY (`Date`,`DomainId`,`Country`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `domain_noaff_redirect_config` */

DROP TABLE IF EXISTS `domain_noaff_redirect_config`;

CREATE TABLE `domain_noaff_redirect_config` (
  `DomainId` int(11) NOT NULL,
  `RedirectType` enum('Viglink','Skimlink','NoAff') NOT NULL DEFAULT 'Viglink',
  `AddUser` varchar(50) DEFAULT NULL,
  `AddTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`DomainId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `domain_outgoing_all` */

DROP TABLE IF EXISTS `domain_outgoing_all`;

CREATE TABLE `domain_outgoing_all` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DID` int(11) DEFAULT NULL,
  `Site` varchar(255) DEFAULT NULL,
  `LimitAccount` varchar(255) DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `IsFake` enum('NO','YES') NOT NULL DEFAULT 'NO',
  `AffiliateDefaultUrl` text,
  `DeepUrlTemplate` text,
  `SupportType` enum('Content','All','Promotion') NOT NULL DEFAULT 'All',
  `DefaultOrder` tinyint(3) NOT NULL DEFAULT '99',
  `PID` int(11) DEFAULT NULL,
  `Key` varchar(255) DEFAULT NULL,
  `Domain` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_u` (`DID`,`Site`,`SupportType`,`DefaultOrder`),
  KEY `idx_did` (`DID`,`PID`),
  KEY `idx_site` (`Site`),
  KEY `idx_pid` (`PID`)
) ENGINE=MyISAM AUTO_INCREMENT=412399 DEFAULT CHARSET=latin1;

/*Table structure for table `domain_outgoing_all_20171117` */

DROP TABLE IF EXISTS `domain_outgoing_all_20171117`;

CREATE TABLE `domain_outgoing_all_20171117` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DID` int(11) DEFAULT NULL,
  `Site` varchar(255) DEFAULT NULL,
  `LimitAccount` varchar(255) DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `IsFake` enum('NO','YES') NOT NULL DEFAULT 'NO',
  `AffiliateDefaultUrl` text,
  `DeepUrlTemplate` text,
  `SupportType` enum('Content','All','Promotion') NOT NULL DEFAULT 'All',
  `DefaultOrder` tinyint(3) NOT NULL DEFAULT '99',
  `PID` int(11) DEFAULT NULL,
  `Key` varchar(255) DEFAULT NULL,
  `Domain` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_u` (`DID`,`Site`,`SupportType`,`DefaultOrder`),
  KEY `idx_did` (`DID`,`PID`),
  KEY `idx_site` (`Site`),
  KEY `idx_pid` (`PID`)
) ENGINE=MyISAM AUTO_INCREMENT=1619829 DEFAULT CHARSET=latin1;

/*Table structure for table `domain_outgoing_all_copy_tmpfortest` */

DROP TABLE IF EXISTS `domain_outgoing_all_copy_tmpfortest`;

CREATE TABLE `domain_outgoing_all_copy_tmpfortest` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DID` int(11) DEFAULT NULL,
  `Site` varchar(255) DEFAULT NULL,
  `LimitAccount` varchar(255) DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `IsFake` enum('NO','YES') NOT NULL DEFAULT 'NO',
  `AffiliateDefaultUrl` text,
  `DeepUrlTemplate` text,
  `SupportType` enum('Content','All','Promotion') NOT NULL DEFAULT 'All',
  `DefaultOrder` tinyint(3) NOT NULL DEFAULT '99',
  `PID` int(11) DEFAULT NULL,
  `Key` varchar(255) DEFAULT NULL,
  `Domain` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_u` (`DID`,`Site`,`SupportType`,`DefaultOrder`),
  KEY `idx_did` (`DID`,`PID`),
  KEY `idx_site` (`Site`),
  KEY `idx_pid` (`PID`)
) ENGINE=MyISAM AUTO_INCREMENT=385834 DEFAULT CHARSET=latin1;

/*Table structure for table `domain_outgoing_default` */

DROP TABLE IF EXISTS `domain_outgoing_default`;

CREATE TABLE `domain_outgoing_default` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DID` int(11) DEFAULT NULL,
  `PID` int(11) DEFAULT NULL,
  `Key` varchar(255) DEFAULT NULL,
  `LimitAccount` varchar(255) DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `IsFake` enum('NO','YES') NOT NULL DEFAULT 'NO',
  `AffiliateDefaultUrl` varchar(255) DEFAULT NULL,
  `DeepUrlTemplate` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_key` (`Key`),
  KEY `idx_did` (`DID`,`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `domain_outgoing_default_changelog` */

DROP TABLE IF EXISTS `domain_outgoing_default_changelog`;

CREATE TABLE `domain_outgoing_default_changelog` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DID` int(11) NOT NULL DEFAULT '0',
  `Key` varchar(255) DEFAULT NULL,
  `ProgramFrom` int(11) NOT NULL DEFAULT '0',
  `ProgramTo` int(11) NOT NULL DEFAULT '0',
  `ChangeTime` datetime DEFAULT NULL,
  `Status` enum('New','Positive','Negative') NOT NULL DEFAULT 'New',
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  KEY `idx_did` (`DID`)
) ENGINE=MyISAM AUTO_INCREMENT=6341 DEFAULT CHARSET=latin1;

/*Table structure for table `domain_outgoing_default_changelog_other` */

DROP TABLE IF EXISTS `domain_outgoing_default_changelog_other`;

CREATE TABLE `domain_outgoing_default_changelog_other` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DID` int(11) NOT NULL DEFAULT '0',
  `Key` varchar(255) DEFAULT NULL,
  `ProgramFrom` int(11) NOT NULL DEFAULT '0',
  `ProgramTo` int(11) NOT NULL DEFAULT '0',
  `ChangeTime` datetime DEFAULT NULL,
  `Status` enum('New','Positive','Negative','Ignore') NOT NULL DEFAULT 'New',
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Site` varchar(255) DEFAULT NULL,
  `Remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `idx_site` (`Site`),
  KEY `idx_did` (`DID`)
) ENGINE=MyISAM AUTO_INCREMENT=395107 DEFAULT CHARSET=latin1;

/*Table structure for table `domain_outgoing_default_changelog_site` */

DROP TABLE IF EXISTS `domain_outgoing_default_changelog_site`;

CREATE TABLE `domain_outgoing_default_changelog_site` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DID` int(11) NOT NULL DEFAULT '0',
  `Key` varchar(255) DEFAULT NULL,
  `ProgramFrom` int(11) NOT NULL DEFAULT '0',
  `ProgramTo` int(11) NOT NULL DEFAULT '0',
  `ChangeTime` datetime DEFAULT NULL,
  `Status` enum('New','Positive','Negative','Ignore') NOT NULL DEFAULT 'New',
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Site` varchar(255) DEFAULT NULL,
  `Remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `idx_site` (`Site`),
  KEY `idx_did` (`DID`)
) ENGINE=MyISAM AUTO_INCREMENT=1284 DEFAULT CHARSET=latin1;

/*Table structure for table `domain_outgoing_default_other` */

DROP TABLE IF EXISTS `domain_outgoing_default_other`;

CREATE TABLE `domain_outgoing_default_other` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DID` int(11) DEFAULT NULL,
  `PID` int(11) DEFAULT NULL,
  `Key` varchar(255) DEFAULT NULL,
  `LimitAccount` varchar(255) DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `IsFake` enum('NO','YES') NOT NULL DEFAULT 'NO',
  `AffiliateDefaultUrl` text,
  `DeepUrlTemplate` text,
  `Site` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_key` (`Key`,`Site`,`DID`),
  KEY `idx_did` (`DID`,`PID`),
  KEY `idx_site` (`Site`,`IsFake`),
  KEY `idx_pid` (`PID`)
) ENGINE=MyISAM AUTO_INCREMENT=907832 DEFAULT CHARSET=latin1;

/*Table structure for table `domain_outgoing_default_other_tmpfortest` */

DROP TABLE IF EXISTS `domain_outgoing_default_other_tmpfortest`;

CREATE TABLE `domain_outgoing_default_other_tmpfortest` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DID` int(11) DEFAULT NULL,
  `PID` int(11) DEFAULT NULL,
  `Key` varchar(255) DEFAULT NULL,
  `LimitAccount` varchar(255) DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `IsFake` enum('NO','YES') NOT NULL DEFAULT 'NO',
  `AffiliateDefaultUrl` text,
  `DeepUrlTemplate` text,
  `Site` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_key` (`Key`,`Site`,`DID`),
  KEY `idx_did` (`DID`,`PID`),
  KEY `idx_site` (`Site`,`IsFake`),
  KEY `idx_pid` (`PID`)
) ENGINE=MyISAM AUTO_INCREMENT=109775 DEFAULT CHARSET=latin1;

/*Table structure for table `domain_outgoing_default_site` */

DROP TABLE IF EXISTS `domain_outgoing_default_site`;

CREATE TABLE `domain_outgoing_default_site` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DID` int(11) DEFAULT NULL,
  `PID` int(11) DEFAULT NULL,
  `Key` varchar(255) DEFAULT NULL,
  `LimitAccount` varchar(255) DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `IsFake` enum('NO','YES') NOT NULL DEFAULT 'NO',
  `AffiliateDefaultUrl` text,
  `DeepUrlTemplate` text,
  `Site` varchar(255) DEFAULT NULL,
  `TMPolicy` enum('UNKNOWN','DISALLOWED','ALLOWED') NOT NULL DEFAULT 'UNKNOWN',
  `TMTermsPolicy` enum('UNKNOWN','DISALLOWED','ALLOWED') NOT NULL DEFAULT 'UNKNOWN',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_key` (`Key`,`Site`,`DID`),
  KEY `idx_did` (`DID`,`PID`),
  KEY `idx_site` (`Site`,`IsFake`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `domain_stats` */

DROP TABLE IF EXISTS `domain_stats`;

CREATE TABLE `domain_stats` (
  `DomainId` int(11) NOT NULL,
  `Site` varchar(10) NOT NULL,
  `Rank` int(11) NOT NULL DEFAULT '0',
  `Sales3D` int(11) NOT NULL DEFAULT '0',
  `Sales7D` int(11) NOT NULL DEFAULT '0',
  `Sales1M` int(11) NOT NULL DEFAULT '0',
  `Sales3M` int(11) NOT NULL DEFAULT '0',
  `Sales1Y` int(11) NOT NULL DEFAULT '0',
  `Orders3D` int(11) NOT NULL DEFAULT '0',
  `Orders7D` int(11) NOT NULL DEFAULT '0',
  `Orders1M` int(11) NOT NULL DEFAULT '0',
  `Orders3M` int(11) NOT NULL DEFAULT '0',
  `Orders1Y` int(11) NOT NULL DEFAULT '0',
  `Revenue3D` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `Revenue7D` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `Revenue1M` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `Revenue3M` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `Revenue1Y` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `Clicks3D` int(11) NOT NULL DEFAULT '0',
  `Clicks7D` int(11) NOT NULL DEFAULT '0',
  `Clicks1M` int(11) NOT NULL DEFAULT '0',
  `Clicks3M` int(11) NOT NULL DEFAULT '0',
  `Clicks1Y` int(11) NOT NULL DEFAULT '0',
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`DomainId`,`Site`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `domain_top_level` */

DROP TABLE IF EXISTS `domain_top_level`;

CREATE TABLE `domain_top_level` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Domain` varchar(255) NOT NULL,
  `HasUsed` enum('YES','NO') NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique` (`Domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `domain_update_queue` */

DROP TABLE IF EXISTS `domain_update_queue`;

CREATE TABLE `domain_update_queue` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DomainID` int(11) NOT NULL,
  `Status` enum('NEW','PROCESSED') NOT NULL DEFAULT 'NEW',
  `AddTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=6308419 DEFAULT CHARSET=utf8;

/*Table structure for table `email_send_record` */

DROP TABLE IF EXISTS `email_send_record`;

CREATE TABLE `email_send_record` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Type` varchar(64) NOT NULL DEFAULT '',
  `To` varchar(200) NOT NULL DEFAULT '' COMMENT '收件人',
  `From` varchar(200) NOT NULL DEFAULT '' COMMENT '发件人',
  `Addtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '发送时间',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `exchange_rate` */

DROP TABLE IF EXISTS `exchange_rate`;

CREATE TABLE `exchange_rate` (
  `Date` date NOT NULL,
  `Name` varchar(255) NOT NULL,
  `ExchangeRate` float DEFAULT NULL,
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Date`,`Name`),
  UNIQUE KEY `NewIndex1` (`Name`,`Date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `feedback` */

DROP TABLE IF EXISTS `feedback`;

CREATE TABLE `feedback` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserId` int(11) DEFAULT NULL,
  `UserType` enum('Publisher','Advertiser','Manager') DEFAULT NULL,
  `Question` text,
  `AddTime` datetime DEFAULT NULL,
  `Status` enum('Pending','Replied','Ignored') NOT NULL DEFAULT 'Pending',
  `Manager` varchar(50) DEFAULT NULL,
  `Reply` text,
  `ReplyTime` datetime DEFAULT NULL,
  `StartDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `idx_u` (`UserType`,`UserId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `fin_rev_acc` */

DROP TABLE IF EXISTS `fin_rev_acc`;

CREATE TABLE `fin_rev_acc` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=226 DEFAULT CHARSET=latin1;

/*Table structure for table `ip_country_state_v4` */

DROP TABLE IF EXISTS `ip_country_state_v4`;

CREATE TABLE `ip_country_state_v4` (
  `ip_from` int(10) unsigned DEFAULT NULL,
  `ip_to` int(10) unsigned DEFAULT NULL,
  `country_code` char(2) COLLATE utf8_bin DEFAULT NULL,
  `country_name` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `region_name` varchar(128) COLLATE utf8_bin DEFAULT NULL,
  `city_name` varchar(128) COLLATE utf8_bin DEFAULT NULL,
  KEY `idx_ip_from` (`ip_from`),
  KEY `idx_ip_to` (`ip_to`),
  KEY `idx_ip_from_to` (`ip_from`,`ip_to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

/*Table structure for table `ip_country_state_v6` */

DROP TABLE IF EXISTS `ip_country_state_v6`;

CREATE TABLE `ip_country_state_v6` (
  `ip_from` decimal(39,0) unsigned DEFAULT NULL,
  `ip_to` decimal(39,0) unsigned NOT NULL,
  `country_code` char(2) COLLATE utf8_bin DEFAULT NULL,
  `country_name` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `region_name` varchar(128) COLLATE utf8_bin DEFAULT NULL,
  `city_name` varchar(128) COLLATE utf8_bin DEFAULT NULL,
  KEY `idx_ip_from` (`ip_from`),
  KEY `idx_ip_to` (`ip_to`),
  KEY `idx_ip_from_to` (`ip_from`,`ip_to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

/*Table structure for table `ip_country_v4` */

DROP TABLE IF EXISTS `ip_country_v4`;

CREATE TABLE `ip_country_v4` (
  `ip_from` int(10) unsigned DEFAULT NULL,
  `ip_to` int(10) unsigned DEFAULT NULL,
  `country_code` char(2) COLLATE utf8_bin DEFAULT NULL,
  `country_name` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  KEY `idx_ip_from` (`ip_from`),
  KEY `idx_ip_to` (`ip_to`),
  KEY `idx_ip_from_to` (`ip_from`,`ip_to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

/*Table structure for table `ip_country_v6` */

DROP TABLE IF EXISTS `ip_country_v6`;

CREATE TABLE `ip_country_v6` (
  `ip_from` decimal(39,0) unsigned DEFAULT NULL,
  `ip_to` decimal(39,0) unsigned NOT NULL,
  `country_code` char(2) COLLATE utf8_bin DEFAULT NULL,
  `country_name` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  KEY `idx_ip_from` (`ip_from`),
  KEY `idx_ip_to` (`ip_to`),
  KEY `idx_ip_from_to` (`ip_from`,`ip_to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

/*Table structure for table `logapi_publisher` */

DROP TABLE IF EXISTS `logapi_publisher`;

CREATE TABLE `logapi_publisher` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `publisherid` int(10) unsigned NOT NULL,
  `site` char(32) NOT NULL,
  `act` varchar(64) DEFAULT NULL,
  `param` varchar(255) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  `updatetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_site_act` (`site`,`act`)
) ENGINE=MyISAM AUTO_INCREMENT=249 DEFAULT CHARSET=utf8;

/*Table structure for table `map_program` */

DROP TABLE IF EXISTS `map_program`;

CREATE TABLE `map_program` (
  `MlinkProgramId` int(11) NOT NULL,
  `MegaProgramId` int(11) NOT NULL,
  PRIMARY KEY (`MlinkProgramId`,`MegaProgramId`),
  KEY `idx_` (`MegaProgramId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `map_wf_aff` */

DROP TABLE IF EXISTS `map_wf_aff`;

CREATE TABLE `map_wf_aff` (
  `MlinkAffId` int(11) NOT NULL,
  `MegaAffId` int(11) NOT NULL,
  PRIMARY KEY (`MlinkAffId`,`MegaAffId`),
  KEY `idx_aff` (`MegaAffId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `mcsky_tmp` */

DROP TABLE IF EXISTS `mcsky_tmp`;

CREATE TABLE `mcsky_tmp` (
  `domain` varchar(255) NOT NULL,
  PRIMARY KEY (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `mega_affdomain_stats` */

DROP TABLE IF EXISTS `mega_affdomain_stats`;

CREATE TABLE `mega_affdomain_stats` (
  `Date` char(10) NOT NULL DEFAULT '0000-00-00',
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `cnt` int(11) NOT NULL DEFAULT '0',
  `domainid` int(11) NOT NULL DEFAULT '0',
  `affid` int(11) NOT NULL DEFAULT '0',
  `programid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Date`,`Domain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `merchant_content` */

DROP TABLE IF EXISTS `merchant_content`;

CREATE TABLE `merchant_content` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdInBcg` int(10) unsigned NOT NULL,
  `Title` varchar(255) CHARACTER SET dec8 NOT NULL,
  `Code` varchar(255) CHARACTER SET dec8 NOT NULL,
  `MerchantId` int(10) unsigned NOT NULL,
  `Site` varchar(10) NOT NULL,
  `Remark` varchar(255) CHARACTER SET dec8 NOT NULL,
  `AddTime` datetime NOT NULL,
  `StartTime` datetime NOT NULL,
  `ExpireTime` datetime NOT NULL,
  `ExpireDateType` varchar(255) CHARACTER SET dec8 NOT NULL,
  `RemindDate` date NOT NULL,
  `PromotionContent` text CHARACTER SET dec8 NOT NULL,
  `IsExclusive` enum('YES','NO') CHARACTER SET dec8 NOT NULL DEFAULT 'NO',
  `Source` varchar(255) CHARACTER SET dec8 NOT NULL,
  `DstUrl` varchar(255) CHARACTER SET dec8 NOT NULL,
  `AffUrl` varchar(255) CHARACTER SET dec8 NOT NULL,
  `ImgUrl` varchar(255) CHARACTER SET dec8 NOT NULL,
  `RestRict` varchar(255) CHARACTER SET dec8 NOT NULL,
  `OnlineState` int(11) NOT NULL,
  `Printable` varchar(10) CHARACTER SET dec8 NOT NULL COMMENT 'value=null',
  `IsActive` enum('NO','YES') CHARACTER SET dec8 NOT NULL,
  `PromoType` int(11) NOT NULL,
  `PromotionDetail` varchar(50) CHARACTER SET dec8 NOT NULL,
  `PromotionOff` int(11) NOT NULL,
  `FreeShipping` enum('NO','YES') CHARACTER SET dec8 NOT NULL DEFAULT 'NO',
  `FreeGift` enum('NO','YES') CHARACTER SET dec8 NOT NULL DEFAULT 'NO',
  `FreeSample` enum('NO','YES') CHARACTER SET dec8 NOT NULL DEFAULT 'NO',
  `LastUpdateTime` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IdInBcg` (`IdInBcg`),
  KEY `idx_lastupdatetime` (`LastUpdateTime`),
  KEY `idx_active` (`IsActive`,`StartTime`,`ExpireTime`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `message` */

DROP TABLE IF EXISTS `message`;

CREATE TABLE `message` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PhoneNumber` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Message` text COLLATE utf8_unicode_ci,
  `IP` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Time` datetime DEFAULT NULL,
  `Type` enum('feedback','partnership') COLLATE utf8_unicode_ci DEFAULT 'feedback',
  `Status` enum('pending','answered','resolved') COLLATE utf8_unicode_ci DEFAULT 'pending',
  `user` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updatetime` datetime DEFAULT NULL,
  `remark` text COLLATE utf8_unicode_ci,
  `publisher_type` enum('I''m a Publisher','I''m a Merchant','I''m an Affiliate Network/Agency representing a merchant','None of the above') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `outbound_change_log` */

DROP TABLE IF EXISTS `outbound_change_log`;

CREATE TABLE `outbound_change_log` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DID` int(11) NOT NULL,
  `PID` int(11) NOT NULL,
  `FieldName` varchar(255) NOT NULL,
  `ValueOld` varchar(255) NOT NULL,
  `ValueNew` varchar(255) NOT NULL,
  `UpdateTime` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `outgoing_log` */

DROP TABLE IF EXISTS `outgoing_log`;

CREATE TABLE `outgoing_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pageUrl` text COMMENT '目标页面的原始url',
  `outUrl` text COMMENT '通过联盟加工过最终出站的url',
  `sessionId` varchar(64) NOT NULL COMMENT 'md5([id]_[created])作为出站的唯一标识可在联盟中的transaction中匹配',
  `created` datetime DEFAULT NULL,
  `publishTracking` varchar(255) NOT NULL DEFAULT '',
  `domainUsed` varchar(255) NOT NULL DEFAULT '',
  `domainId` int(9) unsigned NOT NULL DEFAULT '0',
  `programId` int(9) unsigned NOT NULL DEFAULT '0',
  `affId` int(11) DEFAULT NULL,
  `site` varchar(128) NOT NULL DEFAULT '',
  `ip` varchar(255) DEFAULT NULL,
  `referer` varchar(255) DEFAULT NULL,
  `cookie` varchar(255) DEFAULT NULL,
  `debug` int(11) NOT NULL DEFAULT '0',
  `requestUrl` varchar(255) DEFAULT NULL,
  `isFake` enum('Yes','No') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sess` (`sessionId`),
  KEY `idx_site` (`site`),
  KEY `idx_ip` (`ip`),
  KEY `idx_cookie` (`cookie`),
  KEY `idx_created` (`created`),
  KEY `idx_track` (`publishTracking`)
) ENGINE=MyISAM AUTO_INCREMENT=4501 DEFAULT CHARSET=latin1;

/*Table structure for table `outgoing_stats` */

DROP TABLE IF EXISTS `outgoing_stats`;

CREATE TABLE `outgoing_stats` (
  `Site` varchar(10) NOT NULL DEFAULT '',
  `Hour` char(2) NOT NULL DEFAULT '00',
  `Date` char(10) NOT NULL DEFAULT '0000-00-00',
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `OutgoingCount` int(11) NOT NULL DEFAULT '0',
  `OutgoingCountNoAff` int(11) NOT NULL DEFAULT '0',
  `AffBDG` int(11) NOT NULL DEFAULT '0',
  `AffMlink` int(11) NOT NULL DEFAULT '0',
  `AffBDGNoSub` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Site`,`Hour`,`Date`),
  KEY `idx_date` (`Date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `outgoing_stats_publisher` */

DROP TABLE IF EXISTS `outgoing_stats_publisher`;

CREATE TABLE `outgoing_stats_publisher` (
  `Site` char(32) NOT NULL DEFAULT '',
  `Hour` char(2) NOT NULL DEFAULT '00',
  `Date` char(10) NOT NULL DEFAULT '0000-00-00',
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `OutgoingCount` int(11) NOT NULL DEFAULT '0',
  `OutgoingCountNoAff` int(11) NOT NULL DEFAULT '0',
  `AffBDG` int(11) NOT NULL DEFAULT '0',
  `AffMlink` int(11) NOT NULL DEFAULT '0',
  `AffBDGNoSub` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Site`,`Hour`,`Date`),
  KEY `idx_date` (`Date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `payments` */

DROP TABLE IF EXISTS `payments`;

CREATE TABLE `payments` (
  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `Amount` decimal(16,4) DEFAULT '0.0000' COMMENT '支付金额',
  `Currency` char(3) NOT NULL DEFAULT 'USD' COMMENT '货币',
  `PublisherId` int(9) unsigned NOT NULL DEFAULT '0' COMMENT 'publisher id',
  `Site` char(32) NOT NULL DEFAULT '' COMMENT 'publisher site apikey',
  `CreateTime` datetime DEFAULT NULL COMMENT '款项生成时间',
  `PaidTime` datetime DEFAULT NULL COMMENT '操作时间',
  `Status` enum('paid','succ','fail') NOT NULL DEFAULT 'paid' COMMENT '款项状态',
  `EmailSend` enum('no','yes') NOT NULL DEFAULT 'no' COMMENT '是否发送invoice邮件',
  `PaymentType` enum('bank','paypal') NOT NULL DEFAULT 'paypal' COMMENT '支付方式',
  `PaymentDetail` text COMMENT '账户明细',
  `TransactionId` varchar(200) DEFAULT NULL COMMENT '汇款编号',
  `GroupId` varchar(100) DEFAULT NULL,
  `PaidDate` date DEFAULT NULL COMMENT 'publisher支付时间点',
  `Source` enum('CPS','PLACEMENT') NOT NULL DEFAULT 'CPS' COMMENT '支付类型。CPS: 每月支付publisher的commission PLACEMENT: placement广告费用支付',
  `InvoiceFile` varchar(200) NOT NULL DEFAULT '' COMMENT '支付明细',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uni_Site_PaidDate_Source` (`Site`,`PaidDate`,`Source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `payments_bak` */

DROP TABLE IF EXISTS `payments_bak`;

CREATE TABLE `payments_bak` (
  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `Amount` decimal(16,4) DEFAULT '0.0000' COMMENT '支付金额',
  `Currency` char(3) NOT NULL DEFAULT 'USD' COMMENT '货币',
  `PublisherId` int(9) unsigned NOT NULL DEFAULT '0' COMMENT 'publisher id',
  `Site` char(32) NOT NULL DEFAULT '' COMMENT 'publisher site apikey',
  `CreateTime` datetime DEFAULT NULL COMMENT '款项生成时间',
  `PaidTime` datetime DEFAULT NULL COMMENT '支付时间',
  `Status` enum('paid','succ','fail') NOT NULL DEFAULT 'paid' COMMENT '款项状态',
  `EmailSend` enum('no','yes') NOT NULL DEFAULT 'no' COMMENT '是否发送invoice邮件',
  `PaymentType` enum('bank','paypal') NOT NULL DEFAULT 'paypal' COMMENT '支付细节',
  `PaymentDetail` text,
  `TransactionId` varchar(200) DEFAULT NULL,
  `GroupId` varchar(100) DEFAULT NULL,
  `PaidDate` date DEFAULT NULL,
  `Source` enum('CPS','PLACEMENT') NOT NULL DEFAULT 'CPS',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uni_Site_PaidDate_Source` (`Site`,`PaidDate`,`Source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `payments_invoice` */

DROP TABLE IF EXISTS `payments_invoice`;

CREATE TABLE `payments_invoice` (
  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `CreatedDate` date DEFAULT NULL,
  `VisitedDate` date DEFAULT NULL,
  `PaidDate` date DEFAULT NULL,
  `BRID` char(32) NOT NULL DEFAULT '',
  `Commission` decimal(9,2) NOT NULL DEFAULT '0.00',
  `Site` char(32) NOT NULL DEFAULT '',
  `Af` char(32) NOT NULL DEFAULT '',
  `AffId` int(9) unsigned NOT NULL DEFAULT '0',
  `programId` int(9) unsigned NOT NULL DEFAULT '0',
  `domainId` int(9) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uni_PaidDate_BRID` (`PaidDate`,`BRID`),
  KEY `idx_BRID` (`BRID`),
  KEY `idx_Site` (`Site`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `payments_invoice_bak` */

DROP TABLE IF EXISTS `payments_invoice_bak`;

CREATE TABLE `payments_invoice_bak` (
  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `CreatedDate` date DEFAULT NULL,
  `VisitedDate` date DEFAULT NULL,
  `PaidDate` date DEFAULT NULL,
  `BRID` char(32) NOT NULL DEFAULT '',
  `Commission` decimal(9,2) NOT NULL DEFAULT '0.00',
  `Site` char(32) NOT NULL DEFAULT '',
  `Af` char(32) NOT NULL DEFAULT '',
  `AffId` int(9) unsigned NOT NULL DEFAULT '0',
  `programId` int(9) unsigned NOT NULL DEFAULT '0',
  `domainId` int(9) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uni_PaidDate_BRID` (`PaidDate`,`BRID`),
  KEY `idx_BRID` (`BRID`),
  KEY `idx_Site` (`Site`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `payments_old` */

DROP TABLE IF EXISTS `payments_old`;

CREATE TABLE `payments_old` (
  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `Amount` decimal(16,4) DEFAULT '0.0000' COMMENT '支付净额',
  `Currency` char(3) NOT NULL DEFAULT 'USD' COMMENT '货币',
  `PublisherId` int(9) unsigned NOT NULL DEFAULT '0' COMMENT 'publisher id',
  `Site` char(32) NOT NULL DEFAULT '' COMMENT 'publisher site apikey',
  `CreateTime` datetime DEFAULT NULL COMMENT '款项生成时间',
  `PaidTime` datetime DEFAULT NULL COMMENT '支付时间',
  `Status` enum('pending','paid','locked','confirmed') NOT NULL DEFAULT 'pending' COMMENT '款项状态',
  `EmailSend` enum('no','yes') NOT NULL DEFAULT 'no' COMMENT '是否发送invoice邮件',
  `PaymentDetail` text COMMENT '支付细节',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `payments_pending` */

DROP TABLE IF EXISTS `payments_pending`;

CREATE TABLE `payments_pending` (
  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `Site` char(32) NOT NULL,
  `PublisherId` int(9) NOT NULL DEFAULT '0',
  `PendingDate` date NOT NULL DEFAULT '0000-00-00',
  `Amount` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `OriginDate` date NOT NULL DEFAULT '0000-00-00',
  `PaymentsID` int(9) unsigned NOT NULL DEFAULT '0',
  `PaidDate` date NOT NULL DEFAULT '0000-00-00',
  `lastversion` char(12) NOT NULL DEFAULT '000000000000',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uni_Site_PendingDate_OriginDate` (`Site`,`PendingDate`,`OriginDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `payments_pending_invoice` */

DROP TABLE IF EXISTS `payments_pending_invoice`;

CREATE TABLE `payments_pending_invoice` (
  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `CreatedDate` date DEFAULT NULL,
  `VisitedDate` date DEFAULT NULL,
  `PaidDate` date DEFAULT '0000-00-00',
  `BRID` char(32) NOT NULL DEFAULT '',
  `Commission` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `Site` char(32) NOT NULL DEFAULT '',
  `Af` char(32) NOT NULL DEFAULT '',
  `AffId` int(9) unsigned NOT NULL DEFAULT '0',
  `programId` int(9) unsigned NOT NULL DEFAULT '0',
  `domainId` int(9) unsigned NOT NULL DEFAULT '0',
  `PendingID` int(9) unsigned NOT NULL DEFAULT '0',
  `PendingDate` date NOT NULL DEFAULT '0000-00-00',
  `OriginDate` date NOT NULL DEFAULT '0000-00-00',
  `lastversion` char(12) NOT NULL DEFAULT '000000000000' COMMENT '版本信息',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uni_PendingDate_BRID` (`PendingDate`,`BRID`),
  KEY `idx_BRID` (`BRID`),
  KEY `idx_Site` (`Site`),
  KEY `idx_PaidDate` (`PaidDate`),
  KEY `idx_PendingID` (`PendingID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `payments_pending_invoice_copy` */

DROP TABLE IF EXISTS `payments_pending_invoice_copy`;

CREATE TABLE `payments_pending_invoice_copy` (
  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `CreatedDate` date DEFAULT NULL,
  `VisitedDate` date DEFAULT NULL,
  `PaidDate` date DEFAULT NULL,
  `BRID` char(32) NOT NULL DEFAULT '',
  `Commission` decimal(9,2) NOT NULL DEFAULT '0.00',
  `Site` char(32) NOT NULL DEFAULT '',
  `Af` char(32) NOT NULL DEFAULT '',
  `AffId` int(9) unsigned NOT NULL DEFAULT '0',
  `programId` int(9) unsigned NOT NULL DEFAULT '0',
  `domainId` int(9) unsigned NOT NULL DEFAULT '0',
  `PendingID` int(9) unsigned NOT NULL DEFAULT '0',
  `PendingDate` date NOT NULL DEFAULT '0000-00-00',
  `OriginDate` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uni_PendingDate_BRID` (`PendingDate`,`BRID`),
  KEY `idx_BRID` (`BRID`),
  KEY `idx_Site` (`Site`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `payments_remit` */

DROP TABLE IF EXISTS `payments_remit`;

CREATE TABLE `payments_remit` (
  `TransactionId` varchar(32) NOT NULL,
  `PaidTime` datetime DEFAULT NULL,
  `Amount` decimal(12,2) unsigned NOT NULL DEFAULT '0.00',
  `Currency` char(3) NOT NULL DEFAULT 'USD',
  `PublisherId` int(9) unsigned NOT NULL DEFAULT '0',
  `Site` char(32) NOT NULL DEFAULT '',
  `PaymentType` enum('paypal','bank') NOT NULL DEFAULT 'paypal',
  `Status` enum('succ','fail') NOT NULL DEFAULT 'succ',
  `PaymentDetail` text,
  PRIMARY KEY (`TransactionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `placement` */

DROP TABLE IF EXISTS `placement`;

CREATE TABLE `placement` (
  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) DEFAULT NULL,
  `Desc` text,
  `AffUrl` text,
  `OriginalUrl` text,
  `ImgAdr` varchar(255) DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `Status` enum('Active','InActive') DEFAULT NULL,
  `BRURL` text,
  `StoreId` int(9) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `product_feed` */

DROP TABLE IF EXISTS `product_feed`;

CREATE TABLE `product_feed` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `AffId` int(10) NOT NULL DEFAULT '0',
  `ProgramId` int(11) unsigned NOT NULL,
  `StoreId` int(11) unsigned DEFAULT NULL,
  `AffProductId` varchar(200) NOT NULL,
  `ProductName` varchar(200) NOT NULL,
  `ProductUrl` text NOT NULL,
  `ProductDestUrl` text NOT NULL,
  `ProductDesc` text NOT NULL,
  `ProductImage` varchar(255) NOT NULL,
  `ProductLocalImage` varchar(255) DEFAULT NULL,
  `ProductPrice` decimal(16,4) DEFAULT '0.0000',
  `ProductOriginalPrice` decimal(16,4) DEFAULT '0.0000',
  `ProductRetailPrice` decimal(16,4) DEFAULT '0.0000',
  `ProductCurrency` varchar(20) DEFAULT NULL,
  `ProductCurrencySymbol` varchar(10) NOT NULL DEFAULT '',
  `Commission` text NOT NULL,
  `ProductStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `ProductEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LastUpdateTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `AddTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Source` varchar(100) NOT NULL DEFAULT 'site',
  `EncodeId` int(10) unsigned NOT NULL DEFAULT '0',
  `Country` varchar(255) DEFAULT NULL,
  `Language` varchar(200) NOT NULL DEFAULT 'en',
  `Status` enum('Active','InActive') NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ProgramId` (`ProgramId`,`AffProductId`),
  KEY `EncodeId` (`EncodeId`),
  KEY `StoreId` (`StoreId`)
) ENGINE=MyISAM AUTO_INCREMENT=1176509 DEFAULT CHARSET=latin1;

/*Table structure for table `program` */

DROP TABLE IF EXISTS `program`;

CREATE TABLE `program` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Homepage` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `AffId` int(11) NOT NULL,
  `IdInAff` varchar(255) CHARACTER SET latin1 NOT NULL,
  `CategoryExt` text CHARACTER SET latin1,
  `StatusInAff` enum('Active','TempOffline','Offline') CHARACTER SET latin1 DEFAULT NULL,
  `Partnership` enum('NoPartnership','Active','Pending','Declined','Expired','Removed') CHARACTER SET latin1 DEFAULT NULL,
  `CommissionExt` text CHARACTER SET latin1,
  `TargetCountryExt` longtext,
  `TargetCountryInt` text CHARACTER SET latin1,
  `TargetCountryIntOld` text CHARACTER SET latin1,
  `Contacts` text CHARACTER SET latin1,
  `RankInAff` int(11) DEFAULT NULL,
  `JoinDate` datetime DEFAULT NULL,
  `StatusInAffRemark` text CHARACTER SET latin1,
  `PartnershipChangeReason` text CHARACTER SET latin1,
  `WeDeclined` enum('YES','NO','NoNeedToApply') CHARACTER SET latin1 NOT NULL DEFAULT 'NO',
  `CreateDate` datetime DEFAULT NULL,
  `DropDate` datetime DEFAULT NULL,
  `Description` text CHARACTER SET latin1,
  `Remark` text CHARACTER SET latin1,
  `Research` text CHARACTER SET latin1,
  `LastCommissionExt` text CHARACTER SET latin1,
  `BonusExt` text CHARACTER SET latin1,
  `ContestExt` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `EPCDefault` decimal(10,5) DEFAULT NULL,
  `EPC30d` decimal(10,5) DEFAULT NULL,
  `EPC90d` decimal(10,5) DEFAULT NULL,
  `CookieTime` int(11) DEFAULT NULL,
  `PaymentDays` int(11) DEFAULT '0',
  `HasPendingOffer` enum('YES','NO') CHARACTER SET latin1 NOT NULL DEFAULT 'NO',
  `NumberOfOccurrences` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `SEMPolicyExt` text CHARACTER SET latin1,
  `SEMPolicyRemark` text CHARACTER SET latin1,
  `TermAndCondition` text CHARACTER SET latin1,
  `ProtectedSEMBiddingKeywords` text CHARACTER SET latin1,
  `NonCompeteSEMBiddingKeywords` text CHARACTER SET latin1,
  `RecommendedSEMBiddingKeywords` text CHARACTER SET latin1,
  `ProhibitedSEMDisplayURLContent` text CHARACTER SET latin1,
  `LimitedUseSEMDisplayURLContent` text CHARACTER SET latin1,
  `ProhibitedSEMAdCopyContent` text CHARACTER SET latin1,
  `LimitedUseSEMAdCopyContent` text CHARACTER SET latin1,
  `AuthorizedSearchEngines` text CHARACTER SET latin1,
  `SpecialInstructionsForSEM` text CHARACTER SET latin1,
  `ProhibitedWebSiteURLAndContent` text CHARACTER SET latin1,
  `UnacceptableWebSitesExt` text CHARACTER SET latin1,
  `CouponCodesPolicyExt` text CHARACTER SET latin1,
  `AllowedDirectLink` text CHARACTER SET latin1,
  `SubAffPolicyExt` text CHARACTER SET latin1,
  `Complaint` text CHARACTER SET latin1,
  `CooperateWithCouponSite` enum('YES','NO') CHARACTER SET latin1 NOT NULL DEFAULT 'YES',
  `SecondIdInAff` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `DetailPage` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `Creator` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `MobileFriendly` enum('YES','NO','UNKNOWN') CHARACTER SET latin1 NOT NULL DEFAULT 'UNKNOWN',
  `LastUpdateLinkTime` datetime DEFAULT NULL,
  `MerchantLinkCount` int(11) DEFAULT NULL,
  `MerchantFeedCount` int(11) DEFAULT NULL,
  `LastUpdateFeedTime` datetime DEFAULT NULL,
  `MerchantCountry` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `SupportDeepUrl` enum('YES','NO','UNKNOWN') CHARACTER SET latin1 DEFAULT 'UNKNOWN',
  `AffDefaultUrl` text CHARACTER SET latin1,
  `CommissionApd` text CHARACTER SET latin1 NOT NULL,
  `CategoryFirst` text CHARACTER SET latin1 NOT NULL,
  `CategorySecond` text CHARACTER SET latin1 NOT NULL,
  `AllowInaccuratePromo` enum('NO','YES') CHARACTER SET latin1 NOT NULL DEFAULT 'NO',
  `AllowNonaffCoupon` enum('NO','YES','UNKNOWN') CHARACTER SET latin1 NOT NULL DEFAULT 'UNKNOWN',
  `AllowNonaffPromo` enum('NO','YES','UNKNOWN') CHARACTER SET latin1 NOT NULL DEFAULT 'UNKNOWN',
  `LogoUrl` varchar(255) DEFAULT NULL,
  `LogoName` varchar(255) DEFAULT NULL,
  `PublisherPolicy` text,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `NewIndex1` (`AffId`,`IdInAff`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=109100 DEFAULT CHARSET=utf8;

/*Table structure for table `program_aff_default_url` */

DROP TABLE IF EXISTS `program_aff_default_url`;

CREATE TABLE `program_aff_default_url` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `AffId` int(11) DEFAULT NULL,
  `DeepUrlTpl` varchar(255) DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Remark` text,
  `SupportDeepUrlTpl` enum('YES','NO') DEFAULT 'YES',
  `DefaultUrl` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `affid` (`AffId`)
) ENGINE=MyISAM AUTO_INCREMENT=76 DEFAULT CHARSET=latin1;

/*Table structure for table `program_change_log` */

DROP TABLE IF EXISTS `program_change_log`;

CREATE TABLE `program_change_log` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ProgramId` int(11) NOT NULL,
  `IdInAff` varchar(255) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `AffId` int(11) NOT NULL,
  `FieldName` varchar(255) NOT NULL,
  `FieldValueOld` text NOT NULL,
  `FieldValueNew` text NOT NULL,
  `AddTime` datetime NOT NULL,
  `Status` enum('NEW','PROCESSED','PROCESSED_AGAIN') NOT NULL DEFAULT 'NEW',
  `IsDistributed` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `LastUpdateTime` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Status` (`Status`),
  KEY `ProgramId` (`ProgramId`),
  KEY `idx_time` (`AddTime`)
) ENGINE=MyISAM AUTO_INCREMENT=9141870 DEFAULT CHARSET=latin1;

/*Table structure for table `program_change_log_temp_for_bdg` */

DROP TABLE IF EXISTS `program_change_log_temp_for_bdg`;

CREATE TABLE `program_change_log_temp_for_bdg` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ProgramId` int(11) NOT NULL,
  `AffId` int(11) NOT NULL,
  `IdInAff` varchar(255) NOT NULL,
  `FieldName` varchar(255) DEFAULT NULL,
  `FieldValueOld` text,
  `FieldValueNew` text,
  `Status` enum('NEW','PROCESSED') NOT NULL DEFAULT 'NEW',
  `AddTime` datetime NOT NULL,
  `LastUpdateTime` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `idx_pid` (`ProgramId`),
  KEY `idx_status` (`Status`,`AffId`)
) ENGINE=MyISAM AUTO_INCREMENT=504797 DEFAULT CHARSET=latin1;

/*Table structure for table `program_ctrl` */

DROP TABLE IF EXISTS `program_ctrl`;

CREATE TABLE `program_ctrl` (
  `ProgramId` int(11) NOT NULL,
  `StatusCtrl` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `DomainCtrl` varchar(255) DEFAULT NULL,
  `CommissionCtrl` decimal(6,2) DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  PRIMARY KEY (`ProgramId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `program_domain_change_log` */

DROP TABLE IF EXISTS `program_domain_change_log`;

CREATE TABLE `program_domain_change_log` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ProgramId` int(11) NOT NULL,
  `DomainFrom` varchar(50) DEFAULT NULL,
  `DomainTo` varchar(50) DEFAULT NULL,
  `ChangeDate` datetime DEFAULT NULL,
  `Status` enum('New','Positive','Negative','Wrong') NOT NULL DEFAULT 'New',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_p` (`ProgramId`,`DomainFrom`),
  KEY `idx_status` (`Status`)
) ENGINE=MyISAM AUTO_INCREMENT=1288 DEFAULT CHARSET=latin1;

/*Table structure for table `program_domain_change_log_copy` */

DROP TABLE IF EXISTS `program_domain_change_log_copy`;

CREATE TABLE `program_domain_change_log_copy` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ProgramId` int(11) NOT NULL,
  `DomainFrom` varchar(255) DEFAULT NULL,
  `DomainTo` varchar(255) DEFAULT NULL,
  `ChangeDate` datetime DEFAULT NULL,
  `Status` enum('New','Positive','Negative','Wrong') NOT NULL DEFAULT 'New',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_p` (`ProgramId`,`DomainFrom`),
  KEY `idx_status` (`Status`)
) ENGINE=MyISAM AUTO_INCREMENT=3370 DEFAULT CHARSET=latin1;

/*Table structure for table `program_domain_links` */

DROP TABLE IF EXISTS `program_domain_links`;

CREATE TABLE `program_domain_links` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ProgramId` int(11) NOT NULL,
  `DomainId` int(11) NOT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `AffDefaultUrl` varchar(255) DEFAULT NULL,
  `DeepUrlTpl` varchar(255) DEFAULT NULL,
  `AddTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Uri` varchar(255) DEFAULT NULL,
  `IsHandle` smallint(1) NOT NULL DEFAULT '0',
  `Order` smallint(3) NOT NULL DEFAULT '99',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_p` (`ProgramId`,`DomainId`),
  KEY `idx_d` (`DomainId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `program_homepage_history` */

DROP TABLE IF EXISTS `program_homepage_history`;

CREATE TABLE `program_homepage_history` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ProgramId` int(11) NOT NULL,
  `homepage` varchar(255) DEFAULT NULL,
  `changetime` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=280547 DEFAULT CHARSET=utf8;

/*Table structure for table `program_int` */

DROP TABLE IF EXISTS `program_int`;

CREATE TABLE `program_int` (
  `ProgramId` int(11) NOT NULL,
  `HomepageInt` varchar(255) DEFAULT NULL,
  `CategoryInt` text,
  `CommissionInt` text,
  `BonusInt` varchar(255) DEFAULT NULL,
  `ContestInt` varchar(255) DEFAULT NULL,
  `UnacceptableWebSitesInt` text,
  `CouponCodesPolicyInt` text,
  `SubAffPolicyInt` text,
  `TMTermsPolicy` enum('UNKNOWN','DISALLOWED','ALLOWED','CONFIRMED_DISALLOWED') NOT NULL DEFAULT 'UNKNOWN',
  `ApplyDate` datetime DEFAULT NULL,
  `ApplyOperator` varchar(50) DEFAULT NULL,
  `TMPolicy` enum('UNKNOWN','DISALLOWED','ALLOWED','CONFIRMED_DISALLOWED') NOT NULL DEFAULT 'UNKNOWN',
  `InquiryStatus` enum('Not Inquired','Inquiring','Inquired') NOT NULL DEFAULT 'Not Inquired',
  `ReApplyStatus` enum('In-Progress','Positive','Negative','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `RevenueOrder` int(11) NOT NULL DEFAULT '9999999',
  `ContactsInt` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `GroupInc` varchar(255) DEFAULT NULL,
  `SupportSpread` varchar(255) DEFAULT NULL,
  `SupportSEO` enum('UNKNOWN','NO','YES') NOT NULL DEFAULT 'UNKNOWN',
  `SupportEmail` enum('UNKNOWN','YES','NO') NOT NULL DEFAULT 'UNKNOWN',
  `SupportSNS` enum('UNKNOWN','YES','NO') NOT NULL DEFAULT 'UNKNOWN',
  `SupportSEM` enum('UNKNOWN','YES','NO') DEFAULT NULL,
  `TermAndConditionInt` text,
  PRIMARY KEY (`ProgramId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `program_intell` */

DROP TABLE IF EXISTS `program_intell`;

CREATE TABLE `program_intell` (
  `ProgramId` int(11) NOT NULL AUTO_INCREMENT,
  `AffId` int(11) NOT NULL,
  `IdInAff` varchar(50) NOT NULL,
  `AffDefaultUrl` text,
  `DeepUrlTpl` text,
  `IsActive` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  `TrackingPattern` varchar(250) DEFAULT NULL,
  `CommissionValue` text,
  `CommissionType` enum('Percent','Value','Unknown') NOT NULL DEFAULT 'Unknown',
  `CommissionUsed` decimal(6,2) NOT NULL DEFAULT '0.00',
  `CommissionIncentive` enum('0','1') NOT NULL DEFAULT '0',
  `CommissionCurrency` varchar(10) DEFAULT NULL,
  `Domain` varchar(255) DEFAULT NULL,
  `SupportDeepUrl` enum('Yes','No','Unknown') NOT NULL DEFAULT 'Unknown',
  `SupportDeepUrlOut` enum('Yes','No','Unknown') NOT NULL DEFAULT 'Unknown',
  `SupportFake` enum('Yes','No','Unknown') NOT NULL DEFAULT 'Unknown',
  `DeniedPubCode` varchar(255) DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `OutGoingUrl` text,
  `CountryCode` varchar(255) DEFAULT NULL,
  `ShippingCountry` varchar(1000) DEFAULT NULL,
  `NotAllowCountry` varchar(255) NOT NULL DEFAULT '',
  `CategoryId` text NOT NULL,
  `Order` int(11) NOT NULL DEFAULT '0',
  `LastChangeTime` datetime DEFAULT NULL,
  `SupportType` enum('Content','All','None','Promotion') NOT NULL DEFAULT 'All',
  `RemunerationModel` enum('CPA','CPS','CPC') NOT NULL DEFAULT 'CPS',
  `LogoName` varchar(255) DEFAULT NULL,
  `CommissionBackup` varchar(255) NOT NULL,
  `PPC` enum('1','2','3','4','0') NOT NULL DEFAULT '0',
  PRIMARY KEY (`ProgramId`),
  UNIQUE KEY `idx_aff` (`AffId`,`IdInAff`),
  KEY `idx_status` (`IsActive`)
) ENGINE=MyISAM AUTO_INCREMENT=109100 DEFAULT CHARSET=latin1;

/*Table structure for table `program_intell_change_log` */

DROP TABLE IF EXISTS `program_intell_change_log`;

CREATE TABLE `program_intell_change_log` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ProgramId` int(11) NOT NULL,
  `IdInAff` varchar(255) NOT NULL,
  `AffId` int(11) NOT NULL,
  `FieldName` varchar(255) NOT NULL,
  `FieldValueOld` text NOT NULL,
  `FieldValueNew` text NOT NULL,
  `AddTime` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ProgramId` (`ProgramId`),
  KEY `idx_time` (`AddTime`)
) ENGINE=MyISAM AUTO_INCREMENT=2971587 DEFAULT CHARSET=utf8;

/*Table structure for table `program_intell_control` */

DROP TABLE IF EXISTS `program_intell_control`;

CREATE TABLE `program_intell_control` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ConditionName` varchar(255) NOT NULL COMMENT '条件类型,比如AffId,ProgramID',
  `ConditionValue` varchar(255) NOT NULL COMMENT '条件对应值',
  `FieldName` varchar(255) NOT NULL COMMENT '修改值类型,比如ShippingCountry',
  `FieldValue` varchar(255) NOT NULL COMMENT '修改值',
  `AddTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `program_intell_copy` */

DROP TABLE IF EXISTS `program_intell_copy`;

CREATE TABLE `program_intell_copy` (
  `ProgramId` int(11) NOT NULL AUTO_INCREMENT,
  `AffId` int(11) NOT NULL,
  `IdInAff` varchar(50) NOT NULL,
  `AffDefaultUrl` text,
  `DeepUrlTpl` text,
  `IsActive` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  `TrackingPattern` varchar(250) DEFAULT NULL,
  `CommissionValue` text,
  `CommissionType` enum('Percent','Value','Unknown') NOT NULL DEFAULT 'Unknown',
  `CommissionUsed` decimal(6,2) NOT NULL DEFAULT '0.00',
  `CommissionIncentive` enum('0','1') NOT NULL DEFAULT '0',
  `CommissionCurrency` varchar(10) DEFAULT NULL,
  `Domain` varchar(255) DEFAULT NULL,
  `SupportDeepUrl` enum('Yes','No','Unknown') NOT NULL DEFAULT 'Unknown',
  `SupportDeepUrlOut` enum('Yes','No','Unknown') NOT NULL DEFAULT 'Unknown',
  `SupportFake` enum('Yes','No','Unknown') NOT NULL DEFAULT 'Unknown',
  `DeniedPubCode` varchar(255) DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `OutGoingUrl` text,
  `CountryCode` varchar(255) DEFAULT NULL,
  `ShippingCountry` varchar(1000) DEFAULT NULL,
  `NotAllowCountry` varchar(255) NOT NULL DEFAULT '',
  `CategoryId` text NOT NULL,
  `Order` int(11) NOT NULL DEFAULT '0',
  `LastChangeTime` datetime DEFAULT NULL,
  `SupportType` enum('Content','Pormotion','All','None') NOT NULL DEFAULT 'All',
  `LogoName` varchar(255) DEFAULT NULL,
  `CommissionBackup` varchar(255) NOT NULL,
  PRIMARY KEY (`ProgramId`),
  UNIQUE KEY `idx_aff` (`AffId`,`IdInAff`),
  KEY `idx_status` (`IsActive`)
) ENGINE=MyISAM AUTO_INCREMENT=55007 DEFAULT CHARSET=latin1;

/*Table structure for table `program_internal` */

DROP TABLE IF EXISTS `program_internal`;

CREATE TABLE `program_internal` (
  `ProgramId` int(11) NOT NULL,
  `AffId` int(11) NOT NULL,
  `ManualStatus` enum('Active','Inactive','Unknown') NOT NULL DEFAULT 'Unknown',
  `DomainFixed` varchar(255) DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `SupportDeepUrlOut` enum('YES','NO') NOT NULL DEFAULT 'YES',
  `Url` varchar(255) DEFAULT NULL,
  `ShippingCountry` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ProgramId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `program_links_info` */

DROP TABLE IF EXISTS `program_links_info`;

CREATE TABLE `program_links_info` (
  `ProgramId` int(11) NOT NULL,
  `DomainId` int(11) NOT NULL,
  `LinksId` int(11) DEFAULT NULL,
  PRIMARY KEY (`ProgramId`,`DomainId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `program_manual` */

DROP TABLE IF EXISTS `program_manual`;

CREATE TABLE `program_manual` (
  `ProgramId` int(9) unsigned NOT NULL,
  `CommissionUsed` decimal(9,2) DEFAULT '0.00',
  `CommissionCurrency` varchar(10) DEFAULT NULL,
  `CommissionType` enum('Percent','Value','Unknown') NOT NULL DEFAULT 'Unknown',
  `RealDomain` varchar(1000) DEFAULT NULL,
  `StatusInBdg` enum('Active','Inactive','Unknown') NOT NULL DEFAULT 'Unknown',
  `NotAllowCountry` varchar(255) DEFAULT NULL,
  `TargetCountryInt` text,
  `PPC` varchar(255) NOT NULL,
  `SupportType` varchar(255) NOT NULL,
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ProgramId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `program_manual_change_log` */

DROP TABLE IF EXISTS `program_manual_change_log`;

CREATE TABLE `program_manual_change_log` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ProgramId` int(11) NOT NULL,
  `FieldName` varchar(255) NOT NULL,
  `FieldValueOld` text NOT NULL,
  `FieldValueNew` text NOT NULL,
  `ModifyUser` varchar(255) NOT NULL,
  `Status` enum('NEW','PROCESSED') NOT NULL DEFAULT 'NEW',
  `AddTime` datetime NOT NULL,
  `LastUpdateTime` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=14674 DEFAULT CHARSET=utf8;

/*Table structure for table `program_mk` */

DROP TABLE IF EXISTS `program_mk`;

CREATE TABLE `program_mk` (
  `AffId` int(11) NOT NULL,
  `IdInAff` varchar(255) NOT NULL,
  `StatusInAff` enum('Active','Offline','TempOffline') DEFAULT NULL,
  `Partnership` enum('Active','NoPartnership','Pending','Declined','Expired','Removed') DEFAULT NULL,
  PRIMARY KEY (`AffId`,`IdInAff`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `program_no_commission` */

DROP TABLE IF EXISTS `program_no_commission`;

CREATE TABLE `program_no_commission` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DateRange` varchar(255) NOT NULL,
  `ProgramID` int(11) NOT NULL,
  `Clicks` int(11) NOT NULL,
  `AddTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=3347 DEFAULT CHARSET=utf8;

/*Table structure for table `program_notice_cfg` */

DROP TABLE IF EXISTS `program_notice_cfg`;

CREATE TABLE `program_notice_cfg` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NoticeType` enum('STATUS','COMMISSION','SEMPOLICY','COUPONPOLICY','PARTNERSHIP_ON','PARTNERSHIP_OFF','PPC','SupportDeepUrl') NOT NULL,
  `ChangeType` enum('Change','On_Off','Off_On','Smaller','Larger') NOT NULL DEFAULT 'Change',
  `Fields` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

/*Table structure for table `program_order_manual` */

DROP TABLE IF EXISTS `program_order_manual`;

CREATE TABLE `program_order_manual` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ProgramID` int(11) NOT NULL,
  `ShippingCountry` text NOT NULL,
  `ExtraWeight` int(11) NOT NULL,
  `Operator` varchar(255) NOT NULL DEFAULT 'System',
  `AddTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ProgramID` (`ProgramID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `program_performance` */

DROP TABLE IF EXISTS `program_performance`;

CREATE TABLE `program_performance` (
  `ProgramId` int(11) unsigned NOT NULL,
  `CreatedDate` date NOT NULL,
  `Clicks_BR` int(11) unsigned NOT NULL DEFAULT '0',
  `Sales_BR` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `Commission_BR` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `Clicks_MK` int(11) unsigned NOT NULL DEFAULT '0',
  `Sales_MK` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `Commission_MK` decimal(16,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`ProgramId`,`CreatedDate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `program_stats` */

DROP TABLE IF EXISTS `program_stats`;

CREATE TABLE `program_stats` (
  `ProgramId` int(11) NOT NULL,
  `AffId` int(11) DEFAULT NULL,
  `IdInAff` varchar(50) DEFAULT NULL,
  `Epc` varchar(20) DEFAULT NULL,
  `Sales3D` int(11) NOT NULL DEFAULT '0',
  `Sales7D` int(11) NOT NULL DEFAULT '0',
  `Sales1M` int(11) NOT NULL DEFAULT '0',
  `Sales3M` int(11) NOT NULL DEFAULT '0',
  `Sales1Y` int(11) NOT NULL DEFAULT '0',
  `Orders3D` int(11) NOT NULL DEFAULT '0',
  `Orders7D` int(11) NOT NULL DEFAULT '0',
  `Orders1M` int(11) NOT NULL DEFAULT '0',
  `Orders3M` int(11) NOT NULL DEFAULT '0',
  `Orders1Y` int(11) NOT NULL DEFAULT '0',
  `Revenue3D` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `Revenue7D` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `Revenue1M` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `Revenue3M` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `Revenue1Y` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `Clicks3D` int(11) NOT NULL DEFAULT '0',
  `Clicks7D` int(11) NOT NULL DEFAULT '0',
  `Clicks1M` int(11) NOT NULL DEFAULT '0',
  `Clicks3M` int(11) NOT NULL DEFAULT '0',
  `Clicks1Y` int(11) NOT NULL DEFAULT '0',
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ProgramId`),
  UNIQUE KEY `idx_aff` (`AffId`,`IdInAff`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `program_status_log` */

DROP TABLE IF EXISTS `program_status_log`;

CREATE TABLE `program_status_log` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ProgramId` int(11) unsigned DEFAULT NULL,
  `StatusInAff` varchar(100) DEFAULT NULL,
  `Partnership` varchar(100) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL,
  `Time` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ProgramId` (`ProgramId`,`Time`)
) ENGINE=MyISAM AUTO_INCREMENT=167228 DEFAULT CHARSET=latin1;

/*Table structure for table `program_support_type` */

DROP TABLE IF EXISTS `program_support_type`;

CREATE TABLE `program_support_type` (
  `ProgramId` int(11) NOT NULL AUTO_INCREMENT,
  `0925` enum('Content','Pormotion','All','None') DEFAULT NULL,
  `0926` enum('Content','All','Pormotion','None') DEFAULT NULL,
  `0927` enum('ALl','Content','Promotion','None') DEFAULT NULL,
  `0928` enum('All','Content','Promotion','None') DEFAULT NULL,
  `1023` enum('All','Content','Promotion','None') DEFAULT NULL,
  `1024` enum('All','Content','Promotion','None') NOT NULL,
  `1025` enum('All','Content','Promotion','None') NOT NULL,
  `1026` enum('All','Content','Promotion','None') NOT NULL,
  `1027` enum('All','Content','Promotion','None') NOT NULL,
  `1028` enum('All','Content','Promotion','None') NOT NULL,
  `1029` enum('All','Content','Promotion','None') NOT NULL,
  `1030` enum('All','Content','Promotion','None') NOT NULL,
  `1031` enum('All','Content','Promotion','None') NOT NULL,
  `1101` enum('All','Content','Promotion','None') NOT NULL,
  `1102` enum('All','Content','Promotion','None') NOT NULL,
  `1103` enum('All','Content','Promotion','None') NOT NULL,
  `1104` enum('All','Content','Promotion','None') NOT NULL,
  `1105` enum('All','Content','Promotion','None') NOT NULL,
  `1106` enum('All','Content','Promotion','None') NOT NULL,
  `1107` enum('All','Content','Promotion','None') NOT NULL,
  `1108` enum('All','Content','Promotion','None') NOT NULL,
  `1109` enum('All','Content','Promotion','None') NOT NULL,
  `1110` enum('All','Content','Promotion','None') NOT NULL,
  `1111` enum('All','Content','Promotion','None') NOT NULL,
  `1112` enum('All','Content','Promotion','None') NOT NULL,
  `1113` enum('All','Content','Promotion','None') NOT NULL,
  `1114` enum('All','Content','Promotion','None') NOT NULL,
  `1115` enum('All','Content','Promotion','None') NOT NULL,
  `1116` enum('All','Content','Promotion','None') NOT NULL,
  `1117` enum('All','Content','Promotion','None') NOT NULL,
  `1118` enum('All','Content','Promotion','None') NOT NULL,
  `1119` enum('All','Content','Promotion','None') NOT NULL,
  `1120` enum('All','Content','Promotion','None') NOT NULL,
  `1121` enum('All','Content','Promotion','None') NOT NULL,
  `1122` enum('All','Content','Promotion','None') NOT NULL,
  `1123` enum('All','Content','Promotion','None') NOT NULL,
  `1124` enum('All','Content','Promotion','None') NOT NULL,
  `1125` enum('All','Content','Promotion','None') NOT NULL,
  `1126` enum('All','Content','Promotion','None') NOT NULL,
  `1127` enum('All','Content','Promotion','None') NOT NULL,
  `1128` enum('All','Content','Promotion','None') NOT NULL,
  `1129` enum('All','Content','Promotion','None') NOT NULL,
  `1130` enum('All','Content','Promotion','None') NOT NULL,
  `1201` enum('All','Content','Promotion','None') NOT NULL,
  `1202` enum('All','Content','Promotion','None') NOT NULL,
  `1203` enum('All','Content','Promotion','None') NOT NULL,
  `1204` enum('All','Content','Promotion','None') NOT NULL,
  `1205` enum('All','Content','Promotion','None') NOT NULL,
  `1206` enum('All','Content','Promotion','None') NOT NULL,
  `1207` enum('All','Content','Promotion','None') NOT NULL,
  PRIMARY KEY (`ProgramId`)
) ENGINE=MyISAM AUTO_INCREMENT=62711 DEFAULT CHARSET=utf8;

/*Table structure for table `program_support_type_controller` */

DROP TABLE IF EXISTS `program_support_type_controller`;

CREATE TABLE `program_support_type_controller` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `AffID` int(11) NOT NULL,
  `Field` enum('TermAndCondition','Description') NOT NULL DEFAULT 'TermAndCondition' COMMENT '匹配字段',
  `MatchingMode` enum('equal','include') NOT NULL DEFAULT 'equal' COMMENT '匹配方式，全等于或者包含',
  `Keywords` text NOT NULL COMMENT '关键字，多个关键字用五个|分开',
  `AddUser` varchar(255) NOT NULL,
  `AddTime` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `program_tmp` */

DROP TABLE IF EXISTS `program_tmp`;

CREATE TABLE `program_tmp` (
  `Network` varchar(255) NOT NULL,
  `Program` varchar(255) NOT NULL,
  `AffId` int(11) NOT NULL,
  `IdInAff` varchar(255) NOT NULL,
  `Homepage` varchar(255) NOT NULL,
  `clicks` int(11) NOT NULL,
  `sales` decimal(16,4) NOT NULL,
  `revenues` decimal(16,4) NOT NULL,
  UNIQUE KEY `Program` (`AffId`,`IdInAff`),
  KEY `as` (`AffId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `program_update_queue` */

DROP TABLE IF EXISTS `program_update_queue`;

CREATE TABLE `program_update_queue` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ProgramID` int(11) NOT NULL,
  `FieleName` varchar(255) NOT NULL DEFAULT 'CommissionExt,Partnership,Homepage,SupportDeepUrl,Name,StatusInAff,AffDefaultUrl,TargetCountryExt,CommissionUsed,SupportType,StatusInBdg',
  `Status` enum('NEW','PROCESSED') NOT NULL DEFAULT 'NEW',
  `UpdateTime` datetime NOT NULL,
  KEY `ID` (`ID`),
  KEY `p` (`ProgramID`)
) ENGINE=MyISAM AUTO_INCREMENT=8353087 DEFAULT CHARSET=utf8;

/*Table structure for table `proposal` */

DROP TABLE IF EXISTS `proposal`;

CREATE TABLE `proposal` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `WhiteAccountId` int(11) NOT NULL DEFAULT '0',
  `StoreId` int(11) NOT NULL DEFAULT '0',
  `UserType` enum('Publisher','Advertiser','Manager') DEFAULT NULL,
  `Content` text,
  `AddTime` datetime DEFAULT NULL,
  `Status` enum('Pending','Replied','Ignored') NOT NULL DEFAULT 'Pending',
  `Manager` varchar(50) DEFAULT NULL,
  `Reply` text,
  `ReplyTime` datetime DEFAULT NULL,
  `StartDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL,
  `Duration` varchar(255) NOT NULL DEFAULT '',
  `Title` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `idx_u` (`UserType`,`WhiteAccountId`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `publisher` */

DROP TABLE IF EXISTS `publisher`;

CREATE TABLE `publisher` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) DEFAULT NULL,
  `Domain` varchar(255) DEFAULT NULL,
  `UserName` varchar(64) DEFAULT NULL,
  `UserPass` varchar(64) DEFAULT NULL,
  `Status` enum('Active','Inactive','Unaudited','Remove') NOT NULL DEFAULT 'Unaudited',
  `AddTime` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Email` varchar(255) NOT NULL DEFAULT '',
  `Company` varchar(255) NOT NULL DEFAULT '',
  `Phone` varchar(64) NOT NULL DEFAULT '',
  `CompanyAddr` text,
  `Address2` text,
  `Country` int(9) NOT NULL DEFAULT '0',
  `ZipCode` varchar(255) NOT NULL,
  `Tax` int(9) unsigned NOT NULL DEFAULT '15' COMMENT '抽成百分比.0% - 100%',
  `RefID` int(9) NOT NULL DEFAULT '0' COMMENT '推荐方ID 自身的一部分收入将贡献给推荐方',
  `RefRate` int(9) NOT NULL DEFAULT '5' COMMENT '推荐方抽成.0% - 100%',
  `PayPal` varchar(255) NOT NULL DEFAULT '',
  `Career` varchar(64) DEFAULT NULL,
  `SiteOption` enum('Mixed','Content','Promotion','None') NOT NULL DEFAULT 'None',
  `Remark` text,
  `Manager` varchar(255) NOT NULL DEFAULT 'public',
  `Level` enum('TIER2','TIER1') NOT NULL DEFAULT 'TIER2' COMMENT 'publisher等级TEAR1为最高级',
  `AccountName` varchar(255) NOT NULL DEFAULT '',
  `AccountNumber` varchar(255) NOT NULL DEFAULT '',
  `AccountAddress` varchar(255) NOT NULL DEFAULT '',
  `SwiftCode` varchar(255) NOT NULL DEFAULT '',
  `BankName` varchar(255) NOT NULL DEFAULT '',
  `BranchName` varchar(255) NOT NULL DEFAULT '',
  `MinPaymentAmount` decimal(10,0) NOT NULL DEFAULT '10' COMMENT '最低付款金额',
  `NotificationEmail` varchar(255) NOT NULL DEFAULT '' COMMENT '汇款成功通知邮件',
  `ViolationsStatus` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0未被警告违规,1被警告违规',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uni_username` (`UserName`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=90643 DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_account` */

DROP TABLE IF EXISTS `publisher_account`;

CREATE TABLE `publisher_account` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PublisherId` int(11) DEFAULT NULL,
  `ApiKey` varchar(255) DEFAULT NULL,
  `Name` varchar(50) DEFAULT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `Domain` varchar(50) DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Alias` varchar(255) DEFAULT NULL,
  `GeoBreakdown` varchar(255) NOT NULL DEFAULT '',
  `SiteTypeNew` varchar(255) NOT NULL DEFAULT '',
  `SiteOption` enum('Content','Promotion','None') NOT NULL DEFAULT 'None',
  `SiteType` int(9) NOT NULL DEFAULT '0',
  `TargetCountry` int(9) NOT NULL DEFAULT '0',
  `Description` text,
  `MarketingContinent` varchar(10) DEFAULT NULL,
  `JsWork` enum('yes','no') NOT NULL DEFAULT 'yes',
  `JsIgnoreDomain` text,
  `JsWhiteDomain` text,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uni_ApiKey` (`ApiKey`) USING BTREE,
  KEY `idx_Alias` (`Alias`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1708 DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_alike` */

DROP TABLE IF EXISTS `publisher_alike`;

CREATE TABLE `publisher_alike` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `PublisherId` int(10) unsigned NOT NULL,
  `AlikePublisherId` int(10) unsigned NOT NULL,
  `AlikeContent` text,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `PublisherId` (`PublisherId`,`AlikePublisherId`)
) ENGINE=MyISAM AUTO_INCREMENT=19731 DEFAULT CHARSET=latin1;

/*Table structure for table `publisher_am` */

DROP TABLE IF EXISTS `publisher_am`;

CREATE TABLE `publisher_am` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PublisherId` int(11) NOT NULL,
  `CreatedDate` date NOT NULL DEFAULT '0000-00-00',
  `SiteName` varchar(255) CHARACTER SET latin1 NOT NULL,
  `Domain` varchar(255) CHARACTER SET latin1 NOT NULL,
  `TypeOfSite` varchar(255) CHARACTER SET latin1 NOT NULL,
  `WeeklyTraffic` int(11) NOT NULL,
  `PunishedByGoogle` enum('NO','YES') CHARACTER SET latin1 NOT NULL DEFAULT 'NO',
  `PreferBrands` varchar(255) CHARACTER SET latin1 NOT NULL,
  `Experience` varchar(255) CHARACTER SET latin1 NOT NULL,
  `AimOfTheYear` varchar(255) CHARACTER SET latin1 NOT NULL,
  `Categories` varchar(255) CHARACTER SET latin1 NOT NULL,
  `IfOtherNetwork` enum('NO','YES','I have no clue what networks are') CHARACTER SET latin1 NOT NULL DEFAULT 'NO',
  `OtherNetworks` varchar(255) CHARACTER SET latin1 NOT NULL,
  `ReadershipFrom` varchar(255) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_auth` */

DROP TABLE IF EXISTS `publisher_auth`;

CREATE TABLE `publisher_auth` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Career` varchar(64) DEFAULT NULL,
  `Auth` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `publisher_bank_account_change_log` */

DROP TABLE IF EXISTS `publisher_bank_account_change_log`;

CREATE TABLE `publisher_bank_account_change_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `publisherId` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '修改的类型 1、paypal email 2、payment amount 	3、Reminder mailbox 4、Bank account',
  `PayPal` varchar(255) NOT NULL DEFAULT '',
  `AccountName` varchar(255) NOT NULL DEFAULT '',
  `AccountNumber` varchar(255) NOT NULL DEFAULT '',
  `AccountCountry` int(9) NOT NULL DEFAULT '0',
  `AccountCity` varchar(255) NOT NULL DEFAULT '',
  `AccountAddress` varchar(255) NOT NULL DEFAULT '',
  `SwiftCode` varchar(255) NOT NULL DEFAULT '',
  `BankName` varchar(255) NOT NULL DEFAULT '',
  `BranchName` varchar(255) NOT NULL DEFAULT '',
  `MinPaymentAmount` decimal(10,0) NOT NULL DEFAULT '0',
  `NotificationEmail` varchar(255) NOT NULL DEFAULT '',
  `addTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=181 DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_change_log` */

DROP TABLE IF EXISTS `publisher_change_log`;

CREATE TABLE `publisher_change_log` (
  `Id` int(255) NOT NULL AUTO_INCREMENT,
  `PublisherId` int(255) NOT NULL,
  `Operator` varchar(255) NOT NULL DEFAULT 'system',
  `UpdateTime` date DEFAULT NULL,
  `Field` varchar(255) DEFAULT NULL,
  `OldValue` text,
  `NewValue` text,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_changelevel_log` */

DROP TABLE IF EXISTS `publisher_changelevel_log`;

CREATE TABLE `publisher_changelevel_log` (
  `ID` int(9) NOT NULL AUTO_INCREMENT,
  `PublisherId` int(9) NOT NULL DEFAULT '0',
  `OriginalLevel` varchar(50) NOT NULL DEFAULT '' COMMENT '原先的级别',
  `CurrentLevel` varchar(50) NOT NULL DEFAULT '' COMMENT '现在的级别',
  `Status` enum('Finished','Deleted','Pending') NOT NULL DEFAULT 'Pending' COMMENT '完成的状态',
  `Operation` enum('Delete','Add') NOT NULL DEFAULT 'Add' COMMENT '新增还是删除',
  `Source` enum('Manual','Signup') NOT NULL DEFAULT 'Signup' COMMENT '来源（手工的、注册的）',
  `AddUser` varchar(100) NOT NULL DEFAULT '' COMMENT '添加人',
  `AddTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `UpdateTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_collect` */

DROP TABLE IF EXISTS `publisher_collect`;

CREATE TABLE `publisher_collect` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `sid` varchar(255) DEFAULT NULL,
  `uid` int(10) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=8599 DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_data` */

DROP TABLE IF EXISTS `publisher_data`;

CREATE TABLE `publisher_data` (
  `site` char(32) NOT NULL,
  `objType` enum('domain','program','aff') NOT NULL DEFAULT 'domain',
  `objId` int(9) NOT NULL,
  `clicks` int(9) DEFAULT '0',
  `clicks_robot` int(9) DEFAULT '0',
  `clicks_robot_p` int(9) DEFAULT '0',
  `orders` int(9) DEFAULT '0',
  `sales` decimal(16,4) DEFAULT '0.0000',
  `showrevenues` decimal(16,4) DEFAULT '0.0000',
  `revenues` decimal(16,4) DEFAULT '0.0000',
  `lastversion` char(14) DEFAULT '0',
  PRIMARY KEY (`site`,`objType`,`objId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_detail` */

DROP TABLE IF EXISTS `publisher_detail`;

CREATE TABLE `publisher_detail` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PublisherId` int(11) NOT NULL,
  `StaffNumber` enum('1','2-10','11-25','26+') NOT NULL,
  `GeoBreakdown` varchar(255) NOT NULL,
  `DevKnowledge` enum('turn on a computer','wordpress templates','get by with coding','developing wizard') NOT NULL,
  `ProfitModel` varchar(64) NOT NULL,
  `WaysOfTraffic` varchar(255) NOT NULL,
  `Categories` varchar(255) NOT NULL,
  `ContentProduction` enum('in house','user generated','both') NOT NULL,
  `TypeOfContent` varchar(255) NOT NULL,
  `CurrentNetwork` varchar(255) NOT NULL,
  `SiteType` varchar(255) NOT NULL,
  `CategoryId` varchar(1000) NOT NULL,
  `AdvancedCategoryId` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  FULLTEXT KEY `categoryid` (`CategoryId`)
) ENGINE=MyISAM AUTO_INCREMENT=1521 DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_domain_detail` */

DROP TABLE IF EXISTS `publisher_domain_detail`;

CREATE TABLE `publisher_domain_detail` (
  `ID` int(11) NOT NULL AUTO_INCREMENT COMMENT '每条记录的ID',
  `DomainInfoID` int(11) NOT NULL COMMENT 'publisher_domain_info表里的ID',
  `ExtDomain` varchar(255) NOT NULL COMMENT '外部URL',
  `ExtUrl` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `DomainInfoID` (`DomainInfoID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_domain_info` */

DROP TABLE IF EXISTS `publisher_domain_info`;

CREATE TABLE `publisher_domain_info` (
  `ID` int(11) NOT NULL AUTO_INCREMENT COMMENT '每条记录的ID',
  `AccountID` int(11) NOT NULL,
  `PublisherId` int(11) NOT NULL COMMENT 'publisherId',
  `PublisherName` varchar(256) NOT NULL COMMENT 'publisher名称',
  `Url` varchar(256) NOT NULL COMMENT '网址',
  `Domain` varchar(256) NOT NULL COMMENT '域名',
  `StartTime` datetime DEFAULT NULL COMMENT '标记某一域名的第几次检查',
  `EndTime` datetime DEFAULT NULL,
  `Status` enum('pending','processing','done','error') NOT NULL COMMENT '当前URL状态',
  `Origin` varchar(255) DEFAULT NULL COMMENT '待处理url来源',
  `AddUser` varchar(255) DEFAULT NULL,
  `Addtime` datetime DEFAULT NULL,
  `IsPassSubAff` varchar(255) DEFAULT NULL COMMENT '检测首页是否通过子联盟JsCode',
  `IsPassAff` varchar(255) DEFAULT NULL COMMENT '检测首页是否通过其他联盟JsCode',
  PRIMARY KEY (`ID`),
  KEY `idx_name` (`PublisherName`(255)),
  KEY `idx_url` (`Url`(255))
) ENGINE=MyISAM AUTO_INCREMENT=1345 DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_domain_whois` */

DROP TABLE IF EXISTS `publisher_domain_whois`;

CREATE TABLE `publisher_domain_whois` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `publisherId` int(10) unsigned NOT NULL,
  `domainName` varchar(255) DEFAULT NULL,
  `domainInformation` text,
  `registrantContact` text,
  `administrativeContact` text,
  `technicalContact` text,
  `rawWhoisData` text,
  `alinkCount` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1475 DEFAULT CHARSET=latin1;

/*Table structure for table `publisher_favorites` */

DROP TABLE IF EXISTS `publisher_favorites`;

CREATE TABLE `publisher_favorites` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `aname` varchar(255) DEFAULT NULL,
  `uid` int(10) DEFAULT NULL,
  `cid` int(10) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_login_log` */

DROP TABLE IF EXISTS `publisher_login_log`;

CREATE TABLE `publisher_login_log` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PublisherId` int(11) DEFAULT NULL,
  `Ip` varchar(50) DEFAULT NULL,
  `LoginTime` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=63626 DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_page` */

DROP TABLE IF EXISTS `publisher_page`;

CREATE TABLE `publisher_page` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Url` varchar(255) NOT NULL,
  `AddTime` datetime NOT NULL,
  `AddUser` varchar(255) NOT NULL,
  `Status` enum('pending','processing','done','error') NOT NULL DEFAULT 'pending',
  `PassSubAff` varchar(255) NOT NULL DEFAULT 'None',
  `PassAff` varchar(255) NOT NULL DEFAULT 'None',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UNIQUE` (`Url`)
) ENGINE=MyISAM AUTO_INCREMENT=121 DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_page_detail` */

DROP TABLE IF EXISTS `publisher_page_detail`;

CREATE TABLE `publisher_page_detail` (
  `ID` int(11) NOT NULL AUTO_INCREMENT COMMENT '每条记录的ID',
  `DomainInfoID` int(11) NOT NULL COMMENT 'publisher_domain_info表里的ID',
  `Store` varchar(255) NOT NULL,
  `ExtDomain` varchar(255) NOT NULL COMMENT '外部URL',
  `ExtUrl` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `DomainInfoID` (`DomainInfoID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_potential` */

DROP TABLE IF EXISTS `publisher_potential`;

CREATE TABLE `publisher_potential` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `country` varchar(32) NOT NULL DEFAULT '',
  `category` varchar(64) NOT NULL DEFAULT '',
  `url` text,
  `blogname` varchar(128) NOT NULL DEFAULT '',
  `name` varchar(128) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `comment` text,
  `status` enum('new','coldcall_1','coldcall_2','coldcall_3','welcome_1','welcome_2','welcome_3','active') DEFAULT 'new',
  `laststatustime` datetime DEFAULT NULL,
  `createtime` datetime DEFAULT NULL,
  `am` varchar(64) NOT NULL DEFAULT '',
  `datafile` varchar(255) NOT NULL DEFAULT '',
  `alexarank` int(9) unsigned NOT NULL DEFAULT '0',
  `alexacrawlstatus` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '0:pending,1:doing,2:done',
  `alexaupdatetime` datetime DEFAULT NULL,
  `urlformat` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8276 DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_potential_bak` */

DROP TABLE IF EXISTS `publisher_potential_bak`;

CREATE TABLE `publisher_potential_bak` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `country` varchar(32) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `category` varchar(64) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `url` text CHARACTER SET latin1,
  `blogname` varchar(128) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `name` varchar(128) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `email` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `comment` text CHARACTER SET latin1,
  `status` enum('new','coldcall_1','coldcall_2','coldcall_3','welcome_1','welcome_2','welcome_3','active') CHARACTER SET latin1 DEFAULT 'new',
  `laststatustime` datetime DEFAULT NULL,
  `createtime` datetime DEFAULT NULL,
  `am` varchar(64) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `datafile` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `MD5` char(32) CHARACTER SET latin1 NOT NULL COMMENT 'md5(url_blogname)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_md5` (`MD5`)
) ENGINE=MyISAM AUTO_INCREMENT=1756 DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_potential_contact` */

DROP TABLE IF EXISTS `publisher_potential_contact`;

CREATE TABLE `publisher_potential_contact` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `ppid` int(9) unsigned NOT NULL DEFAULT '0' COMMENT 'publisher_potential pk',
  `type` varchar(64) NOT NULL DEFAULT '' COMMENT 'contact type like: coldcall1,welcome1',
  `time` datetime DEFAULT NULL COMMENT 'contact date time',
  `operator` varchar(64) NOT NULL DEFAULT '' COMMENT 'contact by who',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_ppid_type` (`ppid`,`type`)
) ENGINE=MyISAM AUTO_INCREMENT=3982 DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_potential_upload` */

DROP TABLE IF EXISTS `publisher_potential_upload`;

CREATE TABLE `publisher_potential_upload` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `country` varchar(32) NOT NULL DEFAULT '',
  `category` varchar(64) NOT NULL DEFAULT '',
  `url` text,
  `blogname` varchar(128) NOT NULL DEFAULT '',
  `name` varchar(128) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `comment` text,
  `status` enum('new','coldcall_1','coldcall_2','coldcall_3','welcome_1','welcome_2','welcome_3','active') DEFAULT 'new',
  `laststatustime` datetime DEFAULT NULL,
  `createtime` datetime DEFAULT NULL,
  `am` varchar(64) NOT NULL DEFAULT '',
  `datafile` varchar(255) NOT NULL DEFAULT '',
  `urlformat` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_search` */

DROP TABLE IF EXISTS `publisher_search`;

CREATE TABLE `publisher_search` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `uname` varchar(255) DEFAULT NULL,
  `svalue` varchar(255) DEFAULT NULL,
  `type` enum('Advertiser','Domain') DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1135 DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_signup` */

DROP TABLE IF EXISTS `publisher_signup`;

CREATE TABLE `publisher_signup` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Email` varchar(255) DEFAULT NULL,
  `info` varchar(255) DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `key` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_stats` */

DROP TABLE IF EXISTS `publisher_stats`;

CREATE TABLE `publisher_stats` (
  `PID` int(9) NOT NULL COMMENT 'publisher account id',
  `JsCode` enum('YES','NO') NOT NULL DEFAULT 'NO' COMMENT '是否使用js code',
  `JsLastTime` datetime DEFAULT NULL COMMENT '上次使用js code时间',
  `JsFirstTime` datetime DEFAULT NULL COMMENT '第一次使用js code时间',
  PRIMARY KEY (`PID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_sub` */

DROP TABLE IF EXISTS `publisher_sub`;

CREATE TABLE `publisher_sub` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `PublisherId` int(10) NOT NULL DEFAULT '0' COMMENT 'publihserId',
  `ParentPublisherId` int(10) NOT NULL DEFAULT '0' COMMENT '父级账号publisherId',
  `AccountId` int(10) NOT NULL DEFAULT '0' COMMENT 'publihserAccountId',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_update` */

DROP TABLE IF EXISTS `publisher_update`;

CREATE TABLE `publisher_update` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `PublisherId` int(10) DEFAULT NULL COMMENT '对应Publisher的ID',
  `time` datetime DEFAULT NULL COMMENT '修改时间/更新时间',
  `info` text COMMENT '提交申请的信息',
  `state` smallint(2) DEFAULT '0' COMMENT '0未修改，1以修改',
  `update_user` varchar(50) DEFAULT NULL COMMENT '修改信息的后台人员',
  `uptype` smallint(2) DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `pid` (`PublisherId`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=124 DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_update_log` */

DROP TABLE IF EXISTS `publisher_update_log`;

CREATE TABLE `publisher_update_log` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `PubLisherId` int(10) DEFAULT NULL,
  `update_user` varchar(10) DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  `oldinfo` text,
  `newinfo` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=144 DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_violation_log` */

DROP TABLE IF EXISTS `publisher_violation_log`;

CREATE TABLE `publisher_violation_log` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `pid` int(10) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

/*Table structure for table `publisher_warning` */

DROP TABLE IF EXISTS `publisher_warning`;

CREATE TABLE `publisher_warning` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `BlockBy` enum('Affiliate','Merchant','Internal','Store') NOT NULL DEFAULT 'Internal',
  `AccountId` int(11) DEFAULT NULL,
  `AccountType` enum('AccountId','PublisherId') NOT NULL DEFAULT 'AccountId',
  `ObjId` int(11) DEFAULT NULL,
  `ObjType` enum('Affiliate','Program','Store') DEFAULT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  `AddTime` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `AddUser` varchar(50) DEFAULT NULL,
  `Remark` text,
  `winfo` text,
  `PublisherId` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

/*Table structure for table `question` */

DROP TABLE IF EXISTS `question`;

CREATE TABLE `question` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `qtext` text,
  `atext` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8;

/*Table structure for table `r_d_p_c` */

DROP TABLE IF EXISTS `r_d_p_c`;

CREATE TABLE `r_d_p_c` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DomainId` int(11) DEFAULT NULL,
  `ProgramId` int(11) DEFAULT NULL,
  `Country` char(2) NOT NULL,
  `Status` enum('Inactive','Active') NOT NULL DEFAULT 'Inactive',
  `AddTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateTime` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_u` (`DomainId`,`ProgramId`,`Country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `r_domain_bcg_merchant` */

DROP TABLE IF EXISTS `r_domain_bcg_merchant`;

CREATE TABLE `r_domain_bcg_merchant` (
  `MerchantId` int(10) NOT NULL,
  `DomainId` int(10) NOT NULL,
  `Site` char(60) NOT NULL,
  `Logo` varchar(255) NOT NULL,
  `AddTime` datetime NOT NULL,
  PRIMARY KEY (`DomainId`,`Site`),
  UNIQUE KEY `MerchantId_Site` (`MerchantId`,`Site`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `r_domain_handle` */

DROP TABLE IF EXISTS `r_domain_handle`;

CREATE TABLE `r_domain_handle` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `From` int(11) NOT NULL,
  `To` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

/*Table structure for table `r_domain_program` */

DROP TABLE IF EXISTS `r_domain_program`;

CREATE TABLE `r_domain_program` (
  `DID` int(11) NOT NULL,
  `PID` int(11) NOT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  `Order` smallint(3) NOT NULL DEFAULT '99',
  `AffDefaultUrl` text NOT NULL,
  `DeepUrlTpl` text NOT NULL,
  `IsFake` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `IsHandle` enum('0','1') NOT NULL DEFAULT '0',
  `LastUpdateTime` datetime DEFAULT NULL,
  `Uri` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`DID`,`PID`),
  KEY `idx_p` (`PID`,`Status`) USING BTREE,
  KEY `idx_lastupdatetime` (`LastUpdateTime`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `r_domain_program_copy` */

DROP TABLE IF EXISTS `r_domain_program_copy`;

CREATE TABLE `r_domain_program_copy` (
  `DID` int(11) NOT NULL,
  `PID` int(11) NOT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  `Order` smallint(3) NOT NULL DEFAULT '99',
  `AffDefaultUrl` varchar(255) NOT NULL DEFAULT '',
  `DeepUrlTpl` varchar(255) NOT NULL DEFAULT '',
  `IsFake` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `IsHandle` enum('1','0') NOT NULL DEFAULT '0',
  `LastUpdateTime` datetime DEFAULT NULL,
  `Uri` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`DID`,`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `r_domain_program_ctrl` */

DROP TABLE IF EXISTS `r_domain_program_ctrl`;

CREATE TABLE `r_domain_program_ctrl` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DomainId` int(11) DEFAULT NULL,
  `ProgramId` int(11) DEFAULT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  `AddUser` varchar(20) DEFAULT NULL,
  `AddTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Country` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `idx_status` (`Status`)
) ENGINE=MyISAM AUTO_INCREMENT=438 DEFAULT CHARSET=utf8;

/*Table structure for table `r_domain_program_log` */

DROP TABLE IF EXISTS `r_domain_program_log`;

CREATE TABLE `r_domain_program_log` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DomainId` int(11) DEFAULT NULL,
  `AddUser` varchar(20) DEFAULT NULL,
  `LastUpdateTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Remark` varchar(500) DEFAULT NULL,
  `Status` enum('Active','Inactive') DEFAULT NULL,
  `PID_from` int(11) DEFAULT NULL,
  `PID_to` int(11) DEFAULT NULL,
  `Country` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=179 DEFAULT CHARSET=latin1;

/*Table structure for table `r_domain_union` */

DROP TABLE IF EXISTS `r_domain_union`;

CREATE TABLE `r_domain_union` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DomainFromId` int(11) DEFAULT NULL,
  `DomainToId` int(11) DEFAULT NULL,
  `AddDate` datetime DEFAULT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_d` (`DomainFromId`,`DomainToId`)
) ENGINE=MyISAM AUTO_INCREMENT=9207 DEFAULT CHARSET=latin1;

/*Table structure for table `r_store_category` */

DROP TABLE IF EXISTS `r_store_category`;

CREATE TABLE `r_store_category` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `StoreId` int(11) NOT NULL,
  `CategoryId` int(11) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  `LastUpdateTime` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Idx` (`StoreId`,`CategoryId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `r_store_domain` */

DROP TABLE IF EXISTS `r_store_domain`;

CREATE TABLE `r_store_domain` (
  `StoreId` int(11) NOT NULL,
  `DomainId` int(11) NOT NULL,
  `CountryCode` varchar(10) DEFAULT NULL,
  `SubDomain` varchar(50) DEFAULT NULL,
  `DomainAffSupport` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `LastUpdateTime` datetime NOT NULL,
  PRIMARY KEY (`StoreId`,`DomainId`),
  KEY `idx_domain` (`DomainId`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `r_store_domain_backup` */

DROP TABLE IF EXISTS `r_store_domain_backup`;

CREATE TABLE `r_store_domain_backup` (
  `StoreId` int(11) NOT NULL,
  `DomainId` int(11) NOT NULL,
  `CountryCode` varchar(10) DEFAULT NULL,
  `SubDomain` varchar(50) DEFAULT NULL,
  `DomainAffSupport` enum('YES','NO') NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`StoreId`,`DomainId`),
  KEY `idx_domain` (`DomainId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `r_store_program` */

DROP TABLE IF EXISTS `r_store_program`;

CREATE TABLE `r_store_program` (
  `StoreId` int(11) NOT NULL,
  `ProgramId` int(11) NOT NULL,
  `UpdateTime` datetime NOT NULL,
  `Outbound` text,
  UNIQUE KEY `S_P` (`StoreId`,`ProgramId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `r_store_publisher_ctr` */

DROP TABLE IF EXISTS `r_store_publisher_ctr`;

CREATE TABLE `r_store_publisher_ctr` (
  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `StoreId` int(9) unsigned NOT NULL DEFAULT '0',
  `PAId` int(9) unsigned NOT NULL DEFAULT '0',
  `Status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unq_storeid_paid` (`StoreId`,`PAId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `redirect_default` */

DROP TABLE IF EXISTS `redirect_default`;

CREATE TABLE `redirect_default` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DID` int(11) DEFAULT NULL,
  `PID` int(11) DEFAULT NULL,
  `Key` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `LimitAccount` varchar(255) DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `IsFake` enum('NO','YES') NOT NULL DEFAULT 'NO',
  `AffiliateDefaultUrl` text,
  `DeepUrlTemplate` text,
  `Site` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `SupportType` enum('Content','All') NOT NULL DEFAULT 'All',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_key` (`Key`,`Site`,`DID`,`SupportType`),
  KEY `idx_did` (`DID`,`PID`),
  KEY `idx_site` (`Site`,`IsFake`),
  KEY `idx_pid` (`PID`)
) ENGINE=MyISAM AUTO_INCREMENT=508857 DEFAULT CHARSET=utf8;

/*Table structure for table `redirect_default_tmpfortest` */

DROP TABLE IF EXISTS `redirect_default_tmpfortest`;

CREATE TABLE `redirect_default_tmpfortest` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DID` int(11) DEFAULT NULL,
  `PID` int(11) DEFAULT NULL,
  `Key` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `LimitAccount` varchar(255) DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `IsFake` enum('NO','YES') NOT NULL DEFAULT 'NO',
  `AffiliateDefaultUrl` text,
  `DeepUrlTemplate` text,
  `Site` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `SupportType` enum('Content','All') NOT NULL DEFAULT 'All',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_key` (`Key`,`Site`,`DID`,`SupportType`),
  KEY `idx_did` (`DID`,`PID`),
  KEY `idx_site` (`Site`,`IsFake`),
  KEY `idx_pid` (`PID`)
) ENGINE=MyISAM AUTO_INCREMENT=188953 DEFAULT CHARSET=utf8;

/*Table structure for table `rpt_transaction_base` */

DROP TABLE IF EXISTS `rpt_transaction_base`;

CREATE TABLE `rpt_transaction_base` (
  `ID` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `Af` varchar(32) NOT NULL DEFAULT '' COMMENT '联盟数据所在文件夹名称',
  `AffId` int(9) NOT NULL DEFAULT '0' COMMENT '联盟ID',
  `Created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '交易创建时间',
  `CreatedDate` date NOT NULL DEFAULT '0000-00-00' COMMENT '交易创建时间,年月日,用于索引',
  `Updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '交易修改时间',
  `UpdatedDate` date NOT NULL DEFAULT '0000-00-00' COMMENT '交易修改时间,年月日,用于索引',
  `Sales` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '销售额',
  `Commission` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '佣金',
  `IdInAff` varchar(64) NOT NULL DEFAULT '',
  `ProgramName` varchar(128) NOT NULL DEFAULT '' COMMENT '商家名',
  `SID` varchar(64) NOT NULL DEFAULT '',
  `OrderId` varchar(128) NOT NULL DEFAULT '' COMMENT '订单id',
  `ClickTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '点击时间',
  `TradeId` varchar(128) NOT NULL DEFAULT '' COMMENT '交易id',
  `TradeStatus` varchar(64) NOT NULL DEFAULT '' COMMENT '交易状态',
  `OldCur` varchar(16) NOT NULL DEFAULT '' COMMENT '原始货币',
  `OldSales` varchar(64) NOT NULL DEFAULT '' COMMENT '原始销售额',
  `OldCommission` varchar(64) NOT NULL DEFAULT '' COMMENT '原始佣金',
  `TradeType` varchar(64) NOT NULL DEFAULT '' COMMENT '交易类型',
  `TradeKey` varchar(128) NOT NULL DEFAULT '' COMMENT '交易唯一标示符(Af_Created_md5[IdInAff||SID])',
  `Site` varchar(64) NOT NULL DEFAULT '' COMMENT '来源站点,没有分配为空,找不到来源站点则为unknow',
  `PublishTracking` varchar(64) NOT NULL DEFAULT '' COMMENT '商家的跟踪代码',
  `DataFile` varchar(128) NOT NULL DEFAULT '' COMMENT '数据文件名',
  `Referrer` text COMMENT '从联盟中获取的referrer',
  PRIMARY KEY (`ID`),
  KEY `idx_TradeKey` (`TradeKey`),
  KEY `idx_SID` (`SID`),
  KEY `idx_Af` (`Af`),
  KEY `idx_CreatedDate` (`CreatedDate`),
  KEY `idx_site` (`Site`),
  KEY `idx_DataFile` (`DataFile`),
  KEY `idx_UpdatedDate` (`UpdatedDate`)
) ENGINE=MyISAM AUTO_INCREMENT=8453958 DEFAULT CHARSET=utf8;

/*Table structure for table `rpt_transaction_base_2` */

DROP TABLE IF EXISTS `rpt_transaction_base_2`;

CREATE TABLE `rpt_transaction_base_2` (
  `ID` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `Af` varchar(32) NOT NULL DEFAULT '' COMMENT '联盟数据所在文件夹名称',
  `AffId` int(9) NOT NULL DEFAULT '0' COMMENT '联盟ID',
  `Created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '交易创建时间',
  `CreatedDate` date NOT NULL DEFAULT '0000-00-00' COMMENT '交易创建时间,年月日,用于索引',
  `Updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '交易修改时间',
  `UpdatedDate` date NOT NULL DEFAULT '0000-00-00' COMMENT '交易修改时间,年月日,用于索引',
  `Sales` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '销售额',
  `Commission` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '佣金',
  `IdInAff` varchar(64) NOT NULL DEFAULT '',
  `ProgramName` varchar(128) NOT NULL DEFAULT '' COMMENT '商家名',
  `SID` varchar(64) NOT NULL DEFAULT '',
  `OrderId` varchar(128) NOT NULL DEFAULT '' COMMENT '订单id',
  `ClickTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '点击时间',
  `TradeId` varchar(128) NOT NULL DEFAULT '' COMMENT '交易id',
  `TradeStatus` varchar(64) NOT NULL DEFAULT '' COMMENT '交易状态',
  `OldCur` varchar(16) NOT NULL DEFAULT '' COMMENT '原始货币',
  `OldSales` varchar(64) NOT NULL DEFAULT '' COMMENT '原始销售额',
  `OldCommission` varchar(64) NOT NULL DEFAULT '' COMMENT '原始佣金',
  `TradeType` varchar(64) NOT NULL DEFAULT '' COMMENT '交易类型',
  `TradeKey` varchar(128) NOT NULL DEFAULT '' COMMENT '交易唯一标示符(Af_Created_md5[IdInAff||SID])',
  `Site` varchar(64) NOT NULL DEFAULT '' COMMENT '来源站点,没有分配为空,找不到来源站点则为unknow',
  `PublishTracking` varchar(64) NOT NULL DEFAULT '' COMMENT '商家的跟踪代码',
  `DataFile` varchar(128) NOT NULL DEFAULT '' COMMENT '数据文件名',
  PRIMARY KEY (`ID`),
  KEY `idx_TradeKey` (`TradeKey`),
  KEY `idx_SID` (`SID`),
  KEY `idx_Af` (`Af`),
  KEY `idx_CreatedDate` (`CreatedDate`),
  KEY `idx_site` (`Site`),
  KEY `idx_DataFile` (`DataFile`),
  KEY `idx_UpdatedDate` (`UpdatedDate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `rpt_transaction_base_bak` */

DROP TABLE IF EXISTS `rpt_transaction_base_bak`;

CREATE TABLE `rpt_transaction_base_bak` (
  `ID` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `Af` varchar(32) NOT NULL DEFAULT '' COMMENT '联盟数据所在文件夹名称',
  `AffId` int(9) NOT NULL DEFAULT '0' COMMENT '联盟ID',
  `Created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '交易创建时间',
  `CreatedDate` date NOT NULL DEFAULT '0000-00-00' COMMENT '交易创建时间,年月日,用于索引',
  `Updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '交易修改时间',
  `UpdatedDate` date NOT NULL DEFAULT '0000-00-00' COMMENT '交易修改时间,年月日,用于索引',
  `Sales` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '销售额',
  `Commission` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '佣金',
  `IdInAff` varchar(64) NOT NULL DEFAULT '',
  `ProgramName` varchar(128) NOT NULL DEFAULT '' COMMENT '商家名',
  `SID` varchar(64) NOT NULL DEFAULT '',
  `OrderId` varchar(128) NOT NULL DEFAULT '' COMMENT '订单id',
  `ClickTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '点击时间',
  `TradeId` varchar(128) NOT NULL DEFAULT '' COMMENT '交易id',
  `TradeStatus` varchar(64) NOT NULL DEFAULT '' COMMENT '交易状态',
  `OldCur` varchar(16) NOT NULL DEFAULT '' COMMENT '原始货币',
  `OldSales` varchar(64) NOT NULL DEFAULT '' COMMENT '原始销售额',
  `OldCommission` varchar(64) NOT NULL DEFAULT '' COMMENT '原始佣金',
  `TradeType` varchar(64) NOT NULL DEFAULT '' COMMENT '交易类型',
  `TradeKey` varchar(128) NOT NULL DEFAULT '' COMMENT '交易唯一标示符(Af_Created_md5[IdInAff||SID])',
  `Site` varchar(64) NOT NULL DEFAULT '' COMMENT '来源站点,没有分配为空,找不到来源站点则为unknow',
  `PublishTracking` varchar(64) NOT NULL DEFAULT '' COMMENT '商家的跟踪代码',
  `DataFile` varchar(128) NOT NULL DEFAULT '' COMMENT '数据文件名',
  `Referrer` text COMMENT '从联盟中获取的referrer',
  PRIMARY KEY (`ID`),
  KEY `idx_TradeKey` (`TradeKey`),
  KEY `idx_SID` (`SID`),
  KEY `idx_Af` (`Af`),
  KEY `idx_CreatedDate` (`CreatedDate`),
  KEY `idx_site` (`Site`),
  KEY `idx_DataFile` (`DataFile`),
  KEY `idx_UpdatedDate` (`UpdatedDate`)
) ENGINE=MyISAM AUTO_INCREMENT=3608158 DEFAULT CHARSET=utf8;

/*Table structure for table `rpt_transaction_daily` */

DROP TABLE IF EXISTS `rpt_transaction_daily`;

CREATE TABLE `rpt_transaction_daily` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `AffId` int(11) NOT NULL,
  `Date` date DEFAULT NULL,
  `AffName` varchar(255) DEFAULT NULL,
  `Commission` double DEFAULT '0',
  `CreatDate` datetime DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `exist` (`AffId`,`Date`)
) ENGINE=MyISAM AUTO_INCREMENT=1792 DEFAULT CHARSET=latin1;

/*Table structure for table `rpt_transaction_file` */

DROP TABLE IF EXISTS `rpt_transaction_file`;

CREATE TABLE `rpt_transaction_file` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `file_path` varchar(255) DEFAULT NULL,
  `file_md5` char(32) DEFAULT NULL,
  `af` varchar(64) NOT NULL DEFAULT '',
  `updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_file_path` (`file_path`),
  KEY `idx_af` (`af`)
) ENGINE=MyISAM AUTO_INCREMENT=285945 DEFAULT CHARSET=utf8;

/*Table structure for table `rpt_transaction_unique` */

DROP TABLE IF EXISTS `rpt_transaction_unique`;

CREATE TABLE `rpt_transaction_unique` (
  `ID` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `Af` varchar(32) NOT NULL DEFAULT '' COMMENT '联盟数据所在文件夹名称',
  `AffId` int(9) NOT NULL DEFAULT '0' COMMENT '联盟ID',
  `Created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '交易创建时间',
  `CreatedDate` date NOT NULL DEFAULT '0000-00-00' COMMENT '交易创建时间,年月日,用于索引',
  `Updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '交易修改时间',
  `UpdatedDate` date NOT NULL DEFAULT '0000-00-00' COMMENT '交易修改时间,年月日,用于索引',
  `Sales` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '销售额',
  `Commission` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '佣金',
  `IdInAff` varchar(64) NOT NULL DEFAULT '',
  `ProgramName` varchar(128) NOT NULL DEFAULT '' COMMENT '商家名',
  `SID` varchar(64) NOT NULL DEFAULT '',
  `OrderId` varchar(128) NOT NULL DEFAULT '' COMMENT '订单id',
  `ClickTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '点击时间',
  `TradeId` varchar(128) NOT NULL DEFAULT '' COMMENT '交易id',
  `TradeStatus` varchar(64) NOT NULL DEFAULT '' COMMENT '交易状态',
  `TradeCancelReason` text COMMENT '交易退款原因',
  `OldCur` varchar(16) NOT NULL DEFAULT '' COMMENT '原始货币',
  `OldSales` varchar(64) NOT NULL DEFAULT '' COMMENT '原始销售额',
  `OldCommission` varchar(64) NOT NULL DEFAULT '' COMMENT '原始佣金',
  `TradeType` varchar(64) NOT NULL DEFAULT '' COMMENT '交易类型',
  `TradeKey` varchar(128) NOT NULL DEFAULT '' COMMENT '交易唯一标示符(Af_Created_md5[IdInAff||SID])',
  `Site` varchar(64) NOT NULL DEFAULT '' COMMENT '来源站点,没有分配为空,找不到来源站点则为unknow',
  `PublishTracking` varchar(64) NOT NULL DEFAULT '' COMMENT '商家的跟踪代码',
  `DataFile` varchar(128) NOT NULL DEFAULT '' COMMENT '数据文件名',
  `domainUsed` varchar(255) NOT NULL DEFAULT '',
  `domainId` int(9) unsigned NOT NULL DEFAULT '0',
  `programId` int(9) NOT NULL DEFAULT '0',
  `linkId` int(9) unsigned NOT NULL DEFAULT '0',
  `Visited` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '访问时间(对应的是产生交易的点击出站时间)',
  `VisitedDate` date DEFAULT '0000-00-00' COMMENT '访问日期',
  `Alias` varchar(64) NOT NULL COMMENT '别名如 bfdc csfr',
  `Tax` int(9) unsigned NOT NULL DEFAULT '0' COMMENT 'BR抽成百分比 0 - 100%',
  `TaxCommission` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT 'BR抽成commission金额',
  `ShowRate` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '自身收益百分比 0 - 100%',
  `ShowCommission` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '自身收益commission金额',
  `RefRate` int(9) NOT NULL DEFAULT '0' COMMENT 'Referrer抽成百分比 0 - 100%',
  `RefCommission` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT 'Referrer抽成commission金额',
  `RefPublisherId` int(9) NOT NULL DEFAULT '0' COMMENT 'Referrer publisher id',
  `State` enum('PENDING','PAID','CONFIRMED','FINE','REMOVED','CANCELLED') NOT NULL DEFAULT 'PENDING' COMMENT 'P 未确认 C 已确认/对方已付款 PAID 已付款给PUB',
  `BRID` varchar(32) NOT NULL DEFAULT '' COMMENT 'BR交易ID 唯一 对外公开ID',
  `Referrer` text COMMENT '从联盟中获取的referrer',
  `ReferrerCheck` tinyint(3) unsigned DEFAULT '0',
  `Changed` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否被更新',
  `Country` char(2) NOT NULL DEFAULT '' COMMENT '同步出站国家信息',
  `CommissionStatus` tinyint(3) NOT NULL DEFAULT '0',
  `PaidDate` date NOT NULL DEFAULT '0000-00-00' COMMENT '支付时间',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uni_TradeKey` (`TradeKey`) USING BTREE,
  KEY `idx_SID` (`SID`) USING BTREE,
  KEY `idx_DataFile` (`DataFile`) USING BTREE,
  KEY `idx_site` (`Site`) USING BTREE,
  KEY `idx_Af` (`Af`) USING BTREE,
  KEY `idx_UpdatedDate` (`UpdatedDate`) USING BTREE,
  KEY `idx_domainUsed` (`domainUsed`) USING BTREE,
  KEY `idx_programId` (`programId`) USING BTREE,
  KEY `idx_VisitedDate` (`VisitedDate`) USING BTREE,
  KEY `idx_Alias` (`Alias`) USING BTREE,
  KEY `idx_CreatedDate_Site` (`CreatedDate`,`Site`) USING BTREE,
  KEY `idx_BRID` (`BRID`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1835910 DEFAULT CHARSET=utf8;

/*Table structure for table `rpt_transaction_unique_2` */

DROP TABLE IF EXISTS `rpt_transaction_unique_2`;

CREATE TABLE `rpt_transaction_unique_2` (
  `ID` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `Af` varchar(32) NOT NULL DEFAULT '' COMMENT '联盟数据所在文件夹名称',
  `AffId` int(9) NOT NULL DEFAULT '0' COMMENT '联盟ID',
  `Created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '交易创建时间',
  `CreatedDate` date NOT NULL DEFAULT '0000-00-00' COMMENT '交易创建时间,年月日,用于索引',
  `Updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '交易修改时间',
  `UpdatedDate` date NOT NULL DEFAULT '0000-00-00' COMMENT '交易修改时间,年月日,用于索引',
  `Sales` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '销售额',
  `Commission` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '佣金',
  `IdInAff` varchar(64) NOT NULL DEFAULT '',
  `ProgramName` varchar(128) NOT NULL DEFAULT '' COMMENT '商家名',
  `SID` varchar(64) NOT NULL DEFAULT '',
  `OrderId` varchar(128) NOT NULL DEFAULT '' COMMENT '订单id',
  `ClickTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '点击时间',
  `TradeId` varchar(128) NOT NULL DEFAULT '' COMMENT '交易id',
  `TradeStatus` varchar(64) NOT NULL DEFAULT '' COMMENT '交易状态',
  `OldCur` varchar(16) NOT NULL DEFAULT '' COMMENT '原始货币',
  `OldSales` varchar(64) NOT NULL DEFAULT '' COMMENT '原始销售额',
  `OldCommission` varchar(64) NOT NULL DEFAULT '' COMMENT '原始佣金',
  `TradeType` varchar(64) NOT NULL DEFAULT '' COMMENT '交易类型',
  `TradeKey` varchar(128) NOT NULL DEFAULT '' COMMENT '交易唯一标示符(Af_Created_md5[IdInAff||SID])',
  `Site` varchar(64) NOT NULL DEFAULT '' COMMENT '来源站点,没有分配为空,找不到来源站点则为unknow',
  `PublishTracking` varchar(64) NOT NULL DEFAULT '' COMMENT '商家的跟踪代码',
  `DataFile` varchar(128) NOT NULL DEFAULT '' COMMENT '数据文件名',
  `domainUsed` varchar(255) NOT NULL DEFAULT '',
  `domainId` int(9) unsigned NOT NULL DEFAULT '0',
  `programId` int(9) NOT NULL DEFAULT '0',
  `Visited` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '访问时间(对应的是产生交易的点击出站时间)',
  `VisitedDate` date DEFAULT '0000-00-00' COMMENT '访问日期',
  `Alias` varchar(64) NOT NULL COMMENT '别名如 bfdc csfr',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uni_TradeKey` (`TradeKey`),
  KEY `idx_SID` (`SID`),
  KEY `idx_DataFile` (`DataFile`),
  KEY `idx_CreatedDate` (`CreatedDate`),
  KEY `idx_site` (`Site`),
  KEY `idx_Af` (`Af`),
  KEY `idx_UpdatedDate` (`UpdatedDate`),
  KEY `idx_domainUsed` (`domainUsed`),
  KEY `idx_programId` (`programId`),
  KEY `idx_VisitedDate` (`VisitedDate`),
  KEY `idx_Alias` (`Alias`)
) ENGINE=MyISAM AUTO_INCREMENT=22965 DEFAULT CHARSET=latin1;

/*Table structure for table `rpt_transaction_unique_bak` */

DROP TABLE IF EXISTS `rpt_transaction_unique_bak`;

CREATE TABLE `rpt_transaction_unique_bak` (
  `ID` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `Af` varchar(32) NOT NULL DEFAULT '' COMMENT '联盟数据所在文件夹名称',
  `AffId` int(9) NOT NULL DEFAULT '0' COMMENT '联盟ID',
  `Created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '交易创建时间',
  `CreatedDate` date NOT NULL DEFAULT '0000-00-00' COMMENT '交易创建时间,年月日,用于索引',
  `Updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '交易修改时间',
  `UpdatedDate` date NOT NULL DEFAULT '0000-00-00' COMMENT '交易修改时间,年月日,用于索引',
  `Sales` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '销售额',
  `Commission` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '佣金',
  `IdInAff` varchar(64) NOT NULL DEFAULT '',
  `ProgramName` varchar(128) NOT NULL DEFAULT '' COMMENT '商家名',
  `SID` varchar(64) NOT NULL DEFAULT '',
  `OrderId` varchar(128) NOT NULL DEFAULT '' COMMENT '订单id',
  `ClickTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '点击时间',
  `TradeId` varchar(128) NOT NULL DEFAULT '' COMMENT '交易id',
  `TradeStatus` varchar(64) NOT NULL DEFAULT '' COMMENT '交易状态',
  `TradeCancelReason` text COMMENT '交易退款原因',
  `OldCur` varchar(16) NOT NULL DEFAULT '' COMMENT '原始货币',
  `OldSales` varchar(64) NOT NULL DEFAULT '' COMMENT '原始销售额',
  `OldCommission` varchar(64) NOT NULL DEFAULT '' COMMENT '原始佣金',
  `TradeType` varchar(64) NOT NULL DEFAULT '' COMMENT '交易类型',
  `TradeKey` varchar(128) NOT NULL DEFAULT '' COMMENT '交易唯一标示符(Af_Created_md5[IdInAff||SID])',
  `Site` varchar(64) NOT NULL DEFAULT '' COMMENT '来源站点,没有分配为空,找不到来源站点则为unknow',
  `PublishTracking` varchar(64) NOT NULL DEFAULT '' COMMENT '商家的跟踪代码',
  `DataFile` varchar(128) NOT NULL DEFAULT '' COMMENT '数据文件名',
  `domainUsed` varchar(255) NOT NULL DEFAULT '',
  `domainId` int(9) unsigned NOT NULL DEFAULT '0',
  `programId` int(9) NOT NULL DEFAULT '0',
  `linkId` int(9) unsigned NOT NULL DEFAULT '0',
  `Visited` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '访问时间(对应的是产生交易的点击出站时间)',
  `VisitedDate` date DEFAULT '0000-00-00' COMMENT '访问日期',
  `Alias` varchar(64) NOT NULL COMMENT '别名如 bfdc csfr',
  `Tax` int(9) unsigned NOT NULL DEFAULT '0' COMMENT 'BR抽成百分比 0 - 100%',
  `TaxCommission` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT 'BR抽成commission金额',
  `ShowRate` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '自身收益百分比 0 - 100%',
  `ShowCommission` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '自身收益commission金额',
  `RefRate` int(9) NOT NULL DEFAULT '0' COMMENT 'Referrer抽成百分比 0 - 100%',
  `RefCommission` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT 'Referrer抽成commission金额',
  `RefPublisherId` int(9) NOT NULL DEFAULT '0' COMMENT 'Referrer publisher id',
  `State` enum('PENDING','PAID','CONFIRMED') NOT NULL DEFAULT 'PENDING' COMMENT 'P 未确认 C 已确认/对方已付款 PAID 已付款给PUB',
  `BRID` varchar(32) NOT NULL DEFAULT '' COMMENT 'BR交易ID 唯一 对外公开ID',
  `Referrer` text COMMENT '从联盟中获取的referrer',
  `ReferrerCheck` tinyint(3) unsigned DEFAULT '0',
  `Changed` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否被更新',
  `Country` char(2) NOT NULL DEFAULT '' COMMENT '同步出站国家信息',
  `CommissionStatus` tinyint(3) NOT NULL DEFAULT '0',
  `PaidDate` date NOT NULL DEFAULT '0000-00-00' COMMENT '支付时间',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uni_TradeKey` (`TradeKey`),
  KEY `idx_SID` (`SID`),
  KEY `idx_DataFile` (`DataFile`),
  KEY `idx_site` (`Site`),
  KEY `idx_Af` (`Af`),
  KEY `idx_UpdatedDate` (`UpdatedDate`),
  KEY `idx_domainUsed` (`domainUsed`),
  KEY `idx_programId` (`programId`),
  KEY `idx_VisitedDate` (`VisitedDate`),
  KEY `idx_Alias` (`Alias`),
  KEY `idx_CreatedDate_Site` (`CreatedDate`,`Site`),
  KEY `idx_BRID` (`BRID`)
) ENGINE=MyISAM AUTO_INCREMENT=1816726 DEFAULT CHARSET=utf8;

/*Table structure for table `rpt_transaction_unique_del` */

DROP TABLE IF EXISTS `rpt_transaction_unique_del`;

CREATE TABLE `rpt_transaction_unique_del` (
  `ID` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `Af` varchar(32) NOT NULL DEFAULT '' COMMENT '联盟数据所在文件夹名称',
  `AffId` int(9) NOT NULL DEFAULT '0' COMMENT '联盟ID',
  `Created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '交易创建时间',
  `CreatedDate` date NOT NULL DEFAULT '0000-00-00' COMMENT '交易创建时间,年月日,用于索引',
  `Updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '交易修改时间',
  `UpdatedDate` date NOT NULL DEFAULT '0000-00-00' COMMENT '交易修改时间,年月日,用于索引',
  `Sales` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '销售额',
  `Commission` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '佣金',
  `IdInAff` varchar(64) NOT NULL DEFAULT '',
  `ProgramName` varchar(128) NOT NULL DEFAULT '' COMMENT '商家名',
  `SID` varchar(64) NOT NULL DEFAULT '',
  `OrderId` varchar(128) NOT NULL DEFAULT '' COMMENT '订单id',
  `ClickTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '点击时间',
  `TradeId` varchar(128) NOT NULL DEFAULT '' COMMENT '交易id',
  `TradeStatus` varchar(64) NOT NULL DEFAULT '' COMMENT '交易状态',
  `OldCur` varchar(16) NOT NULL DEFAULT '' COMMENT '原始货币',
  `OldSales` varchar(64) NOT NULL DEFAULT '' COMMENT '原始销售额',
  `OldCommission` varchar(64) NOT NULL DEFAULT '' COMMENT '原始佣金',
  `TradeType` varchar(64) NOT NULL DEFAULT '' COMMENT '交易类型',
  `TradeKey` varchar(128) NOT NULL DEFAULT '' COMMENT '交易唯一标示符(Af_Created_md5[IdInAff||SID])',
  `Site` varchar(64) NOT NULL DEFAULT '' COMMENT '来源站点,没有分配为空,找不到来源站点则为unknow',
  `PublishTracking` varchar(64) NOT NULL DEFAULT '' COMMENT '商家的跟踪代码',
  `DataFile` varchar(128) NOT NULL DEFAULT '' COMMENT '数据文件名',
  `domainUsed` varchar(255) NOT NULL DEFAULT '',
  `domainId` int(9) unsigned NOT NULL DEFAULT '0',
  `programId` int(9) NOT NULL DEFAULT '0',
  `Visited` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '访问时间(对应的是产生交易的点击出站时间)',
  `VisitedDate` date DEFAULT '0000-00-00' COMMENT '访问日期',
  `Alias` varchar(64) NOT NULL COMMENT '别名如 bfdc csfr',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uni_TradeKey` (`TradeKey`),
  KEY `idx_SID` (`SID`),
  KEY `idx_DataFile` (`DataFile`),
  KEY `idx_CreatedDate` (`CreatedDate`),
  KEY `idx_site` (`Site`),
  KEY `idx_Af` (`Af`),
  KEY `idx_UpdatedDate` (`UpdatedDate`),
  KEY `idx_domainUsed` (`domainUsed`),
  KEY `idx_programId` (`programId`),
  KEY `idx_VisitedDate` (`VisitedDate`),
  KEY `idx_Alias` (`Alias`)
) ENGINE=MyISAM AUTO_INCREMENT=12258065 DEFAULT CHARSET=latin1;

/*Table structure for table `rpt_transaction_unique_inner` */

DROP TABLE IF EXISTS `rpt_transaction_unique_inner`;

CREATE TABLE `rpt_transaction_unique_inner` (
  `Source` enum('bdg','mk') NOT NULL DEFAULT 'bdg',
  `SourceID` bigint(11) unsigned NOT NULL,
  `Af` varchar(32) NOT NULL DEFAULT '' COMMENT '联盟数据所在文件夹名称',
  `AffId` int(9) NOT NULL DEFAULT '0' COMMENT '联盟ID',
  `Created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '交易创建时间',
  `CreatedDate` date NOT NULL DEFAULT '0000-00-00' COMMENT '交易创建时间,年月日,用于索引',
  `Updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '交易修改时间',
  `UpdatedDate` date NOT NULL DEFAULT '0000-00-00' COMMENT '交易修改时间,年月日,用于索引',
  `Sales` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '销售额',
  `Commission` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '佣金',
  `IdInAff` varchar(64) NOT NULL DEFAULT '',
  `ProgramName` varchar(128) NOT NULL DEFAULT '' COMMENT '商家名',
  `SID` varchar(64) NOT NULL DEFAULT '',
  `OrderId` varchar(128) NOT NULL DEFAULT '' COMMENT '订单id',
  `TradeId` varchar(128) NOT NULL DEFAULT '' COMMENT '交易id',
  `Site` varchar(64) NOT NULL DEFAULT '' COMMENT '来源站点,没有分配为空,找不到来源站点则为unknow',
  `PublishTracking` varchar(64) NOT NULL DEFAULT '' COMMENT '商家的跟踪代码',
  `domainId` int(9) unsigned NOT NULL DEFAULT '0',
  `programId` int(9) NOT NULL DEFAULT '0',
  `TradeKey` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`Source`,`TradeKey`),
  KEY `idx_SID` (`SID`),
  KEY `idx_site` (`Site`),
  KEY `idx_Af` (`Af`),
  KEY `idx_UpdatedDate` (`UpdatedDate`),
  KEY `idx_programId` (`programId`),
  KEY `idx_CreatedDate_Site` (`CreatedDate`,`Site`),
  KEY `idx_sourceid` (`SourceID`),
  KEY `TradeKey` (`TradeKey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `rpt_transaction_unknown` */

DROP TABLE IF EXISTS `rpt_transaction_unknown`;

CREATE TABLE `rpt_transaction_unknown` (
  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `TID` int(9) unsigned DEFAULT NULL,
  `AffId` int(9) unsigned DEFAULT NULL,
  `ProgramId` int(9) unsigned DEFAULT NULL,
  `OldSales` decimal(16,4) DEFAULT NULL,
  `OldCommission` decimal(16,4) DEFAULT NULL,
  `Sales` decimal(16,4) DEFAULT NULL,
  `Commission` decimal(16,4) DEFAULT NULL,
  `Site` varchar(64) NOT NULL,
  `Alias` varchar(64) NOT NULL,
  `Percent` decimal(9,2) unsigned DEFAULT NULL,
  `Remark` text,
  PRIMARY KEY (`ID`),
  KEY `idx_tid` (`TID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `rpt_transaction_upload` */

DROP TABLE IF EXISTS `rpt_transaction_upload`;

CREATE TABLE `rpt_transaction_upload` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `old_file_name` varchar(255) NOT NULL DEFAULT '',
  `datafile` varchar(255) NOT NULL DEFAULT '',
  `file_path` varchar(255) NOT NULL DEFAULT '',
  `created` datetime DEFAULT NULL,
  `status` enum('info','go') DEFAULT 'info',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `sem_statis_affiliate` */

DROP TABLE IF EXISTS `sem_statis_affiliate`;

CREATE TABLE `sem_statis_affiliate` (
  `createddate` date NOT NULL COMMENT '创建时间',
  `affId` int(9) unsigned NOT NULL DEFAULT '0' COMMENT 'affiliate id',
  `site` char(32) NOT NULL COMMENT '所属site key',
  `clicks` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '出站点击量',
  `orders` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '订单量',
  `sales` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '订单销售额',
  `revenues` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '订单盈利',
  PRIMARY KEY (`createddate`,`affId`,`site`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `sem_statis_program` */

DROP TABLE IF EXISTS `sem_statis_program`;

CREATE TABLE `sem_statis_program` (
  `createddate` date NOT NULL COMMENT '创建时间',
  `programId` int(9) unsigned NOT NULL DEFAULT '0' COMMENT 'program id',
  `site` char(32) NOT NULL COMMENT '所属site key',
  `clicks` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '出站点击量',
  `orders` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '订单量',
  `sales` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '订单销售额',
  `revenues` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '订单盈利',
  PRIMARY KEY (`createddate`,`programId`,`site`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `short_pool` */

DROP TABLE IF EXISTS `short_pool`;

CREATE TABLE `short_pool` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Short` char(7) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_short` (`Short`)
) ENGINE=MyISAM AUTO_INCREMENT=104948 DEFAULT CHARSET=utf8;

/*Table structure for table `short_url` */

DROP TABLE IF EXISTS `short_url`;

CREATE TABLE `short_url` (
  `Short` char(7) NOT NULL,
  `Long` text,
  `LongMD5` char(32) DEFAULT NULL,
  `AccountId` int(11) NOT NULL DEFAULT '0' COMMENT 'publisher account',
  `AddTime` datetime NOT NULL,
  PRIMARY KEY (`Short`),
  UNIQUE KEY `idx_long` (`LongMD5`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `statis_affiliate` */

DROP TABLE IF EXISTS `statis_affiliate`;

CREATE TABLE `statis_affiliate` (
  `createddate` date NOT NULL COMMENT '创建时间',
  `affId` int(9) unsigned NOT NULL DEFAULT '0' COMMENT 'affiliate id',
  `site` char(32) NOT NULL COMMENT '所属site key',
  `clicks` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '出站点击量',
  `orders` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '订单量',
  `sales` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '订单销售额',
  `revenues` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '订单盈利',
  `showrevenues` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '扣除佣金后盈利',
  `lastversion` char(14) NOT NULL DEFAULT '0' COMMENT '最后一次执行更新版本',
  PRIMARY KEY (`createddate`,`affId`,`site`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `statis_affiliate_br` */

DROP TABLE IF EXISTS `statis_affiliate_br`;

CREATE TABLE `statis_affiliate_br` (
  `createddate` date NOT NULL COMMENT '创建时间',
  `affId` int(9) unsigned NOT NULL DEFAULT '0' COMMENT 'affiliate id',
  `site` char(32) NOT NULL COMMENT '所属site key',
  `clicks` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '出站点击量',
  `clicks_robot` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '机器人出站点击量',
  `clicks_robot_p` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '疑似机器人出站点击量',
  `orders` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '订单量',
  `sales` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '订单销售额',
  `revenues` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '订单盈利',
  `showrevenues` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '扣除佣金后盈利',
  `lastversion` char(14) NOT NULL DEFAULT '0' COMMENT '最后一次执行更新版本',
  `c_orders` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '按照clicktime计算的订单量',
  `c_sales` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '按照clicktime计算的订单销售额',
  `c_revenues` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '按照clicktime计算的订单盈利',
  `c_showrevenues` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '按照clicktime计算的扣除佣金后盈利',
  `c_lastversion` char(14) NOT NULL DEFAULT '0',
  PRIMARY KEY (`createddate`,`affId`,`site`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `statis_br` */

DROP TABLE IF EXISTS `statis_br`;

CREATE TABLE `statis_br` (
  `createddate` date NOT NULL,
  `site` char(32) NOT NULL DEFAULT '',
  `affid` int(9) NOT NULL DEFAULT '0',
  `programid` int(9) NOT NULL DEFAULT '0',
  `domainid` int(9) NOT NULL DEFAULT '0',
  `country` char(2) NOT NULL DEFAULT '',
  `clicks` int(9) DEFAULT '0',
  `clicks_robot` int(9) DEFAULT '0',
  `clicks_robot_p` int(9) DEFAULT '0',
  `orders` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '订单量',
  `sales` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '订单销售额',
  `revenues` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '订单盈利',
  `showrevenues` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `lastversion` char(14) NOT NULL DEFAULT '0' COMMENT '最后一次执行更新版本',
  `c_orders` int(9) NOT NULL DEFAULT '0',
  `c_sales` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `c_revenues` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `c_showrevenues` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `c_lastversion` char(14) NOT NULL DEFAULT '0',
  PRIMARY KEY (`createddate`,`site`,`affid`,`programid`,`domainid`,`country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `statis_domain` */

DROP TABLE IF EXISTS `statis_domain`;

CREATE TABLE `statis_domain` (
  `createddate` date NOT NULL COMMENT '创建时间',
  `domainId` int(9) unsigned NOT NULL DEFAULT '0' COMMENT 'domain id',
  `storeId` int(9) unsigned NOT NULL DEFAULT '0' COMMENT 'store id',
  `site` char(32) NOT NULL COMMENT '所属site key',
  `clicks` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '出站点击量',
  `orders` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '订单量',
  `sales` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '订单销售额',
  `revenues` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '订单盈利',
  `showrevenues` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `lastversion` char(14) NOT NULL DEFAULT '0' COMMENT '最后一次执行更新版本',
  PRIMARY KEY (`createddate`,`domainId`,`site`),
  KEY `idx_css` (`createddate`,`storeId`,`site`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `statis_domain_br` */

DROP TABLE IF EXISTS `statis_domain_br`;

CREATE TABLE `statis_domain_br` (
  `createddate` date NOT NULL COMMENT '创建时间',
  `domainId` int(9) unsigned NOT NULL DEFAULT '0' COMMENT 'domain id',
  `storeId` int(9) unsigned NOT NULL DEFAULT '0' COMMENT 'store id',
  `site` char(32) NOT NULL COMMENT '所属site key',
  `clicks` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '出站点击量',
  `clicks_robot` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '机器人出站点击量',
  `clicks_robot_p` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '疑似机器人出站点击量',
  `orders` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '订单量',
  `sales` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '订单销售额',
  `revenues` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '订单盈利',
  `showrevenues` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `lastversion` char(14) NOT NULL DEFAULT '0' COMMENT '最后一次执行更新版本',
  `c_orders` int(9) NOT NULL DEFAULT '0',
  `c_sales` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `c_revenues` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `c_showrevenues` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `c_lastversion` char(14) NOT NULL DEFAULT '0',
  PRIMARY KEY (`createddate`,`domainId`,`site`),
  KEY `idx_css` (`createddate`,`storeId`,`site`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `statis_link` */

DROP TABLE IF EXISTS `statis_link`;

CREATE TABLE `statis_link` (
  `createddate` date NOT NULL,
  `site` char(32) NOT NULL DEFAULT '',
  `linkid` int(9) unsigned NOT NULL DEFAULT '0',
  `country` char(2) NOT NULL DEFAULT '',
  `clicks` int(9) DEFAULT '0',
  `clicks_robot` int(9) DEFAULT '0',
  `clicks_robot_p` int(9) DEFAULT '0',
  `orders` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '订单量',
  `sales` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '订单销售额',
  `revenues` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '订单盈利',
  `showrevenues` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `lastversion` char(14) NOT NULL DEFAULT '0' COMMENT '最后一次执行更新版本',
  `c_orders` int(9) NOT NULL DEFAULT '0',
  `c_sales` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `c_revenues` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `c_showrevenues` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `c_lastversion` char(14) NOT NULL DEFAULT '0',
  PRIMARY KEY (`createddate`,`site`,`linkid`,`country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `statis_program` */

DROP TABLE IF EXISTS `statis_program`;

CREATE TABLE `statis_program` (
  `createddate` date NOT NULL COMMENT '创建时间',
  `programId` int(9) unsigned NOT NULL DEFAULT '0' COMMENT 'program id',
  `site` char(32) NOT NULL COMMENT '所属site key',
  `clicks` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '出站点击量',
  `orders` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '订单量',
  `sales` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '订单销售额',
  `revenues` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '订单盈利',
  `showrevenues` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '扣除佣金盈利',
  `lastversion` char(14) NOT NULL DEFAULT '0' COMMENT '最后一次更新时间作为版本',
  PRIMARY KEY (`createddate`,`programId`,`site`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `statis_program_br` */

DROP TABLE IF EXISTS `statis_program_br`;

CREATE TABLE `statis_program_br` (
  `createddate` date NOT NULL COMMENT '创建时间',
  `programId` int(9) unsigned NOT NULL DEFAULT '0' COMMENT 'program id',
  `site` char(32) NOT NULL COMMENT '所属site key',
  `clicks` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '出站点击量',
  `clicks_robot` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '机器人出站点击量',
  `clicks_robot_p` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '疑似机器人出站点击量',
  `orders` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '订单量',
  `sales` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '订单销售额',
  `revenues` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '订单盈利',
  `showrevenues` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '扣除佣金盈利',
  `lastversion` char(14) NOT NULL DEFAULT '0' COMMENT '最后一次更新时间作为版本',
  `c_orders` int(9) NOT NULL DEFAULT '0',
  `c_sales` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `c_revenues` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `c_showrevenues` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `c_lastversion` char(14) NOT NULL DEFAULT '0',
  PRIMARY KEY (`createddate`,`programId`,`site`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `store` */

DROP TABLE IF EXISTS `store`;

CREATE TABLE `store` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `Domains` text CHARACTER SET latin1 NOT NULL,
  `SubDomains` varchar(500) CHARACTER SET latin1 DEFAULT NULL,
  `CountryCode` text CHARACTER SET latin1,
  `Affids` varchar(255) NOT NULL DEFAULT '',
  `Programids` varchar(255) NOT NULL DEFAULT '',
  `AddTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `StoreAffSupport` enum('YES','NO') CHARACTER SET latin1 NOT NULL DEFAULT 'NO',
  `SupportLoyalty` enum('YES','NO') CHARACTER SET latin1 NOT NULL DEFAULT 'YES',
  `SupportCoupon` enum('YES','NO') CHARACTER SET latin1 NOT NULL DEFAULT 'YES',
  `NameOptimized` varchar(255) NOT NULL,
  `LogoName` varchar(255) CHARACTER SET latin1 NOT NULL,
  `SupportType` enum('None','Content','Promotion','All','Mixed') CHARACTER SET latin1 NOT NULL DEFAULT 'All' COMMENT 'None:P0;Content:P1;Promotion:P2;All:P3.',
  `Clicks` int(11) NOT NULL DEFAULT '0',
  `Clicks_robot` int(11) NOT NULL DEFAULT '0',
  `Clicks_robot_p` int(11) NOT NULL DEFAULT '0',
  `PClicks` int(11) NOT NULL DEFAULT '0',
  `PClicks_robot` int(11) NOT NULL DEFAULT '0',
  `PClicks_robot_p` int(11) NOT NULL DEFAULT '0',
  `Sales` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `Commission_publisher` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `CategoryId` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `IsAffiliate` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否是联盟(1是 0否)',
  `CategoryUpdateTime` datetime DEFAULT NULL,
  `CategoryHumanCtrl` enum('YES','NO') CHARACTER SET latin1 NOT NULL DEFAULT 'NO',
  `PPC` enum('1','2','3','4','0') CHARACTER SET latin1 NOT NULL DEFAULT '0',
  `PPCStatus` enum('PPCAllowed','Mixed','NotAllow','UNKNOWN') CHARACTER SET latin1 NOT NULL DEFAULT 'UNKNOWN',
  `OptimizedType` enum('0','1') NOT NULL DEFAULT '0',
  `Rank` int(11) NOT NULL DEFAULT '999999',
  `Description` text,
  `Sales_publisher` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `Commission` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `LogoStatus` tinyint(3) NOT NULL DEFAULT '0' COMMENT '0:默认,1:人工修改,2:from program',
  `Exclusive_Code` enum('YES','NO') DEFAULT 'NO',
  `CPA_Increase` enum('YES','NO') DEFAULT 'NO',
  `Allow_Inaccurate_Promo` enum('YES','NO') DEFAULT 'NO',
  `Promo_Code_has_been_blacklisted` varchar(500) DEFAULT NULL,
  `Word_has_been_blacklisted` varchar(500) DEFAULT NULL,
  `Coupon_Policy_Others` varchar(500) DEFAULT NULL,
  `Allow_to_Change_Promotion_TitleOrDescription` enum('YES','NO') DEFAULT 'NO',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_name` (`Name`),
  FULLTEXT KEY `CategoryId` (`CategoryId`)
) ENGINE=MyISAM AUTO_INCREMENT=200185 DEFAULT CHARSET=utf8;

/*Table structure for table `store_blacklisting` */

DROP TABLE IF EXISTS `store_blacklisting`;

CREATE TABLE `store_blacklisting` (
  `StoreId` int(11) DEFAULT NULL,
  `PublisherId` int(11) DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `LastUpdateTime` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `store_by_advertiser` */

DROP TABLE IF EXISTS `store_by_advertiser`;

CREATE TABLE `store_by_advertiser` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `StoreId` int(11) NOT NULL,
  `AdvertiserId` int(11) DEFAULT NULL,
  `Name` varchar(255) DEFAULT NULL,
  `Desc` text,
  `LogoAdr` varchar(255) DEFAULT NULL,
  `CategoryId` text,
  `Status` enum('Delete','Active') NOT NULL DEFAULT 'Active',
  `TagId` text,
  `CommissionValue` text,
  `CommissionType` enum('Percent','Value','Unknown') NOT NULL DEFAULT 'Unknown',
  `CommissionUsed` decimal(6,2) NOT NULL DEFAULT '0.00',
  `CommissionCurrency` varchar(10) DEFAULT NULL,
  `AdvertiserEmail` varchar(100) DEFAULT NULL,
  `AdvertiserDesc` text,
  `AdvertiserEmailType` enum('Advertiser','Agency','Unknown') NOT NULL DEFAULT 'Unknown',
  `AdvertiserPhone` varchar(50) DEFAULT NULL,
  `Feedbackto` varchar(50) DEFAULT NULL,
  `CoopType` enum('Whitelist','Blacklist') NOT NULL DEFAULT 'Blacklist',
  `SupportCountry` text,
  `SupportType` varchar(350) DEFAULT NULL,
  `PPCPolicy` text,
  `EarningMethods` text,
  `ExclusiveInfo` text,
  `SupportWay` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

/*Table structure for table `store_change_log` */

DROP TABLE IF EXISTS `store_change_log`;

CREATE TABLE `store_change_log` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `StoreId` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `FieldName` varchar(255) NOT NULL,
  `FieldValueOld` text NOT NULL,
  `FieldValueNew` text NOT NULL,
  `LastUpdateTime` datetime NOT NULL,
  `Operator` varchar(255) NOT NULL COMMENT '修改人',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=34484 DEFAULT CHARSET=utf8;

/*Table structure for table `store_copy` */

DROP TABLE IF EXISTS `store_copy`;

CREATE TABLE `store_copy` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `Domains` text CHARACTER SET latin1 NOT NULL,
  `SubDomains` varchar(500) CHARACTER SET latin1 DEFAULT NULL,
  `CountryCode` text CHARACTER SET latin1,
  `Affids` varchar(255) NOT NULL DEFAULT '',
  `AddTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `StoreAffSupport` enum('YES','NO') CHARACTER SET latin1 NOT NULL DEFAULT 'NO',
  `SupportLoyalty` enum('YES','NO') CHARACTER SET latin1 NOT NULL DEFAULT 'YES',
  `SupportCoupon` enum('YES','NO') CHARACTER SET latin1 NOT NULL DEFAULT 'YES',
  `NameOptimized` varchar(255) NOT NULL,
  `LogoName` varchar(255) CHARACTER SET latin1 NOT NULL,
  `SupportType` enum('None','Content','Promotion','All','Mixed') CHARACTER SET latin1 NOT NULL DEFAULT 'All' COMMENT 'None:P0;Content:P1;Promotion:P2;All:P3.',
  `Clicks` int(11) NOT NULL DEFAULT '0',
  `Commission` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `CategoryId` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `CategoryUpdateTime` datetime DEFAULT NULL,
  `CategoryHumanCtrl` enum('YES','NO') CHARACTER SET latin1 NOT NULL DEFAULT 'NO',
  `PPC` enum('1','2','3','4','0') CHARACTER SET latin1 NOT NULL DEFAULT '0',
  `OptimizedType` enum('0','1') NOT NULL DEFAULT '0',
  `Sales` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `Rank` int(11) NOT NULL DEFAULT '999999',
  `Description` text,
  `LogoStatus` enum('0','1','2') NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_name` (`Name`),
  FULLTEXT KEY `CategoryId` (`CategoryId`)
) ENGINE=MyISAM AUTO_INCREMENT=192432 DEFAULT CHARSET=utf8;

/*Table structure for table `store_custom` */

DROP TABLE IF EXISTS `store_custom`;

CREATE TABLE `store_custom` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Domain` varchar(255) NOT NULL COMMENT '匹配域名',
  `CustomName` varchar(255) NOT NULL COMMENT '人工自定义Name',
  `IsActive` enum('Active','Inactive') DEFAULT 'Active',
  `Status` enum('NEW','PROCESSED','RENEW') DEFAULT 'NEW',
  `AddTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  `UpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Operator` varchar(255) NOT NULL,
  `StoreId` int(11) DEFAULT '0' COMMENT 'store id',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Domain` (`Domain`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

/*Table structure for table `store_desc` */

DROP TABLE IF EXISTS `store_desc`;

CREATE TABLE `store_desc` (
  `StoreID` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `ProgramIDs` varchar(128) DEFAULT NULL,
  `AffIDs` varchar(128) DEFAULT NULL,
  `ProgramUse` int(9) DEFAULT NULL,
  `AffUse` int(9) DEFAULT NULL,
  `Desc` text,
  PRIMARY KEY (`StoreID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `store_in_subaff` */

DROP TABLE IF EXISTS `store_in_subaff`;

CREATE TABLE `store_in_subaff` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DateRange` varchar(255) NOT NULL,
  `StoreID` int(11) NOT NULL,
  `Domain` varchar(255) NOT NULL,
  `Clicks` int(11) NOT NULL,
  `Revenues` decimal(10,4) NOT NULL,
  `AffIds` varchar(255) NOT NULL,
  `AddTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=19716 DEFAULT CHARSET=utf8;

/*Table structure for table `store_logo` */

DROP TABLE IF EXISTS `store_logo`;

CREATE TABLE `store_logo` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `storeid` int(10) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=5283 DEFAULT CHARSET=utf8;

/*Table structure for table `store_multi_brand` */

DROP TABLE IF EXISTS `store_multi_brand`;

CREATE TABLE `store_multi_brand` (
  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `Keywords` varchar(128) NOT NULL DEFAULT '',
  `StoreName` varchar(128) NOT NULL DEFAULT '',
  `StoreId` int(9) unsigned NOT NULL DEFAULT '0',
  `CategoryId` int(9) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unq_keywords_storeid_categoryid` (`Keywords`,`StoreId`,`CategoryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `store_no_commission` */

DROP TABLE IF EXISTS `store_no_commission`;

CREATE TABLE `store_no_commission` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DateRange` varchar(255) NOT NULL,
  `StoreID` int(11) NOT NULL,
  `Clicks` int(11) NOT NULL,
  `AffIds` varchar(255) NOT NULL,
  `AddTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=50355 DEFAULT CHARSET=utf8;

/*Table structure for table `store_program_change_log` */

DROP TABLE IF EXISTS `store_program_change_log`;

CREATE TABLE `store_program_change_log` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `StoreId` int(11) NOT NULL,
  `NetworkStatus` enum('YES','NO') NOT NULL,
  `From` varchar(255) DEFAULT NULL,
  `To` varchar(255) DEFAULT NULL,
  `UpdateTime` datetime NOT NULL,
  `RelatedProgram` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `store_program_history` */

DROP TABLE IF EXISTS `store_program_history`;

CREATE TABLE `store_program_history` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `storeid` int(9) NOT NULL DEFAULT '0',
  `programid` int(9) unsigned NOT NULL DEFAULT '0',
  `startdate` date DEFAULT NULL,
  `enddate` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `store_publisher_ctrl` */

DROP TABLE IF EXISTS `store_publisher_ctrl`;

CREATE TABLE `store_publisher_ctrl` (
  `StoreId` int(11) NOT NULL,
  `AllowCouponSite` enum('YES','NO') NOT NULL DEFAULT 'YES',
  `AddTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`StoreId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `store_publisher_stats` */

DROP TABLE IF EXISTS `store_publisher_stats`;

CREATE TABLE `store_publisher_stats` (
  `StoreId` int(11) NOT NULL,
  `PublisherId` int(11) NOT NULL,
  `Epc` varchar(20) DEFAULT NULL,
  `Sales3D` int(11) NOT NULL DEFAULT '0',
  `Sales7D` int(11) NOT NULL DEFAULT '0',
  `Sales1M` int(11) NOT NULL DEFAULT '0',
  `Sales3M` int(11) NOT NULL DEFAULT '0',
  `Sales1Y` int(11) NOT NULL DEFAULT '0',
  `Orders3D` int(11) NOT NULL DEFAULT '0',
  `Orders7D` int(11) NOT NULL DEFAULT '0',
  `Orders1M` int(11) NOT NULL DEFAULT '0',
  `Orders3M` int(11) NOT NULL DEFAULT '0',
  `Orders1Y` int(11) NOT NULL DEFAULT '0',
  `Revenue3D` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `Revenue7D` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `Revenue1M` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `Revenue3M` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `Revenue1Y` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `Clicks3D` int(11) NOT NULL DEFAULT '0',
  `Clicks7D` int(11) NOT NULL DEFAULT '0',
  `Clicks1M` int(11) NOT NULL DEFAULT '0',
  `Clicks3M` int(11) NOT NULL DEFAULT '0',
  `Clicks1Y` int(11) NOT NULL DEFAULT '0',
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sitetype` enum('content','coupon') DEFAULT NULL,
  PRIMARY KEY (`StoreId`,`PublisherId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `store_recommend_by_am` */

DROP TABLE IF EXISTS `store_recommend_by_am`;

CREATE TABLE `store_recommend_by_am` (
  `country` char(3) NOT NULL,
  `storeid` int(9) NOT NULL,
  `am` varchar(32) DEFAULT NULL,
  `createtime` datetime DEFAULT NULL,
  PRIMARY KEY (`country`,`storeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `store_stats` */

DROP TABLE IF EXISTS `store_stats`;

CREATE TABLE `store_stats` (
  `StoreId` int(11) NOT NULL,
  `Epc` varchar(20) DEFAULT NULL,
  `Sales3D` int(11) NOT NULL DEFAULT '0',
  `Sales7D` int(11) NOT NULL DEFAULT '0',
  `Sales1M` int(11) NOT NULL DEFAULT '0',
  `Sales3M` int(11) NOT NULL DEFAULT '0',
  `Sales1Y` int(11) NOT NULL DEFAULT '0',
  `Orders3D` int(11) NOT NULL DEFAULT '0',
  `Orders7D` int(11) NOT NULL DEFAULT '0',
  `Orders1M` int(11) NOT NULL DEFAULT '0',
  `Orders3M` int(11) NOT NULL DEFAULT '0',
  `Orders1Y` int(11) NOT NULL DEFAULT '0',
  `Revenue3D` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `Revenue7D` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `Revenue1M` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `Revenue3M` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `Revenue1Y` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `Clicks3D` int(11) NOT NULL DEFAULT '0',
  `Clicks7D` int(11) NOT NULL DEFAULT '0',
  `Clicks1M` int(11) NOT NULL DEFAULT '0',
  `Clicks3M` int(11) NOT NULL DEFAULT '0',
  `Clicks1Y` int(11) NOT NULL DEFAULT '0',
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`StoreId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `store_whitelisting` */

DROP TABLE IF EXISTS `store_whitelisting`;

CREATE TABLE `store_whitelisting` (
  `StoreId` int(11) NOT NULL,
  `PublisherId` int(11) NOT NULL,
  `AddTime` datetime DEFAULT NULL,
  `Status` enum('Active','Inactive','Pending') NOT NULL DEFAULT 'Active',
  `LastUpdateTime` datetime DEFAULT NULL,
  PRIMARY KEY (`StoreId`,`PublisherId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `t_assignment` */

DROP TABLE IF EXISTS `t_assignment`;

CREATE TABLE `t_assignment` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Assignment` varchar(255) NOT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  `AddTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `t_assignment_config` */

DROP TABLE IF EXISTS `t_assignment_config`;

CREATE TABLE `t_assignment_config` (
  `AssignmentId` int(11) NOT NULL,
  `EditorId` int(11) NOT NULL,
  `Percent` smallint(3) NOT NULL DEFAULT '0',
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  `AddTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`AssignmentId`,`EditorId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `t_check_p_d_links` */

DROP TABLE IF EXISTS `t_check_p_d_links`;

CREATE TABLE `t_check_p_d_links` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ProgramId` int(11) NOT NULL DEFAULT '0',
  `DomainId` int(11) NOT NULL DEFAULT '0',
  `Status` enum('New','Done','Ignored','Assigned') NOT NULL DEFAULT 'New',
  `AddTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ErrorType` smallint(2) NOT NULL DEFAULT '0',
  `ErrorValue` varchar(255) DEFAULT NULL,
  `Remark` varchar(255) DEFAULT NULL,
  `Editor` varchar(255) DEFAULT NULL,
  `Rank` int(11) NOT NULL DEFAULT '0',
  `CheckUrl` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `idx_status` (`Status`,`Rank`)
) ENGINE=MyISAM AUTO_INCREMENT=181905 DEFAULT CHARSET=utf8;

/*Table structure for table `t_domain_issue` */

DROP TABLE IF EXISTS `t_domain_issue`;

CREATE TABLE `t_domain_issue` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Type` enum('TopNoAff','TopHasAff') DEFAULT NULL,
  `DomainId` int(11) DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Status` enum('Ignored','New','Done','Assigned') NOT NULL DEFAULT 'New',
  `Editor` varchar(50) DEFAULT NULL,
  `Click` int(11) NOT NULL DEFAULT '0',
  `Remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `idx_did` (`DomainId`),
  KEY `idx_cli` (`Click`)
) ENGINE=MyISAM AUTO_INCREMENT=4501 DEFAULT CHARSET=utf8;

/*Table structure for table `t_editor` */

DROP TABLE IF EXISTS `t_editor`;

CREATE TABLE `t_editor` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `EditorName` varchar(255) NOT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  `AddTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `t_error_config` */

DROP TABLE IF EXISTS `t_error_config`;

CREATE TABLE `t_error_config` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  `AddTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `table_change_log_batch` */

DROP TABLE IF EXISTS `table_change_log_batch`;

CREATE TABLE `table_change_log_batch` (
  `BatchId` int(11) NOT NULL AUTO_INCREMENT,
  `BatchTableName` varchar(100) DEFAULT NULL,
  `BatchOperator` varchar(100) DEFAULT NULL,
  `BatchCreationTime` datetime DEFAULT NULL,
  `BatchComments` varchar(255) DEFAULT NULL,
  `BatchPrimaryKeyValue` int(11) DEFAULT NULL,
  `BatchAction` enum('ADD','EDIT','DELETE') DEFAULT NULL,
  PRIMARY KEY (`BatchId`),
  KEY `idx_table_primary` (`BatchTableName`,`BatchPrimaryKeyValue`),
  KEY `idx_table_editor` (`BatchTableName`,`BatchOperator`,`BatchCreationTime`),
  KEY `idx_editor` (`BatchOperator`,`BatchCreationTime`),
  KEY `idx_time` (`BatchCreationTime`)
) ENGINE=MyISAM AUTO_INCREMENT=739 DEFAULT CHARSET=latin1;

/*Table structure for table `table_change_log_detail` */

DROP TABLE IF EXISTS `table_change_log_detail`;

CREATE TABLE `table_change_log_detail` (
  `DetailId` int(11) NOT NULL AUTO_INCREMENT,
  `BatchId` int(11) DEFAULT NULL,
  `FiledName` varchar(100) DEFAULT NULL,
  `FiledValueFrom` text,
  `FiledValueTo` text,
  PRIMARY KEY (`DetailId`),
  KEY `idx_batch_id` (`BatchId`)
) ENGINE=MyISAM AUTO_INCREMENT=2659 DEFAULT CHARSET=latin1;

/*Table structure for table `task_non_aff_domain` */

DROP TABLE IF EXISTS `task_non_aff_domain`;

CREATE TABLE `task_non_aff_domain` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DomainId` int(11) DEFAULT NULL,
  `Clicks3d` int(11) DEFAULT NULL,
  `Clicks7d` int(11) DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Status` enum('New','Positive','Negative') NOT NULL DEFAULT 'New',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=9883 DEFAULT CHARSET=utf8;

/*Table structure for table `temp_partership` */

DROP TABLE IF EXISTS `temp_partership`;

CREATE TABLE `temp_partership` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `ProgramID` int(11) NOT NULL DEFAULT '0',
  `Name` varchar(255) NOT NULL,
  `AffName` varchar(255) NOT NULL,
  `Homepage` varchar(255) NOT NULL,
  `AddTime` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=29988 DEFAULT CHARSET=latin1;

/*Table structure for table `temp_store_off` */

DROP TABLE IF EXISTS `temp_store_off`;

CREATE TABLE `temp_store_off` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Storename` varchar(255) NOT NULL,
  `ProgramName` varchar(255) NOT NULL,
  `Homepage` varchar(255) NOT NULL,
  `Affid` int(10) NOT NULL,
  `programId` int(11) NOT NULL,
  `StatusInAff` varchar(255) NOT NULL,
  `Partnership` varchar(255) NOT NULL,
  `OffTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `MailTo` varchar(255) NOT NULL,
  `StoreId` int(11) NOT NULL,
  `AddTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=4692 DEFAULT CHARSET=latin1;

/*Table structure for table `tmp_domain_stats_feb` */

DROP TABLE IF EXISTS `tmp_domain_stats_feb`;

CREATE TABLE `tmp_domain_stats_feb` (
  `DomainId` int(11) NOT NULL,
  `Domain` varchar(255) DEFAULT NULL,
  `Traffic` int(11) DEFAULT NULL,
  `Revenue` decimal(10,2) DEFAULT NULL,
  `Country` varchar(255) DEFAULT NULL,
  `HasAff` enum('1','0') DEFAULT NULL,
  PRIMARY KEY (`DomainId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `tmp_p_ppc` */

DROP TABLE IF EXISTS `tmp_p_ppc`;

CREATE TABLE `tmp_p_ppc` (
  `programid` int(11) NOT NULL,
  `ppc` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`programid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `union_info` */

DROP TABLE IF EXISTS `union_info`;

CREATE TABLE `union_info` (
  `union_id` int(11) NOT NULL,
  `union_name` varchar(255) NOT NULL,
  `origin` varchar(255) NOT NULL DEFAULT 'manual',
  PRIMARY KEY (`union_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `wf_aff` */

DROP TABLE IF EXISTS `wf_aff`;

CREATE TABLE `wf_aff` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL DEFAULT '',
  `ShortName` varchar(50) NOT NULL,
  `Alias` varchar(50) NOT NULL DEFAULT '',
  `Domain` varchar(50) NOT NULL,
  `BlogUrl` text,
  `FacebookUrl` text,
  `TwitterUrl` text,
  `GetProgramIDInNetworkUrl` text,
  `AffiliateUrlKeywords` text,
  `AffiliateUrlKeywords2` text,
  `SubTracking` varchar(255) DEFAULT NULL,
  `SubTracking2` varchar(255) DEFAULT NULL,
  `IsInHouse` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `IsActive` enum('YES','NO') DEFAULT NULL,
  `DeepUrlParaName` varchar(255) DEFAULT NULL,
  `RevenueAccount` int(11) DEFAULT '0',
  `RevenueReceived` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `RevenueCycle` text,
  `RevenueRemark` text,
  `ProgramCrawled` enum('YES','NO','No Need to Crawl','Request to Crawl','Can Not Crawl') DEFAULT 'NO',
  `ProgramCrawlRemark` text,
  `StatsReportCrawled` enum('YES','NO','No Need to Crawl','Request to Crawl','Can Not Crawl') DEFAULT 'NO',
  `StatsReportCrawlRemark` text,
  `StatsAffiliateName` varchar(32) DEFAULT NULL,
  `ImportanceRank` int(11) DEFAULT '99999999',
  `ProgramUrlTemplate` varchar(1024) DEFAULT NULL,
  `Country` varchar(255) DEFAULT NULL,
  `LoginUrl` varchar(500) DEFAULT NULL,
  `SupportDeepUrl` enum('YES','NO') DEFAULT 'NO',
  `SupportSubTracking` enum('YES','NO') DEFAULT 'NO',
  `JoinDate` datetime DEFAULT NULL,
  `Comment` text,
  `Account` varchar(64) DEFAULT NULL,
  `Password` varchar(64) DEFAULT NULL,
  `CurrentName` varchar(255) NOT NULL DEFAULT '',
  `MarketingContinent` varchar(10) DEFAULT NULL,
  `MarketingCountry` varchar(10) DEFAULT NULL,
  `IsCheckTimeZone` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `TimeZoneName` varchar(255) DEFAULT NULL,
  `TimeZoneDiff` int(10) DEFAULT NULL,
  `Manager` enum('Alain','Giulia','Lillian','Monica','Nicolas','Sarah','Senait','Vivienne') CHARACTER SET utf8 DEFAULT NULL,
  `Level` enum('TIER1','TIER2') NOT NULL DEFAULT 'TIER2',
  `TransactionStatus` varchar(255) NOT NULL,
  `TransactionApiKey` varchar(255) NOT NULL,
  `TransactionDefaultCurrency` varchar(100) NOT NULL,
  `TransactionCrawlStatus` enum('YES','NO') NOT NULL DEFAULT 'YES',
  PRIMARY KEY (`ID`),
  KEY `Name` (`Name`),
  KEY `IsInHouse` (`IsInHouse`),
  KEY `IsActive` (`IsActive`)
) ENGINE=MyISAM AUTO_INCREMENT=20002 DEFAULT CHARSET=latin1;

/*Table structure for table `wf_aff_account` */

DROP TABLE IF EXISTS `wf_aff_account`;

CREATE TABLE `wf_aff_account` (
  `AffAlias` varchar(50) NOT NULL,
  `AffName` varchar(128) DEFAULT NULL,
  `AffUser` varchar(128) DEFAULT NULL,
  `AffPass` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`AffAlias`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `white_list_account` */

DROP TABLE IF EXISTS `white_list_account`;

CREATE TABLE `white_list_account` (
  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `UserName` varchar(64) NOT NULL DEFAULT '' COMMENT '用户名',
  `Name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `UserPass` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
  `Status` enum('Active','Inactive','Delete') NOT NULL DEFAULT 'Active',
  `Remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `StoreIds` varchar(255) NOT NULL DEFAULT '0',
  `AddTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后一次更新时间',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uni_username` (`UserName`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `white_list_store` */

DROP TABLE IF EXISTS `white_list_store`;

CREATE TABLE `white_list_store` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `WhiteAccountId` int(11) NOT NULL DEFAULT '0' COMMENT '账号id',
  `StoreId` int(11) NOT NULL DEFAULT '0' COMMENT '商家id',
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Active' COMMENT '状态（激活 未激活）',
  `DefaultStoreId` tinyint(3) NOT NULL DEFAULT '0' COMMENT '默认的store',
  `CreatedTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `CreatedBy` varchar(100) NOT NULL DEFAULT '' COMMENT '创建人',
  `LastUpdateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*Table structure for table `z_delete_domain_fake` */

DROP TABLE IF EXISTS `z_delete_domain_fake`;

CREATE TABLE `z_delete_domain_fake` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Key` varchar(50) NOT NULL,
  `AddDate` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_key` (`Key`)
) ENGINE=MyISAM AUTO_INCREMENT=106504 DEFAULT CHARSET=latin1;

/*Table structure for table `z_delete_r_domain_fake` */

DROP TABLE IF EXISTS `z_delete_r_domain_fake`;

CREATE TABLE `z_delete_r_domain_fake` (
  `FakeId` int(11) NOT NULL,
  `DomainId` int(11) NOT NULL,
  PRIMARY KEY (`FakeId`,`DomainId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
