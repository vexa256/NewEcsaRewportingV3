DROP VIEW IF EXISTS `vw_cluster_rank_semiannual`;

CREATE ALGORITHM=UNDEFINED
DEFINER=`root`@`localhost`
SQL SECURITY DEFINER
VIEW `vw_cluster_rank_semiannual` AS
/**********************************\*\*\***********************************
Outer Query:

-   Ranks clusters by "final_score_percent" within each (year, half).
-   Provides "Needs Attention", "Progressing", "On Track", "Met".
-   "Over Achieved" comment if aggregated actual > target and target>0.

The subselect 'sums' calculates an averaged fraction (0..100) for each
cluster's half-year, plus sums of actual/target to detect overachieving.
**********************************\*\*\***********************************/
SELECT
sums.cluster_pk,
sums.cluster_code,
sums.cluster_name,
sums.year_val AS timeline_year,
CASE WHEN sums.half=1 THEN 'First Semi Annual'
ELSE 'Second Semi Annual'
END AS semi_annual_label,

/**********************\*\*\*\***********************

1. final_score_percent => aggregated (0..100)
   **********************\*\*\*\***********************/
   sums.final_score_percent,

/**********************\*\*\*\*********************** 2) rank => window function for each half-year
**********************\*\*\*\***********************/
DENSE_RANK() OVER (
PARTITION BY sums.year_val, sums.half
ORDER BY sums.final_score_percent DESC
) AS cluster_rank,

/**********************\*\*\*\*********************** 3) status_label => strictly <10 => "Needs Attention",
<50 => "Progressing", <90 => "On Track", else "Met"
**********************\*\*\*\***********************/
CASE
WHEN sums.final_score_percent < 10 THEN 'Needs Attention'
WHEN sums.final_score_percent < 50 THEN 'Progressing'
WHEN sums.final_score_percent < 90 THEN 'On Track'
ELSE 'Met'
END AS status_label,

/**********************\*\*\*\*********************** 4) comment => "Over Achieved" if sum_actual>sum_target>0
otherwise blank
**********************\*\*\*\***********************/
CASE
WHEN sums.sum_target_val>0
AND sums.sum_actual_val> sums.sum_target_val
THEN 'Over Achieved'
ELSE ''
END AS comment

