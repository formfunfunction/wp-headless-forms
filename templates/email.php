<?php
/**
 * Email content functions
 *
 * @since 0.0.1
 * @package WPHeadlessForms
 */

/**
 * Returns content of email notification body.
 *
 * @param Array $fields Array of fields to include in email.
 * @since 0.0.1
 */
function wphf_notification_email_body( $fields ) {
	ob_start(); // begin collecting output.
	include 'email-styles.php';
	$css = ob_get_clean(); // retrieve output from myfile.php, stop buffering.

	ob_start(); // begin collecting output.
	include 'email-template-header.php';
	$header = ob_get_clean(); // retrieve output from myfile.php, stop buffering.

	ob_start(); // begin collecting output.
	include 'email-template-footer.php';
	$footer = ob_get_clean(); // retrieve output from myfile.php, stop buffering.

	$message_body  = $header;
	$message_body .= '<p>You have recieved an email via your website contact form.</p>';
	$message_body .= '<table cellspacing="0">';

	foreach ( $fields as $key => $value ) {
		$message_body .= '<tr><td id="field_name"><strong>' . $key . '</strong></td><td>' . nl2br( $value ) . '</td></tr>';
	}

	$message_body .= '</table>';

	$message_body .= $footer;

	$emogrifier = new \Pelago\Emogrifier( $message_body, $css );

	return $emogrifier->emogrify();
}

/**
 * Returns content of email confirmation body.
 *
 * @param Array $fields Array of fields to include in email.
 * @since 0.0.1
 */
function wphf_confirmation_email_body( $fields ) {
	ob_start(); // begin collecting output.
	include 'email-styles.php';
	$css = ob_get_clean(); // retrieve output from myfile.php, stop buffering.

	ob_start(); // begin collecting output.
	include 'email-template-header.php';
	$header = ob_get_clean(); // retrieve output from myfile.php, stop buffering.

	ob_start(); // begin collecting output.
	include 'email-template-footer.php';
	$footer = ob_get_clean(); // retrieve output from myfile.php, stop buffering.

	$message_body  = $header;
	$message_body .= '<p>Thanks for your message, ' . $fields['name'] . '.</p><p>We\'ll be in touch soon.</p>';
	$message_body .= '<table cellspacing="0">';
	$message_body .= '<tr><td id="field_name"><strong>Your message</strong></td><td>' . nl2br( $fields['message'] ) . '</td></tr>';
	$message_body .= '</table>';

	$message_body .= $footer;

	$emogrifier = new \Pelago\Emogrifier( $message_body, $css );

	return $emogrifier->emogrify();
}
