<?php

    define('_VALID_MOS', '1');
	define( '_JEXEC', 1 );	

    global $mosConfig_absolute_path, $mosConfig_live_site, $mosConfig_lang, $database,
    $mosConfig_mailfrom, $mosConfig_fromname, $mainframe;	
	
	 /*** access Joomla's configuration file ***/
	$my_path = dirname(__FILE__).'/../../../';
	if( file_exists($my_path."/../../../configuration.php")) {
		$absolute_path = dirname( $my_path."/../../../configuration.php" );
		require_once($my_path."/../../../configuration.php");
	}
	elseif( file_exists($my_path."/../../configuration.php")){
		$absolute_path = dirname( $my_path."/../../configuration.php" );
		require_once($my_path."/../../configuration.php");
	}
	elseif( file_exists($my_path."/configuration.php")){
		$absolute_path = dirname( $my_path."/configuration.php" );
		require_once( $my_path."/configuration.php" );
	}
	else {
		die( "Joomla Configuration File not found!" );
	}
   
    include_once( $my_path.'/compat.joomla1.5.php' );

    /*** VirtueMart part ***/        
    require_once($mosConfig_absolute_path.'/administrator/components/com_virtuemart/virtuemart.cfg.php');
    require_once(CLASSPATH. 'ps_main.php');
    
    require_once(CLASSPATH ."payment/ps_bradesco.cfg.php");
    require_once(CLASSPATH ."payment/ps_bradesco.php");
    require_once(CLASSPATH ."payment/bradesco/pbd.php");
  
    // inclui a configuração do joomla  
    if( class_exists( 'jconfig')) {        
        require_once ( JPATH_BASE. DS .'includes'. DS .'defines.php' );
        
        require_once ( JPATH_BASE. DS .'includes'. DS .'application.php' );
        require_once ( JPATH_SITE. DS .'includes'. DS .'database.php' );
        require_once ( JPATH_CONFIGURATION. DS .'configuration.php' );
        require_once ( JPATH_LIBRARIES. DS .'joomla'. DS .'plugin'. DS .'plugin.php' );
        require_once ( JPATH_LIBRARIES. DS .'joomla'. DS .'plugin'. DS .'helper.php' );
        $config = new JConfig();
        // create the mainframe object
        $mainframe = new JSite(get_object_vars($config));
        
        // load system plugin group
        // JPluginHelper::importPlugin( 'system' );

        // create the session
        $database =& JFactory::getDBO();

    } else {        
        require_once($mosConfig_absolute_path. '/includes/database.php');
        $database = new database( $mosConfig_host, $mosConfig_user, $mosConfig_password, $mosConfig_db, $mosConfig_dbprefix );
    }
    require_once( CLASSPATH. "language.class.php" );
    require_once( CLASSPATH."Log/Log.php" );
    $vmLoggerConf = array(
        'buffering' => true
    );
    /**
     * This Log Object will help us log messages and errors
     * See http://pear.php.net/package/Log
     * @global Log vmLogger
     */
    $vmLogger = &vmLog::singleton('display', '', '', $vmLoggerConf, PEAR_LOG_TIP);
    $GLOBALS['vmLogger'] =& $vmLogger;

    if (file_exists(CLASSPATH . 'phpmailer/class.phpmailer.php')) {
        require_once( CLASSPATH . 'phpmailer/class.phpmailer.php');
        $mail = new vmPHPMailer();
    } else {			
        require_once ( JPATH_LIBRARIES. DS .'phpmailer'. DS .'phpmailer.php' );
        $mail = new PHPMailer();
    }
    $mail->PluginDir = CLASSPATH . 'phpmailer/';
    $mail->SetLanguage("en", CLASSPATH . 'phpmailer/language/');
    
    // adiciona o envio da confirmacao via smtp
    if ( $mosConfig_mailer == 'smtp' ) {
        $mail->SMTPAuth = $mosConfig_smtpauth;
        $mail->Username = $mosConfig_smtpuser;
        $mail->Password = $mosConfig_smtppass;
        $mail->Host 	= $mosConfig_smtphost;
        $mail->Port		= $mosConfig_smtpport;
        $mail->SMTPSecure= $mosConfig_smtpsecure;
    } else {

        if ( $mosConfig_mailer == 'sendmail' ) {
            if (isset($mosConfig_sendmail)) {
                $mail->Sendmail = $mosConfig_sendmail;
            }
        }
    }
    /* Load the VirtueMart database class */
    require_once( CLASSPATH. 'ps_database.php' );
    // restart session
    require_once(CLASSPATH."ps_session.php");

    // Constructor initializes the session!
    $sess = new ps_session();                        
    
    // Include globals; for this, $db is needed, as is htmlTools.class.php
    $db = new ps_DB;
    require_once( CLASSPATH. 'htmlTools.class.php' );
    require_once( ADMINPATH. 'global.php' );

    /* load the VirtueMart Language File */
    if (file_exists( ADMINPATH. 'languages/common/brazilian_portuguese.php' )) {
		require_once( ADMINPATH. 'languages/common/brazilian_portuguese.php' );
	} else {
		require_once( ADMINPATH. 'languages/english.php' );
	}
 