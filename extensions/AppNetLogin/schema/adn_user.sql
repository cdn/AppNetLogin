-- prefix ?
CREATE TABLE IF NOT EXISTS `adn_user` ( 
	`x_user_id` int(10) unsigned NOT NULL, 
	`x_adn_id` varchar(255) NOT NULL,
	`x_adn_username` varchar(20) not null,
	PRIMARY KEY  (`x_user_id`),
	UNIQUE KEY `x_adn_id` (`x_adn_id`)
); 
