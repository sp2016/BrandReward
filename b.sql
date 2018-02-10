/*
SQLyog Ultimate v11.11 (64 bit)
MySQL - 5.7.18-log : Database - pendinglinks
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`pendinglinks` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `pendinglinks`;

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
) ENGINE=MyISAM AUTO_INCREMENT=2032 DEFAULT CHARSET=latin1;

/*Table structure for table `aff_msg_select_show` */

DROP TABLE IF EXISTS `aff_msg_select_show`;

CREATE TABLE `aff_msg_select_show` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `AffId` int(11) NOT NULL,
  `CrawlMethod` enum('getprogram','getlink','getfeed','getmessage','getproduct','gettransaction') NOT NULL,
  `AffField` varchar(255) NOT NULL,
  `BrField` varchar(255) DEFAULT NULL,
  `DataSourceType` enum('API','Page') NOT NULL DEFAULT 'Page',
  `AddTime` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `aff_field` (`AffId`,`AffField`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `affiliate` */

DROP TABLE IF EXISTS `affiliate`;

CREATE TABLE `affiliate` (
  `AffId` int(11) NOT NULL AUTO_INCREMENT,
  `AffName` varchar(255) NOT NULL,
  `AffStatus` enum('On','Off') NOT NULL,
  `AffLoginUrl` varchar(255) DEFAULT NULL,
  `AffLoginPostString` text,
  `AffLoginVerifyString` varchar(255) DEFAULT NULL,
  `AffLoginMethod` varchar(255) DEFAULT NULL,
  `AffLoginSuccUrl` varchar(255) DEFAULT NULL,
  `AffParameters` text,
  `AffFeedEncoding` varchar(20) DEFAULT NULL,
  `AffLinkEncoding` varchar(20) DEFAULT NULL,
  `AffMerchantEncoding` varchar(20) DEFAULT NULL,
  `AffLastUpdateLinkTime` datetime DEFAULT NULL,
  `AffLinkCount` int(11) DEFAULT '0',
  `AffLastUpdateFeedTime` datetime DEFAULT NULL,
  `AffFeedCount` int(11) DEFAULT '0',
  PRIMARY KEY (`AffId`),
  UNIQUE KEY `Name` (`AffName`)
) ENGINE=MyISAM AUTO_INCREMENT=10003 DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_email_promo` */

DROP TABLE IF EXISTS `affiliate_email_promo`;

CREATE TABLE `affiliate_email_promo` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Country` varchar(200) NOT NULL,
  `CouponID` int(11) NOT NULL,
  `Merchant` varchar(200) NOT NULL,
  `Merchant_Originanl_Url` varchar(200) NOT NULL,
  `Title` varchar(200) NOT NULL,
  `Description` text NOT NULL,
  `StartTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ExpireTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `AffUrl` varchar(200) NOT NULL,
  `DestUrl` varchar(200) NOT NULL,
  `Code` varchar(200) NOT NULL,
  `AddTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastUpdateTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=4304 DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_1` */

DROP TABLE IF EXISTS `affiliate_links_1`;

CREATE TABLE `affiliate_links_1` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Type` enum('promotion','link') DEFAULT NULL,
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_10` */

DROP TABLE IF EXISTS `affiliate_links_10`;

CREATE TABLE `affiliate_links_10` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_115` */

DROP TABLE IF EXISTS `affiliate_links_115`;

CREATE TABLE `affiliate_links_115` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Type` enum('promotion','link') DEFAULT NULL,
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_12` */

DROP TABLE IF EXISTS `affiliate_links_12`;

CREATE TABLE `affiliate_links_12` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_124` */

DROP TABLE IF EXISTS `affiliate_links_124`;

CREATE TABLE `affiliate_links_124` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_13` */

DROP TABLE IF EXISTS `affiliate_links_13`;

CREATE TABLE `affiliate_links_13` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_15` */

DROP TABLE IF EXISTS `affiliate_links_15`;

CREATE TABLE `affiliate_links_15` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Type` enum('promotion','link') DEFAULT NULL,
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_152` */

DROP TABLE IF EXISTS `affiliate_links_152`;

CREATE TABLE `affiliate_links_152` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_160` */

DROP TABLE IF EXISTS `affiliate_links_160`;

CREATE TABLE `affiliate_links_160` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_163` */

DROP TABLE IF EXISTS `affiliate_links_163`;

CREATE TABLE `affiliate_links_163` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_177` */

DROP TABLE IF EXISTS `affiliate_links_177`;

CREATE TABLE `affiliate_links_177` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_188` */

DROP TABLE IF EXISTS `affiliate_links_188`;

CREATE TABLE `affiliate_links_188` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_196` */

DROP TABLE IF EXISTS `affiliate_links_196`;

CREATE TABLE `affiliate_links_196` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_197` */

DROP TABLE IF EXISTS `affiliate_links_197`;

CREATE TABLE `affiliate_links_197` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_2` */

DROP TABLE IF EXISTS `affiliate_links_2`;

CREATE TABLE `affiliate_links_2` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Type` enum('promotion','link') DEFAULT NULL,
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_20` */

DROP TABLE IF EXISTS `affiliate_links_20`;

CREATE TABLE `affiliate_links_20` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_2001` */

DROP TABLE IF EXISTS `affiliate_links_2001`;

CREATE TABLE `affiliate_links_2001` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_2021` */

DROP TABLE IF EXISTS `affiliate_links_2021`;

CREATE TABLE `affiliate_links_2021` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_2024` */

DROP TABLE IF EXISTS `affiliate_links_2024`;

CREATE TABLE `affiliate_links_2024` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_2026` */

DROP TABLE IF EXISTS `affiliate_links_2026`;

CREATE TABLE `affiliate_links_2026` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_2027` */

DROP TABLE IF EXISTS `affiliate_links_2027`;

CREATE TABLE `affiliate_links_2027` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_2028` */

DROP TABLE IF EXISTS `affiliate_links_2028`;

CREATE TABLE `affiliate_links_2028` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_2029` */

DROP TABLE IF EXISTS `affiliate_links_2029`;

CREATE TABLE `affiliate_links_2029` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_2031` */

DROP TABLE IF EXISTS `affiliate_links_2031`;

CREATE TABLE `affiliate_links_2031` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_2047` */

DROP TABLE IF EXISTS `affiliate_links_2047`;

CREATE TABLE `affiliate_links_2047` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_22` */

DROP TABLE IF EXISTS `affiliate_links_22`;

CREATE TABLE `affiliate_links_22` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Type` enum('promotion','link') DEFAULT NULL,
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_240` */

DROP TABLE IF EXISTS `affiliate_links_240`;

CREATE TABLE `affiliate_links_240` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_26` */

DROP TABLE IF EXISTS `affiliate_links_26`;

CREATE TABLE `affiliate_links_26` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_28` */

DROP TABLE IF EXISTS `affiliate_links_28`;

CREATE TABLE `affiliate_links_28` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_29` */

DROP TABLE IF EXISTS `affiliate_links_29`;

CREATE TABLE `affiliate_links_29` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_3` */

DROP TABLE IF EXISTS `affiliate_links_3`;

CREATE TABLE `affiliate_links_3` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Type` enum('promotion','link') DEFAULT NULL,
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_35` */

DROP TABLE IF EXISTS `affiliate_links_35`;

CREATE TABLE `affiliate_links_35` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_360` */

DROP TABLE IF EXISTS `affiliate_links_360`;

CREATE TABLE `affiliate_links_360` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_4` */

DROP TABLE IF EXISTS `affiliate_links_4`;

CREATE TABLE `affiliate_links_4` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Type` enum('promotion','link') DEFAULT NULL,
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_415` */

DROP TABLE IF EXISTS `affiliate_links_415`;

CREATE TABLE `affiliate_links_415` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_418` */

DROP TABLE IF EXISTS `affiliate_links_418`;

CREATE TABLE `affiliate_links_418` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_425` */

DROP TABLE IF EXISTS `affiliate_links_425`;

CREATE TABLE `affiliate_links_425` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_426` */

DROP TABLE IF EXISTS `affiliate_links_426`;

CREATE TABLE `affiliate_links_426` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_427` */

DROP TABLE IF EXISTS `affiliate_links_427`;

CREATE TABLE `affiliate_links_427` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_429` */

DROP TABLE IF EXISTS `affiliate_links_429`;

CREATE TABLE `affiliate_links_429` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_430` */

DROP TABLE IF EXISTS `affiliate_links_430`;

CREATE TABLE `affiliate_links_430` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_46` */

DROP TABLE IF EXISTS `affiliate_links_46`;

CREATE TABLE `affiliate_links_46` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_469` */

DROP TABLE IF EXISTS `affiliate_links_469`;

CREATE TABLE `affiliate_links_469` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_49` */

DROP TABLE IF EXISTS `affiliate_links_49`;

CREATE TABLE `affiliate_links_49` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_491` */

DROP TABLE IF EXISTS `affiliate_links_491`;

CREATE TABLE `affiliate_links_491` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_5` */

DROP TABLE IF EXISTS `affiliate_links_5`;

CREATE TABLE `affiliate_links_5` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Type` enum('promotion','link') DEFAULT NULL,
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_50` */

DROP TABLE IF EXISTS `affiliate_links_50`;

CREATE TABLE `affiliate_links_50` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_500` */

DROP TABLE IF EXISTS `affiliate_links_500`;

CREATE TABLE `affiliate_links_500` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_503` */

DROP TABLE IF EXISTS `affiliate_links_503`;

CREATE TABLE `affiliate_links_503` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_52` */

DROP TABLE IF EXISTS `affiliate_links_52`;

CREATE TABLE `affiliate_links_52` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_533` */

DROP TABLE IF EXISTS `affiliate_links_533`;

CREATE TABLE `affiliate_links_533` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_548` */

DROP TABLE IF EXISTS `affiliate_links_548`;

CREATE TABLE `affiliate_links_548` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_557` */

DROP TABLE IF EXISTS `affiliate_links_557`;

CREATE TABLE `affiliate_links_557` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_574` */

DROP TABLE IF EXISTS `affiliate_links_574`;

CREATE TABLE `affiliate_links_574` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_58` */

DROP TABLE IF EXISTS `affiliate_links_58`;

CREATE TABLE `affiliate_links_58` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Type` enum('promotion','link') DEFAULT NULL,
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_6` */

DROP TABLE IF EXISTS `affiliate_links_6`;

CREATE TABLE `affiliate_links_6` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Type` enum('promotion','link') DEFAULT NULL,
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_63` */

DROP TABLE IF EXISTS `affiliate_links_63`;

CREATE TABLE `affiliate_links_63` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_64` */

DROP TABLE IF EXISTS `affiliate_links_64`;

CREATE TABLE `affiliate_links_64` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_65` */

DROP TABLE IF EXISTS `affiliate_links_65`;

CREATE TABLE `affiliate_links_65` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_667` */

DROP TABLE IF EXISTS `affiliate_links_667`;

CREATE TABLE `affiliate_links_667` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_679` */

DROP TABLE IF EXISTS `affiliate_links_679`;

CREATE TABLE `affiliate_links_679` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_7` */

DROP TABLE IF EXISTS `affiliate_links_7`;

CREATE TABLE `affiliate_links_7` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Type` enum('promotion','link') DEFAULT NULL,
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_811` */

DROP TABLE IF EXISTS `affiliate_links_811`;

CREATE TABLE `affiliate_links_811` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_818` */

DROP TABLE IF EXISTS `affiliate_links_818`;

CREATE TABLE `affiliate_links_818` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_links_default` */

DROP TABLE IF EXISTS `affiliate_links_default`;

CREATE TABLE `affiliate_links_default` (
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL DEFAULT '',
  `DataSource` int(11) NOT NULL DEFAULT '0',
  `LinkCode` varchar(255) NOT NULL DEFAULT '',
  `LinkName` varchar(255) NOT NULL DEFAULT '',
  `LinkDesc` text,
  `LinkStartDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkEndDate` datetime DEFAULT '0000-00-00 00:00:00',
  `LinkPromoType` enum('coupon','free shipping','N/A','PRODUCT','DEAL','link','deeplink') DEFAULT 'coupon',
  `LinkHtmlCode` text,
  `LinkOriginalUrl` text,
  `LinkAffUrl` text,
  `LinkImageUrl` text,
  `LastUpdateTime` datetime DEFAULT NULL,
  `Country` varchar(20) NOT NULL DEFAULT '',
  `LinkAddTime` datetime DEFAULT '0000-00-00 00:00:00',
  `Domain` varchar(255) NOT NULL DEFAULT '',
  `HttpCode` int(4) NOT NULL DEFAULT '0',
  `FinalUrl` text,
  `LastCheckTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsDeepLink` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Type` enum('promotion','link') DEFAULT NULL,
  `SupportDeepUrlTpl` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `Language` varchar(50) NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  UNIQUE KEY `aff_id` (`AffMerchantId`,`AffLinkId`),
  KEY `idx_updatetime` (`LastUpdateTime`,`AffMerchantId`),
  KEY `idx_addtime` (`LinkAddTime`),
  KEY `idx_checktime` (`LastCheckTime`,`HttpCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_merchant` */

DROP TABLE IF EXISTS `affiliate_merchant`;

CREATE TABLE `affiliate_merchant` (
  `ProgramId` int(11) NOT NULL AUTO_INCREMENT,
  `AffId` int(11) NOT NULL,
  `AffMerchantId` varchar(255) NOT NULL,
  `MerchantName` varchar(255) NOT NULL,
  `MerchantEPC` varchar(255) DEFAULT NULL,
  `MerchantEPC30d` varchar(255) DEFAULT NULL,
  `MerchantStatus` enum('not apply','pending','approval','declined','expired','siteclosed') DEFAULT 'not apply',
  `MerchantRemark` varchar(255) DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `LastUpdateLinkTime` datetime DEFAULT NULL,
  `MerchantLinkCount` int(11) DEFAULT '0',
  `MerchantFeedCount` int(11) DEFAULT '0',
  `LastUpdateFeedTime` datetime DEFAULT NULL,
  `MerchantCountry` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`ProgramId`),
  UNIQUE KEY `AffId` (`AffId`,`AffMerchantId`)
) ENGINE=MyISAM AUTO_INCREMENT=45164 DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_merchant_status_log` */

DROP TABLE IF EXISTS `affiliate_merchant_status_log`;

CREATE TABLE `affiliate_merchant_status_log` (
  `LogId` int(11) NOT NULL AUTO_INCREMENT,
  `AffId` int(11) NOT NULL,
  `AffMerchantId` varchar(255) NOT NULL,
  `FromStatus` varchar(255) DEFAULT NULL,
  `ToStatus` varchar(255) DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  PRIMARY KEY (`LogId`),
  KEY `NewIndex1` (`AffId`,`AffMerchantId`)
) ENGINE=MyISAM AUTO_INCREMENT=254270 DEFAULT CHARSET=latin1;

/*Table structure for table `affiliate_message` */

DROP TABLE IF EXISTS `affiliate_message`;

CREATE TABLE `affiliate_message` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `affid` bigint(20) NOT NULL COMMENT 'id in table affiliate',
  `messageid` varchar(128) NOT NULL COMMENT 'message id in aff or created by the spider to make sure the message is unique in an affiliate.',
  `title` varchar(1024) NOT NULL COMMENT 'message title',
  `sender` varchar(1024) DEFAULT NULL COMMENT 'message sender (if exist)',
  `content` text COMMENT 'content of the message',
  `created` datetime DEFAULT NULL COMMENT 'time when the message created in the aff',
  `logtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when this record created',
  `status` enum('NEW','DONE') NOT NULL DEFAULT 'NEW' COMMENT 'status of a message',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_message` (`affid`,`messageid`)
) ENGINE=MyISAM AUTO_INCREMENT=25528 DEFAULT CHARSET=utf8;

/*Table structure for table `affiliate_product_13` */

DROP TABLE IF EXISTS `affiliate_product_13`;

CREATE TABLE `affiliate_product_13` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `AffId` int(11) NOT NULL,
  `AffMerchantId` varchar(255) NOT NULL,
  `AffProductId` varchar(255) NOT NULL,
  `ProductName` varchar(255) NOT NULL,
  `ProductCurrency` varchar(255) DEFAULT NULL,
  `ProductPrice` decimal(16,4) DEFAULT '0.0000',
  `ProductOriginalPrice` decimal(16,4) DEFAULT '0.0000',
  `ProductRetailPrice` decimal(16,4) DEFAULT '0.0000',
  `ProductImage` varchar(255) NOT NULL,
  `ProductLocalImage` varchar(255) DEFAULT NULL,
  `ProductUrl` text NOT NULL,
  `ProductDestUrl` text NOT NULL,
  `ProductDesc` text,
  `CommissionExt` text NOT NULL,
  `Language` varchar(255) NOT NULL,
  `ProductStartDate` datetime DEFAULT NULL,
  `ProductEndDate` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `AddTime` datetime NOT NULL,
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `AffId` (`AffId`,`AffMerchantId`,`AffProductId`)
) ENGINE=MyISAM AUTO_INCREMENT=2482 DEFAULT CHARSET=latin1 COMMENT='PRODUCT';

