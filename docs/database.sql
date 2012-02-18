-- phpMyAdmin SQL Dump
-- version 3.3.7deb6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 18, 2012 at 12:04 PM
-- Server version: 5.1.58
-- PHP Version: 5.3.6-13ubuntu3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `fmlymap`
--

-- --------------------------------------------------------

--
-- Table structure for table `actionMessage`
--

CREATE TABLE IF NOT EXISTS `actionMessage` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id|Unique message id',
  `orgId` int(10) unsigned NOT NULL COMMENT 'Org Id|Organisation Id',
  `logDt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Log Date|Log date and time',
  `lvl` int(11) NOT NULL COMMENT 'Priority|Log priority level',
  `msg` text NOT NULL COMMENT 'Msg|Message',
  `uName` varchar(30) NOT NULL COMMENT 'uName|User logging the message',
  `ip` varchar(15) DEFAULT NULL COMMENT 'IP|Ip address',
  PRIMARY KEY (`id`),
  KEY `orgId` (`orgId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='action message log';

-- --------------------------------------------------------

--
-- Table structure for table `cat`
--

CREATE TABLE IF NOT EXISTS `cat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id|Category internal id',
  `orgId` int(10) unsigned NOT NULL COMMENT 'Org Id|Organisation identifier',
  `name` varchar(45) NOT NULL COMMENT 'Name|Category Name',
  `desc` varchar(45) DEFAULT NULL COMMENT 'Desc.|Category Description',
  PRIMARY KEY (`id`),
  KEY `fk_Category_Organisation1` (`orgId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Customers classification';

-- --------------------------------------------------------

--
-- Table structure for table `cat_relType`
--

