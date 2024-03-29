CREATE DATABASE yeltechdb;
USE yeltechdb;
SET default_storage_engine=InnoDB;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE messages (
	messageId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    fromNumber VARCHAR(64) NOT NULL,
    toNumber VARCHAR(64) NOT NULL,
    textBody VARCHAR(1024) NOT NULL,
    messageType VARCHAR(64) NOT NULL,
	timeSent DATETIME DEFAULT CURRENT_TIMESTAMP,
    messageuuid VARCHAR(128) UNIQUE,
    PRIMARY KEY (messageId)
) ENGINE=InnoDB;


CREATE TABLE pendingMessages (
	pendingMessageId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    fromNumber VARCHAR(64) NOT NULL,
    toNumber VARCHAR(64) NOT NULL,
    textBody VARCHAR(1024) NOT NULL,
    timeSent DATETIME DEFAULT CURRENT_TIMESTAMP,
    messageuuid VARCHAR(128) NOT NULL UNIQUE,
	PRIMARY KEY (pendingMessageId)
) ENGINE=InnoDB; 


CREATE TABLE roles (
	roleId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    roleName VARCHAR(128) NOT NULL,
    PRIMARY KEY (roleId)
) ENGINE=InnoDB;
INSERT INTO roles (roleName) VALUES ('Super Admin'),('Yeltech Admin'),('Group Admin'),('Standard User');


CREATE TABLE sendingType (
	sendingId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    sendingType VARCHAR(64) NOT NULL,
    PRIMARY KEY (sendingId)
) ENGINE=InnoDB;
INSERT INTO sendingType (sendingType) VALUES ('EMAIL'),('SMS'),('EMAIL & SMS'),('NONE');


