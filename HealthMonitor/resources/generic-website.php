<?php
/**
 * Class to create Web page for 
 * 
 * @author João Guiomar
 * @since 3.0-b1
 */
abstract class Page {
	private $pageName;
	private $pageIcon;
	private $css;
	private $javascript;
	private $javascript_functions;
	private $forms;
	
	/**
	 * Function to process forms submit
	 * 
	 * @param string $type The form action
	 * @param array $errors Output error messages 
	 * @param array $messages Output success messages
	 * @return int 1 on success | <=0 on error
	 */
	abstract public static function process_submit($type,array &$errors,array &$messages);
	
	/**
	 * Class constructor
	 * 
	 * @param string $pageName The HTML page name
	 * @param string $pageIcon The HTML page icon link 32x32px
	 * @param array $css Array with all the necessary CSS files
	 * @param array $javascript Array with all the necessary JavaScript files
	 * @param array $javascript Array with javascript functions to run when opening the page
	 * @param array $forms Array with all the TrmForm objects
	 */
	public function __construct($pageName,
								$pageIcon,
								array $css, 
								array $javascript,
								array $javascript_functions,
								array $forms) {
		$this->pageName = $pageName;
		$this->pageIcon = $pageIcon;
		$this->css = $css;
		$this->javascript = $javascript;
		$this->javascript_functions = $javascript_functions;
		$this->forms = $forms;
	}

	static function currentPageURL() {
		if(!empty($_SERVER['HTTPS'])) {
			if($_SERVER['HTTPS'] == 'on')
				$pageURL = 'https://';
			else
				$pageURL = 'http://';
		} else {
			if($_SERVER['SERVER_PORT'] == 443)
				$pageURL = 'https://';
			else
				$pageURL = 'http://';
		}
		if($_SERVER['SERVER_PORT'] != '80')
			$pageURL .= $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
		else
			$pageURL .= $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	
		return $pageURL;
	}

	/**
	 * The separator Tab-Bar
	 *
	 * @param string $active The active Tab
	 * @param string $default The default active Tab
	 * @param array $options List of all Tab-Options
	 */
	static function separatorBar($active, $default, $options) {
		echo '<ul class="nav-tab">';
		foreach ( $options as $tab_id => $tab ) {
			if(!is_empty($active)) {
				$class = ( $tab_id == $active ) ? 'nav-tab-active' : NULL;
			} else {
				$class = ( $tab_id == $default ) ? 'nav-tab-active' : NULL;
			}
			echo '<li'.(!is_empty($class)?' class="'.$class.'"':'').'><a href="'.$tab['url'].'">'.$tab['label'].'</a></li>';
		}
		echo '</ul>';
	}
	
	/**
	 * The separator Button-Bar
	 *
	 * @param string $active The active button
	 * @param string $default The default active button
	 * @param array $options List of all Button-Options
	 */
	static function buttonBar($active, $default, $options, $show=true) {
		$bar = '<p>';
		foreach ( $options as $button_id => $button ) {
			if(!is_empty($active)) {
				$class = ( $button_id == $active ) ? 'button-active' : '';
			} else {
				$class = ( $button_id == $default ) ? ' button-active' : '';
			}
			$bar .= '<input type="button" value="'.$button['label'].'" class="'. $class .' button-secondary" onClick="javascript:window.location=\''.$button['url'].'\';"/>';
		}
		$bar .= '</p>';
		if($show) echo $bar;
		else return $bar;
	}
	
	/**
	 * Print HTML page
	 * 
	 * @param array $errors Array with all the process_submit error messages
	 * @param array $messages Array with all the process_submit success messages
	 * @param string $search The search string if a search was made
	 * @param boolean $showHeader Show wordpress page header
	 * @param boolean $showFooter Show wordpress page footer
	 */
	public function printPage(array $errors=array(),array $messages=array(),$search=NULL,$showHeader=false,$showFooter=false) {
		if($showHeader) require_once(ABSPATH . 'header.php');
		// CSS
		foreach($this->css as $css)
			echo '<link rel="stylesheet" href="'.$css.'" type="text/css" media="screen" />';
		// JavaScript
		foreach($this->javascript as $javascript)
			echo '<script type="text/javascript" src="'.$javascript.'"></script>';
		// JavaScript functions
		if(!is_empty($this->javascript_functions)) {
			echo '<script type="text/javascript">';
			foreach($this->javascript_functions as $javascript_function)
				echo "$javascript_function\n";
			echo '</script>';
		} 
		// HTML
		echo '<div class="wrap">';
		if(urlExists($this->pageIcon))	echo '<img class="icon32" src="'.$this->pageIcon.'" />';
		else if(is_html($this->pageIcon)) echo $this->pageIcon;
		echo '<h2>'; 
		$this->pageName;
		if(!is_empty($search)) {
			printf('<span class="subtitle">Search results for &#8220;%s&#8221;</span>',$search);
		} 
		echo '</h2>';
		
		if(!is_empty($errors)) {
			echo '<div class="error"><ul>';
			foreach ( $errors as $err ) {
				echo "<li>$err</li>\n";
			}
			echo '</ul></div>';
		}
		if(!is_empty($messages)) {
			foreach ($messages as $msg)
				echo '<div id="message" class="updated"><p>'.$msg.'</p></div>';
		}
		
		foreach($this->forms as $form) {
			$form->printForm();
		}
		echo '</div>';
		if($showFooter) require_once(ABSPATH . 'footer.php');
	}
}

