<?php

namespace BookneticVendor;

if (\class_exists('BookneticVendor\\Google_Client', \false)) {
    // Prevent error with preloading in PHP 7.4
    // @see https://github.com/googleapis/google-api-php-client/issues/1976
    return;
}
$classMap = ['BookneticVendor\\Google\\Client' => 'Google_Client', 'BookneticVendor\\Google\\Service' => 'Google_Service', 'BookneticVendor\\Google\\AccessToken\\Revoke' => 'Google_AccessToken_Revoke', 'BookneticVendor\\Google\\AccessToken\\Verify' => 'Google_AccessToken_Verify', 'BookneticVendor\\Google\\Model' => 'Google_Model', 'BookneticVendor\\Google\\Utils\\UriTemplate' => 'Google_Utils_UriTemplate', 'BookneticVendor\\Google\\AuthHandler\\Guzzle6AuthHandler' => 'Google_AuthHandler_Guzzle6AuthHandler', 'BookneticVendor\\Google\\AuthHandler\\Guzzle7AuthHandler' => 'Google_AuthHandler_Guzzle7AuthHandler', 'BookneticVendor\\Google\\AuthHandler\\Guzzle5AuthHandler' => 'Google_AuthHandler_Guzzle5AuthHandler', 'BookneticVendor\\Google\\AuthHandler\\AuthHandlerFactory' => 'Google_AuthHandler_AuthHandlerFactory', 'BookneticVendor\\Google\\Http\\Batch' => 'Google_Http_Batch', 'BookneticVendor\\Google\\Http\\MediaFileUpload' => 'Google_Http_MediaFileUpload', 'BookneticVendor\\Google\\Http\\REST' => 'Google_Http_REST', 'BookneticVendor\\Google\\Task\\Retryable' => 'Google_Task_Retryable', 'BookneticVendor\\Google\\Task\\Exception' => 'Google_Task_Exception', 'BookneticVendor\\Google\\Task\\Runner' => 'Google_Task_Runner', 'BookneticVendor\\Google\\Collection' => 'Google_Collection', 'BookneticVendor\\Google\\Service\\Exception' => 'Google_Service_Exception', 'BookneticVendor\\Google\\Service\\Resource' => 'Google_Service_Resource', 'BookneticVendor\\Google\\Exception' => 'Google_Exception'];
foreach ($classMap as $class => $alias) {
//    \class_alias($class, $alias);
}
/**
 * This class needs to be defined explicitly as scripts must be recognized by
 * the autoloader.
 */
class Google_Task_Composer extends \BookneticVendor\Google\Task\Composer
{
}
/**
 * This class needs to be defined explicitly as scripts must be recognized by
 * the autoloader.
 */
//\class_alias('BookneticVendor\\Google_Task_Composer', 'Google_Task_Composer', \false);
if (\false) {
    class Google_AccessToken_Revoke extends \BookneticVendor\Google\AccessToken\Revoke
    {
    }
    class Google_AccessToken_Verify extends \BookneticVendor\Google\AccessToken\Verify
    {
    }
    class Google_AuthHandler_AuthHandlerFactory extends \BookneticVendor\Google\AuthHandler\AuthHandlerFactory
    {
    }
    class Google_AuthHandler_Guzzle5AuthHandler extends \BookneticVendor\Google\AuthHandler\Guzzle5AuthHandler
    {
    }
    class Google_AuthHandler_Guzzle6AuthHandler extends \BookneticVendor\Google\AuthHandler\Guzzle6AuthHandler
    {
    }
    class Google_AuthHandler_Guzzle7AuthHandler extends \BookneticVendor\Google\AuthHandler\Guzzle7AuthHandler
    {
    }
    class Google_Client extends \BookneticVendor\Google\Client
    {
    }
    class Google_Collection extends \BookneticVendor\Google\Collection
    {
    }
    class Google_Exception extends \BookneticVendor\Google\Exception
    {
    }
    class Google_Http_Batch extends \BookneticVendor\Google\Http\Batch
    {
    }
    class Google_Http_MediaFileUpload extends \BookneticVendor\Google\Http\MediaFileUpload
    {
    }
    class Google_Http_REST extends \BookneticVendor\Google\Http\REST
    {
    }
    class Google_Model extends \BookneticVendor\Google\Model
    {
    }
    class Google_Service extends \BookneticVendor\Google\Service
    {
    }
    class Google_Service_Exception extends \BookneticVendor\Google\Service\Exception
    {
    }
    class Google_Service_Resource extends \BookneticVendor\Google\Service\Resource
    {
    }
    class Google_Task_Exception extends \BookneticVendor\Google\Task\Exception
    {
    }
    interface Google_Task_Retryable extends \BookneticVendor\Google\Task\Retryable
    {
    }
    class Google_Task_Runner extends \BookneticVendor\Google\Task\Runner
    {
    }
    class Google_Utils_UriTemplate extends \BookneticVendor\Google\Utils\UriTemplate
    {
    }
}
