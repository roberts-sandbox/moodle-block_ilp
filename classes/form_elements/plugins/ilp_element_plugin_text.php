<?php

require_once($CFG->dirroot.'/blocks/ilp/classes/form_elements/ilp_element_plugin.php');

class ilp_element_plugin_text extends ilp_element_plugin {
	
	public $tablename;
	public $data_entry_tablename;
	public $minimumlength;
	public $maximumlength;
	
	    /**
     * Constructor
     */
    function __construct() {
    	
    	$this->tablename = "block_ilp_plu_tex";
    	$this->data_entry_tablename = "block_ilp_plu_tex_ent";
    	
    	parent::__construct();
    }
	
	
	/**
     * TODO comment this
     *
     */
    public function load($reportfield_id) {
		$reportfield		=	$this->dbc->get_report_field_data($reportfield_id);
		if (!empty($reportfield)) {
			//set the reportfield_id var
			$this->reportfield_id	=	$reportfield_id;
			
			//get the record of the plugin used for the field 
			$plugin		=	$this->dbc->get_form_element_plugin($reportfield->plugin_id);
						
			$this->plugin_id	=	$reportfield->plugin_id;
			
			//get the form element record for the reportfield 
			$pluginrecord	=	$this->dbc->get_form_element_by_reportfield($this->tablename,$reportfield->id);
			
			if (!empty($pluginrecord)) {
				$this->label			=	$reportfield->label;
				$this->description		=	$reportfield->description;
				$this->req			=	$reportfield->req;
				$this->maximumlength	=	$pluginrecord->maximumlength;
				$this->minimumlength	=	$pluginrecord->minimumlength;
				$this->position			=	$reportfield->position;
				return true;	
			}
		}
		return false;	
    }	

	
	/**
     *
     */
    public function install() {
        global $CFG, $DB;

        // create the table to store report fields
        $table = new $this->xmldb_table( $this->tablename );
        $set_attributes = method_exists($this->xmldb_key, 'set_attributes') ? 'set_attributes' : 'setAttributes';

        $table_id = new $this->xmldb_field('id');
        $table_id->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addField($table_id);
        
        $table_report = new $this->xmldb_field('reportfield_id');
        $table_report->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_report);
        
