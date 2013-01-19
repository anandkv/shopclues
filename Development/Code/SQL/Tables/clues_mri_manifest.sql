CREATE TABLE clues_mri_manifest ( 
id bigint( 20 ) NOT NULL auto_increment , 
	manifest_id bigint( 20 ) default NULL , 
	company_id( 20 ) default NULL , 
	pickupboy_id bigint( 20 ) default NULL , 
	status varchar(32) default nul
	status_date int( 11 ) default NULL,
	created_date int( 11 ) default NULL, 
	created_by varchar(255) default NULL, 
	updated_date int( 11 ) default NULL, 
	updated_by varchar(255) default NULL, 
PRIMARY KEY ( id ) , 
UNIQUE KEY manifest_id ( manifest_id,company_id) 

) ENGINE = InnoDB ; 