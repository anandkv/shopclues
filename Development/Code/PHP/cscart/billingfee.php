<?php


/**

SELECT fulfillment_id FROM  clues_fulfillment_lookup WHERE description = "Shopclues Velocity Premium";

SELECT company_id FROM cscart_companies WHERE state = 'NCR' AND fulfillment_id  = (SELECT fulfillment_id FROM  clues_fulfillment_lookup WHERE description = "Shopclues Velocity Premium");

DELIMITER //
	DROP PROCEDURE IF EXISTS packingDetails//
	CREATE PROCEDURE packingDetails()
		BEGIN
			DECLARE mid INT;
			DECLARE moi INT;
			DECLARE oid INT;

			DECLARE osi INT;
			DECLARE no_more_merchant INT DEFAULT 0;
			DECLARE no_more_shipping_id INT DEFAULT 0;
			
			DECLARE Merchant_id CURSOR FOR SELECT co.company_id,co.order_id FROM  cscart_companies cc,cscart_orders co WHERE cc.company_id = co.company_id AND cc.state = 'NCR' AND SCVP = 'N' AND co.timestamp between '1315981309' and '1322236823';
			DECLARE shipping_id CURSOR FOR SELECT csi.shipment_id,order_id,tracking_number,timestamp,package_type,amount FROM cscart_shipment_items csi,cscart_shipments cs WHERE csi.shipment_id = cs.shipment_id AND order_id = oid;

			DECLARE  CONTINUE HANDLER FOR NOT FOUND SET  no_more_merchant = 1;
			OPEN Merchant_id;
	 			REPEAT
					FETCH  Merchant_id  INTO mid,oid;
		 				IF NOT no_more_merchant THEN
		 					OPEN shipping_id;
								BLOCK2: BEGIN
									DECLARE qty INT Default 0;
									DECLARE awb_no varchar(255) Default NULL;
									DECLARE time int(11) Default 0;
									DECLARE p_type varchar(20) Default NULL;
									DECLARE p_fee DECIMAL(3,2) Default 0.0;
									DECLARE sid INT;
									DECLARE orid INT;

									DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_more_shipping_id = 1;
									FETCH  shipping_id  INTO sid,orid,awb_no,time,p_type,qty;
									REPEAT 	
											IF p_type = '1' THEN
												SET p_fee = 3.0;
											END IF;
											IF p_type = '2' THEN
												SET p_fee = 5.0;
											END IF;
											IF awb_no is not null THEN
												INSERT INTO clues_packaging_fee_detail (merchant_id,order_id,awb_no,packaging_material,packaging_material_fee,qty,total_packaging_fee,billing_cycle,created_on) 
												values(mid,oid,awb_no,p_type,p_fee,qty,(qty*p_fee),'28 - oct',now());
											END IF;
											UPDATE  cscart_orders SET SCVP = 'Y' WHERE order_id = oid;
											FETCH  shipping_id  INTO sid,orid,awb_no,time,p_type,qty;
						 				UNTIL no_more_shipping_id
									END REPEAT;					
								END BLOCK2;
							CLOSE shipping_id;
						END IF;
		 				UNTIL no_more_merchant
	 			END REPEAT;
				CLOSE  Merchant_id;
		END //
DELIMITER ;

		IF STRCMP('1',p_type)==0 THEN
												SET p_fee = 3.0;
											END IF;
											IF STRCMP('2',p_type) == 0 THEN
												SET p_fee = 5.0;
											END IF;

 * 
 * 
 * 
 * 
 * cscart_companies
cscart_orders
cscart_order_data
cscart_shipment_items
cscart_shipments


DELIMITER //
	DROP PROCEDURE IF EXISTS packingDetails//
	CREATE PROCEDURE packingDetails()
		BEGIN
			DECLARE qty INT Default 0;
			DECLARE awb_no varchar(255) Default NULL;
			DECLARE time int(11) Default 0;
			DECLARE p_type varchar(11) Default NULL;
			DECLARE p_fee DECIMAL(3,2) Default 0.0;
			DECLARE mid INT;
			DECLARE moi INT;
			DECLARE osi INT;
			
			DECLARE Merchant_id CURSOR FOR SELECT company_id FROM cscart_companies WHERE state = 'NCR' AND timestamp between '1315981310' and '1321883524';
			OPEN Merchant_id;
				FETCH  Merchant_id  INTO mid;
	 			REPEAT
					DECLARE Merchant_Order_id CURSOR FOR SELECT order_id FROM cscart_orders WHERE company_id = mid;
					OPEN Merchant_Order_id;
						FETCH  Merchant_Order_id  INTO moi;
	 					REPEAT
	 						DECLARE Order_Shipping_id CURSOR FOR SELECT shipment_id FROM cscart_shipment_items WHERE order_id = moi;
							OPEN Order_Shipping_id;
								FETCH  Order_Shipping_id  INTO osi;
	 							REPEAT
									SELECT amount INTO qty FROM cscart_shipment_items WHERE order_id = moi AND shipment_id = osi;
									SELECT tracking_number INTO awb_no,timestamp INTO time,package_type INTO p_type FROM cscart_shipments WHERE shipment_id = osi;
										IF package_type == 1
												SET p_fee = 3.0;
										END IF;
										IF package_type == 2
												SET p_fee = 5.0;
										END IF;

										INSERT INTO clues_packaging_fee_detail (merchant_id,order_id,awb_no,packaging_material,packaging_material_fee,qty,total_packaging_fee,billing_cycle,created_on) 
										values(mid,moi,awb_no,p_type,p_fee,qty,(p_fee*qty),'28 oct',now());
										UPDATE  cscart_orders SET SCVP = 'Y' WHERE order_id = moi;1315981310
	 						CLOSE Order_Shipping_id;
	 				CLOSE  Merchant_Order_id;
			CLOSE  Merchant_id;
		END //
DELIMITER ;

 *
 */
   mysql_select_db('scdb',mysql_connect('localhost','root','root'));/**/
   $orders = array();
   $selectMarchent = "SELECT co.company_id,co.order_id FROM  cscart_companies cc,cscart_orders co WHERE cc.company_id = co.company_id AND cc.state = 'NCR' AND SCVP = 'N'";
   $selectMarchentResult = mysql_query($selectMarchent);
   while ($selectMarchentRow = mysql_fetch_assoc($selectMarchentResult)) {
   	  $orders[$selectMarchentRow['company_id']][]  = $selectMarchentRow['order_id']; 
   }
   foreach( $orders as $mercahntKey => $merchantOrderArray) {
   	//echo "$mercahntKey<br/>";
   		$selectShipmentsIds = "SELECT csi.shipment_id,order_id,tracking_number,timestamp,package_type,amount FROM cscart_shipment_items csi,cscart_shipments cs WHERE csi.shipment_id = cs.shipment_id AND order_id in (".implode(",",$merchantOrderArray).")";
		$resultAwbPackaging = mysql_query($selectShipmentsIds);
		while($rowAwbPackaging = mysql_fetch_assoc($resultAwbPackaging)) {
			echo "<br/>".$insertRecordBillingCycle = "INSERT INTO clues_packaging_fee_detail (merchant_id,order_id,awb_no,packaging_material,packaging_material_fee,qty,total_packaging_fee,billing_cycle,created_on) 
										values($mercahntKey,$rowAwbPackaging[order_id],'$rowAwbPackaging[tracking_number]','$rowAwbPackaging[package_type]',0,$rowAwbPackaging[amount],0,'28 Dec',now())";
			mysql_query($insertRecordBillingCycle);
			$updateSCVP = "UPDATE  cscart_orders SET SCVP = 'Y' WHERE order_id = ".$rowAwbPackaging['order_id'];
			mysql_query($updateSCVP);	
		}
    		break;
   }
  // echo "<pre>";
//   print_r(array_keys($orders));
?>