        $table_minlength = new $this->xmldb_field('minimumlength');
        $table_minlength->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_minlength);
        
        $table_maxlength = new $this->xmldb_field('maximumlength');
        $table_maxlength->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_maxlength);
        
        $table_timemodified = new $this->xmldb_field('timemodified');
        $table_timemodified->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_timemodified);

        $table_timecreated = new $this->xmldb_field('timecreated');
        $table_timecreated->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_timecreated);

        $table_key = new $this->xmldb_key('primary');
        $table_key->$set_attributes(XMLDB_KEY_PRIMARY, array('id'));
        $table->addKey($table_key);

        $table_key = new $this->xmldb_key('textplugin_unique_reportfield');
        $table_key->$set_attributes(XMLDB_KEY_FOREIGN_UNIQUE, array('reportfield_id'),'block_ilp_report_field','id');
        $table->addKey($table_key);
        

    /// Launch add key unique_position_report
        //$result = $result && add_key($table, $key);

        if(!$this->dbman->table_exists($table)) {
            $this->dbman->create_table($table);
        }
        
        
	    // create the new table to store responses to fields
        $table = new $this->xmldb_table( $this->data_entry_tablename );
        $set_attributes = method_exists($this->xmldb_key, 'set_attributes') ? 'set_attributes' : 'setAttributes';

        $table_id = new $this->xmldb_field('id');
        $table_id->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addField($table_id);
        
        $table_title = new $this->xmldb_field('value');
        $table_title->$set_attributes(XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addField($table_title);

        $table_report = new $this->xmldb_field('entry_id');
        $table_report->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_report);
        
        $table_maxlength = new $this->xmldb_field('textfield_id');
        $table_maxlength->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_maxlength);
        
        $table_timemodified = new $this->xmldb_field('timemodified');
        $table_timemodified->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_timemodified);

        $table_timecreated = new $this->xmldb_field('timecreated');
        $table_timecreated->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_timecreated);

        $table_key = new $this->xmldb_key('primary');
        $table_key->$set_attributes(XMLDB_KEY_PRIMARY, array('id'));
        $table->addKey($table_key);
        
       	$table_key = new $this->xmldb_key('textplugin_unique_textfield');
        $table_key->$set_attributes(XMLDB_KEY_FOREIGN_UNIQUE, array('textfield_id'), $this->tablename ,'id');
        $table->addKey($table_key);
                
        $table_key = new $this->xmldb_key('textplugin_unique_entry');
        $table_key->$set_attributes(XMLDB_KEY_FOREIGN, array('entry_id'),'block_ilp_entry','id');
        $table->addKey($table_key);
        
        if(!$this->dbman->table_exists($table)) {
            $this->dbman->create_table($table);
        }
        
        
    }

    /**
     *
     */
    public function uninstall() {
        $table = new $this->xmldb_table( $this->tablename );
        drop_table($table);
        
        $table = new $this->xmldb_table( $this->data_entry_tablename );
        drop_table($table);
    }
	
     /**
     *
     */
    public function audit_type() {
        return 'Text Field';
    }
    
    /**
    * function used to return the language strings for the plugin
    */
    function language_strings(&$string) {
        $string['ilp_element_plugin_text'] 		= 'Text';
        $string['ilp_element_plugin_text_type'] = 'Text';
        $string['ilp_element_plugin_text_description'] = 'A text field';
        $string['ilp_element_plugin_text_maxlengthrange'] = 'The maximum length field must have a value between 0 and 255';
        $string['ilp_element_plugin_text_maxlessthanmin'] = 'The maximum length field must have a greater value than the minimum length';
        $string['ilp_element_plugin_text_maximumlength'] = 'Maximum Length';
        $string['ilp_element_plugin_text_minimumlength'] = 'Minimum Length';
        
        return $string;
    }

   	/**
     * Delete a form element
     */
    public function delete_form_element($reportfield_id) {
    	return parent::delete_form_element($this->tablename, $reportfield_id);
    }
    
    /**
    * this function returns the mform elements taht will be added to a report form
	*
    */
    public	function entry_form( &$mform ) {
  	
    	//text field for element label
        $mform->addElement(
            'text',
            $this->reportfield_id,
            $this->label,
            array('class' => 'form_input')
        );
        
        if (!empty($this->minimumlength)) $mform->addRule("$this->reportfield_id", null, 'minlength', $this->maximumlength, 'client');
        if (!empty($this->maximumlength)) $mform->addRule("$this->reportfield_id", null, 'maxlength', $this->maximumlength, 'client');
        if (!empty($this->req)) $mform->addRule("$this->reportfield_id", null, 'required', null, 'client');
        $mform->setType('label', PARAM_RAW);
	 }
	 
	/**
    * this function saves the data entered on a entry form to the plugins _entry table
	* the function expects the data object to contain the id of the entry (it should have been
	* created before this function is called) in a param called id. 
	*
	* perhaps this should be in the element parent class
    */
	 public	function entry_specific_process_data($reportfield_id,$entry_id,$data) {
	 	
	 	//check to see if a entry record already exists for the reportfield in this plugin

	 	//get the plugin table record that has the reportfield_id 
	 	$pluginrecord	=	$this->dbc->get_plugin_record($this->tablename,$reportfield_id);
	 	if (empty($pluginrecord)) {
	 		print_error('pluginrecordnotfound');
	 	}
	 
	 	//get the _entry table record that has the pluginrecord id
	 	$entry 	=	$this->dbc->get_data_entry_record($this->data_entry_tablename,$pluginrecord->id);
	 	
	 	//if no record has been created create the entry record
	 	if (empty($entry)) {
	 		$pluginentry	=	new stdClass();
	 		$pluginentry->value	=	$data->$reportfield_id;
	 		$pluginentry->textfield_id	=	$pluginrecord->id;
	 		$result	= $this->dbc->create_plugin_entry($this->data_entry_tablename,$pluginentry);
	 	} else {
	 		//update the current record
	 		$entry->value	=	$data->$reportfield_id;
	 		$result	= $this->dbc->update_plugin_entry($this->data_entry_tablename,$pluginentry);
	 	}
	 	
	 	return (!empty($result)) ? true: false;
	 	
	 }
	 
	 
}
