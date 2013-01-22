-- --------------------------------------------------------------------------------
-- Routine DDL
-- Note: comments before and after the routine body will not be stored by the server
-- --------------------------------------------------------------------------------
-- FEE001		p_fee_config_code
-- 10,16,17,19,22       p_merchants_need_to_process
-- e			p_merchant_process_type  comnay need to exclude e or include i
-- 2012-12-15		p_from_date
-- 2013-01-01		p_to_date
-- 56,78,90		p_ex_order_ids_al   always excluded
-- 56,90,09		p_ex_order_ids_on   once excluded
-- t			p_run_type           run type test t /Final f
DELIMITER $$
drop procedure if exists clues_billing_fee_sp $$
CREATE PROCEDURE `clues_billing_fee_sp`
(IN p_fee_config_code varchar(512),IN p_merchants_need_to_process text,IN p_merchant_process_type char(1), IN p_from_date date, IN p_to_date date,IN p_ex_order_ids_al text,IN p_ex_order_ids_on text,IN p_run_type char)
BEGIN  
declare v_company_id , v_amount, v_order_id, v_product, v_qty  bigint;
declare no_more_data , no_more_fee,no_more_order_ids tinyint default 0;
declare v_billing_category varchar(20);
declare v_tax_rate,v_subtotal, v_calculated_fee,v_calc_fee, v_tax, v_total_fee decimal(8,2);
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

declare v_order_per cursor for SELECT order_id FROM  clues_order_exclude;


-- Declare cusrsor to list all orders in given billing cycle period
declare order_data cursor for 

select 
    cc.company_id,
    co.order_id,
    sum(co.subtotal) subtotal,
    sum(cod.amount) cartsize,
	date(from_unixtime(co.timestamp)) order_date

from
    cscart_companies cc
        join
    cscart_orders co ON co.company_id = cc.company_id
        join
    cscart_order_details cod ON co.order_id = cod.order_id
        join
    (select distinct
        order_id
    from
        clues_order_history
    where
        from_unixtime(transition_date) between p_from_date and p_to_date
            and concat(',', v_status_applicable, ',') like concat('%,', to_status, ',%') 
	    and order_id not in (CONCAT(p_ex_order_ids_on, if(p_ex_order_ids_on != '',","," "),v_p_order_ids))) coh ON co.order_id = coh.order_id
where    1 = 1
     and cc.fulfillment_id in (v_fulfillment_ids) and (
        if  (   p_merchant_process_type = 'e', 
                concat(',', p_merchants_need_to_process, ',') not like concat('%,', cc.company_id, ',%'),
                concat(',', p_merchants_need_to_process, ',') like concat('%,', cc.company_id, ',%')
            )
    )
	group by  cc.company_id,
    co.order_id
	 ;


-- concat( ',',inCustomerIdList,',' ) like concat( '%,',customer_id,',%' )
-- CUSRSOR TO LIST ALL ACTIVE FEE CODES
-- coh.to_status  LIKE  QUOTE(concat('%',v_status_applicable,'%'))
-- select code , type , amount , status_applicable , tax_rate , unit , fulfillment_ids , from_date, to_date
-- from clues_billing_fee_config 
-- where fee_status = 'A';

declare c_fee_rules cursor for 
SELECT code , type , amount , status_applicable , tax_rate , unit , fulfillment_ids , from_date, to_date 
FROM  clues_billing_fee_config 
WHERE code = p_fee_config_code;

-- CONTINUE HANDLER FOR END OF FEES
declare continue handler for not found set no_more_fee = 1;

-- insert into clues_logs (tag,message) values ('DEBUG',v_fulfillment_ids);


-- spilting order ids need to exclude for test run permanent 


-- concat( ',',inCustomerIdList,',' ) like concat( '%,',customer_id,',%' )
-- CUSRSOR TO LIST ALL ACTIVE FEE CODES
-- coh.to_status  LIKE  QUOTE(concat('%',v_status_applicable,'%'))
-- select code , type , amount , status_applicable , tax_rate , unit , fulfillment_ids , from_date, to_date
-- from clues_billing_fee_config 
-- where fee_status = 'A';


set p_ex_order_ids_al = CONCAT(p_ex_order_ids_al,",");
set v_id := locate(',',p_ex_order_ids_al);
set v_prev_id := 1;
    WHILE v_id > 0 DO
        set v_order_id := substr(p_ex_order_ids_al,v_prev_id,v_id-v_prev_id);
        REPLACE INTO clues_order_exclude (order_id) values (v_order_id);
        set v_prev_id := v_id+1;
        set v_id := locate(',',p_ex_order_ids_al,v_prev_id);
    END WHILE;


