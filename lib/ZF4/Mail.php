<?php

/**
 * ZF4 Library
 *
 * @category	ZF4
 * @package 	Mail
 * @author 	Ashley Kitson
 * @copyright   ZF4 Business Limited 2011, UK
 * @license     GNU AFFERO GENERAL PUBLIC LICENSE V3
 * 
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *    License text is located in /docs/LICENSE.FAMILYMAP.txt
 */

/**
 * Adds additional email ability
 *
 * @category	ZF4
 * @package 	Mail
 */
class ZF4_Mail extends Zend_Mail {

    /**
     * messenger trait
     *
     * @var Saj_Messenger
     */
    private $_messenger;

    /**
     * Mail transport we are using
     *
     * @var Zend_Mail_Transport_Abstract
     */
    private $_transport;

    /**
     * Constructor
     * 
     * options file is relative to application folder
     *
     * @param string|array $options name of config file or array of options
     * @param string $charset  Character set  to use
     */
    public function __construct($options = '/config/mail.xml', $charset = "iso-8859-1") {
        parent::__construct($charset);
        //add ability to store error messages
        $this->_messenger = new ZF4_Messenger();
        //set up mail transport
        if (is_string($options)) {
            //set options from config file
            $cfg = new Zend_Config_Xml(ZF4_BASE_PATH . '/application' . $options);
            $options = $cfg->toArray();
        } elseif (!is_array($options)) {
            throw new ZF4_Exception('Invalid options passed to ZF4_Mail', E_USER_ERROR);
        }
        $opts = (!isset($options['xtraParams']) || empty($options['xtraParams']) ? null : $options['xtraParams']);
        $this->_transport = (strtolower($options['transport']) == 'smtp' ? new Zend_Mail_Transport_Smtp($options['smtpHost'], $opts) : new Zend_Mail_Transport_Sendmail($opts));

        //set the from address if available
        if (isset($options['from']) && !empty($options['from'])) {
            $fromName = (isset($options['fromName']) && !empty($options['fromName']) ? $options['fromName'] : null);
            $this->setFrom($options['from'], $fromName);
        }
    }

    /**
     * Overide parent send() to use our pre configured transport if required
     *
     * @param Zend_Mail_Transport_Abstract $transport
     * @return Saj_Trait_Mail Fluent Interface
     */
    public function send(Zend_Mail_Transport_Abstract $transport = null) {
        if (is_null($transport)) {
            $transport = $this->_transport;
        }
        parent::send($transport);
        return $this;
    }

    /**
     * Send an HTML formatted mail defined by a Template
     *
     * @param Zend_View_Abstract $view The view controller
     * @param string $tplName Template name relative to application/views/mail directory
     * @param array|null $vars array of variables [$varName=>$value] to pass to the template
     * @param string $to recipient email address
     * @param string $subject email subject
     * @param string $cc email CCs
     * @param array $attachments mail attachments [0..n[title,description,url,path]]
     * @param string $encoding  Encoding to use
     */
    public function renderMailTemplate(
    Zend_View_Abstract $view, $tplName, $vars, $to, $subject, $cc = null, $attachments = null, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE) {

        $this->clearAll();
        //save the scriptpath
        $prevScriptPath = $view->getScriptPaths();
        //set up stylesheet for loading in controller
        //$view->headLink()->appendStylesheet('/css/' . $cssName,'screen');
        //set new script path
        $view->setScriptPath(ZF4_BASE_PATH . '/application/views/mail/');

        //add any view values
        foreach ($vars as $key => $val) {
            $view->$key = $val;
        }
        //render the html
        $body = $view->render($tplName);
        //reset script path
        $view->setScriptPath($prevScriptPath);

        //add any attachments
        if (!is_null($attachments) && count($attachments) > 0) {
            foreach ($attachments as $attachment) {
                $content = file_get_contents($attachment['path']);
                $at = $this->createAttachment($content);
                $at->filename = pathinfo($attachment['path'], PATHINFO_BASENAME);
            }
        }
        if (!is_null($cc)) {
            $this->addCc($cc);
        }
        $this->addTo($to)
                ->setSubject($subject)
                ->setBodyHtml($body, $this->getCharset(), $encoding)
                ->send();
    }

    /**
     * Send a text email
     *
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param string $cc
     * @param array $attachments [0..n[title,description,url,path]]
     */
    public function sendTextMail($to, $subject, $body, $cc = null, $attachments = null) {
        $this->clearAll();

        //add any attachments
        if (!is_null($attachments) && count($attachments) > 0) {
            foreach ($attachments as $attachment) {
                $content = file_get_contents($attachment['path']);
                $at = $this->createAttachment($content);
                $at->filename = pathinfo($attachment['path'], PATHINFO_BASENAME);
            }
        }
        //add optional cc's
        if (!is_null($cc)) {
            $this->addCc($cc);
        }

        //send the email
        $this->addTo($to)
                ->setSubject($subject)
                ->setBodyText($body)
                ->send();
    }

    /**
     * Clear down parameters of mailer as we are re-using the same instance
     * and Zend_Mail doesn't like resetting the headers etc
     *
     */
    public function clearAll() {
        $this->clearRecipients()
                ->clearDate()
                ->clearReturnPath()
                ->clearSubject()
                ->clearMessageId();
    }

    /**
     * Return any messages
     *
     * @return array  Array of message strings
     */
    public function getMsg() {
        return $this->_messenger->getMsg();
    }

}