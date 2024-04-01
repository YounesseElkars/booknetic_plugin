<?php

namespace BookneticApp\Frontend\Controller;

use BookneticApp\Models\Customer;
use BookneticApp\Providers\Core\FrontendAjax;
use BookneticApp\Providers\Helpers\Helper;

class SigninAjax extends FrontendAjax
{
    public function signin()
    {
        $login    = Helper::_post( 'login', '', 'string' );
        $password = Helper::_post( 'password', '', 'string' );

        if ( empty( $login ) || empty( $password ) )
        {
            return $this->response( false, bkntc__( 'Please enter your email and password correctly!' ) );
        }

        $user = $this->getUser( $login );

        if( ! $user || !wp_check_password( $password, $user->data->user_pass, $user->ID ) )
        {
            return $this->response(false, bkntc__('Email or password is incorrect!'));
        }

        $email = $user->user_email;

        $customerInf = Customer::where( 'email', $email )->fetch();

        if ( isset( $customerInf[ 'id' ] ) && Customer::getData( $customerInf->id, 'pending_activation' ) == 1 )
        {
            return $this->response( false, bkntc__( 'Your account is not activated' ) );
        }

//        if( in_array( 'booknetic_saas_tenant', $user->roles ) )
//        {
//            return $this->response( false, bkntc__('Please use the log in page from your SaaS provider. This page is intended for customers.') );
//        }

        wp_set_current_user( $user->ID );
        wp_set_auth_cookie( $user->ID, true );
        do_action( 'wp_login', $user->user_login, $user );

        return $this->response( true, [
            'url'   => Helper::getURLOfUsersDashboard( $user )
        ]);
    }

    private function getUser( $login )
    {
        if ( filter_var( $login, FILTER_VALIDATE_EMAIL ) )
            return get_user_by( 'email', $login );

        return get_user_by( 'login', $login );
    }

}