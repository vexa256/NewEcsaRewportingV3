CREATE OR REPLACE VIEW vw_so_indicators_report AS
SELECT
/_ 1) STRATEGIC OBJECTIVE FIELDS _/
so.id AS so_pk,
so.SO_Number AS so_number,
so.SO_Name AS so_name,

    /* 2) INDICATOR FIELDS */
    pi.id               AS indicator_id,
    pi.Indicator_Number,
    pi.Indicator_Name,
    pi.ResponseType     AS indicator_type,  /* 'Number' or 'Yes/No' */

    /* 3) CLUSTER FIELDS */
    c.id                AS cluster_pk,
    c.ClusterID         AS cluster_code,
    c.Cluster_Name      AS cluster_name,

    /* 4) TIMELINE FIELDS */
    t.id                AS timeline_pk,
    t.ReportName        AS timeline_name,
    t.Year              AS timeline_year,
    t.Type              AS timeline_type,
    t.status            AS timeline_status,

    /* 5) USER (Reporter) FIELDS */
    u.id               AS user_pk,
    u.name             AS user_name,
    u.email            AS user_email,

    /* 6) TARGET & ACTUAL FIELDS */
    cit.id             AS cluster_target_pk,
    cit.Target_Year    AS target_year_string,
    cit.Target_Value   AS target_value_raw,

    /* Show the first or any user-entered raw response. Using ANY_VALUE(...)
       avoids ONLY_FULL_GROUP_BY errors for non-aggregated columns. */
    ANY_VALUE(cpm.Response) AS user_entered_value,

    /* 7) AGGREGATED ACTUAL */
    CASE
      WHEN pi.ResponseType='Number'
      THEN CAST(
             COALESCE(
               SUM(
                 CASE
                   WHEN cpm.ResponseType='Number'
                        AND cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                   THEN CAST(cpm.Response AS DECIMAL(20,4))
                   ELSE 0
                 END
               ),
               0
             )
           AS DECIMAL(20,4)
           )
      WHEN pi.ResponseType='Yes/No'
      THEN CAST(
             COALESCE(
               MAX(
                 CASE
                   WHEN cpm.Response IN ('Yes','True') THEN 1
                   ELSE 0
                 END
               ),
               0
             )
           AS DECIMAL(20,4)
           )
      ELSE 0
    END
    AS aggregated_actual_value,

    /* 8) SCORE PERCENT (Clamp 0..100) */
    CASE
      WHEN pi.id=0
           OR cit.IndicatorID='0'
           OR cit.Target_Value IS NULL
           OR cit.Target_Value=''
           OR cit.Target_Value='0'
      THEN 0.0

      WHEN pi.ResponseType='Number'
      THEN
        LEAST(
          100,
          GREATEST(
            0,
            (
              COALESCE(
                SUM(
                  CASE
                    WHEN cpm.ResponseType='Number'
                         AND cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                    THEN CAST(cpm.Response AS DECIMAL(20,4))
                    ELSE 0
                  END
                ),
                0
              )
              / CAST(cit.Target_Value AS DECIMAL(20,4))
            ) * 100
          )
        )

      WHEN pi.ResponseType='Yes/No'
      THEN
        CASE
          WHEN cit.Target_Value IN ('Yes','True')
          THEN
            CASE
              WHEN MAX( CASE WHEN cpm.Response IN ('Yes','True') THEN 1 ELSE 0 END ) >=1
              THEN 100
              ELSE 0
            END
          WHEN cit.Target_Value IN ('No','False')
          THEN
            CASE
              WHEN MAX( CASE WHEN cpm.Response IN ('No','False') THEN 1 ELSE 0 END ) >=1
              THEN 100
              ELSE 0
            END
          ELSE 0
        END
      ELSE
        0
    END
    AS score_percent,

    /* 9) STATUS LABEL (<10 => Needs Attention, <50 => In Progress, <90 => On Track, else => Met) */
    CASE
      WHEN pi.id=0
           OR cit.IndicatorID='0'
           OR cit.Target_Value IS NULL
           OR cit.Target_Value=''
           OR cit.Target_Value='0'
      THEN 'No Valid Target (Flag)'

      ELSE
        CASE
          WHEN
            CASE
              WHEN pi.ResponseType='Number'
              THEN
                LEAST(
                  100,
                  GREATEST(
                    0,
                    (
                      COALESCE(
                        SUM(
                          CASE
                            WHEN cpm.ResponseType='Number'
                                 AND cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                            THEN CAST(cpm.Response AS DECIMAL(20,4))
                            ELSE 0
                          END
                        ),
                        0
                      )
                      / CAST(cit.Target_Value AS DECIMAL(20,4))
                    ) * 100
                  )
                )
              WHEN pi.ResponseType='Yes/No'
              THEN
                CASE
                  WHEN cit.Target_Value IN ('Yes','True')
                  THEN
                    CASE
                      WHEN MAX( CASE WHEN cpm.Response IN ('Yes','True') THEN 1 ELSE 0 END )>=1
                      THEN 100
                      ELSE 0
                    END
                  WHEN cit.Target_Value IN ('No','False')
                  THEN
                    CASE
                      WHEN MAX( CASE WHEN cpm.Response IN ('No','False') THEN 1 ELSE 0 END )>=1
                      THEN 100
                      ELSE 0
                    END
                  ELSE 0
                END
              ELSE 0
            END
            < 10
          THEN 'Needs Attention'

          WHEN
            CASE
              WHEN pi.ResponseType='Number'
              THEN
                LEAST(
                  100,
                  GREATEST(
                    0,
                    (
                      COALESCE(
                        SUM(
                          CASE
                            WHEN cpm.ResponseType='Number'
                                 AND cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                            THEN CAST(cpm.Response AS DECIMAL(20,4))
                            ELSE 0
                          END
                        ),
                        0
                      )
                      / CAST(cit.Target_Value AS DECIMAL(20,4))
                    ) *100
                  )
                )
              WHEN pi.ResponseType='Yes/No'
              THEN
                CASE
                  WHEN cit.Target_Value IN ('Yes','True')
                  THEN
                    CASE
                      WHEN MAX( CASE WHEN cpm.Response IN ('Yes','True') THEN 1 ELSE 0 END )>=1
                      THEN 100
                      ELSE 0
                    END
                  WHEN cit.Target_Value IN ('No','False')
                  THEN
                    CASE
                      WHEN MAX( CASE WHEN cpm.Response IN ('No','False') THEN 1 ELSE 0 END )>=1
                      THEN 100
                      ELSE 0
                    END
                  ELSE 0
                END
              ELSE 0
            END
            < 50
          THEN 'In Progress'

          WHEN
            CASE
              WHEN pi.ResponseType='Number'
              THEN
                LEAST(
                  100,
                  GREATEST(
                    0,
                    (
                      COALESCE(
                        SUM(
                          CASE
                            WHEN cpm.ResponseType='Number'
                                 AND cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                            THEN CAST(cpm.Response AS DECIMAL(20,4))
                            ELSE 0
                          END
                        ),
                        0
                      )
                      / CAST(cit.Target_Value AS DECIMAL(20,4))
                    ) *100
                  )
                )
              WHEN pi.ResponseType='Yes/No'
              THEN
                CASE
                  WHEN cit.Target_Value IN ('Yes','True')
                  THEN
                    CASE
                      WHEN MAX( CASE WHEN cpm.Response IN ('Yes','True') THEN 1 ELSE 0 END )>=1
                      THEN 100
                      ELSE 0
                    END
                  WHEN cit.Target_Value IN ('No','False')
                  THEN
                    CASE
                      WHEN MAX( CASE WHEN cpm.Response IN ('No','False') THEN 1 ELSE 0 END )>=1
                      THEN 100
                      ELSE 0
                    END
                  ELSE 0
                END
              ELSE 0
            END
            < 90
          THEN 'On Track'

          ELSE 'Met'
        END
    END
    AS status_label,

    /* 10) Explanation of how the formula was computed */
    CASE
      WHEN pi.ResponseType='Number'
      THEN 'score = sum_of_numeric_responses / target_value * 100, threshold (<10, <50, <90, >=90)'
      WHEN pi.ResponseType='Yes/No'
      THEN 'score = 100 if any response matches the yes/no target, else 0. threshold (<10, <50, <90, >=90)'
      ELSE 'Not applicable here.'
    END
    AS formula_explanation

