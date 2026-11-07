-- ============================================================
-- دیتابیس سامانه مدیریت خدمات درمانی و سلامت (Health Check)
-- قابل اجرا در MySQL Workbench و phpMyAdmin
-- ============================================================

CREATE DATABASE IF NOT EXISTS `health_check`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `health_check`;

-- حذف جداول قبلی در صورت وجود (به ترتیب وابستگی کلیدهای خارجی)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `medicines`;
DROP TABLE IF EXISTS `healthstatus`;
DROP TABLE IF EXISTS `visit`;
DROP TABLE IF EXISTS `patient`;
DROP TABLE IF EXISTS `doctor`;
DROP TABLE IF EXISTS `person`;
DROP VIEW IF EXISTS `healthstatue`;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- ۱. جدول مشخصات افراد (person)
-- ============================================================
CREATE TABLE `person` (
    `ID` VARCHAR(10) NOT NULL COMMENT 'کد ملی ۱۰ رقمی فرد',
    `name` VARCHAR(100) NOT NULL COMMENT 'نام و نام خانوادگی',
    `gender` ENUM('زن', 'مرد') NOT NULL COMMENT 'جنسیت',
    `birth` DATE NOT NULL COMMENT 'تاریخ تولد',
    `tel` VARCHAR(11) NOT NULL COMMENT 'شماره تماس',
    `province` VARCHAR(50) NOT NULL COMMENT 'استان',
    `city` VARCHAR(50) NOT NULL COMMENT 'شهر',
    `street` VARCHAR(100) NOT NULL COMMENT 'خیابان',
    `plaque` INT NOT NULL COMMENT 'پلاک',
    PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ۲. جدول مشخصات پزشکان (doctor)
-- ============================================================
CREATE TABLE `doctor` (
    `DID` VARCHAR(10) NOT NULL COMMENT 'شناسه پزشک (همان کد ملی در جدول person)',
    `workhistory` INT DEFAULT 0 COMMENT 'سابقه کار به سال',
    `expert` VARCHAR(100) NOT NULL COMMENT 'تخصص پزشک',
    PRIMARY KEY (`DID`),
    CONSTRAINT `fk_doctor_person` 
        FOREIGN KEY (`DID`) REFERENCES `person` (`ID`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ۳. جدول مشخصات بیماران (patient)
-- ============================================================
CREATE TABLE `patient` (
    `PID` VARCHAR(10) NOT NULL COMMENT 'شناسه بیمار (همان کد ملی در جدول person)',
    `age` INT NOT NULL COMMENT 'سن بیمار',
    `sickhistory` LONGTEXT DEFAULT NULL COMMENT 'سابقه بیماری بیمار',
    PRIMARY KEY (`PID`),
    CONSTRAINT `fk_patient_person` 
        FOREIGN KEY (`PID`) REFERENCES `person` (`ID`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ۴. جدول اطلاعات ویزیت (visit)
-- ============================================================
CREATE TABLE `visit` (
    `VID` INT NOT NULL AUTO_INCREMENT COMMENT 'شناسه ویزیت',
    `PTID` VARCHAR(10) NOT NULL COMMENT 'شناسه بیمار',
    `DRID` VARCHAR(10) NOT NULL COMMENT 'شناسه پزشک',
    `DATE` DATETIME NOT NULL COMMENT 'تاریخ و زمان ویزیت',
    PRIMARY KEY (`VID`),
    CONSTRAINT `fk_visit_patient` 
        FOREIGN KEY (`PTID`) REFERENCES `patient` (`PID`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_visit_doctor` 
        FOREIGN KEY (`DRID`) REFERENCES `doctor` (`DID`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ۵. جدول اطلاعات داروها (medicines)
-- ============================================================
CREATE TABLE `medicines` (
    `MID` INT NOT NULL AUTO_INCREMENT COMMENT 'شناسه دارو',
    `VID` INT NOT NULL COMMENT 'شناسه ویزیت مربوطه',
    `medicine` VARCHAR(255) NOT NULL COMMENT 'نام و مشخصات دارو',
    PRIMARY KEY (`MID`),
    CONSTRAINT `fk_medicines_visit` 
        FOREIGN KEY (`VID`) REFERENCES `visit` (`VID`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ۶. جدول ثبت علائم بیمار (healthstatus)
-- ============================================================
CREATE TABLE `healthstatus` (
    `HSID` INT NOT NULL AUTO_INCREMENT COMMENT 'شناسه علائم',
    `SID` VARCHAR(10) NOT NULL COMMENT 'شناسه بیمار',
    `date` DATETIME NOT NULL COMMENT 'تاریخ ثبت علائم',
    `symptoms` LONGTEXT NOT NULL COMMENT 'شرح علائم',
    PRIMARY KEY (`HSID`),
    CONSTRAINT `fk_healthstatus_patient` 
        FOREIGN KEY (`SID`) REFERENCES `patient` (`PID`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ایجاد View به نام healthstatue جهت سازگاری کامل
CREATE OR REPLACE VIEW `healthstatue` AS SELECT * FROM `healthstatus`;


-- ============================================================
-- بخش پرسیجرها (Stored Procedures)
-- ============================================================

-- حذف پرسیجرهای قبلی در صورت وجود
DROP PROCEDURE IF EXISTS `select_person`;
DROP PROCEDURE IF EXISTS `insert_person`;
DROP PROCEDURE IF EXISTS `update_person`;
DROP PROCEDURE IF EXISTS `delete_person`;

DROP PROCEDURE IF EXISTS `select_personfordoc`;
DROP PROCEDURE IF EXISTS `select_doctor`;
DROP PROCEDURE IF EXISTS `insert_update_doctor`;
DROP PROCEDURE IF EXISTS `delete_doctor`;

DROP PROCEDURE IF EXISTS `select_personforpat`;
DROP PROCEDURE IF EXISTS `select_patient`;
DROP PROCEDURE IF EXISTS `insert_update_patient`;
DROP PROCEDURE IF EXISTS `delete_patient`;

DROP PROCEDURE IF EXISTS `select_visit`;
DROP PROCEDURE IF EXISTS `insert_visit`;
DROP PROCEDURE IF EXISTS `update_visit`;
DROP PROCEDURE IF EXISTS `delete_visit`;

DROP PROCEDURE IF EXISTS `select_medicine`;
DROP PROCEDURE IF EXISTS `insert_medicine`;
DROP PROCEDURE IF EXISTS `update_medicine`;
DROP PROCEDURE IF EXISTS `delete_medicine`;

DROP PROCEDURE IF EXISTS `select_statue`;
DROP PROCEDURE IF EXISTS `insert_statue`;
DROP PROCEDURE IF EXISTS `update_statue`;
DROP PROCEDURE IF EXISTS `delete_statue`;


-- ------------------------------------------------------------
-- پرسیجرهای جدول افراد (person)
-- ------------------------------------------------------------
DELIMITER //

CREATE PROCEDURE `select_person`(
    IN `p_id` VARCHAR(10)
)
BEGIN
    SELECT `ID`, `name`, `gender`, `birth`, `tel`, `province`, `city`, `street`, `plaque`
    FROM `person`
    WHERE `ID` = `p_id`;
END //

CREATE PROCEDURE `insert_person`(
    IN `p_id` VARCHAR(10),
    IN `p_name` VARCHAR(100),
    IN `p_gender` VARCHAR(10),
    IN `p_birth` DATE,
    IN `p_tel` VARCHAR(11),
    IN `p_province` VARCHAR(50),
    IN `p_city` VARCHAR(50),
    IN `p_street` VARCHAR(100),
    IN `p_plaque` INT
)
BEGIN
    INSERT INTO `person` (`ID`, `name`, `gender`, `birth`, `tel`, `province`, `city`, `street`, `plaque`)
    VALUES (`p_id`, `p_name`, `p_gender`, `p_birth`, `p_tel`, `p_province`, `p_city`, `p_street`, `p_plaque`);
END //

CREATE PROCEDURE `update_person`(
    IN `p_id` VARCHAR(10),
    IN `p_name` VARCHAR(100),
    IN `p_gender` VARCHAR(10),
    IN `p_birth` DATE,
    IN `p_tel` VARCHAR(11),
    IN `p_province` VARCHAR(50),
    IN `p_city` VARCHAR(50),
    IN `p_street` VARCHAR(100),
    IN `p_plaque` INT
)
BEGIN
    UPDATE `person`
    SET `name` = `p_name`,
        `gender` = `p_gender`,
        `birth` = `p_birth`,
        `tel` = `p_tel`,
        `province` = `p_province`,
        `city` = `p_city`,
        `street` = `p_street`,
        `plaque` = `p_plaque`
    WHERE `ID` = `p_id`;
END //

CREATE PROCEDURE `delete_person`(
    IN `p_id` VARCHAR(10)
)
BEGIN
    DELETE FROM `person` WHERE `ID` = `p_id`;
END //

-- ------------------------------------------------------------
-- پرسیجرهای مربوط به پزشکان (doctor)
-- ------------------------------------------------------------

CREATE PROCEDURE `select_personfordoc`(
    IN `p_did` VARCHAR(10)
)
BEGIN
    SELECT `ID`, `name`, `gender`, `birth`, `tel`, `province`, `city`, `street`, `plaque`
    FROM `person`
    WHERE `ID` = `p_did`;
END //

CREATE PROCEDURE `select_doctor`(
    IN `p_did` VARCHAR(10)
)
BEGIN
    SELECT `DID`, `workhistory`, `expert`
    FROM `doctor`
    WHERE `DID` = `p_did`;
END //

CREATE PROCEDURE `insert_update_doctor`(
    IN `p_did` VARCHAR(10),
    IN `p_name` VARCHAR(100),
    IN `p_gender` VARCHAR(10),
    IN `p_birth` DATE,
    IN `p_tel` VARCHAR(11),
    IN `p_province` VARCHAR(50),
    IN `p_city` VARCHAR(50),
    IN `p_street` VARCHAR(100),
    IN `p_plaque` INT,
    IN `p_workhistory` INT,
    IN `p_expert` VARCHAR(100)
)
BEGIN
    -- در صورت ویرایش مشخصات فرد در فرم پزشک
    UPDATE `person`
    SET `name` = `p_name`,
        `gender` = `p_gender`,
        `birth` = `p_birth`,
        `tel` = `p_tel`,
        `province` = `p_province`,
        `city` = `p_city`,
        `street` = `p_street`,
        `plaque` = `p_plaque`
    WHERE `ID` = `p_did`;

    -- ثبت یا به‌روزرسانی پزشک
    INSERT INTO `doctor` (`DID`, `workhistory`, `expert`)
    VALUES (`p_did`, `p_workhistory`, `p_expert`)
    ON DUPLICATE KEY UPDATE
        `workhistory` = VALUES(`workhistory`),
        `expert` = VALUES(`expert`);
END //

CREATE PROCEDURE `delete_doctor`(
    IN `p_did` VARCHAR(10)
)
BEGIN
    DELETE FROM `doctor` WHERE `DID` = `p_did`;
END //

-- ------------------------------------------------------------
-- پرسیجرهای مربوط به بیماران (patient)
-- ------------------------------------------------------------

CREATE PROCEDURE `select_personforpat`(
    IN `p_pid` VARCHAR(10)
)
BEGIN
    SELECT `ID`, `name`, `gender`, `birth`, `tel`, `province`, `city`, `street`, `plaque`
    FROM `person`
    WHERE `ID` = `p_pid`;
END //

CREATE PROCEDURE `select_patient`(
    IN `p_pid` VARCHAR(10)
)
BEGIN
    SELECT `PID`, `age`, `sickhistory`
    FROM `patient`
    WHERE `PID` = `p_pid`;
END //

CREATE PROCEDURE `insert_update_patient`(
    IN `p_pid` VARCHAR(10),
    IN `p_name` VARCHAR(100),
    IN `p_gender` VARCHAR(10),
    IN `p_birth` DATE,
    IN `p_tel` VARCHAR(11),
    IN `p_province` VARCHAR(50),
    IN `p_city` VARCHAR(50),
    IN `p_street` VARCHAR(100),
    IN `p_plaque` INT,
    IN `p_age` INT,
    IN `p_sickhistory` LONGTEXT
)
BEGIN
    -- در صورت ویرایش مشخصات فرد در فرم بیمار
    UPDATE `person`
    SET `name` = `p_name`,
        `gender` = `p_gender`,
        `birth` = `p_birth`,
        `tel` = `p_tel`,
        `province` = `p_province`,
        `city` = `p_city`,
        `street` = `p_street`,
        `plaque` = `p_plaque`
    WHERE `ID` = `p_pid`;

    -- ثبت یا به‌روزرسانی بیمار
    INSERT INTO `patient` (`PID`, `age`, `sickhistory`)
    VALUES (`p_pid`, `p_age`, `p_sickhistory`)
    ON DUPLICATE KEY UPDATE
        `age` = VALUES(`age`),
        `sickhistory` = VALUES(`sickhistory`);
END //

CREATE PROCEDURE `delete_patient`(
    IN `p_pid` VARCHAR(10)
)
BEGIN
    DELETE FROM `patient` WHERE `PID` = `p_pid`;
END //

-- ------------------------------------------------------------
-- پرسیجرهای مربوط به ویزیت (visit)
-- ------------------------------------------------------------

CREATE PROCEDURE `select_visit`(
    IN `p_vid` INT
)
BEGIN
    SELECT `VID`, `PTID`, `DRID`, `DATE`
    FROM `visit`
    WHERE `VID` = `p_vid`;
END //

CREATE PROCEDURE `insert_visit`(
    IN `p_ptid` VARCHAR(10),
    IN `p_drid` VARCHAR(10),
    IN `p_date` DATETIME
)
BEGIN
    INSERT INTO `visit` (`PTID`, `DRID`, `DATE`)
    VALUES (`p_ptid`, `p_drid`, `p_date`);
END //

CREATE PROCEDURE `update_visit`(
    IN `p_vid` INT,
    IN `p_ptid` VARCHAR(10),
    IN `p_drid` VARCHAR(10),
    IN `p_date` DATETIME
)
BEGIN
    UPDATE `visit`
    SET `PTID` = `p_ptid`,
        `DRID` = `p_drid`,
        `DATE` = `p_date`
    WHERE `VID` = `p_vid`;
END //

CREATE PROCEDURE `delete_visit`(
    IN `p_vid` INT
)
BEGIN
    DELETE FROM `visit` WHERE `VID` = `p_vid`;
END //

-- ------------------------------------------------------------
-- پرسیجرهای مربوط به داروها (medicines)
-- ------------------------------------------------------------

CREATE PROCEDURE `select_medicine`(
    IN `p_mid` INT
)
BEGIN
    SELECT `MID`, `VID`, `medicine`
    FROM `medicines`
    WHERE `MID` = `p_mid`;
END //

CREATE PROCEDURE `insert_medicine`(
    IN `p_vid` INT,
    IN `p_medicine` VARCHAR(255)
)
BEGIN
    INSERT INTO `medicines` (`VID`, `medicine`)
    VALUES (`p_vid`, `p_medicine`);
END //

CREATE PROCEDURE `update_medicine`(
    IN `p_mid` INT,
    IN `p_vid` INT,
    IN `p_medicine` VARCHAR(255)
)
BEGIN
    UPDATE `medicines`
    SET `VID` = `p_vid`,
        `medicine` = `p_medicine`
    WHERE `MID` = `p_mid`;
END //

CREATE PROCEDURE `delete_medicine`(
    IN `p_mid` INT
)
BEGIN
    DELETE FROM `medicines` WHERE `MID` = `p_mid`;
END //

-- ------------------------------------------------------------
-- پرسیجرهای مربوط به علائم (healthstatus)
-- ------------------------------------------------------------

CREATE PROCEDURE `select_statue`(
    IN `p_hsid` INT
)
BEGIN
    SELECT `HSID`, `SID`, `date`, `symptoms`
    FROM `healthstatus`
    WHERE `HSID` = `p_hsid`;
END //

CREATE PROCEDURE `insert_statue`(
    IN `p_sid` VARCHAR(10),
    IN `p_date` DATETIME,
    IN `p_symptoms` LONGTEXT
)
BEGIN
    INSERT INTO `healthstatus` (`SID`, `date`, `symptoms`)
    VALUES (`p_sid`, `p_date`, `p_symptoms`);
END //

CREATE PROCEDURE `update_statue`(
    IN `p_hsid` INT,
    IN `p_sid` VARCHAR(10),
    IN `p_date` DATETIME,
    IN `p_symptoms` LONGTEXT
)
BEGIN
    UPDATE `healthstatus`
    SET `SID` = `p_sid`,
        `date` = `p_date`,
        `symptoms` = `p_symptoms`
    WHERE `HSID` = `p_hsid`;
END //

CREATE PROCEDURE `delete_statue`(
    IN `p_hsid` INT
)
BEGIN
    DELETE FROM `healthstatus` WHERE `HSID` = `p_hsid`;
END //

DELIMITER ;

-- ============================================================
-- داده‌های نمونه برای تست اولیه سیستم (Seed Data)
-- ============================================================

-- درج افراد نمونه
INSERT INTO `person` (`ID`, `name`, `gender`, `birth`, `tel`, `province`, `city`, `street`, `plaque`) VALUES
('0011223344', 'دکتر علی محمدی', 'مرد', '1980-05-15', '09121112233', 'تهران', 'تهران', 'خیابان ولیعصر', 10),
('0022334455', 'دکتر سارا احمدی', 'زن', '1985-08-20', '09122223344', 'تهران', 'تهران', 'خیابان شریعتی', 45),
('0033445566', 'رضا رضایی', 'مرد', '1995-02-10', '09123334455', 'تهران', 'تهران', 'خیابان آزادی', 120),
('0044556677', 'مریم کاظمی', 'زن', '2000-11-25', '09124445566', 'تهران', 'تهران', 'خیابان انقلاب', 78);

-- درج پزشکان
INSERT INTO `doctor` (`DID`, `workhistory`, `expert`) VALUES
('0011223344', 15, 'متخصص قلب و عروق'),
('0022334455', 10, 'متخصص مغز و اعصاب');

-- درج بیماران
INSERT INTO `patient` (`PID`, `age`, `sickhistory`) VALUES
('0033445566', 29, 'فشار خون بالا و میگرن مزمن'),
('0044556677', 24, 'حساسیت دارویی به پنی‌سیلین');

-- درج ویزیت نمونه
INSERT INTO `visit` (`VID`, `PTID`, `DRID`, `DATE`) VALUES
(1, '0033445566', '0011223344', '2025-01-15 10:30:00'),
(2, '0044556677', '0022334455', '2025-01-16 11:45:00');

-- درج داروهای تجویز شده
INSERT INTO `medicines` (`MID`, `VID`, `medicine`) VALUES
(1, 1, 'قرص پروپرانولول ۲۰ میلی‌گرم روزی یک عدد'),
(2, 1, 'قرص آملودیپین ۵ میلی‌گرم روزی یک عدد'),
(3, 2, 'کپسول گاباپنتین ۱۰۰ میلی‌گرم شبی یک عدد');

-- درج علائم ثبت شده بیمار
INSERT INTO `healthstatus` (`HSID`, `SID`, `date`, `symptoms`) VALUES
(1, '0033445566', '2025-01-14 18:00:00', 'سرگیجه خفیف همراه با تپش قلب پس از فعالیت بدنی'),
(2, '0044556677', '2025-01-15 20:30:00', 'سردرد شدید در ناحیه گیجگاهی به مدت ۳ روز');
