<?php

class Application_Form_Login extends Zend_Form
{
    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
        // Set the method for the display form to POST
        $this->setMethod('post');
 
        // Add an username element
        $this->addElement('text', 'username', array(
            'label'      => 'User Name:',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(3, 20))
            )
        ));
 
        // Add the password element
        $this->addElement('password', 'password', array(
            'label'      => 'Password:',
            'required'   => true,
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(6, 20))
                )
        ));
/*        
        // Add a captcha
        $this->addElement('captcha', 'captcha', array(
            'label'      => 'Please enter the 5 letters displayed below:',
            'required'   => true,
            'captcha'    => array(
                'captcha' => 'Figlet',
                'wordLen' => 5,
                'timeout' => 300
            )
        ));
*/        
        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Login',
        ));
    }
}

?>