-- --------------------------------------------------------------------------------
-- Routine DDL
-- Note: comments before and after the routine body will not be stored by the server
-- --------------------------------------------------------------------------------
DELIMITER $$
drop PROCEDURE if exists `clues_billing_fee_sp` $$
CREATE PROCEDURE `clues_billing_fee_sp` 
(IN p_fee_config_code varchar(512),
	IN p_merchants_need_to_process text,
	IN p_merchant_process_type char(1), 
	IN p_from_date date, IN p_to_date date,IN p_ex_order_ids_al text,
	IN p_ex_order_ids_on text,IN p_run_type char)
BEGIN  

declare v_company_id , v_amount, v_order_id, v_product_id, v_item_id, v_qty  bigint;
declare no_more_data , no_more_fee,no_more_order_ids tinyint default 0;
declare v_billing_category varchar(20);
declare v_tax_rate,v_subtotal,v_amount1, v_fee_before_tax,v_calc_fee, v_tax, v_fee_after_tax decimal(14,4);
declare v_cartsize int;
declare v_cid int DEFAULT 0;
declare v_from_date, v_to_date, v_order_date date;
declare v_code, v_type, v_status_applicable,  v_unit varchar(20);
declare v_fulfillment_ids varchar(256);
declare v_id,v_prev_id int;
declare v_oreder_id varchar(10);
declare v_run_type int(1) default 0;
declare v_order_id_ex int;
declare v_p_order_ids text;
declare in_ex_oredr_ids text;
declare v_billing_cycle varchar(255);


declare order_data cursor for
select
    co.company_id,
    co.order_id,
        co.product_id,
        co.item_id,
    co.subtotal,
    co.cartsize,
        co.order_date,
        co.billing_category
from clues_billing_order_details_int co
where fulfillment_id in (v_fulfillment_ids) ;

declare c_fee_rules cursor for
SELECT code , type , amount , status_applicable , tax_rate , unit , fulfillment_ids , from_date, to_date
FROM  clues_billing_fee_config
WHERE code = p_fee_config_code ;


declare continue handler for not found set no_more_fee = 1;


set v_billing_cycle = concat(p_to_date, '-PackingFee');


truncate table clues_order_exclude_int;
truncate table clues_billing_fee_details_int;




set p_ex_order_ids_on = CONCAT(p_ex_order_ids_on,",");
set v_id := locate(',',p_ex_order_ids_on);
set v_prev_id := 1;
    WHILE v_id > 0 DO
        set v_order_id := substr(p_ex_order_ids_on,v_prev_id,v_id-v_prev_id);
        REPLACE INTO clues_order_exclude_int (order_id, exclude_type) values (v_order_id, 0);
        set v_prev_id := v_id+1;
        set v_id := locate(',',p_ex_order_ids_al,v_prev_id);
END WHILE;


set p_ex_order_ids_al = CONCAT(p_ex_order_ids_al,",");
set v_id := locate(',',p_ex_order_ids_al);
set v_prev_id := 1;
    WHILE v_id > 0 DO
        set v_order_id := substr(p_ex_order_ids_al,v_prev_id,v_id-v_prev_id);
        REPLACE INTO clues_order_exclude_int (order_id, exclude_type) values (v_order_id, 1);
        set v_prev_id := v_id+1;
        set v_id := locate(',',p_ex_order_ids_al,v_prev_id);
END WHILE;




drop table if exists clues_billing_companies_int;
create table clues_billing_companies_int as select  distinct cc.company_id, cc.company, cc.fulfillment_id from
    cscart_companies cc
        inner join
    clues_warehouse_contact w ON w.company_id = cc.company_id and w.region_code = 2
        and if  (p_merchant_process_type = 'e',
                concat(',', p_merchants_need_to_process, ',') not like concat('%,', cc.company_id, ',%'),
                concat(',', p_merchants_need_to_process, ',') like concat('%,', cc.company_id, ',%')
            );


drop table if exists clues_billing_orders_int;
create table clues_billing_orders_int as

select distinct
    coh.order_id
from
    clues_order_history coh
        inner join
    (select distinct
        order_id
    from
        clues_mri_receive_details d
    where completed = 'Y') rd ON rd.order_id = coh.order_id
where
     date(from_unixtime(transition_date)) between p_from_date  and p_to_date
    and concat(',', 'G', ',') like concat('%,', to_status, ',%')
and coh.order_id not in (
        select order_id from clues_order_exclude_int
        union
        select order_id from clues_order_exclude
);