/*Table structure for table `affiliate_product_163` */

DROP TABLE IF EXISTS `affiliate_product_163`;

CREATE TABLE `affiliate_product_163` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `AffId` int(11) NOT NULL,
  `AffMerchantId` varchar(255) NOT NULL,
  `AffProductId` varchar(255) NOT NULL,
  `ProductName` varchar(255) NOT NULL,
  `ProductCurrency` varchar(255) DEFAULT NULL,
  `ProductPrice` decimal(16,4) DEFAULT '0.0000',
  `ProductOriginalPrice` decimal(16,4) DEFAULT '0.0000',
  `ProductRetailPrice` decimal(16,4) DEFAULT '0.0000',
  `ProductImage` varchar(255) NOT NULL,
  `ProductLocalImage` varchar(255) DEFAULT NULL,
  `ProductUrl` text NOT NULL,
  `ProductDestUrl` text NOT NULL,
  `ProductDesc` text,
  `ProductStartDate` datetime DEFAULT NULL,
  `ProductEndDate` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `AddTime` datetime NOT NULL,
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `AffId` (`AffId`,`AffMerchantId`,`AffProductId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='PRODUCT';

/*Table structure for table `affiliate_product_2021` */

DROP TABLE IF EXISTS `affiliate_product_2021`;

CREATE TABLE `affiliate_product_2021` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `AffId` int(11) NOT NULL,
  `AffMerchantId` varchar(255) NOT NULL,
  `AffProductId` varchar(255) NOT NULL,
  `ProductName` varchar(255) NOT NULL,
  `ProductCurrency` varchar(255) DEFAULT NULL,
  `ProductPrice` decimal(16,4) DEFAULT '0.0000',
  `ProductOriginalPrice` decimal(16,4) DEFAULT '0.0000',
  `ProductRetailPrice` decimal(16,4) DEFAULT '0.0000',
  `ProductImage` varchar(255) NOT NULL,
  `ProductLocalImage` varchar(255) DEFAULT NULL,
  `ProductUrl` text NOT NULL,
  `ProductDestUrl` text NOT NULL,
  `ProductDesc` text,
  `ProductStartDate` datetime DEFAULT NULL,
  `ProductEndDate` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `AddTime` datetime NOT NULL,
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `AffId` (`AffId`,`AffMerchantId`,`AffProductId`)
) ENGINE=MyISAM AUTO_INCREMENT=1130 DEFAULT CHARSET=latin1 COMMENT='PRODUCT';

/*Table structure for table `affiliate_product_default` */

DROP TABLE IF EXISTS `affiliate_product_default`;

CREATE TABLE `affiliate_product_default` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `AffId` int(11) NOT NULL,
  `AffMerchantId` varchar(255) NOT NULL,
  `AffProductId` varchar(255) NOT NULL,
  `ProductName` varchar(255) NOT NULL,
  `ProductCurrency` varchar(255) DEFAULT NULL,
  `ProductPrice` decimal(16,4) DEFAULT '0.0000',
  `ProductOriginalPrice` decimal(16,4) DEFAULT '0.0000',
  `ProductRetailPrice` decimal(16,4) DEFAULT '0.0000',
  `ProductImage` varchar(255) NOT NULL,
  `ProductLocalImage` varchar(255) DEFAULT NULL,
  `ProductUrl` text NOT NULL,
  `ProductDestUrl` text NOT NULL,
  `ProductDesc` text,
  `CommissionExt` text NOT NULL,
  `Language` varchar(255) NOT NULL,
  `ProductStartDate` datetime DEFAULT NULL,
  `ProductEndDate` datetime DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `AddTime` datetime NOT NULL,
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `AffId` (`AffId`,`AffMerchantId`,`AffProductId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='PRODUCT';

/*Table structure for table `country_codes` */

DROP TABLE IF EXISTS `country_codes`;

CREATE TABLE `country_codes` (
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
) ENGINE=MyISAM AUTO_INCREMENT=3536 DEFAULT CHARSET=latin1;

/*Table structure for table `job` */

DROP TABLE IF EXISTS `job`;

CREATE TABLE `job` (
  `JobId` int(11) NOT NULL AUTO_INCREMENT,
  `JobName` varchar(200) NOT NULL,
  `JobAddTime` datetime NOT NULL,
  `AffId` int(11) unsigned DEFAULT NULL,
  `MerchantId` varchar(255) DEFAULT NULL,
  `SiteId` int(11) DEFAULT NULL,
  `AffectedCount` int(11) unsigned DEFAULT NULL,
  `UpdatedCount` int(11) unsigned DEFAULT NULL,
  `Detail` text,
  `JobEndTime` datetime DEFAULT NULL,
  `ParentJobId` int(11) DEFAULT '0',
  UNIQUE KEY `JobId` (`JobId`),
  KEY `NewIndex1` (`JobName`),
  KEY `NewIndex2` (`JobAddTime`),
  KEY `NewIndex3` (`AffId`,`MerchantId`)
) ENGINE=MyISAM AUTO_INCREMENT=600565 DEFAULT CHARSET=latin1;

/*Table structure for table `program` */

DROP TABLE IF EXISTS `program`;

CREATE TABLE `program` (
  `ID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `AffId` int(11) NOT NULL,
  `TargetCountryExt` text,
  `TargetCountryInt` text,
  `CategoryExt` text,
  `Contacts` text,
  `IdInAff` varchar(255) NOT NULL,
  `RankInAff` int(11) DEFAULT NULL,
  `JoinDate` datetime DEFAULT NULL,
  `StatusInAff` enum('Active','TempOffline','Offline') DEFAULT NULL,
  `StatusInAffRemark` text,
  `Partnership` enum('NoPartnership','Active','Pending','Declined','Expired','Removed') DEFAULT NULL,
  `PartnershipChangeReason` text,
  `WeDeclined` enum('YES','NO','NoNeedToApply') NOT NULL DEFAULT 'NO',
  `CreateDate` datetime DEFAULT NULL,
  `DropDate` datetime DEFAULT NULL,
  `Description` text,
  `Homepage` varchar(255) DEFAULT NULL,
  `Remark` text,
  `Research` text,
  `CommissionExt` text,
  `LastCommissionExt` text,
  `BonusExt` text,
  `ContestExt` varchar(255) DEFAULT NULL,
  `EPCDefault` decimal(10,5) DEFAULT NULL,
  `EPC30d` decimal(10,5) DEFAULT NULL,
  `EPC90d` decimal(10,5) DEFAULT NULL,
  `CookieTime` int(11) DEFAULT NULL,
  `PaymentDays` int(11) DEFAULT '0',
  `HasPendingOffer` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `NumberOfOccurrences` varchar(50) DEFAULT NULL,
  `SEMPolicyExt` text,
  `SEMPolicyRemark` text,
  `TermAndCondition` text,
  `ProtectedSEMBiddingKeywords` text,
  `NonCompeteSEMBiddingKeywords` text,
  `RecommendedSEMBiddingKeywords` text,
  `ProhibitedSEMDisplayURLContent` text,
  `LimitedUseSEMDisplayURLContent` text,
  `ProhibitedSEMAdCopyContent` text,
  `LimitedUseSEMAdCopyContent` text,
  `AuthorizedSearchEngines` text,
  `SpecialInstructionsForSEM` text,
  `ProhibitedWebSiteURLAndContent` text,
  `UnacceptableWebSitesExt` text,
  `CouponCodesPolicyExt` text,
  `AllowedDirectLink` text,
  `SubAffPolicyExt` text,
  `Complaint` text,
  `CooperateWithCouponSite` enum('YES','NO') NOT NULL DEFAULT 'YES',
  `SecondIdInAff` varchar(255) DEFAULT NULL,
  `DetailPage` varchar(255) DEFAULT NULL,
  `LastUpdateTime` datetime DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `Creator` varchar(50) DEFAULT NULL,
  `MobileFriendly` enum('YES','NO','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `LastUpdateLinkTime` datetime DEFAULT NULL,
  `MerchantLinkCount` int(11) DEFAULT NULL,
  `MerchantFeedCount` int(11) DEFAULT NULL,
  `LastUpdateFeedTime` datetime DEFAULT NULL,
  `MerchantCountry` varchar(20) DEFAULT NULL,
  `SupportDeepUrl` enum('YES','NO','UNKNOWN') DEFAULT 'UNKNOWN',
  `AffDefaultUrl` varchar(255) DEFAULT NULL,
  `LogoUrl` varchar(255) DEFAULT 'UNKNOWN',
  `LogoName` varchar(255) DEFAULT NULL,
  `CommissionApd` text NOT NULL,
  `CategoryFirst` text NOT NULL,
  `CategorySecond` text NOT NULL,
  `AllowInaccuratePromo` enum('NO','YES') NOT NULL DEFAULT 'NO',
  `AllowNonaffCoupon` enum('NO','YES','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `AllowNonaffPromo` enum('NO','YES','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `PublisherPolicy` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `NewIndex1` (`AffId`,`IdInAff`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `program_int` */

DROP TABLE IF EXISTS `program_int`;

CREATE TABLE `program_int` (
  `ProgramId` int(11) NOT NULL,
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
  PRIMARY KEY (`ProgramId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `scheduled_job` */

DROP TABLE IF EXISTS `scheduled_job`;

CREATE TABLE `scheduled_job` (
  `SJobId` int(11) NOT NULL AUTO_INCREMENT,
  `SJobName` varchar(255) NOT NULL,
  `SJobStartTime` datetime NOT NULL,
  `AffId` int(11) DEFAULT NULL,
  `SiteId` int(11) DEFAULT NULL,
  `AffMerchantId` varchar(255) DEFAULT NULL,
  `SJobStatus` enum('added','executing','done','failed') DEFAULT NULL,
  `SJobAddTime` datetime DEFAULT NULL,
  `SJobLastUpdateTime` datetime DEFAULT NULL,
  PRIMARY KEY (`SJobId`)
) ENGINE=MyISAM AUTO_INCREMENT=26765 DEFAULT CHARSET=latin1;

/*Table structure for table `site` */

DROP TABLE IF EXISTS `site`;

CREATE TABLE `site` (
  `SiteId` int(11) NOT NULL,
  `SiteGroup` varchar(255) DEFAULT NULL,
  `SiteName` varchar(255) DEFAULT NULL,
  `SiteShortId` varchar(255) DEFAULT NULL,
  `SiteDomain` varchar(255) DEFAULT NULL,
  `SitePassword` varchar(255) DEFAULT NULL,
  `SiteMysqlHost` varchar(255) DEFAULT NULL,
  `SiteMysqlUser` varchar(255) DEFAULT NULL,
  `SiteMysqlPassword` varchar(255) DEFAULT NULL,
  `SiteMysqlDB` varchar(255) DEFAULT NULL,
  `SiteEncoding` varchar(20) DEFAULT NULL,
  `SiteAllowSync` enum('Yes','No') DEFAULT 'No',
  `SiteCountry` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`SiteId`),
  KEY `SID` (`SiteShortId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `site_links` */

DROP TABLE IF EXISTS `site_links`;

CREATE TABLE `site_links` (
  `SiteId` int(11) NOT NULL,
  `SiteCouponId` int(11) NOT NULL,
  `AffId` int(11) NOT NULL,
  `AffMerchantId` varchar(255) NOT NULL,
  `AffLinkId` varchar(255) NOT NULL,
  `LinkStatus` enum('added','changed') DEFAULT 'added',
  `LastUpdateTime` datetime DEFAULT NULL,
  PRIMARY KEY (`SiteId`,`SiteCouponId`),
  KEY `NewIndex1` (`AffId`,`AffMerchantId`,`AffLinkId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `site_merchant` */

DROP TABLE IF EXISTS `site_merchant`;

CREATE TABLE `site_merchant` (
  `SiteId` int(11) NOT NULL,
  `SiteMerchantId` int(11) NOT NULL,
  `AffId` int(11) NOT NULL,
  `AffMerchantId` varchar(255) NOT NULL,
  `LastUpdateDate` datetime DEFAULT NULL,
  `Relationship` enum('Active','Inactive') DEFAULT NULL,
  `InactiveReason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`SiteId`,`SiteMerchantId`),
  KEY `AffID` (`AffId`,`AffMerchantId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
