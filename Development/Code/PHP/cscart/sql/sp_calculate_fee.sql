drop procedure if exists clues_billing_fee_sp $$

DELIMITER $$ 
CREATE PROCEDURE clues_billing_fee_sp(IN p_in_billing_cycle_start date, IN p_in_billing_cycle_end date) 
BEGIN 
declare v_company_id , v_amount, v_order_id, v_product, v_qty  bigint;
declare v_billing_cycle date;
declare v_status varchar(1);
declare no_more_data , no_more_fee tinyint default 0;
declare v_billing_category varchar(20);
declare v_tax_rate, v_subtotal, v_calculated_fee,v_calc_fee, v_tax, v_total_fee decimal(8,2);
declare v_cartsize int;
declare v_cid int DEFAULT 0;
declare v_from_date, v_to_date date;
declare v_code, v_type, v_status_applicable,  v_unit varchar(20);
declare v_fulfillment_ids varchar(256);
declare order_data cursor for 
select 
    cc.company_id,
    cbod.order_id,
    cbod.subtotal,
    cbod.cartsize,
    cbod.billing_cycle,
    cbod.billing_category,
    cbod.status
from
    cscart_companies cc
        join
    clues_billing_order_data_advance cbod ON cbod.company_id = cc.company_id
        join
    clues_order_history coh ON cbod.order_id = coh.order_id
where cbod.billing_cycle between p_in_billing_cycle_start and p_in_billing_cycle_end
and coh.to_status = v_status_applicable and fulfillment_id in (v_fulfillment_ids);


declare c_fee_rules cursor for 
select code , type , amount , status_applicable , tax_rate , unit , fulfillment_ids , from_date, to_date
from clues_billing_fee_config 
where 1=1
and fee_status = 'A';



declare continue handler for not found set no_more_fee = 1;

-- Delete records from table for curent biling cycle
-- delete from clues_billing_fee_details where billing_cycle between p_in_billing_cycle_start and p_in_billing_cycle_end;
delete from clues_billing_total_fee where billing_cycle between p_in_billing_cycle_start and p_in_billing_cycle_end;


open c_fee_rules;
	repeat
		fetch c_fee_rules into v_code, v_type, v_amount, v_status_applicable, v_tax_rate, 
		v_unit, v_fulfillment_ids, v_from_date, v_to_date ;
		delete from clues_billing_fee_details where billing_cycle between p_in_billing_cycle_start and p_in_billing_cycle_end and fee_code = v_code and billing = 'N';

		insert into clues_logs (tag,message) values ('billing',v_fulfillment_ids);
		if not no_more_fee then
			set no_more_data = 0;
			insert into clues_logs (tag,message) values ('billing',v_code);
		open order_data;
				block2 : begin
					declare continue handler for not found set no_more_data = 1;
					repeat 
						fetch order_data into v_company_id , v_order_id, v_subtotal, v_cartsize , v_billing_cycle, v_billing_category , v_status;
						SET v_cid = 0;
						select count(company_id) INTO v_cid from clues_billing_fee_details WHERE order_id = v_order_id and fee_code = v_code;
-- 						if v_cid = 0
-- 							continue;
-- 						end if;
						insert into clues_logs (tag,message) values ('FulFIllment IDs spilted',REPLACE(QUOTE(v_fulfillment_ids), ',', '\', \''));
						
						if not no_more_data then
							set v_calc_fee = 0;
							if v_unit='I' and v_type='F' then
								set v_calc_fee = v_amount * v_cartsize;
							elseif v_unit='O' and v_type='F' then
								set v_calc_fee = v_amount ;
							end if;

							set v_tax = (v_calc_fee*v_tax_rate)/100.0;
			
							insert into clues_billing_fee_details (company_id,order_id,fee_code,fee_unit,qty,calculated_fee,calculated_tax,total_fee,billing_cycle,billing)
							values( v_company_id, v_order_id, v_code, v_unit, v_cartsize, v_calc_fee,v_tax, v_calc_fee + v_tax, v_billing_cycle ,'N');
						end if;
						until no_more_data
					end  repeat;
				end block2;
			close order_data;
		end if;
	until no_more_fee
	end  repeat;
close c_fee_rules;
INSERT INTO clues_billing_total_fee (company_id,fee_type,calculated_fee,calculated_tax,total_fee,billing_cycle)
select company_id, fee_code, sum(calculated_fee) calculated_fee, sum(calculated_tax) calculated_tax, sum(total_fee) total_fee, billing_cycle
from clues_billing_fee_details
where 1=1
group by company_id, fee_code, billing_cycle;
END $$ 
DELIMITER ; 







delimiter $$

CREATE DEFINER=`root`@`%` PROCEDURE `clues_billing_fee_sp`(IN p_in_billing_cycle_start date, IN p_in_billing_cycle_end date, IN p_in_invoice_billing_cycle varchar(20))
BEGIN 
declare v_company_id , v_amount, v_order_id, v_product, v_qty  bigint;
declare v_billing_cycle date;
declare v_status varchar(1);
declare no_more_data , no_more_fee tinyint default 0;
declare v_billing_category varchar(20);
declare v_tax_rate, v_subtotal, v_calculated_fee,v_calc_fee, v_tax, v_total_fee decimal(8,2);
declare v_cartsize int;
declare v_cid int DEFAULT 0;
declare v_from_date, v_to_date date;
declare v_code, v_type, v_status_applicable,  v_unit varchar(20);
declare v_fulfillment_ids varchar(256);

