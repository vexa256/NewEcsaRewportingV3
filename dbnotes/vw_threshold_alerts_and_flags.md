-- Drop the view if it already exists
DROP VIEW IF EXISTS `vw_threshold_alerts_and_flags`;

-- Create the new view that returns only red-flag (underperforming or completely unreported) indicators.
-- It is based on the vw_cluster_vs_target_achievements view, which already computes the achievement percentage.
-- We only return rows where the target is valid (non-zero) and the achievement percentage is below 10%.
-- In our logic, if no valid performance was reported (actual = 0) then achievement_percent becomes 0 and is flagged.
CREATE ALGORITHM=UNDEFINED
DEFINER=`root`@`localhost`
SQL SECURITY DEFINER
VIEW `vw_threshold_alerts_and_flags` AS
SELECT
cluster_pk,
cluster_code,
cluster_name,
indicator_pk,
indicator_number,
indicator_name,
indicator_response_type,
timeline_pk,
timeline_name,
timeline_year,
timeline_quarter,
timeline_closing_date,
timeline_status,
cluster_target_pk,
target_year_string,
target_value_raw,
total_actual_value,
achievement_percent,
status_label,
/_
The alert_flag column gives an immediate label.
Here we flag any indicator with an achievement below 10% (including 0, i.e. completely unreported).
_/
CASE
WHEN achievement_percent < 10 THEN 'Needs Attention'
ELSE ''
END AS alert_flag
FROM vw_cluster_vs_target_achievements
WHERE
-- Only consider indicators that have a valid (non-zero) target value.
CAST(IFNULL(target_value_raw, '0') AS DECIMAL(20,4)) > 0
AND achievement_percent < 10;