class GenericForm {
	protected $currentPageUrl;
	protected $formName;
	protected $formAction;
	protected $elements;
	protected $submitButton;
	protected $onSubmit;
	protected $submitButtonName;
	protected $disableSubmitButton;
	protected $deleteButton;
	
	/**
	 * Class Constructor
	 *
	 * @param string $currentPageUrl Page where this object is being instantiated
	 * @param string $formName The HTML form name
	 * @param string $formAction The HTML form action
	 * @param array $elements Array [tr] of Array [td] with all the form input elements
	 * @param boolean $submitButton Insert a submit button in form
	 * @param string $onSubmit On submit form action
	 * @param string $submitButtonName Submit button name
	 * @param boolean $disableSubmitButton Show submit button 'disabled'
	 * @param boolean $deleteButton Add submit button with delete action
	 */
	public function __construct(
			$currentPageUrl,
			$formName,
			$formAction,
			array $elements,
			$submitButton=true,
			$onSubmit=NULL,
			$submitButtonName=NULL,
			$disableSubmitButton=false,
			$deleteButton=false) {
	
		$this->currentPageUrl = $currentPageUrl;
		$this->formName = $formName;
		$this->formAction = $formAction;
		$this->elements = $elements;
		$this->submitButton = $submitButton;
		$this->onSubmit = $onSubmit;
		$this->submitButtonName = $submitButtonName;
		$this->disableSubmitButton = $disableSubmitButton;
		$this->deleteButton = $deleteButton;
	}
	
	/**
	 * A select dropbox with a list days yyyy-mm-dd
	 * between start date and end date
	 *
	 * @param string $name Select box name
	 * @param string $startDate First option
	 * @param string $endDate Last option
	 * @param string $selectedDate Selected option
	 */
	static function dateSelect($name,$startDate,$endDate,$selectedDate=NULL) {
		$select = '<select name="'.$name.'" >';
		$dNow = strtotime($startDate);
		if(!is_empty($selectedDate)) {
			$dEnd = strtotime($selectedDate);
			while($dNow < $dEnd){
				$select .= "<option value='".date("Y-m-d",$dNow)."' >".date("Y-m-d",$dNow)."</option>";
				$dNow = strtotime('+1 day',$dNow);
			}
			$select .= "<option value='".date("Y-m-d",$dEnd)."' selected>".date("Y-m-d",$dEnd)."</option>";
			$dNow = strtotime('+1 day',$dNow);
		}
		$dEnd = strtotime($endDate);
		while($dNow <= $dEnd){
			$select .= "<option value='".date("Y-m-d",$dNow)."' >".date("Y-m-d",$dNow)."</option>";
			$dNow = strtotime('+1 day',$dNow);
		}
		$select .= '</select>';
		return $select;
	}
	
	/**
	 * A select dropbox with a list time hh:mm
	 * separated by 30 minutes
	 *
	 * @param string $name Select box name
	 * @param string $selectedTime Selected option
	 * @param string $startTime First option
	 */
	static function timeSelect($name,$selectedTime=NULL,$startTime=NULL) {
		$select = '<select name="'.$name.'" >';
		if(!is_empty($startTime)) {
			$hour = date('H',strtotime($startTime));
			$minute = date('i',strtotime($startTime));
			if($minute < 30) $tStart = strtotime($hour.':30');
			else $tStart = strtotime(++$hour.':00');
		} else {
			$tStart = strtotime('00:00');
		}
		$tNow = $tStart;
		if(!is_empty($selectedTime)) {
			$tEnd = strtotime($selectedTime);
			while($tNow < $tEnd){
				$select .= "<option value='".date("H:i",$tNow)."' >".date("H:i",$tNow)."</option>";
				$tNow = strtotime('+30 minutes',$tNow);
			}
			$select .= "<option value='".date("H:i",$tEnd)."' selected>".date("H:i",$tEnd)."</option>";
		}
		$tEnd = strtotime('23:30');
		while($tNow <= $tEnd){
			$select .= "<option value='".date("H:i",$tNow)."' >".date("H:i",$tNow)."</option>";
			$tNow = strtotime('+30 minutes',$tNow);
		}
	
		$select .= '</select>';
		return $select;
	}
	
