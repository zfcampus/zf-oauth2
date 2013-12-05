<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use OAuth2\Server as OAuth2Server;
use OAuth2\Request as OAuth2Request;
use OAuth2\Response as OAuth2Response;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

class AuthController extends AbstractActionController
{
    /**
     * @var OAuth2Server
     */
    protected $server;

    /**
     * Constructor
     *
     * @param $server OAuth2Server
     */
    public function __construct(OAuth2Server $server)
    {
        $this->server = $server;
    }

    /**
     * Token Action (/oauth)
     */
    public function tokenAction()
    {
        $response = $this->server->handleTokenRequest(OAuth2Request::createFromGlobals());
        if ($response->isClientError()) {
            $parameters = $response->getParameters();
            $errorUri   = isset($parameters['error_uri']) ? $parameters['error_uri'] : null;
            return new ApiProblemResponse(
                new ApiProblem(
                    $response->getStatusCode(),
                    $parameters['error_description'],
                    $errorUri,
                    $parameters['error']
                )
            );
        }
        return $this->setHttpResponse($response);
    }

    /**
     * Test resource (/oauth/resource)
     */
    public function resourceAction()
    {
        // Handle a request for an OAuth2.0 Access Token and send the response to the client
        if (!$this->server->verifyResourceRequest(OAuth2Request::createFromGlobals())) {
            $response   = $this->server->getResponse();
            $parameters = $response->getParameters();
            $errorUri   = isset($parameters['error_uri']) ? $parameters['error_uri'] : null;
            return new ApiProblemResponse(
                new ApiProblem(
                    $response->getStatusCode(),
                    $parameters['error_description'],
                    $errorUri,
                    $parameters['error']
                )
            );
        }
        $httpResponse = $this->getResponse();
        $httpResponse->setStatusCode(200);
        $httpResponse->getHeaders()->addHeaders(array('Content-type' => 'application/json'));
        $httpResponse->setContent(
            json_encode(array('success' => true, 'message' => 'You accessed my APIs!'))
        );
        return $httpResponse;
    }

    /**
     * Authorize action (/oauth/authorize)
     */
    public function authorizeAction()
    {
        $request  = OAuth2Request::createFromGlobals();
        $response = new OAuth2Response();

        // validate the authorize request
        if (!$this->server->validateAuthorizeRequest($request, $response)) {
            $parameters = $response->getParameters();
            $errorUri   = isset($parameters['error_uri']) ? $parameters['error_uri'] : null;
            return new ApiProblemResponse(
                new ApiProblem(
                    $response->getStatusCode(),
                    $parameters['error_description'],
                    $errorUri,
                    $parameters['error']
                )
            );
        }

        $authorized = $request->request('authorized', false);
        if (empty($authorized)) {
            $clientId = $request->query('client_id', false);
            return array('clientId' => $clientId);
        }

        $is_authorized = ($authorized === 'yes');
        $this->server->handleAuthorizeRequest($request, $response, $is_authorized);
        if ($is_authorized) {
            $redirect = $response->getHttpHeader('Location');
            if (!empty($redirect)) {
                return $this->redirect()->toUrl($redirect);
            }
        }

        $parameters = $response->getParameters();
        $errorUri   = isset($parameters['error_uri']) ? $parameters['error_uri'] : null;
        return new ApiProblemResponse(
            new ApiProblem(
                $response->getStatusCode(),
                $parameters['error_description'],
                $errorUri,
                $parameters['error']
            )
        );
    }

    /**
     * Receive code action prints the code/token access
     */
    public function receiveCodeAction()
    {
        $code = $this->params()->fromQuery('code', false);
        return array(
            'code' => $code
        );
    }

    /**
     * Convert the OAuth2 response to a \Zend\Http\Response
     *
     * @param $response OAuth2Response
     * @return \Zend\Http\Response
     */
    private function setHttpResponse(OAuth2Response $response)
    {
        $httpResponse = $this->getResponse();
        $httpResponse->setStatusCode($response->getStatusCode());

        $headers = $httpResponse->getHeaders();
        $headers->addHeaders($response->getHttpHeaders());
        $headers->addHeaderLine('Content-type', 'application/json');

        $httpResponse->setContent($response->getResponseBody());
        return $httpResponse;
    }
}
