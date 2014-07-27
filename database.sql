CREATE TABLE IF NOT EXISTS `domains` (
  `domain_id` bigint(15) NOT NULL AUTO_INCREMENT,
  `url` varchar(1000) NOT NULL,
  `crawled` tinyint(1) NOT NULL,
  `creation_time` bigint(15) NOT NULL,
  `found_last` bigint(15) NOT NULL,
  `md5hash` varchar(32) NOT NULL,
  PRIMARY KEY (`domain_id`),
  UNIQUE KEY `md5hash` (`md5hash`),
  KEY `crawled` (`crawled`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13355758 ;
