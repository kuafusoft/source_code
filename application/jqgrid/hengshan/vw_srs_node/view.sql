drop view vw_srs_node_history;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_srs_node_history` AS 
select 
`history`.`id` AS `id`,
`prj`.`name` AS `project`,
`srs_category`.`code` AS `category`,
`srs_node`.`code` AS `code`,
`srs_node_info`.`content` AS `content`,
`srs_node_info`.`updated` AS `updated`,
`srs_node_info`.`creater_id` AS `creater_id`,
link_status.name as link_status,
history.link_status_id,
`srs_node`.`isactive` AS `isactive`,
`srs_node`.`id` AS `srs_node_id`,
`srs_node`.`srs_category_id` AS `srs_category_id`,
`history`.`srs_node_info_id` AS `srs_node_info_id`,
`history`.`prj_id` AS `prj_id`,
history.id as prj_srs_node_info_history_id
 
from prj_srs_node_info_history history
left join `srs_node_info` on `history`.`srs_node_info_id` = `srs_node_info`.`id`
left join `srs_node` on `srs_node_info`.`srs_node_id` = `srs_node`.`id` 
left join `srs_category` on `srs_node`.`srs_category_id` = `srs_category`.`id`
left join `prj` on `history`.`prj_id` = `prj`.`id`
left join link_status on history.link_status_id=link_status.id
where 1;

DROP VIEW vw_srs_node;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_srs_node` AS 
select 
`link`.`id` AS `id`,
`vw`.`project`,
`vw`.`category`,
`vw`.`code`,
`vw`.`content` AS `content`,
`vw`.`srs_node_id`,
`vw`.`srs_category_id`,
`vw`.`updated` AS `updated`,
`vw`.`creater_id` AS `creater_id`,
vw.link_status,
vw.link_status_id,
`vw`.`isactive` AS `isactive`,
`vw`.`srs_node_info_id` AS `srs_node_info_id`,
link.prj_srs_node_info_history_id,
link.`prj_id` AS `prj_id`
 
from `prj_srs_node_info` `link` 
left join vw_srs_node_history vw ON (link.prj_srs_node_info_history_id=vw.id AND link.srs_node_id=vw.srs_node_id AND link.prj_id=vw.prj_id)
where 1 and vw.link_status_id=1;