CREATE TABLE IF NOT EXISTS `cat_relType` (
  `catId` int(10) unsigned NOT NULL COMMENT 'Cat Id|Category Id',
  `relTypeId` int(10) unsigned NOT NULL COMMENT 'Rel Id|Relationship Type Id',
  PRIMARY KEY (`catId`,`relTypeId`),
  KEY `relTypeId` (`relTypeId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `enrolled`
--

CREATE TABLE IF NOT EXISTS `enrolled` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id|Usage internal instance id',
  `prsnId` int(10) unsigned NOT NULL COMMENT 'Customer Id|Person using service as a customer',
  `srvcId` int(10) unsigned NOT NULL COMMENT 'Service|Service identifier',
  `eDate` date NOT NULL COMMENT 'Enroll Date|Date of enrollment',
  `orgId` int(10) unsigned NOT NULL COMMENT 'Org Id|Organisation Id',
  `status` enum('enrolled','waiting','past') NOT NULL DEFAULT 'enrolled' COMMENT 'Status|Enrollment status',
  PRIMARY KEY (`id`),
  KEY `fk_Enrolled_Person` (`prsnId`),
  KEY `fk_Enrolled_Service` (`srvcId`),
  KEY `orgId` (`orgId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Records enrollment for a service by a member';

-- --------------------------------------------------------

--
-- Table structure for table `geoData`
--

CREATE TABLE IF NOT EXISTS `geoData` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Loc Id|Location internal identifier',
  `orgId` int(10) unsigned NOT NULL COMMENT 'Org Id|Organisation Id',
  `hNum` varchar(30) NOT NULL COMMENT 'Hse. No.|House number',
  `pCode` varchar(8) NOT NULL COMMENT 'PCode|Post Code',
  `lat` double DEFAULT NULL COMMENT 'Lat|Centre latitude',
  `lng` double DEFAULT NULL COMMENT 'Long|Centre Longitude',
  `sts` enum('new','found','failed') NOT NULL DEFAULT 'new' COMMENT 'Status|Record status',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hNum` (`hNum`,`pCode`),
  KEY `orgId` (`orgId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Stores geo location data for addresses';
-- --------------------------------------------------------

--
-- Table structure for table `impprofile`
--

CREATE TABLE IF NOT EXISTS `impprofile` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id|Profile internal id',
  `orgId` int(10) unsigned NOT NULL COMMENT 'Org Id|Organisation identifier',
  `tbl` varchar(12) NOT NULL COMMENT 'Table|Table that profile is for',
  `name` varchar(45) NOT NULL COMMENT 'Name|Profile Name',
  `profile` text COMMENT 'Profile|serialized profile',
  PRIMARY KEY (`id`),
  KEY `fk_impprofile_Organisation1` (`orgId`),
  KEY `tbl` (`tbl`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Import profiles';

-- --------------------------------------------------------

--
-- Table structure for table `org`
--

CREATE TABLE IF NOT EXISTS `org` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Uid|Unique Identifier',
  `tag` varchar(3) NOT NULL COMMENT 'Brand Tag|Tag used to seleect branding images etc',
  `name` varchar(45) NOT NULL COMMENT 'OrgName|Organisation Name',
  `address` text NOT NULL COMMENT 'Address|Organisation address',
  `ctctName` varchar(45) NOT NULL COMMENT 'Contact|Contact name',
  `ctctTel` varchar(14) NOT NULL COMMENT 'Tel|Contact Telehone number',
  `ctctEmail` varchar(45) NOT NULL COMMENT 'Email|Contact Email',
  `mapCLat` double NOT NULL COMMENT 'Map CLat|Google map centre Latitude coord',
  `mapCLong` double NOT NULL COMMENT 'Map CLong|Google Map centre Longitude coord',
  `url` varchar(256) DEFAULT NULL COMMENT 'URL|Url of organisation main web site',
  `nextMbrId` int(11) DEFAULT '0' COMMENT 'Next Mbr Id|Next member id',
  `enckey` varchar(120) NOT NULL COMMENT 'Enc. Key|Encryption key',
  `license_key` varchar(30) NOT NULL COMMENT 'LKey|License Key',
  `local_key` text COMMENT 'License|License text',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='An organisation that has a set of data in the system';

--
-- Dumping data for table `org`
--

INSERT INTO `org` (`id`, `tag`, `name`, `address`, `ctctName`, `ctctTel`, `ctctEmail`, `mapCLat`, `mapCLong`, `url`, `nextMbrId`, `enckey`, `license_key`, `local_key`) VALUES
(1, 'ZF4', 'ZF 4 Business', '', 'Ashley Kitson', '', '', 0, 0, 'http://zf4.biz', 0, 'Rmjyhdfowqy2sVjii/nqapAv45m90qDDzO4uXTlkw1NGSFOyF5kFh8ZOx1SbgoHHr1KuL6nSKTqKM4+r44s2bw==', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `overlay`
--

CREATE TABLE IF NOT EXISTS `overlay` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id|Overlay id',
  `orgId` int(10) unsigned NOT NULL COMMENT 'Org Id|Organisation Id',
  `tag` enum('red','green','blue','none') NOT NULL DEFAULT 'none' COMMENT 'Button|Button tag',
  `name` varchar(30) NOT NULL COMMENT 'Name|Overlay name',
  `coords` text NOT NULL COMMENT 'Coords|Serialized coordinates',
  `colour` varchar(7) DEFAULT '#000000' COMMENT 'Colour|Overlay colour',
  `opacity` double DEFAULT '0.5' COMMENT 'Opacity|Display opacity',
  PRIMARY KEY (`id`),
  KEY `orgId` (`orgId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Overlay storage';

-- --------------------------------------------------------

--
-- Table structure for table `person`
--

CREATE TABLE IF NOT EXISTS `person` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id|Internal Id',
  `orgId` int(10) unsigned NOT NULL COMMENT 'OrgId|Organisation Id',
  `uid` varchar(10) NOT NULL COMMENT 'Uid|Unique Indentifier',
  `style` enum('Mr','Mrs','Ms','Miss','Mst','Dr') NOT NULL COMMENT 'Style|Form of address',
  `fName` varchar(20) NOT NULL COMMENT 'First Name|First or christian name',
  `mName` varchar(20) DEFAULT NULL COMMENT 'Mid. Name|Middle name(s)',
  `lName` varchar(30) NOT NULL COMMENT 'Last name|Last or Surname',
  `dob` date DEFAULT NULL COMMENT 'DOB|Date of birth',
  `gender` enum('male','female','undefined') NOT NULL DEFAULT 'undefined' COMMENT 'Gender|Gender',
  `age` smallint(6) DEFAULT NULL COMMENT 'Age|Age of person',
  `ageRange` varchar(1) DEFAULT NULL COMMENT 'Age Range|Age Range code',
  `ethnicity` char(2) NOT NULL DEFAULT '00' COMMENT 'Ethnicity|Ethnic origin',
  `lang` varchar(5) NOT NULL DEFAULT 'en' COMMENT 'Mother Tongue|First or native language',
  `geoId` int(10) unsigned DEFAULT NULL COMMENT 'Loc Id|Location Identifier',
  `pType` set('member','pupil','staff','doctor','health visitor','carer') NOT NULL DEFAULT 'member' COMMENT 'Person Type|Person Type',
  `mTel` varchar(12) DEFAULT NULL COMMENT 'Mobile|Mobile phone',
  `oTel` varchar(12) DEFAULT NULL COMMENT 'Phone|Other phone',
  `email` varchar(30) DEFAULT NULL COMMENT 'Email|Email address',
  `pin` varchar(6) DEFAULT NULL COMMENT 'Mbr Pin|Member Pin Number',
  `jDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date record was first entered',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid_UNIQUE` (`uid`,`orgId`),
  KEY `fk_Person_Organisation1` (`orgId`),
  KEY `geoId` (`geoId`),
  KEY `pType` (`pType`),
  KEY `jDate` (`jDate`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Customers and staff of the WLC Services';


-- --------------------------------------------------------

--
-- Table structure for table `person_cat`
--

CREATE TABLE IF NOT EXISTS `person_cat` (
  `catId` int(10) unsigned NOT NULL COMMENT 'Cat Id|Internal Identifier of category',
  `prsnId` int(10) unsigned NOT NULL COMMENT 'Person Id|Internal identifier of Person',
  PRIMARY KEY (`catId`,`prsnId`),
  KEY `fk_Customer_has_Category_Category1` (`catId`),
  KEY `fk_person_cat_person1` (`prsnId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `query`
--

CREATE TABLE IF NOT EXISTS `query` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID|Unique internal identifier',
  `tag` enum('map','report') NOT NULL DEFAULT 'map' COMMENT 'Tag|Target for query',
  `uid` int(10) unsigned NOT NULL COMMENT 'UID|Staff Id',
  `name` varchar(30) NOT NULL COMMENT 'Name|Query Name',
  `desc` text COMMENT 'Desc|Query Description',
  `sql` mediumtext NOT NULL COMMENT 'SQL|SQL Statement',
  `extra` text COMMENT 'Extra Info|Additional information required by query',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `tag` (`tag`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Saved users queries';


--
-- Table structure for table `relation`
--

CREATE TABLE IF NOT EXISTS `relation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID|Unique internal identifier',
  `prsnIdA` int(10) unsigned NOT NULL COMMENT 'Person A|Internal id of person A in relationship',
  `relTypeId` int(10) unsigned NOT NULL COMMENT 'Rel Type|Internal identifier of relationship type',
  `prsnIdB` int(10) unsigned NOT NULL COMMENT 'Person B|Internal identiifer of person B in relationship',
  PRIMARY KEY (`id`),
  KEY `prsnIdA` (`prsnIdA`),
  KEY `prsnIdB` (`prsnIdB`),
  KEY `relTypeId` (`relTypeId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `relType`
--

CREATE TABLE IF NOT EXISTS `relType` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id|Relationship type internal id',
  `orgId` int(10) unsigned NOT NULL COMMENT 'Org Id|Organisation Id',
  `name` varchar(45) NOT NULL COMMENT 'Name|Relationship Name',
  `revName` varchar(45) NOT NULL COMMENT 'Reverse Name|Relationship reverse name',
  `desc` text COMMENT 'Desc|Relationship description',
  `direction` enum('one-way','two-way') NOT NULL DEFAULT 'one-way' COMMENT 'Rel. Dir.|Relationship direction',
  `relColour` varchar(6) NOT NULL DEFAULT '000000' COMMENT 'Rel Colour|Relationship line colour',
  `relValue` tinyint(4) NOT NULL COMMENT 'Rel thickness|Relationship line thickness',
  `headType` set('member','pupil','staff','doctor','health visitor','carer') DEFAULT NULL COMMENT 'Head Type|Allowable head person type',
  `tailType` set('member','pupil','staff','doctor','health visitor','carer') DEFAULT NULL COMMENT 'Tail Type|Allowable tail person type',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `orgId` (`orgId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Defines allowable relationships between two Persons';

--
-- Dumping data for table `relType`
--

INSERT INTO `relType` (`id`, `orgId`, `name`, `revName`, `desc`, `direction`, `relColour`, `relValue`, `headType`, `tailType`) VALUES
(1, 2, 'Parent to', 'Child of', 'Legal parentage', 'one-way', '66FF66', 7, 'member,staff,doctor,health visitor,carer', 'member,pupil'),
(2, 2, 'Step Parent to', 'Step Child of', 'Parent by marriage', 'one-way', 'FF00FF', 3, 'member,staff,doctor,health visitor,carer', 'member,pupil'),
(3, 2, 'Grandparent to', 'Grandchild of', NULL, 'one-way', '000000', 5, 'member,staff,doctor,health visitor,carer', 'member,pupil'),
(4, 2, 'Guardian for', 'Ward of', 'Legal guardianship', 'one-way', '9900FF', 3, 'member,staff,doctor,health visitor,carer', 'member,pupil'),
(5, 2, 'Live in carer for', 'Cared for by', NULL, 'one-way', '000000', 1, 'carer', 'member,pupil'),
(6, 2, 'Child Minder for', 'Minded by', 'paid childcare', 'one-way', '000000', 1, 'member,carer', 'member,pupil'),
(7, 2, 'Key Worker to', 'has Key Worker', 'Staff member who is key point of contact for customer', 'one-way', '009966', 1, 'staff', 'member,pupil'),
(10, 2, 'Sibling', 'Sibling', 'brother or  sister', 'two-way', '99FF33', 5, 'member,pupil', 'member,pupil'),
(11, 2, 'Lives together with', 'Lives together with', 'lives with (a partnership not ratified by marriage or civil partnership)', 'two-way', '3333FF', 2, 'member,staff,doctor,health visitor,carer', 'member,staff,doctor,health visitor,carer'),
(12, 2, 'Married to', 'Married to', 'Legal marriage', 'two-way', '000000', 5, 'member,staff,doctor,health visitor,carer', 'member,staff,doctor,health visitor,carer'),
(13, 2, 'Partner to', 'Partner to', 'A civil partnership', 'two-way', '000000', 5, 'member,staff,doctor,health visitor,carer', 'member,staff,doctor,health visitor,carer'),
(14, 2, 'Doctor to', 'Patient of', 'doctor patient relationship', 'one-way', '9900CC', 1, 'doctor', 'member,pupil');

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

CREATE TABLE IF NOT EXISTS `service` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id|Internal Id',
  `orgId` int(10) unsigned NOT NULL COMMENT 'OrgId|Organisation Id',
  `staffId` int(10) unsigned DEFAULT NULL COMMENT 'Staff Id|Service led by Staff member id',
  `name` varchar(45) NOT NULL COMMENT 'Name|Service Name',
  `desc` varchar(45) DEFAULT NULL COMMENT 'Desc.|Service description',
  `enrolType` enum('free','admin','member','any','staff') NOT NULL DEFAULT 'free' COMMENT 'Enrol Type|Enrollment type',
  `eLimit` tinyint(4) NOT NULL DEFAULT '-1' COMMENT 'Enrol Limit|Max nuber of persons that can enrol',
  `extInfo` text COMMENT 'Extended description for member enrollment page',
  PRIMARY KEY (`id`),
  KEY `fk_Service_Organisation1` (`orgId`),
  KEY `fk_Service_Person1` (`staffId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='WLC Services to Customers';

-- --------------------------------------------------------

--
-- Table structure for table `systRole`
--

CREATE TABLE IF NOT EXISTS `systRole` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id|Role Id',
  `rName` varchar(45) NOT NULL COMMENT 'Role|Role Name',
  PRIMARY KEY (`id`),
  UNIQUE KEY `rName_UNIQUE` (`rName`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='System ACL roles';

--
-- Dumping data for table `systRole`
--

INSERT INTO `systRole` (`id`, `rName`) VALUES
(2, 'Admin'),
(3, 'Inputter'),
(5, 'Member'),
(1, 'Super Admin'),
(4, 'User');

-- --------------------------------------------------------

--
-- Table structure for table `systUser`
--

CREATE TABLE IF NOT EXISTS `systUser` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id|Internal user id',
  `orgId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'OrgId|Organisation Id',
  `uName` varchar(45) NOT NULL COMMENT 'UName|User name',
  `uEmail` varchar(45) NOT NULL COMMENT 'Email|Email address',
  `payrollId` varchar(45) NOT NULL COMMENT 'Payroll|Payroll number',
  `uPwd` varchar(32) NOT NULL COMMENT 'Password|Encrypted Password',
  `rowSts` enum('active','suspended','defunct') NOT NULL DEFAULT 'active' COMMENT 'Status|User status',
  `lastLogon` datetime DEFAULT NULL COMMENT 'Last Logon|Date and time of last logon',
  `lastIP` varchar(15) DEFAULT NULL COMMENT 'Last IP|IP address of last logon',
  `addDt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Add Date|Date user added',
  `prsnId` int(10) unsigned DEFAULT NULL COMMENT 'Person Id|Link to Person Identity',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uName_UNIQUE` (`uName`),
  KEY `orgId` (`orgId`),
  KEY `payrollId` (`payrollId`),
  KEY `prsnId` (`prsnId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='A system user';

--
-- Dumping data for table `systUser`
--

INSERT INTO `systUser` (`id`, `orgId`, `uName`, `uEmail`, `payrollId`, `uPwd`, `rowSts`, `lastLogon`, `lastIP`, `addDt`, `prsnId`) VALUES
(1, 1, 'SiteBoss', 'ashley@blackhole.com', '0000', 'b02c5595fb3a3d264edebcec7fd84757', 'active', '2011-01-21 10:16:39', '', '0000-00-00 00:00:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `systUserRole`
--

CREATE TABLE IF NOT EXISTS `systUserRole` (
  `uId` int(10) unsigned NOT NULL COMMENT 'User Id|Id of user in role',
  `rId` int(10) unsigned NOT NULL COMMENT 'Role Id|Id of role',
  PRIMARY KEY (`uId`,`rId`),
  KEY `rId` (`rId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='m2m User - role';

--
-- Dumping data for table `systUserRole`
--

INSERT INTO `systUserRole` (`uId`, `rId`) VALUES (1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `usage`
--

CREATE TABLE IF NOT EXISTS `usage` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id|Usage internal instance id',
  `prsnId` int(10) unsigned NOT NULL COMMENT 'Customer Id|Person using service as a customer',
  `srvcId` int(10) unsigned NOT NULL COMMENT 'Service|Service identifier',
  `uDate` date NOT NULL COMMENT 'Date|Date of service usage',
  `orgId` int(10) unsigned NOT NULL COMMENT 'Org Id|Organisation Identifier',
  PRIMARY KEY (`id`),
  KEY `fk_Usage_Customer1` (`prsnId`),
  KEY `fk_Usage_Service1` (`srvcId`),
  KEY `orgId` (`orgId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

--
-- Constraints for table `cat`
--
ALTER TABLE `cat`
  ADD CONSTRAINT `cat_ibfk_1` FOREIGN KEY (`orgId`) REFERENCES `org` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cat_relType`
--
ALTER TABLE `cat_relType`
  ADD CONSTRAINT `cat_relType_ibfk_1` FOREIGN KEY (`catId`) REFERENCES `cat` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cat_relType_ibfk_2` FOREIGN KEY (`relTypeId`) REFERENCES `relType` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `enrolled`
--
ALTER TABLE `enrolled`
  ADD CONSTRAINT `enrolled_ibfk_1` FOREIGN KEY (`orgId`) REFERENCES `org` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_enrolled_person` FOREIGN KEY (`prsnId`) REFERENCES `person` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_enrolled_service` FOREIGN KEY (`srvcId`) REFERENCES `service` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `geoData`
--
ALTER TABLE `geoData`
  ADD CONSTRAINT `geoData_ibfk_1` FOREIGN KEY (`orgId`) REFERENCES `org` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `overlay`
--
ALTER TABLE `overlay`
  ADD CONSTRAINT `overlay_ibfk_1` FOREIGN KEY (`orgId`) REFERENCES `org` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `person`
--
ALTER TABLE `person`
  ADD CONSTRAINT `person_ibfk_2` FOREIGN KEY (`geoId`) REFERENCES `geoData` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `person_ibfk_3` FOREIGN KEY (`orgId`) REFERENCES `org` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `person_cat`
--
ALTER TABLE `person_cat`
  ADD CONSTRAINT `person_cat_ibfk_1` FOREIGN KEY (`catId`) REFERENCES `cat` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `person_cat_ibfk_2` FOREIGN KEY (`prsnId`) REFERENCES `person` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `query`
--
ALTER TABLE `query`
  ADD CONSTRAINT `query_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `systUser` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `relation`
--
ALTER TABLE `relation`
  ADD CONSTRAINT `relation_ibfk_1` FOREIGN KEY (`prsnIdA`) REFERENCES `person` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `relation_ibfk_2` FOREIGN KEY (`relTypeId`) REFERENCES `relType` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `relation_ibfk_3` FOREIGN KEY (`prsnIdB`) REFERENCES `person` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `relType`
--
ALTER TABLE `relType`
  ADD CONSTRAINT `relType_ibfk_1` FOREIGN KEY (`orgId`) REFERENCES `org` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `service`
--
ALTER TABLE `service`
  ADD CONSTRAINT `service_ibfk_5` FOREIGN KEY (`orgId`) REFERENCES `org` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `service_ibfk_6` FOREIGN KEY (`staffId`) REFERENCES `person` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `systUser`
--
ALTER TABLE `systUser`
  ADD CONSTRAINT `systUser_ibfk_1` FOREIGN KEY (`orgId`) REFERENCES `org` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `systUserRole`
--
ALTER TABLE `systUserRole`
  ADD CONSTRAINT `systUserRole_ibfk_1` FOREIGN KEY (`uId`) REFERENCES `systUser` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `systUserRole_ibfk_2` FOREIGN KEY (`rId`) REFERENCES `systRole` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `usage`
--
ALTER TABLE `usage`
  ADD CONSTRAINT `usage_ibfk_3` FOREIGN KEY (`orgId`) REFERENCES `org` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `usage_ibfk_4` FOREIGN KEY (`prsnId`) REFERENCES `person` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usage_ibfk_5` FOREIGN KEY (`srvcId`) REFERENCES `service` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

DELIMITER $$
--
-- Procedures
--
CREATE PROCEDURE `setAgeRange`()
begin
update `person` set ageRange = if( age < 6, 'A', if(age>5 and age < 12, 'B' , if( age >11 and age < 20 , 'C', if( age >19 and age < 26, 'D','E'))));
end$$

--
-- Functions
--
CREATE FUNCTION `getCategories`(pId INT) RETURNS char(50) CHARSET latin1
begin
declare ret char(50);
select cast(GROUP_CONCAT(id) as char) into ret from cat join person_cat on catId=id and prsnId=pId;
return ret;
end$$

CREATE  FUNCTION `getNextMbrId`(orgId int) RETURNS varchar(10) CHARSET latin1
    DETERMINISTIC
begin
declare ret varchar(10);
update org set nextMbrId = nextMbrId + 1 where id = orgId;
select concat(tag,(lpad(cast(nextMbrId as char),10 - length(tag),'0'))) as mid from org where id = orgId into ret;
return ret;
end$$

DELIMITER ;