-- Declare cusrsor to list all orders in given billing cycle period
declare order_data cursor for 
select 
    cc.company_id,
    cbod.order_id,
    cbod.subtotal,
    cbod.cartsize,
    STR_TO_DATE(cbod.billing_cycle, '%d-%m-%y') billing_cycle,
    cbod.billing_category,
    cbod.status
from
    cscart_companies cc
        join
    clues_billing_order_data_advance cbod ON cbod.company_id = cc.company_id
        join
    clues_order_history coh ON cbod.order_id = coh.order_id
where STR_TO_DATE(cbod.billing_cycle, '%d-%m-%y') between p_in_billing_cycle_start and p_in_billing_cycle_end
and concat( ',',v_status_applicable,',' ) like concat( '%,',coh.to_status,',%' ) and fulfillment_id in (v_fulfillment_ids);
-- concat( ',',inCustomerIdList,',' ) like concat( '%,',customer_id,',%' )
-- CUSRSOR TO LIST ALL ACTIVE FEE CODES
-- coh.to_status  LIKE  QUOTE(concat('%',v_status_applicable,'%'))
declare c_fee_rules cursor for 
select code , type , amount , status_applicable , tax_rate , unit , fulfillment_ids , from_date, to_date
from clues_billing_fee_config 
where fee_status = 'A';


-- CONTINUE HANDLER FOR END OF FEES
declare continue handler for not found set no_more_fee = 1;

-- DELETE FEE SUMMARY FOR GIVEN BILLING CYCLE PERIOD
delete from clues_billing_total_fee 
where billing_cycle between p_in_billing_cycle_start and p_in_billing_cycle_end;


open c_fee_rules;
	repeat
		fetch c_fee_rules into v_code, v_type, v_amount, v_status_applicable, v_tax_rate, 
		v_unit, v_fulfillment_ids, v_from_date, v_to_date ;
		if not no_more_fee then
        insert into clues_logs (tag,message) values ('ERROR',CONCAT(v_fulfillment_ids , v_status_applicable));

			-- DELETE FEE DETAILS FOR CURRENT FEE IN GIVEN BILLING CYCLE IF BILLING IS NOT FREEZED
			delete from clues_billing_fee_details 
				where billing_cycle 
				between p_in_billing_cycle_start and p_in_billing_cycle_end and fee_code = v_code and billing = 'N';
			-- RESET ORDER DATA HANDLER TO 0 ON EACH FEE TYPE
			set no_more_data = 0;			
			open order_data;
				block2 : begin
					declare continue handler for not found set no_more_data = 1;
					repeat 
						fetch order_data into v_company_id , v_order_id, v_subtotal, v_cartsize , v_billing_cycle, v_billing_category , v_status;
					if not no_more_data then

					SET v_cid = 0;
					-- CHECK IF FEE IS ALREADY APPLIED ON AN ORDER AND BILLING CYCLE IS FREEZED
					select count(company_id) INTO v_cid from clues_billing_fee_details WHERE order_id = v_order_id and fee_code = v_code;
 						-- IF BILLING IS NOT DONE EARLIER THIS ORDER
						if v_cid = 0 then
							-- IF FEE IS APPLICABLE IN BILLING CYCLE ON THIS ORDER
							if v_billing_cycle between v_from_date and v_to_date then
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
							values( v_company_id, v_order_id, v_code, v_unit, v_cartsize, v_calc_fee,v_tax, v_calc_fee + v_tax, v_billing_cycle ,'N');
							else
							 -- IF FEE IS NOT APPLICABLE THEN LOG THE MESSAGE		
							  insert into clues_logs (tag,message) values ('DEBUG',CONCAT('Fee : ',v_code ,' Not applicable for order : ', v_order_id ));
							end if;
						else
							 -- IF FEE IS ALREADY APPLIED THEN LOG AN ERROR MESSAGE AND CONTINUE		
						  insert into clues_logs (tag,message) values ('ERROR',CONCAT('Fee : ',v_code ,' already applied on Order ID : ', v_order_id ));
					
						end if;
						end if;
						
						until no_more_data
					end  repeat;
				end block2;
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


-- Delete  from payout summary table for invoicing on basis of parameter billing_cycle 
delete  p   from clues_billing_payout_summary p  inner join clues_billing_total_fee f on p.company_id = f.company_id where p.billing_cycle = p_in_invoice_billing_cycle;

-- Insert into payout summary table for invoicing on basis of parameter billing_cycle 
insert into clues_billing_payout_summary (company_id,sum_packing_service_fee,billing_cycle) 
SELECT company_id,sum(calculated_fee),p_in_invoice_billing_cycle FROM clues_billing_total_fee group by company_id;
/*
update clues_billing_payout_summary  cbps
set 
    cbps.sum_packing_service_fee =  (select 
            sum(calculated_fee) calculated_fee
        from
            clues_billing_total_fee
        where
            cbps.company_id = company_id
			and STR_TO_DATE(cbps.billing_cycle, '%d-%m-%y') = billing_cycle

)
where STR_TO_DATE(cbps.billing_cycle, '%d-%m-%y')  between 
p_in_billing_cycle_start and p_in_billing_cycle_end ;
*/

END$$