FROM
(
/********************************\*\*\*\*********************************
Subselect "sums" => aggregates cluster performance for each half: - Q1+Q2 => half=1, Q3+Q4 => half=2 - Averages the fraction(0..1) across all indicators => final_score_percent - Also sums actual vs. target for Over Achieved detection
********************************\*\*\*\*********************************/
SELECT
frac.cluster_pk,
frac.cluster_code,
frac.cluster_name,
frac.year_val,
CASE WHEN frac.quarter_val IN (1,2) THEN 1 ELSE 2 END AS half,

    /* Sum over actual & target across all indicators in that half
       so we can see if sum_actual>sum_target => Over Achieved. */
    SUM(frac.sum_actual_val) AS sum_actual_val,
    SUM(frac.sum_target_val) AS sum_target_val,

    /* final_score_percent => average of fraction(0..1)*100
       ignoring NULL (text indicators). */
    CAST(AVG(frac.indicator_fraction) * 100 AS DECIMAL(6,2)) AS final_score_percent

FROM
(
/_ ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Deep sub-subselect: (cluster, year, quarter, indicator)
=> sum of actual & target, plus fraction(0..1)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ _/
SELECT
c.id AS cluster_pk,
c.ClusterID AS cluster_code,
c.Cluster_Name AS cluster_name,
t.Year AS year_val,
t.Quarter AS quarter_val,

      /* We'll store numeric sums for detection of overachieving. */
      /* sum_actual_val => 0..∞, sum_target_val => 0..∞ */
      CASE
        WHEN pi.ResponseType='Number' THEN
          CAST(
            SUM(
              CASE
                WHEN cpm.ResponseType='Number'
                     AND cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                THEN CAST(cpm.Response AS DECIMAL(20,4))
                ELSE 0
              END
            )
            AS DECIMAL(20,4)
          )
        WHEN pi.ResponseType IN ('Yes/No','Boolean') THEN
          CASE
            /* If CIT target=Yes => 1 if any actual=Yes, else 0 */
            WHEN SUM(
              CASE
                WHEN (
                  (cit.Target_Value IN ('Yes','True'))
                  AND (cpm.Response IN ('Yes','True'))
                ) OR (
                  (cit.Target_Value IN ('No','False'))
                  AND (cpm.Response IN ('No','False'))
                )
                THEN 1
                ELSE 0
              END
            )>0
            THEN 1
            ELSE 0
          END
        ELSE
          0
      END AS sum_actual_val,

      CASE
        WHEN pi.ResponseType='Number'
             AND cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
        THEN CAST(cit.Target_Value AS DECIMAL(20,4))
        WHEN pi.ResponseType IN ('Yes/No','Boolean')
             AND cit.Target_Value IN ('Yes','True','No','False')
        THEN 1
        ELSE
          0
      END AS sum_target_val,

      /* fraction(0..1) or NULL if text. */
      CASE
        WHEN pi.ResponseType='Number' THEN
          CASE
            WHEN cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                 AND CAST(cit.Target_Value AS DECIMAL(20,4))>0
            THEN LEAST(
                   1,
                   GREATEST(
                     0,
                     (
                       CAST(
                         SUM(
                           CASE
                             WHEN cpm.ResponseType='Number'
                                  AND cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                             THEN CAST(cpm.Response AS DECIMAL(20,4))
                             ELSE 0
                           END
                         ) AS DECIMAL(20,4)
                       )
                       /
                       CAST(cit.Target_Value AS DECIMAL(20,4))
                     )
                   )
                 )
            ELSE
              0
          END
        WHEN pi.ResponseType IN ('Yes/No','Boolean') THEN
          CASE
            WHEN cit.Target_Value IN ('Yes','True','No','False')
            THEN
              CASE
                WHEN SUM(
                  CASE
                    WHEN (
                      (cit.Target_Value IN ('Yes','True'))
                      AND (cpm.Response IN ('Yes','True'))
                    ) OR (
                      (cit.Target_Value IN ('No','False'))
                      AND (cpm.Response IN ('No','False'))
                    )
                    THEN 1
                    ELSE 0
                  END
                )>0
                THEN 1
                ELSE 0
              END
            ELSE
              0
          END
        ELSE
          /* Text => NULL => doesn't affect average. */
          NULL
      END AS indicator_fraction

    FROM
      cluster_indicator_targets AS cit
      JOIN performance_indicators AS pi
        ON pi.id = CAST(cit.IndicatorID AS UNSIGNED)
       AND pi.id<>0
       AND JSON_CONTAINS(pi.Responsible_Cluster, JSON_QUOTE(cit.ClusterID))=1

      JOIN clusters AS c
        ON c.ClusterID = cit.ClusterID

      JOIN ecsahc_timelines AS t
        ON t.status IN ('In Progress','Completed')
       AND (
            (
              cit.Target_Year LIKE '%-%'
              AND t.Year BETWEEN
                  CAST(SUBSTRING_INDEX(cit.Target_Year,'-',1) AS UNSIGNED)
                  AND
                  CAST(SUBSTRING_INDEX(cit.Target_Year,'-',-1) AS UNSIGNED)
            )
            OR (
              NOT(cit.Target_Year LIKE '%-%')
              AND t.Year = CAST(cit.Target_Year AS UNSIGNED)
            )
          )

      LEFT JOIN cluster_performance_mappings AS cpm
        ON cpm.ClusterID   = cit.ClusterID
       AND cpm.IndicatorID = cit.IndicatorID
       AND cpm.ReportingID = t.ReportingID

    GROUP BY
      c.id, c.ClusterID, c.Cluster_Name,
      t.Year, t.Quarter,
      pi.id, pi.ResponseType,
      cit.Target_Value

) AS frac

/_ Next grouping => cluster + half + year => average fraction, sum actual/target. _/
GROUP BY
frac.cluster_pk,
frac.cluster_code,
frac.cluster_name,
frac.year_val,
CASE WHEN frac.quarter_val IN (1,2) THEN 1 ELSE 2 END
) AS sums

/_ Sort so you see best to worst within each half-year. _/
ORDER BY
sums.year_val,
sums.half,
sums.final_score_percent DESC;
