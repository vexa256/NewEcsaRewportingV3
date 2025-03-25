DROP VIEW IF EXISTS vw_annual_performance_dashboard;
CREATE ALGORITHM=UNDEFINED
DEFINER=`root`@`localhost`
SQL SECURITY DEFINER
VIEW vw_annual_performance_dashboard AS
SELECT
sub.year_val,
sub.cluster_pk,
sub.cluster_code,
sub.cluster_name,
sub.so_pk,
sub.so_number,
sub.so_name,
sub.indicator_pk,
sub.indicator_number,
sub.indicator_name,
SUM(sub.total_actual_value) AS annual_actual,
SUM(sub.total_target_value) AS annual_target,
CASE
WHEN SUM(sub.total_target_value) = 0 THEN 0
ELSE ROUND((SUM(sub.total_actual_value) / SUM(sub.total_target_value)) \* 100, 2)
END AS achievement_percent,
GROUP_CONCAT(DISTINCT sub.timeline_pk ORDER BY sub.timeline_pk SEPARATOR ', ') AS contributing_timelines,
GROUP_CONCAT(DISTINCT sub.ReportName ORDER BY sub.ReportName SEPARATOR ', ') AS contributing_report_names,
COUNT(DISTINCT sub.timeline_pk) AS reporting_periods_count
FROM (
SELECT
t.Year AS year_val,
c.id AS cluster_pk,
c.ClusterID AS cluster_code,
c.Cluster_Name AS cluster_name,
so.id AS so_pk,
so.SO_Number AS so_number,
so.SO_Name AS so_name,
pi.id AS indicator_pk,
pi.Indicator_Number AS indicator_number,
pi.Indicator_Name AS indicator_name,
CASE
WHEN pi.ResponseType = 'Number' THEN
CAST(SUM(
CASE
WHEN cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$' 
                     THEN CAST(cpm.Response AS DECIMAL(20,4)) 
                     ELSE 0 
                  END
               ) AS DECIMAL(20,4))
            WHEN pi.ResponseType IN ('Yes/No','Boolean') THEN 
               CASE WHEN SUM(CASE WHEN cpm.Response IN ('Yes','True') THEN 1 ELSE 0 END) > 0 
                    THEN 1 ELSE 0 END
            ELSE 0 
         END AS total_actual_value,
         CASE 
            WHEN pi.ResponseType = 'Number' 
                 AND cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
THEN CAST(cit.Target_Value AS DECIMAL(20,4))
WHEN pi.ResponseType IN ('Yes/No','Boolean')
AND cit.Target_Value IN ('Yes','True','No','False')
THEN 1
ELSE 0
END AS total_target_value,
t.id AS timeline_pk,
t.ReportName
FROM
cluster_indicator_targets cit
JOIN performance_indicators pi
ON pi.id = CAST(cit.IndicatorID AS UNSIGNED)
JOIN clusters c
ON c.ClusterID = cit.ClusterID
JOIN ecsahc_timelines t
ON t.status IN ('In Progress','Completed')
AND (
(cit.Target_Year LIKE '%-%'
AND t.Year BETWEEN CAST(SUBSTRING_INDEX(cit.Target_Year, '-', 1) AS UNSIGNED)
AND CAST(SUBSTRING_INDEX(cit.Target_Year, '-', -1) AS UNSIGNED))
OR (NOT (cit.Target_Year LIKE '%-%')
AND t.Year = CAST(cit.Target_Year AS UNSIGNED))
)
LEFT JOIN cluster_performance_mappings cpm
ON cpm.ClusterID = cit.ClusterID
AND cpm.IndicatorID = cit.IndicatorID
AND cpm.ReportingID = t.ReportingID
LEFT JOIN strategic_objectives so
ON so.SO_Number = pi.SO_ID
GROUP BY
c.id, c.ClusterID, c.Cluster_Name,
so.id, so.SO_Number, so.SO_Name,
pi.id, pi.Indicator_Number, pi.Indicator_Name,
t.Year, t.id, t.ReportName,
pi.ResponseType, cit.Target_Value
) AS sub
GROUP BY sub.year_val, sub.cluster_pk, sub.so_pk, sub.indicator_pk;
