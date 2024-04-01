<?php

namespace BookneticApp\Frontend\Controller;

use BookneticApp\Models\Customer;
use BookneticApp\Providers\Core\FrontendAjax;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Helper;

class ForgotPasswordAjax extends FrontendAjax
{
    public function forgot_password()
    {
        $email = Helper::_post('email', '', 'email');

        if( empty( $email ) )
        {
            return $this->response( false, bkntc__('Please fill in all required fields correctly!') );
        }

        $customerInf = Customer::noTenant()->where( 'email', $email )->fetch();

        if( ! email_exists( $email ) || ! $customerInf )
        {
            return $this->response( false, bkntc__('The email address is not registered!') );
        }

        $reset_token = AjaxHelper::generateUserActivationToken( $customerInf->id , $email );

        Customer::setData( $customerInf->id, 'pending_password_reset', 1 );
        Customer::setData( $customerInf->id, 'password_reset_last_sent', Date::epoch() );

        do_action( 'bkntc_customer_forgot_password', $reset_token, $customerInf->id );

        return $this->response( true );
    }

    public function resend_forgot_password_link()
    {
        $email = Helper::_post('email', '', 'email');

        if( empty( $email ) )
        {
            return $this->response( false );
        }

        $customerId = Customer::noTenant()->select([ 'id' ])->where( 'email', $email )->fetch()['id'];

        if ( empty( $customerId ) )
        {
            return $this->response( false );
        }

        $resendSent = Customer::getData( $customerId, 'pending_password_reset', 0 );
        $activationLastSent = Customer::getData( $customerId, 'password_reset_last_sent', 0 );

        if ( $resendSent != 1 )
        {
            return $this->response( false );
        }

        if ( $activationLastSent > Date::epoch( 'now', '- 1 minutes' ) )
        {
            return $this->response( false, bkntc__( 'Please wait at least a minute to resend again.' ) );
        }

        $reset_token = AjaxHelper::generateUserActivationToken( $customerId , $email );

        do_action( 'bkntc_customer_forgot_password', $reset_token, $customerId );

        Customer::setData( $customerId, 'password_reset_last_sent', Date::epoch() );

        return $this->response( true );
    }

    public function complete_forgot_password()
    {
        $token		    =	Helper::_post('token', '', 'string');
        $password1		=	Helper::_post('password1', '', 'string');
        $password2		=	Helper::_post('password2', '', 'string');

        if( empty( $token ) || empty( $password1 ) || empty( $password2 ) )
        {
            return $this->response( false, bkntc__('Please fill in all required fields correctly!') );
        }

        if( $password1 !== $password2 )
        {
            return $this->response( false, bkntc__('Please fill in all required fields correctly!') );
        }

        $tokenParts = explode('.', $token);

        if (count($tokenParts) !== 3)
        {
            return $this->response( false );
        }

        $header = json_decode(base64_decode($tokenParts[0]), true);
        $payload = json_decode(base64_decode($tokenParts[1]), true);

        if (is_array($header) &&
            is_array($payload) &&
            array_key_exists('id', $header) && is_numeric($header['id']) &&
            array_key_exists('expire', $header) && is_numeric($header['expire']) &&
            array_key_exists('email', $payload)) {
            $customerId = $header['id'];
            $expire = $header['expire'];
            $email = $payload['email'];
        }
        else
        {
            return $this->response( false );
        }


        $secret = Helper::getOption('purchase_code', '', false);
        $secret = hash_hmac('SHA256', $email, $secret, true);

        if ( ! Helper::validateToken($token, $secret) )
        {
            return $this->response( false );
        }

        if ( ! email_exists( $email ) )
        {
            return $this->response( false );
        }

        $wpUser = get_user_by( 'email', $email );

        if ( Customer::getData( $customerId, 'pending_password_reset' ) != 1 )
        {
            return $this->response( false );
        }

        $userPass = $password1;
        $userData = [ 'ID' => $wpUser->ID, 'user_pass' => $userPass ];

        wp_update_user($userData);

        Customer::deleteData( $customerId, 'pending_password_reset' );

        do_action( 'bkntc_customer_reset_password', $customerId);

        return $this->response( true );
    }

}