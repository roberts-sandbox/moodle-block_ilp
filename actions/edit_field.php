<?php 

/**
 * Allows the user to create and edit fields 
 *
 * @copyright &copy; 2011 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package ILP
 * @version 2.0
 */

require_once('../configpath.php');

global $USER, $CFG, $SESSION, $PARSER;

//include any neccessary files

// Meta includes
require_once($CFG->dirroot.'/blocks/ilp/actions_includes.php');

//get the id of the course that is currently being used
$course_id = $PARSER->required_param('course_id', PARAM_INT);

//the id of the report  that the field will be in 
$report_id = $PARSER->required_param('report_id', PARAM_INT);

//the id of the plugin ype the field will be
$plugin_id = $PARSER->required_param('plugin_id', PARAM_INT);

//the id of the reportfield used when editing
$reportfield_id = $PARSER->optional_param('reportfield_id',null ,PARAM_INT);

// instantiate the db
$dbc = new ilp_db();

$course	=	$dbc->get_course($course_id);

//set the required level of permission needed to view this page

// setup the navigation breadcrumbs
$PAGE->navbar->add(get_string('blockname', 'block_ilp'),null,'title');
$PAGE->navbar->add($course->shortname,null,'title');
$PAGE->navbar->add($course->shortname,null,'title');

// setup the page title and heading
$PAGE->set_title($course->shortname.': '.get_string('blockname','block_ilp'));
$PAGE->set_heading($course->fullname);
$PAGE->set_url('/blocks/ilp/', $PARSER->get_params());

$pluginrecord	=	$dbc->get_plugin_by_id($plugin_id);

$classname = $pluginrecord->name;

// include the moodle form for this table
include_once("{$CFG->dirroot}/blocks/ilp/classes/form_elements/plugins/{$classname}.php");

if(!class_exists($classname)) {
 	print_error('noclassforplugin', 'block_ilp', '', $pluginrecord->name);
}

$pluginclass	=	new $classname();

$pluginclass->edit($course_id,$report_id,$plugin_id,$reportfield_id);






require_once($CFG->dirroot.'/blocks/ilp/views/edit_field.html');

?>
