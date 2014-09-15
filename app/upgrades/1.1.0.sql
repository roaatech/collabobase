---Preparing files rights
ALTER TABLE `file` ADD `rights` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '||', ADD INDEX(`rights`);

---Modifying old records
UPDATE `file` SET `rights` = '||' WHERE `rights` IS NULL;

---Preparing posts rights
ALTER TABLE `post` ADD `rights` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '||', ADD INDEX(`rights`);

---Modifying old records
UPDATE `post` SET `rights` = '||' WHERE `rights` IS NULL;