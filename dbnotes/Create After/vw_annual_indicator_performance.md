DROP VIEW IF EXISTS `vw_annual_indicator_performance`;

CREATE ALGORITHM=UNDEFINED
DEFINER=`root`@`localhost`
SQL SECURITY DEFINER
VIEW `vw_annual_indicator_performance` AS
SELECT
derived.cluster_pk,
derived.cluster_code,
derived.cluster_name,
derived.so_pk,
derived.so_number,
derived.so_name,
derived.indicator_pk,
derived.indicator_number,
derived.indicator_name,
derived.indicator_response_type,
derived.timeline_year,
GROUP_CONCAT(DISTINCT derived.timeline_name
ORDER BY derived.timeline_quarter
SEPARATOR ', ') AS reporting_periods,
COUNT(DISTINCT derived.timeline_id) AS num_periods,
SUM(derived.total_actual_value) AS annual_actual,
SUM(derived.total_target_value) AS annual_target,
CASE
WHEN SUM(derived.total_target_value) = 0 THEN 0
ELSE ROUND((SUM(derived.total_actual_value) / SUM(derived.total_target_value)) _ 100, 2)
END AS achievement_percent,
CASE
WHEN SUM(derived.total_target_value) = 0 THEN 'No Valid Target'
WHEN ROUND((SUM(derived.total_actual_value) / SUM(derived.total_target_value)) _ 100, 2) < 10 THEN 'Needs Attention'
WHEN ROUND((SUM(derived.total_actual_value) / SUM(derived.total_target_value)) _ 100, 2) < 50 THEN 'Progressing'
WHEN ROUND((SUM(derived.total_actual_value) / SUM(derived.total_target_value)) _ 100, 2) < 90 THEN 'On Track'
ELSE 'Met'
END AS status_label
FROM (
SELECT
c.id AS cluster_pk,
c.ClusterID AS cluster_code,
c.Cluster_Name AS cluster_name,
so.id AS so_pk,
so.SO_Number AS so_number,
so.SO_Name AS so_name,
pi.id AS indicator_pk,
pi.Indicator_Number AS indicator_number,
pi.Indicator_Name AS indicator_name,
pi.ResponseType AS indicator_response_type,
t.Year AS timeline_year,
t.id AS timeline_id,
t.ReportName AS timeline_name,
t.Quarter AS timeline_quarter,
-- Calculate the actual reported value per reporting period:
CASE
WHEN pi.ResponseType = 'Number'
THEN CAST(SUM(
CASE
WHEN cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$' 
               THEN CAST(cpm.Response AS DECIMAL(20,4)) 
               ELSE 0 
             END
           ) AS DECIMAL(20,4))
      WHEN pi.ResponseType IN ('Yes/No','Boolean')
      THEN CASE WHEN SUM(
             CASE 
               WHEN cpm.Response IN ('Yes','True') THEN 1 
               ELSE 0 
             END
           ) > 0 THEN 1 ELSE 0 END
      ELSE 0
    END AS total_actual_value,
    -- Calculate the target value for the indicator:
    CASE 
      WHEN pi.ResponseType = 'Number'
      THEN CAST(
             CASE 
               WHEN cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
THEN cit.Target_Value
ELSE '0'
END AS DECIMAL(20,4))
WHEN pi.ResponseType IN ('Yes/No','Boolean')
THEN CASE WHEN cit.Target_Value IN ('Yes','True','No','False') THEN 1 ELSE 0 END
ELSE 0
END AS total_target_value
FROM cluster_indicator_targets cit
JOIN clusters c ON cit.ClusterID = c.ClusterID
JOIN performance_indicators pi
ON pi.id = CAST(cit.IndicatorID AS UNSIGNED)
AND pi.id <> 0
AND JSON_CONTAINS(pi.Responsible_Cluster, JSON_QUOTE(c.ClusterID)) = 1
JOIN ecsahc_timelines t
ON t.status IN ('In Progress','Completed')
AND (
((cit.Target_Year LIKE '%-%')
AND t.Year BETWEEN CAST(SUBSTRING_INDEX(cit.Target_Year,'-',1) AS UNSIGNED)
AND CAST(SUBSTRING_INDEX(cit.Target_Year,'-',-1) AS UNSIGNED))
OR ((NOT(cit.Target_Year LIKE '%-%'))
AND t.Year = CAST(cit.Target_Year AS UNSIGNED))
)
LEFT JOIN cluster_performance_mappings cpm
ON cpm.ClusterID = cit.ClusterID
AND cpm.IndicatorID = cit.IndicatorID
AND cpm.ReportingID = t.ReportingID
LEFT JOIN strategic_objectives so
ON so.SO_Number = pi.SO_ID
GROUP BY c.id, so.id, pi.id, t.id, cit.Target_Value, pi.ResponseType
) AS derived
GROUP BY derived.cluster_pk, derived.so_pk, derived.indicator_pk, derived.timeline_year;
