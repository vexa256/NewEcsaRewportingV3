--
-- Structure for view `vw_semi_annual_performance`
--
DROP TABLE IF EXISTS `vw_semi_annual_performance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_semi_annual_performance` AS SELECT `base`.`cluster_pk` AS `cluster_pk`, `base`.`cluster_code` AS `cluster_code`, `base`.`cluster_name` AS `cluster_name`, `base`.`so_pk` AS `so_pk`, `base`.`so_number` AS `so_number`, `base`.`so_name` AS `so_name`, `base`.`indicator_pk` AS `indicator_pk`, `base`.`indicator_number` AS `indicator_number`, `base`.`indicator_name` AS `indicator_name`, `base`.`indicator_response_type` AS `indicator_response_type`, `base`.`year_val` AS `timeline_year`, (case when (`base`.`semi_annual_half` = 1) then 'First Semi Annual' else 'Second Semi Annual' end) AS `semi_annual_label`, `base`.`total_actual_value` AS `raw_actual_value`, `base`.`total_target_value` AS `raw_target_value`, (case when (`base`.`total_target_value` <= 0) then 0 else least(100,greatest(0,((`base`.`total_actual_value` / `base`.`total_target_value`) _ 100))) end) AS `achievement_percent`, (case when (`base`.`total_target_value` <= 0) then 'No Valid Target' else (case when (((`base`.`total_actual_value` / `base`.`total_target_value`) _ 100) < 10) then 'Needs Attention' when (((`base`.`total_actual_value` / `base`.`total_target_value`) _ 100) < 50) then 'In Progress' when (((`base`.`total_actual_value` / `base`.`total_target_value`) _ 100) < 90) then 'On Track' else 'Met' end) end) AS `status_label`, (case when (`base`.`total_actual_value` > `base`.`total_target_value`) then 'Over Achieved' else '' end) AS `comment` FROM (select `c`.`id` AS `cluster_pk`,`c`.`ClusterID` AS `cluster_code`,`c`.`Cluster_Name` AS `cluster_name`,`pi`.`id` AS `indicator_pk`,`pi`.`Indicator_Number` AS `indicator_number`,`pi`.`Indicator_Name` AS `indicator_name`,`pi`.`ResponseType` AS `indicator_response_type`,`so`.`id` AS `so_pk`,`so`.`SO_Number` AS `so_number`,`so`.`SO_Name` AS `so_name`,`t`.`Year` AS `year_val`,(case when (`t`.`Quarter` in (1,2)) then 1 else 2 end) AS `semi_annual_half`,(case when (`pi`.`ResponseType` = 'Number') then cast(sum((case when ((`cpm`.`ResponseType` = 'Number') and regexp_like(`cpm`.`Response`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(`cpm`.`Response` as decimal(20,4)) else 0 end)) as decimal(20,4)) when (`pi`.`ResponseType` in ('Yes/No','Boolean')) then (case when (sum((case when (((`cit`.`Target_Value` in ('Yes','True')) and (`cpm`.`Response` in ('Yes','True'))) or ((`cit`.`Target_Value` in ('No','False')) and (`cpm`.`Response` in ('No','False')))) then 1 else 0 end)) > 0) then 1 else 0 end) else 0 end) AS `total_actual_value`,(case when ((`pi`.`ResponseType` = 'Number') and regexp_like(`cit`.`Target_Value`,'^-?[0-9]+(\\.[0-9]+)?$')) then cast(sum(cast(`cit`.`Target_Value` as decimal(20,4))) as decimal(20,4)) when ((`pi`.`ResponseType` in ('Yes/No','Boolean')) and (`cit`.`Target_Value` in ('Yes','True','No','False'))) then 1 else 0 end) AS `total_target_value` from (((((`cluster_indicator_targets` `cit` join `performance_indicators` `pi` on(((`pi`.`id` = cast(`cit`.`IndicatorID` as unsigned)) and (`pi`.`id` <> 0) and (json_contains(`pi`.`Responsible_Cluster`,json_quote(`cit`.`ClusterID`)) = 1)))) left join `strategic_objectives` `so` on((`so`.`SO_Number` = `pi`.`SO_ID`))) join `clusters` `c` on((`c`.`ClusterID` = `cit`.`ClusterID`))) join `ecsahc_timelines` `t` on(((`t`.`status` in ('In Progress','Completed')) and (((`cit`.`Target_Year` like '%-%') and (`t`.`Year` between cast(substring_index(`cit`.`Target_Year`,'-',1) as unsigned) and cast(substring_index(`cit`.`Target_Year`,'-',-(1)) as unsigned))) or ((not((`cit`.`Target_Year` like '%-%'))) and (`t`.`Year` = cast(`cit`.`Target_Year` as unsigned))))))) left join `cluster_performance_mappings` `cpm` on(((`cpm`.`ClusterID` = `cit`.`ClusterID`) and (`cpm`.`IndicatorID` = `cit`.`IndicatorID`) and (`cpm`.`ReportingID` = `t`.`ReportingID`)))) group by `c`.`id`,`c`.`ClusterID`,`c`.`Cluster_Name`,`pi`.`id`,`pi`.`Indicator_Number`,`pi`.`Indicator_Name`,`pi`.`ResponseType`,`so`.`id`,`so`.`SO_Number`,`so`.`SO_Name`,`t`.`Year`,(case when (`t`.`Quarter` in (1,2)) then 1 else 2 end)) AS `base` ;

---
