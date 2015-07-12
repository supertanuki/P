CREATE TABLE IF NOT EXISTS `pp_matches_cron` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_league` int(11) NOT NULL,
  `url_type` varchar(30) NOT NULL,
  `url_id` int(11) NOT NULL,
  `day_number` int(11) NOT NULL,
  `increment` int(11) NOT NULL,
  `enabled` varchar(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `pp_matches_cron` (`id`, `id_league`, `url_type`, `url_id`, `day_number`, `increment`, `enabled`) VALUES
(5, 1, 'URL_RESULTAT', 52207, 3, 1, '1'),
(4, 1, 'URL_RESULTAT', 52205, 1, 1, '1'),
(6, 1, 'URL_RESULTAT', 52206, 2, 1, '1'),
(7, 1, 'URL_RESULTAT', 52208, 4, 35, '1'),
(8, 3, 'URL_RESULTAT', 52243, 1, 38, '1'),
(10, 2, 'URL_RESULTAT', 52167, 1, 38, '1'),
(11, 8, 'URL_RESULTAT', 52642, 1, 34, '1');

UPDATE `pp_config` SET `value` = '2015' WHERE `pp_config`.`param` = 'saison_en_cours';

INSERT INTO `pp_team` (`id_team`, `id_league`, `label`, `xlabels`, `flag`, `featured`, `nb_points_sanction`) VALUES (NULL, '3', 'Bourg-Péronnas', 'Bourg-Péronnas', '', '', '0');

INSERT INTO `pp_team` (`id_team`, `id_league`, `label`, `xlabels`, `flag`, `featured`, `nb_points_sanction`) VALUES (NULL, '2', 'Bournemouth', 'Bournemouth', '', '', '0'), (NULL, '2', 'Watford', 'Watford', '', '', '0');

INSERT INTO `pp_team` (`id_team`, `id_league`, `label`, `xlabels`, `flag`, `featured`, `nb_points_sanction`) VALUES (NULL, '8', 'SV Darmstadt', 'SV Darmstadt', '', '', '0'), (NULL, '8', 'Ingolstadt 04', 'Ingolstadt 04', '', '', '0');
