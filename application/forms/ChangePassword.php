<?php

class Application_Form_ChangePassword extends Zend_Form{
    public function init(){
        /* Form Elements & Other Definitions Here ... */
        // Set the method for the display form to POST
        $this->setMethod('post');
 
        // Add the password element
        $this->addElement('password', 'password', array(
            'label'      => 'New Password:',
            'required'   => true,
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(6, 20)),
//                array('validator'=>'PasswordConfirmation')
                )
        ));

        // Add the password element
        $this->addElement('password', 'password_confirm', array(
            'label'      => 'Password Again:',
            'required'   => true,
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(6, 20)),
                )
        ));
        // add the ajax button
        $this->addElement('button', 'ajax_button', array(
            'label'    => 'Change Password',
            'ignore'   => true,
            'onclick'  => 'ajax_changePwd(event, this)',
        ));
    }
}
?>
