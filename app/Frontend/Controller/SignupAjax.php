<?php

namespace BookneticApp\Frontend\Controller;

use BookneticApp\Models\Customer;
use BookneticApp\Providers\Core\FrontendAjax;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Helper;
use Exception;

class SignupAjax extends FrontendAjax
{
    public function signup()
    {
        try
        {
            AjaxHelper::validateGoogleReCaptcha();
        }
        catch( \Exception $e )
        {
            return $this -> response( false, $e -> getMessage() );
        }

        $first_name		=	Helper::_post('first_name', '', 'string');
        $last_name		=	Helper::_post('last_name', '', 'string');
        $email			=	Helper::_post('email', '', 'email');
        $password		=	Helper::_post('password', '', 'string');

        if( empty( $first_name ) || empty( $last_name ) || empty( $email ) || empty( $password ) )
        {
            return $this->response( false, bkntc__('Please fill in all required fields correctly!') );
        }

        if( email_exists( $email ) )
        {
            return $this->response( false, bkntc__('This email is already registered!') );
        }

        $newUser = wp_insert_user( [
            'user_login'	=> $email,
            'user_email'	=> $email,
            'display_name'	=> $first_name . ' ' . $last_name,
            'first_name'	=> $first_name,
            'last_name'     => $last_name,
            'role'			=> 'booknetic_customer',
            'user_pass'		=> $password
        ]);

        if( is_wp_error( $newUser ) )
        {
            return $this->response( false, $newUser->get_error_message() );
        }

        $checkIfCustomer = Customer::noTenant()->where( 'email', $email )->fetch();

        if ( ! $checkIfCustomer )
        {
            Customer::noTenant()->insert([
                'user_id'           => $newUser,
                'first_name'        => $first_name,
                'last_name'         => $last_name,
                'email'             => $email
            ]);
        }
        else
        {
            //doit: NoTenant burda nezere alinsin eger sayt.com/tenant/login strukturuna kececekse yaxud eyni sehifede olacaqlarsa
            Customer::noTenant()->where( 'email', $email )->noTenant()->update([
                'user_id' => $newUser
            ]);
        }

        $customerId = Customer::noTenant()->select([ 'id' ])->where( 'email', $email )->fetch()['id'];

        Customer::setData( $customerId, 'pending_activation', 1 );
        Customer::setData( $customerId, 'activation_last_sent', Date::epoch() );

        do_action( 'bkntc_customer_sign_up_confirm', AjaxHelper::generateUserActivationToken( $customerId, $email ), $customerId );

        return $this->response( true );
    }

    public function resend_activation_link()
    {
        $email      = Helper::_post('email', '', 'email');

        if( empty( $email ) )
        {
            return $this->response( false );
        }

        $customerId = Customer::noTenant()->select([ 'id' ])->where( 'email', $email )->fetch()['id'];

        if ( empty( $customerId ) )
        {
            return $this->response( false );
        }

        $isActivated = Customer::noTenant()->getData( $customerId, 'pending_activation' );
        $activationLastSent = Customer::noTenant()->getData( $customerId, 'activation_last_sent', 0 );

        if ( $isActivated != 1 )
        {
            return $this->response( false );
        }

        if ( $activationLastSent > Date::epoch( 'now', '- 1 minutes' ) )
        {
            return $this->response( false, bkntc__( 'Please wait at least a minute to resend again.' ) );
        }

        do_action( 'bkntc_customer_sign_up_confirm', AjaxHelper::generateUserActivationToken( $customerId, $email ), $customerId );

        Customer::noTenant()->setData( $customerId, 'activation_last_sent', Date::epoch() );

        return $this->response( true );

    }

}