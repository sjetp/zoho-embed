<?php
	/**
		* ReportClient provides the PHP based language binding to the https based API of ZohoAnalytics.
	*/
	class ReportClient
	{
		/**
			* @var string $reports_url The base request API URL.
		*/
		public $reports_url = "https://analyticsapi.zoho.in";
		/**
			* @var string $accounts_url Account URL.
		*/
		public $accounts_url = "https://accounts.zoho.in";
		/**
			* @var const ZOHO_API_VERSION It contain the API version.It is a constant one.
		*/
		const ZOHO_API_VERSION = '1.0';
		/**
			* @var string $zoho_action It is action name, that is performed by the URL.
		*/
		public $zoho_action;
		/**
			* @var boolean $proxy It will indicate whether the proxy is set or not.
		*/
		public $proxy = FALSE;
		/**
			* @var string $proxy_host The hostname/ip address of the proxy-server.
		*/
		public $proxy_host;
		/**
			* @var int $proxy_port The proxy server port.
		*/
		public $proxy_port;
		/**
			* @var string $proxy_user_name The user name for proxy-server authentication.
		*/
		public $proxy_user_name;
		/**
			* @var string $proxy_password The password for proxy-server authentication.
		*/
		public $proxy_password;
		/**
			* @var string $proxy_type Can be any one ( HTTP , HTTPS , BOTH ).Specify "BOTH" if same configuration can be used for both HTTP and HTTPS.
		*/
		public $proxy_type;
		/**
			* @var int $connection_timeout It is a time value until a connection is established.
		*/
		public $connection_timeout;
		/**
			* @var int $read_timeout It is a time value until waiting to read data.
		*/
		public $read_timeout;

		/**
			* @internal For OAuth Authentication
		*/
		public $OAuth_params = array();

		/**
			* @internal For OAuth Authentication
		*/
		public $zoho_accesstoken;
		
		/**
  			* Creates a new Zoho ReportClient instance.
  			* @param string $client_id Client id of the user.
  			* @param array() $client_secret Client secret of the user.
  			* @param array() $refresh_token Refresh token of the user.
  			* <a href="https://www.zoho.com/analytics/api/#oauth" target="_blank">click here</a> to know about OAuth.
			* @link 
  		*/
		function __construct($client_id, $client_secret, $refresh_token)
		{
			$this->OAuth_params['client_id'] = $client_id;
			$this->OAuth_params['client_secret'] = $client_secret;
			$this->OAuth_params['refresh_token'] = $refresh_token;
			$this->OAuth_params['grant_type'] = 'refresh_token';
		}

		/**
  			* Adds a row to the specified table identified by the URI.
  			* @param string $table_uri The URI of the table.
  			* @param array() $columnvalues Contains the values for the row. The column name(s) are the key.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return array Successfully added rows with value. 
  		*/
		function addRow($table_uri, $columnvalues, $config = array())
		{
			foreach ($columnvalues as $key => $value) 
			{
				$config[$key] = $value;
			}
			$this->zoho_action = 'ADDROW';
			$request_url = $this->getUrl($table_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			$response = $response['response']['result'];
			$count = count($response['column_order']);
			$result_array = array();
			for($i = 0; $i < $count; $i++)
			{
				$result_array[$response['column_order'][$i]] = $response['rows'][0][$i];
			}
			return $result_array;		
		}
		
		/**
  			* Delete the data in the specified table identified by the URI.
  			* @param string $table_uri The URI of the table.
  			* @param string $criteria The criteria to be applied for deleting. Only rows matching the criteria will be deleted. Can be null. In-case it is null, then all rows will be deleted.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return string Deleted row count.
  		*/
		function deleteData($table_uri, $criteria = NULL, $config = array())
		{
			$this->zoho_action = 'DELETE';
			$config['ZOHO_CRITERIA'] = $criteria;
			$request_url = $this->getUrl($table_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result']['deletedrows'];
		}
		
		/**
  			* Update the data in the specified table identified by the URI.
  			* @param string $table_uri The URI of the table.
  			* @param array() $columnvalues Contains the values to be updated. The column name(s) are the key. 
  			* @param string $criteria The criteria to be applied for updating. Only rows matching the criteria will be updated. Can be null. In-case it is null, then all rows will be updated.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return string Updated row count.
  		*/
		function updateData($table_uri, $columnvalues, $criteria = NULL, $config = array())
		{
			foreach ($columnvalues as $key => $value) 
			{
				$config[$key] = $value;
			}
			$this->zoho_action = 'UPDATE';
			$config['ZOHO_CRITERIA'] = $criteria;
			$request_url = $this->getUrl($table_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result']['updatedRows'];
		}
		
		/**
  			* Import the data contained in a given file into the table identified by the URI.
  			* @param string $table_uri The URI of the table.
  			* @param string $import_type The type of import
  			* @param file $file The file containing the data to be imported into the table.
  			* @param string $auto_identify Used to specify whether to auto identify the CSV format.
  			* @param string $on_error This parameter controls the action to be taken In-case there is an error during import.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return object Import result class object. 
  		*/
		function importData($table_uri, $import_type, $file, $auto_identify, $on_error, $config = array())
		{
			$this->zoho_action = 'IMPORT';
			$config['ZOHO_IMPORT_TYPE'] = $import_type;
			$config['ZOHO_AUTO_IDENTIFY'] = $auto_identify;
			$config['ZOHO_ON_IMPORT_ERROR'] = $on_error;
			if(!array_key_exists("ZOHO_CREATE_TABLE",$config))
  			{
  				$config['ZOHO_CREATE_TABLE'] = 'FALSE';
  			}
			$config = array_diff($config,array(''));
			$file_data = explode('/', $file);
			$filename = end($file_data);
            		$config['ZOHO_FILE'] = new CURLFile($file, 'json/csv', $filename);
			//$config['ZOHO_FILE'] = "@$file";
			$request_url = $this->getUrl($table_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			$import_obj = new ImportResult($response);
			return $import_obj;
		}
		
		/**
  			* Import the data contained in a given string into the table identified by the URI.
  			* @param string $table_uri The URI of the table.
  			* @param string $import_type The type of import
  			* @param string $import_data The string containing the data to be imported into the table.
  			* @param string $auto_identify Used to specify whether to auto identify the CSV format.
  			* @param string $on_error This parameter controls the action to be taken In-case there is an error during import.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return object Import result class object. 
  		*/
		function importDataAsString($table_uri, $import_type, $import_data, $auto_identify, $on_error, $config = array())
		{
			$this->zoho_action = 'IMPORT';
			$config['ZOHO_IMPORT_TYPE'] = $import_type;
			$config['ZOHO_AUTO_IDENTIFY'] = $auto_identify;
			$config['ZOHO_ON_IMPORT_ERROR'] = $on_error;
			if(!array_key_exists("ZOHO_CREATE_TABLE",$config))
  			{
  				$config['ZOHO_CREATE_TABLE'] = 'FALSE';
  			}
			$config = array_diff($config,array(''));
            		$config['ZOHO_IMPORT_DATA'] = $import_data;
			$request_url = $this->getUrl($table_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			$import_obj = new ImportResult($response);
			return $import_obj;
		}
		
		/**
  			* Exports the data/report of table identified by the URI.
  			* @param string $table_uri The URI of the table.
  			* @param string $file_format The format in which the data is to be exported. 
  			* @param string $criteria The criteria to be applied for exporting. Only rows matching the criteria will be exported. Can be null. In-case it is null, then all rows will be updated.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return string Table data. 
  		*/
		function exportData($table_uri, $file_format, $criteria = NULL, $config = array())
		{
			$this->zoho_action = 'EXPORT';
			$config['ZOHO_CRITERIA'] = $criteria;
			$request_url = $this->getUrl($table_uri, $file_format);
			$response = $this->sendRequest($request_url, $config, true);
			return $response;
		}
		
		/**
  			* Exports the data with the given SQL Query.
  			* @param string $db_uri The URI of the workspace.
  			* @param string $file_format The format in which the data is to be exported. 
  			* @param string $sql_query The SQL Query whose output is exported.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return string Table data.
  		*/
		function exportDataUsingSQL($db_uri, $file_format, $sql_query, $config = array())
		{
			$this->zoho_action = 'EXPORT';
			$config['ZOHO_SQLQUERY'] = $sql_query;
			$request_url = $this->getUrl($db_uri, $file_format);
			$response = $this->sendRequest($request_url, $config, true);
			return $response;
		}
		
		/**
  			* Copy a specified workspace identified by the URI.
  			* @param string $db_uri The URI of the workspace.
  			* @param string $db_key Contains workspace key that user wants to copy.
  			* @param string $new_db_name Contains new workspace name.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return string The new workspace id.
  		*/
		function copyDatabase($db_uri, $db_key, $new_db_name, $config = array())
		{
			$this->zoho_action = 'COPYDATABASE';
			$config['ZOHO_DATABASE_NAME'] = $new_db_name;
			$config['ZOHO_COPY_DB_KEY'] = $db_key;
			$request_url = $this->getUrl($db_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result']['dbid'];
		}
		
		/**
  			* Deletes a specified workspace from the Zoho Analytics Account.
  			* @param string $user_uri The URI of the user.
  			* @param string $db_name The name of the workspace to be deleted from the Zoho Analytics Account.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server harefresh_tokens responded but client was not able to parse the response.
  		*/
		function deleteDatabase($user_uri, $db_name, $config = array())
		{
			$this->zoho_action = 'DELETEDATABASE';
			$config['ZOHO_DATABASE_NAME'] = $db_name;
			$request_url = $this->getUrl($user_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}
		
		/**
			* Enable workspace for custom domain.
			* @param string $user_uri The URI of the user.
			* @param string $db_name The workspace names which you want to show in your custom domain.
			* @param string $domain_name Custom domain name.
			* @param array() $config Contains any additional control parameters. Can be null.
			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
		*/
		function enableDomainDB($user_uri, $db_name, $domain_name, $config = array())
		{
			$this->zoho_action = 'ENABLEDOMAINDB';
			$request_url = $this->getUrl($user_uri, 'JSON');
			$config['DBNAME'] = $db_name;
			$config['DOMAINNAME'] = $domain_name;
			$this->sendRequest($request_url, $config, false);
		}
		
		/**
			* Disable workspace for custom domain.
			* @param string $user_uri The URI of the user.
			* @param string $db_name The workspace names which you want to disable from your custom domain.
			* @param string $domain_name Custom domain name.
			* @param array() $config Contains any additional control parameters. Can be null.
			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
		*/
		function disableDomainDB($user_uri, $db_name, $domain_name, $config = array())
		{
			$this->zoho_action = 'DISABLEDOMAINDB';
			$request_url = $this->getUrl($user_uri, 'JSON');
			$config['DBNAME'] = $db_name;
			$config['DOMAINNAME'] = $domain_name;
			$this->sendRequest($request_url, $config, false);
		}
		
		/**
    		* Create a table in the specified workspace.
	    	* @param string $db_uri The URI of the workspace.
	    	* @param JSON $table_design_JSON Table structure in JSON format (includes table name, description, folder name, column and lookup details).
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
    		*/
		function createTable($db_uri, $table_design_JSON, $config = array())
		{
			$this->zoho_action = 'CREATETABLE';
			$config['ZOHO_TABLE_DESIGN'] = $table_design_JSON;
			$request_url = $this->getUrl($db_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}
		
		/**
			* To generate reports.
			* @param string $table_uri The URI of the table.
			* @param string $source To set column or table.
			* @param array() $config Contains any additional control parameters. Can be null.
			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return string Auto-generated info.
		*/
		function autoGenReports($table_uri, $source, $config = array())
		{
			$this->zoho_action = "AUTOGENREPORTS";
			$config['ZOHO_SOURCE'] = $source;
			$request_url = $this->getUrl($table_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result'];
		}

		/**
    		* Create a report in the specified workspace.
	    	* @param string $db_uri The URI of the workspace.
	    	* @param JSON $view_design_JSON Table structure in JSON format.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
    		*/
		function createAnalysisView($db_uri, $view_design_JSON, $config = array())
		{
			$this->zoho_action = 'CREATEANALYSISVIEW';
			$config['ZOHO_VIEW_DATA'] = $view_design_JSON;
			$request_url = $this->getUrl($db_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}
		
		/**
			* Create reports similar as another table reports.
			* @param string $table_uri The URI of the table.
			* @param string $ref_view The reference table name.
			* @param string $folder_name Folder name where the reports to be saved.
			* @param boolean $copy_customformula If true, it will create reports with custom formula else it will ignore that formula.
			* @param boolean $copy_aggformula If true, it will create reports with aggregate formula else it will ignore that formula.
			* @param array() $config Contains any additional control parameters. Can be null.
			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return string View-generated info.
		*/
		function createSimilarViews($table_uri, $ref_view, $folder_name, $copy_customformula, $copy_aggformula, $config = array())
		{
			$this->zoho_action = 'CREATESIMILARVIEWS';
			$request_url = $this->getUrl($table_uri, 'JSON');
			$config['ZOHO_REFVIEW'] = $ref_view;
			$config['ZOHO_FOLDERNAME'] = $folder_name;
			$config['ISCOPYCUSTOMFORMULA'] = ($copy_customformula == TRUE) ? "true":"false";
			$config['ISCOPYAGGFORMULA'] = ($copy_aggformula == TRUE) ? "true":"false";
			$this->sendRequest($request_url, $config, false);
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result'];
		}
		
		/**
    		* Rename the specified view with the new name and description.
	    	* @param string $db_uri The URI of the workspace.
    		* @param string $viewname Current name of the view.
	    	* @param string $new_viewname New name for the view.
    		* @param string $new_viewdesc New description for the view.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
    		*/
		function renameView($db_uri, $viewname, $new_viewname, $new_viewdesc = NULL, $config = array())
		{
			$this->zoho_action = 'RENAMEVIEW';
			$config['ZOHO_VIEWNAME'] = $viewname;
			$config['ZOHO_NEW_VIEWNAME'] = $new_viewname;
			$config['ZOHO_NEW_VIEWDESC'] = $new_viewdesc;
			$request_url = $this->getUrl($db_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}

		/**
  			* Deletes the specified view from the workspace.
  			* @param string $db_uri The URI of the workspace.
  			* @param string $view_name The name of the view.
  			* @param array() $config $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function deleteView($db_uri, $view_name, $config = array())
		{
			$this->zoho_action = 'DELETEVIEW';
			$config['ZOHO_VIEW'] = $view_name;
			$request_url = $this->getUrl($db_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}
		
		/**
  			* The Copy Reports API is used to copy one or more reports from one workspace to another within the same account or even across user accounts.
  			* @param string $db_uri The URI of the workspace.
  			* @param string $views This parameter holds the list of view names.
  			* @param string $db_name The workspace name where the reports had to be copied.
  			* @param string $db_key The secret key used for allowing the user to copy the workspace / reports.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function copyReports($db_uri, $views, $db_name, $db_key, $config = array())
		{
			$this->zoho_action = 'COPYREPORTS';
			$config['ZOHO_VIEWTOCOPY'] = $views;
			$config['ZOHO_DATABASE_NAME'] = $db_name;
			$config['ZOHO_COPY_DB_KEY'] = $db_key;
			$request_url = $this->getUrl($db_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}
		
		/**
  			* The Copy Formula API is used to copy one or more formula columns from one table to another within the same workspace or across workspaces and even across one user account to another.
  			* @param string $table_uri The URI of the table.
  			* @param string $formula This parameter holds the list of formula names.
  			* @param string $db_name The workspace name where the formula's had to be copied.
  			* @param string $db_key The secret key used for allowing the user to copy the formula.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function copyFormula($table_uri, $formula, $db_name, $db_key, $config = array())
		{
			$this->zoho_action = 'COPYFORMULA';
			$config['ZOHO_FORMULATOCOPY'] = $formula;
			$config['ZOHO_DATABASE_NAME'] = $db_name;
			$config['ZOHO_COPY_DB_KEY'] = $db_key;
			$request_url = $this->getUrl($table_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}
		
		/**
  			* Adds a column to the specified table identified by the URI.
  			* @param string $table_uri The URI of the table.
  			* @param string $column_name Contains the name of the column to be added.
  			* @param string $data_type Contains the datatype of the column to be added.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function addColumn($table_uri, $column_name, $data_type, $config = array())
		{
			$this->zoho_action = 'ADDCOLUMN';
			$config['ZOHO_COLUMNNAME'] = $column_name;
			$config['ZOHO_DATATYPE'] = $data_type;
			$request_url = $this->getUrl($table_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}
		
		/**
  			* Delete the column in the specified table identified by the URI.
  			* @param string $table_uri The URI of the table.
  			* @param string $column_name Contains the name of the column to be deleted.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function deleteColumn($table_uri, $column_name, $config = array())
		{
			$this->zoho_action = 'DELETECOLUMN';
			$config['ZOHO_COLUMNNAME'] = $column_name;
			$request_url = $this->getUrl($table_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}
		
		/**
  			* Rename the column in the specified table identified by the URI.
  			* @param string $table_uri The URI of the table.
  			* @param string $old_column_name Contains the name of the column to be modified.
  			* @param string $new_column_name Contains the new column name.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function renameColumn($table_uri, $old_column_name, $new_column_name, $config = array())
		{
			$this->zoho_action = 'RENAMECOLUMN';
			$config['OLDCOLUMNNAME'] = $old_column_name;
			$config['NEWCOLUMNNAME'] = $new_column_name;
			$request_url = $this->getUrl($table_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}
		
		/**
			* To hide columns in the table.
			* @param string $table_uri The URI of the table.
			* @param array() $columnNames The column names of the table.
			* @param array() $config Contains any additional control parameters. Can be null.
			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return array() Response result of hidecolumn.
		*/
		function hideColumn($table_uri, $columnNames, $config = array())
		{
			$this->zoho_action = "HIDECOLUMN";
			$request_url = $this->getUrl($table_uri, 'JSON');
			for($i = 0 ; $i<sizeof($columnNames); $i++)
			{
				$request_url = $request_url."&ZOHO_COLUMNNAME=".$columnNames[$i];
			}
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result'];
		}
		
		/**
			* Get the plan informations.
			* @param string $table_uri The URI of the table.
			* @param array() $columnNames The column names of the table.
			* @param array() $config Contains any additional control parameters. Can be null.
			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return array() Response result of showcolumn.
		*/
		function showColumn($table_uri, $columnNames, $config = array())
		{
			$this->zoho_action = "SHOWCOLUMN";
			$request_url = $this->getUrl($table_uri, 'JSON');
			for($i = 0 ; $i<sizeof($columnNames); $i++)
			{
				$request_url = $request_url."&ZOHO_COLUMNNAME=".$columnNames[$i];
			}
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result'];
		}
		
		/**
    		* Add the lookup for the given column.
	    	* @param string $table_uri The URI of the table.
    		* @param string $column_name Name of the column (Child column).
	    	* @param string $referred_table Name of the referred table (parent table).
    		* @param string $referred_column Name of the referred column (parent column).
    		* @param string $on_error This parameter controls the action to be taken In-case there is an error during lookup.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
    		*/
		function addLookup($table_uri, $column_name, $referred_table, $referred_column, $on_error, $config = array())
		{
			$this->zoho_action = 'ADDLOOKUP';
			$config['ZOHO_COLUMNNAME'] = $column_name;
			$config['ZOHO_REFERREDTABLE'] = $referred_table;
			$config['ZOHO_REFERREDCOLUMN'] = $referred_column;
			$config['ZOHO_IFERRORONCONVERSION'] = $on_error;
			$request_url = $this->getUrl($table_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}
		
		/**
    		* Remove the lookup for the given column.
	    	* @param string $table_uri The URI of the table.
	    	* @param string $column_name Name of the column. 
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
    		*/
		function removeLookup($table_uri, $column_name, $config = array())
		{
			$this->zoho_action = 'REMOVELOOKUP';
			$config['ZOHO_COLUMNNAME'] = $column_name;
			$request_url = $this->getUrl($table_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}

		/**
  			* Updates cloud connection information of the given view identified by the URI.
  			* @param string $table_uri The URI of the table.
  			* @param Object $connInfo Contains connection information to be updated.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function updateCloudDBConnection($table_uri, $connInfo, $config = array())
		{
			$this->zoho_action = 'UPDATECLOUDDBCONNECTION';
			$config['ZOHO_CONNECTION_INFO'] = urlencode(json_encode($connInfo));
			$request_url = $this->getUrl($table_uri, 'JSON');
			$this->sendRequest($request_url, $config, true);
		}

		/**
  			* Updates cloud connection information of the given live connect workspace identified by the URI.
  			* @param string $db_uri The URI of the workspace.
  			* @param Object $connInfo Contains connection information to be updated.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function updateRemoteDBConnection($db_uri, $connInfo, $config = array())
		{
			$this->zoho_action = 'UPDATEREMOTEDBCONNECTION';
			$config['ZOHO_CONNECTION_INFO'] = urlencode(json_encode($connInfo));
			$request_url = $this->getUrl($db_uri, 'JSON');
			$this->sendRequest($request_url, $config, true);
		}

		/**
  			* Creates a blank workspace.
  			* @param string $user_uri The URI of the user.
  			* @param string $newdb_name The name of the workspace to be created.
  			* @param string $db_desc The description of the workspace.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function createBlankDb($user_uri, $newdb_name, $db_desc = string, $config = array())
		{
			$this->zoho_action = 'CREATEBLANKDB';
			$config['ZOHO_DATABASE_NAME'] = urlencode($newdb_name);
			if (!empty($db_desc)) {
				$config['ZOHO_DATABASE_DESC'] = urlencode($db_desc);
			}
			$request_url = $this->getUrl($user_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}

		/**
  			* Create a new view by copying the structure and data of existing view.
  			* @param string $db_uri The URI of the workspace.
  			* @param string $viewto_copy Name of the view to be copied.
  			* @param string $new_viewname Name of the view to be created.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function saveAs($db_uri, $viewto_copy, $new_viewname = string, $config = array())
		{
			$this->zoho_action = 'SAVEAS';
			$config['ZOHO_VIEWTOCOPY'] = urlencode($viewto_copy);
			$config['ZOHO_NEW_VIEWNAME'] = urlencode($new_viewname);
			$request_url = $this->getUrl($db_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}
		
		/**
  			* This method is used to get the meta information about the reports.
  			* @param string $user_uri The URI of the user.
  			* @param string $metadata It specifies the information to be fetched.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return array() The metadata.
  		*/
		function getDatabaseMetadata($user_uri, $metadata, $config = array())
		{
			$this->zoho_action = 'DATABASEMETADATA';
			$config['ZOHO_METADATA'] = $metadata;
			$request_url = $this->getUrl($user_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result'];
		}
		
		/**
  			* Get workspace name for a specified workspace identified by the URI.
  			* @param string $user_uri The URI of the user.
  			* @param string $db_id The ID of the workspace.
  			* @param array() $config $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return string workspace name for a specified workspace.
  		*/
		function getDatabaseName($user_uri, $db_id, $config = array())
		{
			$this->zoho_action = 'GETDATABASENAME';
			$config['DBID'] = $db_id;
			$request_url = $this->getUrl($user_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result']['dbname'];
		}

		/**
  			* Get workspace ID of the specified workspace identified by the URI.
  			* @param string $user_uri The URI of the user.
  			* @param string $db_name The name of the workspace.
  			* @param array() $config $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return long workspace ID for a specified workspace.
  		*/
		function getDatabaseID($user_uri, $db_name, $config = array())
		{
			$this->zoho_action = 'GETDATABASEID';
			$config['ZOHO_DATABASE_NAME'] = $db_name;
			$request_url = $this->getUrl($user_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result']['dbid'];
		}
		
		/**
			* Checks whether the workspace exist or not in the ZohoAnalytics account identified by the URI.
			* @param string $user_uri The URI of the user.
			* @param string $dbname The name of the workspace.
			* @param array() $config Contains any additional control parameters. Can be null.
			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return boolean True, if workspace exist. False, otherwise.
		*/
		function isDbExist($user_uri, $dbname, $config = array())
		{
			$this->zoho_action = "ISDBEXIST";
			$config['ZOHO_DB_NAME'] = $dbname;
			$request_url = $this->getUrl($user_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result']['isdbexist'];
		}
		
		/**
			* Checks whether the view exist or not in the workspace identified by dbURI.
			* @param string $db_uri The URI of the workspace.
			* @param string $view_name The name of the view.
			* @param array() $config Contains any additional control parameters. Can be null.
			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return boolean True, if view exist. False, otherwise.
		*/
		function isViewExist($db_uri, $view_name, $config = array())
		{
			$this->zoho_action = "ISVIEWEXIST";
			$config['ZOHO_VIEW_NAME'] = $view_name;
			$request_url = $this->getUrl($db_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result']['isviewexist'];
		}
		
		/**
			* Checks whether the column exist or not in the view identified by the tableURI.
			* @param string $table_uri The URI of the table.
			* @param string $column_name The name of the column.
			* @param array() $config Contains any additional control parameters. Can be null.
			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return boolean True, if column exist. False, otherwise.
		*/
		function isColumnExist($table_uri, $column_name, $config = array())
		{
			$this->zoho_action = "ISCOLUMNEXIST";
			$config['ZOHO_COLUMN_NAME'] = $column_name;
			$request_url = $this->getUrl($table_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result']['iscolumnexist'];
		}
		
		/**
  			* Get copy workspace key for a specified workspace identified by the URI.
  			* @param string $db_uri The URI of the workspace.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return string Copy workspace key for a specified workspace.
  		*/
		function getCopyDbKey($db_uri, $config = array())
		{
			$this->zoho_action = 'GETCOPYDBKEY';
			$request_url = $this->getUrl($db_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result']['copydbkey'];
		}
		
		/**
  			* This function returns the name of a view in Zoho Analytics.
  			* @param string $user_uri The URI of the User.
  			* @param string $obj_id The view id (object id).
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return string The View name.
  		*/
		function getViewName($user_uri, $obj_id, $config = array())
		{
			$this->zoho_action = 'GETVIEWNAME';
			$config['OBJID'] = $obj_id;
			$request_url = $this->getUrl($user_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result']['viewname'];
		}
		
		/**
  			* This method returns the workspace ID (DBID) and View ID (OBJID) of the corresponding workspace.
  			* @param string $table_uri The URI of the table.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return array() The View-Id (object id) and workspace-Id.
  		*/
		function getInfo($table_uri, $config = array())
		{
			$this->zoho_action = 'GETINFO';
			$request_url = $this->getUrl($table_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result'];
		}

		/**
  			* Returns view details like view name,description,type from the the particular workspace identified by dbURI.
  			* @param string $db_uri The URI of the workspace.
  			* @param string $view_id ID of the view.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return array() view info.
  		*/
		function getViewInfo($db_uri, $view_id, $config = array())
		{
			$this->zoho_action = 'GETVIEWINFO';
			$config['ZOHO_VIEW_ID'] = $view_id;
			$request_url = $this->getUrl($db_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result'];
		}

		/**
  			* Returns the details of recently accessed views from the ZohoAnalytics account identified by the URI.
  			* @param string $user_uri The URI of the user.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return array() recently modified items.
  		*/
		function recentItems($user_uri, $config = array())
		{
			$this->zoho_action = 'RECENTITEMS';
			$request_url = $this->getUrl($user_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result'];
		}

		/**
  			* Returns the list of owned/shared dashboards present in the zoho analytics account identified by the URI.
  			* @param string $user_uri The URI of the user.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return array() dashboard info.
  		*/
		function getDashboards($user_uri, $config = array())
		{
			$this->zoho_action = 'GETDASHBOARDS';
			$request_url = $this->getUrl($user_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result']['dashboards'];
		}

		/**
  			* Returns the list of all owned workspaces present in the zoho analytics account identified by the URI.
  			* @param string $user_uri The URI of the user.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return array() owned workspaces metadata.
  		*/
		function myWorkspaceList($user_uri, $config = array())
		{
			$this->zoho_action = 'MYWORKSPACELIST';
			$request_url = $this->getUrl($user_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result'];
		}

		/**
  			* Returns the list of all shared workspaces present in the zoho analytics account identified by the URI.
  			* @param string $user_uri The URI of the user.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return array() shared workspaces metadata.
  		*/
		function sharedWorkspaceList($user_uri, $config = array())
		{
			$this->zoho_action = 'SHAREDWORKSPACELIST';
			$request_url = $this->getUrl($user_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result'];
		}

		/**
  			* Returns the list of all accessible views present in the workspace identified by the URI.
  			* @param string $db_uri The URI of the workspace.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return array() views metadata.
  		*/
		function viewList($db_uri, $config = array())
		{
			$this->zoho_action = 'VIEWLIST';
			$request_url = $this->getUrl($db_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result'];
		}

		/**
  			* Returns the list of all accessible folders present in the workspace identified by the URI.
  			* @param string $db_uri The URI of the workspace.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return array() folders metadata.
  		*/
		function folderList($db_uri, $config = array())
		{
			$this->zoho_action = 'FOLDERLIST';
			$request_url = $this->getUrl($db_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result'];
		}

		/**
  			* Returns metainfo of the given view identified by the URI.
  			* @param string $table_uri The URI of the table.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return array() Contains meta information of the provided view.
  		*/
		function viewMetadata($table_uri, $config = array())
		{
			$this->zoho_action = 'VIEWMETADATA';
			$request_url = $this->getUrl($table_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result'];;
		}
		
		/**
  			* This method is used to share the views (tables/reports/dashboards) created in Zoho Analytics with users.
  			* @param string $db_uri The URI of the workspace.
  			* @param string $email_ids It contains the users email-id (comma separated).
  			* @param string $views It contains the view names.
  			* @param string $criteria It can be null.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function shareView($db_uri, $email_ids, $views, $criteria = NULL, $config = array())
		{
			$this->zoho_action = 'SHARE';
			$config['ZOHO_EMAILS'] = $email_ids;
			$config['ZOHO_VIEWS'] = $views;
			$config['ZOHO_CRITERIA'] = $criteria;
			$request_url = $this->getUrl($db_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}
		
		/**
  			* This method is used to remove the shared views (tables/reports/dashboards) in Zoho Analytics from the users.
  			* @param string $db_uri The URI of the workspace.
  			* @param string $email_ids It contains the users email-id (comma separated).
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function removeShare($db_uri, $email_ids, $config = array())
		{
			$this->zoho_action = 'REMOVESHARE';
			$config['ZOHO_EMAILS'] = $email_ids;
			$request_url = $this->getUrl($db_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}
		
		/**
  			* This method is used to add new admins to the analytics workspace.
  			* @param string $db_uri The URI of the workspace.
  			* @param string $email_ids It contains the users email-id (comma separated).
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function addDbOwner($db_uri, $email_ids, $config = array())
		{
			$this->zoho_action = 'ADDDBOWNER';
			$config['ZOHO_EMAILS'] = $email_ids;
			$request_url = $this->getUrl($db_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}
		
		/**
  			* This method is used to remove the existing admins from the analytics workspace.
  			* @param string $db_uri The URI of the workspace.
  			* @param string $email_ids It contains the owners email-id (comma separated).
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function removeDbOwner($db_uri, $email_ids, $config = array())
		{
			$this->zoho_action = 'REMOVEDBOWNER';
			$config['ZOHO_EMAILS'] = $email_ids;
			$request_url = $this->getUrl($db_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}
		
		/**
			* Get the shared informations.
			* @param string $db_uri The URI of the workspace.
			* @param array() $config Contains any additional control parameters. Can be null.
			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return object ShareInfo class object.
		*/
		function getShareInfo($db_uri, $config = array())
		{
			$this->zoho_action = "GETSHAREINFO";
			$request_url = $this->getUrl($db_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			$shareinfo_obj = new ShareInfo($response);
			return $shareinfo_obj;
		}

		/**
  			* Get meta information on the groups present in the workspace identified by the URI.
  			* @param string $db_uri The URI of the workspace.
  			* @param Object $groupInfo Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return array() Contains meta information of groups present in the workspace.
  		*/
		function groupInfo($db_uri, $groupInfo = array())
		{
			$this->zoho_action = 'GROUPINFO';

			$config = array();
			if(!empty((array)$groupInfo))
			{
				$config['CONFIG'] = urlencode(json_encode($groupInfo));
			}
			$request_url = $this->getUrl($db_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result'];
		}

		/**
  			* Create a new group in the workspace identified by the URI.
  			* @param string $db_uri The URI of the workspace.
  			* @param Object $groupInfo Contains the required details to create a group.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return string Newly created group id.
  		*/
		function createGroup($db_uri, $groupInfo)
		{
			$config = array();
			$this->zoho_action = 'CREATEGROUP';
			$config['CONFIG'] = urlencode(json_encode($groupInfo));
			$request_url = $this->getUrl($db_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result']['groupId'];
		}

		/**
  			* Deletes the specified groups present in the workspace identified by the URI.
  			* @param string $db_uri The URI of the workspace.
  			* @param Object $groupInfo Contains details on the groups to be deleted.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function deleteGroup($db_uri, $groupInfo)
		{
			$config = array();
			$this->zoho_action = 'DELETEGROUP';
			$config['CONFIG'] = urlencode(json_encode($groupInfo));
			$request_url = $this->getUrl($db_uri, 'JSON');
			$this->sendRequest($request_url, $config, true);
		}

		/**
  			* Updates the specified group information present in the workspace identified by the URI.
  			* @param string $db_uri The URI of the workspace.
  			* @param Object $groupInfo Contains details on the group which has to be updated.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function updateGroupInfo($db_uri, $groupInfo)
		{
			$config = array();
			$this->zoho_action = 'UPDATEGROUPINFO';
			$config['CONFIG'] = urlencode(json_encode($groupInfo));
			$request_url = $this->getUrl($db_uri, 'JSON');
			$this->sendRequest($request_url, $config, true);
		}

		/**
  			* Add new members for the specified group present in the workspace identified by the URI.
  			* @param string $db_uri The URI of the workspace.
  			* @param Object $groupInfo Contains the required information to add members into the group.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function addGroupMembers($db_uri, $groupInfo)
		{
			$config = array();
			$this->zoho_action = 'ADDGROUPMEMBERS';
			$config['CONFIG'] = urlencode(json_encode($groupInfo));
			$request_url = $this->getUrl($db_uri, 'JSON');
			$this->sendRequest($request_url, $config, true);
		}

		/**
  			* Remove members from the specified group present in the workspace identified by the URI.
  			* @param string $db_uri The URI of the workspace.
  			* @param Object $groupInfo Contains the required information to remove members from the group.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function removeGroupMembers($db_uri, $groupInfo)
		{
			$config = array();
			$this->zoho_action = 'REMOVEGROUPMEMBERS';
			$config['CONFIG'] = urlencode(json_encode($groupInfo));
			$request_url = $this->getUrl($db_uri, 'JSON');
			$this->sendRequest($request_url, $config, true);
		}

		
		
		/**
  			* This method returns the URL to access the mentioned view.
  			* @param string $table_uri The URI of the table.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return string The View URI.
  		*/
		function getViewUrl($table_uri, $config = array())
		{
			$this->zoho_action = 'GETVIEWURL';
			$request_url = $this->getUrl($table_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result']['viewurl'];
		}
		
		/**
  			* The Get Embed URL API is used to get the embed URL of the particular table / view. This API is available only for the White Label Administrator.
  			* @param string $table_uri The URI of the table.
  			* @param string $criteria It can be null.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return string The embed URI.
  		*/
		function getEmbedURL($table_uri, $criteria = NULL, $config = array())
		{
			$this->zoho_action = 'GETEMBEDURL';
			$config['ZOHO_CRITERIA'] = $criteria;
			$request_url = $this->getUrl($table_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result']['embedurl'];
		}
		
		/**
			* Returns users list for the particular account.
			* @param string $user_uri The URI of the user.
			* @param array() $config Contains any additional control parameters. Can be null.
			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return array() Users list.
		*/

  		/**
  			* Get the list of all available slideshow present in the workspace identified by the URI.
  			* @param string $db_uri The URI of the workspace.
  			* @param Object $slideInfo Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return array() Contains list of slideshows present in the workspace.
  		*/
		function getSlideList($db_uri, $slideInfo = array())
		{
			$this->zoho_action = 'GETSLIDELIST';

			$config = array();
			if(!empty((array)$slideInfo))
			{
				$config['CONFIG'] = urlencode(json_encode($slideInfo));
			}
			$request_url = $this->getUrl($db_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result'];
		}

		/**
  			* Returns meta-information of the provided slide present in the workspace identified by the URI.
  			* @param string $db_uri The URI of the workspace.
  			* @param Object $slideInfo Contains slide configuration.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return array() Contains meta information of the provided slide.
  		*/
		function getSlideInfo($db_uri, $slideInfo)
		{
			$this->zoho_action = 'GETSLIDEINFO';

			$config = array();
			if(!empty((array)$slideInfo))
			{
				$config['CONFIG'] = urlencode(json_encode($slideInfo));
			}
			$request_url = $this->getUrl($db_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result'];
		}

		/**
  			* Get the URL to access the mentioned slide present in the workspace identified by the URI.
  			* @param string $db_uri The URI of the workspace.
  			* @param Object $slideInfo Contains slide configuration.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return array() Contains Slide URL.
  		*/
		function getSlideUrl($db_uri, $slideInfo)
		{
			$this->zoho_action = 'GETSLIDEURL';

			$config = array();
			if(!empty((array)$slideInfo))
			{
				$config['CONFIG'] = urlencode(json_encode($slideInfo));
			}
			$request_url = $this->getUrl($db_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result'];
		}

		/**
  			* Create a new slideshow in the workspace identified by the URI.
  			* @param string $db_uri The URI of the workspace.
  			* @param Object $slideInfo Contains slide configuration.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function createSlideShow($db_uri, $slideInfo)
		{
			$this->zoho_action = 'CREATESLIDESHOW';

			$config = array();
			if(!empty((array)$slideInfo))
			{
				$config['CONFIG'] = urlencode(json_encode($slideInfo));
			}
			$request_url = $this->getUrl($db_uri, 'JSON');
			$this->sendRequest($request_url, $config, true);
		}

		/**
  			* Update the mentioned slide information present in the workspace identified by the URI.
  			* @param string $db_uri The URI of the workspace.
  			* @param Object $slideInfo Contains slide configuration.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function updateSlideShow($db_uri, $slideInfo)
		{
			$this->zoho_action = 'UPDATESLIDESHOW';

			$config = array();
			if(!empty((array)$slideInfo))
			{
				$config['CONFIG'] = urlencode(json_encode($slideInfo));
			}
			$request_url = $this->getUrl($db_uri, 'JSON');
			$this->sendRequest($request_url, $config, true);
		}

		/**
  			* Delete the mentioned slides present in the workspace identified by the URI.
  			* @param string $db_uri The URI of the workspace.
  			* @param Object $slideInfo Contains slide configuration.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function deleteSlideShow($db_uri, $slideInfo)
		{
			$this->zoho_action = 'DELETESLIDESHOW';

			$config = array();
			if(!empty((array)$slideInfo))
			{
				$config['CONFIG'] = urlencode(json_encode($slideInfo));
			}
			$request_url = $this->getUrl($db_uri, 'JSON');
			$this->sendRequest($request_url, $config, true);
		}

		/**
			* Returns users list for the particular account.
			* @param string $user_uri The URI of the user.
			* @param array() $config Contains any additional control parameters. Can be null.
			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return array() Users list.
		*/
		function getUsers($user_uri, $config = array())
		{
			$this->zoho_action = "GETUSERS";
			$request_url = $this->getUrl($user_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			return $response['response']['result'];
		}
		
		/**
  			* Adds the specified user(s) into your Zoho Analytics Account.
  			* @param string $user_uri The URI of the user.
  			* @param string $emails The email addresses of the users to be added to your Zoho Analytics Account separated by comma.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function addUser($user_uri, $emails, $config = array())
		{
			$this->zoho_action = 'ADDUSER';
			$config['ZOHO_EMAILS'] = $emails;
			$request_url = $this->getUrl($user_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}
		
		/**
  			* Removes the specified user(s) from your Zoho Analytics Account.
  			* @param string $user_uri The URI of the user.
  			* @param string $emails The email addresses of the users to be removed from your Zoho Analytics Account separated by comma.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function removeUser($user_uri, $emails, $config = array())
		{
			$this->zoho_action = 'REMOVEUSER';
			$config['ZOHO_EMAILS'] = $emails;
			$request_url = $this->getUrl($user_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}
		
		/**
  			* Activates the specified user(s) in your Zoho Analytics Account.
  			* @param string $user_uri The URI of the user.
  			* @param string $emails The email addresses of the users to be activated in your Zoho Analytics Account separated by comma.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function activateUser($user_uri, $emails, $config = array())
		{
			$this->zoho_action = 'ACTIVATEUSER';
			$config['ZOHO_EMAILS'] = $emails;
			$request_url = $this->getUrl($user_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}
		
		/**
  			* Deactivates the specified user(s) from your Zoho Analytics Account.
  			* @param string $user_uri The URI of the user.
  			* @param string $emails The email addresses of the users to be deactivated from your Zoho Analytics Account separated by comma.
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/		
		function deActivateUser($user_uri, $emails, $config = array())
		{
			$this->zoho_action = 'DEACTIVATEUSER';
			$config['ZOHO_EMAILS'] = $emails;
			$request_url = $this->getUrl($user_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}

		/**
			* Get the plan informations.
			* @param string $user_uri The URI of the user.
			* @param array() $config Contains any additional control parameters. Can be null.
			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  			* @return object PlanInfo class object.
		*/
		function getPlanInfo($user_uri, $config = array())
		{
			$this->zoho_action = "GETUSERPLANDETAILS";
			$request_url = $this->getUrl($user_uri, 'JSON');
			$response = $this->sendRequest($request_url, $config, true);
			$planinfo_obj = new PlanInfo($response);
			return $planinfo_obj;
		}

		/**
  			* Change the role of specified users with the new role provided.
  			* @param string $user_uri The URI of the user.
  			* @param string $emails The email addresses of the users whose role has to be changed.
  			* @param string $role New role for the users. can be one of "ORGADMIN"/"USER".
  			* @param array() $config Contains any additional control parameters. Can be null.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @throws ServerException If the server has received the request but did not process the request due to some error.
  			* @throws ParseException If the server has responded but client was not able to parse the response.
  		*/
		function changeUserRole($user_uri, $emails, $role, $config = array())
		{
			$this->zoho_action = 'CHANGEUSERROLE';
			$config['ZOHO_EMAILS'] = $emails;
			$config['ROLE'] = $role;
			$request_url = $this->getUrl($user_uri, 'JSON');
			$this->sendRequest($request_url, $config, false);
		}
		
		/**
  			* Returns the URI for the specified user login email id. This URI should be used only in case of METADATA Action.
  			* @param string $email User email id to get the workspace metadata.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @return string URI for the user.
  		*/
		function getUserURI($email)
		{
			return $this->reports_url."/api/".urlencode($email);
		}
		
		/**
  			* Returns the URI for the specified workspace. This URI should be used only in case of COPYDATABASE,GETCOPYDBKEY Action.
  			* @param string $email User email id.
  			* @param string $db_name The name of the workspace.
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @return string URI for the workspace.
  		*/
		function getDbURI($email, $db_name)
		{
			return $this->splCharReplace($this->reports_url."/api/".urlencode($email)."/".urlencode($db_name));
		}
		
		/**
  			* Returns the URI for the specified workspace table (or report).
  			* @param string $email The owner of the workspace containing the table (or report).
  			* @param string $db_name The name of the workspace containing the table (or report).
  			* @param string $table_name The name of the table (or report).
  			* @throws IOException If any communication related error(s) like request time out occurs when trying to contact the service.
  			* @return string URI for the table.
  		*/
		function getURI($email, $db_name, $table_name)
		{
			return $this->splCharReplace($this->reports_url."/api/".urlencode($email)."/".urlencode($db_name)."/".urlencode($table_name));
		}
		
		/**
			*@internal Internal method for handling special characters in the table or workspace name.
		*/
		function splCharReplace($string)
		{
			$string = str_replace("%2F", "(/)", $string);
			$string = str_replace("%5C", "(//)", $string);
			return $string;
		}
		
		/**
  			* Used to specify the proxy server details.
  			* @param string $proxy_host The hostname/ip address of the proxy-server.
  			* @param int $proxy_port The proxy server port.
  			* @param string $proxy_type Can be any one ( HTTP , HTTPS , BOTH ).Specify "BOTH" if same configuration can be used for both HTTP and HTTPS.
  			* @param string $proxy_user_name The user name for proxy-server authentication.
  			* @param string $proxy_password The password for proxy-server authentication.
  		*/
		function setProxy($proxy_host, $proxy_port, $proxy_type, $proxy_user_name, $proxy_password)
		{
			$this->proxy = TRUE;
			$this->proxy_host = $proxy_host;
			$this->proxy_port = $proxy_port;
			$this->proxy_user_name = $proxy_user_name;
			$this->proxy_password = $proxy_password;
			$this->proxy_type = $proxy_type;
		}
		
		/**
  			* Sets the timeout until a connection is established. A value of zero means the timeout is not used. The default value is 15000.
  			* @param int $time_limit An integer value.
  		*/
		function setConnectionTimeout($time_limit)
		{
			$this->connection_timeout = $time_limit;
		}
		
		/**
  			* Sets the timeout until waiting to read data. A value of zero means the timeout is not used. The default value is 15000.
  			* @param int $time_limit An integer value.
  		*/
		function setReadTimeout($time_limit)
		{
			$this->read_timeout = $time_limit;
		}
		
		/**
  			* Returns the timeout until a connection is established.A value of zero means the timeout is not used.
  			* @return int Connection timeout limit. 
  		*/
		function getConnectionTimeout()
		{
			return $this->connection_timeout;
		}
		
		/**
  			* Returns the timeout until waiting to read data. A value of zero means the timeout is not used. The default value is 15000.
  			* @return int Read timeout limit. 
  		*/
		function getReadTimeout()
		{
			return $this->read_timeout;
		}
		
		/**
  			* @internal To build request URL.
  		*/
		function getUrl($table_uri, $zoho_output_format)
		{
			$request_url = $table_uri.'?ZOHO_ACTION='.$this->zoho_action.'&ZOHO_OUTPUT_FORMAT='.$zoho_output_format.'&ZOHO_ERROR_FORMAT=JSON&ZOHO_API_VERSION='.self::ZOHO_API_VERSION;
			if($zoho_output_format == "JSON")
			{
				$request_url = $request_url.'&ZOHO_VALID_JSON=TRUE';
			}
			return $request_url;
		}
		
		/**
			*@internal For getting OAuth token to invoke API.
		*/
  		function getOauthTicket($OAuth_params)
  		{
  			$req_url = $this->accounts_url.'/oauth/v2/token';
  			$OAuth_request = curl_init();
  			curl_setopt($OAuth_request, CURLOPT_URL, $req_url);
			curl_setopt($OAuth_request,CURLOPT_RETURNTRANSFER,TRUE);
			curl_setopt($OAuth_request,CURLOPT_FOLLOWLOCATION,TRUE);
			curl_setopt($OAuth_request,CURLOPT_POST, 1);
			curl_setopt($OAuth_request,CURLOPT_POSTFIELDS,$OAuth_params);
			$OAuth_response = curl_exec($OAuth_request);
			$OAuth_status_code = curl_getinfo($OAuth_request, CURLINFO_HTTP_CODE);
			if ($OAuth_response != FALSE) 
			{
				$OAuth_JSON_response = json_decode($OAuth_response, TRUE);
				if(json_last_error() != JSON_ERROR_NONE)
				{
					$OAuth_response = stripslashes($OAuth_response);
					$OAuth_JSON_response = json_decode($OAuth_response, TRUE);
				}
				if(json_last_error()) 
				{
					throw new ParseException("Returned JSON format for getting OAuth Ticket is not proper. Could possibly be version mismatch.");
				}
				if ($OAuth_status_code == 200 && array_key_exists('access_token',$OAuth_JSON_response))
				{
					return $OAuth_JSON_response['access_token'];
				}
				else
				{
					throw new ServerException(0,$OAuth_JSON_response['error'],"GETACCESSTOKEN",0);
				}
			}
			else
			{
				throw new Exception("Internal error occurred.");
			}
			curl_close($OAuth_request);
  		}

  		/**
  			* @internal Send request and get response from the server.
  		*/
  		function sendRequest($request_url, $config, $return_response)
		{
			try
			{
				return $this->sendAPIRequest($request_url, $config, $return_response);
			}
			catch(ServerException $e)
            {
            	if($e->getErrorCode()!=null && $e->getErrorCode()==8535)
            	{
            		$this->zoho_accesstoken = $this->getOauthTicket($this->OAuth_params);
            		return $this->sendAPIRequest($request_url, $config, $return_response);
            	}
            	else
            	{
            		throw $e;
            	}
            }
            catch(Exception $e)
            {
            	throw $e;
            }
			
		}

  		/**
  			* @internal Send request and get response from the server.
  		*/
		function sendAPIRequest($request_url, $config, $return_response)
		{
			if($this->zoho_action != "IMPORT")
			{
				$config = array_diff($config, array(''));
			}
			$HTTP_request = curl_init();
			curl_setopt($HTTP_request,CURLOPT_URL,$request_url);
			curl_setopt($HTTP_request,CURLOPT_RETURNTRANSFER,TRUE);
			curl_setopt($HTTP_request,CURLOPT_FOLLOWLOCATION,TRUE);
			curl_setopt($HTTP_request,CURLOPT_HTTPHEADER, array('User-Agent: ZohoAnalytics PHPLibrary'));
			if($this->zoho_accesstoken == null) 
			{
				$this->zoho_accesstoken = $this->getOauthTicket($this->OAuth_params);
			}
			curl_setopt($HTTP_request,CURLOPT_HTTPHEADER, array('Authorization: Zoho-oauthtoken '.$this->zoho_accesstoken));
			
			//If post params are in the form of array, by default it will set the content type as multipart form data.
			//For import API only , we need to send as form data.
			if(is_array($config) && $this->zoho_action != "IMPORT")
			{
				$post_fields = '';
				foreach($config as $key => $value) 
				{
					$post_fields .= ($post_fields == NULL) ? $key . "=" . $value : "&" . $key . "=" . $value;
				}
				curl_setopt($HTTP_request,CURLOPT_POST, 1);
				curl_setopt($HTTP_request,CURLOPT_POSTFIELDS,$post_fields);
			}
			else if(is_array($config))
			{
				curl_setopt($HTTP_request,CURLOPT_POST, 1);
				curl_setopt($HTTP_request,CURLOPT_POSTFIELDS,$config);
			}
			curl_setopt($HTTP_request,CURLOPT_CONNECTTIMEOUT,$this->connection_timeout);
			curl_setopt($HTTP_request,CURLOPT_TIMEOUT,$this->read_timeout);
			if($this->proxy == TRUE)
			{
				curl_setopt($HTTP_request,CURLOPT_PROXY,$this->proxy_host);
				curl_setopt($HTTP_request,CURLOPT_PROXYTYPE,$this->proxy_type);
				curl_setopt($HTTP_request,CURLOPT_PROXYPORT,$this->proxy_port);
				curl_setopt($HTTP_request,CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
				curl_setopt($HTTP_request,CURLOPT_PROXYUSERPWD,"$this->proxy_user_name:$this->proxy_password");
			}
    		$HTTP_response = curl_exec($HTTP_request);
			$HTTP_status_code = curl_getinfo($HTTP_request, CURLINFO_HTTP_CODE);
			if($HTTP_response != FALSE)
			{
				if($HTTP_status_code != 200)
				{
					$JSON_response = json_decode($HTTP_response, TRUE);
					if(json_last_error() != JSON_ERROR_NONE)
					{
						$HTTP_response = stripslashes($HTTP_response);
						$JSON_response = json_decode($HTTP_response, TRUE);
					}
					if(json_last_error()) 
					{
						throw new ParseException("Returned JSON format for ".$this->zoho_action." is not proper. Could possibly be version mismatch");
					}
					$error_message = $JSON_response['response']['error']['message'];
					$error_code = $JSON_response['response']['error']['code'];
					throw new ServerException($error_code, $error_message, $this->zoho_action, $HTTP_status_code); 
				}
				else
				{
					$action = $this->zoho_action;
					if($action=="EXPORT")
					{
						return $HTTP_response;
					}
					else if($return_response == true)
					{
						$JSON_response = json_decode($HTTP_response, TRUE);
						if(json_last_error() != JSON_ERROR_NONE)
						{
							$HTTP_response = stripslashes($HTTP_response);
							$JSON_response = json_decode($HTTP_response, TRUE);
						}
						if(json_last_error()) 
						{
							throw new ParseException("Returned JSON format for ".$this->zoho_action." is not proper. Could possibly be version mismatch");
    						}
    						else
    						{
							return $JSON_response;
    						}
					}
				}
			}
			else
			{
				throw new IOException(curl_error($HTTP_request), $this->zoho_action, $HTTP_status_code);
			}
    			curl_close($HTTP_request);
		}
	}

	/**
		* ImportResult contains the result of an import operation.
	*/
	class ImportResult
	{
		/**
			* @var string $import_type The type of the import operation.
		*/
		private $import_type;
		/**
			* @var int $total_column_count The total columns that were present in the imported file.
		*/
		private $total_column_count;
		/**
			* @var int $selected_column_count The number of columns that were imported.See ZOHO_SELECTED_COLUMNS parameter.
		*/
		private $selected_column_count;
		/**
			* @var long $total_row_count The total row count in the imported file.
		*/
		private $total_row_count;
		/**
			* @var long $success_row_count The number of rows that were imported successfully without errors.
		*/
		private $success_row_count;
		/**
			* @var string $warnings The number of rows that were imported with warnings.
		*/
		private $warnings;
		/**
			* @var string $import_operation The type of import operation.
		*/
		private $import_operation;
		/**
			* @var string $import_errors The first 100 import errors.
		*/
		private $import_errors;
		/**
			* @var string $column_details The column names of the imported columns.
		*/
		private $column_details;
		
		/**
			* @internal Creates a new Import_Result instance.
		*/
		function __construct($JSON_result)
		{
			$JSON_importsummary = $JSON_result['response']['result']['importSummary'];
			$this->import_type = $JSON_importsummary['importType'];
			$this->total_column_count = $JSON_importsummary['totalColumnCount'];
			$this->selected_column_count = $JSON_importsummary['selectedColumnCount'];
			$this->total_row_count = $JSON_importsummary['totalRowCount'];
			$this->success_row_count = $JSON_importsummary['successRowCount'];
			$this->warnings = $JSON_importsummary['warnings'];
			$this->import_operation = $JSON_importsummary['importOperation'];
			$this->import_errors = $JSON_result['response']['result']['importErrors'];	
			$this->column_details = $JSON_result['response']['result']['columnDetails'];
		}
		
		/**
			* Get the type of the import operation.
			* @return string The type of the import operation.
		*/
		function getImportType()
		{
			return $this->import_type;
		}
		
		/**
			* Get the total columns that were present in the imported file.
			* @return integer The total columns that were present in the imported file.
		*/
		function getTotalColumnCount()
		{
			return $this->total_column_count;
		}
		
		/**
			* Get the number of columns that were imported.See ZOHO_SELECTED_COLUMNS parameter.
			* @return integer The number of columns that were imported.
		*/
		function getSelectedColumnCount()
		{
			return $this->selected_column_count;
		}
		
		/**
			* Get the total row count in the imported file.
			* @return long The total row count in the imported file.
		*/
		function getTotalRowCount()
		{
			return $this->total_row_count;
		}
		
		/**
			* Get the number of rows that were imported successfully without errors.
			* @return long The number of rows that were imported successfully without errors.
		*/
		function getSuccessRowCount()
		{
			return $this->success_row_count;
		}
		
		/**
			* Get the number of rows that were imported with warnings. Applicable if ZOHO_ON_IMPORT_ERROR parameter has been set to SETCOLUMNEMPTY.
			* @return long The number of rows that were imported with warnings.
		*/
		function getRowWithWarningCount()
		{
			return $this->warnings;
		}
		
		/**
			* Get the type of import operation. Can be either.
			* created --> if the specified table has been created. For this ZOHO_CREATE_TABLE parameter should have been set to true or updated --> if the specified table already exists.
			* @return string The type of import operation.
		*/
		function getImportOperation()
		{
			return $this->import_operation;
		}
		
		/**
			* Get the first 100 import errors. Applicable if ZOHO_ON_IMPORT_ERROR parameter is either SKIPROW or SETCOLUMNEMPTY. In case of ABORT , ServerException is thrown.
			* @return string The first 100 import errors.
		*/
		function getImportErrors()
		{
			return $this->import_errors;
		}
		
		/**
			* Get the column names of the imported columns.
			* @return string The imported column names.
		*/
		function getImportedColumns()
		{
			return $this->column_details;
		}
		
		/**
			* Get the data type of the specified column.
			* @param string $column_name Name of the column.
			* @return string The column datatype.
		*/
		function getColumnDataType($column_name)
		{
			return $this->column_details[$column_name];
		}
		
		/**
			* Get the complete response content as sent by the server.
			* @return string The complete response content.
		*/
		function toString()
		{
			$str1 = "importtype  $this->import_type totalcolumncount $this->total_column_count";
			$str2 = "selectedcolumncount $this->selected_column_count totalrowcount $this->total_row_count";
			$str3 = "successrowcount $this->success_row_count rowwithwarningcount $this->warnings importoperation $this->import_operation";
			return "Import result: ".$str1." ".$str2." ".$str3;
		}
	}

	/**
		* PlanInfo contains the plan details.
	*/
	class PlanInfo
	{
		/**
			* @var string $plan The type of the user plan.
		*/
		private $plan;
		/**
			* @var string $addons The addon details.
		*/
		private $addons;
		/**
			* @var string $billing_date The billing date.
		*/
		private $billing_date;
		/**
			* @var long $rows_allowed The total row allowed to the user.
		*/
		private $rows_allowed;
		/**
			* @var long $rows_used The number of rows used by the user.
		*/
		private $rows_used;
		/**
			* @var string $trial_availed Used to identify the trial pack.
		*/
		private $trial_availed;
		/**
			* @var string $trial_plan The trial plan detail.
		*/
		private $trial_plan;
		/**
			* @var boolean $trial_status The trial pack status.
		*/
		private $trial_status;
		/**
			* @var string $trial_end_date The end date of the trial pack.
		*/
		private $trial_end_date;
		
		/**
			* @internal Creates a new PlanInfo instance.
		*/
		function __construct($JSON_result)
		{
			$JSON_result = $JSON_result['response']['result'];
			$this->plan = $JSON_result['plan'];
			$this->addons = $JSON_result['addon'];
			$this->billing_date = $JSON_result['billingDate'];
			$this->rows_allowed = $JSON_result['rowsAllowed'];
			$this->rows_used = $JSON_result['rowsUsed'];
			$this->trial_availed = $JSON_result['TrialAvailed'];
			if($this->trial_availed != "false")
			{
				$this->trial_plan = $JSON_result['TrialPlan'];
				$this->trial_status = $JSON_result['TrialStatus'];	
				$this->trial_end_date = $JSON_result['TrialEndDate'];
			}
		}
		
		/**
			* Get the type of the user plan.
			* @return string $plan The type of the user plan.
		*/
		function getPlan()
		{
			return $this->plan;
		}
		
		/**
			* Get all the addons of the account.
			* @return string $addons The addon details.
		*/
		function getAddons()
		{
			return $this->addons;
		}
		
		/**
			* Get the billing date of the plan.
			* @return string $billing_date The billing date.
		*/
		function getBillingDate()
		{
			return $this->billing_date;
		}
		
		/**
			* Get the total row allowed to the user.
			* @return long The total row allowed to the user.
		*/
		function getRowsAllowed()
		{
			return $this->rows_allowed;
		}
		
		/**
			* Get the number of rows that were used by the user.
			* @return long The number of rows used by the user.
		*/
		function getRowsUsed()
		{
			return $this->rows_used;
		}
		
		/**
			* This method is Used to identify the trial pack.
			* @return boolean $trial_availed Used to identify the trial pack.
		*/
		function isTrialAvailed()
		{
			return $this->trial_availed;
		}
		
		/**
			* Get the trial plan detail.
			* @return string  The trial plan detail.
		*/
		function getTrialPlan()
		{
			return $this->trial_plan;
		}
		
		/**
			* Get the trial pack status.
			* @return boolean The trial pack status.
		*/
		function getTrialStatus()
		{
			if($this->trial_status == 'true')
		         	{
		            		$this->trial_status = TRUE;
		         	}
		         	else
		         	{
		            		$this->trial_status = FALSE;
		         	}
			return $this->trial_status;
		}
		
		/**
			* Get the end date of the trial pack.
			* @return string The end date of the trial pack.
		*/
		function getTrialEndDate()
		{
			return $this->trial_end_date;
		}
	}

	/**
		* ShareInfo contains the workspace shared details.
	*/
	class ShareInfo
	{
		/**
			* @var array $group_members Group Members of the workspace.
		*/
		private $group_members;
		/**
			* @var array $admin_members workspace Admins of the workspace.
		*/
		private $admin_members;
		/**
			* @var array $shared_user_perm_info The PermissionInfo list object for the shared user.
		*/
		private $shared_user_perm_info;
		/**
			* @var array $group_perm_info The PermissionInfo list object for the groups.
		*/
		private $group_perm_info;
		/**
			* @var array $public_perm_info The PermissionInfo list object for the public link.
		*/
		private $public_perm_info;
		/**
			* @var array $private_link_perm_info The PermissionInfo list object for the private link.
		*/
		private $private_link_perm_info;
		/**
			* @var const GROUPNAME It will indicate the groups.
		*/
		const GROUPNAME = "groupName";
      	
		/**
			* @internal Create ShareInfo class instance.
		*/
		function __construct($JSON_response)
      		{
			$JSON_result = $JSON_response['response']['result'];
			$user_info = $JSON_result['usershareinfo'];
			$this->shared_user_perm_info = $this->getMailList($user_info, 'email');
			$group_info = $JSON_result['groupshareinfo'];
			$this->group_perm_info = $this->getMailList($group_info, 'groupName');
			$public_info = $JSON_result['publicshareinfo'];
			$this->public_perm_info = $this->getLinkList($public_info);
			$private_info = $JSON_result['privatelinkshareinfo'];
			$this->private_link_perm_info = $this->getLinkList($private_info);
			$this->admin_members = $JSON_result['dbownershareinfo']['dbowners'];
		}
      	
		/**
			* @internal Get the permission list.
		*/
		function getMailList($info, $name)
		{
			$permissionlist = array();
			$info_count = count($info);
			for($i = 0 ; $i < $info_count ; $i++)
			{
				$JSON_new_info = $info[$i]['shareinfo'];
				$user_list[$i] = $JSON_new_info[$name];
				$tablecount[$i] = count($JSON_new_info['permissions']);
				if($name == self::GROUPNAME)
				{
					$member_count[$i] = count($JSON_new_info['groupmembers']);
					$grp_details = array();
					$grp_details['name'] = $user_list[$i];
					$grp_details['desc'] = $JSON_new_info['desc'];
					if($member_count[$i] != 0)
					{
						for($j = 0 ; $j < $member_count[$i] ; $j++)
						{
							$grp_details['members'][$j] = $JSON_new_info['groupmembers'][$j];
						}
						$this->group_members[$i] = $grp_details;
					}
					else
					{
						$grp_details['members'] = array();
						$this->group_members[$i] = $grp_details;
					}
				}
				for($j = 0 ; $j < $tablecount[$i] ; $j++)
				{
					$JSON_info = $JSON_new_info['permissions'][$j]['perminfo'];
					$view_name = $JSON_info['viewname'];
					$shared_by = $JSON_info['sharedby'];
					$perm_info = new PermissionInfo($view_name, $shared_by);
					$permission = $JSON_info['permission'];
					foreach ($permission as $key => $value) 
					{
						$perm_info->setPermission($key, $value);
					}
					$permissionlist[$user_list[$i]][$j] = $perm_info;
				}
			}
			return $permissionlist;
		}

		/**
			* @internal Get the permission list.
		*/
		function getLinkList($info)
		{
			$permissionlist = array();
			if(array_key_exists("email", $info))
			{
				$email = $info['email'];
				$JSON_new_info = $info['permissions'];
				$tablecount = count($JSON_new_info);
				for($i = 0 ; $i < $tablecount ; $i++)
				{
					$JSON_info = $JSON_new_info[$i]['perminfo'];
					$view_name = $JSON_info['viewname'];
					$shared_by = $JSON_info['sharedby'];
					$perm_info = new PermissionInfo($view_name, $shared_by);
					$permission = $JSON_info['permission'];
					foreach ($permission as $key => $value) 
					{
						$perm_info->setPermission($key, $value);
					}
					$permissionlist[$email][$i] = $perm_info;
				}
			}
			return $permissionlist;
		}

		/**
			* This method is used to get the Shared Users of the specified workspace.
			* @return array Shared Users of the workspace.
		*/
		function getSharedUsers()
		{
			return array_keys($this->shared_user_perm_info);
		}

		/**
			* This method is used to get the Group Members of the specified workspace.
			* @return array Group Members of the workspace.
		*/
		function getGroupMembers()
		{
			return $this->group_members;
		} 

		/**
			* This method is used to get the workspace Admins of the specified workspace.
			* @return array workspace Admins of the workspace.
		*/
		function getDatabaseOwners()
		{
			return $this->admin_members;
		}

		/**
			* This method is used to get the Permissions of the Shared Users.
			* @return array-of-objects The PermissionInfo list for the Shared User.
		*/
		function getSharedUserPermissions()
		{
			return $this->shared_user_perm_info;
		}

		/**
			* This method is used to get the Permissions of the workspace Group.
			* @return array-of-objects The PermissionInfo list for the workspace Group.
		*/
		function getGroupPermissions()
		{
			return $this->group_perm_info;
		}

		/**
			* This method is used to get the Permissions of the Private Link.
			* @return array-of-objects The PermissionInfo list for the Private Link.
		*/
		function getPrivateLinkPermissions()
		{
			return $this->private_link_perm_info;
		}

		/**
			* This method is used to get the Permissions of the Public Visitors.
			* @return array-of-objects The PermissionInfo list for the Public Visitors.
		*/
		function getPublicPermissions()
		{
			return $this->public_perm_info;
		}
	}

	/**
		* PermissionInfo contains the permission details of views.
	*/
	class PermissionInfo 
	{
		/**
			* @var string $view_name View name of the user.
		*/
		public $view_name;
		/**
			* @var string $shared_by Contains the Shared by user mail-id.
		*/
		public $shared_by;
		/**
			* @var array $filter_criteria Contains Filter criteria..
		*/
		public $filter_criteria = NULL;
		/**
			* @var array $perms_map Contains permissions list of views.
		*/
		public $perms_map = array();

		/**
			* @internal Create PermissionInfo instance.
		*/
		function __construct($view_name, $shared_by)
		{
			$this->view_name = $view_name;
			$this->shared_by = $shared_by;
		}

		/**
			* @internal To set permissions.
		*/
		function setPermission($perm_name, $perm_value)
		{
			if($perm_value == 'true')
			{
				$perm_value = TRUE;
			}
			else
			{
				$perm_value = FALSE;
			}
			$this->perms_map[$perm_name] = $perm_value;
		}

		/**
			* @internal To set filter criteria.
		*/
		function setFilterCriteria($filter_criteria)
		{
			$this->filter_criteria = $filter_criteria;
		}

		/**
			* This method is used to get the name of the View that is shared.
			* @return String A String value holds the name of the view.
		*/
		function getViewName()
		{
			return $this->view_name;
		}

		/**
			* This method is used to get the email address of the user who shared the View.
			* @return String A String value holds the email address of the user who shared the view.
		*/
		function getSharedBy()
		{
			return $this->shared_by;
		}

		/**
			* This method is used to get the filter criteria associated to this PermissionInfo.
			* @return String A String value holds the filter criteria.
		*/
		function getFilterCriteria()
		{
			return $this->filter_criteria;
		}

		/**
			* This method is used to find whether this permission entry has READ permission.
			* @return Boolean A Boolean value holds whether the READ operation is allowed or not.
		*/
		function hasReadPermission()
		{
			return $this->perms_map["read"];
		}

		/**
			* This method is used to find whether this permission entry has EXPORT permission.
			* @return Boolean A Boolean value holds whether EXPORT operation is allowed or not.
		*/
		function hasExportPermission()
		{
			return $this->perms_map["export"];
		}

		/**
			* This method is used to find whether this permission entry has View Underlying Data permission.
			* @return Boolean A Boolean value holds whether View Underlying Data operation is allowed or not.
		*/
		function hasVUDPermission()
		{
			return $this->perms_map["vud"];
		}

		/**
			* This method is used to find whether this permission entry has ADDROW permission.
			* @return Boolean A Boolean value holds whether the ADDROW operation is allowed or not.
		*/
		function hasAddRowPermission()
		{
			return $this->perms_map["addrow"];
		}

		/**
			* This method is used to find whether this permission entry has UPDATEROW permission.
			* @return Boolean A Boolean value holds whether the UPDATEROW operation is allowed or not.
		*/
		function hasUpdateRowPermission()
		{
			return $this->perms_map["updaterow"];
		}

		/**
			* This method is used to find whether this permission entry has DELETEROW permission.
			* @return Boolean A Boolean value holds whether the DELETEROW operation is allowed or not.
		*/
		function hasDeleteRowPermission()
		{
			return $this->perms_map["deleterow"];
		}

		/**
			* This method is used to find whether this permission entry has DELETEALLROWS permission.
			* @return Boolean A Boolean value holds whether the DELETE ALL ROWS operation is allowed or not.
		*/
		function hasDeleteAllRowsPermission()
		{
			return $this->perms_map["deleteallrows"];
		}

		/**
			* This method is used to find whether this permission entry has APPENDIMPORT permission.
			* @return Boolean A Boolean value holds whether the APPEND IMPORT operation is allowed or not.
		*/
		function hasAppendImportPermission()
		{
			return $this->perms_map["appendimport"];
		}

		/**
			* This method is used to find whether this permission entry has UPDATEIMPORT permission.
			* @return Boolean A Boolean value holds whether the UPDATE IMPORT operation is allowed or not.
		*/
		function hasUpdateImportPermission()
		{
			return $this->perms_map["updateimport"];
		} 

		/**
			* This method is used to find whether this permission entry has TRUNCATEIMPORT permission.
			* @return Boolean A Boolean value holds whether the TRUNCATE IMPORT operation is allowed or not.
		*/
		function hasTruncateImportPermission()
		{
			return $this->perms_map["truncateimport"];
		}

		/**
			* This method is used to find whether this permission entry has DELETEUPDATEADDIMPORT permission.
			* @return Boolean A Boolean value holds whether the DELETEUPDATEADD IMPORT operation is allowed or not.
		*/
		function hasDeleteUpdateAddImportPermission()
		{
			return $this->perms_map["deleteupdateaddimport"];
		}

		/**
			* This method is used to find whether this permission entry has SHARE permission.
			* @return Boolean A Boolean value holds whether the SHARE permission operation is allowed or not.
		*/
		function hasSharePermission()
		{
			return $this->perms_map["share"];
		}
	}	

	/**
		* ParseException is thrown if the server has responded but client was not able to parse the response. Possible reasons could be version mismatch.The client might have to be updated to a newer version.
	*/
	class ParseException extends Exception
	{
		/**
			* @var string The error message sent by the server.
		*/
		private $error_message;

		/**
			* @internal Creates a new Parse_Exception instance.
		*/
		function __construct($error_message) 
		{
			$this->error_message = $error_message;
		}

		/**
			* Get the complete response content as sent by the server.
			* @return string The complete response content.
		*/
		function getResponseContent()
		{
			return "Error Message : $this->error_message";
		}
	}

	/**
		*IOException is thrown when an input or output operation is failed or interpreted.
	*/

	class IOException extends Exception
	{
		/**
			* @var string The error message sent by the server.
		*/
		private $error_message;
		/**
			* @var string The action to be performed over the resource specified by the URI.
		*/
		private $action;
		/**
			* @var int The HTTP status code for the request.
		*/
		private $HTTP_status_code;

		/**
			* @internal Creates a new IO_Exception instance.
		*/
		function __construct($error_message, $action, $HTTP_status_code) 
		{
			$this->error_message = $error_message;
			$this->action = $action;
			$this->HTTP_status_code = $HTTP_status_code;
		}

		/**
			* Get the complete response content as sent by the server.
			* @return string The complete response content.
		*/
		function getResponseContent()
		{
			$str1 = "HttpStatusCode: $this->HTTP_status_code ";
			$str2 = "Action: $this->action Error Message: $this->error_message";
			return "IO Exception ( ".$str1." ".$str2." )";
		}	
	}

	/**
		*ServerException is thrown if the report server has received the request but did not process the request due to some error. 
	*/
	class ServerException extends Exception
	{
		/**
			* @var int The error code sent by the server.
		*/
		private $error_code;
		/**
			* @var string The error message sent by the server.
		*/
		private $error_message;
		/**
			* @var string The action to be performed over the resource specified by the URI.
		*/
		private $action;
		/**
			* @var int The HTTP status code for the request.
		*/
		private $HTTP_status_code;

		/**
			* @internal Creates a new Server_Exception instance.
		*/
		function __construct($error_code, $error_message, $action, $HTTP_status_code) 
		{
			$this->error_code = $error_code;
			$this->error_message = $error_message;
			$this->action = $action;
			$this->HTTP_status_code = $HTTP_status_code;
		}

		/**
			* Get the error message sent by the server.
			* @return string The error message.
		*/
		function getErrorMessage()
		{
			return $this->error_message;
		}

		/**
			* Get the error code sent by the server.
			* @return int The error code.
		*/
		function getErrorCode()
		{
			return $this->error_code;
		}

		/**
			* Get The action to be performed over the resource specified by the URI.
			* @return string The action.
		*/
		function getAction()
		{
			return $this->action;
		}

		/**
			* Get the HTTP status code for the request.
			* @return int The HTTP status code.
		*/
		function getHTTPStatusCode()
		{
			return $this->HTTP_status_code;
		}

		/**
			* Get the complete response content as sent by the server. 
			* @return string The complete response content.
		*/
		function toString()
		{
			$str1 = "HttpStatusCode: $this->HTTP_status_code Error Code: $this->error_code";
			$str2 = "Action: $this->action Error Message: $this->error_message";
			return "ServerException ( ".$str1." ".$str2." )";
		}
	}
