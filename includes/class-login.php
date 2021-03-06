<?php

class Affiliate_WP_Login {

	private $errors;

	public function __construct() {

		add_action( 'affwp_user_login', array( $this, 'process_login' ) );

	}

	public function process_login( $data ) {

		if( ! isset( $_POST['affwp_login_nonce'] ) || ! wp_verify_nonce( $_POST['affwp_login_nonce'], 'affwp-login-nonce' ) ) {
			return;
		}

		do_action( 'affwp_pre_process_login_form' );

		if( empty( $data['affwp_user_login'] ) ) {
			$this->add_error( 'empty_username', __( 'Invalid username', 'affiliate-wp' ) );
		}

		$user = get_user_by( 'login', $_POST['affwp_user_login'] );

		if( ! $user ) {
			$this->add_error( 'no_such_user', __( 'Invalid username', 'affiliate-wp' ) );
		}

		if( empty( $_POST['affwp_user_pass'] ) ) {
			$this->add_error( 'empty_password', __( 'Please enter a password', 'affiliate-wp' ) );
		}

		if( $user ) {
			// check the user's login with their password
			if( ! wp_check_password( $_POST['affwp_user_pass'], $user->user_pass, $user->ID ) ) {
				// if the password is incorrect for the specified user
				$this->add_error( 'password_incorrect', __( 'Incorrect password', 'affiliate-wp' ) );
			}
		}

		if( function_exists( 'is_limit_login_ok' ) && ! is_limit_login_ok() ) {

			$this->add_error( 'limit_login_failed', limit_login_error_msg() );

		}

		do_action( 'affwp_process_login_form' );


		// only log the user in if there are no errors
		if( empty( $this->errors ) ) {

			$remember = isset( $_POST['affwp_user_remember'] );

			$this->log_user_in( $user->ID, $_POST['affwp_user_login'], $remember );

		} else {

			if( function_exists( 'limit_login_failed' ) ) {
				limit_login_failed( $_POST['affwp_user_login'] );
			}

		}
	}

	private function log_user_in( $user_id = 0, $user_login = '', $remember = false ) {

		$user = get_userdata( $user_id );
		if( ! $user )
			return;

		wp_set_auth_cookie( $user_id, $remember );
		wp_set_current_user( $user_id, $user_login );
		do_action( 'wp_login', $user_login, $user );

	}

	private function add_error( $error_id, $message = '' ) {
		$this->errors[ $error_id ] = $message;
	}

	public function print_errors() {

		if( empty( $this->errors ) ) {
			return;
		}

		echo '<div class="affwp_errors">';

		foreach( $this->errors as $error_id => $error ) {

			echo '<p class="affwp_error">' . esc_html( $error ) . '</p>';

		}

		echo '</div>';

	}

}