	/**
	 * Returns a submit button, with provided text and appropriate class
	 *
	 * @copyright Wordpress
	 *
	 * @since 3.1.0
	 *
	 * @param string $text The text of the button (defaults to 'Save Changes')
	 * @param string $type The type of button. One of: primary, secondary, delete
	 * @param string $name The HTML name of the submit button. Defaults to "submit". If no id attribute
	 *               is given in $other_attributes below, $name will be used as the button's id.
	 * @param bool $wrap True if the output button should be wrapped in a paragraph tag,
	 * 			   false otherwise. Defaults to true
	 * @param array|string $other_attributes Other attributes that should be output with the button,
	 *                     mapping attributes to their values, such as array( 'tabindex' => '1' ).
	 *                     These attributes will be output as attribute="value", such as tabindex="1".
	 *                     Defaults to no other attributes. Other attributes can also be provided as a
	 *                     string such as 'tabindex="1"', though the array format is typically cleaner.
	 */
	protected function submitButton( $text = null, $type = 'primary large', $name = 'submit', $wrap = true, $other_attributes = null ) {
		if ( ! is_array( $type ) )
			$type = explode( ' ', $type );
	
		$button_shorthand = array( 'primary', 'small', 'large' );
		$classes = array( 'button' );
		foreach ( $type as $t ) {
			if ( 'secondary' === $t || 'button-secondary' === $t )
				continue;
			$classes[] = in_array( $t, $button_shorthand ) ? 'button-' . $t : $t;
		}
		$class = implode( ' ', array_unique( $classes ) );
	
		if ( 'delete' === $type )
			$class = 'button-secondary delete';
	
		$text = $text ? $text : __( 'Save Changes' );
	
		// Default the id attribute to $name unless an id was specifically provided in $other_attributes
		$id = $name;
		if ( is_array( $other_attributes ) && isset( $other_attributes['id'] ) ) {
			$id = $other_attributes['id'];
			unset( $other_attributes['id'] );
		}
	
		$attributes = '';
		if ( is_array( $other_attributes ) ) {
			foreach ( $other_attributes as $attribute => $value ) {
				$attributes .= $attribute . '="' . esc_attr( $value ) . '" '; // Trailing space is important
			}
		} else if ( !empty( $other_attributes ) ) { // Attributes provided as a string
			$attributes = $other_attributes;
		}
	
		$button = '<input type="submit" name="' . $name . '" id="' . $id . '" class="' . $class;
		$button	.= '" value="' . $text . '" ' . $attributes . ' />';
	
		if ( $wrap ) {
			$button = '<p class="submit">' . $button . '</p>';
		}
	
		echo $button;
	}
	
	protected function formTitle() {
		echo '<h3>' . $this->formName . '</h3>';
	}
	
	protected function formHead() {
		echo '<form name="'.$this->formAction.'" id="'.$this->formAction.'" enctype="multipart/form-data" method="post" action="'.($this->currentPageUrl!=NULL?$this->currentPageUrl:currentPageURL()).'&action='.$this->formAction.'" '.($this->onSubmit!=NULL?"onSubmit='$this->onSubmit'":"").'>';
	}
	
	protected function formFields() {
		if(is_array($this->elements) && count($this->elements)>0) {
			echo '<table class="form-table">';
			foreach($this->elements as $tr) {
				$tdStr = '';
				foreach($tr as $title => $td) {
					$tdStr .= '<th scope="row">'.$title.'</th>';
					$tdStr .= '<td>'.$td.'</td>';
				}
				// check for checkboxes - don't use tr class
				if((strpos($tdStr,'checkbox') !== false) || (strpos($tdStr,'radio') !== false) || (strpos($tdStr,'name="countdown"') !== false))
					echo '<tr>'.$tdStr.'</tr>';
				else
					echo '<tr class="form-field form-required">'.$tdStr.'</tr>';
			}
			echo '</table>';
		}
	}
	
