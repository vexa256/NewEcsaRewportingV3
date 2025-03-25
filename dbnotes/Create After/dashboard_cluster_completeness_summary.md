DROP VIEW IF EXISTS `dashboard_cluster_completeness_summary`;
CREATE ALGORITHM=UNDEFINED
DEFINER=`root`@`localhost`
SQL SECURITY DEFINER
VIEW `dashboard_cluster_completeness_summary` AS
SELECT
c.id AS cluster*pk,
c.ClusterID AS cluster_text_identifier,
c.Cluster_Name AS cluster_name,
c.Description AS cluster_description,
t.id AS timeline_pk,
t.ReportName AS timeline_name,
t.Type AS timeline_type,
t.Description AS timeline_description,
t.ReportingID AS timeline_reporting_id,
t.Year AS timeline_year,
t.Quarter AS timeline_quarter,
t.ClosingDate AS timeline_closing_date,
t.status AS timeline_status,
COUNT(*) AS total*indicators,
SUM(CASE WHEN cpm.id IS NOT NULL THEN 1 ELSE 0 END) AS reported_indicators,
SUM(CASE WHEN cpm.id IS NULL THEN 1 ELSE 0 END) AS not_reported_indicators,
CASE
WHEN COUNT(*) = 0 THEN 0
ELSE CAST((100.0 _ SUM(CASE WHEN cpm.id IS NOT NULL THEN 1 ELSE 0 END)) / COUNT(_) AS DECIMAL(5,2))
END AS completeness_percentage,
GROUP_CONCAT(DISTINCT t.ReportingID ORDER BY t.ReportingID SEPARATOR ', ') AS considered_reports
FROM clusters c
JOIN cluster_indicator_targets cit
ON c.ClusterID = cit.ClusterID
JOIN performance_indicators pi
ON pi.id = CAST(cit.IndicatorID AS UNSIGNED)
AND pi.id <> 0
AND JSON_CONTAINS(pi.Responsible_Cluster, JSON_QUOTE(c.ClusterID)) = 1
JOIN ecsahc_timelines t
ON t.status IN ('In Progress','Completed')
AND (
(cit.Target_Year LIKE '%-%'
AND t.Year BETWEEN CAST(SUBSTRING_INDEX(cit.Target_Year, '-', 1) AS UNSIGNED)
AND CAST(SUBSTRING_INDEX(cit.Target_Year, '-', -1) AS UNSIGNED))
OR (cit.Target_Year NOT LIKE '%-%'
AND t.Year = CAST(cit.Target_Year AS UNSIGNED))
)
LEFT JOIN cluster_performance_mappings cpm
ON cpm.ClusterID = cit.ClusterID
AND cpm.IndicatorID = cit.IndicatorID
AND cpm.ReportingID = t.ReportingID
GROUP BY c.id, c.ClusterID, c.Cluster_Name, c.Description,
t.id, t.ReportName, t.Type, t.Description, t.ReportingID, t.Year, t.Quarter, t.ClosingDate, t.status;
