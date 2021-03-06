<?php
/*******
 * doesn't allow this file to be loaded with a browser.
 */
if (!defined('AT_INCLUDE_PATH')) { exit; }

/******
 * this file must only be included within a Module obj
 */
if (!isset($this) || (isset($this) && (strtolower(get_class($this)) != 'module'))) { exit(__FILE__ . ' is not a Module'); }

/*******
 * assign the instructor and admin privileges to the constants.
 */
define('AT_PRIV_COURSE_SEATS',       $this->getPrivilege());
define('AT_ADMIN_PRIV_COURSE_SEATS', $this->getAdminPrivilege());

/*******
 * add the admin pages when needed.
 */
if (admin_authenticate(AT_ADMIN_PRIV_COURSE_SEATS, TRUE) || admin_authenticate(AT_ADMIN_PRIV_ADMIN, TRUE)) {
	global $_config, $db;
	// replace the create course tab for admin, removed by "disable_create" config setting
	// remove the following lines if installed on atsp
	if($_config['disable_create'] == "1"){

		$sql = "SELECT * FROM %smodules WHERE dir_name ='_core/services' && status ='2'";
		$row_services = queryDB($sql, array(TABLE_PREFIX));

		if(count($row_services) > 0){
		    //This is a Service site 
			$service_site = 1;
		}
		if($service_site > 0){
		    // do something
		} else{
		    $this->_pages['mods/_core/courses/admin/create_course.php']['title_var'] = 'create_course';
		    $this->_pages['mods/_core/courses/admin/create_course.php']['parent']    = 'mods/_core/courses/admin/courses.php';
		    $this->_pages['mods/_core/courses/admin/create_course.php']['guide']     = 'admin/?p=creating_courses.php';	
		    $this->_pages['mods/_core/courses/admin/courses.php']['children']  = array('mods/_core/courses/admin/create_course.php');
		}
		if(isset($_config['default_seats'])) {
		    $sql = "DELETE from %sconfig WHERE name='default_seats'";
		    $result = queryDB($sql, array(TABLE_PREFIX));
		} 
	} elseif(!isset($_config['default_seats'])) {
	    $sql = "REPLACE into %sconfig value('disable_create', '1')";
		$result = queryDB($sql, array(TABLE_PREFIX));
	}



	//If Payments is installed, add tab to payment manager
	if($_config['ec_uri']){
		$this->_pages['mods/payments/payments_admin.php']['children'] = array_merge(array('mods/course_seats/index_admin.php'), isset($this->_pages['mods/payments/payments_admin.php']['children']) ? $this->_pages['mods/payments/payments_admin.php']['children'] : array());;
	}
	$this->_pages['mods/_core/courses/admin/courses.php']['children']  = array_merge(array('mods/course_seats/index_admin.php'), isset($this->_pages['mods/_core/courses/admin/courses.php']['children']) ? $this->_pages['mods/_core/courses/admin/courses.php']['children'] : array());
	$this->_pages['mods/course_seats/index_admin.php']['title_var'] = 'seats_course_seats';
	$this->_pages['mods/course_seats/index_admin.php']['parent']    = 'mods/_core/courses/admin/courses.php';
	
	$this->_pages['mods/course_seats/seats_config.php']['title_var'] = 'seats_config';
	$this->_pages['mods/course_seats/seats_config.php']['parent']    = 'mods/course_seats/index_admin.php';

}

$this->_pages['mods/course_seats/disabled.php']['title_var'] = 'enrolment';
$this->_pages['mods/course_seats/disabled.php']['parent']    = 'mods/_core/enrolment/index.php';


/*******
* instructor Manage section added if course seats is set for a course:
*/
global $_config;

if($_SESSION['course_id'] > 0){
$sql = "SELECT * FROM %scourse_seats WHERE course_id = %d";
$row = queryDB($sql, array(TABLE_PREFIX, $_SESSION['course_id']), TRUE);
}


if($row['seats'] >= "1" && isset($_config['seats_allow']) && $_config['seats_allow'] != 0){
	$this->_pages['mods/course_seats/index_instructor.php']['title_var'] = 'course_seats';
	$this->_pages['mods/course_seats/index_instructor.php']['parent'] = 'mods/_core/enrolment/index.php';
	$this->_pages['mods/_core/enrolment/index.php']['children']  = array_merge(array('mods/course_seats/index_instructor.php'), isset($this->_pages['mods/_core/enrolment/index.php']['children']) ? $this->_pages['mods/_core/enrolment/index.php']['children'] : array());
}

// If a new course is being created and default seat limit is set
// Set the max seats for that course to the default_seats value
if($_config['default_seats'] && $_SESSION['course_id'] > 0){

	$sql = "SELECT seats from %scourse_seats WHERE course_id = %d";
	$rows_courses = queryDB($sql, array(TABLE_PREFIX, $_SESSION['course_id']));	

	if(count($rows_courses) == 0){

		$sql = "INSERT into %scourse_seats (`course_id`,`seats`) VALUES(%d,%d)";
		$result = queryDB($sql, array(TABLE_PREFIX, $_SESSION['course_id'], $_config['default_seats']));
	}
}

//require(AT_INCLUDE_PATH.'../mods/course_seats/course_seats.php');
?>