	protected function formTail() {
		echo '<p class="submit">';
		if($this->submitButton) {
			$this->submitButton((is_empty($this->submitButtonName)?$this->formName:$this->submitButtonName),'primary',$this->formAction,false,($this->disableSubmitButton?'disabled':''));
			$space = '&nbsp;';
		}
		if($this->deleteButton) {
			echo $space; $this->submitButton('Delete','delete',$this->formAction.'-delete',false,'onclick="this.form.action=\''.($this->currentPageUrl!=NULL?$this->currentPageUrl:currentPageURL()).'&action='.$this->formAction.'-delete\';"');
		}
		echo '</p>';
		echo '</form>';
	}
}

/**
 * Class to create a form
 *
 * @author joao.guiomar
 * @since 3.0-b1
 */
class Form extends GenericForm {
	/**
	 * Class Constructor
	 * 
	 * @param string $currentPageUrl Page where this object is being instantiated
	 * @param string $formName The HTML form name
	 * @param string $formAction The HTML form action
	 * @param array $elements Array [tr] of Array [td] with all the form input elements
	 * @param boolean $submitButton Insert a submit button in form
	 * @param string $onSubmit On submit form action
	 * @param boolean $deleteButton Insert a delete button to form
	 */
	public function __construct(
			$currentPageUrl,
			$formName,
			$formAction,
			array $elements,
			$submitButton=true,
			$onSubmit=NULL,
			$submitButtonName=NULL,
			$deleteButton=false) {
		
		parent::__construct(
			$currentPageUrl,
			$formName,
			$formAction,
			$elements,
			$submitButton,
			$onSubmit,
			$submitButtonName,
			/*disableSubmitButton*/false,
			$deleteButton);
	}
	
	/**
	 * Print HTML form
	 */
	public function printForm() {
		$this->formTitle();
		$this->formHead();
		$this->formFields();
		$this->formTail();
	}
}

abstract class ListTable {
	protected $list;

	abstract function getColumns();
	abstract function defaultColumns($item,$column_name);
	abstract function prepare();
	
	// function searchBox() { }
	// function filterRow() { }

	function display() {
		if(is_array($this->list) && count($this->list)>0) {
			echo '<table class="list-table">';
			$columns = $this->getColumns();
			$th = '<tr>';
			foreach($columns as $col) {
				$th .= '<th scope="col">'.$col.'</th>';
			}
			$th .= '</th>';
			echo $th;
				
			foreach($this->list as $row) {
				$tr = '<tr>';
				foreach($columns as $col => $value) {
					$tr .= '<td>'.$this->defaultColumns($row,$col).'</td>';
				}
				$tr .= '</tr>';
				echo $tr;
			}
			echo '</table>';
		}
	}
}

/**
 * Class to create a form list
 *
 * @author joao.guiomar
 * @since 3.0-b1
 */
class FormList extends GenericForm {
	private $list;
	private $searchName;
	private $searchAction;
	private $filterAction;
	
	/**
	 * Class Constructor
	 *
	 * @param string $currentPageUrl Page where this object is being instantiated
	 * @param string $formName The HTML form name
	 * @param string $formAction The HTML form action
	 * @param List_Table $list List to be displayed
	 * @param string $searchName
	 * @param string $searchAction The HTML form search
	 * @param boolean $submitButton Insert a submit button in form
	 * @param string $onSubmit On submit form action
	 */
	public function __construct(
			$currentPageUrl,
			$formName,
			$formAction,
			ListTable $list,
			$searchName=NULL,
			$searchAction=NULL,
			$filterAction=NULL,
			array $elements=array(),
			$submitButton=false,
			$onSubmit=NULL,
			$submitButtonName=NULL,
			$disableSubmitButton=false) {

		parent::__construct(
				$currentPageUrl,
				$formName,
				$formAction,
				$elements,
				$submitButton,
				$onSubmit,
				$submitButtonName,
				$disableSubmitButton);
		
		$this->list = $list;
		$this->searchName = $searchName;
		$this->searchAction = $searchAction;
		$this->filterAction = $filterAction;
	}
	
	/**
	 * Print HTML list form
	 */
	public function printForm() {
		$this->formTitle();
		// seach form
		if(!is_empty($this->searchAction) && !is_empty($this->searchName)) {
			echo '<form method="post" action="'.($this->currentPageUrl!=NULL?$this->currentPageUrl:currentPageURL()).'&action='.$this->searchAction.'">';
			$this->list->searchBox($this->searchName, $this->searchAction);
			echo '</form>';
		}
		if(!is_empty($this->filterAction)) {
			echo '<form method="post" action="'.($this->currentPageUrl!=NULL?$this->currentPageUrl:currentPageURL()).'&action='.$this->filterAction.'">';
			$this->list->filterRow();
			echo '</form>';
		}
		// list form
		$this->formHead();
		$this->formFields();
		$this->list->display();
		$this->formTail();
	}
}
