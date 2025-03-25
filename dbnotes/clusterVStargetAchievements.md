/* 1) Drop any old version first */
DROP VIEW IF EXISTS `vw_cluster_vs_target_achievements`;

/* 2) Create the new, comprehensive view */
CREATE ALGORITHM=UNDEFINED
DEFINER=`root`@`localhost`
SQL SECURITY DEFINER
VIEW `vw_cluster_vs_target_achievements` AS
SELECT
    /* ---------- CLUSTER INFO ---------- */
    c.id AS cluster_pk,
    c.ClusterID AS cluster_code,
    c.Cluster_Name AS cluster_name,
    
    /* ---------- INDICATOR INFO ---------- */
    pi.id AS indicator_pk,
    pi.Indicator_Number AS indicator_number,
    pi.Indicator_Name AS indicator_name,
    pi.ResponseType AS indicator_response_type,
    
    /* ---------- TIMELINE INFO ---------- */
    t.id AS timeline_pk,
    t.ReportName AS timeline_name,
    t.Year AS timeline_year,
    t.Quarter AS timeline_quarter,
    t.ClosingDate AS timeline_closing_date,
    t.status AS timeline_status,
    
    /* ---------- TARGET INFO ---------- */
    cit.id AS cluster_target_pk,
    cit.Target_Year AS target_year_string,
    cit.Target_Value AS target_value_raw,

    /* ------------------------------------------------------------------
       1) total_actual_value:
          Summation logic depends on the indicator type:

          - NUMBER:
             Sums numeric cpm.Response if it is a valid numeric string
          - YES/NO or BOOLEAN:
             If CIT target is "Yes"/"True" => total_actual_value = 1 if
               any actual is "Yes" or "True", else 0
             If CIT target is "No"/"False" => similarly
             Otherwise => 0
          - TEXT:
             Not aggregated => 0
       ------------------------------------------------------------------ */
    CAST(
      SUM(
        CASE
          WHEN pi.ResponseType = 'Number' THEN
            /* Summation of numeric values if they pass the regex check */
            CASE 
              WHEN (cpm.ResponseType = 'Number'
                    AND cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$')
              THEN CAST(cpm.Response AS DECIMAL(20,4))
              ELSE 0
            END

          WHEN pi.ResponseType IN ('Yes/No','Boolean') THEN
            /*
               For yes/no/boolean: we check if the target_value is "Yes" or "No",
               then we see if ANY actual matches that. Because we are summing
               over possibly many cpm rows, we only want "1" if ANY row matched.

               We do that by "max" logic inside a SUM, effectively:
                 - If CIT target says "Yes"/"True" => 
                     we convert each row to (1 if cpm.Response in('Yes','True') else 0),
                     then the sum of those can be >0 if any row was yes => we want "1" total
                     so we use MIN(1, sum(...) ) eventually or "CASE WHEN sum>0 THEN 1 else 0".
               We'll do it with a single pass: see "yesMatchValue" below.
            */
            CASE
              WHEN (
                (cit.Target_Value IN ('Yes','True'))
                AND (cpm.Response IN ('Yes','True'))
              ) THEN 1

              WHEN (
                (cit.Target_Value IN ('No','False'))
                AND (cpm.Response IN ('No','False'))
              ) THEN 1

              ELSE 0
            END

          ELSE
            /* TEXT or anything else => 0 */
            0
        END
      ) AS DECIMAL(20,4)
    ) AS sum_of_raw_actual_before_clamp,

    /* ------------------------------------------------------------------
       2) We also need to do a "0/1" clamp for yes/no in the final step:
          We'll handle that in the next columns.
       ------------------------------------------------------------------ */

    /* ------------------------------------------------------------------
       3) target_value_for_calc:
          The numeric target we divide by. Logic:
          - NUMBER => if target_value is numeric => that numeric
                      else 0
          - YES/NO or BOOLEAN => treat target=1 if CIT target is 
                                 "Yes"/"True" or "No"/"False"
          - TEXT => 0
       ------------------------------------------------------------------ */
    CASE
      WHEN pi.ResponseType = 'Number' THEN
        CASE
          WHEN cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
          THEN CAST(cit.Target_Value AS DECIMAL(20,4))
          ELSE 0
        END

      WHEN pi.ResponseType IN ('Yes/No','Boolean') THEN
        CASE
          WHEN cit.Target_Value IN ('Yes','True','No','False')
          THEN 1
          ELSE 0
        END

      ELSE
        /* For text => 0 so the ratio is 0/0 => final is 0% */
        0
    END AS target_value_for_calc,

    /* ------------------------------------------------------------------
       4) total_actual_value (final):
          For yes/no we only want 0 or 1 total if ANY row matched the 
          condition. We'll handle that with "MIN(1, sum_of_flags)" logic.
       ------------------------------------------------------------------ */
    /* We'll do the final clamp in an outer expression. See next columns. */
    
    /* ------------------------------------------------------------------
       We'll provide a final "total_actual_value" that is forcibly
       0 or 1 for yes/no, and the numeric sum for numbers, 
       ignoring text:

       We'll do it with a sub-CASE on sum_of_raw_actual_before_clamp.
    ------------------------------------------------------------------ */
    CASE
      WHEN pi.ResponseType = 'Number'
      THEN CAST(
        SUM(
          CASE 
            WHEN cpm.ResponseType = 'Number'
                 AND cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
            THEN CAST(cpm.Response AS DECIMAL(20,4))
            ELSE 0
          END
        ) 
        AS DECIMAL(20,4)
      )

      WHEN pi.ResponseType IN ('Yes/No','Boolean') THEN
        /* If sum_of_raw_actual_before_clamp > 0 => that means
           at least one row matched => final=1
           else 0
        */
        CASE 
          WHEN 
            SUM(
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
            ) > 0
          THEN 1
          ELSE 0
        END

      ELSE
        /* TEXT => 0 */
        0
    END 
    AS total_actual_value,

    /* ------------------------------------------------------------------
       5) PERCENTAGE OF TARGET ACHIEVED (CAPPED [0..100])
          ratio = total_actual_value / target_value_for_calc * 100
    ------------------------------------------------------------------ */
    CASE
      WHEN (
        CASE
          WHEN pi.ResponseType = 'Number'
               AND cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
          THEN CAST(cit.Target_Value AS DECIMAL(20,4))
          
          WHEN pi.ResponseType IN ('Yes/No','Boolean')
               AND cit.Target_Value IN ('Yes','True','No','False')
          THEN 1

          ELSE 0
        END
      ) = 0
      THEN 0  /* if the 'target_value_for_calc' is 0 => ratio=0 */
      ELSE LEAST(
             100,
             GREATEST(
               0,
               (
                 /* The final actual value from the yes/no clamp above */
                 CASE
                   WHEN pi.ResponseType = 'Number'
                   THEN CAST(
                     SUM(
                       CASE 
                         WHEN cpm.ResponseType='Number'
                              AND cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                         THEN CAST(cpm.Response AS DECIMAL(20,4))
                         ELSE 0
                       END
                     ) AS DECIMAL(20,4)
                   )
                   WHEN pi.ResponseType IN ('Yes/No','Boolean') THEN
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
                       ) > 0
                       THEN 1
                       ELSE 0
                     END
                   ELSE
                     0
                 END
                 /
                 CASE
                   WHEN pi.ResponseType = 'Number'
                        AND cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                   THEN CAST(cit.Target_Value AS DECIMAL(20,4))
                   WHEN pi.ResponseType IN ('Yes/No','Boolean')
                        AND cit.Target_Value IN ('Yes','True','No','False')
                   THEN 1
                   ELSE 0
                 END
               ) * 100
             )
           )
    END AS achievement_percent,

    /* ------------------------------------------------------------------
       6) STATUS LABEL 
         - <10% => "Needs Attention"
         - <50% => "In Progress"
         - <90% => "On Track"
         - else => "Met"
    ------------------------------------------------------------------ */
    CASE
      WHEN (
        CASE
          /* same ratio logic as above */
          WHEN pi.ResponseType = 'Number'
               AND cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
          THEN LEAST(
                 100,
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
                   ) * 100
                 )
               )
          WHEN pi.ResponseType IN ('Yes/No','Boolean')
               AND cit.Target_Value IN ('Yes','True','No','False')
          THEN 
            /* either 0% or 100% */
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
              ) > 0
              THEN 100
              ELSE 0
            END
          ELSE
            0
        END
      ) < 10 THEN 'Needs Attention'

      WHEN (
        CASE
          /* same ratio logic */
          WHEN pi.ResponseType = 'Number'
               AND cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
          THEN LEAST(
                 100,
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
                   ) * 100
                 )
               )
          WHEN pi.ResponseType IN ('Yes/No','Boolean')
               AND cit.Target_Value IN ('Yes','True','No','False')
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
              ) > 0
              THEN 100
              ELSE 0
            END
          ELSE
            0
        END
      ) < 50 THEN 'In Progress'

      WHEN (
        CASE
          /* same ratio logic */
          WHEN pi.ResponseType = 'Number'
               AND cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
          THEN LEAST(
                 100,
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
                   ) * 100
                 )
               )
          WHEN pi.ResponseType IN ('Yes/No','Boolean')
               AND cit.Target_Value IN ('Yes','True','No','False')
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
              ) > 0
              THEN 100
              ELSE 0
            END
          ELSE
            0
        END
      ) < 90 THEN 'On Track'
      ELSE 'Met'
    END AS status_label

FROM
    /* We no longer filter to numeric only; handle all response types */
    cluster_indicator_targets AS cit
    JOIN performance_indicators AS pi
      ON pi.id = CAST(cit.IndicatorID AS UNSIGNED)
     AND pi.id <> 0
     /* Must confirm the cluster is responsible for the indicator */
     AND JSON_CONTAINS(pi.Responsible_Cluster, JSON_QUOTE(cit.ClusterID)) = 1
    
    /* Join clusters */
    JOIN clusters AS c
      ON c.ClusterID = cit.ClusterID
    
    /* Join timeline with year-range logic */
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
    
    /* Performance (LEFT) to gather actuals */
    LEFT JOIN cluster_performance_mappings AS cpm
      ON cpm.ClusterID   = cit.ClusterID
     AND cpm.IndicatorID = cit.IndicatorID
     AND cpm.ReportingID = t.ReportingID

GROUP BY 
    c.id, c.ClusterID, c.Cluster_Name,
    pi.id, pi.Indicator_Number, pi.Indicator_Name, pi.ResponseType,
    t.id, t.ReportName, t.Year, t.Quarter, t.ClosingDate, t.status,
    cit.id, cit.Target_Year, cit.Target_Value
;