CREATE TABLE users (
	userId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    roleId INT UNSIGNED NOT NULL,
    fullName VARCHAR(128) NOT NULL,
    groupId INT UNSIGNED NOT NULL,
    phoneNumber VARCHAR(64),
    email VARCHAR(128) NOT NULL UNIQUE,
    pwd VARCHAR(128) NOT NULL,
    sendingId INT UNSIGNED NOT NULL DEFAULT 1,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    createdBy VARCHAR(128) DEFAULT NULL,
    PRIMARY KEY (userId),
    FOREIGN KEY (groupId) REFERENCES `groups`(groupId) ON DELETE CASCADE,
	FOREIGN KEY (sendingId) REFERENCES sendingType(sendingId),
    FOREIGN KEY (roleId) REFERENCES roles(roleId)
) ENGINE=InnoDB;
INSERT INTO users (roleId, fullname, groupId, email, pwd, createdBy, phoneNumber) 
VALUES (1, 'Harry Potter', 1, 'staduser@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (3, 'Lebron James', 3, 'groupadmin@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (2, 'Michael Jordan', 5, 'yeladmin@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (1, 'John Doe', 3,'johndoe@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (3, 'Diamond Doris', 5,'jodoe@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (4, 'Kobe Bryant', 1,'johdaandoe@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (4, 'Alex Dodge', 2,'johdoe@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (2, 'Steve Jobs', 3,'joashndoe@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (4, 'Bill Gates', 4,'joha68dndoe@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (2, 'Steve Kalabrine', 3,'jaasda78dwsddoe@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (4, 'Tom Mexico', 2,'johawndoe@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (3, 'Steve Vasturia', 3,'johsdndoe@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (1, 'Stuart Little', 5,'johnw6wasdwdoe@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (2, 'Harvey Specter', 2, 'yela68dmin@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (1, 'Mike Ross', 3,'jooe@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (3, 'Rachel Zane', 5,'johnaddoe@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (4, 'Donald Trump', 3,'johdadandoe@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (4, 'Kevin Durant', 2,'johndadoe@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (2, 'Paul George', 3,'joasdd83hndoe@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (4, 'Elon Musk', 5,'joh3sawdndoe@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (2, 'Kanye West', 1,'johjn153sddoe@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (4, 'Jared Dudley', 2,'johaa153sdndoe@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (3, 'Luka Doncic', 3,'johasasdndoe@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122'),
 (1, 'Kristaps Porzingis', 1,'johnwd153oe@gmail.com', '$2y$10$/MayYegaOu/N59kAQwvq.uZOORbfVsBJTY8SfkoyCU5g.R1ab6KGG', 0, '07716494122');


CREATE TABLE `groups` (
	groupId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    groupName VARCHAR(128) NOT NULL UNIQUE,
    latitude DECIMAL(9,6),
    longitude DECIMAL(8,6),
    dashAccess TINYINT NOT NULL DEFAULT 1,
    appAccess TINYINT NOT NULL DEFAULT 1,
    smsLeft INT NOT NULL DEFAULT 0,
    createdBy VARCHAR(128),
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (groupId)
) ENGINE=InnoDB;
INSERT INTO `groups`
	(groupName, latitude, longitude, createdBy)
VALUES
	('Yeltech',51.713944, -2.534767, 1),
	('Plymouth',50.713944, -3.534767, 1),
    ('Leicester',52.637230, -1.139145, 1),
    ('JBVTRD',63.445075, 10.895973, 1),
    ('ANM-CWG',24.594706, 46.549900, 1),
    ('Empty Grp',24.594706, 46.549900, 1);


CREATE TABLE auth_tokens (
	authId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    selector CHAR(16) NOT NULL,
    hashedValidator CHAR(64) NOT NULL,
    userId INT UNSIGNED NOT NULL,
    activeFrom DATETIME NOT NULL,
    activeTo DATETIME NOT NULL,
    PRIMARY KEY (authId),
    FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE subscriptions(
	subscriptionId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    deviceId INT UNSIGNED NOT NULL,
	subStart DATE,
    subFinish DATE,
    PRIMARY KEY (subscriptionId),
    FOREIGN KEY (deviceId) REFERENCES devices(deviceId) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE deviceTypes (
	deviceTypeId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    deviceTypeName VARCHAR(128) NOT NULL UNIQUE,
    PRIMARY KEY (deviceTypeId)
) ENGINE=InnoDB;
INSERT INTO deviceTypes (deviceTypeName) VALUES ('BSC-50-D'), ('BSC-50-E'), ('ADU-500'), ('SCOM-100');


CREATE TABLE products (
	productId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    productName VARCHAR(128) NOT NULL UNIQUE,
    PRIMARY KEY (productId)
) ENGINE=InnoDB;
INSERT INTO products (productName) VALUES ('RTMU'), ('EWB'), ('EWB V2'), ('TILT');


CREATE TABLE devices (
	deviceId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    deviceName VARCHAR(128) NOT NULL UNIQUE,
    deviceAlias VARCHAR(128),
    customLocation VARCHAR(64),
    latitude DECIMAL(9,6),
    longitude DECIMAL(8,6),
    devicePhone VARCHAR(64) UNIQUE NOT NULL,
    deviceTypeId INT UNSIGNED NOT NULL,
    productId INT UNSIGNED NOT NULL,
    groupId INT UNSIGNED,
	createdAt DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    createdBy VARCHAR(128),
    lastCalibration DATE,
    nextCalibrationDue DATE,
    deviceStatus TINYINT NOT NULL DEFAULT 0,
    PRIMARY KEY (deviceId),
    FOREIGN KEY (productId) REFERENCES products(productId),
    FOREIGN KEY (groupId) REFERENCES `groups`(groupId) ON DELETE SET NULL,
    FOREIGN KEY (deviceTypeId) REFERENCES deviceTypes(deviceTypeId)
) ENGINE=InnoDB;


CREATE TABLE channels(
	channelId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    channelName VARCHAR(128) NOT NULL,
    unitId INT UNSIGNED,
    deviceId INT UNSIGNED NOT NULL,
    channelType ENUM('DI', 'AI', 'COUNTER') NOT NULL,
    PRIMARY KEY (channelId),
    FOREIGN KEY (unitId) REFERENCES units(unitId) ON DELETE SET NULL,
    FOREIGN KEY (deviceId) REFERENCES devices(deviceId) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE units(
	unitId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    unitName VARCHAR(8) UNIQUE,
    PRIMARY KEY (unitId)
) ENGINE=InnoDB;
INSERT INTO units (unitName) VALUES ('oC'), ('mm'), ('mV'), ('mA'), ('A'), ('%');


CREATE TABLE measurements(
	measurementId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    channelId INT UNSIGNED NOT NULL,
    deviceId INT UNSIGNED NOT NULL,
    measurement VARCHAR(64),
    measurementTime DATETIME,
    PRIMARY KEY (measurementId),
    FOREIGN KEY (deviceId) REFERENCES devices(deviceId) ON DELETE CASCADE,
    FOREIGN KEY (channelId) REFERENCES channels(channelId) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE smsAlarms(
	smsAlarmId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    channelId INT UNSIGNED NOT NULL,
    deviceId INT UNSIGNED NOT NULL,
    smsAlarmHeader VARCHAR(64),
    smsAlarmReading VARCHAR(64),
    smsAlarmTime DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (smsAlarmId),
    FOREIGN KEY (channelId) REFERENCES channels(channelId) ON DELETE CASCADE,
    FOREIGN KEY (deviceId) REFERENCES devices(deviceId) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE smsStatus(
	smsStatusId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    deviceId INT UNSIGNED NOT NULL,
    smsStatus VARCHAR(64),
    samplingData VARCHAR(128),
	latitude DECIMAL(9,6),
    longitude DECIMAL(8,6),
    smsStatusTime DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (smsStatusId),
    FOREIGN KEY (deviceId) REFERENCES devices(deviceId) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE alarmTriggers(
	triggerId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    channelId INT UNSIGNED NOT NULL,
    deviceId INT UNSIGNED NOT NULL,
    operator VARCHAR(8) NOT NULL,
    thresholdValue VARCHAR(16),
    alarmDescription VARCHAR(64),
    isTriggered TINYINT NOT NULL DEFAULT 0,
    timeCreated DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (triggerId),
    FOREIGN KEY (channelId) REFERENCES channels(channelId) ON DELETE CASCADE,
    FOREIGN KEY (deviceId) REFERENCES devices(deviceId) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE triggeredAlarmsHistory (
	historyId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    deviceId INT UNSIGNED NOT NULL,
    channelName VARCHAR(64),
    unitName VARCHAR(8),
    operator VARCHAR(8),
    thresholdValue VARCHAR(16),
    alarmDescription VARCHAR(64),
    clearedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (historyId),
    FOREIGN KEY (deviceId) REFERENCES devices(deviceId) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE alarmRecipients(
	alarmRecipientId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    deviceId INT UNSIGNED NOT NULL,
    userId INT UNSIGNED NOT NULL,
    PRIMARY KEY (alarmRecipientId),
    FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE,
    FOREIGN KEY (deviceId) REFERENCES devices(deviceId) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tiltDashboardSettings (
	tiltDashboardSetting INT UNSIGNED AUTO_INCREMENT NOT NULL,
    deviceId INT UNSIGNED NOT NULL,
    imageURL VARCHAR(256),
    horizontalBox_offset_top INT,
    horizontalBox_offset_left INT,
    horizontalBox_lt0_text VARCHAR(64),
    horizontalBox_mt0_text VARCHAR(64),
    horizontalBox_lt0_arrowDirection VARCHAR(8),
    horizontalBox_mt0_arrowDirection VARCHAR(8),
    verticalBox_offset_top INT,
    verticalBox_offset_left INT,
    verticalBox_lt0_text VARCHAR(64),
    verticalBox_mt0_text VARCHAR(64),
    verticalBox_lt0_arrowDirection VARCHAR(8),
    verticalBox_mt0_arrowDirection VARCHAR(8),
    PRIMARY KEY (tiltDashboardSetting),
    FOREIGN KEY (deviceId) REFERENCES devices(deviceId) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE dashboardImage (
	dashboardImageId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    deviceId INT UNSIGNED NOT NULL,
    imageURL VARCHAR(256),
    PRIMARY KEY (dashboardImageId),
    FOREIGN KEY (deviceId) REFERENCES devices(deviceId) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE dashboardMovableBox (
	dashboardMovableBoxId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    channelId INT UNSIGNED NOT NULL,
    deviceId INT UNSIGNED NOT NULL,
    offset_top INT,
    offset_left INT,
    PRIMARY KEY (dashboardMovableBoxId),
    FOREIGN KEY (channelId) REFERENCES channels(channelId) ON DELETE CASCADE,
    FOREIGN KEY (deviceId) REFERENCES devices(deviceId) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE bwmFormulas (
	bwmFormulaId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    deviceId INT UNSIGNED NOT NULL,
    formulaA VARCHAR(64),
    formulaA_offset_top INT,
    formulaA_offset_left INT,
	formulaB VARCHAR(64),
    formulaB_offset_top INT,
    formulaB_offset_left INT,
	formulaC VARCHAR(64),
    formulaC_offset_top INT,
    formulaC_offset_left INT,
	formulaD VARCHAR(64),
    formulaD_offset_top INT,
    formulaD_offset_left INT,
    PRIMARY KEY (bwmFormulaId),
    FOREIGN KEY (deviceId) REFERENCES devices(deviceId) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE deviceMACId (
	_id INT UNSIGNED AUTO_INCREMENT NOT NULL,
    deviceMACId VARCHAR(256),
    deviceId INT UNSIGNED NOT NULL,
    PRIMARY KEY (_id),
    FOREIGN KEY (deviceId) REFERENCES devices(deviceId) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE losantMessageHistory (
	losantMessageHistoryId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    deviceId INT UNSIGNED NOT NULL,
    losantMessage VARCHAR(256),
    PRIMARY KEY (losantMessageHistoryId),
    FOREIGN KEY (deviceId) REFERENCES devices(deviceId) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE losantReadingsBuffer (
	losantReadingsBufferId INT UNSIGNED AUTO_INCREMENT NOT NULL,
    channelName VARCHAR(64),
    measurement VARCHAR(64),
    measurementTime VARCHAR(64),
    macId VARCHAR(64),
    PRIMARY KEY (losantReadingsBufferId)
) ENGINE=InnoDB;

CREATE TABLE ftpTracker (
    trackerId INT PRIMARY KEY DEFAULT 0,
    lastKeyNum INT NOT NULL DEFAULT 0
);
INSERT INTO ftpTracker(lastKeyNum) VALUES (0);

CREATE TABLE mqttTracker (
    trackerId INT PRIMARY KEY DEFAULT 0,
    lastKeyNum INT NOT NULL DEFAULT 0
);
INSERT INTO mqttTracker(lastKeyNum) VALUES (0);

CREATE TABLE pwdReset (
    pwdResetId INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    pwdResetEmail VARCHAR(128) NOT NULL,
    selector VARCHAR(64) NOT NULL,
    token VARCHAR(256) NOT NULL,
    expires DATETIME NOT NULL
);