drop table if exists clues_billing_order_details_int;
create table clues_billing_order_details_int
as select
        co.company_id,
    cc.fulfillment_id,
        co.order_id,
    sum(co.subtotal) subtotal,
    cod.item_id,
        cod.product_id,
    sum(cod.amount) cartsize,
         (select c.billing_category from
    cscart_products_categories pc
        inner join
    cscart_categories c ON c.category_id = pc.category_id
        and billing_category  not in (0,1)
where pc.product_id = cod.product_id
limit 0,1
) as billing_category,
        date(from_unixtime(co.timestamp)) order_date
 from cscart_orders co inner join clues_billing_companies_int cc on cc.company_id = co.company_id
        inner join clues_billing_orders_int ot on ot.order_id = co.order_id
        inner join cscart_order_details cod ON co.order_id = cod.order_id



group by cod.item_id, co.company_id, co.order_id;






if p_run_type = 't' THEN
        SET v_run_type = 0;
elseif p_run_type = 'f' THEN
        SET v_run_type = 1;
end if;







open c_fee_rules;
        repeat
                fetch c_fee_rules into v_code, v_type, v_amount, v_status_applicable, v_tax_rate,
                v_unit, v_fulfillment_ids, v_from_date, v_to_date ;
                if not no_more_fee then
                insert into clues_logs (tag,message) values ('DEBUG',CONCAT(v_fulfillment_ids , v_status_applicable));
                set no_more_data = 0;

                delete from clues_billing_fee_details where 1=1 and fee_code = v_code and billing = 0;

                delete from clues_billing_total_fee where billing = 0 and fee_type = v_code;

                        open order_data;
                                block3 : begin
                                        declare continue handler for not found set no_more_data = 1;
                                        repeat
                                                fetch order_data into v_company_id , v_order_id,v_product_id,v_item_id, v_subtotal, v_cartsize, v_order_date, v_billing_category ;
                                        if not no_more_data then

                                        insert into clues_logs (tag,message) values ('ORDER',CONCAT(v_order_id , v_code));





                                        SET v_cid = 0;

                                        select count(company_id) INTO v_cid from clues_billing_fee_details WHERE order_id = v_order_id and fee_code = v_code;

                                                if v_cid = 0 then

                                                                  insert into clues_logs (tag,message) values ('DEBUG',CONCAT('Fee : ',v_code ,' Order ID : ', v_order_id, ' Order Date', v_order_date ));
                                                        set v_calc_fee = 0;


                                                        if v_billing_category = 32 then
                                                                set v_amount1 =7;
                                                        else
                                                                set v_amount1 =v_amount;
                                                        end if;

                                                        if v_unit='I' and v_type='F' then
                                                                set v_calc_fee = v_amount1 * v_cartsize;
                                                        elseif v_unit='O' and v_type='F' then
                                                                set v_calc_fee = v_amount ;
                                                        elseif v_type='P' then
                                                                set v_calc_fee = v_amount*v_subtotal/100 ;
                                                        end if;

                                                        set v_tax = (v_calc_fee*v_tax_rate)/100.0;

                                                        insert into clues_billing_fee_details_int (company_id,order_id,
                                                product_id,item_id,billing_category,
                                                fee_code,fee_unit,qty,fee_before_tax, tax_rate,
                                                tax_amount,fee_after_tax,order_date,billing, billing_cycle)
                                                        values( v_company_id, v_order_id,v_product_id,v_item_id,v_billing_category, v_code, v_unit,
                                v_cartsize, v_calc_fee,v_tax_rate, v_tax, v_calc_fee + v_tax, v_order_date ,v_run_type, v_billing_cycle);
                                                else

                                                  insert into clues_logs (tag,message) values ('ERROR',CONCAT('Fee : ',v_code ,' already applied on Order ID : ', v_order_id ));

                                                end if;

                                                end if;
                                                until no_more_data
                                        end  repeat;
                                end block3;
                        close order_data;
                end if;
        until no_more_fee
        end  repeat;
close c_fee_rules;


INSERT INTO clues_billing_total_fee (company_id,fee_type,fee_before_tax,tax_amount,fee_after_tax,billing, run_date, billing_cycle)
select company_id, fee_code, sum(fee_before_tax) fee_before_tax, sum(tax_amount) tax_amount,
sum(fee_after_tax) fee_after_tax, v_run_type,  CURRENT_DATE, v_billing_cycle
from clues_billing_fee_details_int
where 1=1
group by company_id, fee_code;

insert INTO clues_billing_fee_details select * from clues_billing_fee_details_int;

if v_run_type=1 then
	REPLACE INTO clues_order_exclude (order_id)  select order_id from clues_order_exclude_int where exclude_type!=0;

	insert into clues_billing_payout_summary (billing_cycle,company_id,merchant, sum_net_payout, Packing_Fee, summary_type )
	select v_billing_cycle, fd.company_id, cc.company, 0-sum(fee_after_tax), sum(fee_after_tax) , 'PackingFee'
		from clues_billing_fee_details_int fd inner join clues_billing_companies_int cc on fd.company_id = cc.company_id
		where 1=1
		group by company_id, fee_code;

end if;

END $$
delimiter ;


