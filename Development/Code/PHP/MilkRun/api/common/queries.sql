select 
    pb.pickupboy,
    manifest_id,
    company_id,
    pb.pickupboy_id,
    max(download_date) download_date,
    max(dispatch_date) dispatch_date
from
    (select  * from clues_pickupboy where imei is not null and imei != '') pb
    left outer join
    (SELECT 
        manifest_id,
            company_id,
            pickupboy_id,
            case
                when status = 'Download' then status_date
            end as download_date,
            case
                when status = 'Closed' then status_date
            end as dispatch_date
    FROM
        clues_mri_manifest mf

) manifest ON pb.pickupboy_id = manifest.pickupboy_id
group by manifest_id , company_id , pickupboy_id;
