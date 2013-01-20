select 
	pb.pickupboy,
	mf.manifest_id,
	mf.company_id,
	mfd.company_name,
	pb.pickupboy_id,
	max(mf.download_date) download_date,
	max(mf.dispatch_date) dispatch_date,
	max(mf.login_dt) login_dt,
	max(mf.logout_dt) logout_dt,
	SUM(mfd.expected_qty) expected_qty,
	SUM(mfd.received_qty) received_qty
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
            end as dispatch_date,
            case
                when status = 'logout' then status_date
            end as logout_dt,
			case
                when status = 'login' then status_date
            end as logint_dt
    FROM
        clues_mri_manifest mf

) mf ON pb.pickupboy_id = mf.pickupboy_id
left outer join clues_mri_manifest_details_mobile_app mfd
on mfd.manifest_id = mf.manifest_id and mf.company_id=mfd.merchant_id
group by mf.manifest_id , mf.company_id , pb.pickupboy_id
