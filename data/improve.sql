/* Add primary key */
ALTER TABLE `locations_countries` ADD PRIMARY KEY(`id`);

/* Change type to unsigned tinyint(3) because we don't expect more than 255 countries anytime soon, also add AUTO_INCREMENT just in case a few more are inserted */
ALTER TABLE `locations_countries` CHANGE `id` `id` TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT;

/* Make varchar smaller as no countries will have 255 chars anytime soon*/
ALTER TABLE `locations_countries` CHANGE `name` `name` VARCHAR(64);

/* Same as above, only make it varchar(3), although data only has max 2 chars, documentation says we can expect 3 */
ALTER TABLE `locations_countries` CHANGE `code` `code` VARCHAR(3);

/* Same as above although this column should be modified to a smallint and the "+" should be added via php (or client side) coding, but I am not sure I can alter the data that I received */
ALTER TABLE `locations_countries` CHANGE `prefix` `prefix` VARCHAR(5);