-- End Split



open c_fee_rules;
	block2 : begin
          -- CONTINUE HANDLER FOR END OF ORDERS PER
        declare continue handler for not found set no_more_order_ids = 1;
        SET v_p_order_ids = '';
        open v_order_per;
            repeat
                fetch v_order_per INTO v_order_id_ex; 
                    if not no_more_order_ids then
                        SET v_p_order_ids = CONCAT(v_p_order_ids,",",v_order_id_ex);
                    end if;
                until no_more_order_ids
            end  repeat;
            SET v_p_order_ids = SUBSTRING(v_p_order_ids,2);
        close v_order_per;
	end block2;
	repeat
		fetch c_fee_rules into v_code, v_type, v_amount, v_status_applicable, v_tax_rate, 
		v_unit, v_fulfillment_ids, v_from_date, v_to_date ;
		if not no_more_fee then
        insert into clues_logs (tag,message) values ('DEBUG',CONCAT(v_fulfillment_ids , v_status_applicable));

			set no_more_data = 0;			
			open order_data;
				block3 : begin
					declare continue handler for not found set no_more_data = 1;
					repeat 
						fetch order_data into v_company_id , v_order_id, v_subtotal, v_cartsize, v_order_date ;
					if not no_more_data then
                            if p_run_type = 't' THEN
                                SET v_run_type = 0;
                            elseif p_run_type = 'f' THEN
                                SET v_run_type = 1;
                            end if;
					-- DELETE FEE DETAILS FOR CURRENT FEE IN GIVEN BILLING CYCLE IF BILLING IS NOT FREEZED
					delete from clues_billing_fee_details 
					where order_id = v_order_id and fee_code = v_code and billing = v_run_type; -- 'N';
					-- RESET ORDER DATA HANDLER TO 0 ON EACH FEE TYPE

					-- DELETE FEE SUMMARY FOR GIVEN BILLING CYCLE PERIOD
					delete from clues_billing_total_fee 
					where billing_cycle = v_order_date and company_id = v_company_id and fee_type = v_code;

					SET v_cid = 0;
					-- CHECK IF FEE IS ALREADY APPLIED ON AN ORDER AND BILLING CYCLE IS FREEZED
					select count(company_id) INTO v_cid from clues_billing_fee_details WHERE order_id = v_order_id and fee_code = v_code;
 						-- IF BILLING IS NOT DONE EARLIER THIS ORDER
						if v_cid = 0 then
							-- IF FEE IS APPLICABLE IN BILLING CYCLE ON THIS ORDER
								  insert into clues_logs (tag,message) values ('DEBUG',CONCAT('Fee : ',v_code ,' Order ID : ', v_order_id, ' Order Date', v_order_date ));
							set v_calc_fee = 0;
							if v_unit='I' and v_type='F' then
								set v_calc_fee = v_amount * v_cartsize;
							elseif v_unit='O' and v_type='F' then
								set v_calc_fee = v_amount ;
							elseif v_type='P' then
								set v_calc_fee = v_amount*v_subtotal/100 ;	
							end if;

							set v_tax = (v_calc_fee*v_tax_rate)/100.0;
							
							insert into clues_billing_fee_details (company_id,order_id,fee_code,fee_unit,qty,calculated_fee,calculated_tax,total_fee,billing_cycle,billing)
							values( v_company_id, v_order_id, v_code, v_unit, v_cartsize, v_calc_fee,v_tax, v_calc_fee + v_tax, v_order_date ,v_run_type);
						else
							 -- IF FEE IS ALREADY APPLIED THEN LOG AN ERROR MESSAGE AND CONTINUE		
						  insert into clues_logs (tag,message) values ('ERROR',CONCAT('Fee : ',v_code ,' already applied on Order ID : ', v_order_id ));
					
						end if;  -- Check for duplicate fee if ends

						end if;  -- no more data if ends
						until no_more_data
					end  repeat;
				end block3;
			close order_data;
		end if;
	until no_more_fee
	end  repeat;
close c_fee_rules;

-- POPULATE BILLING SUMMARY TABLE FROM BILLING FEE DETAILS FOR GIVEN BILLING CYCLE
INSERT INTO clues_billing_total_fee (company_id,fee_type,calculated_fee,calculated_tax,total_fee,billing_cycle)
select company_id, fee_code, sum(calculated_fee) calculated_fee, sum(calculated_tax) calculated_tax, sum(total_fee) total_fee, billing_cycle
from clues_billing_fee_details
where 1=1
group by company_id, fee_code, billing_cycle;
END $$
