USE NewEcsaRewportingV3;

CREATE VIEW vw_cluster_indicator_targets_full AS
SELECT
    cit.id                                       AS target_pk,
    cit.ClusterTargetID,
    cit.ClusterID                                AS cluster_id_db,
    cit.IndicatorID                              AS target_indicator_id,
    cit.Target_Year,
    cit.Target_Value,
    cit.ResponseType                             AS target_response_type,
    cit.Baseline2024,
    cit.created_at                               AS target_created_at,
    cit.updated_at                               AS target_updated_at,

    c.id                                         AS cluster_pk,
    c.Description                                AS cluster_description,
    c.Cluster_Name                               AS cluster_name,
    c.created_at                                 AS cluster_created_at,
    c.updated_at                                 AS cluster_updated_at,
    c.ClusterID                                  AS cluster_text_identifier

FROM cluster_indicator_targets cit
JOIN clusters c
   ON cit.ClusterID = c.ClusterID
;


CREATE VIEW vw_cluster_performance_mappings_full AS
SELECT
    cpm.id                                       AS performance_pk,
    cpm.ClusterID                                AS cluster_id_db,
    cpm.ReportingID,
    cpm.SO_ID,
    cpm.UserID,
    cpm.IndicatorID,
    cpm.Response,
    cpm.ReportingComment,
    cpm.ResponseType                             AS performance_response_type,
    cpm.Baseline_2023_2024,
    cpm.Target_Year1,
    cpm.Target_Year2,
    cpm.Target_Year3,
    cpm.created_at                               AS performance_created_at,
    cpm.updated_at                               AS performance_updated_at,

    c.id                                         AS cluster_pk,
    c.Description                                AS cluster_description,
    c.Cluster_Name                               AS cluster_name,
    c.created_at                                 AS cluster_created_at,
    c.updated_at                                 AS cluster_updated_at,
    c.ClusterID                                  AS cluster_text_identifier,

    so.id                                        AS so_pk,
    so.SO_Name,
    so.Description                               AS so_description,
    so.SO_Number,
    so.StrategicObjectiveID,
    so.created_at                                AS so_created_at,
    so.updated_at                                AS so_updated_at,

    t.id                                         AS timeline_pk,
    t.ReportName,
    t.Type                                       AS timeline_type,
    t.Description                                AS timeline_description,
    t.ReportingID                                AS timeline_reporting_id,
    t.Year                                       AS timeline_year,
    t.ClosingDate                                AS timeline_closing_date,
    t.status                                     AS timeline_status,
    t.created_at                                 AS timeline_created_at,
    t.updated_at                                 AS timeline_updated_at,
    t.Quarter                                    AS timeline_quarter,

    u.id                                         AS user_pk,
    u.name                                       AS user_name,
    u.email                                      AS user_email,
    u.ClusterID                                  AS user_cluster_id,
    u.UserType                                   AS user_type,
    u.UserCode                                   AS user_code,
    u.Phone                                      AS user_phone,
    u.Nationality                                AS user_nationality,
    u.Address                                    AS user_address,
    u.ParentOrganization                         AS user_parent_org,
    u.Sex                                        AS user_sex,
    u.JobTitle                                   AS user_job_title,
    u.AccountRole                                AS user_account_role,
    u.created_at                                 AS user_created_at,
    u.updated_at                                 AS user_updated_at

FROM cluster_performance_mappings cpm
JOIN clusters c
   ON cpm.ClusterID = c.ClusterID
LEFT JOIN strategic_objectives so
   ON cpm.SO_ID = so.SO_Number
LEFT JOIN ecsahc_timelines t
   ON cpm.ReportingID = t.ReportingID
LEFT JOIN users u
   ON cpm.UserID = u.UserID
;


CREATE VIEW vw_indicator_target_and_performance_full AS
SELECT
    cpm.id                                       AS performance_pk,
    cpm.ClusterID                                AS performance_cluster_id_db,
    cpm.ReportingID,
    cpm.SO_ID,
    cpm.UserID,
    cpm.IndicatorID                              AS performance_indicator_id,
    cpm.Response,
    cpm.ReportingComment,
    cpm.ResponseType                             AS performance_response_type,
    cpm.Baseline_2023_2024,
    cpm.Target_Year1,
    cpm.Target_Year2,
    cpm.Target_Year3,
    cpm.created_at                               AS performance_created_at,
    cpm.updated_at                               AS performance_updated_at,

    cit.id                                       AS target_pk,
    cit.ClusterTargetID,
    cit.IndicatorID                              AS target_indicator_id,
    cit.Target_Year,
    cit.Target_Value,
    cit.ResponseType                             AS target_response_type,
    cit.Baseline2024,
    cit.created_at                               AS target_created_at,
    cit.updated_at                               AS target_updated_at,

    c.id                                         AS cluster_pk,
    c.Description                                AS cluster_description,
    c.Cluster_Name                               AS cluster_name,
    c.created_at                                 AS cluster_created_at,
    c.updated_at                                 AS cluster_updated_at,
    c.ClusterID                                  AS cluster_text_identifier,

    so.id                                        AS so_pk,
    so.SO_Name,
    so.Description                               AS so_description,
    so.SO_Number,
    so.StrategicObjectiveID,
    so.created_at                                AS so_created_at,
    so.updated_at                                AS so_updated_at,

    t.id                                         AS timeline_pk,
    t.ReportName,
    t.Type                                       AS timeline_type,
    t.Description                                AS timeline_description,
    t.ReportingID                                AS timeline_reporting_id,
    t.Year                                       AS timeline_year,
    t.ClosingDate                                AS timeline_closing_date,
    t.status                                     AS timeline_status,
    t.created_at                                 AS timeline_created_at,
    t.updated_at                                 AS timeline_updated_at,
    t.Quarter                                    AS timeline_quarter,

    u.id                                         AS user_pk,
    u.name                                       AS user_name,
    u.email                                      AS user_email,
    u.ClusterID                                  AS user_cluster_id,
    u.UserType                                   AS user_type,
    u.UserCode                                   AS user_code,
    u.Phone                                      AS user_phone,
    u.Nationality                                AS user_nationality,
    u.Address                                    AS user_address,
    u.ParentOrganization                         AS user_parent_org,
    u.Sex                                        AS user_sex,
    u.JobTitle                                   AS user_job_title,
    u.AccountRole                                AS user_account_role,
    u.created_at                                 AS user_created_at,
    u.updated_at                                 AS user_updated_at

FROM cluster_performance_mappings cpm
JOIN cluster_indicator_targets cit
    ON cpm.ClusterID = cit.ClusterID
   AND cpm.IndicatorID = cit.IndicatorID
JOIN clusters c
   ON cpm.ClusterID = c.ClusterID
LEFT JOIN strategic_objectives so
   ON cpm.SO_ID = so.SO_Number
LEFT JOIN ecsahc_timelines t
   ON cpm.ReportingID = t.ReportingID
LEFT JOIN users u
   ON cpm.UserID = u.UserID
;
