<?php

namespace OAuth2\Controller;

use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;

/**
 *  This controller is called when a "resource" is requested.
 *  call verifyResourceRequest in order to determine if the request
 *  contains a valid token.
 *
 * @code
 *     if (!$resourceController->verifyResourceRequest(OAuth2\Request::createFromGlobals(), $response = new OAuth2\Response())) {
 *         $response->send(); // authorization failed
 *         exit();
 *     }
 *     return json_encode($resource); // valid token!  Send the stuff!
 * @endcode
 */
interface ResourceControllerInterface
{
    /**
     * Verify the resource request
     *
     * @param RequestInterface  $request  - Request object
     * @param ResponseInterface $response - Response object
     * @param string            $scope
     * @return mixed
     */
    public function verifyResourceRequest(RequestInterface $request, ResponseInterface $response, $scope = null);

    /**
     * Get access token data.
     *
     * @param RequestInterface  $request  - Request object
     * @param ResponseInterface $response - Response object
     * @return mixed
     */
    public function getAccessTokenData(RequestInterface $request, ResponseInterface $response);
}