FROM cluster_indicator_targets AS cit
JOIN performance_indicators AS pi
ON pi.id = CAST(cit.IndicatorID AS UNSIGNED)
AND pi.id <> 0
AND JSON_CONTAINS(pi.Responsible_Cluster, JSON_QUOTE(cit.ClusterID))=1

JOIN clusters AS c
ON c.ClusterID = cit.ClusterID

JOIN ecsahc_timelines AS t
ON t.status IN ('In Progress','Completed')
AND (
( cit.Target_Year LIKE '%-%'
AND t.Year BETWEEN
CAST(SUBSTRING_INDEX(cit.Target_Year,'-',1) AS UNSIGNED)
AND
CAST(SUBSTRING_INDEX(cit.Target_Year,'-',-1) AS UNSIGNED)
)
OR
( cit.Target_Year NOT LIKE '%-%'
AND t.Year = CAST(cit.Target_Year AS UNSIGNED)
)
)

LEFT JOIN cluster_performance_mappings AS cpm
ON cpm.ClusterID = cit.ClusterID
AND cpm.IndicatorID = cit.IndicatorID
AND cpm.ReportingID = t.ReportingID

LEFT JOIN strategic_objectives AS so
ON so.SO_Number = pi.SO_ID

LEFT JOIN users AS u
ON cpm.UserID = u.UserID

WHERE
cit.IndicatorID <> '0'
AND pi.ResponseType IN ('Number','Yes/No')

GROUP BY
so.id,
so.SO_Number,
so.SO_Name,
pi.id,
pi.Indicator_Number,
pi.Indicator_Name,
pi.ResponseType,
c.id,
c.ClusterID,
c.Cluster_Name,
t.id,
t.ReportName,
t.Year,
t.Type,
t.status,
cit.id,
cit.Target_Year,
cit.Target_Value,
cit.IndicatorID,
u.id,
u.name,
u.email;
